@extends('layouts.app')
@section('title', 'Home — Bringing China Closer to You')
@section('meta_description', 'Shop from 1688, Taobao & Alibaba. Buy from China and get delivered to Nigeria, Ghana & Niger. Fast shipping, best prices, secure payments.')
@section('meta_keywords', 'buy from china nigeria, 1688 nigeria, taobao nigeria, china goods nigeria, import from china, bybhub, china to nigeria shipping')
@section('body_pad', '')
@section('content')

{{-- STRIP BANNER --}}
@if($stripBanner)
<div class="rounded-2xl mb-6 text-white text-center text-xs py-2.5 px-4 font-bold flex items-center justify-center gap-2"
     style="background:linear-gradient(90deg,#F47B20,#D4660E)">
    <i class="fas fa-tag"></i>
    @if($stripBanner->link_url)<a href="{{ $stripBanner->link_url }}" class="hover:underline">@endif
        {{ $stripBanner->title }}
    @if($stripBanner->link_url)</a>@endif
</div>
@endif

{{-- ============================================================ --}}
{{-- HERO SECTION — CSS gradient + emoji illustrations, no image  --}}
{{-- ============================================================ --}}
<section style="width:100vw;margin-left:calc(-50vw + 50%);background:linear-gradient(135deg,#0A1628 0%,#1A3A6B 60%,#1e4d8c 100%);min-height:560px;position:relative;overflow:hidden;" class="flex items-center">

    {{-- Decorative floating circles --}}
    <div style="position:absolute;top:-80px;right:-80px;width:400px;height:400px;border-radius:50%;background:radial-gradient(circle,rgba(244,123,32,0.12),transparent 70%);pointer-events:none;"></div>
    <div style="position:absolute;bottom:-60px;left:-60px;width:300px;height:300px;border-radius:50%;background:radial-gradient(circle,rgba(255,255,255,0.05),transparent 70%);pointer-events:none;"></div>
    <div style="position:absolute;top:40%;left:42%;width:200px;height:200px;border-radius:50%;background:radial-gradient(circle,rgba(36,81,163,0.3),transparent 70%);pointer-events:none;"></div>

    {{-- Animated dots grid --}}
    <div style="position:absolute;inset:0;pointer-events:none;overflow:hidden;">
        @for($i=0;$i<15;$i++)
        <div style="position:absolute;width:{{ rand(3,7) }}px;height:{{ rand(3,7) }}px;border-radius:50%;background:rgba(255,255,255,{{ rand(5,15)/100 }});top:{{ rand(5,90) }}%;left:{{ rand(5,90) }}%;animation:float {{ rand(3,6) }}s ease-in-out infinite;animation-delay:{{ $i*0.3 }}s;"></div>
        @endfor
    </div>

    <div class="max-w-7xl mx-auto px-6 lg:px-8 py-16 w-full">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">

            {{-- LEFT: Text content --}}
            <div>
                <span class="inline-flex items-center gap-2 text-xs font-black uppercase tracking-wider px-4 py-2 rounded-full mb-6"
                      style="background:rgba(244,123,32,0.2);border:1px solid rgba(244,123,32,0.4);color:#F47B20;">
                    🇨🇳 Direct from China
                </span>
                <h1 style="font-family:Poppins,sans-serif;font-size:clamp(2rem,5vw,3.5rem);font-weight:900;color:#fff;line-height:1.1;margin-bottom:1.25rem;">
                    Bringing China<br><span style="color:#F47B20;">Closer to You</span>
                </h1>
                <p style="color:rgba(147,197,253,0.9);font-size:1rem;line-height:1.7;max-width:480px;margin-bottom:2rem;">
                    Shop directly from 1688, Taobao &amp; Alibaba. We procure, ship and deliver to Nigeria, Ghana &amp; Niger with real-time tracking.
                </p>

                {{-- Hero search bar --}}
                <form action="{{ route('products.index') }}" method="GET" class="flex gap-2 mb-6 max-w-lg"
                      x-data="{ q: '' }">
                    <div class="flex-1 relative">
                        <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 text-sm pointer-events-none"></i>
                        <input type="text" name="q" x-model="q"
                               placeholder="Search products or paste 1688 / Taobao link..."
                               class="w-full pl-11 pr-4 py-4 rounded-2xl text-sm bg-white/15 backdrop-blur-sm border-2 border-white/25 text-white placeholder-blue-300/80 focus:outline-none focus:border-white/60 focus:bg-white/20 transition"
                               style="-webkit-text-fill-color:white">
                    </div>
                    <button type="submit"
                            class="flex-shrink-0 bg-accent hover:bg-accent-dark text-white font-bold px-5 py-4 rounded-2xl text-sm transition btn-glow">
                        Search
                    </button>
                </form>
                <div class="flex flex-wrap gap-3">
                    <a href="{{ route('products.index') }}"
                       class="inline-flex items-center gap-2 text-white font-black px-7 py-3.5 rounded-full text-sm transition active:scale-95 hover:-translate-y-0.5 btn-glow"
                       style="background:#F47B20;box-shadow:0 6px 28px rgba(244,123,32,0.4);">
                        <i class="fas fa-store"></i> Browse Products
                    </a>
                    <a href="{{ route('user.scraper.index') }}"
                       class="inline-flex items-center gap-2 text-white font-bold px-7 py-3.5 rounded-full text-sm transition active:scale-95 hover:bg-white/10"
                       style="border:2px solid rgba(255,255,255,0.25);">
                        <i class="fas fa-link"></i> Paste Product Link
                    </a>
                    <a href="{{ route('tracking.index') }}"
                       class="inline-flex items-center gap-2 font-semibold px-5 py-3.5 rounded-full text-sm transition active:scale-95 hover:bg-white/10"
                       style="color:rgba(255,255,255,0.8);border:1px solid rgba(255,255,255,0.15);">
                        <i class="fas fa-search-location text-sm"></i> Track Order
                    </a>
                </div>
                {{-- Trust badges --}}
                <div class="flex flex-wrap gap-4 mt-8">
                    <div style="display:flex;align-items:center;gap:6px;color:rgba(255,255,255,0.7);font-size:0.75rem;">
                        <i class="fas fa-shield-alt" style="color:#4ade80;"></i> Secure Payments
                    </div>
                    <div style="display:flex;align-items:center;gap:6px;color:rgba(255,255,255,0.7);font-size:0.75rem;">
                        <i class="fas fa-truck" style="color:#60a5fa;"></i> Fast Shipping
                    </div>
                    <div style="display:flex;align-items:center;gap:6px;color:rgba(255,255,255,0.7);font-size:0.75rem;">
                        <i class="fas fa-star" style="color:#F47B20;"></i> Growing every day
                    </div>
                </div>
            </div>

            {{-- RIGHT: CSS/Emoji shipping illustration --}}
            <div class="hidden lg:flex items-center justify-center relative" style="min-height:380px;">
                {{-- Central globe --}}
                <div class="animate-float" style="position:absolute;top:50%;left:50%;transform:translate(-50%,-55%);font-size:5rem;filter:drop-shadow(0 8px 20px rgba(0,0,0,0.3));">🌐</div>
                {{-- Big ship bottom --}}
                <div class="animate-float2" style="position:absolute;bottom:10px;left:50%;transform:translateX(-50%);font-size:4.5rem;filter:drop-shadow(0 6px 16px rgba(0,0,0,0.4));">🚢</div>
                {{-- Floating boxes --}}
                <div style="position:absolute;top:15%;left:8%;animation:float 3.8s ease-in-out infinite;animation-delay:0.5s;font-size:2.2rem;filter:drop-shadow(0 4px 10px rgba(0,0,0,0.3));">📦</div>
                <div style="position:absolute;top:20%;right:10%;animation:float 4.2s ease-in-out infinite;animation-delay:1s;font-size:1.8rem;filter:drop-shadow(0 4px 10px rgba(0,0,0,0.3));">📦</div>
                <div style="position:absolute;top:55%;right:5%;animation:float 3.5s ease-in-out infinite;animation-delay:1.5s;font-size:2rem;filter:drop-shadow(0 4px 10px rgba(0,0,0,0.3));">📦</div>
                {{-- Plane --}}
                <div style="position:absolute;top:5%;right:20%;animation:float 5s ease-in-out infinite;animation-delay:0.8s;font-size:2.5rem;filter:drop-shadow(0 4px 10px rgba(0,0,0,0.3));">✈️</div>
                {{-- Flag --}}
                <div style="position:absolute;top:5%;left:20%;animation:float 4s ease-in-out infinite;animation-delay:0.3s;font-size:2rem;">🇨🇳</div>
                {{-- Nigeria flag --}}
                <div style="position:absolute;bottom:15%;left:5%;animation:float 3.2s ease-in-out infinite;animation-delay:0.7s;font-size:1.8rem;">🇳🇬</div>
                {{-- Dotted connecting lines via SVG --}}
                <svg style="position:absolute;inset:0;width:100%;height:100%;pointer-events:none;opacity:0.25;" viewBox="0 0 400 380">
                    <line x1="200" y1="160" x2="80" y2="80" stroke="#F47B20" stroke-width="1.5" stroke-dasharray="6,4"/>
                    <line x1="200" y1="160" x2="330" y2="90" stroke="#F47B20" stroke-width="1.5" stroke-dasharray="6,4"/>
                    <line x1="200" y1="160" x2="200" y2="300" stroke="#60a5fa" stroke-width="1.5" stroke-dasharray="6,4"/>
                    <circle cx="200" cy="160" r="4" fill="#F47B20"/>
                    <circle cx="80" cy="80" r="3" fill="#F47B20"/>
                    <circle cx="330" cy="90" r="3" fill="#F47B20"/>
                    <circle cx="200" cy="300" r="3" fill="#60a5fa"/>
                </svg>
                {{-- Glowing backdrop --}}
                <div style="position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);width:280px;height:280px;border-radius:50%;background:radial-gradient(circle,rgba(36,81,163,0.4),transparent 70%);pointer-events:none;"></div>
            </div>
        </div>
    </div>
