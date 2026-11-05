<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\CartItem;
use App\Models\Coupon;
use App\Models\CouponUsage;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderServiceRequest;
use App\Models\OrderShipment;
use App\Models\OrderStatusHistory;
use App\Models\Product;
use App\Models\ProductSku;
use App\Models\Referral;
use App\Models\Setting;
use App\Models\User;
use App\Services\NotificationService;
use App\Services\PricingService;
use App\Services\WalletService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function __construct(
        private WalletService       $walletService,
        private PricingService      $pricingService,
        private NotificationService $notifier,
    ) {}

    // â”€â”€ Index â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€

    public function index()
    {
        $user   = auth()->user();
        $orders = Order::where('user_id', $user->id)
            ->with(['items', 'serviceRequests', 'shipment'])
            ->latest()->paginate(15);
        $symbol = $user->currencySymbol();

        // Orders eligible to combine (at china_warehouse, not in a shipment yet, with weight set)
        $warehouseOrders = Order::where('user_id', $user->id)
            ->where('status', 'china_warehouse')
            ->whereNull('shipment_id')
            ->get();

        return view('user.orders.index', compact('orders', 'symbol', 'user', 'warehouseOrders'));
    }

    // â”€â”€ Checkout â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€

    public function checkout(Request $request)
    {
        $request->validate([
            'shipping_method'  => 'required|string',
            'coupon_code'      => 'nullable|string|max:30',
            'terms_accepted'   => 'accepted',
        ]);

        // Only sea shipping is allowed at checkout
        if (!str_starts_with($request->shipping_method, 'sea')) {
            return back()->with('error', 'We couldn\'t process your order. Please return to checkout and try again.');
        }

        $user  = auth()->user();
        $items = CartItem::where('user_id', $user->id)->with('product')->get();

        if ($items->isEmpty()) {
            return back()->with('error', __('cart.empty'));
        }

        foreach ($items as $item) {
            if ($item->isOutOfStock()) {
                return back()->with('error', __('cart.item_out_of_stock', ['title' => $item->getTitle()]));
            }
        }

        $totalWeightKg = $items->sum(fn($i) => ($i->product?->getBillableWeight() ?? 0.5) * $i->quantity);
        $isFirstOrder  = Order::where('user_id', $user->id)->doesntExist();

        $pricing = $this->pricingService->calculateOrderTotal(
            cartItems:         $items->all(),
            currency:          $user->currency,
            country:           $user->country,
            shippingMethod:    $request->shipping_method,
            estimatedWeightKg: $totalWeightKg,
            isHomeDelivery:    false,
            deliveryState:     '',
            couponCode:        $request->coupon_code,
            isFirstOrder:      $isFirstOrder
        );

        if (!$this->walletService->hasSufficientBalance($user, $pricing['grand_total'])) {
            return back()->with('error', __('wallet.insufficient_balance'));
        }

        DB::transaction(function () use ($user, $items, $pricing, $request, $totalWeightKg) {
            $this->walletService->debit(
                user:        $user,
                amount:      $pricing['grand_total'],
                category:    'order_payment',
                description: 'Order payment (products + service fee)',
            );

            $order = Order::create([
                'order_number'               => Order::generateOrderNumber(),
                'user_id'                    => $user->id,
                'country'                    => $user->country,
                'currency'                   => $user->currency,
                'exchange_rate_used'         => $pricing['exchange_rate_used'],
                'items_total_cny'            => $pricing['items_total_cny'],
                'items_total_local'          => $pricing['items_total_local'],
                'purchase_fee'               => $pricing['purchase_fee'],
                'international_shipping_fee' => 0,
                'local_freight_fee'          => $pricing['domestic_freight_local'] ?? 0,
                'discount_amount'            => $pricing['discount_amount'],
                'coupon_discount'            => $pricing['coupon_discount'],
                'grand_total'                => $pricing['grand_total'],
                'coupon_id'                  => $pricing['coupon']?->id,
                'coupon_code'                => $pricing['coupon']?->code,
                'shipping_method'            => $request->shipping_method,
                'estimated_weight_kg'        => $totalWeightKg,
                'shipping_fee_status'        => 'unpaid',
                'delivery_type'              => 'pickup', // user selects delivery type when goods arrive in Nigeria
                'doorstep_status'            => 'none',
                'status'                     => 'paid',
                'paid_at'                    => now(),
                'buyer_note'                 => $request->buyer_note ?: null,
            ]);

            foreach ($items as $item) {
                $unitLocal = round($item->unit_price_cny * $pricing['exchange_rate_used'], 2);
                OrderItem::create([
                    'order_id'          => $order->id,
                    'product_id'        => $item->product_id,
                    'product_snapshot'  => [
                        'title'         => $item->getTitle(),
                        'images'        => $item->product?->images ?? ($item->scraped_data['images'] ?? []),
                        'source_url'    => $item->product?->source_url,
                        // Snapshot the variant-specific image so admin order page
                        // shows the exact colour/style the customer selected
                        'variant_image' => $item->selected_variant && $item->product_id
                            ? optional(
                                ProductSku::where('product_id', $item->product_id)
                                    ->where('is_available', true)
                                    ->get()
                                    ->first(function ($sku) use ($item) {
                                        $attrs = is_string($sku->attributes)
                                            ? json_decode($sku->attributes, true)
                                            : ($sku->attributes ?? []);
                                        $label = collect($attrs)->map(fn($v, $k) => "{$k}: {$v}")->implode(' / ');
                                        return str_contains($item->selected_variant, $label)
                                            || $item->selected_variant === $label
                                            || collect($attrs)->values()->contains(
                                                fn($v) => str_contains($item->selected_variant, (string)$v)
                                            );
                                    })
                              )->image
                            : null,
                    ],
                    'selected_variant'  => $item->selected_variant,
                    'quantity'          => $item->quantity,
                    'unit_price_cny'    => $item->unit_price_cny,
                    'unit_price_local'  => $unitLocal,
                    'total_price_local' => round($unitLocal * $item->quantity, 2),
                ]);

                if ($item->product_id) {
                    Product::where('id', $item->product_id)->increment('order_count', $item->quantity);
                }
            }

            OrderStatusHistory::create([
                'order_id'   => $order->id,
                'old_status' => null,
                'new_status' => 'paid',
                'note'       => 'Order placed. Products + service fee paid. Shipping fee will be assigned after weighing.',
            ]);

            $order->update(['byb_tracking_number' => Order::generateTrackingNumber()]);

            if ($pricing['coupon'] && $pricing['coupon_discount'] > 0) {
                CouponUsage::create([
                    'coupon_id'       => $pricing['coupon']->id,
                    'user_id'         => $user->id,
                    'order_id'        => $order->id,
                    'discount_amount' => $pricing['coupon_discount'],
                ]);
                $pricing['coupon']->increment('used_count');
            }

            CartItem::where('user_id', $user->id)->delete();

            $this->notifier->orderStatusChanged($user, $order->order_number, 'paid', $order);

            // Check if referred user has now crossed the spending threshold â†’ pay referrer
            $this->checkAndPayReferralBonus($user, $order);

            session(['last_order_id' => $order->id]);
        });

        return redirect()->route('user.order.placed', session('last_order_id'));
    }

    // â”€â”€ Show â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€

    public function show(Order $order)
    {
        abort_if($order->user_id !== auth()->id(), 403);
        $order->load('items', 'statusHistory', 'serviceRequests', 'shipment', 'messages', 'rating');
        $symbol = auth()->user()->currencySymbol();
        return view('user.orders.show', compact('order', 'symbol'));
    }

    // â”€â”€ Cancel â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€

    public function cancel(Request $request, Order $order)
    {
        abort_if($order->user_id !== auth()->id(), 403);

        if (!$order->canBeCancelled()) {
            return back()->with('error', __('orders.cannot_cancel'));
        }

        $request->validate(['cancel_reason' => 'nullable|string|max:500']);

        DB::transaction(function () use ($order, $request) {
            $user = auth()->user();

            // Refund what was actually paid at checkout (no shipping was charged)
            $this->walletService->credit(
                user:        $user,
                amount:      (float) $order->grand_total,
                category:    'refund',
                description: "Refund for cancelled order #{$order->order_number}",
                meta:        ['order_id' => $order->id]
            );

            if ($order->coupon_id) {
                CouponUsage::where('order_id', $order->id)->delete();
                Coupon::where('id', $order->coupon_id)->decrement('used_count');
            }

            $order->update([
                'status'        => 'cancelled',
                'cancel_reason' => $request->cancel_reason,
            ]);

            OrderStatusHistory::create([
                'order_id'   => $order->id,
                'old_status' => $order->getOriginal('status'),
                'new_status' => 'cancelled',
                'note'       => 'Cancelled by user: ' . ($request->cancel_reason ?? ''),
                'changed_by' => $user->id,
            ]);

            $this->notifier->orderStatusChanged($user, $order->order_number, 'cancelled', $order);
        });

        return redirect()->route('user.orders.index')->with('success', __('orders.cancelled'));
    }

    // â”€â”€ Request product photos â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€

    public function requestPhoto(Request $request, Order $order)
    {
        abort_if($order->user_id !== auth()->id(), 403);

        if (!$order->canRequestPhoto()) {
            return back()->with('error', 'Photo request is not available for this order right now.');
        }

        $request->validate(['user_note' => 'nullable|string|max:300']);

        $fee = (float) Setting::get('service_fee_photo',
            config('bybhub.service_request_fees.photo', 500));

        if ($fee > 0 && !$this->walletService->hasSufficientBalance(auth()->user(), $fee)) {
            return back()->with('error',
                'Insufficient wallet balance. Photo request fee: ' .
                auth()->user()->currencySymbol() . number_format($fee, 2));
        }

        DB::transaction(function () use ($order, $request, $fee) {
            $user = auth()->user();

            if ($fee > 0) {
                $this->walletService->debit(
                    user:        $user,
                    amount:      $fee,
                    category:    'service_fee',
                    description: "Product photo request fee â€” order #{$order->order_number}",
                    meta:        ['order_id' => $order->id, 'type' => 'photo']
                );
            }

            OrderServiceRequest::create([
                'order_id'  => $order->id,
                'user_id'   => $user->id,
                'type'      => 'photo',
                'status'    => 'pending',
                'fee'       => $fee,
                'fee_paid'  => $fee > 0,
                'user_note' => $request->user_note,
            ]);
        });

        return back()->with('success', 'Photo request submitted! We will upload photos of your goods shortly.');
    }

    // â”€â”€ Request packaging â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€

    public function requestPackaging(Request $request, Order $order)
    {
        abort_if($order->user_id !== auth()->id(), 403);

        if (!$order->canRequestPackaging()) {
            return back()->with('error', 'Packaging request is not available for this order right now.');
        }

        $request->validate(['user_note' => 'nullable|string|max:500']);

        $fee = (float) Setting::get('service_fee_packaging',
            config('bybhub.service_request_fees.packaging', 300));

        if ($fee > 0 && !$this->walletService->hasSufficientBalance(auth()->user(), $fee)) {
            return back()->with('error',
                'Insufficient wallet balance. Packaging fee: ' .
                auth()->user()->currencySymbol() . number_format($fee, 2));
        }

        DB::transaction(function () use ($order, $request, $fee) {
            $user = auth()->user();

            if ($fee > 0) {
                $this->walletService->debit(
                    user:        $user,
                    amount:      $fee,
                    category:    'service_fee',
                    description: "Packaging request fee â€” order #{$order->order_number}",
                    meta:        ['order_id' => $order->id, 'type' => 'packaging']
                );
            }

            OrderServiceRequest::create([
                'order_id'  => $order->id,
                'user_id'   => $user->id,
                'type'      => 'packaging',
                'status'    => 'pending',
                'fee'       => $fee,
                'fee_paid'  => $fee > 0,
                'user_note' => $request->user_note,
            ]);
        });

        return back()->with('success', 'Packaging request submitted! Your goods will be carefully packaged.');
    }

    // â”€â”€ Pay shipping fee (individual order, not in shipment) â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€

    public function payShippingFee(Order $order)
    {
        abort_if($order->user_id !== auth()->id(), 403);

        if (!$order->hasUnpaidShippingFee()) {
            return back()->with('error', 'No shipping fee is due for this order right now.');
        }

        $fee  = (float) $order->shipping_fee_assigned;
        $user = auth()->user();

        if (!$this->walletService->hasSufficientBalance($user, $fee)) {
            return back()->with('error',
                'Insufficient wallet balance. Shipping fee: ' .
                $user->currencySymbol() . number_format($fee, 2));
        }

        DB::transaction(function () use ($order, $fee, $user) {
            $this->walletService->debit(
                user:        $user,
                amount:      $fee,
                category:    'shipping_fee',
                description: "International shipping fee â€” order #{$order->order_number}",
                meta:        ['order_id' => $order->id]
            );

            $order->update([
                'shipping_fee_status'        => 'paid',
                'international_shipping_fee' => $fee,
            ]);

            // If order was waiting for payment before shipping, move back to china_warehouse
            // If order already arrived (on_arrival timing), stay at arrived
            $newStatus = $order->status === 'awaiting_shipping_payment' ? 'china_warehouse' : $order->status;
            $note      = 'Shipping fee paid: ' . $user->currencySymbol() . number_format($fee, 2);

            if ($order->status === 'awaiting_shipping_payment') {
                $order->update(['status' => 'china_warehouse']);
            }

            OrderStatusHistory::create([
                'order_id'   => $order->id,
                'old_status' => $order->status,
                'new_status' => $newStatus,
                'note'       => $note . '. ' . ($order->shipping_fee_description ?? ''),
                'changed_by' => $user->id,
            ]);
        });

        return back()->with('success',
            'Shipping fee of ' . $user->currencySymbol() . number_format($fee, 2) .
            ' paid successfully!');
    }

    // â”€â”€ Pay shipment fee (consolidated shipment) â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€

    public function payShipmentFee(OrderShipment $shipment)
    {
        abort_if($shipment->user_id !== auth()->id(), 403);

        if (!$shipment->hasFeeAssigned() || $shipment->isFeePaid()) {
            return back()->with('error', 'No payment is due for this shipment.');
        }

        $fee  = (float) $shipment->shipping_fee;
        $user = auth()->user();

        if (!$this->walletService->hasSufficientBalance($user, $fee)) {
            return back()->with('error',
                'Insufficient wallet balance. Shipment fee: ' .
                $user->currencySymbol() . number_format($fee, 2));
        }

        DB::transaction(function () use ($shipment, $fee, $user) {
            $this->walletService->debit(
                user:        $user,
                amount:      $fee,
                category:    'shipping_fee',
                description: "Shipment fee â€” #{$shipment->shipment_number}",
                meta:        ['shipment_id' => $shipment->id]
            );

            $shipment->update([
                'status'      => 'fee_paid',
                'fee_paid_at' => now(),
            ]);

            // Update all orders in this shipment
            foreach ($shipment->orders as $order) {
                $order->update(['shipping_fee_status' => 'paid']);
                OrderStatusHistory::create([
                    'order_id'   => $order->id,
                    'old_status' => $order->status,
                    'new_status' => $order->status,
                    'note'       => "Shipment fee paid for #{$shipment->shipment_number}. Goods ready to ship.",
                    'changed_by' => $user->id,
                ]);
            }
        });

        return back()->with('success', 'Shipment fee paid. Your goods will be shipped shortly!');
    }

    // â”€â”€ Request doorstep delivery (fee set by admin) â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€

    public function requestDoorstep(Request $request, Order $order)
    {
        abort_if($order->user_id !== auth()->id(), 403);

        if (!$order->canExpressInterestInDoorstep()) {
            return back()->with('error', 'Doorstep delivery is not available for this order at this time.');
        }

        // If admin hasn't assigned fee yet, just record the interest
        if ($order->doorstep_fee_assigned === null) {
            $request->validate([
                'delivery_address' => 'required|string|max:500',
                'delivery_state'   => 'required|string|max:100',
                'delivery_city'    => 'nullable|string|max:100',
            ]);

            $order->update([
                'delivery_address' => $request->delivery_address,
                'delivery_state'   => $request->delivery_state,
                'delivery_city'    => $request->delivery_city,
                'doorstep_status'  => 'requested',
            ]);

            OrderStatusHistory::create([
                'order_id'   => $order->id,
                'old_status' => $order->status,
                'new_status' => $order->status,
                'note'       => 'Doorstep delivery requested. Awaiting admin fee assignment. Address: ' . $request->delivery_address,
                'changed_by' => auth()->id(),
            ]);

            return back()->with('success', 'Doorstep delivery requested! Admin will assign the delivery fee shortly and you\'ll be notified.');
        }

        // Fee assigned â€” validate and pay
        $request->validate([
            'delivery_address' => 'required|string|max:500',
            'delivery_state'   => 'required|string|max:100',
            'delivery_city'    => 'nullable|string|max:100',
        ]);

        $fee  = (float) $order->doorstep_fee_assigned;
        $user = auth()->user();

        if ($fee > 0 && !$this->walletService->hasSufficientBalance($user, $fee)) {
            return back()->with('error',
                'Insufficient wallet balance. Doorstep delivery fee: ' .
                $user->currencySymbol() . number_format($fee, 2) .
                ($order->doorstep_fee_description ? ' â€” ' . $order->doorstep_fee_description : ''));
        }

        DB::transaction(function () use ($order, $request, $fee, $user) {
            if ($fee > 0) {
                $this->walletService->debit(
                    user:        $user,
                    amount:      $fee,
                    category:    'doorstep_fee',
                    description: "Doorstep delivery fee â€” order #{$order->order_number}",
                    meta:        ['order_id' => $order->id]
                );
            }

            $order->update([
                'delivery_type'         => 'home_delivery',
                'delivery_address'      => $request->delivery_address,
                'delivery_state'        => $request->delivery_state,
                'delivery_city'         => $request->delivery_city,
                'doorstep_status'       => 'paid',
                'doorstep_delivery_fee' => $fee,
            ]);

            OrderStatusHistory::create([
                'order_id'   => $order->id,
                'old_status' => $order->status,
                'new_status' => $order->status,
                'note'       => 'Doorstep delivery fee paid (' . $user->currencySymbol() . number_format($fee, 2) . '). Delivery to: ' . $request->delivery_address,
                'changed_by' => $user->id,
            ]);

            $this->notifier->doorstepConfirmed($user, $order);
        });

        return back()->with('success', 'Doorstep delivery confirmed! We will deliver to your address soon.');
    }

    // â”€â”€ Re-order â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€

    public function reOrder(Order $order)
    {
        abort_if($order->user_id !== auth()->id(), 403);

        $user    = auth()->user();
        $added   = 0;
        $skipped = 0;

        foreach ($order->items as $item) {
            if (!$item->product_id) { $skipped++; continue; }

            $product = Product::find($item->product_id);
            if (!$product || $product->isOutOfStock()) { $skipped++; continue; }

            $priceCny = $product->resolvePriceCny($item->selected_variant, (int) $item->quantity);

            $existing = CartItem::where('user_id', $user->id)
                ->where('product_id', $product->id)
                ->where('selected_variant', $item->selected_variant)
                ->first();

            if ($existing) {
                $existing->increment('quantity', $item->quantity);
            } else {
                CartItem::create([
                    'user_id'          => $user->id,
                    'product_id'       => $product->id,
                    'selected_variant' => $item->selected_variant,
                    'quantity'         => $item->quantity,
                    'unit_price_cny'   => $priceCny,
                ]);
            }
            $added++;
        }

        $msg = $added . ' item' . ($added !== 1 ? 's' : '') . ' added to your cart.';
        if ($skipped > 0) {
            $msg .= ' ' . $skipped . ' item' . ($skipped !== 1 ? 's' : '') . ' could not be added (product no longer available).';
        }

        return redirect()->route('user.cart.index')->with('success', $msg);
    }

    // â”€â”€ Confirm self-pickup â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€

    public function confirmPickup(Order $order)
    {
        abort_if($order->user_id !== auth()->id(), 403);

        if (!$order->canConfirmPickup()) {
            return back()->with('error', 'Pickup cannot be confirmed for this order right now.');
        }

        $user = auth()->user();

        $order->update([
            'pickup_confirmed_at' => now(),
            'delivered_at'        => now(),
            'status'              => 'delivered',
        ]);

        OrderStatusHistory::create([
            'order_id'   => $order->id,
            'old_status' => 'arrived',
            'new_status' => 'delivered',
            'note'       => 'Customer confirmed self-pickup.',
            'changed_by' => $user->id,
        ]);

        $this->notifier->orderStatusChanged($user, $order->order_number, 'delivered', $order);

        return back()->with('success', 'Pickup confirmed! Thank you. Please rate your experience below.');
    }

    // â”€â”€ Consolidate orders into a shipment â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€

    /**
     * Check if the referred user has now crossed the spend threshold
     * and pay the referrer's bonus if so.
     * Also fires first_order promotion on the very first order.
     */
    private function checkAndPayReferralBonus($user, $order): void
    {
        // Fire first_order promotions
        $isFirstOrder = Order::where('user_id', $user->id)
            ->where('id', '!=', $order->id)
            ->where('status', '!=', 'cancelled')
            ->doesntExist();

        if ($isFirstOrder) {
            app(\App\Services\PromotionService::class)->fire('first_order', $user);
        }

        // Referral bonus check
        if (!$user->referred_by) return;

        $referral = Referral::where('referred_id', $user->id)
            ->where('bonus_paid', false)
            ->first();

        if (!$referral) return;

        $minSpend = (float) Setting::get('referral_min_order_spend',
            config('bybhub.referral.min_order_spend', 10000));

        $totalSpent = Order::where('user_id', $user->id)
            ->whereNotIn('status', ['cancelled', 'pending'])
            ->sum('grand_total');

        if ($totalSpent < $minSpend) return;

        // Threshold crossed â€” pay the referrer
        $referrer     = User::find($user->referred_by);
        $bonusAmount  = (float) $referral->bonus_amount;

        if (!$referrer || $bonusAmount <= 0) return;

        $this->walletService->credit(
            user:        $referrer,
            amount:      $bonusAmount,
            category:    'referral_bonus',
            description: "Referral bonus â€” {$user->name} has spent " .
                         $referrer->currencySymbol() . number_format($totalSpent, 2),
            meta:        ['referred_user_id' => $user->id, 'referral_id' => $referral->id]
        );

        $referral->update([
            'bonus_paid'    => true,
            'bonus_paid_at' => now(),
        ]);

        $this->notifier->send(
            $referrer, 'wallet_credit',
            'Referral Bonus Paid! ðŸŽ‰',
            'Your referral bonus of ' . $referrer->currencySymbol() . number_format($bonusAmount, 2) .
                ' has been added to your wallet. ' . $user->name . ' completed their first ' .
                $referrer->currencySymbol() . number_format($minSpend, 0) . ' in orders!',
            ['referred_user' => $user->name, 'bonus' => $bonusAmount],
            route('user.wallet.index')
        );

        // Email
        app(\App\Services\EmailService::class)->sendReferralBonus(
            $referrer,
            $user->name,
            $bonusAmount,
            $referrer->referral_code
        );
    }

    public function consolidate(Request $request)
    {
        $user = auth()->user();

        $request->validate([
            'order_ids'   => 'required|array|min:2',
            'order_ids.*' => 'required|exists:orders,id',
        ]);

        $orders = Order::whereIn('id', $request->order_ids)
            ->where('user_id', $user->id)
            ->where('status', 'china_warehouse')
            ->whereNull('shipment_id')
            ->get();

        if ($orders->count() < 2) {
            return back()->with('error', 'Please select at least 2 orders at the China warehouse (not already in a shipment).');
        }

        $combinedWeight = $orders->sum(fn($o) => (float) ($o->actual_weight_kg ?? $o->estimated_weight_kg ?? 0));

        $shipment = OrderShipment::create([
            'shipment_number'  => OrderShipment::generateShipmentNumber(),
            'user_id'          => $user->id,
            'status'           => 'pending',
            'actual_weight_kg' => $combinedWeight > 0 ? $combinedWeight : null,
        ]);

        foreach ($orders as $order) {
            $order->update(['shipment_id' => $shipment->id]);
            OrderStatusHistory::create([
                'order_id'   => $order->id,
                'old_status' => $order->status,
                'new_status' => $order->status,
                'note'       => 'Combined into shipment ' . $shipment->shipment_number .
                                '. Combined weight: ' . number_format($combinedWeight, 3) . 'kg.',
                'changed_by' => $user->id,
            ]);
        }

        return redirect()->route('user.orders.index')
            ->with('success',
                $orders->count() . ' orders combined for shipping! Reference: ' .
                $shipment->shipment_number .
                ($combinedWeight > 0 ? ' Â· Total weight: ' . number_format($combinedWeight, 3) . 'kg.' : '') .
                ' Admin will assign the shipping fee shortly.'
            );
    }

    // â”€â”€ Checkout page (GET) â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€

    public function checkoutShow(Request $request)
    {
        $user  = auth()->user();
        $items = CartItem::where('user_id', $user->id)->with('product')->get();

        if ($items->isEmpty()) {
            return redirect()->route('user.cart.index')->with('error', 'Your cart is empty.');
        }

        $currency = $user->currency;
        $symbol   = $user->currencySymbol();

        $totalWeightKg = $items->sum(fn($i) => ($i->product?->getBillableWeight() ?? 0.5) * $i->quantity);
        $isFirstOrder  = Order::where('user_id', $user->id)->doesntExist();

        $pricing = $this->pricingService->calculateOrderTotal(
            cartItems:         $items->all(),
            currency:          $currency,
            country:           $user->country,
            shippingMethod:    'sea_los',
            estimatedWeightKg: $totalWeightKg,
            isHomeDelivery:    false,
            deliveryState:     '',
            couponCode:        null,
            isFirstOrder:      $isFirstOrder
        );

        $totalCny        = $pricing['items_total_cny'];
        $totalLocal      = $pricing['items_total_local'];
        $domFreightLocal = $pricing['domestic_freight_local'] ?? 0;
        $purchaseFee     = $pricing['purchase_fee'];
        $rate            = $pricing['exchange_rate_used'];

        // BNPL provider settings (from admin Settings DB)
        $bnpl = [
            'klump' => [
                'enabled'    => \App\Models\Setting::get('bnpl_klump_enabled', '0') === '1',
                'public_key' => \App\Models\Setting::get('bnpl_klump_public_key', ''),
                'env'        => \App\Models\Setting::get('bnpl_klump_env', 'test'),
                'min_amount' => (float) \App\Models\Setting::get('bnpl_klump_min_amount', 5000),
            ],
            'credpal' => [
                'enabled'    => \App\Models\Setting::get('bnpl_credpal_enabled', '0') === '1',
                'public_key' => \App\Models\Setting::get('bnpl_credpal_public_key', ''),
                'env'        => \App\Models\Setting::get('bnpl_credpal_env', 'test'),
                'min_amount' => (float) \App\Models\Setting::get('bnpl_credpal_min_amount', 5000),
            ],
            'zilla' => [
                'enabled'    => \App\Models\Setting::get('bnpl_zilla_enabled', '0') === '1',
                'public_key' => \App\Models\Setting::get('bnpl_zilla_public_key', ''),
                'env'        => \App\Models\Setting::get('bnpl_zilla_env', 'test'),
                'min_amount' => (float) \App\Models\Setting::get('bnpl_zilla_min_amount', 5000),
            ],
        ];

        return view('user.checkout', compact(
            'items', 'totalCny', 'totalLocal', 'domFreightLocal',
            'purchaseFee', 'totalWeightKg', 'symbol', 'rate', 'user', 'pricing', 'bnpl'
        ));
    }

    // â”€â”€ Klump BNPL complete â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€

    // ── Klump BNPL complete ────────────────────────────────────────────────

    public function klumpComplete(Request $request)
    {
        $request->validate(['klump_reference' => 'required|string']);
        $this->processBnpl(
            $request,
            fn($user) => back()->with('error', 'Cart is empty.'),
            'KLUMP:' . $request->klump_reference,
            'Order placed via Klump BNPL. Reference: ' . $request->klump_reference
        );
        return redirect()->route('user.order.placed', session('last_order_id'));
    }

    // ── Credpal BNPL complete ──────────────────────────────────────────────

    public function credpalComplete(Request $request)
    {
        $request->validate(['credpal_reference' => 'required|string']);
        $this->processBnpl(
            $request,
            fn($user) => back()->with('error', 'Cart is empty.'),
            'CREDPAL:' . $request->credpal_reference,
            'Order placed via Credpal BNPL. Reference: ' . $request->credpal_reference
        );
        return redirect()->route('user.order.placed', session('last_order_id'));
    }

    // ── ZiLLA BNPL complete ────────────────────────────────────────────────

    public function zillaComplete(Request $request)
    {
        $request->validate(['zilla_reference' => 'required|string']);
        $this->processBnpl(
            $request,
            fn($user) => back()->with('error', 'Cart is empty.'),
            'ZILLA:' . $request->zilla_reference,
            'Order placed via ZiLLA BNPL. Reference: ' . $request->zilla_reference
        );
        return redirect()->route('user.order.placed', session('last_order_id'));
    }

    // ── Shared BNPL processing ─────────────────────────────────────────────

    private function processBnpl(Request $request, $emptyCallback, string $buyerNote, string $historyNote)
    {
        $user  = auth()->user();
        $items = CartItem::where('user_id', $user->id)->with('product')->get();

        if ($items->isEmpty()) {
            return back()->with('error', 'Cart is empty.');
        }

        foreach ($items as $item) {
            if ($item->isOutOfStock()) {
                return back()->with('error', __('cart.item_out_of_stock', ['title' => $item->getTitle()]));
            }
        }

        $totalWeightKg = $items->sum(fn($i) => ($i->product?->getBillableWeight() ?? 0.5) * $i->quantity);
        $isFirstOrder  = Order::where('user_id', $user->id)->doesntExist();

        $pricing = $this->pricingService->calculateOrderTotal(
            cartItems:         $items->all(),
            currency:          $user->currency,
            country:           $user->country,
            shippingMethod:    'sea_los',
            estimatedWeightKg: $totalWeightKg,
            isHomeDelivery:    false,
            deliveryState:     '',
            couponCode:        null,
            isFirstOrder:      $isFirstOrder
        );

        DB::transaction(function () use ($user, $items, $pricing, $totalWeightKg, $buyerNote, $historyNote) {
            $order = Order::create([
                'order_number'               => Order::generateOrderNumber(),
                'user_id'                    => $user->id,
                'country'                    => $user->country,
                'currency'                   => $user->currency,
                'exchange_rate_used'         => $pricing['exchange_rate_used'],
                'items_total_cny'            => $pricing['items_total_cny'],
                'items_total_local'          => $pricing['items_total_local'],
                'purchase_fee'               => $pricing['purchase_fee'],
                'international_shipping_fee' => 0,
                'local_freight_fee'          => $pricing['domestic_freight_local'] ?? 0,
                'discount_amount'            => $pricing['discount_amount'],
                'coupon_discount'            => $pricing['coupon_discount'],
                'grand_total'                => $pricing['grand_total'],
                'coupon_id'                  => $pricing['coupon']?->id,
                'coupon_code'                => $pricing['coupon']?->code,
                'shipping_method'            => 'sea_los',
                'estimated_weight_kg'        => $totalWeightKg,
                'shipping_fee_status'        => 'unpaid',
                'delivery_type'              => 'pickup',
                'doorstep_status'            => 'none',
                'status'                     => 'paid',
                'paid_at'                    => now(),
                'buyer_note'                 => $buyerNote,
            ]);

            foreach ($items as $item) {
                $unitLocal = round($item->unit_price_cny * $pricing['exchange_rate_used'], 2);
                OrderItem::create([
                    'order_id'          => $order->id,
                    'product_id'        => $item->product_id,
                    'product_snapshot'  => [
                        'title'      => $item->getTitle(),
                        'images'     => $item->product?->images ?? ($item->scraped_data['images'] ?? []),
                        'source_url' => $item->product?->source_url,
                    ],
                    'selected_variant'  => $item->selected_variant,
                    'quantity'          => $item->quantity,
                    'unit_price_cny'    => $item->unit_price_cny,
                    'unit_price_local'  => $unitLocal,
                    'total_price_local' => round($unitLocal * $item->quantity, 2),
                ]);
                if ($item->product_id) {
                    Product::where('id', $item->product_id)->increment('order_count', $item->quantity);
                }
            }

            OrderStatusHistory::create([
                'order_id'   => $order->id,
                'old_status' => null,
                'new_status' => 'paid',
                'note'       => $historyNote,
            ]);

            $order->update(['byb_tracking_number' => Order::generateTrackingNumber()]);
            CartItem::where('user_id', $user->id)->delete();
            $this->notifier->orderStatusChanged($user, $order->order_number, 'paid', $order);
            $this->checkAndPayReferralBonus($user, $order);
            session(['last_order_id' => $order->id]);
        });
    }
}