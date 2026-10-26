@extends('layouts.app')
@section('title', 'Checkout')

@push('scripts')
<script src="https://js.useklump.com/klump.js" defer></script>
@endpush

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
                        <span class="text-primary text-lg">{{ $symbol }}{{ number_format($pricing['grand_total'], 2) }}</span>
                    </div>
                    <div class="flex items-center justify-between text-xs text-gray-400 mt-1">
                        <span>Rate: ¥1 = {{ $symbol }}{{ number_format($rate, 2) }}</span>
                        <span>Est. weight: ~{{ number_format($totalWeightKg, 2) }}kg</span>
                    </div>
                </div>
            </div>

            {{-- Estimated Weight Explanation --}}
            <div class="bg-blue-50 border border-blue-200 rounded-2xl px-5 py-4 flex items-start gap-3">
                <i class="fas fa-weight-hanging text-blue-400 text-lg flex-shrink-0 mt-0.5"></i>
                <div>
                    <p class="text-sm font-bold text-blue-800">About Shipping Weight</p>
                    <p class="text-xs text-blue-700 mt-1 leading-relaxed">
                        The estimated weight of <strong>~{{ number_format($totalWeightKg, 2) }}kg</strong> is based on product data.
                        Your actual international shipping fee will be calculated after your goods are weighed at our China warehouse.
                        Sea freight applies — ideal for heavy or bulk orders.
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

                    {{-- Wallet --}}
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
                            @php $grandTotal = $pricing['grand_total']; @endphp
                            @if(($user->wallet_balance ?? 0) < $grandTotal)
                            <p class="text-xs text-amber-600 mt-2 flex items-center gap-1">
                                <i class="fas fa-exclamation-triangle text-[10px]"></i>
                                Insufficient balance.
                                <a href="{{ route('user.wallet.fund') }}" class="underline font-semibold">Top up wallet</a>
                            </p>
                            @endif
                        </div>
                    </label>

                    {{-- Klump BNPL --}}
                    <label class="block cursor-pointer">
                        <input type="radio" name="payment_method" value="klump" x-model="paymentMethod" class="sr-only">
                        <div :class="paymentMethod === 'klump' ? 'border-primary bg-primary/5 ring-2 ring-primary/20' : 'border-gray-200 hover:border-gray-300'"
                             class="border-2 rounded-xl p-4 transition active:scale-95">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 bg-green-100 rounded-xl flex items-center justify-center">
                                        <i class="fas fa-hand-holding-usd text-green-600 text-sm"></i>
                                    </div>
                                    <div>
                                        <p class="font-bold text-gray-800 text-sm">BNPL via Klump</p>
                                        <p class="text-xs text-gray-400">Pay in instalments</p>
                                    </div>
                                </div>
                                <div class="w-5 h-5 rounded-full border-2 flex items-center justify-center flex-shrink-0"
                                     :class="paymentMethod === 'klump' ? 'border-primary bg-primary' : 'border-gray-300'">
                                    <div x-show="paymentMethod === 'klump'" class="w-2 h-2 bg-white rounded-full"></div>
                                </div>
                            </div>
                        </div>
                    </label>

                    {{-- Zilla (coming soon) --}}
                    <div class="border-2 border-gray-100 rounded-xl p-4 opacity-60 cursor-not-allowed">
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
                            <span class="text-[10px] bg-gray-100 text-gray-500 font-bold px-2 py-1 rounded-full">Coming Soon</span>
                        </div>
                    </div>

                    {{-- Credpal (coming soon) --}}
                    <div class="border-2 border-gray-100 rounded-xl p-4 opacity-60 cursor-not-allowed">
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
                            <span class="text-[10px] bg-gray-100 text-gray-500 font-bold px-2 py-1 rounded-full">Coming Soon</span>
                        </div>
                    </div>

                </div>

                {{-- T&C --}}
                <div class="px-5 pb-4 space-y-3">
                    <label class="flex items-start gap-2.5 cursor-pointer" x-data="{ accepted: false }">
                        <input type="checkbox" x-model="accepted"
                            class="mt-0.5 w-3.5 h-3.5 flex-shrink-0 rounded accent-primary">
                        <span class="text-[11px] text-gray-400 leading-relaxed">
                            I agree to the
                            <a href="{{ route('terms') }}" target="_blank"
                               class="text-primary hover:underline font-semibold">Terms &amp; Conditions</a>.
                            No returns on purchased goods. Shipping fee charged separately after weighing.
                        </span>
                    </label>

                    {{-- Wallet Payment --}}
                    <div x-show="paymentMethod === 'wallet'" x-transition>
                        <form action="{{ route('user.orders.checkout') }}" method="POST" @submit="loading=true" id="wallet-form">
                            @csrf
                            <input type="hidden" name="shipping_method" value="sea_los">
                            <input type="hidden" name="terms_accepted" value="1">
                            <textarea name="buyer_note" class="hidden" id="wallet-note"></textarea>
                            <button type="submit" :disabled="loading"
                                class="w-full bg-accent hover:bg-accent-dark text-white font-bold py-3.5 rounded-xl text-sm transition btn-glow flex items-center justify-center gap-2">
                                <i class="fas" :class="loading ? 'fa-spinner fa-spin' : 'fa-lock'"></i>
                                <span x-text="loading ? 'Processing...' : 'Pay from Wallet'"></span>
                            </button>
                        </form>
                    </div>

                    {{-- Klump Payment --}}
                    <div x-show="paymentMethod === 'klump'" x-transition>
                        <button type="button"
                            onclick="initKlump()"
                            class="w-full bg-green-600 hover:bg-green-700 text-white font-bold py-3.5 rounded-xl text-sm transition flex items-center justify-center gap-2">
                            <i class="fas fa-hand-holding-usd"></i>
                            Pay with Klump BNPL
                        </button>
                        <p class="text-[10px] text-gray-400 text-center mt-1.5">You will be prompted to set up your instalment plan</p>
                    </div>

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

{{-- Klump hidden success form --}}
<form id="klump-form" action="{{ route('user.orders.klump-complete') }}" method="POST" class="hidden">
    @csrf
    <input type="hidden" id="klump-ref" name="klump_reference">
</form>

{{-- Klump initialiser --}}
<script>
function initKlump() {
    @if(config('bybhub.klump.public_key'))
    var klump = new Klump({
        publicKey: '{{ config('bybhub.klump.public_key') }}',
        data: {
            amount: {{ round($pricing['grand_total'] * 100) }},
            currency: '{{ $user->currency }}',
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
    @else
    alert('Klump is not configured yet. Please use Wallet payment or contact support.');
    @endif
}
</script>
@endsection
