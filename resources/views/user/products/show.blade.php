@extends('layouts.app')
@section('title', $product->title)
@section('meta_description', \Illuminate\Support\Str::limit(strip_tags($product->description ?? ''), 155) ?: 'Buy ' . $product->title . ' from China via BYBHUB — factory prices, shipped to Nigeria.')

@section('og_type', 'product')
@section('og_title', $product->title . ' — BYBHUB')
@section('og_description', \Illuminate\Support\Str::limit(strip_tags($product->description ?? ''), 200) ?: 'Buy ' . $product->title . ' direct from China. Factory price ¥' . number_format($product->price_cny, 2) . '. Shipped to Nigeria via BYBHUB.')
@php
    // Use the first real image URL — avoid proxied URLs for OG (social crawlers can't follow JS-redirected proxies)
    $ogImage = asset('images/og-cover.jpg');
    if (!empty($product->images)) {
        $firstImg = is_array($product->images) ? ($product->images[0] ?? '') : '';
        if ($firstImg && str_starts_with($firstImg, 'http')) {
            $ogImage = $firstImg; // direct CDN URL works for social crawlers
        }
    }
@endphp
@section('og_image', $ogImage)
@section('content')
<div x-data="{ mainImg: '{{ $product->getMainImage() }}', variant: '' }">

    {{-- Breadcrumb --}}
    <div class="flex items-center gap-2 text-xs text-gray-400 mb-4">
        <a href="{{ route('products.index') }}" class="hover:text-primary transition font-medium">Products</a>
        @if($product->category)
        <i class="fas fa-chevron-right text-[10px]"></i>
        <a href="{{ route('products.category',$product->category->slug) }}" class="hover:text-primary transition">{{ $product->category->getTranslatedName() }}</a>
        @endif
        <i class="fas fa-chevron-right text-[10px]"></i>
        <span class="text-gray-600 truncate max-w-40">{{ Str::limit($product->title, 40) }}</span>
    </div>

    <div class="bg-white rounded-3xl shadow-card p-6 md:p-8 mb-7">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            {{-- Image Gallery --}}
            <div>
                <div class="aspect-square rounded-2xl overflow-hidden border-2 border-gray-100 bg-gray-50 mb-3">
                    <img :src="mainImg" alt="{{ $product->title }}"
                        class="w-full h-full object-contain"
                        onerror="this.src='{{ asset('images/placeholder.svg') }}'">
                </div>
                @if(!empty($product->images) && count($product->images) > 1)
                <div class="flex gap-2 overflow-x-auto pb-1 scrollbar-hide">
                    @foreach($product->images as $img)
                    @php $proxied = $product->proxyImage($img); @endphp
                    <button @click="mainImg='{{ addslashes($proxied) }}'"
                        class="flex-shrink-0 w-16 h-16 rounded-xl overflow-hidden border-2 transition"
                        :class="mainImg==='{{ addslashes($proxied) }}' ? 'border-primary' : 'border-gray-200 hover:border-primary/50'">
                        <img src="{{ $proxied }}" class="w-full h-full object-cover" onerror="this.parentElement.style.display='none'">
                    </button>
                    @endforeach
                </div>
                @endif
            </div>

            {{-- Details --}}
            <div class="space-y-5">
                @if($product->category)
                <a href="{{ route('products.category',$product->category->slug) }}"
                    class="inline-flex items-center gap-1 text-xs text-accent font-bold hover:underline">
                    <i class="fas fa-tag text-[10px]"></i> {{ $product->category->getTranslatedName() }}
                </a>
                @endif

                <div>
                    <h1 class="text-xl font-bold text-gray-800 leading-snug" style="font-family:Poppins,sans-serif">{{ $product->title }}</h1>
                    @if($product->vendor)
                    {{-- Local vendor product --}}
                    <div class="flex items-center gap-2 mt-2 flex-wrap">
                        <a href="{{ route('store.show', $product->vendor->store_slug) }}"
                            class="inline-flex items-center gap-2 bg-accent/10 hover:bg-accent/20 border border-accent/30 text-accent font-bold text-xs px-3 py-1.5 rounded-xl transition">
                            @if($product->vendor->store_logo)
                            <img src="{{ Storage::url($product->vendor->store_logo) }}" class="w-4 h-4 rounded-full object-cover">
                            @else
                            <i class="fas fa-store text-[10px]"></i>
                            @endif
                            {{ $product->vendor->store_name }}
                            <i class="fas fa-arrow-right text-[9px] opacity-60"></i>
                        </a>
                        <span class="text-[10px] bg-green-100 text-green-700 font-bold px-2 py-1 rounded-full flex items-center gap-1">
                            <i class="fas fa-shield-alt text-[9px]"></i> Verified Vendor
                        </span>
                        <span class="text-[10px] bg-blue-100 text-blue-700 font-bold px-2 py-1 rounded-full flex items-center gap-1">
                            <i class="fas fa-map-marker-alt text-[9px]"></i> {{ $product->vendor->business_city }}, Nigeria
                        </span>
                    </div>
                    @elseif($product->seller_name)
                    <p class="text-xs text-gray-400 mt-1">Sold by: {{ $product->seller_name }}</p>
                    @endif
                </div>

                @php
                    // Pass all PHP data to JS via a script tag — avoids HTML attribute
                    // corruption caused by JSON containing > < & characters from $skus->toJson()
                @endphp
                <script>
                window._productPriceData = {
                    basePriceCny:   {{ (float)$product->price_cny }},
                    basePriceLocal: {{ $priceLocal }},
                    symbol:         @json($symbol),
                    rate:           {{ $rate }},
                    skus:           {!! $skus->toJson() !!},
                    tiers:          {!! json_encode($product->price_tiers ?? []) !!},
                    moq:            {{ (int)($product->moq ?? 1) }}
                };
                </script>

                <div class="bg-gradient-to-r from-primary-50 to-blue-50 border border-primary/10 rounded-2xl p-4"
                     x-data="productPriceBlock()">
                    @if($product->vendor && $product->price_ngn)
                    {{-- Local vendor: NGN only --}}
                    <p class="text-3xl font-black text-primary" style="font-family:Poppins,sans-serif">₦{{ number_format((float)$product->price_ngn, 0) }}</p>
                    <p class="text-xs text-green-600 font-bold mt-1 flex items-center gap-1">
                        <i class="fas fa-truck text-[10px]"></i> Fast local delivery available
                    </p>

                    @elseif($product->isFlashSaleActive())
                    {{-- Flash sale --}}
                    <div class="flex items-baseline gap-3 flex-wrap">
                        <p class="text-3xl font-black text-red-500" style="font-family:Poppins,sans-serif">{{ $symbol }}{{ number_format($priceLocal,2) }}</p>
                        <p class="text-base text-gray-400 line-through">{{ $symbol }}{{ number_format(\App\Models\ExchangeRate::convert($product->price_cny,$currency),2) }}</p>
                        <span class="text-xs bg-red-100 text-red-600 font-black px-2 py-1 rounded-full">🔥 Flash Sale</span>
                    </div>
                    <p class="text-sm text-gray-500 mt-1">¥{{ number_format($product->price_cny,2) }} · Rate: ¥1={{ $symbol }}{{ number_format($rate,2) }}</p>

                    @else
                    {{-- Normal China import product: dynamic price + range ──────── --}}
                    <div>
                        {{-- Price range badge --}}
                        @if($hasRange)
                        <p class="text-xs text-gray-400 mb-1 flex items-center gap-1">
                            <i class="fas fa-tags text-accent text-[10px]"></i>
                            Price varies by variant/quantity: <strong class="text-primary">¥{{ number_format($priceMin,2) }} – ¥{{ number_format($priceMax,2) }}</strong>
                        </p>
                        @endif
                        {{-- Live price (updates when SKU/qty selected) --}}
                        <p class="text-3xl font-black text-primary" style="font-family:Poppins,sans-serif"
                           x-text="symbol + fmt(activePriceLocal)"></p>
                        <p class="text-sm text-gray-500 mt-1"
                           x-text="'¥' + activePriceCny.toFixed(2) + ' · Rate: ¥1=' + symbol + rate.toFixed(2)"></p>
                        @if($hasRange)
                        <p class="text-[10px] text-blue-500 mt-1 flex items-center gap-1">
                            <i class="fas fa-info-circle"></i> Price updates when you select a variant or quantity
                        </p>
                        @endif
                    </div>
                    @endif
                </div>

                <div class="flex items-center gap-3 flex-wrap">
                    <span class="inline-flex items-center gap-1.5 text-xs font-bold px-3 py-1.5 rounded-full bg-{{ $product->getStockBadgeColor() }}-100 text-{{ $product->getStockBadgeColor() }}-700">
                        <span class="w-1.5 h-1.5 rounded-full bg-{{ $product->getStockBadgeColor() }}-500"></span>
                        @if($product->stock_status === 'in_stock') In Stock
                        @elseif($product->stock_status === 'low_stock') Only a Few Left!
                        @else Out of Stock
                        @endif
                    </span>
                    @if($product->moq > 1)
                    <span class="text-xs text-gray-500 bg-gray-100 px-3 py-1.5 rounded-full font-medium">Min. order: {{ $product->moq }} pcs</span>
                    @endif
                    @if($product->vendor)
                    <span class="text-xs text-blue-600 bg-blue-50 px-3 py-1.5 rounded-full font-medium flex items-center gap-1">
                        <i class="fas fa-shield-alt text-[10px]"></i> Escrow Protected Purchase
                    </span>
                    @endif
                </div>

                {{-- Variants — task 3: click updates price live ──────────────── --}}
                @if($product->variants && !$product->vendor)
                @foreach($product->variants as $v)
                <div>
                    <p class="text-sm font-bold text-gray-700 mb-2 flex items-center gap-2">
                        {{ $v['name'] }}
                        @if($hasRange)
                        <span class="text-[10px] text-blue-500 font-normal">(price may change)</span>
                        @endif
                    </p>
                    <div class="flex flex-wrap gap-2">
                        @foreach($v['options'] ?? [] as $opt)
                        @php
                            $optLabel   = $opt['label'] ?? '';
                            $variantKey = addslashes($v['name']) . ': ' . addslashes($optLabel);
                            // Find matching SKU price for this option
                            $matchedSku = $skus->first(fn($s) =>
                                in_array($optLabel, array_values($s['attributes'] ?? []))
                                || str_contains($s['label'], $optLabel)
                            );
                            $skuPrice = $matchedSku ? $matchedSku['price_local'] : null;
                        @endphp
                        <button type="button"
                            @click="
                                variant='{{ $variantKey }}';
                                updateFromVariant('{{ addslashes($optLabel) }}');
                            "
                            :class="variant==='{{ $variantKey }}'
                                ? 'border-accent bg-accent text-white shadow-md'
                                : 'border-gray-200 text-gray-600 hover:border-primary'"
                            class="border-2 rounded-xl px-3.5 py-2 text-xs font-semibold transition active:scale-95 flex flex-col items-center gap-0.5">
                            <span>{{ $optLabel }}</span>
                            @if($skuPrice && $hasRange)
                            <span class="text-[10px] font-bold opacity-80">{{ $symbol }}{{ number_format($skuPrice, 0) }}</span>
                            @endif
                        </button>
                        @endforeach
                    </div>
                </div>
                @endforeach
                @endif

                {{-- Shipping restriction notice — task 5 ──────────────────── --}}
                @if($product->has_battery || $product->has_liquid || $product->is_powder)
                <div class="bg-amber-50 border border-amber-200 rounded-xl p-3 flex items-start gap-2.5 text-xs text-amber-800">
                    <i class="fas fa-exclamation-triangle text-amber-500 flex-shrink-0 mt-0.5"></i>
                    <div>
                        <p class="font-bold mb-0.5">Special Shipping Required</p>
                        <p class="text-amber-700 leading-relaxed">
                            This product contains
                            @if($product->has_battery) <strong>battery/electronics</strong>@endif
                            @if($product->has_liquid) @if($product->has_battery), @endif<strong>liquids</strong>@endif
                            @if($product->is_powder) @if($product->has_battery||$product->has_liquid), @endif<strong>powder</strong>@endif
                            and <strong>must be shipped via Air Cargo HK route</strong>.
                            GZ route is not available for this product.
                        </p>
                    </div>
                </div>
                @endif

                {{-- Quantity — task 2+3: MOQ enforced, price updates on change ── --}}
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <p class="text-sm font-bold text-gray-700">Quantity</p>
                        @if($product->moq > 1)
                        <span class="text-[11px] text-orange-600 bg-orange-50 border border-orange-200 font-bold px-2.5 py-1 rounded-full flex items-center gap-1">
                            <i class="fas fa-layer-group text-[9px]"></i> Min. {{ $product->moq }} pcs
                        </span>
                        @endif
                    </div>
                    <div class="flex items-center gap-3 flex-wrap">
                        <button @click="qty=Math.max({{ (int)($product->moq??1) }},qty-1); $dispatch('qty-changed', {qty: qty})"
                            class="w-10 h-10 bg-gray-100 hover:bg-gray-200 rounded-xl font-bold transition flex items-center justify-center text-lg">−</button>
                        <span x-text="qty" class="font-black text-xl w-10 text-center" style="font-family:Poppins,sans-serif"></span>
                        <button @click="qty++; $dispatch('qty-changed', {qty: qty})"
                            class="w-10 h-10 bg-gray-100 hover:bg-gray-200 rounded-xl font-bold transition flex items-center justify-center text-lg">+</button>
                        {{-- Total price = qty × unit price — always visible ────── --}}
                        <span class="text-sm text-gray-500 flex items-center gap-1">
                            <span x-text="qty"></span>
                            <span class="text-gray-400">×</span>
                            <span class="font-semibold text-gray-700" x-text="symbol + fmt(activePriceLocal)"></span>
                            <span class="text-gray-400">=</span>
                            <strong class="text-primary font-black text-base" x-text="symbol + fmt(qty * activePriceLocal)"></strong>
                        </span>
                        @if($product->moq > 1)
                        <span class="text-[11px] text-orange-600 bg-orange-50 border border-orange-200 font-bold px-2 py-1 rounded-full">
                            Min. {{ $product->moq }} pcs
                        </span>
                        @endif
                    </div>
                </div>

                {{-- Task 6: Bulk price calculator table ─────────────────────── --}}
                @if(!empty($product->price_tiers) && count($product->price_tiers) > 1 && !$product->vendor)
                <div class="border border-blue-100 bg-blue-50 rounded-2xl p-4">
                    <p class="text-xs font-bold text-blue-700 uppercase tracking-wider mb-3 flex items-center gap-1.5">
                        <i class="fas fa-tags text-blue-500"></i> Bulk Price Calculator
                    </p>
                    <table class="w-full text-xs">
                        <thead>
                            <tr class="text-blue-500 border-b border-blue-200">
                                <th class="pb-2 text-left font-bold">Quantity</th>
                                <th class="pb-2 text-right font-bold">Unit Price (CNY)</th>
                                <th class="pb-2 text-right font-bold">Unit Price ({{ $symbol }})</th>
                                <th class="pb-2 text-right font-bold">You Save</th>
                            </tr>
                        </thead>
                        <tbody>
                        @php
                            $basePrice = (float)$product->price_cny;
                            $tiers     = collect($product->price_tiers)->sortBy('min_qty');
                        @endphp
                        @foreach($tiers as $idx => $tier)
                        @php
                            $nextTier  = $tiers->values()->get($idx + 1);
                            $minQty    = (int)$tier['min_qty'];
                            $qtyLabel  = ($nextTier && (int)$nextTier['min_qty'] > $minQty + 1)
                                ? "{$minQty} – " . ((int)$nextTier['min_qty'] - 1) . " pcs"
                                : "{$minQty}+ pcs";
                            $tierPrice = (float)$tier['price'];
                            $localPrice = round($tierPrice * $rate, 2);
                            $saving    = $idx === 0 ? 0 : round((($basePrice - $tierPrice) / $basePrice) * 100, 1);
                        @endphp
                        <tr class="border-b border-blue-100 {{ $idx === 0 ? '' : 'hover:bg-blue-100 cursor-pointer' }}"
                            @if($idx > 0) @click="qty={{ $tier['min_qty'] }}; $dispatch('qty-changed', {qty: {{ $tier['min_qty'] }}})" title="Click to set quantity" @endif>
                            <td class="py-2 font-semibold text-gray-700">{{ $qtyLabel }}</td>
                            <td class="py-2 text-right font-bold text-gray-800">¥{{ number_format($tierPrice, 2) }}</td>
                            <td class="py-2 text-right font-bold text-primary">{{ $symbol }}{{ number_format($localPrice, 2) }}</td>
                            <td class="py-2 text-right">
                                @if($saving > 0)
                                <span class="bg-green-100 text-green-700 font-bold px-1.5 py-0.5 rounded-lg">−{{ $saving }}%</span>
                                @else
                                <span class="text-gray-400">—</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                        </tbody>
                    </table>
                    <p class="text-[10px] text-blue-500 mt-2 flex items-center gap-1">
                        <i class="fas fa-mouse-pointer"></i> Click any row to auto-set quantity and see updated price
                    </p>
                </div>
                @endif

                @if(!$product->isOutOfStock())
                @if($product->vendor)
                {{-- Local vendor product: Buy Now → dedicated escrow checkout --}}
                @auth
                <div class="space-y-3">
                    <a href="{{ route('local.checkout', $product) }}"
                        class="flex items-center justify-center gap-2.5 w-full bg-accent hover:bg-orange-600 text-white font-black py-4 rounded-2xl text-sm transition btn-glow shadow-lg">
                        <i class="fas fa-shopping-bag"></i> Buy Now — ₦{{ number_format((float)$product->price_ngn, 0) }}
                    </a>
                    <p class="text-center text-xs text-gray-400 flex items-center justify-center gap-1.5">
                        <i class="fas fa-lock text-green-400"></i>
                        Payment held in escrow · Released after you confirm delivery
                    </p>
                </div>
                @else
                <a href="{{ route('login') }}"
                    class="flex items-center justify-center gap-2 w-full bg-accent hover:bg-orange-600 text-white font-black py-4 rounded-2xl text-sm transition shadow-lg">
                    <i class="fas fa-sign-in-alt"></i> Sign In to Purchase
                </a>
                @endauth
                @else
                {{-- China import product: Add to Cart --}}
                @auth
                {{-- Service fee notice for China products --}}
                @php
                    $serviceFeeRate = \App\Models\PurchaseFee::estimatePercent(auth()->user()->country ?? 'NG');
                @endphp
                @if($serviceFeeRate > 0)
                <div class="bg-blue-50 border border-blue-100 rounded-xl px-3 py-2.5 mb-3 flex items-start gap-2">
                    <i class="fas fa-info-circle text-blue-400 text-xs mt-0.5 flex-shrink-0"></i>
                    <p class="text-[11px] text-blue-700 leading-snug">
                        A <strong>{{ $serviceFeeRate }}% service fee</strong> is added at checkout (covers procurement + handling).
                        @if(($product->domestic_freight_cny ?? 0) > 0)
                        China domestic freight of <strong>¥{{ number_format($product->domestic_freight_cny, 2) }}</strong> also applies.
                        @endif
                        International shipping is billed separately after your goods are weighed in China.
                    </p>
                </div>
                @endif
                <form action="{{ route('user.cart.add') }}" method="POST" class="flex flex-col sm:flex-row gap-3">
                    @csrf
                    <input type="hidden" name="product_id" value="{{ $product->id }}">
                    <input type="hidden" name="quantity" :value="qty">
                    <input type="hidden" name="selected_variant" :value="variant">
                    <button class="flex-1 bg-accent hover:bg-accent-dark text-white font-bold py-3.5 rounded-2xl text-sm transition btn-glow flex items-center justify-center gap-2">
                        <i class="fas fa-cart-plus"></i> Add to Cart
                    </button>
                </form>
                @else
                <a href="{{ route('login') }}" class="flex items-center justify-center gap-2 w-full bg-primary hover:bg-primary-dark text-white font-bold py-3.5 rounded-2xl text-sm transition">
                    <i class="fas fa-sign-in-alt"></i> Sign In to Add to Cart
                </a>
                @endauth
                @endif
                @endif

                @if($product->weight_kg || $product->source_url)
                <div class="text-xs text-gray-400 space-y-1.5 pt-3 border-t border-gray-100">
                    @if($product->weight_kg)<p class="flex items-center gap-2"><i class="fas fa-weight-hanging w-4 text-gray-300"></i>Weight: {{ $product->weight_kg }}kg</p>@endif
                    @if($product->source_url)<p class="flex items-center gap-2"><i class="fas fa-external-link-alt w-4 text-gray-300"></i><a href="{{ $product->source_url }}" target="_blank" class="hover:underline text-blue-400 font-medium">View on {{ ucfirst($product->source) }}</a></p>@endif
                </div>
                @endif

                {{-- 🔔 Price Alert — unique BYBHUB differentiator ──────────────────── --}}
                @auth
                <div x-data="{
                    open: false,
                    sent: false,
                    loading: false,
                    email: '{{ auth()->user()->email }}',
                    targetPrice: '',
                    async submit() {
                        this.loading = true;
                        await fetch('/api/price-alert', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                            },
                            body: JSON.stringify({
                                product_id: {{ $product->id }},
                                target_price_cny: this.targetPrice,
                            })
                        });
                        this.loading = false;
                        this.sent = true;
                    }
                }">
                    <button @click="open=!open"
                            class="w-full mt-1 flex items-center justify-center gap-2 border-2 border-dashed border-primary/30
                                   text-primary text-xs font-bold py-2.5 rounded-2xl hover:border-primary hover:bg-primary/5 transition">
                        <i class="fas fa-bell text-xs"></i>
                        🔔 Set a Price Alert
                    </button>
                    <div x-show="open" x-transition class="mt-2 bg-primary/5 border border-primary/15 rounded-2xl p-4">
                        <div x-show="!sent">
                            <p class="text-xs font-bold text-primary mb-2">
                                Alert me when this drops below:
                            </p>
                            <div class="flex gap-2">
                                <div class="relative flex-1">
                                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs font-bold">¥</span>
                                    <input type="number" x-model="targetPrice"
                                           placeholder="{{ number_format($product->price_cny * 0.9, 2) }}"
                                           class="w-full pl-7 pr-3 py-2.5 border-2 border-gray-200 rounded-xl text-sm
                                                  focus:outline-none focus:border-primary transition">
                                </div>
                                <button @click="submit()" :disabled="loading || !targetPrice"
                                        class="bg-primary text-white font-bold px-4 py-2.5 rounded-xl text-xs transition
                                               disabled:opacity-50 flex items-center gap-1.5">
                                    <i class="fas" :class="loading ? 'fa-spinner fa-spin' : 'fa-bell'"></i>
                                    Alert Me
                                </button>
                            </div>
                            <p class="text-[10px] text-gray-400 mt-2">We'll email you at {{ auth()->user()->email }}</p>
                        </div>
                        <div x-show="sent" class="text-center py-1">
                            <p class="text-sm font-bold text-green-600">✅ Alert set! We'll notify you.</p>
                        </div>
                    </div>
                </div>
                @endauth
            </div>
        </div>
    </div>

    {{-- Related Products --}}
    @if($related->isNotEmpty())
    <h3 class="font-bold text-primary text-lg mb-4 flex items-center gap-2">
        <i class="fas fa-th-large text-sm"></i> Related Products
    </h3>
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        @foreach($related as $p)
            @include('partials.product-card', ['product'=>$p,'symbol'=>$symbol,'currency'=>$currency])
        @endforeach
    </div>
    @endif
