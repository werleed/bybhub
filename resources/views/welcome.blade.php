@extends('layouts.app')
@section('title', 'Home — Bringing China Closer to You')
@section('meta_description', 'Shop from 1688, Taobao & Alibaba. Delivered to Nigeria, Ghana & Niger. Fast, transparent, secure.')
@section('content')

{{-- ══════════════════════════════════════════════════════════ --}}
{{-- HERO                                                       --}}
{{-- ══════════════════════════════════════════════════════════ --}}
<section style="width:100vw;margin-left:calc(-50vw + 50%);background:linear-gradient(135deg,#060f20 0%,#1A3A6B 55%,#1e4d8c 100%);min-height:580px;position:relative;overflow:hidden;" class="flex items-center">

    {{-- Background glow blobs --}}
    <div style="position:absolute;top:-100px;right:-100px;width:500px;height:500px;border-radius:50%;background:radial-gradient(circle,rgba(244,123,32,0.13),transparent 70%);pointer-events:none;"></div>
    <div style="position:absolute;bottom:-80px;left:-80px;width:400px;height:400px;border-radius:50%;background:radial-gradient(circle,rgba(36,81,163,0.25),transparent 70%);pointer-events:none;"></div>

    {{-- Animated dot grid --}}
    <div style="position:absolute;inset:0;pointer-events:none;overflow:hidden;">
        @for($i=0;$i<18;$i++)
        <div style="position:absolute;width:{{ rand(3,6) }}px;height:{{ rand(3,6) }}px;border-radius:50%;background:rgba(255,255,255,{{ rand(4,12)/100 }});top:{{ rand(3,95) }}%;left:{{ rand(2,95) }}%;animation:float {{ rand(3,7) }}s ease-in-out infinite {{ $i*0.25 }}s alternate;"></div>
        @endfor
    </div>

    <div class="max-w-7xl mx-auto px-6 lg:px-8 py-16 w-full">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-14 items-center">

            {{-- LEFT --}}
            <div class="animate-fade-up">
                <div class="inline-flex items-center gap-2 text-xs font-black uppercase tracking-wider px-4 py-2 rounded-full mb-6"
                     style="background:rgba(244,123,32,0.18);border:1px solid rgba(244,123,32,0.45);color:#F47B20;">
                    🇨🇳 Direct from China &nbsp;·&nbsp; Delivered to West Africa
                </div>
                <h1 style="font-family:Poppins,sans-serif;font-size:clamp(2rem,5vw,3.6rem);font-weight:900;color:#fff;line-height:1.08;margin-bottom:1.25rem;">
                    Bringing China<br><span style="color:#F47B20;">Closer to You</span>
                </h1>
                <p style="color:rgba(147,197,253,0.92);font-size:1.05rem;line-height:1.75;max-width:490px;margin-bottom:2rem;">
                    Shop directly from 1688, Taobao &amp; Alibaba. We procure, ship and deliver to Nigeria, Ghana &amp; Niger with transparent pricing and real-time tracking.
                </p>

                {{-- Search bar --}}
                <form action="{{ route('products.index') }}" method="GET" class="mb-6 max-w-lg" x-data="{ q:'' }">
                    <div class="flex gap-2">
                        <div class="flex-1 relative">
                            <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-blue-300/70 text-sm pointer-events-none"></i>
                            <input type="text" name="q" x-model="q"
                                   placeholder="Search products or paste 1688 / Taobao link…"
                                   class="w-full pl-11 pr-4 py-4 rounded-2xl text-sm text-white placeholder-blue-300/60 focus:outline-none transition"
                                   style="background:rgba(255,255,255,0.12);border:1.5px solid rgba(255,255,255,0.22);-webkit-text-fill-color:white;">
                        </div>
                        <button type="submit" class="flex-shrink-0 text-white font-bold px-6 py-4 rounded-2xl text-sm transition btn-glow active:scale-95" style="background:#F47B20;">
                            Search
                        </button>
                    </div>
                </form>

                {{-- CTA buttons --}}
                <div class="flex flex-wrap gap-3 mb-8">
                    <a href="{{ route('products.index') }}"
                       class="inline-flex items-center gap-2 text-white font-black px-7 py-3.5 rounded-full text-sm transition active:scale-95 hover:-translate-y-0.5"
                       style="background:linear-gradient(135deg,#F47B20,#D4660E);box-shadow:0 6px 28px rgba(244,123,32,0.42);">
                        <i class="fas fa-store"></i> Browse Products
                    </a>
                    <a href="{{ route('user.scraper.index') }}"
                       class="inline-flex items-center gap-2 text-white font-bold px-7 py-3.5 rounded-full text-sm transition active:scale-95 hover:bg-white/15"
                       style="border:2px solid rgba(255,255,255,0.28);">
                        <i class="fas fa-link"></i> Paste Product Link
                    </a>
                    <a href="{{ route('tracking.index') }}"
                       class="inline-flex items-center gap-2 font-semibold px-5 py-3.5 rounded-full text-sm transition active:scale-95 hover:bg-white/10"
                       style="color:rgba(255,255,255,0.75);border:1px solid rgba(255,255,255,0.15);">
                        <i class="fas fa-search-location text-sm"></i> Track Order
                    </a>
                </div>

                {{-- Trust badges --}}
                <div class="flex flex-wrap gap-5">
                    @foreach([
                        ['fa-shield-alt','#4ade80','Secure Payments'],
                        ['fa-truck','#60a5fa','Fast Shipping'],
                        ['fa-star','#F47B20','Verified Sellers'],
                    ] as [$icon,$color,$label])
                    <div style="display:flex;align-items:center;gap:6px;color:rgba(255,255,255,0.65);font-size:0.78rem;">
                        <i class="fas {{ $icon }}" style="color:{{ $color }};"></i> {{ $label }}
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- RIGHT: Floating illustration --}}
            <div class="hidden lg:flex items-center justify-center relative" style="min-height:400px;">
                <div class="animate-float" style="position:absolute;top:50%;left:50%;transform:translate(-50%,-55%);font-size:5.5rem;filter:drop-shadow(0 10px 24px rgba(0,0,0,0.35));">🌐</div>
                <div class="animate-float2" style="position:absolute;bottom:10px;left:50%;transform:translateX(-50%);font-size:5rem;filter:drop-shadow(0 8px 20px rgba(0,0,0,0.4));">🚢</div>
                <div style="position:absolute;top:12%;left:6%;animation:float 3.8s ease-in-out infinite 0.5s;font-size:2.4rem;filter:drop-shadow(0 4px 12px rgba(0,0,0,0.3));">📦</div>
                <div style="position:absolute;top:18%;right:8%;animation:float 4.2s ease-in-out infinite 1s;font-size:2rem;filter:drop-shadow(0 4px 12px rgba(0,0,0,0.3));">📦</div>
                <div style="position:absolute;top:52%;right:4%;animation:float 3.5s ease-in-out infinite 1.5s;font-size:2.2rem;">📦</div>
                <div style="position:absolute;top:4%;right:18%;animation:float 5s ease-in-out infinite 0.8s;font-size:2.8rem;">✈️</div>
                <div style="position:absolute;top:4%;left:18%;animation:float 4s ease-in-out infinite 0.3s;font-size:2.2rem;">🇨🇳</div>
                <div style="position:absolute;bottom:14%;left:4%;animation:float 3.2s ease-in-out infinite 0.7s;font-size:2rem;">🇳🇬</div>
                {{-- Connecting lines --}}
                <svg style="position:absolute;inset:0;width:100%;height:100%;pointer-events:none;opacity:0.22;" viewBox="0 0 400 380">
                    <line x1="200" y1="168" x2="80" y2="78" stroke="#F47B20" stroke-width="1.5" stroke-dasharray="6,4"/>
                    <line x1="200" y1="168" x2="330" y2="88" stroke="#F47B20" stroke-width="1.5" stroke-dasharray="6,4"/>
                    <line x1="200" y1="168" x2="200" y2="305" stroke="#60a5fa" stroke-width="1.5" stroke-dasharray="6,4"/>
                    <circle cx="200" cy="168" r="4" fill="#F47B20"/>
                    <circle cx="80"  cy="78"  r="3" fill="#F47B20"/>
                    <circle cx="330" cy="88"  r="3" fill="#F47B20"/>
                    <circle cx="200" cy="305" r="3" fill="#60a5fa"/>
                </svg>
                <div style="position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);width:300px;height:300px;border-radius:50%;background:radial-gradient(circle,rgba(36,81,163,0.35),transparent 70%);pointer-events:none;"></div>
            </div>
        </div>
    </div>
