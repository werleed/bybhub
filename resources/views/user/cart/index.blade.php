@extends('layouts.app')
@section('title', __('app.cart'))
@section('content')

<div x-data="{ loading: false }" class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">

    {{-- ── Cart Items ───────────────────────────────────────────────── --}}
    <div class="lg:col-span-2 space-y-4">

        {{-- Header --}}
        <div class="flex justify-between items-center">
            <h2 class="text-xl font-bold text-primary flex items-center gap-2" style="font-family:Poppins,sans-serif">
                <i class="fas fa-shopping-cart text-base"></i>
                Cart
                <span class="text-gray-400 font-normal text-base">({{ $items->count() }})</span>
            </h2>
            @if($items->isNotEmpty())
            <form action="{{ route('user.cart.clear') }}" method="POST">
                @csrf @method('DELETE')
                <button class="text-xs text-red-400 hover:text-red-600 font-medium transition flex items-center gap-1">
                    <i class="fas fa-trash text-[10px]"></i> Clear Cart
                </button>
            </form>
            @endif
        </div>

        {{-- Empty state --}}
        @if($items->isEmpty())
        <div class="bg-white rounded-3xl shadow-card p-14 text-center text-gray-400">
            <div class="w-20 h-20 bg-gray-100 rounded-3xl flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-shopping-cart text-3xl text-gray-300"></i>
            </div>
            <p class="font-bold text-gray-600 text-base">Your cart is empty</p>
            <p class="text-sm mt-1 mb-5">Browse our catalogue and add items to your cart</p>
            <a href="{{ route('products.index') }}"
               class="inline-flex items-center gap-2 bg-accent text-white font-bold px-6 py-3 rounded-2xl text-sm btn-glow transition">
                <i class="fas fa-store"></i> Browse Products
            </a>
        </div>

        {{-- Items --}}
        @else
        <div class="space-y-3">
            @foreach($items as $item)
            <div class="bg-white rounded-2xl shadow-card p-4 flex gap-4
                {{ $item->isOutOfStock() ? 'border-2 border-red-200 bg-red-50/30' : '' }}">
                <img src="{{ $item->getImage() }}" alt="{{ $item->getTitle() }}"
                    loading="lazy"
                    class="w-20 h-20 object-cover rounded-xl flex-shrink-0 border border-gray-100"
                    onerror="this.src='{{ asset('images/placeholder.svg') }}'">
                <div class="flex-1 min-w-0">
                    <div class="flex justify-between gap-2">
                        <div class="min-w-0">
                            <h3 class="font-semibold text-gray-800 text-sm line-clamp-2 leading-snug">{{ $item->getTitle() }}</h3>
                            @if($item->selected_variant)
                            <p class="text-xs text-gray-400 mt-0.5">{{ $item->selected_variant }}</p>
                            @endif
                            @if($item->isOutOfStock())
                            <p class="text-xs text-red-500 font-semibold mt-1 flex items-center gap-1">
                                <i class="fas fa-exclamation-triangle text-[10px]"></i> Out of Stock — remove to continue
                            </p>
                            @endif
                        </div>
                        <form action="{{ route('user.cart.remove', $item->id) }}" method="POST" class="flex-shrink-0 ml-1">
                            @csrf @method('DELETE')
                            <button class="w-7 h-7 bg-red-50 hover:bg-red-100 text-red-400 hover:text-red-600 rounded-lg flex items-center justify-center transition">
                                <i class="fas fa-times text-xs"></i>
                            </button>
                        </form>
                    </div>
                    <div class="flex items-center justify-between mt-3">
                        <div>
                            <p class="font-black text-accent text-sm" style="font-family:Poppins,sans-serif">
                                {{ $symbol }}{{ number_format($item->getLineTotalLocal($user->currency), 2) }}
                            </p>
                            @if(($item->product?->domestic_freight_cny ?? 0) > 0)
                            <p class="text-[10px] text-amber-600 flex items-center gap-1 mt-0.5">
                                <i class="fas fa-warehouse text-[9px]"></i>
                                +{{ $symbol }}{{ number_format($item->product->domestic_freight_cny * $rate, 2) }} local freight
                            </p>
                            @endif
                            <p class="text-[10px] text-gray-400 mt-0.5">
                                Subtotal: {{ $symbol }}{{ number_format($item->unit_price_cny * $rate * $item->quantity, 2) }}
                            </p>
                        </div>
                        <div class="flex flex-col items-end gap-2">
                            <form action="{{ route('user.cart.update', $item->id) }}" method="POST" class="flex items-center gap-2">
                                @csrf @method('PATCH')
                                <button type="button"
                                    onclick="let i=this.nextElementSibling;i.value=Math.max(1,parseInt(i.value)-1);this.form.submit()"
                                    class="w-8 h-8 bg-gray-100 hover:bg-gray-200 rounded-lg font-bold flex items-center justify-center transition active:scale-95">−</button>
                                <input type="number" name="quantity" value="{{ $item->quantity }}" min="1"
                                    class="w-12 text-center border-2 border-gray-100 rounded-lg py-1.5 text-sm font-bold focus:outline-none focus:border-primary"
                                    onchange="this.form.submit()">
                                <button type="button"
                                    onclick="let i=this.previousElementSibling;i.value=parseInt(i.value)+1;this.form.submit()"
                                    class="w-8 h-8 bg-gray-100 hover:bg-gray-200 rounded-lg font-bold flex items-center justify-center transition active:scale-95">+</button>
                            </form>
                            {{-- Save for Later --}}
                            <form action="{{ route('user.cart.save-later', $item->id) }}" method="POST">
                                @csrf
                                <button type="submit"
                                    class="text-[11px] text-blue-500 hover:text-blue-700 font-medium flex items-center gap-1 transition">
                                    <i class="fas fa-bookmark text-[10px]"></i> Save for Later
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        {{-- ── Saved for Later Section ─────────────────────────────────── --}}
        @php $savedItems = session('saved_later', []); @endphp
        @if(!empty($savedItems))
        <div x-data="{ open: true }" class="bg-white rounded-2xl shadow-card overflow-hidden">
            <button type="button" @click="open=!open"
                class="w-full flex items-center justify-between px-5 py-4 border-b border-gray-100 text-left">
                <h3 class="font-bold text-gray-700 text-sm flex items-center gap-2">
                    <i class="fas fa-bookmark text-blue-400 text-xs"></i>
                    Saved for Later
                    <span class="text-xs bg-blue-100 text-blue-600 rounded-full px-2 py-0.5 font-semibold">{{ count($savedItems) }}</span>
                </h3>
                <i class="fas text-gray-400 text-xs" :class="open ? 'fa-chevron-up' : 'fa-chevron-down'"></i>
            </button>
            <div x-show="open" x-transition class="divide-y divide-gray-50">
                @foreach($savedItems as $savedId => $savedData)
                <div class="flex gap-3 px-5 py-3 items-center">
                    <img src="{{ $savedData['image'] }}" alt="{{ $savedData['title'] }}"
                        loading="lazy"
                        class="w-14 h-14 object-cover rounded-xl flex-shrink-0 border border-gray-100"
                        onerror="this.src='{{ asset('images/placeholder.svg') }}'">
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold text-gray-800 line-clamp-1">{{ $savedData['title'] }}</p>
                        @if(!empty($savedData['selected_variant']))
                        <p class="text-xs text-gray-400">{{ $savedData['selected_variant'] }}</p>
                        @endif
                        <p class="text-xs text-gray-500 mt-0.5">Qty: {{ $savedData['quantity'] }} · {{ $symbol }}{{ number_format($savedData['unit_price_cny'] * $rate * $savedData['quantity'], 2) }}</p>
                    </div>
                    <form action="{{ route('user.cart.move-to-cart', $savedId) }}" method="POST" class="flex-shrink-0">
                        @csrf
                        <button type="submit"
                            class="text-xs bg-primary/10 hover:bg-primary/20 text-primary font-bold px-3 py-1.5 rounded-lg transition whitespace-nowrap">
                            Move to Cart
                        </button>
                    </form>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        @endif

    </div>

    {{-- ── Order Summary ────────────────────────────────────────────── --}}
    @if($items->isNotEmpty())
    <div class="lg:sticky lg:top-24">
        <div class="bg-white rounded-2xl shadow-card overflow-hidden"
             x-data="{ couponCode: '', couponMsg: '', couponValid: false, couponDiscount: 0 }">

            {{-- Title bar --}}
            <div class="px-5 pt-5 pb-4 border-b border-gray-100">
                <h3 class="font-bold text-primary flex items-center gap-2 text-sm">
                    <i class="fas fa-receipt"></i> Order Summary
                </h3>
            </div>

            {{-- Shipping method — Sea only --}}
            <div class="px-5 py-4 border-b border-gray-100">
                <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Shipping Method</p>
                <div class="mt-1 flex items-start gap-1.5 text-[11px] text-blue-700 bg-blue-50 border border-blue-200 rounded-xl px-3 py-2.5">
                    <i class="fas fa-ship text-blue-400 flex-shrink-0 mt-0.5"></i>
                    <span>Shipping method is selected by our team based on your goods. Sea freight is used by default.</span>
                </div>
                <div class="mt-2 flex items-start gap-1.5 text-[11px] text-amber-700 bg-amber-50 border border-amber-200 rounded-xl px-3 py-2">
                    <i class="fas fa-weight-hanging text-amber-400 flex-shrink-0 mt-0.5"></i>
                    <span>Shipping fee is charged <strong>after weighing</strong> at our China warehouse. Estimated weight: ~{{ number_format($totalWeightKg, 2) }}kg.</span>
                </div>
            </div>

            <div x-data="{ couponCode: '', couponMsg: '', couponValid: false, couponDiscount: 0 }">
            {{-- Coupon --}}
            <div class="px-5 py-4 border-b border-gray-100">
                <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-3">Coupon Code</p>
                <div class="flex gap-2">
                    <input type="text" x-model="couponCode"
                        placeholder="Enter code"
                        class="flex-1 border-2 border-gray-100 bg-gray-50 rounded-xl px-3 py-2 text-sm focus:outline-none focus:border-primary transition uppercase font-mono tracking-widest">
                    <button type="button"
                        @click="fetch('{{ route('user.coupon.validate') }}',{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}'},body:JSON.stringify({code:couponCode})}).then(r=>r.json()).then(d=>{ couponMsg = d.valid ? ('✓ Coupon applied! You save {{ $symbol }}' + parseFloat(d.discount||0).toFixed(2)) : (d.message||'Invalid coupon'); couponValid = d.valid; couponDiscount = d.valid ? parseFloat(d.discount||0) : 0; })"
                        class="bg-primary hover:bg-primary-dark text-white px-3 py-2 rounded-xl text-xs font-bold transition whitespace-nowrap">
                        Apply
                    </button>
                </div>
                <p x-show="couponMsg"
                   :class="couponValid ? 'text-green-600' : 'text-red-500'"
                   class="text-xs mt-1.5 font-medium" x-text="couponMsg"></p>
            </div>

            {{-- Price breakdown --}}
            <div class="px-5 py-4 space-y-2.5 border-b border-gray-100">
                <div class="flex justify-between text-sm">
                    <span class="text-gray-500">Products (CNY)</span>
                    <span class="font-semibold text-gray-800">¥{{ number_format($totalCny, 2) }}</span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-gray-500">Products ({{ $user->currency }})</span>
                    <span class="font-semibold text-gray-800">{{ $symbol }}{{ number_format($totalLocal, 2) }}</span>
                </div>

                @if($domFreightCny > 0)
                <div class="flex justify-between text-sm">
                    <span class="text-gray-500 flex items-center gap-1.5">
                        <i class="fas fa-warehouse text-gray-400 text-xs"></i>
                        China Local Freight
                        <span class="text-[10px] bg-amber-100 text-amber-700 rounded px-1.5 py-0.5 font-medium">supplier → warehouse</span>
                    </span>
                    <span class="font-semibold text-gray-800">
                        {{ $symbol }}{{ number_format($domFreightLocal, 2) }}
                        <span class="text-xs text-gray-400 font-normal">(¥{{ number_format($domFreightCny, 2) }})</span>
                    </span>
                </div>
                @endif

                @if($purchaseFee > 0)
                <div class="flex justify-between text-sm">
                    <span class="text-gray-500 flex items-center gap-1.5">
                        <i class="fas fa-concierge-bell text-gray-400 text-xs"></i>
                        Service Fee
                        <span class="text-[10px] bg-blue-100 text-blue-700 rounded px-1.5 py-0.5 font-medium">{{ round(($purchaseFee / max($totalLocal, 0.01)) * 100, 1) }}%</span>
                    </span>
                    <span class="font-semibold text-gray-800">{{ $symbol }}{{ number_format($purchaseFee, 2) }}</span>
                </div>
                @endif

                {{-- Coupon discount line --}}
                <div x-show="couponValid && couponDiscount > 0" class="flex justify-between text-sm text-green-600">
                    <span class="flex items-center gap-1.5">
                        <i class="fas fa-tag text-green-400 text-xs"></i>
                        Coupon Discount
                        <span class="text-[10px] bg-green-100 text-green-700 rounded px-1.5 py-0.5 font-medium uppercase font-mono" x-text="couponCode"></span>
                    </span>
                    <span class="font-semibold" x-text="'- {{ $symbol }}' + couponDiscount.toFixed(2)"></span>
                </div>

                <div class="border-t border-gray-100 pt-2.5 flex justify-between text-sm font-bold">
                    <span class="text-gray-700">Est. Total</span>
                    <span class="text-primary"
                          x-text="'{{ $symbol }}' + Math.max(0, {{ $totalLocal + $domFreightLocal + $purchaseFee }} - couponDiscount).toFixed(2)">
                        {{ $symbol }}{{ number_format($totalLocal + $domFreightLocal + $purchaseFee, 2) }}
                    </span>
                </div>

                <div class="flex justify-between text-xs text-gray-400">
                    <span>Rate: ¥1 = {{ $symbol }}{{ number_format($rate, 2) }}</span>
                    <span>~{{ number_format($totalWeightKg, 2) }}kg · Sea Shipping Only</span>
                </div>

                <div class="bg-amber-50 border border-amber-100 rounded-xl px-3 py-2 text-xs text-amber-700 flex items-start gap-1.5">
                    <i class="fas fa-truck flex-shrink-0 mt-0.5 text-amber-400"></i>
                    <span>International shipping fee is charged separately after weighing at our China warehouse.</span>
                </div>
            </div>

            {{-- T&C + CTA --}}
            <div class="px-5 py-4 space-y-3">
                {{-- Buyer Note (kept for wallet checkout later) --}}
                <div class="bg-amber-50 border border-amber-200 rounded-xl p-4">
                    <label class="block text-xs font-bold text-gray-600 uppercase tracking-wider mb-1.5 flex items-center gap-1.5">
                        <i class="fas fa-sticky-note text-amber-500 text-xs"></i>
                        Order Note <span class="font-normal text-gray-400 text-[11px] normal-case">(optional)</span>
                    </label>
                    <textarea id="cart-note" rows="2"
                        placeholder="e.g. I want red color, size L — or — please confirm availability before processing"
                        class="w-full border-2 border-amber-200 bg-white rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-amber-400 transition resize-none">{{ old('buyer_note') }}</textarea>
                    <p class="text-[10px] text-amber-600 mt-1">This note goes directly to our procurement team with your order.</p>
                </div>

                <a href="{{ route('user.checkout') }}"
                    @click="loading=true"
                    class="w-full bg-accent hover:bg-accent-dark text-white font-bold py-3.5 rounded-xl text-sm transition btn-glow flex items-center justify-center gap-2">
                    <i class="fas fa-arrow-right"></i>
                    Proceed to Checkout
                </a>

                <p class="text-[11px] text-gray-400 text-center flex items-center justify-center gap-1.5">
                    <i class="fas fa-shield-alt text-gray-300"></i>
                    Secure checkout · Wallet or Instalments
                </p>
            </div>
            </div>
        </div>
    </div>
    @endif

</div>

{{-- ── Mobile sticky bottom checkout bar ─────────────────────────────────── --}}
@if($items->isNotEmpty())
<div class="sm:hidden fixed bottom-16 left-0 right-0 bg-white border-t border-gray-200 px-4 py-3 flex items-center justify-between z-50 shadow-lg">
    <div>
        <p class="text-xs text-gray-400 font-medium">Est. Total</p>
        <p class="font-black text-primary text-base" style="font-family:Poppins,sans-serif">
            {{ $symbol }}{{ number_format($totalLocal + $domFreightLocal + $purchaseFee, 2) }}
        </p>
    </div>
    <a href="{{ route('user.checkout') }}"
        class="bg-accent hover:bg-accent-dark text-white font-bold px-6 py-3 rounded-xl text-sm flex items-center gap-2 btn-glow transition">
        <i class="fas fa-arrow-right text-xs"></i> Checkout
    </a>
</div>
<div class="h-20 sm:hidden"></div>{{-- spacer to avoid content hiding behind sticky bar --}}
@endif

@endsection
