@extends('layouts.app')
@section('title','Product Found')
@section('content')

{{-- ── Success flash — brief "Product Found!" celebration before content shows ── --}}
<div id="found-flash"
     style="position:fixed;inset:0;z-index:9998;
            background:linear-gradient(135deg,rgba(26,58,107,0.97),rgba(13,29,64,0.97));
            display:flex;flex-direction:column;align-items:center;justify-content:center;
            padding:24px;text-align:center;transition:opacity .5s ease">
    <div style="font-size:64px;margin-bottom:16px;animation:pop .5s cubic-bezier(.175,.885,.32,1.275) both">✅</div>
    <h2 style="color:white;font-size:22px;font-weight:800;font-family:Poppins,sans-serif;margin-bottom:8px">
        Product Found!
    </h2>
    <p style="color:rgba(147,197,253,1);font-size:14px">Loading details…</p>
</div>

<style>
@keyframes pop {
    0%   { transform: scale(0) rotate(-10deg); opacity:0; }
    100% { transform: scale(1) rotate(0deg);   opacity:1; }
}
@keyframes slideUp {
    from { transform: translateY(32px); opacity: 0; }
    to   { transform: translateY(0);    opacity: 1; }
}
.anim-slide-up {
    opacity: 0;
    animation: slideUp .5s ease forwards;
}
</style>

