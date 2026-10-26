@extends('layouts.app')
@section('title','Order #'.$order->order_number)
@section('content')
@php
    $sym      = $symbol;
    $pipeline = \App\Models\Order::statusPipeline();
    $step     = $order->pipelineStep();
    $cfg      = config('bybhub.order_statuses');
    $whatsapp = \App\Models\Setting::get('contact_whatsapp','');
    $isLocal  = $order->order_type === 'local';
    $localCfg = config('bybhub.local_order_statuses');
@endphp
<div class="space-y-5">

{{-- ── Header ─────────────────────────────────────────────────────────── --}}
<div class="flex flex-wrap items-start justify-between gap-3">
    <div>
        <div class="flex items-center gap-3 flex-wrap">
            <h2 class="text-xl font-black text-primary" style="font-family:Poppins,sans-serif">
                Order #{{ $order->order_number }}
            </h2>
            @if($isLocal)
            {{-- Local order: show local status --}}
            @php $lc = $localCfg[$order->local_order_status] ?? []; @endphp
            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-bold
                bg-{{ $lc['color']??'gray' }}-100 text-{{ $lc['color']??'gray' }}-700">
                <span class="w-1.5 h-1.5 rounded-full bg-{{ $lc['color']??'gray' }}-500 animate-pulse"></span>
                <i class="fas {{ $lc['icon']??'fa-circle' }} text-[9px]"></i>
                {{ $lc['label'] ?? ucfirst($order->local_order_status) }}
            </span>
            <span class="text-[10px] bg-orange-100 text-orange-600 font-bold px-2 py-0.5 rounded-full flex items-center gap-1">
                <i class="fas fa-store text-[9px]"></i> Local Vendor Order
            </span>
            @else
            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-bold
                bg-{{ $order->getStatusColor() }}-100 text-{{ $order->getStatusColor() }}-700">
                <span class="w-1.5 h-1.5 rounded-full bg-{{ $order->getStatusColor() }}-500 animate-pulse"></span>
                {{ $order->getStatusLabel() }}
            </span>
            @endif
        </div>
        <p class="text-xs text-gray-400 mt-1">Placed {{ $order->created_at->format('M d, Y · h:i A') }}</p>
        @if($order->byb_tracking_number && !$isLocal)
        <p class="text-xs text-gray-500 mt-1 font-mono">
            Tracking: <span class="font-bold text-primary">{{ $order->byb_tracking_number }}</span>
            <a href="{{ route('tracking.index') }}?t={{ $order->byb_tracking_number }}"
               class="ml-2 text-accent hover:underline text-[11px]">Track publicly ↗</a>
        </p>
        @endif
    </div>
    <div class="flex items-center gap-2 flex-col sm:flex-row items-start flex-wrap">
        @if(!$isLocal)
        <form action="{{ route('user.orders.reorder', $order->id) }}" method="POST">
            @csrf
            <button class="inline-flex items-center gap-1.5 text-xs font-bold text-primary border border-primary/30 hover:bg-primary hover:text-white px-3 py-2 rounded-xl transition">
                <i class="fas fa-redo-alt text-[10px]"></i> Re-order
            </button>
        </form>
        @endif
        <a href="{{ route('user.orders.index') }}" class="text-sm text-gray-500 hover:text-primary transition font-semibold flex items-center gap-1">
            <i class="fas fa-arrow-left text-xs"></i> My Orders
        </a>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════════════════ --}}
{{-- LOCAL ORDER SECTION — completely different from China import orders   --}}
{{-- ══════════════════════════════════════════════════════════════════════ --}}
@if($isLocal)

    {{-- ── Local status pipeline ─────────────────────────────────────── --}}
    @php
        $localPipeline = ['paid','confirmed','preparing','ready_for_pickup','out_for_delivery','delivered','completed'];
        $localStep     = array_search($order->local_order_status, $localPipeline) ?? 0;
    @endphp
    <div class="bg-white rounded-2xl shadow-card p-5 overflow-x-auto">
        <h3 class="font-bold text-primary text-sm mb-5 flex items-center gap-2">
            <i class="fas fa-route text-accent text-xs"></i> Order Progress
        </h3>
        <div class="flex items-center min-w-max">
            @foreach($localPipeline as $i => $s)
            @php
                $sc    = $localCfg[$s] ?? ['label'=>ucfirst($s),'icon'=>'fa-circle','color'=>'gray'];
                $done  = $i <= $localStep;
                $active= $i === $localStep;
                $color = $active ? 'primary' : ($done ? 'green' : 'gray');
            @endphp
            <div class="flex flex-col items-center gap-1.5 w-[88px]">
                <div class="w-9 h-9 rounded-full flex items-center justify-center border-2 transition-all
                    {{ $active ? 'bg-accent border-accent scale-110 shadow-md'
                               : ($done ? 'bg-green-500 border-green-500' : 'bg-white border-gray-200') }}">
                    <i class="fas {{ $sc['icon'] }} text-xs {{ ($done || $active) ? 'text-white' : 'text-gray-300' }}"></i>
                </div>
                <p class="text-[10px] text-center leading-tight {{ $active ? 'font-black text-accent' : ($done ? 'font-bold text-green-600' : 'text-gray-400') }}">
                    {{ $sc['label'] }}
                </p>
            </div>
            @if(!$loop->last)
            <div class="h-0.5 w-5 flex-shrink-0 mb-4 {{ $i < $localStep ? 'bg-green-400' : 'bg-gray-200' }}"></div>
            @endif
            @endforeach
        </div>
        @if($order->vendor_delivery_note)
        <div class="mt-4 bg-blue-50 border border-blue-200 rounded-xl px-3 py-2 text-xs text-blue-700 flex items-center gap-2">
            <i class="fas fa-comment text-blue-400 flex-shrink-0"></i>
            <span><strong>Vendor note:</strong> {{ $order->vendor_delivery_note }}</span>
        </div>
        @endif
    </div>

    {{-- ── Vendor info card ───────────────────────────────────────────── --}}
    @if($order->vendor)
    <div class="bg-white rounded-2xl shadow-card p-5 flex items-center gap-4">
        @if($order->vendor->store_logo)
        <img src="{{ Storage::url($order->vendor->store_logo) }}" class="w-12 h-12 rounded-xl object-cover flex-shrink-0">
        @else
        <div class="w-12 h-12 rounded-xl bg-accent/20 flex items-center justify-center flex-shrink-0">
            <span class="font-black text-accent text-lg">{{ strtoupper(substr($order->vendor->store_name,0,1)) }}</span>
        </div>
        @endif
        <div class="flex-1 min-w-0">
            <p class="font-bold text-gray-800">{{ $order->vendor->store_name }}</p>
            <p class="text-xs text-gray-500">{{ $order->vendor->business_city }}, Nigeria</p>
            @if($order->vendor->business_phone)
            <a href="tel:{{ $order->vendor->business_phone }}" class="text-xs text-primary hover:underline flex items-center gap-1 mt-0.5">
                <i class="fas fa-phone text-[10px]"></i> {{ $order->vendor->business_phone }}
            </a>
            @endif
        </div>
        <a href="{{ route('store.show', $order->vendor->store_slug) }}" class="text-xs text-accent font-bold hover:underline flex-shrink-0">
            Visit Store →
        </a>
    </div>
    @endif

    {{-- ── Escrow info ────────────────────────────────────────────────── --}}
    <div class="bg-blue-50 border border-blue-200 rounded-2xl p-4 flex items-start gap-3">
        <div class="w-9 h-9 bg-blue-100 rounded-xl flex items-center justify-center flex-shrink-0">
            <i class="fas fa-shield-alt text-blue-500 text-sm"></i>
        </div>
        <div>
            <p class="font-bold text-blue-800 text-sm">Your payment is protected by escrow</p>
            <p class="text-xs text-blue-600 mt-0.5 leading-relaxed">
                ₦{{ number_format($order->grand_total,2) }} is safely held by BYBHUB.
                @if(in_array($order->local_order_status, ['paid','confirmed','preparing','ready_for_pickup']))
                It will only be released to the vendor <strong>after you confirm delivery</strong>.
                @elseif($order->local_order_status === 'out_for_delivery')
                Your order is on the way. Please confirm delivery once received.
                @elseif($order->local_order_status === 'delivered')
                Payment will be released automatically in 72 hours. Confirm delivery now or raise a dispute if there's an issue.
                @elseif($order->local_order_status === 'completed')
                Payment has been released to the vendor. Order completed.
                @endif
            </p>
        </div>
    </div>

    {{-- ── BYBHUB delivery fee payment ─────────────────────────────────── --}}
    @if($order->delivery_handled_by === 'bybhub' && $order->local_delivery_fee_assigned > 0)
    <div class="rounded-2xl p-5 border-2 {{ $order->local_delivery_fee_status === 'paid' ? 'bg-green-50 border-green-300' : 'bg-orange-50 border-orange-400' }}">
        <div class="flex items-start gap-4">
            <div class="w-11 h-11 rounded-xl flex items-center justify-center flex-shrink-0 {{ $order->local_delivery_fee_status === 'paid' ? 'bg-green-100' : 'bg-orange-100' }}">
                <i class="fas {{ $order->local_delivery_fee_status === 'paid' ? 'fa-check-circle text-green-500' : 'fa-truck text-orange-500' }} text-lg"></i>
            </div>
            <div class="flex-1">
                @if($order->local_delivery_fee_status === 'paid')
                <p class="font-bold text-green-800 text-sm">BYBHUB Delivery Fee Paid ✅</p>
                <p class="text-xs text-green-600 mt-0.5">₦{{ number_format((float)$order->local_delivery_fee_assigned,2) }} delivery fee paid. Your order will be dispatched shortly.</p>
                @else
                <p class="font-bold text-orange-800 text-sm">⚠️ Delivery Fee Required</p>
                <p class="text-sm text-orange-700 mt-1">BYBHUB is handling delivery for your order. Please pay the delivery fee to proceed.</p>
                <p class="text-2xl font-black text-orange-700 mt-2" style="font-family:Poppins,sans-serif">₦{{ number_format((float)$order->local_delivery_fee_assigned,2) }}</p>
                @php $buyerBal = auth()->user()->getWalletBalance(); @endphp
                <p class="text-xs text-gray-500 mt-1">Your wallet: ₦{{ number_format($buyerBal,2) }}</p>
                @if($buyerBal >= $order->local_delivery_fee_assigned)
                <form action="{{ route('user.orders.pay-local-delivery', $order->id) }}" method="POST" class="mt-3"
                      data-confirm data-confirm-title="Pay Delivery Fee"
                      data-confirm-msg="Pay ₦{{ number_format((float)$order->local_delivery_fee_assigned,2) }} delivery fee?"
                      data-confirm-ok="Pay Now">
                    @csrf
                    <button class="inline-flex items-center gap-2 bg-orange-500 hover:bg-orange-600 text-white font-bold px-6 py-2.5 rounded-xl text-sm transition">
                        <i class="fas fa-wallet"></i> Pay Delivery Fee Now
                    </button>
                </form>
                @else
                <a href="{{ route('user.wallet.fund') }}" class="inline-flex items-center gap-2 mt-3 bg-red-500 hover:bg-red-600 text-white font-bold px-5 py-2.5 rounded-xl text-sm transition">
                    <i class="fas fa-plus"></i> Fund Wallet First
                </a>
                @endif
                @endif
            </div>
        </div>
    </div>
    @endif

    {{-- ── Buyer action buttons ───────────────────────────────────────── --}}
    @if($order->local_order_status === 'out_for_delivery')
    <div class="bg-white rounded-2xl shadow-card p-5 space-y-3">
        <h3 class="font-bold text-primary text-sm flex items-center gap-2">
            <i class="fas fa-hand-pointer text-accent text-xs"></i> Your Action Required
        </h3>
        <p class="text-sm text-gray-600">Have you received your order? Please confirm or raise a dispute.</p>
        <div class="grid grid-cols-2 gap-3">
            <form action="{{ route('user.orders.confirm-delivery', $order->id) }}" method="POST"
                  data-confirm data-confirm-title="Confirm Delivery"
                  data-confirm-msg="Confirm you received order #{{ $order->order_number }}? This releases payment to the vendor."
                  data-confirm-ok="Yes, I received it">
                @csrf
                <button class="w-full bg-green-500 hover:bg-green-600 text-white font-bold py-3.5 rounded-2xl text-sm transition flex items-center justify-center gap-2 shadow-md">
                    <i class="fas fa-check-circle"></i> I Received My Order ✅
                </button>
            </form>
            <button type="button" onclick="document.getElementById('disputeForm').classList.toggle('hidden')"
                class="w-full bg-red-50 hover:bg-red-100 text-red-600 font-bold py-3.5 rounded-2xl text-sm transition border border-red-200 flex items-center justify-center gap-2">
                <i class="fas fa-exclamation-triangle"></i> Raise a Dispute ⚠️
            </button>
        </div>
        <form id="disputeForm" action="{{ route('user.orders.dispute', $order->id) }}" method="POST" class="hidden space-y-2 mt-2">
            @csrf
            <textarea name="reason" required rows="2" placeholder="Describe the issue (e.g. item not received, wrong item, damaged)..."
                class="w-full border-2 border-red-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:border-red-400 resize-none"></textarea>
            <button class="w-full bg-red-500 hover:bg-red-600 text-white font-bold py-2.5 rounded-xl text-sm transition">
                Submit Dispute
            </button>
            <p class="text-[10px] text-gray-400">Our team will investigate within 24 hours and contact both parties.</p>
        </form>
    </div>
    @elseif($order->local_order_status === 'delivered')
    <div class="bg-white rounded-2xl shadow-card p-5 space-y-3">
        <h3 class="font-bold text-primary text-sm flex items-center gap-2">
            <i class="fas fa-box-open text-accent text-xs"></i> Confirm You Received Your Order
        </h3>
        <p class="text-xs text-gray-500">Payment auto-releases in 72 hours if not confirmed. Confirm now to complete the order.</p>
        <div class="grid grid-cols-2 gap-3">
            <form action="{{ route('user.orders.confirm-delivery', $order->id) }}" method="POST"
                  data-confirm data-confirm-title="Confirm Delivery"
                  data-confirm-msg="Confirm receipt of order #{{ $order->order_number }}? This releases payment to the vendor and closes the order."
                  data-confirm-ok="Yes, Confirm">
                @csrf
                <button class="w-full bg-green-500 hover:bg-green-600 text-white font-bold py-3 rounded-2xl text-sm transition flex items-center justify-center gap-2">
                    <i class="fas fa-check-circle"></i> Confirm Receipt
                </button>
            </form>
            <button type="button" onclick="document.getElementById('disputeForm2').classList.toggle('hidden')"
                class="w-full bg-red-50 hover:bg-red-100 text-red-600 font-bold py-3 rounded-2xl text-sm border border-red-200 transition flex items-center justify-center gap-2">
                <i class="fas fa-exclamation-triangle"></i> Raise Dispute
            </button>
        </div>
        <form id="disputeForm2" action="{{ route('user.orders.dispute', $order->id) }}" method="POST" class="hidden space-y-2">
            @csrf
            <textarea name="reason" required rows="2" placeholder="What went wrong?"
                class="w-full border-2 border-red-200 rounded-xl px-3 py-2 text-sm focus:outline-none resize-none"></textarea>
            <button class="w-full bg-red-500 text-white font-bold py-2.5 rounded-xl text-sm transition">Submit Dispute</button>
        </form>
    </div>
    @elseif($order->local_order_status === 'completed')
    <div class="bg-green-50 border border-green-200 rounded-2xl p-5 flex items-center gap-3">
        <i class="fas fa-check-circle text-green-500 text-2xl flex-shrink-0"></i>
        <div>
            <p class="font-bold text-green-800">Order Completed 🎉</p>
            <p class="text-xs text-green-600 mt-0.5">Payment released to the vendor. Thank you for shopping with BYBHUB!</p>
        </div>
    </div>
    @elseif($order->local_order_status === 'disputed')
    <div class="bg-red-50 border-2 border-red-300 rounded-2xl p-5 flex items-start gap-3">
        <i class="fas fa-exclamation-triangle text-red-500 text-lg flex-shrink-0 mt-0.5"></i>
        <div>
            <p class="font-bold text-red-800">Dispute in Progress</p>
            <p class="text-xs text-red-600 mt-0.5">Our team is investigating. Escrow is frozen. We will contact you within 24 hours.</p>
        </div>
    </div>
    @endif

    {{-- ── Order items ─────────────────────────────────────────────────── --}}
    <div class="bg-white rounded-2xl shadow-card p-5">
        <h3 class="font-bold text-primary text-sm mb-4 flex items-center gap-2"><i class="fas fa-box text-accent text-xs"></i> Items</h3>
        <div class="space-y-3">
            @foreach($order->items as $item)
            <div class="flex items-center gap-3">
                <img src="{{ ($item->product?->images[0] ?? null) ? Storage::url($item->product->images[0]) : asset('images/placeholder.svg') }}"
                     class="w-12 h-12 rounded-xl object-cover flex-shrink-0">
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-bold text-gray-800 truncate">{{ $item->getTitle() }}</p>
                    @if($item->selected_variant)<p class="text-xs text-gray-400">{{ $item->selected_variant }}</p>@endif
                </div>
                <div class="text-right flex-shrink-0">
                    <p class="text-xs text-gray-500">×{{ $item->quantity }}</p>
                    <p class="font-bold text-sm text-primary">{{ $sym }}{{ number_format($item->total_price_local,0) }}</p>
                </div>
            </div>
            @endforeach
        </div>
        <div class="border-t border-gray-100 mt-4 pt-3 flex justify-between font-black text-gray-800">
            <span>Total Paid</span>
            <span class="text-primary text-lg" style="font-family:Poppins,sans-serif">{{ $sym }}{{ number_format($order->grand_total,2) }}</span>
        </div>
        @if($order->coupon_discount > 0)
        <div class="flex justify-between text-xs text-green-600 mt-1">
            <span>Coupon Discount Applied</span>
            <span>−{{ $sym }}{{ number_format($order->coupon_discount,2) }}</span>
        </div>
        @endif
    </div>

    {{-- ── Delivery info ───────────────────────────────────────────────── --}}
    <div class="bg-white rounded-2xl shadow-card p-5 grid md:grid-cols-2 gap-4 text-sm">
        <div>
            <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Delivery Method</p>
            <p class="font-semibold text-gray-800">{{ $order->delivery_type === 'home_delivery' ? '🚚 Home Delivery' : '🏢 Self Pickup from Vendor' }}</p>
            @if($order->delivery_type === 'home_delivery' && $order->delivery_address)
            <p class="text-xs text-gray-500 mt-1">{{ $order->delivery_address }}, {{ $order->delivery_city }}, {{ $order->delivery_state }}</p>
            @endif
        </div>
        <div>
            <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Order Date</p>
            <p class="font-semibold text-gray-800">{{ $order->created_at->format('M d, Y · h:i A') }}</p>
            @if($order->vendor_confirmed_at)
            <p class="text-xs text-gray-500 mt-0.5">Confirmed {{ $order->vendor_confirmed_at->diffForHumans() }}</p>
            @endif
        </div>
    </div>

    {{-- ── Messages with vendor ────────────────────────────────────────── --}}
    @if(!in_array($order->local_order_status, ['pending']))
    @include('user.orders._messages_section', ['order' => $order])
    @endif

