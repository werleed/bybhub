@extends('layouts.app')
@section('title', 'My Account')
@section('content')
@php
    $totalOrders     = \App\Models\Order::where('user_id',$user->id)->count();
    $deliveredOrders = \App\Models\Order::where('user_id',$user->id)->where('status','delivered')->count();
    $walletBalance   = $user->getWalletBalance();
    $sym             = $user->currencySymbol();
    $totalReferrals  = \App\Models\Referral::where('referrer_id',$user->id)->count();
    $initials        = collect(explode(' ', $user->name))->map(fn($w) => strtoupper(substr($w,0,1)))->take(2)->join('');
    // Vendor data
    $vendor          = $user->is_vendor ? $user->vendorProfile : null;
    $pendingVendorOrders = $vendor ? \App\Models\Order::where('vendor_id',$vendor->id)->where('local_order_status','paid')->count() : 0;
@endphp

<div class="max-w-4xl mx-auto space-y-6"
     x-data="{
        tab: '{{ session('tab', request()->fragment ?? ($user->is_vendor ? 'vendor' : 'profile')) }}',
        showSuccess: {{ session('success') ? 'true' : 'false' }},
        init(){ if(this.showSuccess) setTimeout(()=>this.showSuccess=false, 4000) }
     }">

{{-- ── Hero Banner ─────────────────────────────────────────────────────────── --}}
<div class="relative rounded-3xl overflow-hidden"
     style="background:{{ $user->is_vendor ? 'linear-gradient(135deg,#7c2d12 0%,#c2410c 40%,#F47B20 100%)' : 'linear-gradient(135deg,#0A1628 0%,#1A3A6B 55%,#2451A3 100%)' }};min-height:180px">
    {{-- Decorative circles --}}
    <div class="absolute top-0 right-0 w-64 h-64 rounded-full opacity-10" style="background:radial-gradient(circle,#F47B20,transparent);transform:translate(30%,-30%)"></div>
    <div class="absolute bottom-0 left-0 w-48 h-48 rounded-full opacity-8" style="background:radial-gradient(circle,white,transparent);transform:translate(-20%,30%)"></div>
    {{-- Animated dots --}}
    <div class="absolute inset-0 overflow-hidden pointer-events-none">
        @for($i=0;$i<8;$i++)<div class="absolute w-1 h-1 rounded-full bg-white/10" style="top:{{ rand(10,90) }}%;left:{{ rand(5,95) }}%;animation:float {{ rand(3,6) }}s ease-in-out infinite {{ $i*0.4 }}s"></div>@endfor
    </div>
    <div class="relative px-6 py-8 flex items-center gap-6 flex-wrap">
        {{-- Avatar --}}
        <div class="relative flex-shrink-0"
             x-data="{ hover: false }" @mouseenter="hover=true" @mouseleave="hover=false">
            <div class="w-20 h-20 rounded-2xl flex items-center justify-center text-white text-3xl font-black shadow-2xl transition-transform duration-300"
                 :class="hover ? 'scale-110' : 'scale-100'"
                 style="background:linear-gradient(135deg,#F47B20,#D4660E);box-shadow:0 8px 30px rgba(244,123,32,0.4)">
                {{ $initials }}
            </div>
            <div class="absolute -bottom-1 -right-1 w-5 h-5 bg-green-400 rounded-full border-2 border-white flex items-center justify-center">
                <i class="fas fa-check text-white text-[8px]"></i>
            </div>
        </div>
        {{-- Info --}}
        <div class="flex-1 min-w-0">
            <h1 class="text-white font-black text-2xl leading-tight" style="font-family:Poppins,sans-serif">{{ $user->name }}</h1>
            <p class="text-blue-300 text-sm mt-0.5">{{ $user->email }}</p>
            <div class="flex items-center gap-3 mt-2 flex-wrap text-xs">
                <span class="flex items-center gap-1.5 text-white/70">
                    {{ config('bybhub.countries.'.$user->country.'.flag','🌍') }}
                    {{ config('bybhub.countries.'.$user->country.'.name', $user->country) }}
                </span>
                <span class="w-1 h-1 bg-white/30 rounded-full"></span>
                <span class="flex items-center gap-1.5 text-white/70">
                    <i class="fas fa-calendar text-white/40"></i>
                    Member since {{ $user->created_at->format('M Y') }}
                </span>
                @if($user->is_vendor)
                <span class="flex items-center gap-1.5 bg-accent/30 border border-accent/40 text-accent px-2.5 py-0.5 rounded-full font-bold">
                    <i class="fas fa-store text-[10px]"></i> Verified Vendor
                </span>
                @endif
            </div>
        </div>
        {{-- Stats --}}
        <div class="grid grid-cols-3 gap-3 sm:flex sm:gap-3 flex-shrink-0">
            @foreach([
                [$totalOrders,    'Orders',    'fa-box',      'blue'],
                [$deliveredOrders,'Delivered', 'fa-check',    'green'],
                [$totalReferrals, 'Referrals', 'fa-users',    'purple'],
            ] as [$val,$label,$icon,$color])
            <div class="text-center rounded-2xl px-4 py-3 min-w-[72px]" style="background:rgba(255,255,255,0.1);border:1px solid rgba(255,255,255,0.15)">
                <i class="fas {{ $icon }} text-{{ $color }}-300 text-sm mb-1 block"></i>
                <p class="font-black text-white text-xl leading-none" style="font-family:Poppins,sans-serif">{{ $val }}</p>
                <p class="text-white/50 text-[10px] mt-0.5">{{ $label }}</p>
            </div>
            @endforeach
            @if($vendor)
            {{-- Vendor-specific stats --}}
            @foreach([
                ['₦'.number_format((float)$vendor->available_balance,0), 'Available',  'fa-wallet',        'yellow'],
                [number_format((float)$vendor->total_revenue,0,'.',','), 'Revenue',    'fa-chart-line',    'green'],
                [$pendingVendorOrders,                                    'New Orders', 'fa-shopping-bag',  'red'],
            ] as [$val,$label,$icon,$color])
            <div class="text-center rounded-2xl px-4 py-3 min-w-[72px]" style="background:rgba(255,255,255,0.12);border:1px solid rgba(255,255,255,0.2)">
                <i class="fas {{ $icon }} text-{{ $color }}-300 text-sm mb-1 block"></i>
                <p class="font-black text-white text-base leading-none" style="font-family:Poppins,sans-serif">{{ $val }}</p>
                <p class="text-white/50 text-[10px] mt-0.5">{{ $label }}</p>
            </div>
            @endforeach
            @endif
        </div>
    </div>