</section>

{{-- ══════════════════════════════════════════════════════════ --}}
{{-- LIVE RATES BAR                                            --}}
{{-- ══════════════════════════════════════════════════════════ --}}
<div class="section-reveal my-6">
    <div class="flex flex-wrap items-center justify-between gap-4 px-5 py-3.5 rounded-2xl"
         style="background:linear-gradient(135deg,#0f2040,#1A3A6B);box-shadow:0 6px 24px rgba(26,58,107,0.28);">
        <div class="flex items-center gap-2.5">
            <span style="width:9px;height:9px;border-radius:50%;background:#4ade80;display:inline-block;animation:pulse-dot 2s ease-in-out infinite;"></span>
            <span style="color:rgba(255,255,255,0.6);font-size:0.7rem;font-weight:800;text-transform:uppercase;letter-spacing:0.1em;">Live Rates</span>
        </div>
        <div class="flex flex-wrap gap-2.5">
            @foreach($rates as $currency => $rate)
            @php $sym2 = match($currency){'GHS'=>'₵','XOF'=>'Fr',default=>'₦'}; $flag = match($currency){'GHS'=>'🇬🇭','XOF'=>'🇳🇪',default=>'🇳🇬'}; @endphp
            <div style="display:flex;align-items:center;gap:5px;background:rgba(255,255,255,0.09);padding:5px 14px;border-radius:50px;border:1px solid rgba(255,255,255,0.12);">
                <span>{{ $flag }}</span>
                <span style="color:#fff;font-weight:800;font-size:0.88rem;font-family:Poppins,sans-serif;">{{ $sym2 }}{{ number_format((float)($rate['rate']??0),2) }}</span>
                <span style="color:rgba(255,255,255,0.45);font-size:0.68rem;">{{ $currency }}</span>
            </div>
            @endforeach
        </div>
        <a href="{{ route('shipping.calculator') }}"
           class="inline-flex items-center gap-2 text-white text-xs font-bold px-4 py-2 rounded-xl transition hover:opacity-90"
           style="background:rgba(244,123,32,0.3);border:1px solid rgba(244,123,32,0.45);">
            <i class="fas fa-calculator"></i> Calc Shipping
        </a>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════ --}}
{{-- FLASH SALE                                                 --}}
{{-- ══════════════════════════════════════════════════════════ --}}
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