{{-- END LOCAL ORDER SECTION --}}
@else
{{-- ══════════════════════════════════════════════════════════════════════ --}}
{{-- CHINA IMPORT ORDER — existing pipeline content follows               --}}
{{-- ══════════════════════════════════════════════════════════════════════ --}}
@php $pinnedQ = $order->messages->where('is_pinned', true)->where('message_type', 'question')->first(); @endphp
@if($pinnedQ)
<div class="bg-amber-50 border-2 border-amber-400 rounded-2xl p-5 flex items-start gap-4 shadow-sm">
    <div class="w-11 h-11 bg-amber-100 rounded-xl flex items-center justify-center flex-shrink-0">
        <i class="fas fa-question-circle text-amber-500 text-lg"></i>
    </div>
    <div class="flex-1">
        <p class="font-bold text-amber-800 flex items-center gap-2 flex-wrap">
            We have a question about your order
            <span class="text-[10px] font-bold bg-amber-200 text-amber-800 px-2 py-0.5 rounded-full">{{ $pinnedQ->getTagLabel() }}</span>
        </p>
        <p class="text-sm text-amber-700 mt-1 leading-relaxed">{{ $pinnedQ->message }}</p>
        <p class="text-xs text-amber-500 mt-1">{{ $pinnedQ->created_at->diffForHumans() }} — Please reply so we can proceed with your order.</p>
        <a href="#messages" class="inline-flex items-center gap-1.5 mt-3 bg-amber-500 hover:bg-amber-600 text-white font-bold px-4 py-2 rounded-xl text-xs transition">
            <i class="fas fa-reply text-[10px]"></i> Reply Now
        </a>
    </div>