</div>

{{-- ── Toast success --}}
<div x-show="showSuccess" x-transition.opacity.duration.500ms
     class="fixed top-6 right-6 z-50 bg-green-500 text-white text-sm font-bold px-5 py-3 rounded-2xl shadow-2xl flex items-center gap-2.5">
    <i class="fas fa-check-circle"></i> {{ session('success') }}
</div>

{{-- ── Tab nav --}}
<div class="bg-white rounded-2xl shadow-card p-1.5 grid gap-1 overflow-x-auto
    {{ $user->is_vendor ? 'grid-cols-2 md:grid-cols-6' : 'grid-cols-2 md:grid-cols-5' }}" id="profile-tabs" style="-webkit-overflow-scrolling:touch">
    @foreach([
        ['profile',  'fa-user',        'Profile'],
        ['address',  'fa-map-marker-alt','Address'],
        ['security', 'fa-shield-alt',   'Security'],
        ['activity', 'fa-history',      'Activity'],
        ['settings', 'fa-sliders-h',    'Preferences'],
    ] as [$id,$icon,$label])
    <button type="button" @click="tab='{{ $id }}'"
        class="flex items-center justify-center gap-1.5 py-2.5 px-3 rounded-xl text-xs font-semibold transition"
        :class="tab==='{{ $id }}' ? 'bg-primary text-white shadow-md' : 'text-gray-500 hover:text-gray-700 hover:bg-gray-100'">
        <i class="fas {{ $icon }} text-[10px]"></i>
        {{ $label }}
    </button>
    @endforeach
    @if($user->is_vendor)
    <button type="button" @click="tab='vendor'"
        class="flex items-center justify-center gap-1.5 py-2.5 px-3 rounded-xl text-xs font-semibold transition relative"
        :class="tab==='vendor' ? 'bg-accent text-white shadow-md' : 'text-gray-500 hover:text-gray-700 hover:bg-orange-50'">
        <i class="fas fa-store text-[10px]"></i>
        My Store
        @if($pendingVendorOrders > 0)
        <span class="absolute -top-1 -right-1 w-4 h-4 bg-red-500 rounded-full text-white text-[8px] font-black flex items-center justify-center">{{ $pendingVendorOrders }}</span>
        @endif
    </button>
    @endif
</div>

@if($errors->any())
<div class="bg-red-50 border border-red-200 rounded-2xl p-4 text-sm text-red-700 space-y-1">
    @foreach($errors->all() as $e)<p class="flex items-center gap-2"><i class="fas fa-times-circle text-red-400 flex-shrink-0"></i>{{ $e }}</p>@endforeach
</div>
@endif

