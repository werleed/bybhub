@php
    $cardRate     = isset($rate) ? (float)(is_array($rate) ? ($rate['rate'] ?? $rate[0] ?? 0) : $rate) : (float)($product->price_cny > 0 ? 1 : 0);
    $cardPriceMin = (float) $product->price_cny;
    $cardPriceMax = (float) ($product->price_cny_max ?? $product->price_cny);
    $cardHasRange = $cardRate > 0 && $cardPriceMax > $cardPriceMin + 0.5;
    $cardLocalMin = $cardRate > 0 ? round($cardPriceMin * $cardRate, 2) : $product->getPriceInCurrency($currency ?? 'NGN');
    $cardLocalMax = $cardRate > 0 ? round($cardPriceMax * $cardRate, 2) : $cardLocalMin;
    $isLoggedIn   = auth()->check();
    $isLocalProd  = $product->product_type === 'local';
    $showWeight   = $product->weight_kg && (float)$product->weight_kg > 0.5;
@endphp

<div class="group bg-white rounded-2xl overflow-hidden border border-gray-100/80 prod-card relative"
     style="box-shadow:0 2px 14px rgba(26,58,107,0.07);">

    {{-- Image container --}}
    <a href="{{ route('products.show', $product) }}" class="block relative overflow-hidden bg-gray-50" style="aspect-ratio:1/1;">
        <img src="{{ $product->getMainImage() }}"
             alt="{{ $product->title }}"
             class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500 ease-out"
             loading="lazy"
             onerror="this.src='https://placehold.co/300x300/F0F4F8/1A3A6B?text=No+Image'">

        {{-- Gradient overlay --}}
        <div class="absolute inset-0 bg-gradient-to-t from-black/10 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>

        {{-- Top-left badges --}}
        <div class="absolute top-2 left-2 flex flex-col gap-1.5">
            @if($isLocalProd)
            <span class="inline-flex items-center gap-1 text-white text-[10px] font-black px-2 py-1 rounded-lg shadow-md" style="background:linear-gradient(135deg,#16a34a,#15803d)">
                🇳🇬 Local
            </span>
            @endif
            @if($product->isFlashSaleActive())
            <span class="inline-flex items-center gap-1 bg-red-500 text-white text-[10px] font-black px-2 py-1 rounded-lg shadow-md">
                <i class="fas fa-bolt text-[8px]"></i> SALE
            </span>
            @endif
            @if($product->moq > 1)
            <span class="inline-flex items-center gap-1 text-white text-[10px] font-black px-2 py-1 rounded-lg shadow-md" style="background:linear-gradient(135deg,#7c3aed,#6d28d9)">
                <i class="fas fa-layer-group text-[8px]"></i> MOQ {{ $product->moq }}
            </span>
            @endif
            @if($product->isOutOfStock())
            <span class="bg-gray-800/80 text-white text-[10px] font-bold px-2 py-1 rounded-lg backdrop-blur-sm">Sold Out</span>
            @elseif(isset($product->stock_status) && $product->stock_status === 'low_stock')
            <span class="bg-amber-500 text-white text-[10px] font-bold px-2 py-1 rounded-lg shadow-sm">Low Stock</span>
            @endif
        </div>

        {{-- Top-right: weight badge (mobile-friendly, always visible) --}}
        @if($showWeight && !$product->isOutOfStock())
        <div class="absolute top-2 right-2">
            <span class="inline-flex items-center gap-1 bg-white/90 backdrop-blur-sm text-gray-600 text-[9px] font-bold px-1.5 py-0.5 rounded-lg shadow-sm">
                <i class="fas fa-weight-hanging text-[8px] text-gray-400"></i>{{ number_format((float)$product->weight_kg, 1) }}kg
            </span>
        </div>
        @endif
    </a>

    {{-- Product info --}}
    <div class="p-3">
        {{-- Category pill --}}
        @if($product->category)
        @php
            $catColors = ['blue','pink','green','purple','orange','yellow','red','teal','indigo'];
            $catIdx = crc32($product->category->slug ?? '') % count($catColors);
            $catPillStyles = [
                'blue'   => 'background:#dbeafe;color:#1d4ed8;',
                'pink'   => 'background:#fce7f3;color:#be185d;',
                'green'  => 'background:#d1fae5;color:#065f46;',
                'purple' => 'background:#ede9fe;color:#6d28d9;',
                'orange' => 'background:#ffedd5;color:#c2410c;',
                'yellow' => 'background:#fef9c3;color:#854d0e;',
                'red'    => 'background:#fee2e2;color:#991b1b;',
                'teal'   => 'background:#ccfbf1;color:#0f766e;',
                'indigo' => 'background:#e0e7ff;color:#3730a3;',
            ];
            $pillStyle = $catPillStyles[$catColors[$catIdx]];
        @endphp
        <span class="inline-block text-[10px] font-bold px-2 py-0.5 rounded-full mb-1.5 uppercase tracking-wider" style="{{ $pillStyle }}">
            {{ $product->category->getTranslatedName() }}
        </span>
        @endif

        {{-- Title --}}
        <a href="{{ route('products.show', $product) }}">
            <h3 class="text-[13px] font-semibold text-gray-800 line-clamp-2 leading-snug hover:text-primary transition mb-2" style="font-family:Inter,sans-serif;">{{ $product->title }}</h3>
        </a>

        {{-- Price row --}}
        <div class="flex items-end justify-between gap-2 mb-2.5">
            <div>
                @if($isLocalProd && $product->price_ngn)
                <p class="text-base font-black text-primary leading-none" style="font-family:Poppins,sans-serif;">₦{{ number_format((float)$product->price_ngn, 0) }}</p>
                <p class="text-[11px] text-green-600 mt-0.5 flex items-center gap-1 font-semibold">
                    <i class="fas fa-store text-[9px]"></i> Local Vendor
                </p>
                @elseif($product->isFlashSaleActive())
                <p class="text-base font-black text-red-500 leading-none" style="font-family:Poppins,sans-serif;">{{ $symbol ?? '₦' }}{{ number_format($product->getPriceInCurrency($currency ?? 'NGN'),2) }}</p>
                <p class="text-xs text-gray-400 line-through leading-snug">{{ $symbol ?? '₦' }}{{ number_format(\App\Models\ExchangeRate::convert($product->price_cny,$currency ?? 'NGN'),2) }}</p>
                @elseif($cardHasRange)
                <p class="text-sm font-black text-primary leading-none" style="font-family:Poppins,sans-serif;">
                    {{ $symbol ?? '₦' }}{{ number_format($cardLocalMin, 0) }}–{{ number_format($cardLocalMax, 0) }}
                </p>
                <p class="text-[10px] text-blue-500 mt-0.5 font-semibold flex items-center gap-1">
                    <i class="fas fa-tags text-[9px]"></i> Price varies
                </p>
                @else
                <p class="text-base font-black text-primary leading-none" style="font-family:Poppins,sans-serif;">{{ $symbol ?? '₦' }}{{ number_format($product->getPriceInCurrency($currency ?? 'NGN'),2) }}</p>
                <p class="text-[11px] text-gray-400 mt-0.5 flex items-center gap-1">
                    <span class="text-gray-300">¥</span>{{ number_format($product->price_cny,2) }}
                </p>
                @endif
            </div>
            @if(isset($product->order_count) && $product->order_count > 0)
            <div class="flex items-center gap-1 text-[11px] text-gray-400 flex-shrink-0">
                <i class="fas fa-fire text-orange-400 text-[10px]"></i>
                <span>{{ $product->order_count }}</span>
            </div>
            @endif
        </div>

        {{-- CTA row: different for logged-in vs guest, mobile-tap-friendly --}}
        @if(!$product->isOutOfStock())
        @if($isLocalProd)
        {{-- Local vendor: Buy Now (both guest and logged-in) --}}
        <a href="{{ route('products.show', $product) }}"
           class="w-full flex items-center justify-center gap-1.5 bg-accent/10 hover:bg-accent text-accent hover:text-white font-bold text-xs py-2 rounded-xl transition border border-accent/20">
            <i class="fas fa-shopping-bag text-[10px]"></i> View & Buy
        </a>
        @elseif($isLoggedIn)
        {{-- Logged-in user: Add to Cart directly --}}
        <form action="{{ route('user.cart.add') }}" method="POST" class="w-full">
            @csrf
            <input type="hidden" name="product_id" value="{{ $product->id }}">
            <input type="hidden" name="quantity" value="{{ max(1, (int)($product->moq ?? 1)) }}">
            <button type="submit"
                class="w-full flex items-center justify-center gap-1.5 bg-primary/10 hover:bg-primary text-primary hover:text-white font-bold text-xs py-2 rounded-xl transition border border-primary/20 active:scale-95">
                <i class="fas fa-cart-plus text-[10px]"></i> Add to Cart
            </button>
        </form>
        @else
        {{-- Guest: prompt to sign in --}}
        <a href="{{ route('login') }}"
           class="w-full flex items-center justify-center gap-1.5 bg-gray-50 hover:bg-primary/5 text-gray-500 hover:text-primary font-bold text-xs py-2 rounded-xl transition border border-gray-200 active:scale-95">
            <i class="fas fa-sign-in-alt text-[10px]"></i> Sign In to Order
        </a>
        @endif
        @else
        <div class="w-full flex items-center justify-center gap-1.5 bg-gray-50 text-gray-400 font-bold text-xs py-2 rounded-xl">
            <i class="fas fa-times text-[10px]"></i> Sold Out
        </div>
        @endif
    </div>
</div>