</div>
@endif

{{-- ── Progress Pipeline ───────────────────────────────────────────────── --}}
@if(!in_array($order->status, ['cancelled','pending']))
<div class="bg-white rounded-2xl shadow-card p-5 overflow-x-auto">
    <h3 class="font-bold text-primary text-sm mb-5 flex items-center gap-2">
        <i class="fas fa-route"></i> Shipment Progress
    </h3>
    @if($order->estimated_arrival_date && $order->status === 'in_transit')
    <div class="mb-4 flex items-center gap-2 text-xs text-indigo-700 bg-indigo-50 border border-indigo-200 rounded-xl px-3 py-2">
        <i class="fas fa-calendar-check text-indigo-400"></i>
        Estimated arrival: <strong>{{ $order->estimated_arrival_date->format('M d, Y') }}</strong>
        @if($order->estimated_arrival_date->isPast())
        <span class="text-orange-600 font-bold ml-1">— We are following up on a delay.</span>
        @else
        <span class="text-gray-500 ml-1">({{ $order->estimated_arrival_date->diffForHumans() }})</span>
        @endif
    </div>
    @endif
    <div class="flex items-center min-w-max">
        @foreach($pipeline as $i => $s)
        @php $sc = $cfg[$s] ?? []; $done = $i <= $step; $active = $i === $step; @endphp
        <div class="flex flex-col items-center gap-1.5 w-[88px]">
            <div class="w-9 h-9 rounded-full flex items-center justify-center border-2 transition-all
                {{ $active ? 'bg-primary border-primary scale-110 shadow-md' : ($done ? 'bg-primary border-primary' : 'bg-white border-gray-200') }}">
                <i class="fas {{ $sc['icon'] ?? 'fa-circle' }} text-xs {{ $done ? 'text-white' : 'text-gray-300' }}"></i>
            </div>
            <p class="text-[10px] text-center leading-tight {{ $done ? 'font-bold text-primary' : 'text-gray-400' }}">
                {{ $sc['label'] ?? $s }}
            </p>
        </div>
        @if(!$loop->last)
        <div class="h-0.5 w-5 flex-shrink-0 mb-4 {{ $i < $step ? 'bg-primary' : 'bg-gray-200' }}"></div>
        @endif
        @endforeach
    </div>