<div id="result-content" style="opacity:0;transition:opacity .4s ease" class="max-w-5xl mx-auto"
     x-data="{
        mainImg: '{{ !empty($data['images']) ? route('img.proxy', ['url' => $data['images'][0]]) : '' }}',
        qty: {{ $data['moq'] ?? 1 }},
        variant: '',
        variantPrice: {{ $data['price_cny'] ?? 0 }}
     }">

    {{-- Warning if needs manual edit --}}
    @if(!empty($data['needs_manual_edit']))
    <div class="bg-amber-50 border border-amber-200 rounded-2xl p-4 mb-4 flex items-center gap-3">
        <i class="fas fa-exclamation-triangle text-amber-500"></i>
        <p class="text-sm text-amber-800 font-medium">Product saved with basic info. <a href="{{ route('user.scraper.manual', ['url'=>$data['source_url']??'']) }}" class="underline">Click here to add more details.</a></p>
    </div>
    @endif

    <div class="bg-white rounded-3xl shadow-card overflow-hidden anim-slide-up" style="animation-delay:.1s">
        <div class="grid grid-cols-1 lg:grid-cols-2">

            {{-- ── IMAGE GALLERY ── --}}
            <div class="p-6 bg-gray-50">
                <div class="aspect-square rounded-2xl overflow-hidden border border-gray-200 mb-3 bg-white">
                    <img :src="mainImg || 'https://placehold.co/500x500/F0F4F8/1A3A6B?text=No+Image'"
                         alt="{{ $data['title'] }}"
                         class="w-full h-full object-contain transition-all duration-300"
                         onerror="this.src='https://placehold.co/500x500/F0F4F8/1A3A6B?text=Loading'">
                </div>
                @if(!empty($data['images']) && count($data['images']) > 1)
                <div class="flex gap-2 overflow-x-auto pb-1">
                    @foreach(array_slice($data['images'],0,8) as $idx => $img)
                    @php $p = route('img.proxy',['url'=>$img]); @endphp
                    <button @click="mainImg='{{ $p }}'"
                        class="flex-shrink-0 w-14 h-14 rounded-xl overflow-hidden border-2 transition"
                        :class="mainImg==='{{ $p }}' ? 'border-primary' : 'border-gray-200 hover:border-primary/50'">
                        <img src="{{ $p }}" class="w-full h-full object-cover"
                             onerror="this.parentElement.style.display='none'">
                    </button>
                    @endforeach
                </div>
                @endif

                {{-- Source link --}}
                @if(!empty($data['source_url']))
                <div class="mt-3">
                    <a href="{{ $data['source_url'] }}" target="_blank"
                       class="inline-flex items-center gap-1.5 text-xs text-blue-500 hover:underline">
                        <i class="fas fa-external-link-alt text-[10px]"></i>
                        View on {{ ucfirst($data['source'] ?? '1688') }}
                    </a>
                </div>
                @endif
            </div>

            {{-- ── PRODUCT INFO ── --}}
            <div class="p-6 flex flex-col">

                {{-- Source badge --}}
                <div class="flex items-center gap-2 mb-3">
                    @if(!empty($data['source']))
                    <span class="text-xs bg-blue-100 text-blue-700 font-bold px-2.5 py-1 rounded-full uppercase">{{ $data['source'] }}</span>
                    @endif
                    @if(!empty($data['stock_status']))
                    <span class="text-xs font-bold px-2.5 py-1 rounded-full
                        {{ $data['stock_status']==='in_stock' ? 'bg-green-100 text-green-700' :
                          ($data['stock_status']==='low_stock' ? 'bg-amber-100 text-amber-700' : 'bg-red-100 text-red-700') }}">
                        {{ $data['stock_status']==='in_stock' ? '✓ In Stock' : ($data['stock_status']==='low_stock' ? '⚠ Low Stock' : '✗ Out of Stock') }}
                    </span>
                    @endif
                    @if(!empty($data['sold_count']) && $data['sold_count'] > 0)
                    <span class="text-xs text-gray-400"><i class="fas fa-fire text-orange-400 mr-0.5"></i>{{ $data['sold_count'] }} sold</span>
                    @endif
                </div>

                {{-- Title --}}
                <h1 class="text-xl font-black text-gray-900 leading-tight mb-1" style="font-family:Poppins,sans-serif">{{ $data['title'] }}</h1>
                @if(!empty($data['title_cn']) && $data['title_cn'] !== $data['title'])
                <p class="text-xs text-gray-400 mb-3">{{ $data['title_cn'] }}</p>
                @endif

                {{-- Auto-detected category badge ─────────────────────────── --}}
                @if(isset($detectedCategory) && $detectedCategory)
                <div class="flex items-center gap-2 mb-3">
                    <span class="inline-flex items-center gap-1.5 bg-green-50 border border-green-200 text-green-700 text-xs font-bold px-3 py-1.5 rounded-xl">
                        <i class="fas fa-magic text-green-500 text-[10px]"></i>
                        Auto-categorised: {{ $detectedCategory->getTranslatedName() }}
                    </span>
                    @if(!empty($data['product_id']))
                    <a href="{{ route('products.category', $detectedCategory->slug) }}"
                       class="text-xs text-blue-500 hover:underline">
                        View in category →
                    </a>
                    @endif
                </div>
                @elseif(!empty($data['product_id']))
                <div class="flex items-center gap-2 mb-3">
                    <span class="inline-flex items-center gap-1.5 bg-amber-50 border border-amber-200 text-amber-700 text-xs font-bold px-3 py-1.5 rounded-xl">
                        <i class="fas fa-tag text-amber-400 text-[10px]"></i>
                        No category detected — our team will assign one
                    </span>
                </div>
                @endif

                {{-- Seller --}}
                @if(!empty($data['seller_name']))
                <p class="text-xs text-gray-500 mb-4 flex items-center gap-1.5">
                    <i class="fas fa-store text-gray-400"></i> {{ $data['seller_name'] }}
                </p>
                @endif

                {{-- Price --}}
                <div class="bg-primary-50 rounded-2xl p-4 mb-4">
                    <div class="flex items-end gap-3 flex-wrap">
                        <div>
                            <p class="text-xs text-gray-500 mb-0.5">Price (CNY)</p>
                            <p class="text-3xl font-black text-primary" style="font-family:Poppins,sans-serif">
                                ¥<span x-text="parseFloat(variantPrice).toFixed(2)">{{ number_format($data['price_cny'],2) }}</span>
                                @if(!empty($data['price_cny_max']) && $data['price_cny_max'] > $data['price_cny'])
                                <span class="text-lg text-gray-400">– ¥{{ number_format($data['price_cny_max'],2) }}</span>
                                @endif
                            </p>
                        </div>
                        <div class="border-l border-primary/20 pl-3">
                            <p class="text-xs text-gray-500 mb-0.5">In {{ $data['currency'] ?? auth()->user()->currency }}</p>
                            <p class="text-xl font-bold text-accent">{{ $data['symbol'] ?? auth()->user()->currencySymbol() }}<span x-text="(parseFloat(variantPrice) * {{ (float)($data['rate'] ?? 205) }}).toFixed(2)">{{ $data['price_local_fmt'] ?? '' }}</span></p>
                        </div>
                    </div>

                    {{-- Price tiers --}}
                    @if(!empty($data['price_tiers']) && count($data['price_tiers']) > 1)
                    <div class="mt-3 pt-3 border-t border-primary/15">
                        <p class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Quantity Pricing</p>
                        <div class="flex flex-wrap gap-2">
                            @foreach($data['price_tiers'] as $tier)
                            <div class="bg-white border border-primary/20 rounded-xl px-3 py-1.5 text-xs font-semibold">
                                <span class="text-gray-500">{{ $tier['min_qty'] }}+ pcs</span>
                                <span class="text-primary ml-1 font-black">¥{{ number_format($tier['price'],2) }}</span>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    <p class="text-xs text-gray-400 mt-2">Exchange rate: ¥1 = {{ $data['symbol'] ?? '₦' }}{{ number_format($data['rate'] ?? 205, 2) }}</p>
                    {{-- Price disclaimer --}}
                    <div class="mt-2 flex items-start gap-1.5 bg-amber-50 border border-amber-200 rounded-xl px-3 py-2">
                        <i class="fas fa-info-circle text-amber-400 text-xs mt-0.5 flex-shrink-0"></i>
                        <p class="text-[11px] text-amber-700 leading-snug">
                            <strong>Prices may change.</strong> Supplier prices on Chinese platforms can change at any time — the price shown is the latest fetched price and may differ at the time of actual purchase. BYBHUB will always confirm the final price before your order is placed.
                        </p>
                    </div>
                </div>

                {{-- Domestic freight (China local shipping) --}}
                @if(!empty($data['domestic_freight_cny']) && $data['domestic_freight_cny'] > 0)
                <div class="flex items-center gap-2 text-xs text-gray-500 bg-gray-50 rounded-xl px-3 py-2 mb-4">
                    <i class="fas fa-truck text-gray-400"></i>
                    <span>China domestic shipping from supplier: <strong>¥{{ number_format($data['domestic_freight_cny'], 2) }}</strong>
                    <span class="text-gray-400">(this covers supplier → our China warehouse; included in your order)</span>
                    </span>
                </div>
                @endif

                {{-- ── Estimated Weight & Shipping Fee ──────────────────────── --}}
                @php
                    $estProduct = \App\Models\Product::find($data['product_id'] ?? null);
                    $estWeight  = $estProduct ? $estProduct->getBillableWeight('sea') : 0.5;
                    $estSymbol  = $data['symbol'] ?? auth()->user()->currencySymbol();
                    $estRate    = (float)($data['rate'] ?? 205);
                    $estCountry = auth()->user()->country;
                    // Sea shipping per-kg rate from DB
                    $seaRate = \App\Models\ShippingRate::where('country', $estCountry)
                        ->where('method', 'sea_los')->where('is_active', true)->first();
                    $seaPerKg = $seaRate ? (float)$seaRate->rate : 0;
                    $seaRateCurrency = $seaRate->currency ?? 'NGN';
                    // Convert per-kg rate to user's currency if needed
                    if ($seaPerKg > 0 && $seaRateCurrency !== auth()->user()->currency) {
                        $convertRate = \App\Models\ExchangeRate::getRate($seaRateCurrency, auth()->user()->currency);
                        if ($convertRate > 0) $seaPerKg = $seaPerKg * $convertRate;
                    }
                    $isDefaultWeight = $estProduct && (float)($estProduct->weight_kg ?? 0) <= 0
                        || (abs($estWeight - 0.5) < 0.001);
                @endphp
                @if($estProduct)
                <div class="bg-blue-50 border border-blue-200 rounded-2xl p-4 mb-4"
                     x-data="{
                        weight: {{ $estWeight }},
                        seaPerKg: {{ $seaPerKg }},
                        editing: false,
                        saving: false,
                        saved: false,
                        updateWeight() {
                            this.saving = true;
                            fetch('{{ route('user.product.weight', $estProduct->id) }}', {
                                method: 'POST',
                                headers: {'Content-Type':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}','Accept':'application/json'},
                                body: JSON.stringify({weight_kg: this.weight})
                            }).then(r => r.json()).then(d => {
                                this.saving = false;
                                if(d.success){ this.saved = true; this.editing = false; setTimeout(()=>this.saved=false, 2000); }
                            });
                        }
                     }">
                    <div class="flex items-center justify-between mb-2">
                        <p class="text-xs font-bold text-blue-800 uppercase tracking-wider flex items-center gap-1.5">
                            <i class="fas fa-weight-hanging text-blue-500"></i> Estimated Weight & Shipping
                        </p>
                        <button type="button" @click="editing = !editing"
                            class="text-[11px] text-blue-600 hover:text-blue-800 font-semibold flex items-center gap-1">
                            <i class="fas fa-edit text-[10px]"></i> Edit weight
                        </button>
                    </div>

                    {{-- Weight display / edit --}}
                    <div x-show="!editing" class="flex items-center gap-3 text-sm">
                        <span class="text-gray-600">Item weight:</span>
                        <span class="font-bold text-blue-800" x-text="weight.toFixed(2) + ' kg'">{{ number_format($estWeight, 2) }} kg</span>
                        @if($isDefaultWeight)
                        <span class="text-[10px] bg-amber-100 text-amber-700 px-2 py-0.5 rounded-full font-medium">Estimated</span>
                        @endif
                    </div>
                    <div x-show="editing" x-transition class="flex items-center gap-2">
                        <input type="number" x-model.number="weight" step="0.1" min="0.1"
                            class="w-20 border-2 border-blue-200 rounded-lg px-2 py-1.5 text-sm focus:outline-none focus:border-blue-400">
                        <span class="text-sm text-gray-500">kg</span>
                        <button type="button" @click="updateWeight()" :disabled="saving"
                            class="bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold px-3 py-1.5 rounded-lg transition">
                            <span x-text="saving ? 'Saving...' : 'Save'">Save</span>
                        </button>
                        <span x-show="saved" x-transition class="text-xs text-green-600 font-bold flex items-center gap-1">
                            <i class="fas fa-check"></i> Updated
                        </span>
                    </div>

                    {{-- Shipping fee estimate (uses parent scope qty) --}}
                    @if($seaPerKg > 0)
                    <div class="mt-3 pt-3 border-t border-blue-200 space-y-1.5 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-500">Est. sea shipping (1 item):</span>
                            <span class="font-bold text-blue-700">{{ $estSymbol }}<span x-text="(Math.ceil(weight * 2) / 2 * seaPerKg).toFixed(2)">{{ number_format(ceil($estWeight * 2) / 2 * $seaPerKg, 2) }}</span></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">Est. sea shipping (your qty):</span>
                            <span class="font-bold text-blue-700">{{ $estSymbol }}<span x-text="(Math.ceil(weight * qty * 2) / 2 * seaPerKg).toFixed(2)">{{ number_format(ceil($estWeight * ($data['moq'] ?? 1) * 2) / 2 * $seaPerKg, 2) }}</span></span>
                        </div>
                    </div>
                    <div class="mt-2 text-[10px] text-gray-500 flex items-start gap-1.5">
                        <i class="fas fa-info-circle text-gray-400 mt-0.5"></i>
                        <span>Sea shipping is charged per kg after weighing at our China warehouse. Weight is rounded up to the nearest 0.5kg. @if($isDefaultWeight)This is an estimate — actual weight will be confirmed when your goods arrive at our warehouse.@endif</span>
                    </div>
                    @else
                    <div class="mt-2 text-[10px] text-gray-500 flex items-start gap-1.5">
                        <i class="fas fa-info-circle text-gray-400 mt-0.5"></i>
                        <span>Sea shipping fee is calculated per kg after weighing at our China warehouse. @if($isDefaultWeight)Weight shown is an estimate — actual weight will be confirmed on arrival.@endif</span>
                    </div>
                    @endif
                </div>
                @endif

                {{-- Auto-detected shipping flags ────────────────────────────── --}}
                @php
                    $flags = [];
                    if(!empty($data['has_battery'])) $flags[] = ['🔋','Has Battery','HK route required','amber'];
                    if(!empty($data['has_liquid']))  $flags[] = ['💧','Contains Liquid','HK route required','blue'];
                    if(!empty($data['is_powder']))   $flags[] = ['🫙','Contains Powder','Special handling','orange'];
                    if(!empty($data['is_food']))     $flags[] = ['🍎','Food Item','Check import rules','green'];
                    if(!empty($data['is_fragile']))  $flags[] = ['⚠️','Fragile Item','Special packaging','red'];
                @endphp
                @if(count($flags))
                <div class="bg-amber-50 border border-amber-200 rounded-xl p-3 mb-4">
                    <p class="text-[10px] font-bold text-amber-700 uppercase tracking-wider mb-2 flex items-center gap-1">
                        <i class="fas fa-magic text-amber-500"></i> Auto-Detected Attributes
                    </p>
                    <div class="flex flex-wrap gap-2">
                        @foreach($flags as [$emoji,$label,$note,$color])
                        <span class="inline-flex items-center gap-1.5 text-[11px] font-bold bg-{{ $color }}-100 text-{{ $color }}-700 border border-{{ $color }}-200 px-2.5 py-1 rounded-lg">
                            {{ $emoji }} {{ $label }}
                            <span class="font-normal opacity-75 text-[10px]">· {{ $note }}</span>
                        </span>
                        @endforeach
                    </div>
                </div>
                @endif

                {{-- Variants --}}
                @if(!empty($data['variants']))
                @foreach($data['variants'] as $vidx => $variant)
                <div class="mb-4" x-data>
                    <p class="text-xs font-bold text-gray-600 uppercase tracking-wider mb-2">
                        {{ $variant['name'] }}
                        @if(!empty($variant['name_cn']) && $variant['name_cn'] !== $variant['name'])
                        <span class="text-gray-400 font-normal normal-case ml-1">{{ $variant['name_cn'] }}</span>
                        @endif
                    </p>
                    <div class="flex flex-wrap gap-2">
                        @foreach($variant['options'] as $opt)
                        @php
                            $hasImg = !empty($opt['image']);
                            $hasPrice = !empty($opt['price_cny']) && $opt['price_cny'] > 0;
                            $key = $variant['name'].':'.$opt['label'];
                            $optPrice = $hasPrice ? (float)$opt['price_cny'] : (float)$data['price_cny'];
                            $imgClick = $hasImg ? "mainImg='".route('img.proxy',['url'=>$opt['image']])."';" : '';
                        @endphp
                        <button type="button"
                            @click="variant='{{ $key }}'; variantPrice={{ $optPrice }}; {{ $imgClick }}"
                            :class="variant==='{{ $key }}' ? 'border-primary ring-2 ring-primary/30' : 'border-gray-200 hover:border-primary/60'"
                            class="border-2 rounded-xl transition active:scale-95 flex items-center gap-2 {{ $hasImg ? 'p-1 pr-3' : 'px-3 py-2' }}">
                            @if($hasImg)
                            <img src="{{ route('img.proxy',['url'=>$opt['image']]) }}"
                                 class="w-9 h-9 rounded-lg object-cover flex-shrink-0"
                                 onerror="this.style.display='none'">
                            @endif
                            <span class="text-xs font-semibold text-gray-700 leading-tight">
                                {{ $opt['label'] }}
                                @if(!empty($opt['label_cn']) && $opt['label_cn'] !== $opt['label'])
                                <span class="block text-[10px] text-gray-400 font-normal">{{ $opt['label_cn'] }}</span>
                                @endif
                                @if($hasPrice)
                                <span class="block text-[10px] text-primary font-bold">¥{{ number_format($opt['price_cny'],2) }}</span>
                                @endif
                            </span>
                        </button>
                        @endforeach
                    </div>
                </div>
                @endforeach
                @endif

                {{-- MOQ + Quantity --}}
                @php $moq = (int)($data['moq'] ?? 1); @endphp
                <div class="mb-4">
                    <p class="text-xs text-gray-500 mb-2 font-semibold">
                        Quantity
                        @if($moq > 1)
                        <span class="text-gray-400 font-normal">(minimum {{ $moq }})</span>
                        @endif
                    </p>
                    <div class="flex items-center gap-4">
                        {{-- Stepper --}}
                        <div class="flex items-center border-2 border-gray-200 rounded-xl overflow-hidden">
                            <button type="button"
                                    @click="qty = Math.max({{ $moq }}, parseInt(qty) - 1)"
                                    class="w-11 h-11 flex items-center justify-center text-gray-600 hover:bg-gray-100 text-xl font-bold transition active:scale-95 select-none">
                                −
                            </button>
                            <input type="number"
                                   x-model.number="qty"
                                   min="{{ $moq }}"
                                   @change="qty = Math.max({{ $moq }}, parseInt(qty) || {{ $moq }})"
                                   class="w-14 h-11 text-center text-sm font-bold border-0 outline-none bg-transparent">
                            <button type="button"
                                    @click="qty = parseInt(qty) + 1"
                                    class="w-11 h-11 flex items-center justify-center text-gray-600 hover:bg-gray-100 text-xl font-bold transition active:scale-95 select-none">
                                +
                            </button>
                        </div>
                        {{-- Live total --}}
                        <div class="bg-primary-50 rounded-xl px-4 py-2.5 flex-1">
                            <p class="text-[10px] text-gray-400 font-semibold uppercase tracking-wider">Total</p>
                            <p class="font-black text-primary text-lg leading-tight" style="font-family:Poppins,sans-serif">
                                ¥<span x-text="(parseFloat(variantPrice) * parseInt(qty || 1)).toFixed(2)"></span>
                            </p>
                            <p class="text-[10px] text-gray-400 mt-0.5">
                                {{ $data['symbol'] ?? '₦' }}<span x-text="(parseFloat(variantPrice) * parseInt(qty || 1) * {{ (float)($data['rate'] ?? 205) }}).toFixed(2)"></span>
                            </p>
                        </div>
                    </div>
                </div>

                {{-- Add to cart --}}
                @if(empty($data['stock_status']) || $data['stock_status'] !== 'out_of_stock')
                <div class="flex flex-col sm:flex-row gap-3 mb-3">
                <form action="{{ route('user.scraper.add-to-cart') }}" method="POST" class="flex-1">
                    @csrf
                    <input type="hidden" name="product_id" value="{{ $data['product_id'] }}">
                    <input type="hidden" name="quantity" :value="qty">
                    <input type="hidden" name="selected_variant" :value="variant">
                    <button type="submit"
                        class="w-full text-white font-black py-4 rounded-2xl text-sm transition btn-glow flex items-center justify-center gap-2 active:scale-95"
                        style="background:linear-gradient(135deg,#F47B20,#D4660E)">
                        <i class="fas fa-cart-plus"></i> Add to Cart
                    </button>
                </form>
                <a href="{{ route('user.checkout') }}"
                    class="flex-1 inline-flex items-center justify-center gap-2 bg-primary hover:bg-primary-dark text-white font-black py-4 rounded-2xl text-sm transition active:scale-95">
                    <i class="fas fa-arrow-right"></i> Checkout
                </a>
                </div>
                @else
                <div class="w-full bg-gray-200 text-gray-500 font-bold py-4 rounded-2xl text-center text-sm mb-3">Out of Stock</div>
                @endif

                <a href="{{ route('user.scraper.index') }}" class="text-xs text-center text-gray-400 hover:text-primary transition block">
                    ← Search Another Product
                </a>
            </div>
        </div>

        {{-- Description --}}
        @if(!empty($data['description']))
        <div class="px-6 pb-6 border-t border-gray-100 pt-5 anim-slide-up" style="animation-delay:.35s">
            <h3 class="font-bold text-gray-900 mb-3 flex items-center gap-2">
                <i class="fas fa-info-circle text-primary text-sm"></i> Product Details
            </h3>
            <div class="bg-gray-50 rounded-2xl p-4 text-sm text-gray-700 whitespace-pre-line leading-relaxed">{{ $data['description'] }}</div>
        </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
// Show the "Product Found!" flash for 1.2s then fade it out and reveal content
(function () {
    var flash   = document.getElementById('found-flash');
    var content = document.getElementById('result-content');
    if (!flash || !content) return;

    setTimeout(function () {
        // Fade out flash
        flash.style.opacity = '0';
        flash.style.pointerEvents = 'none';

        // Show content
        content.style.opacity = '1';

        // Remove flash from DOM after transition
        setTimeout(function () {
            flash.style.display = 'none';
        }, 600);
    }, 1200);
})();
</script>
@endpush
@endsection
