@extends('layouts.app')
@section('title', __('app.dashboard'))
@section('content')

<div class="space-y-6 pb-24 sm:pb-6">

{{-- ── Mobile title ──────────────────────────────────────────── --}}
<h1 class="text-2xl font-black text-primary sm:hidden" style="font-family:Poppins,sans-serif;">Dashboard</h1>

{{-- ── Clipboard import nudge ───────────────────────────────── --}}
<div x-data="{
    detected: null,
    dismissed: localStorage.getItem('clip_dismissed_url') || '',
    supported: typeof navigator !== 'undefined' && !!navigator.clipboard && typeof navigator.clipboard.readText === 'function',
    async check() {
        if (!this.supported) return;
        try {
            const text = await navigator.clipboard.readText();
            const m = String(text).match(/https?:\/\/[^\s<>()\]\x22']+/i);
            if (!m) return;
            const url = m[0];
            if (!/(1688|taobao|tmall|alibaba|aliexpress)\.com/i.test(url)) return;
            if (this.dismissed === url) return;
            let host = url; try { host = new URL(url).hostname.replace(/^www\./, ''); } catch(e) {}
            this.detected = { url, host };
        } catch(e) {}
    },
    go()      { window.location = '{{ route('user.scraper.index') }}?url=' + encodeURIComponent(this.detected.url); },
    dismiss() { if (this.detected) { localStorage.setItem('clip_dismissed_url', this.detected.url); this.detected = null; } }
}" x-init="$nextTick(() => { setTimeout(() => check(), 800); document.addEventListener('visibilitychange', () => { if(!document.hidden) check(); }); })">
    <div x-show="detected" x-cloak x-transition
         class="rounded-2xl p-4 flex items-center gap-3 border"
         style="display:none;background:linear-gradient(135deg,#ecfdf5,#d1fae5);border-color:#6ee7b7;">
        <div class="w-10 h-10 bg-green-100 rounded-xl flex items-center justify-center flex-shrink-0">
            <i class="fas fa-clipboard-check text-green-600 text-sm"></i>
        </div>
        <div class="flex-1 min-w-0">
            <p class="font-bold text-green-800 text-sm">Product link detected on clipboard</p>
            <p class="text-xs text-green-700 truncate" x-text="detected?.host"></p>
        </div>
        <button @click="go()" class="flex-shrink-0 bg-accent hover:bg-accent-dark text-white font-bold text-xs px-4 py-2.5 rounded-xl transition active:scale-95 flex items-center gap-1.5">
            <i class="fas fa-bolt text-[10px]"></i> Import
        </button>
        <button @click="dismiss()" class="text-green-700/50 hover:text-green-800 px-1"><i class="fas fa-times text-sm"></i></button>
    </div>
</div>

{{-- ── Email verification banner ────────────────────────────── --}}
@if(!$user->hasVerifiedEmail())
<div class="rounded-2xl p-4 flex flex-wrap items-center gap-4 border border-amber-200"
     style="background:linear-gradient(135deg,#fffbeb,#fef3c7);">
    <div class="w-10 h-10 bg-amber-100 rounded-xl flex items-center justify-center flex-shrink-0">
        <i class="fas fa-envelope text-amber-500 text-sm"></i>
    </div>
    <div class="flex-1 min-w-0">
        <p class="font-bold text-amber-900 text-sm">Please verify your email address</p>
        <p class="text-amber-700 text-xs mt-0.5">Check your inbox for a verification link from BYBHUB.</p>
    </div>
    <form action="{{ route('verification.send') }}" method="POST" class="flex-shrink-0">
        @csrf
        <button class="inline-flex items-center gap-2 bg-amber-500 hover:bg-amber-600 text-white text-xs font-bold px-4 py-2.5 rounded-xl transition shadow-sm">
            <i class="fas fa-paper-plane text-[10px]"></i> Resend
        </button>
    </form>
</div>
@endif