</div>
@endif

{{-- ── Shipping Fee Banners ─────────────────────────────────────────────── --}}
@if($order->status === 'awaiting_shipping_payment')
<div class="bg-orange-50 border-2 border-orange-300 rounded-2xl p-5">
    <div class="flex items-start gap-4">
        <div class="w-12 h-12 bg-orange-100 rounded-xl flex items-center justify-center flex-shrink-0">
            <i class="fas fa-credit-card text-orange-500 text-lg"></i>
        </div>
        <div class="flex-1">
            <h3 class="font-bold text-orange-800">Shipping Fee Ready to Pay</h3>
            <p class="text-sm text-orange-700 mt-1">Your goods have been weighed. Please pay the shipping fee so we can dispatch your order.</p>
            @if($order->shipping_fee_description)
            <p class="text-xs text-orange-600 mt-1 font-medium">{{ $order->shipping_fee_description }}</p>
            @endif
            <p class="text-2xl font-black text-orange-700 mt-2" style="font-family:Poppins,sans-serif">
                {{ $sym }}{{ number_format((float)$order->shipping_fee_assigned, 2) }}
            </p>
            <form action="{{ route('user.orders.pay-shipping', $order->id) }}" method="POST" class="mt-3"
                  data-confirm
                  data-confirm-title="Pay Shipping Fee"
                  data-confirm-msg="Pay {{ $sym }}{{ number_format((float)$order->shipping_fee_assigned,2) }} from your wallet? This cannot be undone."
                  data-confirm-ok="Pay Now"
                  data-confirm-cancel="Not Now">
                @csrf
                <button class="inline-flex items-center gap-2 bg-orange-500 hover:bg-orange-600 text-white font-bold px-6 py-2.5 rounded-xl text-sm transition">
                    <i class="fas fa-wallet"></i> Pay Now
                </button>
            </form>
        </div>
    </div>
</div>
@elseif($order->shipping_fee_status === 'assigned' && $order->shipping_fee_pay_timing === 'on_arrival' && $order->status === 'arrived')
<div class="bg-orange-50 border-2 border-orange-300 rounded-2xl p-5">
    <div class="flex items-start gap-4">
        <div class="w-12 h-12 bg-orange-100 rounded-xl flex items-center justify-center flex-shrink-0">
            <i class="fas fa-credit-card text-orange-500 text-lg"></i>
        </div>
        <div class="flex-1">
            <h3 class="font-bold text-orange-800">Shipping Fee Due</h3>
            <p class="text-sm text-orange-700 mt-1">Your goods have arrived. Please pay the outstanding shipping fee to proceed with collection or delivery.</p>
            @if($order->shipping_fee_description)
            <p class="text-xs text-orange-600 mt-1 font-medium">{{ $order->shipping_fee_description }}</p>
            @endif
            <p class="text-2xl font-black text-orange-700 mt-2" style="font-family:Poppins,sans-serif">
                {{ $sym }}{{ number_format((float)$order->shipping_fee_assigned, 2) }}
            </p>
            <form action="{{ route('user.orders.pay-shipping', $order->id) }}" method="POST" class="mt-3"
                  data-confirm
                  data-confirm-title="Pay Shipping Fee"
                  data-confirm-msg="Pay {{ $sym }}{{ number_format((float)$order->shipping_fee_assigned,2) }} from your wallet?"
                  data-confirm-ok="Pay Now">
                @csrf
                <button class="inline-flex items-center gap-2 bg-orange-500 hover:bg-orange-600 text-white font-bold px-6 py-2.5 rounded-xl text-sm transition">
                    <i class="fas fa-wallet"></i> Pay Shipping Fee
                </button>
            </form>
        </div>
    </div>
</div>
@endif