{{-- ── TAB 1: Profile ─────────────────────────────────────────────────────── --}}
<div x-show="tab==='profile'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0">
    <form action="{{ route('user.profile.update') }}" method="POST" class="bg-white rounded-2xl shadow-card p-6 space-y-5">
        @csrf @method('PATCH')
        <div class="flex items-center gap-2 pb-3 border-b border-gray-100">
            <div class="w-8 h-8 bg-primary/10 rounded-xl flex items-center justify-center flex-shrink-0">
                <i class="fas fa-user text-primary text-xs"></i>
            </div>
            <div>
                <p class="font-bold text-primary text-sm">Personal Information</p>
                <p class="text-xs text-gray-400">Your public name and contact details</p>
            </div>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="md:col-span-2 md:col-span-1">
                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Full Name</label>
                <div class="relative">
                    <i class="fas fa-user absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-300 text-sm"></i>
                    <input type="text" name="name" value="{{ old('name',$user->name) }}" required
                        class="w-full border-2 border-gray-100 bg-gray-50 rounded-xl pl-10 pr-4 py-3 text-sm focus:outline-none focus:border-primary focus:bg-white transition">
                </div>
            </div>
            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Phone Number</label>
                <div class="relative">
                    <i class="fas fa-phone absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-300 text-sm"></i>
                    <input type="text" name="phone" value="{{ old('phone',$user->phone) }}" required
                        class="w-full border-2 border-gray-100 bg-gray-50 rounded-xl pl-10 pr-4 py-3 text-sm focus:outline-none focus:border-primary focus:bg-white transition"
                        placeholder="+234 800 000 0000">
                </div>
            </div>
            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Email Address</label>
                <div class="relative">
                    <i class="fas fa-envelope absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-300 text-sm"></i>
                    <input type="email" value="{{ $user->email }}" disabled
                        class="w-full border-2 border-gray-100 bg-gray-50 rounded-xl pl-10 pr-4 py-3 text-sm text-gray-400 cursor-not-allowed">
                </div>
                <p class="text-[10px] text-gray-400 mt-1 flex items-center gap-1">
                    <i class="fas fa-info-circle text-gray-300"></i> Contact support to change your email.
                </p>
            </div>
            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Country</label>
                <div class="relative">
                    <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-lg">{{ config('bybhub.countries.'.$user->country.'.flag','🌍') }}</span>
                    <input type="text" value="{{ config('bybhub.countries.'.$user->country.'.name', $user->country) }}" disabled
                        class="w-full border-2 border-gray-100 bg-gray-50 rounded-xl pl-10 pr-4 py-3 text-sm text-gray-400 cursor-not-allowed">
                </div>
            </div>
        </div>
        {{-- Referral code display --}}
        @if($user->referral_code)
        <div class="bg-purple-50 border border-purple-200 rounded-xl p-4 flex items-center justify-between gap-3"
             x-data="{ copied: false }">
            <div>
                <p class="text-xs font-bold text-purple-700 mb-0.5">Your Referral Code</p>
                <p class="font-black text-purple-800 font-mono text-lg tracking-widest">{{ $user->referral_code }}</p>
                <p class="text-[10px] text-purple-500 mt-0.5">Share this code — earn ₦500 for every qualifying referral</p>
            </div>
            <button type="button"
                @click="navigator.clipboard.writeText('{{ $user->referral_code }}').then(()=>{copied=true;setTimeout(()=>copied=false,2000)})"
                class="flex-shrink-0 bg-purple-600 hover:bg-purple-700 text-white font-bold px-4 py-2 rounded-xl text-xs transition flex items-center gap-1.5">
                <i class="fas" :class="copied ? 'fa-check' : 'fa-copy'"></i>
                <span x-text="copied ? 'Copied!' : 'Copy'"></span>
            </button>
        </div>
        @endif
        <button class="bg-primary hover:bg-primary-dark text-white font-bold px-8 py-3 rounded-xl text-sm transition flex items-center gap-2 shadow-md hover:shadow-lg">
            <i class="fas fa-save"></i> Save Profile
        </button>
    </form>
</div>

{{-- ── TAB 2: Address ─────────────────────────────────────────────────────── --}}
<div x-show="tab==='address'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" id="address">
    <form action="{{ route('user.profile.update') }}" method="POST" class="bg-white rounded-2xl shadow-card p-6 space-y-5">
        @csrf @method('PATCH')
        <div class="flex items-center gap-2 pb-3 border-b border-gray-100">
            <div class="w-8 h-8 bg-accent/10 rounded-xl flex items-center justify-center flex-shrink-0">
                <i class="fas fa-map-marker-alt text-accent text-xs"></i>
            </div>
            <div>
                <p class="font-bold text-primary text-sm">Default Delivery Address</p>
                <p class="text-xs text-gray-400">Auto-fills when ordering from local vendors · Nigeria only</p>
            </div>
        </div>
        @if($user->default_address)
        <div class="bg-green-50 border border-green-200 rounded-xl p-3 text-xs text-green-700 flex items-start gap-2">
            <i class="fas fa-check-circle text-green-500 flex-shrink-0 mt-0.5"></i>
            <div>
                <p class="font-bold">Current saved address:</p>
                <p class="mt-0.5 text-green-600">{{ $user->default_address }}, {{ $user->default_city }}, {{ $user->default_state }}
                @if($user->default_landmark) · {{ $user->default_landmark }}@endif</p>
            </div>
        </div>
        @endif
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="md:col-span-2">
                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Street Address</label>
                <div class="relative">
                    <i class="fas fa-road absolute left-3.5 top-3.5 text-gray-300 text-sm"></i>
                    <input type="text" name="default_address" value="{{ old('default_address',$user->default_address) }}"
                        placeholder="e.g. 14 Balogun Street, Ikeja"
                        class="w-full border-2 border-gray-100 bg-gray-50 rounded-xl pl-10 pr-4 py-3 text-sm focus:outline-none focus:border-primary focus:bg-white transition">
                </div>
            </div>
            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">City</label>
                <input type="text" name="default_city" value="{{ old('default_city',$user->default_city) }}" placeholder="e.g. Lagos"
                    class="w-full border-2 border-gray-100 bg-gray-50 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-primary focus:bg-white transition">
            </div>
            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">State</label>
                <input type="text" name="default_state" value="{{ old('default_state',$user->default_state) }}" placeholder="e.g. Lagos State"
                    class="w-full border-2 border-gray-100 bg-gray-50 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-primary focus:bg-white transition">
            </div>
            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">LGA <span class="text-gray-400 font-normal normal-case">(optional)</span></label>
                <input type="text" name="default_lga" value="{{ old('default_lga',$user->default_lga) }}" placeholder="e.g. Ikeja LGA"
                    class="w-full border-2 border-gray-100 bg-gray-50 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-primary focus:bg-white transition">
            </div>
            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Landmark <span class="text-gray-400 font-normal normal-case">(optional)</span></label>
                <input type="text" name="default_landmark" value="{{ old('default_landmark',$user->default_landmark) }}"
                    placeholder="e.g. Near GTBank, opposite Total"
                    class="w-full border-2 border-gray-100 bg-gray-50 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-primary focus:bg-white transition">
            </div>
        </div>
        <div class="bg-blue-50 border border-blue-200 rounded-xl p-3 text-xs text-blue-700 flex items-center gap-2">
            <i class="fas fa-info-circle text-blue-400 flex-shrink-0"></i>
            This address auto-fills at local vendor checkout so you don't have to type it every time.
        </div>
        <button class="bg-accent hover:bg-orange-600 text-white font-bold px-8 py-3 rounded-xl text-sm transition flex items-center gap-2 shadow-md">
            <i class="fas fa-map-pin"></i> Save Address
        </button>
    </form>