{{-- ══════════════════════════════════════════════════════════ --}}
{{-- HOW IT WORKS                                               --}}
{{-- ══════════════════════════════════════════════════════════ --}}
<section class="mb-14 section-reveal">
    <div class="text-center mb-8">
        <p class="text-xs font-black uppercase tracking-widest text-accent mb-2">Simple Process</p>
        <h2 class="text-2xl md:text-3xl font-black text-gray-900" style="font-family:Poppins,sans-serif;">How BYBHUB Works</h2>
    </div>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        @foreach([
            ['1','fa-search','Find Your Product','Browse our catalogue or paste a 1688 / Taobao / Alibaba link directly.','#1A3A6B'],
            ['2','fa-wallet','Fund Your Wallet','Transfer to your dedicated virtual account — it credits instantly.','#F47B20'],
            ['3','fa-shopping-bag','Place Your Order','We quote the exact price in NGN including all fees, then you confirm.','#10B981'],
            ['4','fa-truck','We Deliver','We procure from China, handle customs, and ship to your door.','#6366f1'],
        ] as [$step,$icon,$title,$desc,$color])
        <div class="bg-white rounded-2xl p-5 shadow-card card-hover relative overflow-hidden">
            <div class="absolute top-3 right-3 text-6xl font-black opacity-[0.04]" style="font-family:Poppins,sans-serif;color:{{ $color }};">{{ $step }}</div>
            <div class="w-12 h-12 rounded-2xl flex items-center justify-center mb-4 shadow-sm" style="background:{{ $color }}15;">
                <i class="fas {{ $icon }} text-lg" style="color:{{ $color }};"></i>
            </div>
            <p class="font-black text-gray-900 text-base mb-1.5" style="font-family:Poppins,sans-serif;">{{ $title }}</p>
            <p class="text-sm text-gray-500 leading-relaxed">{{ $desc }}</p>
        </div>
        @endforeach
    </div>