{{-- ── China Warehouse ─────────────────────────────────────────────────── --}}
@if($order->status === 'china_warehouse')
<div class="bg-purple-50 border border-purple-200 rounded-2xl p-5 space-y-4">
    <div class="flex items-start gap-3">
        <div class="w-10 h-10 bg-purple-100 rounded-xl flex items-center justify-center flex-shrink-0">
            <i class="fas fa-warehouse text-purple-600"></i>
        </div>
        <div>
            <h3 class="font-bold text-purple-800">Your goods are at our China warehouse</h3>
            <p class="text-sm text-purple-700 mt-0.5">We are preparing them for international shipping. You can request product photos or special packaging below.</p>
        </div>
    </div>

    @if($order->actual_weight_kg || $order->cbm || $order->shipping_fee_assigned)
    <div class="bg-white rounded-xl border border-purple-200 p-4">
        <p class="text-xs font-bold text-purple-600 uppercase tracking-wider mb-3">Weight & Shipping</p>
        <div class="grid grid-cols-2 md:grid-cols-3 gap-3 text-sm">
            @if($order->actual_weight_kg)
            <div class="bg-purple-50 rounded-lg p-2.5 text-center">
                <p class="text-xs text-gray-500">Actual Weight</p>
                <p class="font-black text-purple-700 text-lg">{{ $order->actual_weight_kg }}<span class="text-xs font-normal">kg</span></p>
            </div>
            @endif
            @if($order->cbm)
            <div class="bg-purple-50 rounded-lg p-2.5 text-center">
                <p class="text-xs text-gray-500">Volume (CBM)</p>
                <p class="font-black text-purple-700 text-lg">{{ $order->cbm }}<span class="text-xs font-normal">m³</span></p>
            </div>
            @endif
            @if($order->shipping_fee_assigned && $order->shipping_fee_status === 'assigned')
            <div class="bg-orange-50 rounded-lg p-2.5 text-center border border-orange-200">
                <p class="text-xs text-gray-500">Shipping Fee</p>
                <p class="font-black text-orange-600 text-lg">{{ $sym }}{{ number_format((float)$order->shipping_fee_assigned,0) }}</p>
                <p class="text-[10px] text-orange-500 mt-0.5">{{ $order->shipping_fee_pay_timing === 'on_arrival' ? 'Payable on arrival' : 'Pay before shipping' }}</p>
            </div>
            @endif
        </div>
        @if($order->shipping_fee_description)
        <p class="text-xs text-gray-500 mt-2 flex items-start gap-1">
            <i class="fas fa-info-circle text-gray-400 flex-shrink-0 mt-0.5"></i> {{ $order->shipping_fee_description }}
        </p>
        @endif
    </div>
    @else
    <p class="text-xs text-purple-600 flex items-center gap-1.5">
        <i class="fas fa-clock"></i> Your goods are being received and weighed. Shipping details will appear here once ready.
    </p>
    @endif

    @if($order->shipment_id)
    <div class="bg-indigo-50 border border-indigo-200 rounded-xl p-3 flex items-center gap-3">
        <i class="fas fa-cubes text-indigo-500"></i>
        <div>
            <p class="font-bold text-indigo-800 text-sm">Combined Shipment — {{ $order->shipment->shipment_number }}</p>
            <p class="text-xs text-indigo-600">This order is grouped with your other orders for combined shipping.
                @if($order->shipment->actual_weight_kg) Total combined weight: <strong>{{ $order->shipment->actual_weight_kg }}kg</strong>@endif
            </p>
        </div>
    </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
        @php $photoReq = $order->serviceRequests->where('type','photo')->first(); @endphp
        @if($order->canRequestPhoto())
        <div class="bg-white rounded-xl border border-purple-100 p-4">
            <p class="font-bold text-sm text-gray-800 flex items-center gap-2 mb-1"><i class="fas fa-camera text-purple-500"></i> Request Product Photos</p>
            <p class="text-xs text-gray-500 mb-3">We will photograph your goods and upload the images to this order.</p>
            <form action="{{ route('user.orders.photo', $order->id) }}" method="POST" x-data="{open:false}">
                @csrf
                <button type="button" @click="open=!open" class="text-xs bg-purple-600 hover:bg-purple-700 text-white font-bold px-4 py-2 rounded-lg transition">📸 Request Photos</button>
                <div x-show="open" class="mt-3 space-y-2">
                    <textarea name="user_note" rows="2" placeholder="Any specific angles or details? (optional)" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-xs focus:outline-none focus:border-purple-400"></textarea>
                    <button type="submit" class="w-full bg-purple-600 text-white text-xs font-bold py-2 rounded-lg hover:bg-purple-700 transition">Confirm</button>
                </div>
            </form>
        </div>
        @elseif($photoReq)
        <div class="bg-white rounded-xl border border-purple-100 p-4">
            <p class="font-bold text-sm text-gray-800 flex items-center gap-2 mb-2">
                <i class="fas fa-camera text-purple-500"></i> Product Photos
                <span class="ml-auto text-xs font-bold px-2 py-0.5 rounded-full bg-{{ $photoReq->getStatusColor() }}-100 text-{{ $photoReq->getStatusColor() }}-700">{{ ucfirst($photoReq->status) }}</span>
            </p>
            @if($photoReq->result_images && count($photoReq->result_images))
            <div class="grid grid-cols-3 gap-1">
                @foreach($photoReq->result_images as $img)
                <a href="{{ Storage::url($img) }}" target="_blank"><img src="{{ Storage::url($img) }}" class="w-full h-16 object-cover rounded-lg border"></a>
                @endforeach
            </div>
            @else
            <p class="text-xs text-gray-400">{{ $photoReq->admin_note ?? 'Photos will be uploaded shortly.' }}</p>
            @endif
        </div>
        @endif

        @php $packReq = $order->serviceRequests->where('type','packaging')->first(); @endphp
        @if($order->canRequestPackaging())
        <div class="bg-white rounded-xl border border-purple-100 p-4">
            <p class="font-bold text-sm text-gray-800 flex items-center gap-2 mb-1"><i class="fas fa-box text-purple-500"></i> Request Special Packaging</p>
            <p class="text-xs text-gray-500 mb-3">Extra protection for fragile or high-value items.</p>
            <form action="{{ route('user.orders.packaging', $order->id) }}" method="POST" x-data="{open:false}">
                @csrf
                <button type="button" @click="open=!open" class="text-xs bg-purple-600 hover:bg-purple-700 text-white font-bold px-4 py-2 rounded-lg transition">📦 Request Packaging</button>
                <div x-show="open" class="mt-3 space-y-2">
                    <textarea name="user_note" rows="2" placeholder="e.g. fragile items, bubble wrap needed..." class="w-full border border-gray-200 rounded-lg px-3 py-2 text-xs focus:outline-none focus:border-purple-400"></textarea>
                    <button type="submit" class="w-full bg-purple-600 text-white text-xs font-bold py-2 rounded-lg hover:bg-purple-700 transition">Confirm</button>
                </div>
            </form>
        </div>
        @elseif($packReq)
        <div class="bg-white rounded-xl border border-purple-100 p-4">
            <p class="font-bold text-sm text-gray-800 flex items-center gap-2 mb-1">
                <i class="fas fa-box text-purple-500"></i> Packaging
                <span class="ml-auto text-xs font-bold px-2 py-0.5 rounded-full bg-{{ $packReq->getStatusColor() }}-100 text-{{ $packReq->getStatusColor() }}-700">{{ ucfirst($packReq->status) }}</span>
            </p>
            <p class="text-xs text-gray-500">{{ $packReq->admin_note ?? 'Your goods will be carefully packaged.' }}</p>
        </div>
        @endif
    </div>
</div>
@endif

{{-- ── Nigeria Warehouse ────────────────────────────────────────────────── --}}
@if($order->status === 'arrived')
@php
    $freeDays    = (int)\App\Models\Setting::get('storage_fine_free_days', 7);
    $finePerDay  = (float)\App\Models\Setting::get('storage_fine_per_day', 200);
    $daysInStore = $order->arrived_at ? (int)now()->diffInDays($order->arrived_at) : 0;
    $daysLeft    = max(0, $freeDays - $daysInStore);
    $fineEnabled = \App\Models\Setting::get('storage_fine_enabled','1') === '1';