</section>

{{-- ============================================================ --}}
{{-- LIVE RATES BAR                                               --}}
{{-- ============================================================ --}}
<div class="section-reveal" style="margin:0 0 2.5rem 0;">
    <div class="flex flex-wrap items-center justify-between gap-4 px-5 py-3.5 rounded-2xl"
         style="background:linear-gradient(135deg,#112548,#1A3A6B);box-shadow:0 6px 24px rgba(26,58,107,0.25);">
        <div class="flex items-center gap-2.5">
            <span style="width:10px;height:10px;border-radius:50%;background:#4ade80;display:inline-block;animation:pulse-dot 2s ease-in-out infinite;"></span>
            <span style="color:rgba(255,255,255,0.65);font-size:0.7rem;font-weight:800;text-transform:uppercase;letter-spacing:0.1em;">Live Rates</span>
        </div>
        <div class="flex flex-wrap gap-3">
            @foreach($rates as $currency => $rate)
            @php
                $sym2 = match($currency) { 'GHS' => '₵', 'XOF' => 'Fr', default => '₦' };
                $flag = match($currency) { 'GHS' => '🇬🇭', 'XOF' => '🇳🇪', default => '🇳🇬' };
            @endphp
            <div style="display:flex;align-items:center;gap:6px;background:rgba(255,255,255,0.1);padding:6px 14px;border-radius:50px;">
                <span>{{ $flag }}</span>
                <span style="color:#fff;font-weight:800;font-size:0.9rem;font-family:Poppins,sans-serif;">{{ $sym2 }}{{ number_format((float)($rate['rate']??0),2) }}</span>
                <span style="color:rgba(255,255,255,0.5);font-size:0.7rem;">{{ $currency }}</span>
            </div>
            @endforeach
        </div>
        <a href="{{ route('shipping.calculator') }}"
           style="display:inline-flex;align-items:center;gap:6px;color:#fff;font-size:0.75rem;font-weight:700;padding:8px 16px;border-radius:50px;background:rgba(244,123,32,0.25);border:1px solid rgba(244,123,32,0.4);text-decoration:none;transition:all 0.2s;"
           onmouseover="this.style.background='rgba(244,123,32,0.45)'"
           onmouseout="this.style.background='rgba(244,123,32,0.25)'">
            <i class="fas fa-calculator"></i> Calc Shipping
        </a>
    </div>
</div>