{{-- ── Hero wallet card ──────────────────────────────────────── --}}
<div class="rounded-3xl relative overflow-hidden text-white"
     style="background:linear-gradient(135deg,#060f20 0%,#1A3A6B 50%,#2451A3 100%);min-height:160px;">
    {{-- Glow blobs --}}
    <div class="absolute top-0 right-0 w-72 h-72 rounded-full -translate-y-1/2 translate-x-1/3 opacity-10" style="background:radial-gradient(circle,#fff,transparent);pointer-events:none;"></div>
    <div class="absolute bottom-0 left-0 w-48 h-48 rounded-full translate-y-1/3 -translate-x-1/4 opacity-10" style="background:radial-gradient(circle,#F47B20,transparent);pointer-events:none;"></div>
    {{-- Animated shimmer line --}}
    <div class="absolute inset-x-0 top-0 h-0.5 opacity-40" style="background:linear-gradient(90deg,transparent,#F47B20,transparent);"></div>

    <div class="relative z-10 px-6 py-7 md:px-8 flex flex-wrap items-center justify-between gap-5">
        <div>
            <p class="text-blue-300 text-sm font-medium">Welcome back,</p>
            <h2 class="text-2xl md:text-3xl font-black mt-0.5" style="font-family:Poppins,sans-serif;">{{ explode(' ', $user->name)[0] }} 👋</h2>
            <div class="flex items-center gap-2 mt-2 flex-wrap">
                <span class="text-xs text-blue-300 font-medium">{{ $user->countryName() }}</span>
                <span class="w-1 h-1 bg-blue-400 rounded-full"></span>
                <span class="text-xs text-blue-300">{{ $user->currency }}</span>
                <span class="w-1 h-1 bg-blue-400 rounded-full"></span>
                <span class="font-mono text-xs text-blue-200/70 bg-white/10 px-2 py-0.5 rounded-lg">{{ $user->referral_code }}</span>
            </div>
        </div>
        <div class="text-right">
            <p class="text-blue-300/80 text-xs font-semibold uppercase tracking-wider mb-1">Wallet Balance</p>
            <p class="font-black leading-none" style="font-family:Poppins,sans-serif;font-size:clamp(1.8rem,5vw,2.8rem);">
                {{ $symbol }}{{ number_format($wallet, 2) }}
            </p>
            <div class="flex gap-2 mt-3 justify-end">
                <a href="{{ route('user.wallet.fund') }}"
                   class="inline-flex items-center gap-1.5 text-white text-xs font-bold px-4 py-2.5 rounded-xl transition active:scale-95 btn-glow"
                   style="background:#F47B20;">
                    <i class="fas fa-plus text-[10px]"></i> Fund Wallet
                </a>
                <a href="{{ route('user.wallet.index') }}"
                   class="inline-flex items-center gap-1.5 text-white/90 text-xs font-semibold px-4 py-2.5 rounded-xl transition hover:bg-white/20"
                   style="background:rgba(255,255,255,0.12);">
                    <i class="fas fa-wallet text-[10px]"></i> View
                </a>
            </div>
        </div>
    </div>
</div>

{{-- ── Stat cards ────────────────────────────────────────────── --}}
<div class="grid grid-cols-2 md:grid-cols-4 gap-4">
    @foreach([
        ['icon'=>'fas fa-box',         'grad'=>'from-blue-500 to-blue-600',   'val'=>$totalOrders,             'label'=>'Total Orders',    'sub'=>'All time'],
        ['icon'=>'fas fa-check-circle','grad'=>'from-green-500 to-green-600', 'val'=>$deliveredOrders,         'label'=>'Delivered',       'sub'=>'Completed'],
        ['icon'=>'fab fa-alipay',      'grad'=>'from-purple-500 to-purple-600','val'=>$pendingSupplierPayments,'label'=>'Pending Payments','sub'=>'Awaiting'],
        ['icon'=>'fas fa-users',       'grad'=>'from-accent to-orange-500',   'val'=>$user->referrals_count??0,'label'=>'Referrals',       'sub'=>'Invited'],
    ] as $s)
    <div class="stat-card bg-white rounded-2xl p-5 shadow-card">
        <div class="flex items-start justify-between mb-3">
            <div class="w-11 h-11 bg-gradient-to-br {{ $s['grad'] }} rounded-xl flex items-center justify-center shadow-sm">
                <i class="{{ $s['icon'] }} text-white text-sm"></i>
            </div>
            <span class="text-[10px] text-gray-400 font-medium bg-gray-50 px-2 py-0.5 rounded-lg">{{ $s['sub'] }}</span>
        </div>
        <p class="text-2xl font-black text-gray-900 count-animate" style="font-family:Poppins,sans-serif;">{{ $s['val'] }}</p>
        <p class="text-xs text-gray-500 font-medium mt-0.5">{{ $s['label'] }}</p>
    </div>
    @endforeach