@endphp
<div class="bg-teal-50 border-2 border-teal-300 rounded-2xl p-5 space-y-4">
    <div class="flex items-start gap-4">
        <div class="w-12 h-12 bg-teal-100 rounded-xl flex items-center justify-center flex-shrink-0">
            <i class="fas fa-building text-teal-600 text-lg"></i>
        </div>
        <div class="flex-1">
            <h3 class="font-bold text-teal-800 text-base">Your order has arrived in Nigeria 🎉</h3>
            @if($order->pickup_office_info)
            <div class="mt-2 bg-white rounded-xl border border-teal-200 p-3 text-sm text-gray-700">
                <p class="text-xs font-bold text-teal-600 mb-1 uppercase tracking-wider">Collection Details</p>
                {!! nl2br(e($order->pickup_office_info)) !!}
            </div>
            @endif
            @if($fineEnabled)
            <div class="mt-3 text-xs {{ $daysLeft <= 2 ? 'text-red-600 font-bold' : 'text-teal-700' }}">
                @if($daysLeft > 0)
                ⏰ Free storage: <strong>{{ $daysLeft }} day{{ $daysLeft !== 1 ? 's' : '' }}</strong> remaining before daily storage charges apply ({{ $sym }}{{ number_format($finePerDay,2) }}/day).
                @else
                ⚠️ Free storage period has ended. Daily storage charges of <strong>{{ $sym }}{{ number_format($finePerDay,2) }}</strong> are being applied.
                @if((float)$order->storage_fine_total > 0) Total charged: <strong>{{ $sym }}{{ number_format((float)$order->storage_fine_total,2) }}</strong>@endif
                @endif
            </div>
            @endif
        </div>
    </div>

    {{-- Self-pickup confirmation --}}
    @if($order->canConfirmPickup())
    <div class="bg-white rounded-xl border border-teal-200 p-4">
        <p class="font-bold text-gray-800 text-sm mb-1 flex items-center gap-2"><i class="fas fa-check-circle text-teal-500"></i> Confirm Collection</p>
        <p class="text-xs text-gray-600 mb-3">Once you have collected your goods, please confirm below to mark your order as complete.</p>
        <form action="{{ route('user.orders.confirm-pickup', $order->id) }}" method="POST"
              data-confirm
              data-confirm-title="Confirm Collection"
              data-confirm-msg="Please confirm that you have physically received and collected your goods for order #{{ $order->order_number }}."
              data-confirm-ok="Yes, I Have Collected"
              data-confirm-cancel="Not Yet">
            @csrf
            <button class="inline-flex items-center gap-2 bg-teal-600 hover:bg-teal-700 text-white font-bold px-5 py-2.5 rounded-xl text-sm transition">
                <i class="fas fa-check"></i> I Have Collected My Order
            </button>
        </form>
    </div>
    @endif

    {{-- Doorstep delivery --}}
    @if($order->doorstep_status === 'none')
        @if($order->doorstep_fee_assigned !== null)
        <div class="bg-white rounded-xl border border-teal-200 p-4">
            <h4 class="font-bold text-gray-800 mb-1 flex items-center gap-2"><i class="fas fa-truck text-teal-500"></i> Home Delivery Available</h4>
            @if($order->doorstep_fee_description)<p class="text-xs text-gray-500 mb-2">{{ $order->doorstep_fee_description }}</p>@endif
            <p class="text-xl font-black text-teal-700 mb-3" style="font-family:Poppins,sans-serif">{{ $sym }}{{ number_format((float)$order->doorstep_fee_assigned,2) }}</p>
            <form action="{{ route('user.orders.doorstep',$order->id) }}" method="POST" x-data="{open:false}" class="space-y-2">
                @csrf
                <button type="button" @click="open=!open" class="inline-flex items-center gap-2 bg-teal-600 hover:bg-teal-700 text-white font-bold px-5 py-2.5 rounded-xl text-sm transition">
                    <i class="fas fa-home"></i> Request Home Delivery
                </button>
                <div x-show="open" class="space-y-2 mt-3 border-t border-teal-100 pt-3">
                    <textarea name="delivery_address" rows="2" required placeholder="Full delivery address..." class="w-full border-2 border-gray-200 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-teal-400">{{ $order->delivery_address }}</textarea>
                    <div class="grid grid-cols-2 gap-2">
                        <input name="delivery_state" type="text" required placeholder="State" value="{{ $order->delivery_state }}" class="w-full border-2 border-gray-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:border-teal-400">
                        <input name="delivery_city" type="text" placeholder="City (optional)" value="{{ $order->delivery_city }}" class="w-full border-2 border-gray-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:border-teal-400">
                    </div>
                    <button type="submit"
                        class="w-full bg-teal-600 hover:bg-teal-700 text-white font-bold py-2.5 rounded-xl text-sm transition">
                        Confirm &amp; Pay {{ $sym }}{{ number_format((float)$order->doorstep_fee_assigned,2) }}
                    </button>
                </div>
            </form>
        </div>
        @else
        <div class="bg-white rounded-xl border border-teal-200 p-4">
            <h4 class="font-bold text-gray-800 mb-1 flex items-center gap-2"><i class="fas fa-truck text-teal-500"></i> Request Home Delivery</h4>
            <p class="text-sm text-gray-600 mb-3">Submit your address and we will calculate the delivery fee for your area.</p>
            <form action="{{ route('user.orders.doorstep',$order->id) }}" method="POST" x-data="{open:false}" class="space-y-2">
                @csrf
                <button type="button" @click="open=!open" class="inline-flex items-center gap-2 bg-teal-600 hover:bg-teal-700 text-white font-bold px-5 py-2.5 rounded-xl text-sm transition">
                    <i class="fas fa-home"></i> I Want Home Delivery
                </button>
                <div x-show="open" class="space-y-2 mt-3 border-t border-teal-100 pt-3">
                    <textarea name="delivery_address" rows="2" required placeholder="Full delivery address..." class="w-full border-2 border-gray-200 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-teal-400"></textarea>
                    <div class="grid grid-cols-2 gap-2">
                        <input name="delivery_state" required type="text" placeholder="State" class="w-full border-2 border-gray-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:border-teal-400">
                        <input name="delivery_city" type="text" placeholder="City (optional)" class="w-full border-2 border-gray-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:border-teal-400">
                    </div>
                    <p class="text-xs text-gray-400">We will confirm the delivery fee and notify you before any charge.</p>
                    <button type="submit" class="w-full bg-teal-600 hover:bg-teal-700 text-white font-bold py-2.5 rounded-xl text-sm transition">Submit Request</button>
                </div>
            </form>
        </div>
        @endif
    @elseif($order->doorstep_status === 'requested')
    <div class="bg-blue-50 border border-blue-200 rounded-xl p-3 flex items-center gap-3">
        <i class="fas fa-clock text-blue-400"></i>
        <div>
            <p class="font-bold text-blue-800 text-sm">Home Delivery Requested</p>
            <p class="text-xs text-blue-600">We are calculating the delivery fee for your area. You will be notified shortly.</p>
        </div>
    </div>
    @elseif(in_array($order->doorstep_status, ['paid','dispatched']))
    <div class="bg-green-50 border border-green-200 rounded-xl p-3 flex items-center gap-3">
        <i class="fas fa-check-circle text-green-500 text-lg"></i>
        <div>
            <p class="font-bold text-green-800 text-sm">Home Delivery Confirmed 🏠</p>
            <p class="text-xs text-green-600">Delivering to: {{ $order->delivery_address }}</p>
        </div>
    </div>
    @endif
</div>
@endif

{{-- ── Delivered: Pickup Confirmed + Rating ────────────────────────────── --}}
@if($order->status === 'delivered')
@if($order->canBeRated())
<div class="bg-gradient-to-r from-yellow-50 to-orange-50 border-2 border-yellow-300 rounded-2xl p-5">
    <h3 class="font-bold text-yellow-800 mb-1 flex items-center gap-2">
        <i class="fas fa-star text-yellow-500"></i> How was your experience?
    </h3>
    <p class="text-sm text-yellow-700 mb-4">Your order has been delivered. Share your feedback — it helps us serve you better.</p>
    <form action="{{ route('user.orders.rating.store', $order->id) }}" method="POST" class="space-y-4">
        @csrf
        <div x-data="{ rating: 0, hover: 0 }">
            <p class="text-xs font-bold text-gray-600 mb-2">Overall Rating</p>
            <div class="flex gap-1">
                @for($i = 1; $i <= 5; $i++)
                <button type="button"
                    @click="rating = {{ $i }}"
                    @mouseover="hover = {{ $i }}"
                    @mouseleave="hover = 0"
                    class="text-3xl transition"
                    :class="(hover || rating) >= {{ $i }} ? 'text-yellow-400' : 'text-gray-300'">★</button>
                @endfor
            </div>
            <input type="hidden" name="rating" :value="rating" required>
            <p class="text-xs text-gray-400 mt-1" x-text="rating === 0 ? 'Tap to rate' : ['','Poor','Average','Good','Very Good','Excellent'][rating] + ' (' + rating + '/5)'"></p>
        </div>
        <div>
            <p class="text-xs font-bold text-gray-600 mb-2">How was the overall experience?</p>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-2">
                @foreach(['excellent'=>'😍 Excellent','good'=>'😊 Good','average'=>'😐 Average','poor'=>'😞 Poor'] as $val=>$label)
                <label class="cursor-pointer">
                    <input type="radio" name="experience" value="{{ $val }}" class="sr-only peer" required>
                    <div class="border-2 rounded-xl py-2 text-center text-xs font-semibold transition peer-checked:border-yellow-400 peer-checked:bg-yellow-50 border-gray-200 text-gray-600 hover:border-yellow-300">
                        {{ $label }}
                    </div>
                </label>
                @endforeach
            </div>
        </div>
        <div>
            <label class="text-xs font-bold text-gray-600 block mb-1">Comment (optional)</label>
            <textarea name="comment" rows="3" placeholder="Tell us about your experience with this order..."
                class="w-full border-2 border-gray-100 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-yellow-400 transition"></textarea>
        </div>
        <button type="submit" class="inline-flex items-center gap-2 bg-yellow-500 hover:bg-yellow-600 text-white font-bold px-6 py-2.5 rounded-xl text-sm transition">
            <i class="fas fa-paper-plane"></i> Submit Feedback
        </button>
    </form>