{{-- ============================================================ --}}
{{-- FLASH SALE (if active)                                       --}}
{{-- ============================================================ --}}
@if($flashSale->isNotEmpty())
<section class="mb-12 section-reveal">
    <div class="flex items-center justify-between mb-5">
        <div class="flex items-center gap-3">
            <span class="inline-flex items-center gap-1.5 bg-red-500 text-white text-xs font-black px-3 py-1.5 rounded-xl shadow-md animate-pulse">
                <i class="fas fa-bolt"></i> FLASH SALE
            </span>
            <h2 class="text-xl md:text-2xl font-black text-gray-900" style="font-family:Poppins,sans-serif;">Limited Time Deals</h2>
        </div>
        <a href="{{ route('products.index') }}" class="text-sm text-accent hover:underline font-bold flex items-center gap-1">View All <i class="fas fa-arrow-right text-xs"></i></a>
    </div>
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        @foreach($flashSale as $product)
            @include('partials.product-card', ['product'=>$product,'symbol'=>$symbol,'currency'=>$currency,'rate'=>(float)($currentRate??222)])
        @endforeach
    </div>
</section>
@endif

{{-- ============================================================ --}}
{{-- CATEGORIES — Colorful emoji cards                           --}}
{{-- ============================================================ --}}
<section class="mb-14 section-reveal">
    <div class="flex items-center justify-between mb-6">
        <div>
            <p class="text-xs font-black uppercase tracking-widest text-accent mb-1">Explore</p>
            <h2 class="text-2xl md:text-3xl font-black text-gray-900" style="font-family:Poppins,sans-serif;">Shop by Category</h2>
        </div>
        <a href="{{ route('products.index') }}" class="text-sm text-accent hover:underline font-bold flex items-center gap-1">View All <i class="fas fa-arrow-right text-xs"></i></a>
    </div>
    <div class="grid grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-3">
        @php
        $catConfig = [
            'electronics'  => ['🖥️','linear-gradient(135deg,#1e40af,#3b82f6)','#bfdbfe'],
            'fashion'       => ['👗','linear-gradient(135deg,#be185d,#ec4899)','#fbcfe8'],
            'home'          => ['🏠','linear-gradient(135deg,#065f46,#10b981)','#a7f3d0'],
            'health'        => ['💊','linear-gradient(135deg,#6d28d9,#a78bfa)','#ede9fe'],
            'sports'        => ['⚽','linear-gradient(135deg,#c2410c,#f97316)','#fed7aa'],
            'toys'          => ['🧸','linear-gradient(135deg,#b45309,#f59e0b)','#fde68a'],
            'auto'          => ['🚗','linear-gradient(135deg,#991b1b,#ef4444)','#fecaca'],
            'tools'         => ['🔧','linear-gradient(135deg,#374151,#6b7280)','#e5e7eb'],
            'baby'          => ['👶','linear-gradient(135deg,#c2410c,#fb923c)','#fed7aa'],
            'food'          => ['🥗','linear-gradient(135deg,#3f6212,#84cc16)','#d9f99d'],
            'office'        => ['💼','linear-gradient(135deg,#0e7490,#06b6d4)','#cffafe'],
            'phones'        => ['📱','linear-gradient(135deg,#3730a3,#6366f1)','#e0e7ff'],
        ];
        $defaultEmojis  = ['🛍️','📦','🎁','🛒','✨','💡','🎯','🎪','🌟','💎','🏆','🎨'];
        $defaultGrads   = [
            'linear-gradient(135deg,#1e40af,#3b82f6)',
            'linear-gradient(135deg,#be185d,#ec4899)',
            'linear-gradient(135deg,#065f46,#10b981)',
            'linear-gradient(135deg,#6d28d9,#a78bfa)',
            'linear-gradient(135deg,#c2410c,#f97316)',
            'linear-gradient(135deg,#b45309,#f59e0b)',
            'linear-gradient(135deg,#991b1b,#ef4444)',
            'linear-gradient(135deg,#374151,#6b7280)',
            'linear-gradient(135deg,#c2410c,#fb923c)',
            'linear-gradient(135deg,#3f6212,#84cc16)',
            'linear-gradient(135deg,#0e7490,#06b6d4)',
            'linear-gradient(135deg,#3730a3,#6366f1)',
        ];
        @endphp
        @foreach($categories as $idx => $cat)
        @php
            $slug = $cat->slug ?? '';
            $matched = null;
            foreach ($catConfig as $key => $cfg) {
                if (str_contains(strtolower($slug), $key) || str_contains(strtolower($cat->getTranslatedName()), $key)) {
                    $matched = $cfg; break;
                }
            }
            $emoji  = $matched ? $matched[0] : ($defaultEmojis[$idx % count($defaultEmojis)]);
            $grad   = $matched ? $matched[1] : ($defaultGrads[$idx % count($defaultGrads)]);
            $textC  = $matched ? $matched[2] : '#fff';
        @endphp
        <a href="{{ route('products.category',$cat->slug) }}"
           class="group relative rounded-2xl p-4 text-center overflow-hidden card-hover cursor-pointer"
           style="background:{{ $grad }};box-shadow:0 4px 15px rgba(0,0,0,0.15);">
            {{-- Decorative circle --}}
            <div style="position:absolute;top:-20px;right:-20px;width:80px;height:80px;border-radius:50%;background:rgba(255,255,255,0.1);pointer-events:none;"></div>
            <div class="relative z-10">
                <div style="font-size:2.2rem;margin-bottom:8px;display:block;filter:drop-shadow(0 2px 6px rgba(0,0,0,0.25));" class="group-hover:scale-125 transition-transform duration-300 inline-block">{{ $emoji }}</div>
                <p style="color:#fff;font-size:0.72rem;font-weight:700;line-height:1.2;" class="truncate">{{ $cat->getTranslatedName() }}</p>
                @if($cat->products_count > 0)
                <p style="color:rgba(255,255,255,0.7);font-size:0.65rem;margin-top:2px;">{{ $cat->products_count }} items</p>
                @endif
            </div>
        </a>
        @endforeach
    </div>
</section>

