@extends('layouts.app')
@section('title', __('app.dashboard'))
@section('content')
<div class="space-y-6">

    <h2 class="text-xl font-bold text-primary mb-4 sm:hidden" style="font-family:Poppins,sans-serif">Dashboard</h2>

    {{-- ── Email verification reminder ───────────────────────────────────── --}}
    @if(!$user->hasVerifiedEmail())
    <div class="rounded-2xl p-4 flex flex-wrap items-center gap-4 border"
         style="background:linear-gradient(135deg,#fffbeb,#fef3c7); border-color:#fde68a;">
        <div class="w-10 h-10 bg-amber-100 rounded-xl flex items-center justify-center flex-shrink-0">
            <i class="fas fa-envelope text-amber-500 text-base"></i>
        </div>
        <div class="flex-1 min-w-0">
            <p class="font-bold text-amber-900 text-sm">Please verify your email address</p>
            <p class="text-amber-700 text-xs mt-0.5">
                We sent a verification link to <strong>{{ $user->email }}</strong>.
                Check your inbox (and spam folder).
            </p>
        </div>
        <form action="{{ route('verification.send') }}" method="POST" class="flex-shrink-0">
            @csrf
            <button type="submit"
                class="inline-flex items-center gap-2 bg-amber-500 hover:bg-amber-600 text-white text-xs font-bold px-4 py-2.5 rounded-xl transition shadow-sm">
                <i class="fas fa-paper-plane text-[10px]"></i> Resend Verification Email
            </button>
        </form>
    </div>
    @endif

    {{-- Welcome Hero --}}
    <div class="rounded-3xl p-6 md:p-8 text-white relative overflow-hidden" style="background:linear-gradient(135deg,#1A3A6B 0%,#2451A3 60%,#3560DF 100%)">
        <div class="absolute top-0 right-0 w-80 h-80 rounded-full -translate-y-1/2 translate-x-1/3 opacity-10" style="background:radial-gradient(circle,#fff,transparent)"></div>
        <div class="absolute bottom-0 left-0 w-48 h-48 rounded-full translate-y-1/2 -translate-x-1/4 opacity-8" style="background:radial-gradient(circle,#F47B20,transparent)"></div>
        <div class="relative z-10 flex flex-wrap items-center justify-between gap-5">
            <div>
                <p class="text-blue-300 text-sm font-medium mb-1">Good to see you back,</p>
                <h2 class="text-2xl md:text-3xl font-bold" style="font-family:Poppins,sans-serif">{{ explode(' ', $user->name)[0] }} 👋</h2>
                <p class="text-blue-300 text-sm mt-1.5 flex items-center gap-2">
                    <span>{{ $user->countryName() }}</span>
                    <span class="w-1 h-1 bg-blue-400 rounded-full"></span>
                    <span>{{ $user->currency }}</span>
                    <span class="w-1 h-1 bg-blue-400 rounded-full"></span>
                    <span class="font-mono text-xs text-blue-200">{{ $user->referral_code }}</span>
                </p>
            </div>
            <div class="text-right">
                <p class="text-blue-300 text-xs font-semibold uppercase tracking-wider mb-1">Wallet Balance</p>
                <p class="text-4xl font-black" style="font-family:Poppins,sans-serif">{{ $symbol }}{{ number_format($wallet, 2) }}</p>
                <div class="flex gap-2 mt-3 justify-end">
                    <a href="{{ route('user.wallet.fund') }}" class="inline-flex items-center gap-1.5 bg-accent hover:bg-accent-dark text-white text-xs font-bold px-4 py-2 rounded-xl transition btn-glow">
                        <i class="fas fa-plus"></i> Fund Wallet
                    </a>
                    <a href="{{ route('user.wallet.index') }}" class="inline-flex items-center gap-1.5 text-white text-xs font-semibold px-4 py-2 rounded-xl transition" style="background:rgba(255,255,255,0.12)">
                        <i class="fas fa-wallet"></i> View Wallet
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Stats --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        @foreach([
            ['icon'=>'fas fa-box','color'=>'from-blue-500 to-blue-600','val'=>$totalOrders,'label'=>'Total Orders'],
            ['icon'=>'fas fa-check-circle','color'=>'from-green-500 to-green-600','val'=>$deliveredOrders,'label'=>'Delivered'],
            ['icon'=>'fab fa-alipay','color'=>'from-purple-500 to-purple-600','val'=>$pendingSupplierPayments,'label'=>'Pending Payments'],
            ['icon'=>'fas fa-users','color'=>'from-accent to-orange-500','val'=>$user->referrals_count ?? 0,'label'=>'Referrals'],
        ] as $s)
        <div class="bg-white rounded-2xl p-5 shadow-card hover:shadow-card-hover transition-shadow">
            <div class="flex items-center justify-between mb-3">
                <div class="w-10 h-10 bg-gradient-to-br {{ $s['color'] }} rounded-xl flex items-center justify-center shadow-sm">
                    <i class="{{ $s['icon'] }} text-white text-sm"></i>
                </div>
            </div>
            <p class="text-2xl font-black text-gray-800" style="font-family:Poppins,sans-serif">{{ $s['val'] }}</p>
            <p class="text-xs text-gray-500 font-medium mt-0.5">{{ $s['label'] }}</p>
        </div>
        @endforeach
    </div>

    {{-- Virtual Account + Exchange Rates --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
        @if($monnifyAccount)
        <div class="bg-white rounded-2xl p-5 shadow-card">
            <h3 class="font-bold text-primary mb-4 flex items-center gap-2 text-sm">
                <div class="w-7 h-7 bg-primary/10 rounded-lg flex items-center justify-center"><i class="fas fa-university text-primary text-xs"></i></div>
                Your Dedicated Virtual Account
            </h3>
            @foreach($monnifyAccount->accountsList() as $acct)
            <div class="bg-gradient-to-r from-primary-50 to-blue-50 border border-primary/10 rounded-xl p-4 mb-2.5 last:mb-0" x-data>
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <p class="text-[11px] font-semibold text-gray-500 uppercase tracking-wider">{{ $acct['bank'] }}</p>
                        <p class="text-2xl font-black text-primary tracking-widest mt-0.5 font-mono">{{ $acct['number'] }}</p>
                        <p class="text-xs text-gray-600 mt-0.5">{{ $acct['name'] }}</p>
                    </div>
                    <button @click="navigator.clipboard.writeText('{{ $acct['number'] }}').then(()=>{$el.innerHTML='<i class=\'fas fa-check\'></i> Copied';setTimeout(()=>{$el.innerHTML='<i class=\'fas fa-copy\'></i> Copy'},2000)})"
                        class="flex-shrink-0 text-xs bg-primary text-white px-3 py-1.5 rounded-lg hover:bg-primary-dark transition flex items-center gap-1.5">
                        <i class="fas fa-copy"></i> Copy
                    </button>
                </div>
            </div>
            @endforeach
            <p class="text-xs text-gray-400 mt-3 flex items-center gap-1.5">
                <i class="fas fa-info-circle text-blue-400"></i>
                Transfer any amount — wallet credits within seconds
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
            <a href="{{ route('user.wallet.request-virtual-account') }}" class="inline-flex items-center gap-2 bg-primary text-white text-xs font-bold px-5 py-2.5 rounded-xl hover:bg-primary-dark transition shadow-glow-primary">
                <i class="fas fa-plus"></i> Request Virtual Account
            </a>
            @endif
        </div>
        @endif

        <div class="bg-white rounded-2xl p-5 shadow-card">
            <h3 class="font-bold text-primary mb-4 flex items-center gap-2 text-sm">
                <div class="w-7 h-7 bg-primary/10 rounded-lg flex items-center justify-center"><i class="fas fa-chart-line text-primary text-xs"></i></div>
                Live Exchange Rates
            </h3>
            @foreach($rates as $currency => $rate)
            <div class="flex items-center justify-between py-3 border-b border-gray-50 last:border-0">
                <span class="text-sm text-gray-500 font-medium">¥1 CNY</span>
                <div class="text-right">
                    <span class="font-bold text-primary text-sm">
                        {{ config("bybhub.countries.".match($currency){'GHS'=>'GH','XOF'=>'NE',default=>'NG'}.".symbol") }}{{ number_format((float)($rate['rate']??0),2) }}
                    </span>
                    <span class="text-xs text-gray-400 ml-1">{{ $currency }}</span>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    {{-- Quick Actions --}}
    <div>
        <h3 class="text-sm font-bold text-gray-700 mb-3 uppercase tracking-wider">Quick Actions</h3>

        {{-- Abandoned cart nudge --}}
        @if(isset($hasAbandonedCart) && $hasAbandonedCart)
        <div class="bg-amber-50 border border-amber-200 rounded-2xl p-4 mb-4 flex items-center gap-3">
            <div class="w-10 h-10 bg-amber-100 rounded-xl flex items-center justify-center flex-shrink-0">
                <i class="fas fa-shopping-cart text-amber-500"></i>
            </div>
            <div class="flex-1 min-w-0">
                <p class="font-bold text-amber-800 text-sm">You left items in your cart!</p>
                <p class="text-xs text-amber-600 mt-0.5">Your saved items are waiting. Complete your order before they sell out.</p>
            </div>
            <a href="{{ route('user.cart.index') }}" class="bg-accent text-white font-bold text-xs px-3 py-2 rounded-xl flex-shrink-0 hover:bg-accent-dark transition">
                View Cart
            </a>
        </div>
        @endif

        <div class="grid grid-cols-2 md:grid-cols-4 gap-3">

            {{-- My Orders with pending badge --}}
            <a href="{{ route('user.orders.index') }}" class="relative bg-white rounded-2xl p-4 shadow-card hover:shadow-card-hover transition-all group">
                @if(($pendingOrdersCount ?? 0) > 0)
                <span class="absolute -top-1.5 -right-1.5 min-w-[20px] h-5 bg-red-500 text-white text-[10px] font-black rounded-full flex items-center justify-center px-1 z-10">{{ ($pendingOrdersCount > 9) ? '9+' : $pendingOrdersCount }}</span>
                @endif
                <div class="w-11 h-11 bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl flex items-center justify-center mb-3 group-hover:scale-110 transition-transform shadow-sm">
                    <i class="fas fa-box text-white text-base"></i>
                </div>
                <p class="font-bold text-gray-800 text-sm">My Orders</p>
                <p class="text-xs text-gray-400 mt-0.5">{{ $pendingOrdersCount ?? 0 }} in progress</p>
            </a>

            {{-- Cart with item count badge --}}
            <a href="{{ route('user.cart.index') }}" class="relative bg-white rounded-2xl p-4 shadow-card hover:shadow-card-hover transition-all group">
                @if(($cartItemsCount ?? 0) > 0)
                <span class="absolute -top-1.5 -right-1.5 min-w-[20px] h-5 bg-accent text-white text-[10px] font-black rounded-full flex items-center justify-center px-1 z-10">{{ $cartItemsCount }}</span>
                @endif
                <div class="w-11 h-11 bg-gradient-to-br from-accent to-orange-400 rounded-xl flex items-center justify-center mb-3 group-hover:scale-110 transition-transform shadow-sm">
                    <i class="fas fa-shopping-cart text-white text-base"></i>
                </div>
                <p class="font-bold text-gray-800 text-sm">My Cart</p>
                <p class="text-xs text-gray-400 mt-0.5">{{ $cartItemsCount ?? 0 }} item(s)</p>
            </a>

            {{-- Notifications with unread badge --}}
            <a href="{{ route('user.notifications.index') }}" class="relative bg-white rounded-2xl p-4 shadow-card hover:shadow-card-hover transition-all group">
                @if(($unreadNotificationsCount ?? 0) > 0)
                <span class="absolute -top-1.5 -right-1.5 min-w-[20px] h-5 bg-red-500 text-white text-[10px] font-black rounded-full flex items-center justify-center px-1 z-10">{{ ($unreadNotificationsCount > 9) ? '9+' : $unreadNotificationsCount }}</span>
                @endif
                <div class="w-11 h-11 bg-gradient-to-br from-purple-500 to-purple-600 rounded-xl flex items-center justify-center mb-3 group-hover:scale-110 transition-transform shadow-sm">
                    <i class="fas fa-bell text-white text-base"></i>
                </div>
                <p class="font-bold text-gray-800 text-sm">Alerts</p>
                <p class="text-xs text-gray-400 mt-0.5">{{ $unreadNotificationsCount ?? 0 }} unread</p>
            </a>

            {{-- Find Product --}}
            <a href="{{ route('user.scraper.index') }}" class="bg-white rounded-2xl p-4 shadow-card hover:shadow-card-hover transition-all group">
                <div class="w-11 h-11 bg-gradient-to-br from-green-500 to-emerald-600 rounded-xl flex items-center justify-center mb-3 group-hover:scale-110 transition-transform shadow-sm">
                    <i class="fas fa-search text-white text-base"></i>
                </div>
                <p class="font-bold text-gray-800 text-sm">Find Product</p>
                <p class="text-xs text-gray-400 mt-0.5">1688 / Taobao / Alibaba</p>
            </a>

        </div>
    </div>

    {{-- Spin & Win promo banner (only shown when feature is active) --}}
    @if(\App\Models\Setting::get('spinwin_enabled','0') === '1')
    <a href="{{ route('user.spinwin.show') }}"
       class="block relative rounded-2xl overflow-hidden text-white px-6 py-5 group"
       style="background:linear-gradient(135deg,#6d28d9 0%,#db2777 55%,#f97316 100%)">
        {{-- Decorative circles --}}
        <div class="absolute top-0 right-0 w-40 h-40 rounded-full opacity-20 pointer-events-none" style="background:radial-gradient(circle,#fff,transparent);transform:translate(30%,-30%)"></div>
        <div class="absolute bottom-0 left-0 w-24 h-24 rounded-full opacity-15 pointer-events-none" style="background:radial-gradient(circle,#fff,transparent);transform:translate(-20%,30%)"></div>
        <div class="relative flex items-center gap-4">
            <div class="text-4xl group-hover:scale-110 transition-transform duration-300" style="animation:spin-slow 6s linear infinite">🎡</div>
            <div class="flex-1 min-w-0">
                <p class="font-black text-lg" style="font-family:Poppins,sans-serif">Spin &amp; Win!</p>
                <p class="text-white/80 text-sm">Spin the wheel today and win a discount coupon for your next order.</p>
            </div>
            <div class="shrink-0 bg-white/20 hover:bg-white/30 backdrop-blur-sm px-4 py-2 rounded-xl text-sm font-bold transition">
                Spin Now →
            </div>
        </div>
    </a>
    <style>@keyframes spin-slow { from{transform:rotate(0deg)} to{transform:rotate(360deg)} }</style>
    @endif

    {{-- Recent Orders --}}
    {{-- ── Push notification prompt (dismissable, shown once) ──────────────── --}}
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
        dismiss() {
            localStorage.setItem('push_prompt_dismissed', '1');
            this.show = false;
        }
    }" x-show="show" style="display:none"
    x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="opacity-0 -translate-y-2"
    x-transition:enter-end="opacity-100 translate-y-0">
        <div class="bg-primary/5 border border-primary/20 rounded-2xl p-4 flex items-center gap-3">
            <div class="w-9 h-9 bg-primary/10 rounded-xl flex items-center justify-center flex-shrink-0">
                <i class="fas fa-bell text-primary text-sm"></i>
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-sm font-bold text-primary leading-tight">Stay updated on your orders</p>
                <p class="text-xs text-gray-500 mt-0.5">Enable push notifications to get instant alerts.</p>
            </div>
            <div class="flex items-center gap-2 flex-shrink-0">
                <button @click="enable()" class="bg-primary text-white font-bold text-xs px-3 py-2 rounded-xl hover:bg-primary-dark transition">Enable</button>
                <button @click="dismiss()" class="text-gray-400 hover:text-gray-600 text-xs px-2 py-2 transition"><i class="fas fa-times"></i></button>
            </div>
        </div>
    </div>

    {{-- Recent Orders --}}
    <div class="bg-white rounded-2xl shadow-card p-5">
        <div class="flex justify-between items-center mb-5">
            <h3 class="font-bold text-primary flex items-center gap-2">
                <i class="fas fa-box text-sm"></i> Recent Orders
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
                <p class="font-semibold text-sm">{{ __('orders.no_orders') }}</p>
                <p class="text-xs mt-1">Explore our products and place your first order</p>
                <a href="{{ route('products.index') }}" class="mt-4 inline-block bg-accent text-white text-xs font-bold px-5 py-2.5 rounded-xl btn-glow transition">Browse Products</a>
            </div>
        @else
            <div class="overflow-x-auto -mx-1">
                <table class="w-full text-sm min-w-[500px]">
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
                        <tr class="border-b border-gray-50 last:border-0 hover:bg-gray-50/50 transition">
                            <td class="py-3 pl-1"><a href="{{ route('user.orders.show',$order->id) }}" class="font-bold text-primary hover:underline">#{{ $order->order_number }}</a></td>
                            <td class="py-3 font-bold text-gray-800">{{ $symbol }}{{ number_format($order->grand_total,2) }}</td>
                            <td class="py-3"><span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-{{ $order->getStatusColor() }}-100 text-{{ $order->getStatusColor() }}-700">{{ $order->getStatusLabel() }}</span></td>
                            <td class="py-3 text-gray-400 text-xs">{{ $order->created_at->format('M d, Y') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    {{-- Telegram Prompt --}}
    @if(!$user->hasTelegramLinked())
    <div class="rounded-2xl p-5 text-white flex flex-wrap items-center gap-4" style="background:linear-gradient(135deg,#229ED9 0%,#1a7fb5 100%)">
        <div class="w-12 h-12 bg-white/15 rounded-2xl flex items-center justify-center flex-shrink-0">
            <i class="fab fa-telegram text-2xl"></i>
        </div>
        <div class="flex-1">
            <p class="font-bold">Link Telegram for Instant Alerts</p>
            <p class="text-sm text-blue-100 mt-0.5">Get real-time order updates, wallet credits & payment confirmations on Telegram.</p>
        </div>
        <a href="{{ route('user.link-telegram') }}" class="flex-shrink-0 bg-white text-blue-600 font-bold text-sm px-5 py-2.5 rounded-xl hover:bg-blue-50 transition shadow-sm">
            Link Now <i class="fas fa-arrow-right ml-1 text-xs"></i>
        </a>
    </div>
    @endif
</div>
@endsection