</div>
@elseif($order->is_rated && $order->rating)
<div class="bg-yellow-50 border border-yellow-200 rounded-2xl p-4 flex items-center gap-3">
    <div class="w-10 h-10 bg-yellow-100 rounded-xl flex items-center justify-center flex-shrink-0">
        <i class="fas fa-star text-yellow-500"></i>
    </div>
    <div>
        <p class="font-bold text-yellow-800 text-sm">Your Feedback — {{ $order->rating->getStars() }}</p>
        <p class="text-xs text-yellow-700">{{ $order->rating->getExperienceLabel() }}@if($order->rating->comment) — "{{ Str::limit($order->rating->comment, 80) }}"@endif</p>
    </div>
</div>
@endif
@endif

{{-- ── Payment Summary + Shipping Info ───────────────────────────────── --}}
<div class="grid grid-cols-1 md:grid-cols-2 gap-5">
    <div class="bg-white rounded-2xl shadow-card p-5">
        <h3 class="font-bold text-primary mb-4 text-sm flex items-center gap-2"><i class="fas fa-calculator"></i> Payment Summary</h3>
        <div class="space-y-2.5 text-sm">
            <div class="flex justify-between text-gray-600"><span>Products</span><span class="font-semibold">{{ $sym }}{{ number_format((float)$order->items_total_local,2) }}</span></div>
            <div class="flex justify-between text-gray-600"><span>Service Fee</span><span class="font-semibold">{{ $sym }}{{ number_format((float)$order->purchase_fee,2) }}</span></div>
            @if((float)$order->local_freight_fee > 0)
            <div class="flex justify-between text-gray-600">
                <span class="flex items-center gap-1"><i class="fas fa-truck text-gray-400 text-xs"></i> China Domestic Freight</span>
                <span class="font-semibold">{{ $sym }}{{ number_format((float)$order->local_freight_fee,2) }}</span>
            </div>
            @endif
            @if((float)$order->discount_amount > 0)
            <div class="flex justify-between text-green-600"><span>Discount Applied</span><span class="font-semibold">−{{ $sym }}{{ number_format((float)$order->discount_amount,2) }}</span></div>
            @endif
            @if((float)$order->coupon_discount > 0)
            <div class="flex justify-between text-green-600">
                <span>Coupon <span class="font-mono text-xs bg-green-100 px-1.5 py-0.5 rounded">{{ $order->coupon_code }}</span></span>
                <span class="font-semibold">−{{ $sym }}{{ number_format((float)$order->coupon_discount,2) }}</span>
            </div>
            @endif
            <div class="flex justify-between font-black text-base border-t-2 border-gray-100 pt-2.5">
                <span>Paid at Checkout</span>
                <span class="text-accent" style="font-family:Poppins,sans-serif">{{ $sym }}{{ number_format((float)$order->grand_total,2) }}</span>
            </div>
            @if((float)$order->international_shipping_fee > 0)
            <div class="flex justify-between text-gray-600"><span>Shipping Fee</span><span class="font-semibold">{{ $sym }}{{ number_format((float)$order->international_shipping_fee,2) }}</span></div>
            @elseif(in_array($order->shipping_fee_status, ['unpaid','assigned']))
            <div class="flex justify-between text-orange-500 text-xs font-semibold">
                <span>Shipping Fee</span>
                <span>{{ $order->shipping_fee_status === 'assigned' ? $sym.number_format((float)$order->shipping_fee_assigned,2).' — pending payment' : 'To be confirmed' }}</span>
            </div>
            @endif
            @if((float)$order->doorstep_delivery_fee > 0)
            <div class="flex justify-between text-gray-600"><span>Home Delivery Fee</span><span class="font-semibold">{{ $sym }}{{ number_format((float)$order->doorstep_delivery_fee,2) }}</span></div>
            @endif
            @if((float)$order->storage_fine_total > 0)
            <div class="flex justify-between text-red-500 text-xs"><span>Storage Charges</span><span class="font-bold">{{ $sym }}{{ number_format((float)$order->storage_fine_total,2) }}</span></div>
            @endif
        </div>
        <p class="text-xs text-gray-400 mt-3 pt-3 border-t border-gray-100">Exchange rate: ¥1 = {{ $sym }}{{ number_format((float)$order->exchange_rate_used,2) }} · Product cost: ¥{{ number_format((float)$order->items_total_cny,2) }}</p>
    </div>

    <div class="bg-white rounded-2xl shadow-card p-5">
        <h3 class="font-bold text-primary mb-4 text-sm flex items-center gap-2"><i class="fas fa-shipping-fast"></i> Shipping Details</h3>
        <div class="space-y-2 text-sm divide-y divide-gray-50">
            <div class="flex gap-3 py-2"><span class="text-gray-400 text-xs w-28 flex-shrink-0 pt-0.5">Method</span><span class="font-semibold">{{ config("bybhub.shipping_methods.{$order->shipping_method}",$order->shipping_method) }}</span></div>
            <div class="flex gap-3 py-2"><span class="text-gray-400 text-xs w-28 flex-shrink-0 pt-0.5">Delivery</span>
                <span class="font-semibold">
                    @if($order->delivery_type === 'home_delivery') 🏠 Home Delivery
                    @elseif($order->pickup_confirmed_at) ✅ Collected
                    @else 🏢 Self-Collection
                    @endif
                </span>
            </div>
            @if($order->delivery_address)<div class="flex gap-3 py-2"><span class="text-gray-400 text-xs w-28 flex-shrink-0 pt-0.5">Address</span><span>{{ $order->delivery_address }}</span></div>@endif
            @if($order->byb_tracking_number)<div class="flex gap-3 py-2"><span class="text-gray-400 text-xs w-28 flex-shrink-0 pt-0.5">Tracking No.</span><span class="font-black text-primary font-mono">{{ $order->byb_tracking_number }}</span></div>@endif
            @if($order->tracking_number)<div class="flex gap-3 py-2"><span class="text-gray-400 text-xs w-28 flex-shrink-0 pt-0.5">Carrier</span><span class="font-bold font-mono">{{ $order->tracking_number }}@if($order->carrier) <span class="font-normal text-gray-400 text-xs">({{ $order->carrier }})</span>@endif</span></div>@endif
            @if($order->actual_weight_kg)<div class="flex gap-3 py-2"><span class="text-gray-400 text-xs w-28 flex-shrink-0 pt-0.5">Actual Weight</span><span class="font-semibold">{{ $order->actual_weight_kg }}kg</span></div>@endif
        </div>
        @if($order->canBeCancelled())
        <form action="{{ route('user.orders.cancel',$order->id) }}" method="POST" class="mt-4"
              data-confirm
              data-confirm-type="danger"
              data-confirm-title="Cancel Order"
              data-confirm-msg="Are you sure you want to cancel order #{{ $order->order_number }}? Your checkout payment will be refunded to your wallet."
              data-confirm-ok="Yes, Cancel Order"
              data-confirm-cancel="Keep Order">
            @csrf
            <textarea name="cancel_reason" rows="2" placeholder="Reason for cancellation (optional)"
                class="w-full border-2 border-gray-100 bg-gray-50 rounded-xl px-3 py-2.5 text-xs mb-2 focus:outline-none focus:border-red-300"></textarea>
            <button class="w-full bg-red-50 text-red-600 border-2 border-red-200 py-2.5 rounded-xl text-sm font-bold hover:bg-red-100 transition flex items-center justify-center gap-2">
                <i class="fas fa-times-circle"></i> Cancel Order
            </button>
        </form>
        @endif
    </div>