{{-- ── DIFFERENTIATOR STRIP: Social proof + live CNY rate + trust signals ─── --}}
<section class="mb-10 section-reveal">
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">

        {{-- Live CNY rate card --}}
        <div class="bg-gradient-to-br from-primary to-blue-700 rounded-2xl p-4 text-white flex items-center gap-4 shadow-lg">
            <div class="w-11 h-11 bg-white/15 rounded-xl flex items-center justify-center flex-shrink-0 text-2xl">🇨🇳</div>
            <div>
                <p class="text-blue-200 text-[11px] font-semibold uppercase tracking-wide">Live CNY → NGN</p>
                @php
                    $liveRate = \App\Models\ExchangeRate::where('from_currency','CNY')
                        ->where('to_currency','NGN')->value('rate') ?? 222;
                @endphp
                <p class="text-2xl font-black leading-none" style="font-family:Poppins,sans-serif">
                    ₦{{ number_format((float)$liveRate, 0) }}
                </p>
                <p class="text-blue-300 text-[10px] mt-0.5">per 1 Chinese Yuan — updated daily</p>
            </div>
        </div>

        {{-- Social proof --}}
        <div class="bg-gradient-to-br from-orange-500 to-accent rounded-2xl p-4 text-white flex items-center gap-4 shadow-lg">
            <div class="w-11 h-11 bg-white/15 rounded-xl flex items-center justify-center flex-shrink-0 text-2xl">📦</div>
            <div>
                @php
                    $totalOrders = \App\Models\Order::count();
                    $totalUsers  = \App\Models\User::where('is_admin', false)->count();
                @endphp
                <p class="text-orange-200 text-[11px] font-semibold uppercase tracking-wide">Orders Placed</p>
                <p class="text-2xl font-black leading-none" style="font-family:Poppins,sans-serif">
                    {{ number_format($totalOrders) }}+
                </p>
                <p class="text-orange-200 text-[10px] mt-0.5">by {{ number_format($totalUsers) }}+ shoppers</p>
            </div>
        </div>

        {{-- Sourcing guarantee --}}
        <div class="bg-gradient-to-br from-green-600 to-emerald-700 rounded-2xl p-4 text-white flex items-center gap-4 shadow-lg">
            <div class="w-11 h-11 bg-white/15 rounded-xl flex items-center justify-center flex-shrink-0 text-2xl">🛡️</div>
            <div>
                <p class="text-green-200 text-[11px] font-semibold uppercase tracking-wide">Our Guarantee</p>
                <p class="text-base font-black leading-tight" style="font-family:Poppins,sans-serif">
                    Pay only after<br>we confirm price
                </p>
                <p class="text-green-300 text-[10px] mt-0.5">No hidden fees. Ever.</p>
            </div>
        </div>

    </div>
</section>

{{-- TRENDING --}}
@if($trending->isNotEmpty())
<section class="mb-12 section-reveal">
    <div class="flex items-center justify-between mb-5">
        <div class="flex items-center gap-2.5">
            <span class="text-2xl">🔥</span>
            <h2 class="text-xl md:text-2xl font-black text-gray-900" style="font-family:Poppins,sans-serif;">Trending Now</h2>
        </div>
        <a href="{{ route('products.index',['sort'=>'trending']) }}" class="text-sm text-accent hover:underline font-bold flex items-center gap-1">View All <i class="fas fa-arrow-right text-xs"></i></a>
    </div>
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        @foreach($trending as $product)
            @include('partials.product-card', ['product'=>$product,'symbol'=>$symbol,'currency'=>$currency,'rate'=>(float)($currentRate??222)])
        @endforeach
    </div>
</section>
@endif