</section>

{{-- ══════════════════════════════════════════════════════════ --}}
{{-- CATEGORIES                                                 --}}
{{-- ══════════════════════════════════════════════════════════ --}}
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
        $catCfg = [
            'electronics'=>['🖥️','linear-gradient(135deg,#1e40af,#3b82f6)'],
            'fashion'     =>['👗','linear-gradient(135deg,#be185d,#ec4899)'],
            'home'        =>['🏠','linear-gradient(135deg,#065f46,#10b981)'],
            'health'      =>['💊','linear-gradient(135deg,#6d28d9,#a78bfa)'],
            'sports'      =>['⚽','linear-gradient(135deg,#c2410c,#f97316)'],
            'phones'      =>['📱','linear-gradient(135deg,#3730a3,#6366f1)'],
            'toys'        =>['🧸','linear-gradient(135deg,#b45309,#f59e0b)'],
            'tools'       =>['🔧','linear-gradient(135deg,#374151,#6b7280)'],
            'food'        =>['🥗','linear-gradient(135deg,#3f6212,#84cc16)'],
            'office'      =>['💼','linear-gradient(135deg,#0e7490,#06b6d4)'],
            'auto'        =>['🚗','linear-gradient(135deg,#991b1b,#ef4444)'],
            'baby'        =>['👶','linear-gradient(135deg,#c2410c,#fb923c)'],
        ];
        $defEmoji = ['🛍️','📦','🎁','🛒','✨','💡','🎯','💎','🏆','🎨','🌟','🎪'];
        $defGrad  = array_values(array_column($catCfg,'1'));
        @endphp
        @foreach($categories as $idx => $cat)
        @php
            $matched = null;
            foreach($catCfg as $k=>$c) {
                if(str_contains(strtolower($cat->slug??''),$k)||str_contains(strtolower($cat->getTranslatedName()),$k)){$matched=$c;break;}
            }
            $emoji = $matched ? $matched[0] : $defEmoji[$idx % count($defEmoji)];
            $grad  = $matched ? $matched[1] : $defGrad[$idx % count($defGrad)];
        @endphp
        <a href="{{ route('products.category', $cat->slug) }}"
           class="group relative rounded-2xl p-4 text-center overflow-hidden card-hover cursor-pointer"
           style="background:{{ $grad }};box-shadow:0 4px 16px rgba(0,0,0,0.14);">
            <div style="position:absolute;top:-20px;right:-20px;width:70px;height:70px;border-radius:50%;background:rgba(255,255,255,0.1);pointer-events:none;"></div>
            <div class="relative z-10">
                <div style="font-size:2.1rem;margin-bottom:7px;filter:drop-shadow(0 2px 6px rgba(0,0,0,0.22));" class="group-hover:scale-125 transition-transform duration-300 inline-block">{{ $emoji }}</div>
                <p style="color:#fff;font-size:0.71rem;font-weight:700;line-height:1.2;" class="truncate">{{ $cat->getTranslatedName() }}</p>
                @if($cat->products_count > 0)
                <p style="color:rgba(255,255,255,0.65);font-size:0.63rem;margin-top:2px;">{{ $cat->products_count }} items</p>
                @endif
            </div>
        </a>
        @endforeach
    </div>
</section>