</div>

@push('scripts')
<script>
// Alpine component for the price block — defined outside the attribute
// to avoid HTML attribute corruption from JSON containing > < & characters
document.addEventListener('alpine:init', function() {
    Alpine.data('productPriceBlock', function() {
        var d = window._productPriceData;
        return {
            basePriceCny:    d.basePriceCny,
            basePriceLocal:  d.basePriceLocal,
            activePriceCny:  d.basePriceCny,
            activePriceLocal: d.basePriceLocal,
            activeImage: null,
            symbol: d.symbol,
            rate:   d.rate,
            skus:   d.skus,
            tiers:  d.tiers,
            moq:    d.moq,
            qty:    d.moq,   // ← qty lives here now, starts at MOQ

            updateFromVariant: function(label) {
                var sku = null;
                // 1. Exact full label match (e.g. "Red / L")
                sku = this.skus.find(function(s) { return s.label === label; });
                // 2. Exact attribute value match (case-insensitive)
                if (!sku) {
                    sku = this.skus.find(function(s) {
                        return Object.values(s.attributes).some(function(v) {
                            return String(v).trim().toLowerCase() === String(label).trim().toLowerCase();
                        });
                    });
                }
                // 3. Substring fallback (values > 1 char only)
                if (!sku) {
                    sku = this.skus.find(function(s) {
                        return Object.values(s.attributes).some(function(v) {
                            return String(label).trim().toLowerCase().includes(String(v).trim().toLowerCase())
                                && String(v).trim().length > 1;
                        });
                    });
                }
                if (sku && sku.price_cny > 0) {
                    this.activePriceCny   = sku.price_cny;
                    this.activePriceLocal = sku.price_local;
                    if (sku.image) this.activeImage = sku.image;
                } else {
                    this.activePriceCny   = this.basePriceCny;
                    this.activePriceLocal = this.basePriceLocal;
                }
            },

            decQty: function() {
                this.qty = Math.max(this.moq, parseInt(this.qty) - 1);
                this.updateFromQty(this.qty);
            },

            incQty: function() {
                this.qty = parseInt(this.qty) + 1;
                this.updateFromQty(this.qty);
            },

            setQty: function(n) {
                this.qty = Math.max(this.moq, parseInt(n) || this.moq);
                this.updateFromQty(this.qty);
            },

            updateFromQty: function(qty) {
                if (!this.tiers || this.tiers.length === 0) return;
                var sorted = this.tiers.slice().sort(function(a,b) { return b.min_qty - a.min_qty; });
                var tier   = sorted.find(function(t) { return qty >= t.min_qty; });
                if (tier) {
                    this.activePriceCny   = tier.price;
                    this.activePriceLocal = Math.round(tier.price * d.rate * 100) / 100;
                }
            },

            fmt: function(n) {
                return new Intl.NumberFormat('en-NG', {minimumFractionDigits:2, maximumFractionDigits:2}).format(n);
            },

            init: function() {}   // no event listeners needed — qty is local now
        };
    });
});
</script>
@endpush

@endsection