{{-- ============================================================ --}}
{{-- ============================================================ --}}
{{-- BECOME A VENDOR — animated CTA box                         --}}
{{-- ============================================================ --}}
@php $vendorOpen = \App\Models\Setting::get('vendor_marketplace_open','0') === '1'; @endphp
<section class="mb-14 section-reveal">
    <div class="relative rounded-3xl overflow-hidden"
         style="background:linear-gradient(135deg,#0A1628 0%,#1A3A6B 40%,#2451A3 70%,#F47B20 100%)"
         x-data="{
            show: false,
            boxes: [0,1,2,3,4],
            init(){ let io = new IntersectionObserver(e=>{ if(e[0].isIntersecting){ this.show=true; io.disconnect(); } },{threshold:.3}); io.observe(this.$el); }
         }">

        {{-- Floating decorations --}}
        <div class="absolute inset-0 pointer-events-none overflow-hidden">
            <div class="absolute top-8 right-24 text-6xl opacity-10 animate-bounce-soft">📦</div>
            <div class="absolute bottom-8 right-48 text-5xl opacity-10" style="animation:float 4s ease-in-out infinite">🛍️</div>
            <div class="absolute top-12 left-1/2 text-4xl opacity-10" style="animation:float2 5s ease-in-out infinite">💰</div>
            <div class="absolute bottom-12 left-24 text-5xl opacity-10" style="animation:float 3.5s ease-in-out infinite 0.5s">🏪</div>
            {{-- Animated circles --}}
            <div class="absolute top-0 right-0 w-72 h-72 rounded-full opacity-10" style="background:radial-gradient(circle,#F47B20,transparent);transform:translate(30%,-30%)"></div>
            <div class="absolute bottom-0 left-0 w-60 h-60 rounded-full opacity-8" style="background:radial-gradient(circle,white,transparent);transform:translate(-30%,30%)"></div>
        </div>

        <div class="relative px-6 md:px-12 py-10 md:py-14">
            <div class="flex flex-col md:flex-row items-center gap-10">

                {{-- Animated gift box --}}
                <div class="flex-shrink-0 flex flex-col items-center"
                     x-show="show" x-transition:enter="transition duration-700" x-transition:enter-start="opacity-0 scale-50" x-transition:enter-end="opacity-100 scale-100">
                    <div class="relative" style="width:120px;height:130px">
                        {{-- Box body --}}
                        <div class="absolute bottom-0 left-0 right-0 rounded-2xl flex flex-col items-center justify-end pb-3 shadow-2xl"
                             style="height:80px;background:linear-gradient(135deg,#F47B20,#D4660E)">
                            <div class="w-full h-4 absolute top-0 left-0 right-0 rounded-t-2xl" style="background:rgba(0,0,0,0.15)"></div>
                            <p class="text-white text-[10px] font-black tracking-widest">BYBHUB</p>
                        </div>
                        {{-- Box lid — animated open --}}
                        <div class="absolute left-0 right-0 h-10 rounded-t-2xl shadow-xl"
                             style="background:linear-gradient(135deg,#D4660E,#B0520B);top:0;transform-origin:bottom center;animation:lidOpen 2s ease-in-out infinite alternate;">
                            {{-- Ribbon vertical --}}
                            <div class="absolute left-1/2 -translate-x-1/2 top-0 bottom-0 w-5" style="background:rgba(255,255,255,0.25)"></div>
                            {{-- Bow --}}
                            <div class="absolute -top-3 left-1/2 -translate-x-1/2 text-white text-lg">🎀</div>
                        </div>
                        {{-- Popping stars --}}
                        <div class="absolute -top-4 left-1/2 -translate-x-1/2 text-lg" style="animation:popStars 2s ease-in-out infinite alternate 0.3s">✨💫✨</div>
                    </div>
                </div>

                {{-- Text content --}}
                <div class="flex-1 text-white text-center md:text-left"
                     x-show="show" x-transition:enter="transition duration-700 delay-200" x-transition:enter-start="opacity-0 translate-y-6" x-transition:enter-end="opacity-100 translate-y-0">
                    <span class="inline-flex items-center gap-2 text-[11px] font-bold uppercase tracking-widest px-3 py-1 rounded-full mb-3"
                          style="background:rgba(255,255,255,0.15);border:1px solid rgba(255,255,255,0.25)">
                        🇳🇬 Open to Nigerian Sellers
                    </span>
                    <h2 class="text-2xl md:text-3xl font-black mb-3" style="font-family:Poppins,sans-serif">
                        Grow Your Business on<br><span style="color:#F47B20">BYBHUB Marketplace</span>
                    </h2>
                    <p class="text-white/75 text-sm leading-relaxed mb-5 max-w-lg">
                        Reach thousands of buyers across Nigeria. List your products for free, receive orders directly,
                        and get paid through our <strong class="text-white">secure escrow system</strong> —
                        your funds are held safely and released once the buyer confirms delivery.
                    </p>
                    {{-- Benefits --}}
                    <div class="flex flex-wrap gap-2 mb-6 justify-center md:justify-start">
                        @foreach([
                            '🔒 Secure Escrow',
                            '💸 Fast Payouts',
                            '📦 Free Listing',
                            '🛡️ Verified Store Badge',
                            '📊 Sales Dashboard',
                            '📱 Instant Order Alerts',
                        ] as $benefit)
                        <span class="text-[11px] font-semibold px-3 py-1 rounded-full"
                              style="background:rgba(255,255,255,0.12);border:1px solid rgba(255,255,255,0.2);color:rgba(255,255,255,0.9)">
                            {{ $benefit }}
                        </span>
                        @endforeach
                    </div>
                    {{-- CTA buttons --}}
                    <div class="flex flex-wrap gap-3 justify-center md:justify-start">
                        @if($vendorOpen)
                        @php $activationFee = \App\Models\Setting::get('vendor_activation_fee','5000'); @endphp
                        <a href="{{ route('vendor.apply') }}"
                            class="inline-flex items-center gap-2.5 font-bold px-7 py-3.5 rounded-2xl text-sm transition hover:-translate-y-1 hover:shadow-2xl"
                            style="background:linear-gradient(135deg,#F47B20,#D4660E);color:white;box-shadow:0 6px 30px rgba(244,123,32,0.45)">
                            <i class="fas fa-store text-sm"></i>
                            Apply to Sell on BYBHUB
                            @if((float)$activationFee > 0)
                            <span class="text-[11px] font-normal opacity-80">&nbsp;· ₦{{ number_format((float)$activationFee, 0) }} one-time fee</span>
                            @else
                            <span class="text-[11px] font-normal opacity-80">&nbsp;· Free to join</span>
                            @endif
                        </a>
                        @else
                        <a href="{{ route('register') }}"
                            class="inline-flex items-center gap-2.5 font-bold px-7 py-3.5 rounded-2xl text-sm transition hover:-translate-y-1"
                            style="background:rgba(255,255,255,0.18);color:white;border:2px solid rgba(255,255,255,0.3)">
                            <i class="fas fa-bell text-sm"></i>
                            Create an Account — Be First to Know
                        </a>
                        @endif
                        <a href="{{ route('products.index', ['type'=>'local']) }}"
                            class="inline-flex items-center gap-2 font-semibold px-6 py-3.5 rounded-2xl text-sm transition hover:-translate-y-1"
                            style="background:rgba(255,255,255,0.08);color:rgba(255,255,255,0.85);border:1px solid rgba(255,255,255,0.18)">
                            <i class="fas fa-arrow-right text-xs"></i> Browse Local Products
                        </a>
                    </div>
                </div>

                {{-- Stats --}}
                <div class="flex md:flex-col gap-4 flex-shrink-0"
                     x-show="show" x-transition:enter="transition duration-700 delay-400" x-transition:enter-start="opacity-0 translate-x-6" x-transition:enter-end="opacity-100 translate-x-0">
                    @foreach([['🏪','Vendors','Active stores'],['🛡️','Escrow','Protected'],['💰','24–48h','Payout time']] as [$icon,$val,$sub])
                    <div class="text-center px-6 py-4 rounded-2xl" style="background:rgba(255,255,255,0.1);border:1px solid rgba(255,255,255,0.15)">
                        <div class="text-2xl mb-1">{{ $icon }}</div>
                        <p class="font-black text-white text-lg">{{ $val }}</p>
                        <p class="text-white/60 text-[11px]">{{ $sub }}</p>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</section>

<style>
@keyframes lidOpen { 0%{transform:rotate(0deg) translateY(0)} 100%{transform:rotate(-25deg) translateY(-8px)} }
@keyframes popStars { 0%{transform:translateX(-50%) translateY(0);opacity:0.6} 100%{transform:translateX(-50%) translateY(-12px);opacity:1} }
</style>

{{-- ============================================================ --}}
{{-- LOCAL MARKETPLACE — Nigerian Vendor Products               --}}
{{-- ============================================================ --}}
@php $localProducts = \App\Models\Product::where('product_type','local')->where('is_active',true)->where('is_approved',true)->with(['vendor','category'])->latest()->take(8)->get(); @endphp
@if($localProducts->isNotEmpty())
<section class="mb-14 section-reveal">
    <div class="rounded-3xl overflow-hidden" style="background:linear-gradient(135deg,#0f2347 0%,#1A3A6B 60%,#F47B20 100%)">
        <div class="px-6 md:px-10 pt-8 pb-4">
            <div class="flex items-center justify-between flex-wrap gap-3 mb-6">
                <div>
                    <span class="inline-flex items-center gap-2 bg-white/10 border border-white/20 text-white text-[11px] font-bold px-3 py-1 rounded-full mb-2">
                        <span class="w-1.5 h-1.5 bg-green-400 rounded-full animate-pulse"></span>
                        🇳🇬 Nigerian Vendors
                    </span>
                    <h2 class="text-xl md:text-2xl font-black text-white" style="font-family:Poppins,sans-serif">
                        Local Marketplace
                    </h2>
                    <p class="text-white/60 text-sm mt-0.5">Buy directly from verified Nigerian sellers — fast, local, trusted.</p>
                </div>
                <a href="{{ route('products.index', ['type' => 'local']) }}"
                    class="flex items-center gap-2 bg-accent hover:bg-orange-500 text-white font-bold px-5 py-2.5 rounded-xl text-sm transition">
                    Browse All <i class="fas fa-arrow-right text-xs"></i>
                </a>
            </div>
        </div>
        <div class="px-6 md:px-10 pb-8 grid grid-cols-2 md:grid-cols-4 gap-4">
            @foreach($localProducts as $lp)
            <a href="{{ route('products.show', $lp) }}"
                class="bg-white rounded-2xl overflow-hidden shadow-lg hover:shadow-2xl hover:-translate-y-1 transition-all duration-300 group block">
                <div class="aspect-square overflow-hidden bg-gray-50">
                    <img loading="lazy" src="{{ ($lp->images[0] ?? null) ? Storage::url($lp->images[0]) : asset('images/placeholder.svg') }}"
                         alt="{{ $lp->title }}"
                         class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                </div>
                <div class="p-3">
                    <p class="text-[10px] text-accent font-bold mb-0.5 flex items-center gap-1">
                        <i class="fas fa-store text-[8px]"></i> {{ $lp->vendor?->store_name ?? 'Local Vendor' }}
                    </p>
                    <p class="font-semibold text-gray-800 text-xs leading-tight line-clamp-2 mb-1.5">{{ $lp->title }}</p>
                    <p class="font-black text-primary text-sm" style="font-family:Poppins,sans-serif">
                        ₦{{ number_format((float)$lp->price_ngn, 0) }}
                    </p>
                </div>
            </a>
            @endforeach
        </div>
    </div>