{{-- ══════════════════════════════════════════════════════════ --}}
{{-- STATS STRIP                                                --}}
{{-- ══════════════════════════════════════════════════════════ --}}
<section class="mb-12 section-reveal">
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">

        {{-- Live CNY rate --}}
        <div class="rounded-2xl p-5 text-white flex items-center gap-4 shadow-lg" style="background:linear-gradient(135deg,#1A3A6B,#2451A3);">
            <div class="w-12 h-12 bg-white/15 rounded-xl flex items-center justify-center flex-shrink-0 text-2xl">🇨🇳</div>
            <div>
                <p class="text-blue-200 text-[11px] font-semibold uppercase tracking-wide">Live CNY → NGN</p>
                @php $liveRate = \App\Models\ExchangeRate::where('from_currency','CNY')->where('to_currency','NGN')->value('rate') ?? 222; @endphp
                <p class="text-2xl font-black leading-none" style="font-family:Poppins,sans-serif;">₦{{ number_format((float)$liveRate,0) }}</p>
                <p class="text-blue-300 text-[10px] mt-0.5">per ¥1 CNY · updated daily</p>
            </div>
        </div>

        {{-- Orders placed --}}
        <div class="rounded-2xl p-5 text-white flex items-center gap-4 shadow-lg" style="background:linear-gradient(135deg,#F47B20,#D4660E);">
            <div class="w-12 h-12 bg-white/15 rounded-xl flex items-center justify-center flex-shrink-0 text-2xl">📦</div>
            <div>
                @php $totalOrders2 = \App\Models\Order::count(); $totalUsers2 = \App\Models\User::where('is_admin',false)->count(); @endphp
                <p class="text-orange-200 text-[11px] font-semibold uppercase tracking-wide">Orders Placed</p>
                <p class="text-2xl font-black leading-none" style="font-family:Poppins,sans-serif;">{{ number_format($totalOrders2) }}+</p>
                <p class="text-orange-200 text-[10px] mt-0.5">by {{ number_format($totalUsers2) }}+ shoppers</p>
            </div>
        </div>

        {{-- Our guarantee --}}
        <div class="rounded-2xl p-5 text-white flex items-center gap-4 shadow-lg" style="background:linear-gradient(135deg,#065f46,#10b981);">
            <div class="w-12 h-12 bg-white/15 rounded-xl flex items-center justify-center flex-shrink-0 text-2xl">🛡️</div>
            <div>
                <p class="text-green-200 text-[11px] font-semibold uppercase tracking-wide">Our Guarantee</p>
                <p class="text-base font-black leading-tight" style="font-family:Poppins,sans-serif;">Pay only after<br>we confirm price</p>
                <p class="text-green-300 text-[10px] mt-0.5">No hidden fees. Ever.</p>
            </div>
        </div>
    </div>
</section>

{{-- ══════════════════════════════════════════════════════════ --}}
{{-- TRENDING PRODUCTS                                          --}}
{{-- ══════════════════════════════════════════════════════════ --}}
@if($trending->isNotEmpty())
<section class="mb-14 section-reveal">
    <div class="flex items-center justify-between mb-5">
        <div class="flex items-center gap-3">
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

{{-- ══════════════════════════════════════════════════════════ --}}
{{-- NEW ARRIVALS                                               --}}
{{-- ══════════════════════════════════════════════════════════ --}}
@if(isset($recentlyAdded) && $recentlyAdded->isNotEmpty())
<section class="mb-14 section-reveal">
    <div class="flex items-center justify-between mb-5">
        <div class="flex items-center gap-3">
            <span class="text-2xl">✨</span>
            <h2 class="text-xl md:text-2xl font-black text-gray-900" style="font-family:Poppins,sans-serif;">New Arrivals</h2>
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