</div>

{{-- ── TAB 3: Security ────────────────────────────────────────────────────── --}}
<div x-show="tab==='security'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0">
    <div class="space-y-4">
        {{-- Change password --}}
        <form action="{{ route('user.profile.password') }}" method="POST" class="bg-white rounded-2xl shadow-card p-6 space-y-5"
              x-data="{ showCurrent:false, showNew:false, showConf:false, strength:0, pass:'',
                checkStr(v){ this.pass=v; let s=0; if(v.length>=8)s++; if(/[A-Z]/.test(v))s++; if(/[0-9]/.test(v))s++; if(/[^A-Za-z0-9]/.test(v))s++; this.strength=s; } }">
            @csrf @method('PATCH')
            <div class="flex items-center gap-2 pb-3 border-b border-gray-100">
                <div class="w-8 h-8 bg-red-100 rounded-xl flex items-center justify-center flex-shrink-0">
                    <i class="fas fa-lock text-red-500 text-xs"></i>
                </div>
                <div>
                    <p class="font-bold text-primary text-sm">Change Password</p>
                    <p class="text-xs text-gray-400">Use a strong password you don't use elsewhere</p>
                </div>
            </div>
            @foreach([['current_password','Current Password','showCurrent'],['password','New Password','showNew'],['password_confirmation','Confirm New Password','showConf']] as [$field,$label,$showVar])
            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">{{ $label }}</label>
                <div class="relative">
                    <i class="fas fa-lock absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-300 text-sm"></i>
                    <input :type="{{ $showVar }} ? 'text' : 'password'" name="{{ $field }}" required
                        {{ $field==='password' ? "@input=checkStr(\$event.target.value)" : '' }}
                        class="w-full border-2 border-gray-100 bg-gray-50 rounded-xl pl-10 pr-11 py-3 text-sm focus:outline-none focus:border-primary focus:bg-white transition">
                    <button type="button" @click="{{ $showVar }}=!{{ $showVar }}" class="absolute right-3.5 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 transition">
                        <i :class="{{ $showVar }} ? 'fas fa-eye-slash' : 'fas fa-eye'" class="text-sm"></i>
                    </button>
                </div>
                @if($field==='password')
                <div class="flex gap-1 mt-2" x-show="pass.length>0" x-transition>
                    @foreach([1,2,3,4] as $i)
                    <div class="flex-1 h-1.5 rounded-full transition-all duration-300"
                         :class="strength>={{ $i }} ? (strength<=1?'bg-red-400':strength<=2?'bg-yellow-400':strength<=3?'bg-blue-400':'bg-green-400') : 'bg-gray-200'"></div>
                    @endforeach
                </div>
                <p class="text-[10px] mt-1" x-show="pass.length>0" x-transition
                   :class="strength<=1?'text-red-500':strength<=2?'text-yellow-500':strength<=3?'text-blue-500':'text-green-500'"
                   x-text="['','Weak','Fair','Good ✓','Strong 🔒'][strength]+' password'"></p>
                @endif
            </div>
            @endforeach
            <button class="bg-red-500 hover:bg-red-600 text-white font-bold px-8 py-3 rounded-xl text-sm transition flex items-center gap-2 shadow-md">
                <i class="fas fa-shield-alt"></i> Update Password
            </button>
        </form>

        {{-- Security info tiles --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            @foreach([
                ['fa-envelope','text-blue-500','bg-blue-50','Email Verified','Your email address is confirmed','border-blue-200'],
                ['fa-mobile-alt','text-green-500','bg-green-50','2FA Not Set','Add two-factor authentication soon','border-green-200'],
                ['fa-clock','text-gray-400','bg-gray-50','Last Login',($user->last_login_at?$user->last_login_at->diffForHumans():'N/A'),'border-gray-200'],
            ] as [$icon,$color,$bg,$title,$desc,$border])
            <div class="rounded-2xl p-4 border {{ $bg }} {{ $border }} flex items-start gap-3">
                <div class="w-8 h-8 rounded-xl {{ $bg }} flex items-center justify-center flex-shrink-0 border {{ $border }}">
                    <i class="fas {{ $icon }} {{ $color }} text-sm"></i>
                </div>
                <div><p class="font-bold text-gray-800 text-sm">{{ $title }}</p><p class="text-xs text-gray-500 mt-0.5">{{ $desc }}</p></div>
            </div>
            @endforeach
        </div>
    </div>
</div>

{{-- ── TAB 4: Activity ────────────────────────────────────────────────────── --}}
<div x-show="tab==='activity'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0">
    <div class="space-y-4">
        {{-- Quick stats grid --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            @php
                $pendingOrders   = \App\Models\Order::where('user_id',$user->id)->whereIn('status',['paid','procuring','china_warehouse','in_transit'])->count();
                $walletTx        = \App\Models\WalletTransaction::where('user_id',$user->id)->count();
                $unreadNotifs    = \App\Models\UserNotification::where('user_id',$user->id)->whereNull('read_at')->count();
            @endphp
            @foreach([
                [$walletBalance,    $sym.' Balance',    'fa-wallet',    'from-teal-500 to-emerald-600',  route('user.wallet.index')],
                [$totalOrders,      'Total Orders',     'fa-box',       'from-blue-500 to-blue-600',     route('user.orders.index')],
                [$pendingOrders,    'In Progress',      'fa-sync-alt',  'from-amber-500 to-orange-500',  route('user.orders.index')],
                [$unreadNotifs,     'Unread Notifs',    'fa-bell',      'from-purple-500 to-indigo-600', route('user.notifications.index')],
            ] as [$val,$label,$icon,$gradient,$link])
            <a href="{{ $link }}" class="bg-white rounded-2xl shadow-card p-5 hover:shadow-card-hover transition-shadow group block">
                <div class="w-10 h-10 rounded-2xl bg-gradient-to-br {{ $gradient }} flex items-center justify-center mb-3 group-hover:scale-110 transition-transform shadow-md">
                    <i class="fas {{ $icon }} text-white text-sm"></i>
                </div>
                <p class="font-black text-gray-800 text-xl" style="font-family:Poppins,sans-serif">
                    {{ is_float($val) || $label===$sym.' Balance' ? $sym.number_format((float)$val,2) : $val }}
                </p>
                <p class="text-xs text-gray-500 font-medium mt-0.5">{{ $label }}</p>
            </a>
            @endforeach
        </div>

        {{-- Recent orders --}}
        @php $recentOrders = \App\Models\Order::where('user_id',$user->id)->latest()->take(5)->get(); @endphp
        <div class="bg-white rounded-2xl shadow-card overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                <h3 class="font-bold text-primary text-sm flex items-center gap-2"><i class="fas fa-box text-accent text-xs"></i> Recent Orders</h3>
                <a href="{{ route('user.orders.index') }}" class="text-xs text-primary font-bold hover:underline">View all →</a>
            </div>
            @forelse($recentOrders as $o)
            @php $cfg=config("bybhub.order_statuses.{$o->status}",[]); @endphp
            <a href="{{ route('user.orders.show', $o) }}" class="flex items-center gap-3 px-5 py-3.5 border-b border-gray-50 last:border-0 hover:bg-gray-50 transition">
                <div class="w-8 h-8 rounded-xl bg-{{ $cfg['color']??'gray' }}-100 flex items-center justify-center flex-shrink-0">
                    <i class="fas {{ $cfg['icon']??'fa-circle' }} text-{{ $cfg['color']??'gray' }}-500 text-xs"></i>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-bold text-gray-800 font-mono">#{{ $o->order_number }}</p>
                    <p class="text-xs text-gray-400">{{ $o->created_at->format('M d, Y') }}</p>
                </div>
                <div class="text-right flex-shrink-0">
                    <p class="font-black text-sm text-primary">{{ $sym }}{{ number_format($o->grand_total,0) }}</p>
                    <span class="text-[10px] font-bold text-{{ $cfg['color']??'gray' }}-600">{{ $cfg['label']??ucfirst($o->status) }}</span>
                </div>
            </a>
            @empty
            <div class="px-5 py-10 text-center text-gray-400">
                <i class="fas fa-box-open text-3xl text-gray-200 mb-2 block"></i>
                <p class="text-sm">No orders yet. <a href="{{ route('products.index') }}" class="text-accent font-bold hover:underline">Start shopping →</a></p>
            </div>
            @endforelse
        </div>

        {{-- Quick links --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
            @foreach([
                [route('user.orders.index'),      'fa-box',            'My Orders',        'View & track all your orders',          'blue'],
                [route('user.wallet.index'),       'fa-wallet',         'My Wallet',        'View balance & transactions',           'teal'],
                [route('user.referrals.index'),    'fa-users',          'Referrals',        'Earn ₦500 per referral',               'purple'],
                [route('tracking.index'),          'fa-search-location','Track Order',      'Track any order by number',             'orange'],
            ] as [$url,$icon,$title,$desc,$color])
            <a href="{{ $url }}" class="bg-white rounded-2xl shadow-card p-4 hover:shadow-card-hover transition-shadow group block">
                <div class="w-9 h-9 bg-{{ $color }}-100 rounded-xl flex items-center justify-center mb-2.5 group-hover:scale-110 transition-transform">
                    <i class="fas {{ $icon }} text-{{ $color }}-500 text-sm"></i>
                </div>
                <p class="font-bold text-gray-800 text-sm">{{ $title }}</p>
                <p class="text-[11px] text-gray-400 mt-0.5 leading-relaxed">{{ $desc }}</p>
            </a>
            @endforeach
        </div>
    </div>
</div>

{{-- ── TAB 5: Preferences ─────────────────────────────────────────────────── --}}
<div x-show="tab==='settings'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0">
    <div class="space-y-4">
        {{-- Currency & Language --}}
        <div class="bg-white rounded-2xl shadow-card p-6 space-y-4">
            <div class="flex items-center gap-2 pb-3 border-b border-gray-100">
                <div class="w-8 h-8 bg-indigo-100 rounded-xl flex items-center justify-center flex-shrink-0">
                    <i class="fas fa-globe text-indigo-500 text-xs"></i>
                </div>
                <div>
                    <p class="font-bold text-primary text-sm">Language &amp; Currency</p>
                    <p class="text-xs text-gray-400">How prices and content are displayed</p>
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <p class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Display Currency</p>
                    <div class="flex flex-wrap gap-2">
                        @foreach(config('bybhub.display_currencies') as $cur=>$info)
                        <a href="{{ route('currency.switch',$cur) }}"
                           class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl text-xs font-bold border-2 transition
                               {{ session('currency',$user->currency)===$cur ? 'border-primary bg-primary/5 text-primary' : 'border-gray-200 text-gray-500 hover:border-gray-300' }}">
                            {{ $info['flag'] }} {{ $info['symbol'] }} {{ $cur }}
                        </a>
                        @endforeach
                    </div>
                </div>
                <div>
                    <p class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Language</p>
                    <div class="flex gap-2">
                        @foreach(['en'=>['🇬🇧','English'],'fr'=>['🇫🇷','Français']] as $lang=>[$flag,$label])
                        <a href="{{ route('language.switch',$lang) }}"
                           class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs font-bold border-2 transition
                               {{ app()->getLocale()===$lang ? 'border-primary bg-primary/5 text-primary' : 'border-gray-200 text-gray-500 hover:border-gray-300' }}">
                            {{ $flag }} {{ $label }}
                        </a>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        {{-- Notifications --}}
        <div class="bg-white rounded-2xl shadow-card p-6 space-y-4">
            <div class="flex items-center gap-2 pb-3 border-b border-gray-100">
                <div class="w-8 h-8 bg-amber-100 rounded-xl flex items-center justify-center flex-shrink-0">
                    <i class="fas fa-bell text-amber-500 text-xs"></i>
                </div>
                <div>
                    <p class="font-bold text-primary text-sm">Notification Channels</p>
                    <p class="text-xs text-gray-400">How we reach you with order updates</p>
                </div>
            </div>
            <div class="space-y-3">
                @foreach([
                    ['fa-envelope','text-blue-500','bg-blue-50','Email Notifications','Order updates, shipping alerts, promotions','Always On'],
                    ['fa-bell','text-amber-500','bg-amber-50','In-App Notifications','Alerts inside the BYBHUB dashboard','Always On'],
                    ['fab fa-telegram','text-blue-400','bg-blue-50','Telegram Bot',($user->telegramUser?->is_verified ? 'Connected ✓' : 'Not connected yet'),$user->telegramUser?->is_verified ? 'Connected' : 'Setup'],
                ] as [$icon,$color,$bg,$title,$desc,$badge])
                <div class="flex items-center gap-3 p-3 rounded-xl {{ $bg }} border border-white">
                    <i class="{{ $icon }} {{ $color }} text-base flex-shrink-0 w-5 text-center"></i>
                    <div class="flex-1">
                        <p class="font-bold text-gray-800 text-sm">{{ $title }}</p>
                        <p class="text-xs text-gray-500">{{ $desc }}</p>
                    </div>
                    @if($badge==='Setup')
                    <a href="{{ route('user.link-telegram') }}" class="text-[10px] font-bold bg-blue-500 text-white px-2.5 py-1 rounded-lg hover:bg-blue-600 transition flex-shrink-0">
                        Connect
                    </a>
                    @else
                    <span class="text-[10px] font-bold text-green-600 bg-green-100 px-2.5 py-1 rounded-lg flex-shrink-0">{{ $badge }}</span>
                    @endif
                </div>
                @endforeach
            </div>
        </div>

        {{-- Danger zone --}}
        <div class="bg-white rounded-2xl shadow-card p-6 border-l-4 border-red-400">
            <div class="flex items-center gap-2 mb-4">
                <div class="w-8 h-8 bg-red-100 rounded-xl flex items-center justify-center flex-shrink-0">
                    <i class="fas fa-exclamation-triangle text-red-500 text-xs"></i>
                </div>
                <p class="font-bold text-red-700 text-sm">Danger Zone</p>
            </div>
            <div class="flex items-center justify-between flex-wrap gap-3">
                <div>
                    <p class="text-sm font-semibold text-gray-700">Sign out everywhere</p>
                    <p class="text-xs text-gray-400">This will log you out of all active sessions</p>
                </div>
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button class="bg-red-50 hover:bg-red-100 text-red-600 font-bold px-4 py-2 rounded-xl text-sm transition border border-red-200 flex items-center gap-2">
                        <i class="fas fa-sign-out-alt text-xs"></i> Sign Out
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- ── TAB 6: My Store (Vendor only) ─────────────────────────────────────── --}}
@if($user->is_vendor && $vendor)
<div x-show="tab==='vendor'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0">
    <div class="space-y-4">

        {{-- Store Identity Card --}}
        <div class="rounded-3xl overflow-hidden shadow-card"
             style="background:linear-gradient(135deg,#7c2d12,#c2410c,#F47B20)">
            <div class="p-6 flex items-center gap-5 flex-wrap">
                {{-- Store logo / initial --}}
                <div class="flex-shrink-0">
                    @if($vendor->store_logo)
                    <img src="{{ Storage::url($vendor->store_logo) }}" class="w-16 h-16 rounded-2xl object-cover border-2 border-white/30 shadow-xl">
                    @else
                    <div class="w-16 h-16 rounded-2xl bg-white/20 border-2 border-white/30 flex items-center justify-center text-white text-2xl font-black shadow-xl">
                        {{ strtoupper(substr($vendor->store_name,0,1)) }}
                    </div>
                    @endif
                </div>
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 flex-wrap mb-1">
                        <h2 class="font-black text-white text-xl" style="font-family:Poppins,sans-serif">{{ $vendor->store_name }}</h2>
                        <span class="text-[10px] font-bold px-2.5 py-1 rounded-full bg-{{ $vendor->getStatusColor() }}-500 text-white">
                            {{ ucfirst($vendor->status) }}
                        </span>
                        @if($vendor->isKycVerified())
                        <span class="text-[10px] font-bold px-2.5 py-1 rounded-full bg-green-400 text-white flex items-center gap-1">
                            <i class="fas fa-shield-alt text-[9px]"></i> KYC Verified
                        </span>
                        @else
                        <span class="text-[10px] font-bold px-2.5 py-1 rounded-full bg-yellow-400 text-gray-900 flex items-center gap-1">
                            <i class="fas fa-clock text-[9px]"></i> KYC Pending
                        </span>
                        @endif
                    </div>
                    <p class="text-white/70 text-xs">{{ $vendor->business_city }}, {{ $vendor->business_state }}</p>
                    <p class="text-white/50 text-xs mt-0.5">Member since {{ $vendor->created_at->format('M Y') }} · {{ $vendor->products()->where('is_approved',true)->count() }} live products</p>
                </div>
                <div class="flex gap-2 flex-shrink-0 flex-wrap">
                    <a href="{{ route('vendor.dashboard') }}"
                       class="inline-flex items-center gap-2 bg-white text-accent font-black px-4 py-2.5 rounded-xl text-xs hover:bg-orange-50 transition shadow-lg">
                        <i class="fas fa-tachometer-alt"></i> Dashboard
                    </a>
                    <a href="{{ route('store.show', $vendor->store_slug) }}" target="_blank"
                       class="inline-flex items-center gap-2 text-white font-bold px-4 py-2.5 rounded-xl text-xs transition"
                       style="background:rgba(255,255,255,0.2);border:1px solid rgba(255,255,255,0.3)">
                        <i class="fas fa-external-link-alt"></i> View Store
                    </a>
                </div>
            </div>

            {{-- Earnings bar --}}
            <div class="border-t border-white/10 px-6 py-4 grid grid-cols-2 md:grid-cols-4 gap-4">
                @foreach([
                    ['₦'.number_format((float)$vendor->available_balance,0),  'Available',     'fa-wallet',     'Available to withdraw'],
                    ['₦'.number_format((float)$vendor->pending_balance,0),     'In Escrow',     'fa-lock',       'Held until delivery confirmed'],
                    ['₦'.number_format((float)$vendor->total_revenue,0),       'Total Revenue', 'fa-chart-bar',  'All-time earnings'],
                    ['₦'.number_format((float)$vendor->total_withdrawn,0),     'Withdrawn',     'fa-arrow-up',   'Paid out to your bank'],
                ] as [$val,$label,$icon,$tip])
                <div class="text-center">
                    <i class="fas {{ $icon }} text-white/60 text-xs mb-1 block"></i>
                    <p class="font-black text-white text-sm" style="font-family:Poppins,sans-serif">{{ $val }}</p>
                    <p class="text-white/50 text-[10px]">{{ $label }}</p>
                </div>
                @endforeach
            </div>
        </div>

        {{-- Quick actions grid --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
            @foreach([
                [route('vendor.products'),         'fa-box',           'My Products',      'bg-blue-50 text-blue-600',   'Add, edit or remove products'],
                [route('vendor.products.create'),  'fa-plus-circle',   'Add Product',      'bg-green-50 text-green-600', 'List a new product'],
                [route('vendor.orders.index'),     'fa-shopping-bag',  'Customer Orders',  'bg-orange-50 text-orange-600','View & manage orders' . ($pendingVendorOrders ? " ($pendingVendorOrders new)" : '')],
                [route('vendor.analytics'),        'fa-chart-line',    'Analytics',        'bg-purple-50 text-purple-600','Views, sales & conversion'],
            ] as [$url,$icon,$title,$colorClass,$desc])
            <a href="{{ $url }}" class="bg-white rounded-2xl shadow-card p-4 hover:shadow-card-hover transition group block">
                <div class="w-10 h-10 {{ $colorClass }} rounded-2xl flex items-center justify-center mb-2.5 group-hover:scale-110 transition-transform">
                    <i class="fas {{ $icon }} text-sm"></i>
                </div>
                <p class="font-bold text-gray-800 text-sm">{{ $title }}</p>
                <p class="text-[11px] text-gray-400 mt-0.5 leading-relaxed">{{ $desc }}</p>
            </a>
            @endforeach
        </div>

        {{-- Withdrawal request --}}
        @if($vendor->isActive() && $vendor->available_balance > 0)
        <div class="bg-white rounded-2xl shadow-card p-5">
            <h3 class="font-bold text-primary text-sm mb-4 flex items-center gap-2">
                <i class="fas fa-money-bill-wave text-accent text-xs"></i> Request Withdrawal
            </h3>
            <div class="grid grid-cols-2 gap-3 mb-4">
                <div class="bg-teal-50 border border-teal-200 rounded-xl p-3 text-center">
                    <p class="text-xs text-teal-600">Available to Withdraw</p>
                    <p class="font-black text-teal-700 text-xl">₦{{ number_format((float)$vendor->available_balance,0) }}</p>
                </div>
                <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-3 text-center">
                    <p class="text-xs text-yellow-600">In Escrow</p>
                    <p class="font-black text-yellow-700 text-xl">₦{{ number_format((float)$vendor->pending_balance,0) }}</p>
                    <p class="text-[9px] text-yellow-500">Released after delivery confirmed</p>
                </div>
            </div>
            @if(\App\Models\VendorWithdrawal::where('vendor_id',$vendor->id)->where('status','pending')->exists())
            <div class="bg-yellow-50 border border-yellow-200 rounded-xl px-4 py-3 text-xs text-yellow-700 flex items-center gap-2">
                <i class="fas fa-clock text-yellow-500"></i> Your withdrawal is being processed. Funds will arrive in your bank shortly.
            </div>
            @else
            <form action="{{ route('vendor.withdrawal.request') }}" method="POST" class="flex gap-2"
                  data-confirm data-confirm-title="Request Withdrawal"
                  data-confirm-msg="Submit a withdrawal request? Funds will be transferred to your bank account within 24–48 hours."
                  data-confirm-ok="Yes, Request">
                @csrf
                <input type="number" name="amount" placeholder="Amount (min ₦1,000)"
                    min="1000" max="{{ $vendor->available_balance }}" step="100" required
                    class="flex-1 border-2 border-gray-100 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-accent">
                <button class="bg-teal-600 hover:bg-teal-700 text-white font-bold px-5 py-2.5 rounded-xl text-sm transition">
                    Withdraw
                </button>
            </form>
            <p class="text-[10px] text-gray-400 mt-2 flex items-center gap-1.5">
                <i class="fas fa-university text-gray-300"></i>
                Sent to: {{ $vendor->bank_name }} · {{ $vendor->bank_account_number }}
            </p>
            @endif
        </div>
        @endif

        {{-- KYC status --}}
        @if(!$vendor->isKycVerified())
        <div class="bg-amber-50 border-2 border-amber-300 rounded-2xl p-5 flex items-start gap-4">
            <div class="w-10 h-10 bg-amber-100 rounded-xl flex items-center justify-center flex-shrink-0">
                <i class="fas fa-id-card text-amber-500"></i>
            </div>
            <div class="flex-1">
                <p class="font-bold text-amber-800 text-sm">KYC Verification {{ $vendor->kyc_status === 'rejected' ? 'Rejected' : 'Pending' }}</p>
                @if($vendor->kyc_status === 'rejected' && $vendor->kyc_notes)
                <p class="text-xs text-amber-700 mt-1">Reason: {{ $vendor->kyc_notes }}</p>
                <p class="text-xs text-amber-600 mt-1">Please contact support to re-submit your documents.</p>
                @else
                <p class="text-xs text-amber-700 mt-1">Our team is reviewing your identity documents. This usually takes under 24 hours.</p>
                @endif
            </div>
        </div>
        @endif

        {{-- Recent products --}}
        @php $recentVendorProducts = \App\Models\Product::where('vendor_id',$vendor->id)->latest()->take(4)->get(); @endphp
        @if($recentVendorProducts->isNotEmpty())
        <div class="bg-white rounded-2xl shadow-card p-5">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-bold text-primary text-sm flex items-center gap-2"><i class="fas fa-box text-accent text-xs"></i> Recent Products</h3>
                <a href="{{ route('vendor.products') }}" class="text-xs text-primary font-bold hover:underline">View all →</a>
            </div>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                @foreach($recentVendorProducts as $vp)
                <a href="{{ route('vendor.products.edit', $vp) }}" class="group block">
                    <div class="aspect-square rounded-xl overflow-hidden bg-gray-50 mb-1.5">
                        <img src="{{ ($vp->images[0] ?? null) ? Storage::url($vp->images[0]) : asset('images/placeholder.svg') }}"
                             class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                    </div>
                    <p class="text-xs font-semibold text-gray-700 line-clamp-1">{{ $vp->title }}</p>
                    <div class="flex items-center justify-between">
                        <p class="text-xs font-black text-accent">₦{{ number_format($vp->price_ngn,0) }}</p>
                        <span class="text-[9px] font-bold px-1.5 py-0.5 rounded-full {{ $vp->is_approved ? 'bg-green-100 text-green-600' : 'bg-yellow-100 text-yellow-600' }}">
                            {{ $vp->is_approved ? 'Live' : 'Pending' }}
                        </span>
                    </div>
                </a>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Apply if not yet vendor --}}
        @if($vendor->isPending())
        <div class="bg-blue-50 border border-blue-200 rounded-2xl p-5 flex items-start gap-3">
            <i class="fas fa-info-circle text-blue-500 text-lg flex-shrink-0 mt-0.5"></i>
            <div>
                <p class="font-bold text-blue-800 text-sm">Application Under Review</p>
                <p class="text-xs text-blue-700 mt-1">Your vendor application for "{{ $vendor->store_name }}" is being reviewed. You'll be notified by email and in-app once approved.</p>
            </div>
        </div>
        @endif

    </div>
</div>
@elseif($user->is_vendor && !$vendor)
{{-- Has vendor flag but no profile — edge case --}}
<div x-show="tab==='vendor'" class="bg-white rounded-2xl shadow-card p-10 text-center">
    <i class="fas fa-store text-4xl text-gray-200 mb-3 block"></i>
    <p class="font-bold text-gray-600">Vendor profile not found</p>
    <a href="{{ route('vendor.apply') }}" class="mt-3 inline-block bg-accent text-white font-bold px-6 py-2.5 rounded-xl text-sm">Apply Now</a>
</div>
@endif

</div>{{-- end x-data --}}
@endsection