</section>
@endif

{{-- HOW IT WORKS — White bg, 4 steps with connecting arrows     --}}
{{-- ============================================================ --}}
<section class="mb-14 section-reveal">
    <div class="bg-white rounded-3xl p-8 md:p-12" style="box-shadow:0 4px 30px rgba(26,58,107,0.08);">
        <div class="text-center mb-10">
            <span class="text-accent text-xs font-black uppercase tracking-widest">Simple Process</span>
            <h2 class="text-2xl md:text-3xl font-black text-gray-900 mt-2" style="font-family:Poppins,sans-serif;">How BYBHUB Works</h2>
            <p class="text-gray-500 mt-2 text-sm">From China to your door in 4 simple steps</p>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-0 relative">
            {{-- Connecting line (desktop) --}}
            <div class="hidden md:block absolute top-10 left-[12.5%] right-[12.5%] h-0.5 z-0" style="background:linear-gradient(90deg,#F47B20,#3b82f6,#10b981,#a78bfa);"></div>
            @foreach([
                ['emoji'=>'🔍','num'=>'01','title'=>'Find Product','desc'=>'Browse catalogue or paste any 1688, Taobao or Alibaba link.','bg'=>'linear-gradient(135deg,#f97316,#F47B20)','delay'=>'0s'],
                ['emoji'=>'💳','num'=>'02','title'=>'Fund Wallet','desc'=>'Add funds via virtual account, card or bank transfer.','bg'=>'linear-gradient(135deg,#3b82f6,#1d4ed8)','delay'=>'0.1s'],
                ['emoji'=>'🛒','num'=>'03','title'=>'Place Order','desc'=>'We procure goods from your supplier and warehouse in China.','bg'=>'linear-gradient(135deg,#10b981,#065f46)','delay'=>'0.2s'],
                ['emoji'=>'📬','num'=>'04','title'=>'Receive Goods','desc'=>'We ship to your country. Track from warehouse to door.','bg'=>'linear-gradient(135deg,#a78bfa,#6d28d9)','delay'=>'0.3s'],
            ] as $step)
            <div class="relative z-10 flex flex-col items-center text-center px-4 group" style="animation-delay:{{ $step['delay'] }}">
                <div class="w-20 h-20 rounded-2xl flex items-center justify-center mb-4 shadow-lg group-hover:scale-110 transition-transform duration-300 text-3xl" style="background:{{ $step['bg'] }};">
                    {{ $step['emoji'] }}
                </div>
                <div class="w-7 h-7 rounded-full bg-white border-4 border-gray-200 flex items-center justify-center text-[10px] font-black text-gray-400 mb-3" style="box-shadow:0 2px 8px rgba(0,0,0,0.1);">{{ $step['num'] }}</div>
                <h3 class="font-black text-gray-900 text-base mb-2" style="font-family:Poppins,sans-serif;">{{ $step['title'] }}</h3>
                <p class="text-gray-500 text-xs leading-relaxed">{{ $step['desc'] }}</p>
            </div>
            @endforeach
        </div>
        <div class="text-center mt-10">
            <a href="{{ route('register') }}"
               class="inline-flex items-center gap-2 text-white font-black px-10 py-4 rounded-full text-sm transition btn-glow"
               style="background:linear-gradient(135deg,#F47B20,#D4660E);">
                Start Shopping Now — It's Free <i class="fas fa-arrow-right"></i>
            </a>
        </div>
    </div>
</section>

{{-- Recently Added --}}
@if($recentlyAdded->isNotEmpty())
<section class="mb-12 section-reveal">
    <div class="flex items-center justify-between mb-5">
        <div class="flex items-center gap-2"><span class="text-2xl">✨</span>
            <h2 class="text-xl md:text-2xl font-black text-gray-900" style="font-family:Poppins,sans-serif;">Recently Added</h2>
        </div>
        <a href="{{ route('products.index',['sort'=>'newest']) }}" class="text-sm text-accent hover:underline font-bold flex items-center gap-1">View All <i class="fas fa-arrow-right text-xs"></i></a>
    </div>
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        @foreach($recentlyAdded as $product)
            @include('partials.product-card', ['product'=>$product,'symbol'=>$symbol,'currency'=>$currency,'rate'=>(float)($currentRate??222)])
        @endforeach
    </div>
</section>
@endif

{{-- Most Ordered --}}
@if($mostOrdered->isNotEmpty())
<section class="mb-12 section-reveal">
    <div class="flex items-center justify-between mb-5">
        <div class="flex items-center gap-2"><span class="text-2xl">⭐</span>
            <h2 class="text-xl md:text-2xl font-black text-gray-900" style="font-family:Poppins,sans-serif;">Most Ordered</h2>
        </div>
        <a href="{{ route('products.index',['sort'=>'popular']) }}" class="text-sm text-accent hover:underline font-bold flex items-center gap-1">View All <i class="fas fa-arrow-right text-xs"></i></a>
    </div>
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        @foreach($mostOrdered as $product)
            @include('partials.product-card', ['product'=>$product,'symbol'=>$symbol,'currency'=>$currency,'rate'=>(float)($currentRate??222)])
        @endforeach
    </div>
