@extends('layouts.app')
@section('title', 'Checkout')

@php
    // Determine which BNPL providers are live on this order amount
    $grandTotal      = $pricing['grand_total'];
    $klumpLive       = $bnpl['klump']['enabled']  && !empty($bnpl['klump']['public_key'])  && $grandTotal >= $bnpl['klump']['min_amount'];
    $credpalLive     = $bnpl['credpal']['enabled'] && !empty($bnpl['credpal']['public_key']) && $grandTotal >= $bnpl['credpal']['min_amount'];
    $zillaLive       = $bnpl['zilla']['enabled']   && !empty($bnpl['zilla']['public_key'])   && $grandTotal >= $bnpl['zilla']['min_amount'];
@endphp

@if($klumpLive)
@push('scripts')
<script src="https://js.useklump.com/klump.js" defer></script>
@endpush
@endif

@section('content')
<div x-data="{ loading: false, paymentMethod: 'wallet' }" class="max-w-4xl mx-auto space-y-6">

    {{-- ── Page heading --}}
    <div class="flex items-center gap-3">
        <a href="{{ route('user.cart.index') }}"
            class="w-9 h-9 bg-white rounded-xl shadow flex items-center justify-center text-gray-400 hover:text-gray-700 transition">
            <i class="fas fa-arrow-left text-sm"></i>
        </a>
        <div>
            <h1 class="text-xl font-bold text-primary" style="font-family:Poppins,sans-serif">Checkout</h1>
            <p class="text-xs text-gray-400">Review your order and choose how to pay</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">

        {{-- ── Left: Items + Breakdown ──────────────────────────────── --}}
        <div class="lg:col-span-2 space-y-4">

            {{-- Order Items --}}
            <div class="bg-white rounded-2xl shadow-card overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                    <h2 class="font-bold text-gray-800 text-sm flex items-center gap-2">
                        <i class="fas fa-box text-primary text-xs"></i>
                        Order Items ({{ $items->count() }})
                    </h2>
                    <a href="{{ route('user.cart.index') }}" class="text-xs text-primary hover:underline">Edit cart</a>
                </div>
                <div class="divide-y divide-gray-50">
                    @foreach($items as $item)
                    <div class="flex gap-3 px-5 py-3 items-center">
                        <img src="{{ $item->getImage() }}" alt="{{ $item->getTitle() }}"
                            loading="lazy"
                            class="w-14 h-14 object-cover rounded-xl flex-shrink-0 border border-gray-100"
                            onerror="this.src='{{ asset('images/placeholder.svg') }}'">
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-semibold text-gray-800 line-clamp-2">{{ $item->getTitle() }}</p>
                            @if($item->selected_variant)
                            <p class="text-xs text-gray-400 mt-0.5">{{ $item->selected_variant }}</p>
                            @endif
                            <p class="text-xs text-gray-500 mt-0.5">Qty: {{ $item->quantity }}</p>
                        </div>
                        <div class="text-right flex-shrink-0">
                            <p class="font-bold text-accent text-sm">
                                {{ $symbol }}{{ number_format($item->unit_price_cny * $rate * $item->quantity, 2) }}
                            </p>
                            <p class="text-xs text-gray-400">¥{{ number_format($item->unit_price_cny * $item->quantity, 2) }}</p>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- Pricing Breakdown --}}
            <div class="bg-white rounded-2xl shadow-card overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100">
                    <h2 class="font-bold text-gray-800 text-sm flex items-center gap-2">
                        <i class="fas fa-receipt text-primary text-xs"></i>
                        Pricing Breakdown
                    </h2>
                </div>
                <div class="px-5 py-4 space-y-3">
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">Products (CNY)</span>
                        <span class="font-semibold">¥{{ number_format($totalCny, 2) }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">Products ({{ $user->currency }})</span>
                        <span class="font-semibold">{{ $symbol }}{{ number_format($totalLocal, 2) }}</span>
                    </div>
                    @if($domFreightLocal > 0)
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500 flex items-center gap-1.5">
                            <i class="fas fa-warehouse text-amber-400 text-xs"></i>
                            China Local Freight
                        </span>
                        <span class="font-semibold">{{ $symbol }}{{ number_format($domFreightLocal, 2) }}</span>
                    </div>
                    @endif
                    @if($purchaseFee > 0)
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500 flex items-center gap-1.5">
                            <i class="fas fa-concierge-bell text-blue-400 text-xs"></i>
                            Service Fee
                        </span>
                        <span class="font-semibold">{{ $symbol }}{{ number_format($purchaseFee, 2) }}</span>
                    </div>
                    @endif
                    <div class="border-t border-gray-100 pt-3 flex justify-between text-base font-bold">
                        <span class="text-gray-800">Total Due Now</span>
                        <span class="text-primary text-lg">{{ $symbol }}{{ number_format($grandTotal, 2) }}</span>
                    </div>
                    <div class="flex items-center justify-between text-xs text-gray-400 mt-1">
                        <span>Rate: ¥1 = {{ $symbol }}{{ number_format($rate, 2) }}</span>
                        <span>Est. weight: ~{{ number_format($totalWeightKg, 2) }}kg</span>
                    </div>
                </div>
            </div>

            {{-- Weight / Shipping Note --}}
            <div class="bg-blue-50 border border-blue-200 rounded-2xl px-5 py-4 flex items-start gap-3">
                <i class="fas fa-ship text-blue-400 text-lg flex-shrink-0 mt-0.5"></i>
                <div>
                    <p class="text-sm font-bold text-blue-800">Sea Freight — How It Works</p>
                    <p class="text-xs text-blue-700 mt-1 leading-relaxed">
                        Estimated weight: <strong>~{{ number_format($totalWeightKg, 2) }}kg</strong>.
                        Your international shipping fee is calculated after your goods are weighed at our China warehouse — not now.
                        Sea freight is assigned by our team for all orders.
                    </p>
                </div>
            </div>

        </div>

        {{-- ── Right: Payment Methods ────────────────────────────────── --}}
        <div class="lg:sticky lg:top-24 space-y-4">

            <div class="bg-white rounded-2xl shadow-card overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100">
                    <h2 class="font-bold text-gray-800 text-sm flex items-center gap-2">
                        <i class="fas fa-credit-card text-primary text-xs"></i>
                        Payment Method
                    </h2>
                </div>

                <div class="p-4 space-y-3">

                    {{-- ── Wallet ──────────────────────────────────────────── --}}
                    <label class="block cursor-pointer">
                        <input type="radio" name="payment_method" value="wallet" x-model="paymentMethod" class="sr-only">
                        <div :class="paymentMethod === 'wallet' ? 'border-primary bg-primary/5 ring-2 ring-primary/20' : 'border-gray-200 hover:border-gray-300'"
                             class="border-2 rounded-xl p-4 transition active:scale-95">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 bg-primary/10 rounded-xl flex items-center justify-center">
                                        <i class="fas fa-wallet text-primary text-sm"></i>
                                    </div>
                                    <div>
                                        <p class="font-bold text-gray-800 text-sm">Pay from Wallet</p>
                                        <p class="text-xs text-gray-400">Balance: {{ $user->currencySymbol() }}{{ number_format($user->wallet_balance ?? 0, 2) }}</p>
                                    </div>
                                </div>
                                <div class="w-5 h-5 rounded-full border-2 flex items-center justify-center flex-shrink-0"
                                     :class="paymentMethod === 'wallet' ? 'border-primary bg-primary' : 'border-gray-300'">
                                    <div x-show="paymentMethod === 'wallet'" class="w-2 h-2 bg-white rounded-full"></div>
                                </div>
                            </div>
                            @if(($user->wallet_balance ?? 0) < $grandTotal)
                            <p class="text-xs text-amber-600 mt-2 flex items-center gap-1">
                                <i class="fas fa-exclamation-triangle text-[10px]"></i>
                                Insufficient balance.
                                <a href="{{ route('user.wallet.fund') }}" class="underline font-semibold">Top up wallet</a>
                            </p>
                            @endif
                        </div>
                    </label>

                    {{-- ── Klump ───────────────────────────────────────────── --}}
                    @if($klumpLive)
                    <label class="block cursor-pointer">
                        <input type="radio" name="payment_method" value="klump" x-model="paymentMethod" class="sr-only">
                        <div :class="paymentMethod === 'klump' ? 'border-green-500 bg-green-50 ring-2 ring-green-200' : 'border-gray-200 hover:border-green-300'"
                             class="border-2 rounded-xl p-4 transition active:scale-95">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 bg-green-100 rounded-xl flex items-center justify-center">
                                        <i class="fas fa-hand-holding-usd text-green-600 text-sm"></i>
                                    </div>
                                    <div>
                                        <p class="font-bold text-gray-800 text-sm">Klump — Pay in Instalments</p>
                                        <p class="text-xs text-gray-400">Split your payment interest-free</p>
                                    </div>
                                </div>
                                <div class="w-5 h-5 rounded-full border-2 flex items-center justify-center flex-shrink-0"
                                     :class="paymentMethod === 'klump' ? 'border-green-500 bg-green-500' : 'border-gray-300'">
                                    <div x-show="paymentMethod === 'klump'" class="w-2 h-2 bg-white rounded-full"></div>
                                </div>
                            </div>
                            @if($bnpl['klump']['env'] === 'test')
                            <span class="mt-2 inline-block text-[10px] bg-yellow-100 text-yellow-700 font-bold px-2 py-0.5 rounded-full">Test Mode</span>
                            @endif
                        </div>
                    </label>
                    @else
                    <div class="border-2 border-gray-100 rounded-xl p-4 opacity-50 cursor-not-allowed select-none">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 bg-gray-100 rounded-xl flex items-center justify-center">
                                    <i class="fas fa-hand-holding-usd text-gray-400 text-sm"></i>
                                </div>
                                <div>
                                    <p class="font-bold text-gray-600 text-sm">Klump BNPL</p>
                                    <p class="text-xs text-gray-400">Pay in instalments</p>
                                </div>
                            </div>
                            <span class="text-[10px] bg-gray-100 text-gray-500 font-bold px-2 py-1 rounded-full">
                                @if(!$bnpl['klump']['enabled']) Coming Soon
                                @elseif(empty($bnpl['klump']['public_key'])) Not Configured
                                @else Min {{ $symbol }}{{ number_format($bnpl['klump']['min_amount'], 0) }}
                                @endif
                            </span>
                        </div>
                    </div>
                    @endif

                    {{-- ── Credpal ─────────────────────────────────────────── --}}
                    @if($credpalLive)
                    <label class="block cursor-pointer">
                        <input type="radio" name="payment_method" value="credpal" x-model="paymentMethod" class="sr-only">
                        <div :class="paymentMethod === 'credpal' ? 'border-indigo-500 bg-indigo-50 ring-2 ring-indigo-200' : 'border-gray-200 hover:border-indigo-300'"
                             class="border-2 rounded-xl p-4 transition active:scale-95">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 bg-indigo-100 rounded-xl flex items-center justify-center">
                                        <i class="fas fa-credit-card text-indigo-600 text-sm"></i>
                                    </div>
                                    <div>
                                        <p class="font-bold text-gray-800 text-sm">Credpal — Credit Payment</p>
                                        <p class="text-xs text-gray-400">Buy now, pay later on credit</p>
                                    </div>
                                </div>
                                <div class="w-5 h-5 rounded-full border-2 flex items-center justify-center flex-shrink-0"
                                     :class="paymentMethod === 'credpal' ? 'border-indigo-500 bg-indigo-500' : 'border-gray-300'">
                                    <div x-show="paymentMethod === 'credpal'" class="w-2 h-2 bg-white rounded-full"></div>
                                </div>
                            </div>
                            @if($bnpl['credpal']['env'] === 'test')
                            <span class="mt-2 inline-block text-[10px] bg-yellow-100 text-yellow-700 font-bold px-2 py-0.5 rounded-full">Test Mode</span>
                            @endif
                        </div>
                    </label>
                    @else
                    <div class="border-2 border-gray-100 rounded-xl p-4 opacity-50 cursor-not-allowed select-none">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 bg-gray-100 rounded-xl flex items-center justify-center">
                                    <i class="fas fa-credit-card text-gray-400 text-sm"></i>
                                </div>
                                <div>
                                    <p class="font-bold text-gray-600 text-sm">Credpal</p>
                                    <p class="text-xs text-gray-400">Credit-based payment</p>
                                </div>
                            </div>
                            <span class="text-[10px] bg-gray-100 text-gray-500 font-bold px-2 py-1 rounded-full">
                                @if(!$bnpl['credpal']['enabled']) Coming Soon
                                @elseif(empty($bnpl['credpal']['public_key'])) Not Configured
                                @else Min {{ $symbol }}{{ number_format($bnpl['credpal']['min_amount'], 0) }}
                                @endif
                            </span>
                        </div>
                    </div>
                    @endif

                    {{-- ── ZiLLA ────────────────────────────────────────────── --}}
                    @if($zillaLive)
                    <label class="block cursor-pointer">
                        <input type="radio" name="payment_method" value="zilla" x-model="paymentMethod" class="sr-only">
                        <div :class="paymentMethod === 'zilla' ? 'border-violet-500 bg-violet-50 ring-2 ring-violet-200' : 'border-gray-200 hover:border-violet-300'"
                             class="border-2 rounded-xl p-4 transition active:scale-95">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 bg-violet-100 rounded-xl flex items-center justify-center">
                                        <i class="fas fa-bolt text-violet-600 text-sm"></i>
                                    </div>
                                    <div>
                                        <p class="font-bold text-gray-800 text-sm">ZiLLA — Buy Now Pay Later</p>
                                        <p class="text-xs text-gray-400">Flexible instalment payments</p>
                                    </div>
                                </div>
                                <div class="w-5 h-5 rounded-full border-2 flex items-center justify-center flex-shrink-0"
                                     :class="paymentMethod === 'zilla' ? 'border-violet-500 bg-violet-500' : 'border-gray-300'">
                                    <div x-show="paymentMethod === 'zilla'" class="w-2 h-2 bg-white rounded-full"></div>
                                </div>
                            </div>
                            @if($bnpl['zilla']['env'] === 'test')
                            <span class="mt-2 inline-block text-[10px] bg-yellow-100 text-yellow-700 font-bold px-2 py-0.5 rounded-full">Test Mode</span>
                            @endif
                        </div>
                    </label>
                    @else
                    <div class="border-2 border-gray-100 rounded-xl p-4 opacity-50 cursor-not-allowed select-none">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 bg-gray-100 rounded-xl flex items-center justify-center">
                                    <i class="fas fa-bolt text-gray-400 text-sm"></i>
                                </div>
                                <div>
                                    <p class="font-bold text-gray-600 text-sm">ZiLLA</p>
                                    <p class="text-xs text-gray-400">Buy now, pay later</p>
                                </div>
                            </div>
                            <span class="text-[10px] bg-gray-100 text-gray-500 font-bold px-2 py-1 rounded-full">
                                @if(!$bnpl['zilla']['enabled']) Coming Soon
                                @elseif(empty($bnpl['zilla']['public_key'])) Not Configured
                                @else Min {{ $symbol }}{{ number_format($bnpl['zilla']['min_amount'], 0) }}
                                @endif
                            </span>
                        </div>
                    </div>
                    @endif

                </div>{{-- end provider list --}}

                {{-- ── T&C + action buttons ────────────────────────────── --}}
                <div class="px-5 pb-5 space-y-3 border-t border-gray-100 pt-4">

                    <label class="flex items-start gap-2.5 cursor-pointer">
                        <input type="checkbox" id="tc-accept"
                            class="mt-0.5 w-3.5 h-3.5 flex-shrink-0 rounded accent-primary">
                        <span class="text-[11px] text-gray-400 leading-relaxed">
                            I agree to the
                            <a href="{{ route('terms') }}" target="_blank"
                               class="text-primary hover:underline font-semibold">Terms &amp; Conditions</a>.
                            No returns. Shipping fee charged separately after weighing.
                        </span>
                    </label>

                    {{-- Wallet --}}
                    <div x-show="paymentMethod === 'wallet'" x-transition>
                        <form action="{{ route('user.orders.checkout') }}" method="POST"
                              @submit="if(!document.getElementById('tc-accept').checked){$event.preventDefault();alert('Please accept the Terms & Conditions.');}else{loading=true;}"
                              id="wallet-form">
                            @csrf
                            <input type="hidden" name="shipping_method" value="sea_los">
                            <input type="hidden" name="terms_accepted" value="1">
                            <button type="submit" :disabled="loading"
                                class="w-full bg-accent hover:bg-accent-dark text-white font-bold py-3.5 rounded-xl text-sm transition btn-glow flex items-center justify-center gap-2">
                                <i class="fas" :class="loading ? 'fa-spinner fa-spin' : 'fa-lock'"></i>
                                <span x-text="loading ? 'Processing...' : 'Pay {{ $symbol }}{{ number_format($grandTotal, 2) }} from Wallet'"></span>
                            </button>
                        </form>
                    </div>

                    @if($klumpLive)
                    {{-- Klump --}}
                    <div x-show="paymentMethod === 'klump'" x-transition>
                        <button type="button" onclick="initKlump()"
                            class="w-full bg-green-600 hover:bg-green-700 text-white font-bold py-3.5 rounded-xl text-sm transition flex items-center justify-center gap-2">
                            <i class="fas fa-hand-holding-usd"></i>
                            Pay {{ $symbol }}{{ number_format($grandTotal, 2) }} with Klump
                        </button>
                        <p class="text-[10px] text-gray-400 text-center mt-1.5">You'll be prompted to set up your instalment plan</p>
                    </div>
                    @endif

                    @if($credpalLive)
                    {{-- Credpal --}}
                    <div x-show="paymentMethod === 'credpal'" x-transition>
                        <button type="button" onclick="initCredpal()"
                            class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3.5 rounded-xl text-sm transition flex items-center justify-center gap-2">
                            <i class="fas fa-credit-card"></i>
                            Pay {{ $symbol }}{{ number_format($grandTotal, 2) }} with Credpal
                        </button>
                        <p class="text-[10px] text-gray-400 text-center mt-1.5">Powered by Credpal — subject to credit check</p>
                    </div>
                    @endif

                    @if($zillaLive)
                    {{-- ZiLLA --}}
                    <div x-show="paymentMethod === 'zilla'" x-transition>
                        <button type="button" onclick="initZilla()"
                            class="w-full bg-violet-600 hover:bg-violet-700 text-white font-bold py-3.5 rounded-xl text-sm transition flex items-center justify-center gap-2">
                            <i class="fas fa-bolt"></i>
                            Pay {{ $symbol }}{{ number_format($grandTotal, 2) }} with ZiLLA
                        </button>
                        <p class="text-[10px] text-gray-400 text-center mt-1.5">Flexible instalments via ZiLLA</p>
                    </div>
                    @endif

                </div>
            </div>

        </div>
    </div>

    {{-- Loading overlay --}}
    <div x-show="loading" x-transition
         class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 backdrop-blur-sm">
        <div class="bg-white rounded-2xl p-8 text-center shadow-2xl">
            <div class="w-14 h-14 bg-primary/10 rounded-2xl flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-spinner fa-spin text-primary text-2xl"></i>
            </div>
            <p class="font-bold text-gray-800 text-base">Processing your order…</p>
            <p class="text-xs text-gray-400 mt-1">Please do not close this page.</p>
        </div>
    </div>

</div>

{{-- Hidden success forms for each BNPL provider --}}
<form id="klump-form" action="{{ route('user.orders.klump-complete') }}" method="POST" class="hidden">
    @csrf
    <input type="hidden" id="klump-ref" name="klump_reference">
</form>
<form id="credpal-form" action="{{ route('user.orders.credpal-complete') }}" method="POST" class="hidden">
    @csrf
    <input type="hidden" id="credpal-ref" name="credpal_reference">
</form>
<form id="zilla-form" action="{{ route('user.orders.zilla-complete') }}" method="POST" class="hidden">
    @csrf
    <input type="hidden" id="zilla-ref" name="zilla_reference">
</form>

{{-- BNPL JS initialisers --}}
<script>
@if($klumpLive)
function initKlump() {
    if (!document.getElementById('tc-accept').checked) {
        alert('Please accept the Terms & Conditions first.');
        return;
    }
    var klump = new Klump({
        publicKey: '{{ $bnpl['klump']['public_key'] }}',
        data: {
            amount:    {{ round($grandTotal * 100) }},
            currency:  '{{ $user->currency }}',
            reference: 'BYB-' + Date.now(),
            customer: {
                email: '{{ $user->email }}',
                name:  '{{ addslashes($user->name) }}',
                phone: '{{ $user->phone ?? '' }}'
            }
        },
        onSuccess: function(data) {
            document.getElementById('klump-ref').value = data.reference;
            document.getElementById('klump-form').submit();
        },
        onClose: function() {}
    });
}
@else
function initKlump() { alert('Klump is not available. Please use another payment method.'); }
@endif

@if($credpalLive)
function initCredpal() {
    if (!document.getElementById('tc-accept').checked) {
        alert('Please accept the Terms & Conditions first.');
        return;
    }
    // Credpal standard integration — loads their hosted checkout
    var ref = 'BYB-CP-' + Date.now();
    var cpConfig = {
        key:      '{{ $bnpl['credpal']['public_key'] }}',
        email:    '{{ $user->email }}',
        amount:   {{ $grandTotal }},
        currency: '{{ $user->currency }}',
        ref:      ref,
        onSuccess: function(response) {
            document.getElementById('credpal-ref').value = response.reference || ref;
            document.getElementById('credpal-form').submit();
        },
        onClose: function() {}
    };
    if (typeof CredpalCheckout !== 'undefined') {
        var handler = CredpalCheckout.setup(cpConfig);
        handler.openIframe();
    } else {
        // Load SDK on-demand
        var s = document.createElement('script');
        s.src = 'https://js.credpal.com/v1/checkout.js';
        s.onload = function() {
            var handler = CredpalCheckout.setup(cpConfig);
            handler.openIframe();
        };
        document.body.appendChild(s);
    }
}
@else
function initCredpal() { alert('Credpal is not available. Please use another payment method.'); }
@endif

@if($zillaLive)
function initZilla() {
    if (!document.getElementById('tc-accept').checked) {
        alert('Please accept the Terms & Conditions first.');
        return;
    }
    var ref = 'BYB-ZL-' + Date.now();
    var zlConfig = {
        publicKey:  '{{ $bnpl['zilla']['public_key'] }}',
        email:      '{{ $user->email }}',
        firstName:  '{{ addslashes(explode(' ', $user->name)[0] ?? $user->name) }}',
        lastName:   '{{ addslashes(explode(' ', $user->name)[1] ?? '') }}',
        phone:      '{{ $user->phone ?? '' }}',
        amount:     {{ $grandTotal }},
        currency:   '{{ $user->currency }}',
        reference:  ref,
        onSuccess: function(response) {
            document.getElementById('zilla-ref').value = response.reference || ref;
            document.getElementById('zilla-form').submit();
        },
        onClose: function() {}
    };
    if (typeof ZillaCheckout !== 'undefined') {
        ZillaCheckout.init(zlConfig);
    } else {
        var s = document.createElement('script');
        s.src = 'https://js.zilla.africa/v1/zilla.js';
        s.onload = function() { ZillaCheckout.init(zlConfig); };
        document.body.appendChild(s);
    }
}
@else
function initZilla() { alert('ZiLLA is not available. Please use another payment method.'); }
@endif
</script>

@endsection
