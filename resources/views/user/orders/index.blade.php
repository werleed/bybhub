@extends('layouts.app')
@section('title', __('app.orders'))
@section('content')
<div class="space-y-5">
    <div class="flex items-center justify-between">
        <h2 class="text-xl font-bold text-primary flex items-center gap-2" style="font-family:Poppins,sans-serif">
            <i class="fas fa-box text-base"></i> My Orders
        </h2>
        </h2>
        <a href="{{ route('products.index') }}" class="text-sm text-accent hover:underline font-semibold flex items-center gap-1">
            <i class="fas fa-plus text-xs"></i> New Order
        </a>
    </div>

    {{-- ── Combine Orders Banner ─────────────────────────────────────────── --}}
    @if($warehouseOrders->count() >= 2)
    <div class="bg-gradient-to-r from-indigo-50 to-purple-50 border-2 border-indigo-200 rounded-2xl p-5"
         x-data="{ open: false, selected: [] }">
        <div class="flex items-start gap-4">
            <div class="w-12 h-12 bg-indigo-100 rounded-xl flex items-center justify-center flex-shrink-0">
                <i class="fas fa-cubes text-indigo-600 text-lg"></i>
            </div>
            <div class="flex-1">
                <h3 class="font-bold text-indigo-800">Combine Orders for Cheaper Shipping</h3>
                <p class="text-sm text-indigo-700 mt-0.5">
                    You have <strong>{{ $warehouseOrders->count() }}</strong> orders at our China warehouse.
                    Shipping them together saves money — one combined weight instead of separate fees.
                </p>
                @php
                    $totalCombinedKg = $warehouseOrders->sum(fn($o) => (float)($o->actual_weight_kg ?? $o->estimated_weight_kg ?? 0));
                @endphp
                @if($totalCombinedKg > 0)
                <p class="text-xs text-indigo-600 mt-1">Combined weight if all selected: <strong>{{ number_format($totalCombinedKg, 3) }}kg</strong></p>
                @endif
                <button type="button" @click="open=!open"
                    class="mt-3 inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold px-5 py-2.5 rounded-xl text-sm transition">
                    <i class="fas fa-cubes"></i>
                    <span x-text="open ? 'Cancel' : 'Combine Orders'"></span>
                </button>
            </div>
        </div>

        <div x-show="open" x-transition class="mt-5 border-t border-indigo-200 pt-5 space-y-5">
            {{-- Combine shipping --}}
            <div>
                <p class="text-xs font-bold text-indigo-700 uppercase tracking-wider mb-3 flex items-center gap-2">
                    <i class="fas fa-boxes text-indigo-400"></i> Combine into one shipment (min. 2 orders)
                </p>
                <form action="{{ route('user.orders.consolidate') }}" method="POST">
                    @csrf
                    <div class="space-y-2 mb-4">
                        @foreach($warehouseOrders as $wo)
                        <label class="flex items-center gap-3 bg-white rounded-xl border-2 border-gray-100 hover:border-indigo-300 transition p-3 cursor-pointer"
                               :class="selected.includes({{ $wo->id }}) ? 'border-indigo-400 bg-indigo-50' : ''">
                            <input type="checkbox" name="order_ids[]" value="{{ $wo->id }}"
                                @change="selected.includes({{ $wo->id }}) ? selected.splice(selected.indexOf({{ $wo->id }}),1) : selected.push({{ $wo->id }})"
                                class="w-4 h-4 rounded accent-indigo-600">
                            <div class="flex-1 min-w-0">
                                <p class="font-bold text-sm text-gray-800">#{{ $wo->order_number }}</p>
                                <p class="text-xs text-gray-500 truncate">{{ $wo->items->count() }} item{{ $wo->items->count() !== 1 ? 's' : '' }}
                                    @if($wo->actual_weight_kg) · <strong>{{ $wo->actual_weight_kg }}kg</strong> (weighed)
                                    @elseif($wo->estimated_weight_kg) · ~{{ $wo->estimated_weight_kg }}kg (est.)
                                    @endif
                                    @if($wo->shipment_id) · <span class="text-indigo-500 font-semibold font-mono">{{ $wo->shipment->shipment_number }}</span>@endif
                                </p>
                            </div>
                            <span class="text-xs font-bold text-indigo-600 font-mono">{{ $symbol }}{{ number_format((float)$wo->grand_total,0) }}</span>
                        </label>
                        @endforeach
                    </div>
                    <div class="flex items-center gap-3 flex-wrap">
                        <button type="submit"
                            :disabled="selected.length < 2"
                            :class="selected.length < 2 ? 'opacity-50 cursor-not-allowed' : 'hover:bg-indigo-700'"
                            class="bg-indigo-600 text-white font-bold px-5 py-2.5 rounded-xl text-sm transition flex items-center gap-2">
                            <i class="fas fa-link"></i>
                            Combine <span x-text="selected.length"></span> Orders
                        </button>
                        <p class="text-xs text-gray-400">One combined shipping fee will be calculated.</p>
                    </div>
                </form>
            </div>

            {{-- Request packaging for ALL warehouse orders --}}
            @php
                $packableOrders = $warehouseOrders->filter(fn($o) => $o->canRequestPackaging());
            @endphp
            @if($packableOrders->isNotEmpty())
            <div class="border-t border-indigo-100 pt-4"
                 x-data="{ packOpen: false, packNote: '' }">
                <p class="text-xs font-bold text-purple-700 uppercase tracking-wider mb-2 flex items-center gap-2">
                    <i class="fas fa-box text-purple-400"></i> Request Special Packaging for All Warehouse Orders
                </p>
                <p class="text-xs text-gray-500 mb-3">
                    Request extra packaging for all {{ $packableOrders->count() }} orders currently in our China warehouse — fragile protection, bubble wrap, reinforced boxes.
                </p>
                <button type="button" @click="packOpen=!packOpen"
                    class="inline-flex items-center gap-2 bg-purple-600 hover:bg-purple-700 text-white font-bold px-5 py-2.5 rounded-xl text-sm transition">
                    <i class="fas fa-box text-xs"></i>
                    <span x-text="packOpen ? 'Cancel' : 'Request Packaging for All (' + {{ $packableOrders->count() }} + ' orders)'"></span>
                </button>
                <div x-show="packOpen" x-transition class="mt-3 bg-purple-50 border border-purple-200 rounded-xl p-4 space-y-3">
                    <p class="text-xs font-semibold text-purple-700">Orders that will be packaged:</p>
                    <div class="space-y-1">
                        @foreach($packableOrders as $po)
                        <p class="text-xs text-gray-700 flex items-center gap-2">
                            <i class="fas fa-check-circle text-purple-400 text-[10px]"></i>
                            <strong>#{{ $po->order_number }}</strong>
                            <span class="text-gray-400">· {{ $po->items->count() }} item{{ $po->items->count() > 1 ? 's' : '' }}</span>
                        </p>
                        @endforeach
                    </div>
                    @foreach($packableOrders as $po)
                    <form action="{{ route('user.orders.packaging', $po->id) }}" method="POST" class="hidden bulk-pack-form">
                        @csrf
                        <input type="hidden" name="user_note" x-bind:value="packNote">
                    </form>
                    @endforeach
                    <textarea x-model="packNote" rows="2"
                        placeholder="Any special instructions? e.g. bubble wrap for all fragile items, extra reinforcement..."
                        class="w-full border border-purple-200 rounded-xl px-3 py-2 text-xs focus:outline-none focus:border-purple-400 bg-white resize-none"></textarea>
                    <button type="button"
                        @click="
                            document.querySelectorAll('.bulk-pack-form').forEach(f => {
                                f.querySelector('input[name=user_note]').value = packNote;
                                f.submit();
                            });
                        "
                        class="w-full bg-purple-600 hover:bg-purple-700 text-white font-bold py-2.5 rounded-xl text-sm transition flex items-center justify-center gap-2">
                        <i class="fas fa-box"></i> Confirm — Package All {{ $packableOrders->count() }} Orders
                    </button>
                    <p class="text-[10px] text-gray-400 text-center">Our team will package each order and notify you when done.</p>
                </div>
            </div>
            @endif
        </div>
    </div>
    @elseif($warehouseOrders->count() === 1)
    <div class="bg-purple-50 border border-purple-200 rounded-2xl p-4 flex items-center gap-3">
        <i class="fas fa-warehouse text-purple-400 text-lg"></i>
        <div>
            <p class="font-semibold text-purple-800 text-sm">1 order at China warehouse</p>
            <p class="text-xs text-purple-600">Place another order to unlock combined shipping and save on fees.</p>
        </div>
    </div>
    @endif

    @if($orders->isEmpty())
    <div class="bg-white rounded-3xl shadow-card p-16 text-center text-gray-400">
        <div class="w-20 h-20 bg-gray-100 rounded-3xl flex items-center justify-center mx-auto mb-4">
            <i class="fas fa-box-open text-3xl text-gray-300"></i>
        </div>
        <p class="font-bold text-gray-600 text-base">{{ __('orders.no_orders') }}</p>
        <p class="text-sm mt-1 mb-5">Start by browsing our catalogue or paste a product URL</p>
        <a href="{{ route('products.index') }}" class="inline-flex items-center gap-2 bg-accent text-white font-bold px-6 py-3 rounded-2xl text-sm btn-glow transition">
            <i class="fas fa-store"></i> Start Shopping
        </a>
    </div>
    @else
    <div class="space-y-3">
        @foreach($orders as $order)
        <div class="bg-white rounded-2xl shadow-card p-5 hover:shadow-card-hover transition-shadow">
            <div class="flex flex-wrap justify-between items-start gap-3">
                <div>
                    <a href="{{ route('user.orders.show', $order) }}" class="font-black text-primary hover:underline text-lg" style="font-family:Poppins,sans-serif">
                        #{{ $order->order_number }}
                    </a>
                    <p class="text-xs text-gray-400 mt-0.5">{{ $order->created_at->format('M d, Y · h:i A') }}</p>
                    <p class="text-xs text-gray-500 mt-1">{{ $order->items->count() }} item{{ $order->items->count() > 1 ? 's' : '' }} · {{ config("bybhub.shipping_methods.{$order->shipping_method}", $order->shipping_method) }}
                    @if($order->shipment_id)
                    · <span class="text-indigo-600 font-semibold font-mono">📦 {{ $order->shipment->shipment_number }}</span>
                    @endif
                    @if($order->actual_weight_kg)
                    · <span class="font-semibold">{{ $order->actual_weight_kg }}kg</span>
                    @endif
                </p>
                <div class="text-right">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-bold bg-{{ $order->getStatusColor() }}-100 text-{{ $order->getStatusColor() }}-700">
                        <span class="w-1.5 h-1.5 rounded-full bg-{{ $order->getStatusColor() }}-500"></span>
                        {{ $order->getStatusLabel() }}
                    </span>
                    <p class="font-black text-xl text-gray-800 mt-1.5" style="font-family:Poppins,sans-serif">{{ $symbol }}{{ number_format($order->grand_total,2) }}</p>
                    @if($order->tracking_number)
                    <p class="text-xs text-gray-400 mt-0.5 font-mono">{{ $order->tracking_number }}</p>
                    @endif
                </div>
            </div>
            <div class="flex gap-2 mt-4 flex-wrap">
                <a href="{{ route('user.orders.show', $order) }}"
                    class="inline-flex items-center gap-1.5 text-xs bg-primary text-white font-bold px-4 py-2 rounded-xl hover:bg-primary-dark transition">
                    <i class="fas fa-eye text-[10px]"></i> View Details
                </a>
                {{-- Unread message badge --}}
                @if($order->unreadSupportMessages() > 0)
                <a href="{{ route('user.orders.show', $order) }}#messages"
                    class="inline-flex items-center gap-1.5 text-xs bg-accent text-white font-bold px-4 py-2 rounded-xl hover:bg-accent-dark transition">
                    <i class="fas fa-comment-dots text-[10px]"></i> {{ $order->unreadSupportMessages() }} new message{{ $order->unreadSupportMessages() > 1 ? 's' : '' }}
                </a>
                @endif
                {{-- Pay shipping fee shortcut --}}
                @if($order->hasUnpaidShippingFee())
                <a href="{{ route('user.orders.show', $order) }}"
                    class="inline-flex items-center gap-1.5 text-xs bg-orange-500 text-white font-bold px-4 py-2 rounded-xl hover:bg-orange-600 transition">
                    <i class="fas fa-credit-card text-[10px]"></i> Pay Shipping Fee
                </a>
                @endif
                {{-- Re-order --}}
                @if(in_array($order->status, ['delivered','cancelled']))
                <form action="{{ route('user.orders.reorder',$order->id) }}" method="POST">
                    @csrf
                    <button class="inline-flex items-center gap-1.5 text-xs bg-gray-100 text-gray-700 font-semibold px-4 py-2 rounded-xl hover:bg-gray-200 transition">
                        <i class="fas fa-redo-alt text-[10px]"></i> Re-order
                    </button>
                </form>
                @endif
                @if($order->canBeCancelled())
                <form action="{{ route('user.orders.cancel',$order->id) }}" method="POST"
                      data-confirm
                      data-confirm-type="danger"
                      data-confirm-title="Cancel Order"
                      data-confirm-msg="Cancel order #{{ $order->order_number }}? Your payment will be refunded to your wallet."
                      data-confirm-ok="Yes, Cancel"
                      data-confirm-cancel="Keep Order">
                    @csrf
                    <button class="inline-flex items-center gap-1.5 text-xs bg-red-50 text-red-600 font-semibold px-4 py-2 rounded-xl hover:bg-red-100 transition border border-red-100">
                        <i class="fas fa-times text-[10px]"></i> Cancel Order
                    </button>
                </form>
                @endif
            </div>
        </div>
        @endforeach
    </div>
    <div class="mt-4">{{ $orders->links() }}</div>
    @endif
</div>
@endsection