</section>
@endif

{{-- MOST VIEWED / POPULAR SEARCHES --}}
@if($mostViewed->isNotEmpty())
<section class="mb-12 section-reveal">
    <div class="flex items-center justify-between mb-5">
        <div class="flex items-center gap-3">
            <span class="text-2xl">🔥</span>
            <div>
                <h2 class="text-xl md:text-2xl font-black text-gray-900" style="font-family:Poppins,sans-serif;">People Are Looking At</h2>
                <p class="text-xs text-gray-400 mt-0.5">Most browsed products right now</p>
            </div>
        </div>
        <a href="{{ route('products.index',['sort'=>'trending']) }}" class="text-sm text-accent hover:underline font-bold flex items-center gap-1">View All <i class="fas fa-arrow-right text-xs"></i></a>
    </div>
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        @foreach($mostViewed as $product)
            @include('partials.product-card', ['product'=>$product,'symbol'=>$symbol,'currency'=>$currency,'rate'=>(float)($currentRate??222)])
        @endforeach
    </div>
</section>
@endif

{{-- EDITORS PICK --}}
@if($editorsPick->isNotEmpty())
<section class="mb-12 section-reveal">
    <div class="flex items-center justify-between mb-5">
        <div class="flex items-center gap-2"><span class="text-2xl">🎯</span>
            <h2 class="text-xl md:text-2xl font-black text-gray-900" style="font-family:Poppins,sans-serif;">Editor's Pick</h2>
        </div>
        <a href="{{ route('products.index') }}" class="text-sm text-accent hover:underline font-bold flex items-center gap-1">View All <i class="fas fa-arrow-right text-xs"></i></a>
    </div>
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        @foreach($editorsPick as $product)
            @include('partials.product-card', ['product'=>$product,'symbol'=>$symbol,'currency'=>$currency,'rate'=>(float)($currentRate??222)])
        @endforeach
    </div>
</section>
@endif

{{-- ============================================================ --}}
{{-- SCRAPER PROMO — Encourage product link pasting               --}}
{{-- ============================================================ --}}
<section class="mb-14 section-reveal">
    <div class="rounded-3xl overflow-hidden relative p-8 md:p-12"
         style="background:linear-gradient(135deg,#312e81 0%,#4f46e5 50%,#7c3aed 100%);">
        <div style="position:absolute;top:-40px;right:-40px;width:200px;height:200px;border-radius:50%;background:radial-gradient(circle,rgba(255,255,255,0.08),transparent);pointer-events:none;"></div>
        <div class="relative z-10 grid grid-cols-1 md:grid-cols-2 gap-8 items-center">
            <div>
                <span class="inline-flex items-center gap-2 bg-white/15 text-white text-xs font-bold px-3 py-1.5 rounded-full mb-4">
                    <i class="fab fa-searchengin"></i> Product Finder
                </span>
                <h2 class="text-2xl md:text-3xl font-black text-white mb-3" style="font-family:Poppins,sans-serif;">
                    Found a Product on 1688 or Taobao?
                </h2>
                <p class="text-indigo-200 text-sm leading-relaxed mb-5">
                    Just paste the product URL and we'll extract all details — title, price, images, variants, stock — in seconds. Then add to cart and order.
                </p>
                <a href="{{ route('user.scraper.index') }}"
                   class="inline-flex items-center gap-2 bg-white text-indigo-700 font-black px-6 py-3 rounded-2xl text-sm hover:bg-indigo-50 transition">
                    <i class="fas fa-link"></i> Paste Product URL
                    <i class="fas fa-arrow-right text-xs"></i>
                </a>
            </div>
            <div class="hidden md:flex items-center justify-center gap-4">
                @foreach(['🏭 1688.com','🛍 Taobao','🌐 Alibaba'] as $platform)
                <div class="bg-white/10 border border-white/20 rounded-2xl px-4 py-3 text-center text-white text-xs font-bold">
                    {{ $platform }}
                </div>
                @endforeach
            </div>
        </div>
    </div>
</section>

{{-- ============================================================ --}}
{{-- WHY BYBHUB — 3x2 grid of feature cards                     --}}
{{-- ============================================================ --}}
{{-- WHY CHOOSE — Redesigned with hover effects & better visual  --}}
{{-- ============================================================ --}}
<section class="mb-14 section-reveal">
    <div class="text-center mb-10">
        <span class="inline-block bg-accent/10 text-accent text-xs font-black uppercase tracking-widest px-4 py-1.5 rounded-full mb-3">Our Promise</span>
        <h2 class="text-2xl md:text-3xl font-black text-gray-900" style="font-family:Poppins,sans-serif;">Why 1,000s Choose BYBHUB</h2>
        <p class="text-gray-500 text-sm mt-2 max-w-lg mx-auto">Everything you need to buy from China — from browsing to delivery — in one platform.</p>
    </div>
    <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
        @foreach([
            ['💰','Lowest Prices','Save 40–70% vs local retail. Buy direct from factories at 1688, Taobao, Alibaba.','from-green-50 to-emerald-100','#059669'],
            ['🔒','Escrow Protection','Your money is safe. We only pay suppliers after confirming availability. Full refunds on failed orders.','from-blue-50 to-blue-100','#1d4ed8'],
            ['🚢','Sea & Air Shipping','Choose what suits you. Sea for bulk orders, air for speed. Real weight = real price, no surprises.','from-teal-50 to-teal-100','#0f766e'],
            ['💳','Smart Wallet','Fund once. Order many times. Pay with Klump BNPL, Paystack, or bank transfer.','from-purple-50 to-purple-100','#7c3aed'],
            ['🤖','AI Assistant','Ask our bot anything 24/7 — product prices, shipping, how to order. No waiting for support.','from-indigo-50 to-indigo-100','#4338ca'],
            ['🎧','Real Support','Dedicated human support via WhatsApp and email. English and French. Lagos timezone.','from-red-50 to-red-100','#dc2626'],
        ] as [$emoji,$title,$desc,$bg,$color])
        <div class="bg-white rounded-2xl p-5 border border-gray-100 hover:shadow-lg hover:-translate-y-1 transition-all duration-300 group cursor-default"
             style="box-shadow:0 2px 12px rgba(26,58,107,0.06)">
            <div class="w-12 h-12 rounded-2xl flex items-center justify-center mb-3 text-xl bg-gradient-to-br {{ $bg }} group-hover:scale-110 transition-transform duration-300">
                {{ $emoji }}
            </div>
            <h3 class="font-black text-gray-900 text-sm mb-1.5" style="font-family:Poppins,sans-serif;color:{{ $color }}">{{ $title }}</h3>
            <p class="text-xs text-gray-500 leading-relaxed">{{ $desc }}</p>
        </div>
        @endforeach
    </div>