</div>

{{-- ── Quick actions ─────────────────────────────────────────── --}}
<div>
    <h3 class="text-xs font-black text-gray-400 uppercase tracking-widest mb-3">Quick Actions</h3>

    {{-- Abandoned cart nudge --}}
    @if(isset($hasAbandonedCart) && $hasAbandonedCart)
    <div class="bg-amber-50 border border-amber-200 rounded-2xl p-4 mb-4 flex items-center gap-3">
        <div class="w-10 h-10 bg-amber-100 rounded-xl flex items-center justify-center flex-shrink-0">
            <i class="fas fa-shopping-cart text-amber-500 text-sm"></i>
        </div>
        <div class="flex-1 min-w-0">
            <p class="font-bold text-amber-800 text-sm">Items left in your cart!</p>
            <p class="text-xs text-amber-600 mt-0.5">Complete your order before they sell out.</p>
        </div>
        <a href="{{ route('user.cart.index') }}" class="bg-accent text-white font-bold text-xs px-3 py-2 rounded-xl flex-shrink-0 hover:bg-accent-dark transition">
            View Cart
        </a>
    </div>
    @endif

    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">

        {{-- Orders --}}
        <a href="{{ route('user.orders.index') }}" class="quick-action relative bg-white rounded-2xl p-4 shadow-card group block">
            @if(($pendingOrdersCount ?? 0) > 0)
            <span class="absolute -top-1.5 -right-1.5 min-w-[20px] h-5 bg-red-500 text-white text-[10px] font-black rounded-full flex items-center justify-center px-1 z-10">
                {{ $pendingOrdersCount > 9 ? '9+' : $pendingOrdersCount }}
            </span>
            @endif
            <div class="w-11 h-11 bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl flex items-center justify-center mb-3 group-hover:scale-110 transition-transform shadow-sm">
                <i class="fas fa-box text-white text-base"></i>
            </div>
            <p class="font-bold text-gray-800 text-sm">My Orders</p>
            <p class="text-xs text-gray-400 mt-0.5">{{ $pendingOrdersCount ?? 0 }} in progress</p>
        </a>

        {{-- Cart --}}
        <a href="{{ route('user.cart.index') }}" class="quick-action relative bg-white rounded-2xl p-4 shadow-card group block">
            @if(($cartItemsCount ?? 0) > 0)
            <span class="absolute -top-1.5 -right-1.5 min-w-[20px] h-5 bg-accent text-white text-[10px] font-black rounded-full flex items-center justify-center px-1 z-10">{{ $cartItemsCount }}</span>
            @endif
            <div class="w-11 h-11 bg-gradient-to-br from-accent to-orange-400 rounded-xl flex items-center justify-center mb-3 group-hover:scale-110 transition-transform shadow-sm">
                <i class="fas fa-shopping-cart text-white text-base"></i>
            </div>
            <p class="font-bold text-gray-800 text-sm">My Cart</p>
            <p class="text-xs text-gray-400 mt-0.5">{{ $cartItemsCount ?? 0 }} item(s)</p>
        </a>

        {{-- Notifications --}}
        <a href="{{ route('user.notifications.index') }}" class="quick-action relative bg-white rounded-2xl p-4 shadow-card group block">
            @if(($unreadNotificationsCount ?? 0) > 0)
            <span class="absolute -top-1.5 -right-1.5 min-w-[20px] h-5 bg-red-500 text-white text-[10px] font-black rounded-full flex items-center justify-center px-1 z-10">
                {{ $unreadNotificationsCount > 9 ? '9+' : $unreadNotificationsCount }}
            </span>
            @endif
            <div class="w-11 h-11 bg-gradient-to-br from-purple-500 to-purple-600 rounded-xl flex items-center justify-center mb-3 group-hover:scale-110 transition-transform shadow-sm">
                <i class="fas fa-bell text-white text-base"></i>
            </div>
            <p class="font-bold text-gray-800 text-sm">Alerts</p>
            <p class="text-xs text-gray-400 mt-0.5">{{ $unreadNotificationsCount ?? 0 }} unread</p>
        </a>

        {{-- Find Product --}}
        <a href="{{ route('user.scraper.index') }}" class="quick-action bg-white rounded-2xl p-4 shadow-card group block">
            <div class="w-11 h-11 bg-gradient-to-br from-green-500 to-emerald-600 rounded-xl flex items-center justify-center mb-3 group-hover:scale-110 transition-transform shadow-sm">
                <i class="fas fa-search text-white text-base"></i>
            </div>
            <p class="font-bold text-gray-800 text-sm">Find Product</p>
            <p class="text-xs text-gray-400 mt-0.5">1688 / Taobao / Alibaba</p>
        </a>

    </div>