{{-- ══════════════════════════════════════════════════════════ --}}
{{-- BECOME A VENDOR                                            --}}
{{-- ══════════════════════════════════════════════════════════ --}}
@php $vendorOpen = \App\Models\Setting::get('vendor_marketplace_open','0') === '1'; @endphp
<section class="mb-14 section-reveal">
    <div class="relative rounded-3xl overflow-hidden text-white"
         style="background:linear-gradient(135deg,#060f20 0%,#1A3A6B 40%,#2451A3 70%,#F47B20 100%)">
        <div class="absolute inset-0 pointer-events-none overflow-hidden">
            <div class="absolute top-6 right-20 text-6xl opacity-[0.08] animate-bounce-soft">📦</div>
            <div class="absolute bottom-6 right-44 text-5xl opacity-[0.08]" style="animation:float 4s ease-in-out infinite">🛍️</div>
            <div class="absolute bottom-10 left-20 text-5xl opacity-[0.08]" style="animation:float 3.5s ease-in-out infinite 0.5s">🏪</div>
            <div class="absolute top-0 right-0 w-72 h-72 rounded-full opacity-10" style="background:radial-gradient(circle,#F47B20,transparent);transform:translate(30%,-30%)"></div>
        </div>
        <div class="relative px-8 md:px-14 py-12 md:py-16 flex flex-col md:flex-row items-center gap-10">
            <div class="flex-1 text-center md:text-left">
                <span class="inline-flex items-center gap-2 text-[11px] font-bold uppercase tracking-widest px-3 py-1 rounded-full mb-4" style="background:rgba(255,255,255,0.14);border:1px solid rgba(255,255,255,0.22);">
                    🇳🇬 Open to Nigerian Sellers
                </span>
                <h2 class="text-2xl md:text-3xl font-black mb-3" style="font-family:Poppins,sans-serif;">
                    Grow Your Business on<br><span style="color:#F47B20;">BYBHUB Marketplace</span>
                </h2>
                <p class="text-white/75 text-sm leading-relaxed mb-5 max-w-lg">
                    Reach thousands of buyers across Nigeria. List for free, get paid through our secure escrow system, and manage everything from your dashboard.
                </p>
                <div class="flex flex-wrap gap-2 mb-6 justify-center md:justify-start">
                    @foreach(['🔒 Secure Escrow','💸 Fast Payouts','📦 Free Listing','📊 Sales Dashboard','📱 Order Alerts'] as $b)
                    <span class="text-[11px] font-semibold px-3 py-1 rounded-full" style="background:rgba(255,255,255,0.12);border:1px solid rgba(255,255,255,0.2);">{{ $b }}</span>
                    @endforeach
                </div>
                <div class="flex flex-wrap gap-3 justify-center md:justify-start">
                    @if($vendorOpen)
                    @php $fee = \App\Models\Setting::get('vendor_activation_fee','5000'); @endphp
                    <a href="{{ route('vendor.apply') }}"
                       class="inline-flex items-center gap-2 font-bold px-7 py-3.5 rounded-2xl text-sm transition active:scale-95 hover:-translate-y-0.5"
                       style="background:linear-gradient(135deg,#F47B20,#D4660E);box-shadow:0 6px 28px rgba(244,123,32,0.42);">
                        <i class="fas fa-store"></i> Apply to Sell
                        @if((float)$fee>0)<span class="text-[11px] opacity-80">&nbsp;· ₦{{ number_format((float)$fee,0) }} fee</span>@endif
                    </a>
                    @else
                    <a href="{{ route('register') }}" class="inline-flex items-center gap-2 font-bold px-7 py-3.5 rounded-2xl text-sm" style="background:rgba(255,255,255,0.16);border:2px solid rgba(255,255,255,0.28);">
                        <i class="fas fa-bell"></i> Create Account — Be First to Know
                    </a>
                    @endif
                    <a href="{{ route('products.index',['type'=>'local']) }}" class="inline-flex items-center gap-2 font-semibold px-6 py-3.5 rounded-2xl text-sm" style="background:rgba(255,255,255,0.07);border:1px solid rgba(255,255,255,0.18);color:rgba(255,255,255,0.82);">
                        <i class="fas fa-arrow-right text-xs"></i> Browse Local Products
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ══════════════════════════════════════════════════════════ --}}
{{-- FEATURED (non-flash) PRODUCTS                             --}}
{{-- ══════════════════════════════════════════════════════════ --}}
@if($editorsPick->isNotEmpty())
<section class="mb-14 section-reveal">
    <div class="flex items-center justify-between mb-5">
        <div class="flex items-center gap-3">
            <span class="text-2xl">⭐</span>
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

{{-- ══════════════════════════════════════════════════════════ --}}
{{-- POPUP BANNER (from welcome.blade.php, via controller)      --}}
{{-- ══════════════════════════════════════════════════════════ --}}
@if($popupBanner)
<div x-data="{ show: !sessionStorage.getItem('popup_closed') }" x-show="show" x-cloak
     x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     class="fixed inset-0 z-[99] flex items-center justify-center p-4"
     style="background:rgba(0,0,0,0.6);backdrop-filter:blur(4px)">
    <div class="relative bg-white rounded-3xl overflow-hidden max-w-md w-full shadow-2xl"
         @click.outside="show=false;sessionStorage.setItem('popup_closed','1')">
        <button @click="show=false;sessionStorage.setItem('popup_closed','1')"
                class="absolute top-3 right-3 w-9 h-9 bg-black/40 hover:bg-black/60 rounded-full flex items-center justify-center z-10 text-white font-bold text-sm transition">
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