</section>

{{-- ============================================================ --}}
{{-- STATS COUNTER — Orange accent on white bg                   --}}
{{-- ============================================================ --}}
<section class="mb-14 section-reveal">
    <div class="rounded-3xl py-12 px-8 md:px-16 text-center overflow-hidden relative"
         style="background:#fff;box-shadow:0 4px 30px rgba(26,58,107,0.08);border:1px solid #E5E9EF;">
        {{-- Accent stripe top --}}
        <div style="position:absolute;top:0;left:0;right:0;height:4px;background:linear-gradient(90deg,#F47B20,#1A3A6B,#F47B20);"></div>
        <p class="text-xs font-black uppercase tracking-widest text-accent mb-2">Our Numbers</p>
        <h2 class="text-2xl md:text-3xl font-black text-gray-900 mb-10" style="font-family:Poppins,sans-serif;">Trusted Across West Africa</h2>
        @php
            $statUsers    = \App\Models\User::where('is_admin', false)->count();
            $statOrders   = \App\Models\Order::count();
            $statProducts = \App\Models\Product::where('is_active', true)->count();
        @endphp
        <div class="grid grid-cols-2 md:grid-cols-4 gap-8"
             x-data="{ started: false }"
             x-intersect="started = true">
            @foreach([
                ['val'=> number_format($statUsers),       'suffix'=>'+','label'=>'Registered Users',    'emoji'=>'😊'],
                ['val'=>'3',                              'suffix'=>'', 'label'=>'Countries Served',     'emoji'=>'🌍'],
                ['val'=> number_format($statOrders),      'suffix'=>'+','label'=>'Orders Placed',        'emoji'=>'📦'],
                ['val'=> number_format($statProducts),    'suffix'=>'+','label'=>'Products Available',   'emoji'=>'🛍'],
            ] as $stat)
            <div>
                <div style="font-size:2rem;margin-bottom:4px;">{{ $stat['emoji'] }}</div>
                <p class="font-black text-gray-900 leading-none" style="font-size:2.4rem;font-family:Poppins,sans-serif;color:#F47B20;">
                    {{ $stat['val'] }}<span style="color:#1A3A6B;">{{ $stat['suffix'] }}</span>
                </p>
                <p class="text-gray-500 text-sm mt-2 font-semibold">{{ $stat['label'] }}</p>
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ============================================================ --}}
{{-- CTA BANNER                                                   --}}
{{-- ============================================================ --}}
<section class="mb-12 section-reveal">
    <div class="relative rounded-3xl overflow-hidden py-14 px-8 md:px-16 text-center"
         style="background:linear-gradient(135deg,#0A1628 0%,#1A3A6B 50%,#2451A3 100%);">
        {{-- Decorative orbs --}}
        <div style="position:absolute;top:-60px;left:-60px;width:240px;height:240px;border-radius:50%;background:radial-gradient(circle,rgba(244,123,32,0.15),transparent);pointer-events:none;"></div>
        <div style="position:absolute;bottom:-60px;right:-60px;width:240px;height:240px;border-radius:50%;background:radial-gradient(circle,rgba(255,255,255,0.06),transparent);pointer-events:none;"></div>
        <div class="relative z-10">
            <span class="text-4xl mb-4 block">🚀</span>
            <h2 class="text-2xl md:text-4xl font-black text-white mb-4" style="font-family:Poppins,sans-serif;">
                Start Shopping From China Today
            </h2>
            <p class="text-blue-200 text-sm md:text-base mb-8 max-w-lg mx-auto leading-relaxed">
                Register free, fund your wallet, and start ordering directly from China. Delivered to Nigeria, Ghana & Niger.
            </p>
            <div class="flex flex-wrap gap-4 justify-center">
                <a href="{{ route('register') }}"
                   class="inline-flex items-center gap-2 text-white font-black px-10 py-4 rounded-full text-sm transition btn-glow"
                   style="background:linear-gradient(135deg,#F47B20,#D4660E);">
                    <i class="fas fa-user-plus"></i> Create Free Account
                </a>
                <a href="{{ route('products.index') }}"
                   style="display:inline-flex;align-items:center;gap:8px;color:#fff;font-weight:700;padding:14px 32px;border-radius:50px;font-size:0.875rem;text-decoration:none;border:2px solid rgba(255,255,255,0.3);transition:all 0.2s;"
                   onmouseover="this.style.background='rgba(255,255,255,0.1)'"
                   onmouseout="this.style.background='transparent'">
                    <i class="fas fa-store"></i> Browse Products
                </a>
            </div>
        </div>
    </div>
</section>

{{-- ============================================================ --}}
{{-- POPUP BANNER                                                 --}}
{{-- ============================================================ --}}
@if($popupBanner)
<div x-data="{ show: !sessionStorage.getItem('popup_closed') }" x-show="show"
     x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     class="fixed inset-0 z-50 flex items-center justify-center p-4"
     style="background:rgba(0,0,0,0.65);backdrop-filter:blur(4px);">
    <div class="relative bg-white rounded-3xl overflow-hidden max-w-md w-full"
         style="box-shadow:0 25px 60px rgba(0,0,0,0.4);"
         @click.outside="show=false;sessionStorage.setItem('popup_closed','1')">
        <button @click="show=false;sessionStorage.setItem('popup_closed','1')"
                class="absolute top-3 right-3 w-9 h-9 rounded-full flex items-center justify-center z-10 text-white font-bold text-sm transition"
                style="background:rgba(0,0,0,0.5);">
            <i class="fas fa-times"></i>
        </button>
        <a href="{{ $popupBanner->link_url ?? '#' }}" @click="sessionStorage.setItem('popup_closed','1')">
            <img src="{{ $popupBanner->getImageUrl() }}" alt="{{ $popupBanner->title }}" class="w-full">
        </a>
        @if($popupBanner->title)
        <div class="p-5 text-center">
            <p class="font-black text-primary text-lg" style="font-family:Poppins,sans-serif;">{{ $popupBanner->title }}</p>
            @if($popupBanner->subtitle)<p class="text-gray-500 text-sm mt-1">{{ $popupBanner->subtitle }}</p>@endif
        </div>
        @endif
    </div>
</div>
@endif

@endsection