</div>

{{-- ── Message Thread ───────────────────────────────────────────────────── --}}
@if(!in_array($order->status, ['pending','cancelled']))
<div class="bg-white rounded-2xl shadow-card p-5"
     x-data="{ open: {{ $order->unreadSupportMessages() > 0 ? 'true' : 'false' }} }">
    <button type="button" @click="open=!open"
        class="w-full flex items-center justify-between text-left">
        <h3 class="font-bold text-primary text-sm flex items-center gap-2">
            <i class="fas fa-comments"></i> Messages &amp; Support
            @if($order->unreadSupportMessages() > 0)
            <span class="bg-accent text-white text-[10px] font-bold px-2 py-0.5 rounded-full">
                {{ $order->unreadSupportMessages() }} new
            </span>
            @endif
        </h3>
        <i class="fas fa-chevron-down text-gray-400 text-xs transition-transform" :class="open ? 'rotate-180' : ''"></i>
    </button>

        <div x-show="open" x-transition class="mt-4 space-y-3" id="messages">
            @if($order->messages->isEmpty())
            <p class="text-sm text-gray-400 text-center py-4">No messages yet. Send us a message about this order below.</p>
            @else
            <div class="space-y-3 max-h-72 overflow-y-auto pr-1">
                @foreach($order->messages->sortBy('created_at') as $msg)
                <div class="flex {{ $msg->isFromCustomer() ? 'justify-end' : 'justify-start' }}">
                    <div class="max-w-[80%] rounded-2xl px-4 py-2.5
                        {{ $msg->isFromCustomer()
                            ? ($msg->message_type === 'answer' ? 'bg-green-500 text-white rounded-br-sm' : 'bg-primary text-white rounded-br-sm')
                            : ($msg->isQuestion() ? 'bg-amber-100 text-amber-900 rounded-bl-sm border-2 border-amber-300' : 'bg-gray-100 text-gray-800 rounded-bl-sm') }}">
                        @if($msg->isFromSupport())
                        <p class="text-[10px] font-bold mb-1 opacity-70">
                            BYBHUB Support{{ $msg->isQuestion() ? ' · ❓ ' . $msg->getTagLabel() : '' }}
                            @if($msg->is_pinned) · 📌 @endif
                        </p>
                        @elseif($msg->message_type === 'answer')
                        <p class="text-[10px] font-bold mb-1 text-white/80">Your Answer ✅</p>
                        @endif
                        <p class="text-sm leading-relaxed">{{ $msg->message }}</p>
                        <p class="text-[10px] mt-1 opacity-60">{{ $msg->created_at->format('M d, h:i A') }}</p>
                    </div>
                </div>
                @endforeach
            </div>
            @endif

        <form action="{{ route('user.orders.messages.store', $order->id) }}" method="POST"
              class="flex gap-2 pt-3 border-t border-gray-100" x-init="$el.addEventListener('submit', () => { $dispatch('mark-read') })">
            @csrf
            <input type="text" name="message" required placeholder="Type your message..."
                class="flex-1 border-2 border-gray-100 bg-gray-50 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-primary transition">
            <button class="bg-primary hover:bg-primary-dark text-white font-bold px-4 py-2.5 rounded-xl text-sm transition flex items-center gap-1.5">
                <i class="fas fa-paper-plane text-xs"></i> Send
            </button>
        </form>

        @if($whatsapp)
        <p class="text-xs text-center text-gray-400 pt-1">
            Prefer WhatsApp?
            <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $whatsapp) }}?text=Hi, I need help with order %23{{ $order->order_number }}"
               target="_blank" class="text-green-600 font-bold hover:underline">
                <i class="fab fa-whatsapp"></i> Chat with us
            </a>
        </p>
        @endif
    </div>
</div>
@endif

{{-- ── Status History ───────────────────────────────────────────────────── --}}
<div class="bg-white rounded-2xl shadow-card p-5">
    <h3 class="font-bold text-primary mb-5 text-sm flex items-center gap-2"><i class="fas fa-history"></i> Order Timeline</h3>
    <div class="relative">
        <div class="absolute left-4 top-2 bottom-2 w-0.5 bg-gray-100"></div>
        <div class="space-y-4">
            @foreach($order->statusHistory->sortByDesc('created_at') as $h)
            @php $hcfg = config("bybhub.order_statuses.{$h->new_status}",[]); @endphp
            <div class="flex gap-4">
                <div class="w-8 h-8 rounded-full bg-{{ $hcfg['color'] ?? 'gray' }}-500 flex items-center justify-center flex-shrink-0 z-10 shadow-sm">
                    <i class="fas {{ $hcfg['icon'] ?? 'fa-check' }} text-white text-xs"></i>
                </div>
                <div class="flex-1 pb-1">
                    <p class="font-bold text-gray-800 text-sm">{{ $hcfg['label'] ?? ucfirst(str_replace('_',' ',$h->new_status)) }}</p>
                    @if($h->note && !str_starts_with($h->note, 'Added to shipment') && !str_starts_with($h->note, 'Combined into'))
                    <p class="text-xs text-gray-500 mt-0.5">{{ $h->note }}</p>
                    @endif
                    <p class="text-xs text-gray-400 mt-0.5">{{ $h->created_at->format('M d, Y · h:i A') }}</p>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>

{{-- ── Order Items ──────────────────────────────────────────────────────── --}}
<div class="bg-white rounded-2xl shadow-card p-5">
    <h3 class="font-bold text-primary mb-4 text-sm flex items-center gap-2"><i class="fas fa-boxes"></i> Order Items</h3>
    <div class="divide-y divide-gray-50">
        @foreach($order->items as $item)
        <div class="flex gap-4 py-4">
            <img src="{{ $item->getImage() }}" class="w-16 h-16 object-cover rounded-xl flex-shrink-0 border border-gray-100"
                onerror="this.src='{{ asset('images/placeholder.svg') }}'">
            <div class="flex-1 min-w-0">
                <p class="font-semibold text-gray-800 text-sm line-clamp-2">{{ $item->getTitle() }}</p>
                @if($item->selected_variant)<p class="text-xs text-gray-400 mt-0.5">{{ $item->selected_variant }}</p>@endif
                <div class="flex justify-between items-end mt-2">
                    <span class="text-xs text-gray-500">Qty: <strong>{{ $item->quantity }}</strong></span>
                    <span class="font-black text-gray-800 text-sm" style="font-family:Poppins,sans-serif">
                        {{ $sym }}{{ number_format((float)$item->total_price_local,2) }}
                    </span>
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>

</div>{{-- end space-y-5 --}}
@endif
@endsection