</div>

{{-- ── Virtual account + Exchange rates ─────────────────────── --}}
<div class="grid grid-cols-1 md:grid-cols-2 gap-5">

    {{-- Virtual account --}}
    @if($monnifyAccount)
    <div class="bg-white rounded-2xl p-5 shadow-card">
        <h3 class="font-bold text-primary mb-4 flex items-center gap-2 text-sm">
            <div class="w-7 h-7 bg-primary/10 rounded-lg flex items-center justify-center">
                <i class="fas fa-university text-primary text-xs"></i>
            </div>
            Your Virtual Account
        </h3>
        @foreach($monnifyAccount->accountsList() as $acct)
        <div class="bg-gradient-to-r from-primary/5 to-blue-50 border border-primary/10 rounded-xl p-4 mb-2.5 last:mb-0" x-data>
            <div class="flex items-start justify-between gap-3">
                <div>
                    <p class="text-[11px] font-semibold text-gray-500 uppercase tracking-wider">{{ $acct['bank'] }}</p>
                    <p class="text-xl font-black text-primary tracking-widest mt-0.5 font-mono">{{ $acct['number'] }}</p>
                    <p class="text-xs text-gray-600 mt-0.5">{{ $acct['name'] }}</p>
                </div>
                <button @click="navigator.clipboard.writeText('{{ $acct['number'] }}').then(()=>{$el.innerHTML='<i class=\'fas fa-check\'></i> Copied';setTimeout(()=>{$el.innerHTML='<i class=\'fas fa-copy\'></i> Copy'},2000)})"
                    class="flex-shrink-0 text-xs bg-primary text-white px-3 py-1.5 rounded-lg hover:bg-primary-dark transition flex items-center gap-1.5">
                    <i class="fas fa-copy text-[10px]"></i> Copy
                </button>
            </div>
        </div>
        @endforeach
        <p class="text-xs text-gray-400 mt-3 flex items-center gap-1.5">
            <i class="fas fa-info-circle text-blue-400"></i>
            Transfer any amount — credits within seconds
        </p>
    </div>
    @else
    <div class="bg-white rounded-2xl p-5 shadow-card flex flex-col items-center justify-center text-center gap-3">
        <div class="w-16 h-16 bg-gray-100 rounded-2xl flex items-center justify-center">
            <i class="fas fa-university text-gray-300 text-2xl"></i>
        </div>
        <div>
            <p class="font-semibold text-gray-700 text-sm">No Virtual Account Yet</p>
            <p class="text-xs text-gray-400 mt-1">Get a dedicated account to fund your wallet instantly</p>
        </div>
        @if($user->country === 'NG')
        <a href="{{ route('user.wallet.request-virtual-account') }}"
           class="inline-flex items-center gap-2 bg-primary text-white text-xs font-bold px-5 py-2.5 rounded-xl hover:bg-primary-dark transition">
            <i class="fas fa-plus"></i> Request Virtual Account
        </a>
        @endif
    </div>
    @endif

    {{-- Exchange rates --}}
    <div class="bg-white rounded-2xl p-5 shadow-card">
        <h3 class="font-bold text-primary mb-4 flex items-center gap-2 text-sm">
            <div class="w-7 h-7 bg-primary/10 rounded-lg flex items-center justify-center">
                <i class="fas fa-chart-line text-primary text-xs"></i>
            </div>
            Live Exchange Rates
        </h3>
        @foreach($rates as $currency => $rate)
        @php $rateSym = match($currency){'GHS'=>'₵','XOF'=>'Fr',default=>'₦'}; $rateFlag = match($currency){'GHS'=>'🇬🇭','XOF'=>'🇳🇪',default=>'🇳🇬'}; @endphp
        <div class="flex items-center justify-between py-3 border-b border-gray-50 last:border-0">
            <div class="flex items-center gap-2">
                <span class="text-base">{{ $rateFlag }}</span>
                <span class="text-sm text-gray-500 font-medium">¥1 CNY</span>
            </div>
            <div class="text-right">
                <span class="font-bold text-primary text-sm">{{ $rateSym }}{{ number_format((float)($rate['rate']??0),2) }}</span>
                <span class="text-xs text-gray-400 ml-1">{{ $currency }}</span>
            </div>
        </div>
        @endforeach
        <a href="{{ route('shipping.calculator') }}" class="mt-4 flex items-center justify-center gap-2 text-xs font-bold text-primary hover:text-primary-dark transition py-2 rounded-xl bg-primary/5 hover:bg-primary/10">
            <i class="fas fa-calculator text-xs"></i> Shipping Calculator
        </a>
    </div>
</div>

{{-- ── Spin & Win promo --}}
@if(\App\Models\Setting::get('spinwin_enabled','0') === '1')
<a href="{{ route('user.spinwin.show') }}" class="block relative rounded-2xl overflow-hidden text-white px-6 py-5 group"
   style="background:linear-gradient(135deg,#6d28d9 0%,#db2777 55%,#f97316 100%)">
    <div class="absolute inset-0 opacity-10 pointer-events-none" style="background:radial-gradient(circle at 80% 50%,white,transparent)"></div>
    <div class="relative flex items-center gap-4">
        <div class="text-4xl group-hover:scale-110 transition-transform duration-300">🎡</div>
        <div class="flex-1">
            <p class="font-black text-lg" style="font-family:Poppins,sans-serif;">Spin &amp; Win!</p>
            <p class="text-white/80 text-sm">Spin the wheel and win a discount coupon for your next order.</p>
        </div>
        <div class="shrink-0 bg-white/20 hover:bg-white/30 px-4 py-2 rounded-xl text-sm font-bold transition">Spin Now →</div>
    </div>
</a>
@endif

{{-- ── Push notification prompt ─────────────────────────────── --}}
<div x-data="{
    show: false,
    init() {
        const dismissed = localStorage.getItem('push_prompt_dismissed');
        const perm = typeof Notification !== 'undefined' ? Notification.permission : 'denied';
        this.show = perm === 'default' && !dismissed;
    },
    async enable() {
        const result = await Notification.requestPermission();
        if (result === 'granted') await window.bybPushSubscribe?.();
        this.show = false;
    },
    dismiss() { localStorage.setItem('push_prompt_dismissed','1'); this.show = false; }
}" x-show="show" style="display:none"
x-transition:enter="transition ease-out duration-300"
x-transition:enter-start="opacity-0 -translate-y-2"
x-transition:enter-end="opacity-100 translate-y-0">
    <div class="bg-primary/5 border border-primary/20 rounded-2xl p-4 flex items-center gap-3">
        <div class="w-9 h-9 bg-primary/10 rounded-xl flex items-center justify-center flex-shrink-0">
            <i class="fas fa-bell text-primary text-sm"></i>
        </div>
        <div class="flex-1 min-w-0">
            <p class="text-sm font-bold text-primary">Stay updated on your orders</p>
            <p class="text-xs text-gray-500 mt-0.5">Enable notifications for instant order alerts.</p>
        </div>
        <div class="flex items-center gap-2 flex-shrink-0">
            <button @click="enable()" class="bg-primary text-white font-bold text-xs px-3 py-2 rounded-xl hover:bg-primary-dark transition">Enable</button>
            <button @click="dismiss()" class="text-gray-400 hover:text-gray-600 text-xs px-2 py-2"><i class="fas fa-times"></i></button>
        </div>
    </div>
</div>

{{-- ── Recent Orders ─────────────────────────────────────────── --}}
<div class="bg-white rounded-2xl shadow-card p-5">
    <div class="flex justify-between items-center mb-5">
        <h3 class="font-bold text-primary flex items-center gap-2 text-sm">
            <div class="w-7 h-7 bg-primary/10 rounded-lg flex items-center justify-center">
                <i class="fas fa-box text-primary text-xs"></i>
            </div>
            Recent Orders
        </h3>
        <a href="{{ route('user.orders.index') }}" class="text-sm text-accent hover:underline font-semibold flex items-center gap-1">
            View All <i class="fas fa-arrow-right text-xs"></i>
        </a>
    </div>
    @if($recentOrders->isEmpty())
    <div class="text-center py-12 text-gray-400">
        <div class="w-16 h-16 bg-gray-100 rounded-2xl flex items-center justify-center mx-auto mb-3">
            <i class="fas fa-box-open text-2xl text-gray-300"></i>
        </div>
        <p class="font-semibold text-sm text-gray-600">No orders yet</p>
        <p class="text-xs mt-1">Browse products and place your first order</p>
        <a href="{{ route('products.index') }}" class="mt-4 inline-flex items-center gap-2 bg-accent text-white text-xs font-bold px-5 py-2.5 rounded-xl btn-glow transition">
            Browse Products
        </a>
    </div>
    @else
    <div class="overflow-x-auto -mx-1">
        <table class="w-full text-sm min-w-[480px]">
            <thead>
                <tr class="text-left text-xs text-gray-400 uppercase tracking-wider border-b border-gray-100">
                    <th class="pb-3 pl-1 font-semibold">Order #</th>
                    <th class="pb-3 font-semibold">Amount</th>
                    <th class="pb-3 font-semibold">Status</th>
                    <th class="pb-3 font-semibold">Date</th>
                </tr>
            </thead>
            <tbody>
                @foreach($recentOrders as $order)
                @php
                    $statusKey = $order->order_type === 'local' ? ($order->local_order_status ?? '') : ($order->status ?? '');
                    $statusConfig = $order->order_type === 'local'
                        ? (config("bybhub.local_order_statuses.{$statusKey}") ?? ['label'=>$statusKey,'color'=>'gray'])
                        : (config("bybhub.order_statuses.{$statusKey}") ?? ['label'=>$statusKey,'color'=>'gray']);
                    $colorMap = ['blue'=>'bg-blue-100 text-blue-700','green'=>'bg-green-100 text-green-700','yellow'=>'bg-yellow-100 text-yellow-700','red'=>'bg-red-100 text-red-700','orange'=>'bg-orange-100 text-orange-700','purple'=>'bg-purple-100 text-purple-700','teal'=>'bg-teal-100 text-teal-700','amber'=>'bg-amber-100 text-amber-700','gray'=>'bg-gray-100 text-gray-600','indigo'=>'bg-indigo-100 text-indigo-700','violet'=>'bg-violet-100 text-violet-700','cyan'=>'bg-cyan-100 text-cyan-700'];
                    $pill = $colorMap[$statusConfig['color']] ?? 'bg-gray-100 text-gray-600';
                @endphp
                <tr class="border-b border-gray-50 last:border-0 hover:bg-gray-50/50 transition">
                    <td class="py-3 pl-1">
                        <a href="{{ route('user.orders.show', $order->id) }}" class="font-bold text-primary text-xs hover:underline">
                            #{{ $order->order_number }}
                        </a>
                        <p class="text-[11px] text-gray-400">{{ $order->order_type === 'local' ? 'Local' : 'China Import' }}</p>
                    </td>
                    <td class="py-3 font-semibold text-gray-800 text-xs">
                        {{ $symbol }}{{ number_format((float)$order->grand_total, 0) }}
                    </td>
                    <td class="py-3">
                        <span class="text-[10px] font-bold px-2 py-1 rounded-full {{ $pill }}">
                            {{ $statusConfig['label'] ?? $statusKey }}
                        </span>
                    </td>
                    <td class="py-3 text-xs text-gray-400">{{ $order->created_at->format('d M') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif
</div>

</div>
@endsection
