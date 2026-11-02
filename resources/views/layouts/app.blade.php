<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="csrf-token" content="{{ csrf_token() }}">
<meta name="vapid-public-key" content="{{ config('webpush.vapid.public_key', '') }}">
<title>@yield('title','BYBHUB') — Bringing China Closer to You</title>
<meta name="description" content="@yield('meta_description','Shop from 1688, Taobao & Alibaba. Delivered to Nigeria, Ghana & Niger.')">
<link rel="canonical" href="{{ url()->current() }}">

{{-- ── Open Graph / Social Share (WhatsApp, Facebook, Twitter) ── --}}
<meta property="og:site_name"   content="BYBHUB">
<meta property="og:type"        content="@yield('og_type','website')">
<meta property="og:url"         content="{{ url()->current() }}">
<meta property="og:title"       content="@yield('og_title', 'BYBHUB — Bringing China Closer to You')">
<meta property="og:description" content="@yield('og_description', 'Shop from 1688, Taobao & Alibaba. Delivered to Nigeria, Ghana & Niger.')">
<meta property="og:image"       content="@yield('og_image', asset('images/logo.jpg'))">
<meta property="og:image:width"  content="1200">
<meta property="og:image:height" content="630">
<meta name="twitter:card"        content="summary_large_image">
<meta name="twitter:title"       content="@yield('og_title', 'BYBHUB — Bringing China Closer to You')">
<meta name="twitter:description" content="@yield('og_description', 'Shop from 1688, Taobao & Alibaba.')">
<meta name="twitter:image"       content="@yield('og_image', asset('images/logo.jpg'))">

<link rel="icon" type="image/png" href="{{ asset('images/icon-192.png') }}">
<link rel="shortcut icon" href="{{ asset('images/icon-192.png') }}">
<link rel="apple-touch-icon" href="{{ asset('images/apple-touch-icon.png') }}">
<link rel="apple-touch-icon" sizes="152x152" href="{{ asset('images/apple-touch-icon.png') }}">
<link rel="apple-touch-icon" sizes="180x180" href="{{ asset('images/apple-touch-icon.png') }}">
{{-- Preload SVG icon so browsers can pick it up quickly for bookmarks/tabs --}}
<link rel="icon" type="image/svg+xml" href="{{ asset('images/icon.svg') }}">
<link rel="manifest" href="{{ asset('manifest.json') }}">
<meta name="mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
<meta name="apple-mobile-web-app-title" content="BYBHUB">
<meta name="theme-color" content="#1A3A6B">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&family=Poppins:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
<script src="https://cdn.tailwindcss.com"></script>
<script>
tailwind.config = {
  theme: {
    extend: {
      fontFamily: {
        sans: ['Inter','ui-sans-serif','system-ui'],
        display: ['Poppins','Inter','ui-sans-serif']
      },
      colors: {
        primary: {
          DEFAULT:'#1A3A6B', 50:'#EFF4FF', 100:'#DBE7FF', 200:'#BFCFFF',
          300:'#8FA8F5', 400:'#5C7EEF', 500:'#3560DF', 600:'#2451A3',
          700:'#1A3A6B', 800:'#112548', 900:'#0A1628', dark:'#112548', light:'#2451A3'
        },
        accent: {
          DEFAULT:'#F47B20', 50:'#FFF6EE', 100:'#FEECDA', 200:'#FDD4A8',
          300:'#FBB570', 400:'#F89347', 500:'#F47B20', 600:'#D4660E',
          700:'#B0520B', dark:'#D4660E', light:'#F89347'
        },
      },
      boxShadow: {
        'card': '0 2px 16px 0 rgba(26,58,107,0.08)',
        'card-hover': '0 8px 32px 0 rgba(26,58,107,0.16)',
        'glow-accent': '0 4px 24px rgba(244,123,32,0.35)',
        'glow-primary': '0 4px 24px rgba(26,58,107,0.25)',
      },
    }
  }
}
</script>
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
*{-webkit-font-smoothing:antialiased;-moz-osx-font-smoothing:grayscale;box-sizing:border-box}
body{font-family:'Inter',sans-serif;background:#F0F4F8;overflow-x:hidden}
h1,h2,h3,.font-display{font-family:'Poppins',sans-serif}
.glass{background:rgba(255,255,255,0.12);backdrop-filter:blur(14px);-webkit-backdrop-filter:blur(14px);border:1px solid rgba(255,255,255,0.22)}
.card-hover{transition:transform 0.22s cubic-bezier(.4,0,.2,1),box-shadow 0.22s cubic-bezier(.4,0,.2,1)}
.card-hover:hover{transform:translateY(-5px);box-shadow:0 16px 40px -8px rgba(26,58,107,0.18)}
.btn-glow{box-shadow:0 4px 20px rgba(244,123,32,0.38)}
.btn-glow:hover{box-shadow:0 6px 28px rgba(244,123,32,0.52);transform:translateY(-1px)}
.gradient-text{background:linear-gradient(135deg,#1A3A6B 0%,#F47B20 100%);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text}
.section-reveal{opacity:0;transform:translateY(22px);transition:opacity 0.55s ease,transform 0.55s ease}
.section-reveal.visible{opacity:1;transform:translateY(0)}
.badge-pill{display:inline-flex;align-items:center;gap:0.25rem;padding:0.25rem 0.75rem;border-radius:9999px;font-size:0.7rem;font-weight:700;text-transform:uppercase;letter-spacing:0.05em}
::-webkit-scrollbar{width:5px;height:5px}
::-webkit-scrollbar-track{background:#f1f5f9}
::-webkit-scrollbar-thumb{background:#1A3A6B;border-radius:3px}
@keyframes fadeUp{from{opacity:0;transform:translateY(20px)}to{opacity:1;transform:translateY(0)}}
@keyframes fadeIn{from{opacity:0}to{opacity:1}}
@keyframes float{0%,100%{transform:translateY(0)}50%{transform:translateY(-10px)}}
@keyframes float2{0%,100%{transform:translateY(0) rotate(0deg)}50%{transform:translateY(-14px) rotate(5deg)}}
@keyframes shimmer{0%{background-position:-200% 0}100%{background-position:200% 0}}
@keyframes pulse-dot{0%,100%{opacity:1;transform:scale(1)}50%{opacity:0.7;transform:scale(1.3)}}
@keyframes bounce-soft{0%,100%{transform:translateY(0)}50%{transform:translateY(-6px)}}
@keyframes spin-slow{from{transform:rotate(0deg)}to{transform:rotate(360deg)}}
@keyframes counter{from{opacity:0;transform:translateY(12px)}to{opacity:1;transform:translateY(0)}}
.animate-fade-up{animation:fadeUp 0.55s ease forwards}
.animate-fade-in{animation:fadeIn 0.4s ease forwards}
.animate-float{animation:float 3.5s ease-in-out infinite}
.animate-float2{animation:float2 4s ease-in-out infinite}
.animate-bounce-soft{animation:bounce-soft 2s ease-in-out infinite}
.skeleton{background:linear-gradient(90deg,#f0f0f0 25%,#e0e0e0 50%,#f0f0f0 75%);background-size:200% 100%;animation:shimmer 1.5s infinite}

/* ── Reusable badge counter ── */
/* .byb-badge: a round pill badge used on nav icons, bottom tabs, etc.       */
/* Usage: <span class="byb-badge">3</span>  or  <span class="byb-badge byb-badge--dot"></span> */
.byb-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 18px;
    height: 18px;
    padding: 0 4px;
    border-radius: 9999px;
    background: #EF4444;      /* red-500 */
    color: #fff;
    font-size: 10px;
    font-weight: 700;
    line-height: 1;
    font-family: 'Inter', ui-sans-serif, system-ui;
    border: 2px solid #fff;
    box-shadow: 0 1px 4px rgba(239,68,68,0.35);
    pointer-events: none;
    user-select: none;
}
.byb-badge--dot {
    min-width: 10px;
    height: 10px;
    padding: 0;
    border-width: 1.5px;
}
.byb-badge--accent {
    background: #F47B20;      /* accent orange — for cart */
}
.byb-badge--sm {
    min-width: 16px;
    height: 16px;
    font-size: 9px;
    border-width: 1.5px;
}
@keyframes badge-pop {
    0%   { transform: scale(0.7); opacity: 0; }
    60%  { transform: scale(1.25); }
    100% { transform: scale(1); opacity: 1; }
}
.byb-badge-enter { animation: badge-pop 0.25s cubic-bezier(.4,0,.2,1) forwards; }
</style>
@stack('styles')

{{-- ── BYBHUB Live Badge Store ──────────────────────────────────────────────
     Initialised before Alpine boots (defer on Alpine means it reads x-data
     at DOMContentLoaded; document.addEventListener fires before that when
     placed here).  This block is only emitted for authenticated users so
     the poll never fires for guests.
--}}
@auth
<script>
// Seed the initial server-side counts so the first render is instant.
// These are refreshed immediately by the poll / SW message in background.
window._bybInitCounts = {
    unread:       {{ (int) auth()->user()->unreadNotificationsCount() }},
    cart:         {{ (int) \App\Models\CartItem::where('user_id', auth()->id())->count() }},
    // Active (in-progress) orders — excludes terminal states.
    activeOrders: {{ (int) \App\Models\Order::where('user_id', auth()->id())
        ->whereNotIn('status', ['delivered','cancelled','refunded',''])
        ->whereNotIn('local_order_status', ['delivered','completed','cancelled','refunded','disputed',''])
        ->count() }},
};
</script>
@endauth
<body class="text-gray-800 antialiased">

@auth
{{-- ── Live Badge Alpine Store ─────────────────────────────────────────────
     Registers `Alpine.store('badge', { unread, cart, activeOrders })`
     which all badge UI in this layout reads from.

     Behaviour:
     • Seeded instantly from server-side counts (no flash of empty badge).
     • Polls /api/notifications/unread-count every 3 minutes while tab is visible.
     • Listens for service worker postMessage to get push-triggered updates instantly.
     • Updates document.title with "(N) BYBHUB" as an iOS Safari fallback — iOS
       does not support the Web App Badging API.
     • Clears the title count when the user visits the notifications page.
--}}
<script>
document.addEventListener('alpine:init', function () {
    const init = window._bybInitCounts || { unread: 0, cart: 0, activeOrders: 0 };

    Alpine.store('badge', {
        unread:       init.unread,
        cart:         init.cart,
        activeOrders: init.activeOrders,

        // Apply counts to OS badge + document title
        _apply(unread) {
            this.unread = unread;
            // iOS Safari title fallback — shows "(2) BYBHUB" in the tab
            const base = 'BYBHUB — Bringing China Closer to You';
            document.title = unread > 0
                ? `(${unread > 99 ? '99+' : unread}) ${document.title.replace(/^\(\d+\+?\)\s*/, '')}`
                : document.title.replace(/^\(\d+\+?\)\s*/, '');
        },

        // Poll /api/notifications/unread-count (called on load + every 3 min)
        async poll() {
            try {
                const r = await fetch('/api/notifications/unread-count', { credentials: 'include' });
                if (r.ok) {
                    const { count = 0 } = await r.json();
                    this._apply(count);
                }
            } catch (_) { /* offline — keep stale count */ }
        },

        // Called when user visits the notifications page to clear counts
        clearUnread() {
            this._apply(0);
            // Tell the SW to refresh the OS badge too
            if (navigator.serviceWorker?.controller) {
                navigator.serviceWorker.controller.postMessage({ type: 'BYBHUB_REFRESH_BADGE' });
            }
        },

        init() {
            // Apply the seeded count to title immediately
            this._apply(init.unread);

            // Poll on tab focus + every 3 minutes while visible
            this.poll();
            setInterval(() => {
                if (!document.hidden) this.poll();
            }, 3 * 60 * 1000);

            // React to SW push messages (works on Android/Desktop; iOS SW is limited)
            if ('serviceWorker' in navigator) {
                navigator.serviceWorker.addEventListener('message', (event) => {
                    if (event.data?.type === 'BYBHUB_UNREAD_COUNT') {
                        this._apply(event.data.count ?? 0);
                    }
                });
            }

            // Re-poll when tab becomes visible again (e.g. user switches back)
            document.addEventListener('visibilitychange', () => {
                if (!document.hidden) this.poll();
            });
        },
    });
});
</script>
@endauth

{{-- Impersonation banner --}}
@if(session('admin_impersonating'))
<div class="bg-amber-500 text-white text-xs py-2 px-4 flex items-center justify-between sticky top-0 z-[60]">
    <span class="flex items-center gap-2">
        <i class="fas fa-user-secret"></i>
        You are browsing as <strong>{{ auth()->user()->name }}</strong>. Changes are real.
    </span>
    <a href="{{ url('/dashboard/stop-impersonation') }}"
       class="bg-white text-amber-600 font-bold px-3 py-1 rounded-lg text-xs hover:bg-amber-50 transition">
        Return to Admin
    </a>
</div>
@endif

{{-- TOP ANNOUNCEMENT BAR --}}
@php
    $announcement        = \App\Models\Setting::get('announcement_text', '');
    $announcementOn      = \App\Models\Setting::get('announcement_enabled', '0') === '1';
    $vendorOpen          = \App\Models\Setting::get('vendor_marketplace_open', '0') === '1';
    $comingSoonMode      = \App\Models\Setting::get('coming_soon_mode', '0') === '1';
    $launchDate          = \App\Models\Setting::get('launch_datetime', '');
    $launchTitle         = \App\Models\Setting::get('coming_soon_title', '🚀 Something Amazing is Coming');
    $launchMessage       = \App\Models\Setting::get('coming_soon_message', "We're launching very soon. Stay tuned!");
    $launchProgress      = (int) \App\Models\Setting::get('launch_progress', 75);
    $socialInstagram     = \App\Models\Setting::get('social_instagram', '');
    $socialWhatsapp      = \App\Models\Setting::get('social_whatsapp_group', '');
    $socialTelegram      = \App\Models\Setting::get('social_telegram_channel', '');
    $whatsappSupport     = \App\Models\Setting::get('whatsapp_support_number', \App\Models\Setting::get('contact_whatsapp', ''));
    $isAdmin             = auth()->check() && auth()->user()->is_admin;
    // Popup settings
    $popupEnabled        = \App\Models\Setting::get('popup_enabled', '0') === '1';
    $popupTitle          = \App\Models\Setting::get('popup_title', '');
    $popupMessage        = \App\Models\Setting::get('popup_message', '');
    $popupImage          = \App\Models\Setting::get('popup_image', '');
    $popupButtonText     = \App\Models\Setting::get('popup_button_text', 'Shop Now');
    $popupButtonUrl      = \App\Models\Setting::get('popup_button_url', '');
    $popupShowBuyNow     = \App\Models\Setting::get('popup_show_buy_now', '0') === '1';
    $popupProductId      = \App\Models\Setting::get('popup_product_id', '');
    $popupFrequency      = \App\Models\Setting::get('popup_frequency', 'once'); // once | session | always
    $popupDelay          = (int) \App\Models\Setting::get('popup_delay_seconds', 2);
@endphp

{{-- ══════════════════════════════════════════════════════════════════════════ --}}
{{-- COMING SOON — Full page overlay. Admin never sees it.                    --}}
{{-- Hidden admin login: visit /login?admin_bypass=1 to reach login page      --}}
{{-- ══════════════════════════════════════════════════════════════════════════ --}}
@if($comingSoonMode && !$isAdmin && request()->path() !== 'login' && !str_starts_with(request()->path(), 'admin'))
<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>{{ $launchTitle }} — BYBHUB</title>
<link rel="icon" type="image/jpeg" href="{{ asset('images/logo.jpg') }}">
<meta name="theme-color" content="#1A3A6B">
<script src="https://cdn.tailwindcss.com"></script>
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
@keyframes float{0%,100%{transform:translateY(0)}50%{transform:translateY(-12px)}}
@keyframes lidOpen{0%{transform:rotate(0) translateY(0)}100%{transform:rotate(-22deg) translateY(-10px)}}
@keyframes confettiFall{0%{transform:translateY(-20px) rotate(0);opacity:1}100%{transform:translateY(100vh) rotate(720deg);opacity:0}}
@keyframes shimmer{0%{background-position:-200% 0}100%{background-position:200% 0}}
@keyframes pulse{0%,100%{opacity:1;transform:scale(1)}50%{opacity:.6;transform:scale(1.15)}}
*{box-sizing:border-box}
body{margin:0;font-family:Inter,sans-serif;overflow-x:hidden;overflow-y:auto;min-height:100vh}
</style>
</head>
<body>
<div class="min-h-screen flex flex-col items-center justify-start md:justify-center px-4 py-8 overflow-x-hidden"
     style="background:linear-gradient(135deg,#0A1628 0%,#1A3A6B 55%,#2451A3 80%,#F47B20 100%)"
     x-data="{
        show: false, boxOpen: false, submitted: false, submitting: false, msg: '',
        days:0, hours:0, mins:0, secs:0, expired:false,
        confetti: [],
        progress: {{ $launchProgress }},
        launchDate: '{{ $launchDate }}',
        init(){
            setTimeout(()=>{ this.show=true; }, 200);
            setTimeout(()=>{ this.boxOpen=true; this.launchConfetti(); }, 900);
            if(this.launchDate){ this.tick(); setInterval(()=>this.tick(), 1000); }
        },
        tick(){
            const diff = new Date(this.launchDate).getTime() - Date.now();
            if(diff<=0){ this.expired=true; return; }
            this.days  = Math.floor(diff/86400000);
            this.hours = Math.floor((diff%86400000)/3600000);
            this.mins  = Math.floor((diff%3600000)/60000);
            this.secs  = Math.floor((diff%60000)/1000);
        },
        launchConfetti(){
            const colors=['#F47B20','#ffffff','#10B981','#F59E0B','#8B5CF6','#60A5FA'];
            this.confetti = Array.from({length:55},(_,i)=>({
                id:i,left:Math.random()*100,delay:Math.random()*2.5,
                dur:2+Math.random()*3,color:colors[Math.floor(Math.random()*colors.length)],
                size:5+Math.random()*8,rotate:Math.random()*360
            }));
            setTimeout(()=>this.confetti=[],6000);
        },
        async joinList(e){
            this.submitting=true;
            const fd=new FormData(e.target);
            const r=await fetch('{{ route('waitlist.store') }}',{
                method:'POST',
                headers:{'X-CSRF-TOKEN':'{{ csrf_token() }}','Accept':'application/json'},
                body:fd
            });
            const d=await r.json();
            this.submitting=false; this.submitted=true; this.msg=d.message;
        }
     }">

    {{-- Confetti --}}
    <div class="fixed inset-0 pointer-events-none overflow-hidden z-50">
        <template x-for="c in confetti" :key="c.id">
            <div class="absolute top-0 rounded-sm"
                 :style="`left:${c.left}%;width:${c.size}px;height:${c.size}px;background:${c.color};animation:confettiFall ${c.dur}s ${c.delay}s ease-in forwards;transform:rotate(${c.rotate}deg)`"></div>
        </template>
    </div>

    {{-- Floating bg particles --}}
    <div class="absolute inset-0 pointer-events-none overflow-hidden">
        @for($i=0;$i<10;$i++)
        <div class="absolute rounded-full bg-white/5"
             style="width:{{ rand(60,180) }}px;height:{{ rand(60,180) }}px;top:{{ rand(-10,90) }}%;left:{{ rand(0,100) }}%;animation:float {{ rand(4,8) }}s ease-in-out infinite {{ $i*0.5 }}s alternate"></div>
        @endfor
    </div>

    <div class="relative max-w-3xl w-full mx-auto py-6 md:py-10"
         x-show="show"
         x-transition:enter="transition ease-out duration-700"
         x-transition:enter-start="opacity-0 translate-y-10"
         x-transition:enter-end="opacity-100 translate-y-0">

        {{-- Logo --}}
        <div class="text-center mb-8">
            <img src="{{ asset('images/logo.jpg') }}" alt="BYBHUB"
                 class="h-16 mx-auto rounded-2xl shadow-2xl bg-white p-1.5 mb-4">
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 items-start">

            {{-- LEFT: animated box + title + BIG countdown + social --}}
            <div class="text-white text-center md:text-left">

                {{-- Animated gift box --}}
                <div class="flex justify-center md:justify-start mb-5">
                    <div class="relative" style="width:90px;height:100px">
                        <div class="absolute bottom-0 left-0 right-0 h-14 rounded-2xl shadow-2xl flex items-end justify-center pb-2"
                             style="background:linear-gradient(135deg,#F47B20,#D4660E)">
                            <div class="w-full h-3 absolute top-0 rounded-t-2xl" style="background:rgba(0,0,0,0.15)"></div>
                            <span class="text-white text-[9px] font-black tracking-widest">BYBHUB</span>
                        </div>
                        <div class="absolute left-0 right-0 h-8 rounded-t-xl shadow-xl transition-all duration-700"
                             style="background:linear-gradient(135deg,#D4660E,#B0520B)"
                             :style="boxOpen ? 'top:-22px;transform:rotate(-22deg)' : 'top:24px'">
                            <div class="absolute left-1/2 -translate-x-1/2 top-0 bottom-0 w-4" style="background:rgba(255,255,255,0.2)"></div>
                            <div class="absolute -top-3 left-1/2 -translate-x-1/2 text-lg" x-show="boxOpen" x-transition>🎁</div>
                        </div>
                        <div class="absolute -top-7 left-1/2 -translate-x-1/2 text-lg whitespace-nowrap" x-show="boxOpen" x-transition.duration.500ms>✨🎉✨</div>
                    </div>
                </div>

                <span class="inline-flex items-center gap-2 text-[11px] font-bold uppercase tracking-widest bg-white/10 border border-white/20 px-3 py-1 rounded-full mb-3">
                    <span class="w-1.5 h-1.5 bg-green-400 rounded-full" style="animation:pulse 1.5s ease-in-out infinite"></span>
                    Coming Soon
                </span>
                <h1 class="text-2xl md:text-3xl font-black mb-2 leading-tight" style="font-family:Poppins,sans-serif">
                    🇨🇳 China Is About to Get Much Closer.
                </h1>
                <p class="text-white/80 text-sm leading-relaxed mb-1">
                    Source products from 1688, Taobao &amp; Alibaba. Copy the product link, paste it into BYBHUB, and let us handle the procurement and shipping.
                </p>

                {{-- ── BIG COUNTDOWN (the hero element) ──────────────────── --}}
                @if($launchDate)
                <div x-show="!expired" class="mb-6">
                    <p class="text-white/50 text-xs uppercase tracking-widest mb-3 text-center md:text-left">Launching in</p>
                    <div class="flex gap-2 md:gap-3 justify-center md:justify-start">
                        @foreach(['days'=>'Days','hours'=>'Hours','mins'=>'Min','secs'=>'Sec'] as $var=>$label)
                        <div class="flex flex-col items-center">
                            <div class="rounded-xl md:rounded-2xl flex items-center justify-center shadow-2xl relative overflow-hidden"
                                 style="width:clamp(52px,15vw,72px);height:clamp(52px,15vw,72px);background:rgba(255,255,255,0.12);border:1px solid rgba(255,255,255,0.25)">
                                <div class="absolute inset-0 opacity-20" style="background:linear-gradient(135deg,rgba(255,255,255,0.3),transparent 60%);pointer-events:none"></div>
                                <p class="font-black text-white leading-none relative"
                                   x-text="String({{ $var }}).padStart(2,'0')"
                                   style="font-family:Poppins,monospace;text-shadow:0 2px 10px rgba(0,0,0,0.3);font-size:clamp(20px,6vw,32px)"></p>
                            </div>
                            <p class="text-white/50 text-[9px] md:text-[10px] uppercase tracking-wider mt-1.5">{{ $label }}</p>
                        </div>
                        @if(!$loop->last)
                        <div class="flex items-center pb-5">
                            <span class="text-white/40 font-black" style="font-size:clamp(18px,5vw,26px);animation:pulse 1s ease-in-out infinite">:</span>
                        </div>
                        @endif
                        @endforeach
                    </div>
                    @php
                        // Format launch date in Africa/Lagos timezone for display
                        $displayDate = $launchDate
                            ? \Carbon\Carbon::parse($launchDate)->setTimezone('Africa/Lagos')->format('D, M d Y · h:i A') . ' Lagos Time'
                            : '';
                    @endphp
                    @if($displayDate)
                    <p class="text-white/40 text-[11px] mt-3 text-center md:text-left flex items-center justify-center md:justify-start gap-1.5">
                        <i class="fas fa-calendar-alt text-accent text-[10px]"></i>
                        {{ $displayDate }}
                    </p>
                    @endif
                </div>
                {{-- When expired --}}
                <div x-show="expired" x-transition class="mb-6 text-center md:text-left">
                    <div class="inline-flex items-center gap-2 bg-green-500/20 border border-green-400/40 text-green-300 font-bold text-sm px-4 py-2 rounded-full">
                        <span class="w-2 h-2 bg-green-400 rounded-full" style="animation:pulse 1s ease-in-out infinite"></span>
                        We are LIVE! 🚀 Refresh the page.
                    </div>
                </div>
                @else
                <div class="mb-6 bg-white/10 border border-white/20 rounded-2xl p-4 text-center md:text-left">
                    <p class="text-white/60 text-sm">Launch date not set yet. Stay tuned!</p>
                </div>
                @endif

                {{-- Social links --}}
                <div class="flex gap-2 justify-center md:justify-start flex-wrap">
                    @if($socialInstagram)
                    <a href="{{ $socialInstagram }}" target="_blank" class="flex items-center gap-1.5 text-[11px] font-bold text-white/70 hover:text-white px-3 py-1.5 rounded-xl transition" style="background:rgba(255,255,255,0.08);border:1px solid rgba(255,255,255,0.12)">
                        <i class="fab fa-instagram text-pink-400"></i> Instagram
                    </a>
                    @endif
                    @if($socialWhatsapp)
                    <a href="{{ $socialWhatsapp }}" target="_blank" class="flex items-center gap-1.5 text-[11px] font-bold text-white/70 hover:text-white px-3 py-1.5 rounded-xl transition" style="background:rgba(255,255,255,0.08);border:1px solid rgba(255,255,255,0.12)">
                        <i class="fab fa-whatsapp text-green-400"></i> WhatsApp
                    </a>
                    @endif
                    @if($socialTelegram)
                    <a href="{{ $socialTelegram }}" target="_blank" class="flex items-center gap-1.5 text-[11px] font-bold text-white/70 hover:text-white px-3 py-1.5 rounded-xl transition" style="background:rgba(255,255,255,0.08);border:1px solid rgba(255,255,255,0.12)">
                        <i class="fab fa-telegram text-blue-400"></i> Telegram
                    </a>
                    @endif
                </div>
            </div>

            {{-- RIGHT: waitlist form + what's coming --}}
            <div class="rounded-3xl p-6" style="background:rgba(255,255,255,0.08);border:1px solid rgba(255,255,255,0.15);backdrop-filter:blur(12px)">

                {{-- What's coming --}}
                <p class="text-white/70 text-xs uppercase tracking-wider font-bold mb-3">What's Coming</p>
                <div class="space-y-2 mb-5">
                    @foreach([
                        ['🇨🇳','China Sourcing','Order from 1688, Taobao & Alibaba'],
                        ['🛍️','Local Marketplace','Buy from verified Nigerian vendors'],
                        ['🚚','China-to-Doorstep Shipping','We help move your goods from China to you'],
                    ] as [$icon,$t,$d])
                    <div class="flex items-center gap-2.5 rounded-xl p-2.5" style="background:rgba(255,255,255,0.06)">
                        <span class="text-xl flex-shrink-0">{{ $icon }}</span>
                        <div><p class="text-white text-xs font-bold">{{ $t }}</p><p class="text-white/45 text-[10px]">{{ $d }}</p></div>
                    </div>
                    @endforeach
                </div>

                {{-- Success state --}}
                <div x-show="submitted" x-transition.duration.500ms class="text-center py-6">
                    <div class="text-5xl mb-3" style="animation:float 2s ease-in-out infinite">🎉</div>
                    <p class="text-green-300 font-black text-base mb-1">You're on the list!</p>
                    <p class="text-white/70 text-sm leading-relaxed" x-text="msg"></p>
                    <div class="mt-4 rounded-2xl p-4 text-left space-y-2" style="background:rgba(16,185,129,0.12);border:1px solid rgba(16,185,129,0.25)">
                        <div class="flex items-start gap-2.5 text-xs text-white/70">
                            <span class="text-green-400 mt-0.5 flex-shrink-0">✅</span>
                            <span>We'll notify you by email the moment BYBHUB goes live.</span>
                        </div>
                        <div class="flex items-start gap-2.5 text-xs text-white/70">
                            <span class="text-green-400 mt-0.5 flex-shrink-0">📧</span>
                            <span>Watch for an email from <strong class="text-white/80">noreply@bybimport.com</strong></span>
                        </div>
                        <div class="flex items-start gap-2.5 text-xs text-white/70">
                            <span class="text-green-400 mt-0.5 flex-shrink-0">🚫</span>
                            <span>No spam. We'll only contact you for the launch announcement.</span>
                        </div>
                    </div>
                </div>

                {{-- Waitlist form --}}
                <div x-show="!submitted">
                    <p class="text-white font-black text-sm mb-0.5">Be Among the First to Use BYBHUB 🚀</p>
                    <p class="text-white/45 text-xs mb-3">
                        Join the people already waiting. We'll notify you when BYBHUB goes live.
                    </p>
                    <form @submit.prevent="joinList($event)" class="space-y-2.5">
                        <input type="hidden" name="source" value="coming_soon_page">
                        <input type="text" name="name" placeholder="Your name (optional)"
                            class="w-full rounded-xl px-4 py-2.5 text-sm text-gray-800 focus:outline-none focus:ring-2 focus:ring-accent/40"
                            style="background:rgba(255,255,255,0.92)">
                        <input type="email" name="email" required placeholder="your@email.com"
                            class="w-full rounded-xl px-4 py-2.5 text-sm text-gray-800 focus:outline-none focus:ring-2 focus:ring-accent/40"
                            style="background:rgba(255,255,255,0.92)">
                        <div class="flex gap-1.5">
                            @foreach(['buyer'=>'🛍️ Buyer','vendor'=>'🏪 Vendor','both'=>'👥 Both'] as $val=>$label)
                            <label class="flex-1 text-center cursor-pointer">
                                <input type="radio" name="interest" value="{{ $val }}" class="sr-only peer" {{ $val==='buyer'?'checked':'' }}>
                                <div class="rounded-xl py-2 text-[11px] font-bold transition border peer-checked:border-accent peer-checked:text-white peer-checked:bg-accent/30 border-white/20 text-white/55 hover:border-white/40">{{ $label }}</div>
                            </label>
                            @endforeach
                        </div>
                        <button type="submit" :disabled="submitting"
                            class="w-full font-bold py-3 rounded-xl text-sm transition flex items-center justify-center gap-2"
                            style="background:linear-gradient(135deg,#F47B20,#D4660E);color:white;box-shadow:0 4px 20px rgba(244,123,32,0.4)">
                            <i class="fas" :class="submitting?'fa-spinner fa-spin':'fa-paper-plane'"></i>
                            <span x-text="submitting?'Joining...':'Notify Me at Launch 🚀'"></span>
                        </button>
                        <p class="text-white/30 text-[10px] text-center">We'll only email you to notify you when we launch. No spam.</p>
                    </form>
                </div>

                {{-- Admin access link — subtle but findable --}}
                <div class="mt-5 pt-4 border-t border-white/10 flex items-center justify-between">
                    <p class="text-white/20 text-[10px]">BYBHUB © {{ date('Y') }}</p>
                    <a href="{{ route('login') }}"
                       class="text-white/20 hover:text-white/70 transition text-[10px] flex items-center gap-1"
                       title="Admin Login">
                        <i class="fas fa-lock text-[9px]"></i> Admin
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
@php exit(); @endphp
@endif
{{-- EMAIL VERIFICATION BANNER — soft nudge, never blocks --}}
@auth
@if(!auth()->user()->hasVerifiedEmail() && !auth()->user()->is_admin)
<div class="bg-amber-500 text-white text-xs py-2 px-4 flex items-center justify-between gap-3 flex-wrap"
     x-data="{ show: true }" x-show="show">
    <span class="flex items-center gap-2 flex-wrap">
        <i class="fas fa-envelope text-amber-200"></i>
        <span>Please verify your email address to secure your account and receive order updates.</span>
        <form method="POST" action="{{ route('verification.send') }}" class="inline">
            @csrf
            <button type="submit" class="underline font-bold hover:text-amber-100 transition">Resend verification email</button>
        </form>
    </span>
    <button @click="show=false" class="text-amber-200 hover:text-white flex-shrink-0">
        <i class="fas fa-times text-xs"></i>
    </button>
</div>
@endif
@endauth

{{-- DYNAMIC ANNOUNCEMENT BAR --}}
@php $announcementKey = 'bybhub_bar_' . md5($announcement . ($announcementOn ? '1' : '0')); @endphp
<div class="bg-gradient-to-r from-primary-900 to-primary-800 text-white text-xs py-2 px-4 flex items-center justify-between" id="topBar"
     x-data="{ show: true }"
     x-init="show = localStorage.getItem('{{ $announcementKey }}') !== '1'"
     x-show="show"
     x-transition:leave="transition duration-300"
     x-transition:leave-start="opacity-100 max-h-10"
     x-transition:leave-end="opacity-0 max-h-0 overflow-hidden">
    <div class="flex items-center gap-2">
        <span class="w-1.5 h-1.5 bg-green-400 rounded-full animate-pulse inline-block"></span>
        @if($announcementOn && $announcement)
        <span class="font-medium">{{ $announcement }}</span>
        @else
        <span class="hidden md:block font-medium">🇨🇳 Sourcing directly from China · Delivered to West Africa · Fast Shipping Available</span>
        <span class="md:hidden font-medium">🇨🇳 China → West Africa</span>
        @endif
    </div>
    <div class="flex items-center gap-4">
        <div class="flex items-center gap-1.5 text-white/80">
            <a href="{{ route('language.switch','en') }}" class="hover:text-accent transition font-semibold {{ app()->getLocale()=='en'?'text-accent':'' }}">EN</a>
            <span class="opacity-30 text-xs">|</span>
            <a href="{{ route('language.switch','fr') }}" class="hover:text-accent transition font-semibold {{ app()->getLocale()=='fr'?'text-accent':'' }}">FR</a>
        </div>
        <div class="flex items-center gap-2 text-white/80">
            @foreach(['NGN'=>'₦','GHS'=>'₵','XOF'=>'Fr','USD'=>'$'] as $cur=>$sym)
            <a href="{{ route('currency.switch',$cur) }}" class="hover:text-accent transition text-xs font-bold {{ session('currency')==$cur?'text-accent':'text-white/70' }}">{{ $sym }}</a>
            @endforeach
        </div>
        <button @click="show=false; localStorage.setItem('{{ $announcementKey }}','1')" class="opacity-50 hover:opacity-100 transition ml-1">
            <i class="fas fa-times text-xs"></i>
        </button>
    </div>
</div>

{{-- MAIN NAVBAR --}}
<nav class="bg-white shadow-sm sticky top-0 z-50 border-b border-gray-100" x-data="{ mob: false, search: false }">
    <div class="max-w-7xl mx-auto px-4 lg:px-6">
        <div class="flex items-center h-[56px] lg:h-[70px] gap-4">

            {{-- Logo --}}
            <a href="{{ route('home') }}" class="flex-shrink-0">
                <img src="{{ asset('images/logo.jpg') }}" alt="BYBHUB"
                     class="h-9 lg:h-12 w-auto"
                     style="max-width:140px;object-fit:contain;background:white;border-radius:12px;padding:4px 8px;display:block;">
            </a>

            {{-- Smart Search Bar (center, desktop) --}}
            <div class="hidden lg:flex flex-1 justify-center px-4" x-data="{ q: '', isUrl: false }" x-init="$watch('q', v => isUrl = v.startsWith('http'))">
                <div class="relative w-full max-w-2xl flex items-center">
                    <i class="fas absolute left-4 top-1/2 -translate-y-1/2 text-sm z-10 transition-colors pointer-events-none"
                       :class="isUrl ? 'fa-link text-accent' : 'fa-search text-gray-400'"></i>
                    <input type="text" x-model="q"
                        @keydown.enter.prevent="if(isUrl){ @auth $refs.urlForm.submit() @else window.location='{{ route('register') }}' @endauth }else{ $refs.srchForm.submit() }"
                        placeholder="Search products, or paste 1688 / Taobao / Alibaba link..."
                        class="w-full pl-11 pr-28 py-3 bg-gray-50 border-2 border-gray-200 rounded-2xl text-sm focus:outline-none focus:border-primary focus:bg-white transition">
                    <span x-show="isUrl" x-cloak class="absolute right-[88px] top-1/2 -translate-y-1/2 text-[10px] bg-accent text-white px-2 py-0.5 rounded-full font-bold">URL</span>
                    <button @click="if(isUrl){ @auth $refs.urlForm.submit() @else window.location='{{ route('register') }}' @endauth }else{ $refs.srchForm.submit() }"
                        class="absolute right-1.5 top-1/2 -translate-y-1/2 bg-primary hover:bg-primary-dark text-white px-4 py-2 rounded-xl text-xs font-bold transition"
                        x-text="isUrl ? 'Fetch' : 'Search'"></button>
                    <form x-ref="srchForm" action="{{ route('products.index') }}" method="GET" class="hidden">
                        <input type="text" name="q" :value="q">
                    </form>
                    @auth
                    <form x-ref="urlForm" action="{{ route('user.scraper.fetch') }}" method="POST" class="hidden">
                        @csrf <input type="text" name="url" :value="q">
                    </form>
                    @endauth
                </div>
            </div>

            {{-- BYBLEARN Hub Link (desktop) --}}
            <a href="{{ route('hub.home') }}"
               class="hidden lg:flex items-center gap-1.5 px-3 py-2 rounded-xl text-sm font-bold transition
                      bg-hub-50 text-hub hover:bg-hub hover:text-white border border-hub/20 hover:border-hub ml-2 flex-shrink-0"
               style="color:#7C3AED">
              <i class="fas fa-graduation-cap text-xs"></i>
              <span>BYBLEARN</span>
            </a>

            {{-- Right actions --}}
            <div class="flex items-center gap-1 ml-auto lg:ml-0">
                <button @click="search=!search" class="lg:hidden w-9 h-9 flex items-center justify-center rounded-xl text-gray-500 hover:bg-gray-100 transition">
                    <i class="fas fa-search text-sm"></i>
                </button>
                @auth
                <a href="{{ route('user.cart.index') }}" class="relative w-9 h-9 flex items-center justify-center rounded-xl text-gray-500 hover:text-primary hover:bg-gray-100 transition">
                    <i class="fas fa-shopping-cart text-sm"></i>
                    @php $cc=\App\Models\CartItem::where('user_id',auth()->id())->count() @endphp
                    <span x-data
                          x-show="$store.badge.cart > 0"
                          x-text="$store.badge.cart > 9 ? '9+' : $store.badge.cart"
                          class="byb-badge byb-badge--accent absolute -top-0.5 -right-0.5"
                          style="{{ $cc > 0 ? '' : 'display:none' }}">{{ $cc > 9 ? '9+' : $cc }}</span>
                </a>
                <a href="{{ route('user.notifications.index') }}" class="relative w-9 h-9 flex items-center justify-center rounded-xl text-gray-500 hover:text-primary hover:bg-gray-100 transition"
                   @click="$store.badge.clearUnread()">
                    <i class="fas fa-bell text-sm"></i>
                    {{-- Static seed (no Alpine flash) replaced immediately by Alpine --}}
                    @php $nc=auth()->user()->unreadNotificationsCount() @endphp
                    <span id="header-bell-badge"
                          x-data
                          x-show="$store.badge.unread > 0"
                          x-text="$store.badge.unread > 9 ? '9+' : $store.badge.unread"
                          :class="{'byb-badge-enter': $store.badge.unread > 0}"
                          class="byb-badge absolute -top-0.5 -right-0.5"
                          style="{{ $nc > 0 ? '' : 'display:none' }}">{{ $nc > 9 ? '9+' : $nc }}</span>
                </a>
                @php
                    $walletSym  = \App\Helpers\CurrencyHelper::symbol(auth()->user()->currency);
                    $navVendor  = auth()->user()->vendorProfile;
                    $navBalance = ($navVendor && $navVendor->isActive())
                        ? (float) $navVendor->available_balance
                        : auth()->user()->getWalletBalance();
                @endphp
                <a href="{{ $navVendor && $navVendor->isActive() ? route('vendor.wallet') : route('user.wallet.index') }}"
                   class="hidden md:flex items-center gap-2 px-3 py-1.5 rounded-xl text-xs font-bold transition border border-primary/20 hover:border-primary text-primary hover:bg-primary hover:text-white" style="background:rgba(26,58,107,0.06)">
                    <i class="fas {{ $navVendor && $navVendor->isActive() ? 'fa-store' : 'fa-wallet' }} text-xs"></i>
                    <span>{{ $walletSym }}{{ number_format($navBalance, 2) }}</span>
                    @if($navVendor && $navVendor->isActive())
                    <span class="text-[9px] opacity-60">earnings</span>
                    @endif
                </a>
                <div class="relative" x-data="{ open:false }">
                    <button @click="open=!open" class="flex items-center gap-2 pl-1.5 pr-2.5 py-1 rounded-xl hover:bg-gray-100 transition">
                        <div class="w-8 h-8 bg-gradient-to-br from-primary to-primary-light text-white rounded-lg flex items-center justify-center text-xs font-black shadow-sm">
                            {{ strtoupper(substr(auth()->user()->name,0,1)) }}
                        </div>
                        <span class="hidden md:block text-sm font-semibold text-gray-700">{{ explode(' ',auth()->user()->name)[0] }}</span>
                        <i class="fas fa-chevron-down text-gray-400 text-[10px] transition-transform" :class="open?'rotate-180':''"></i>
                    </button>
                    <div x-show="open" x-transition:enter="transition ease-out duration-150"
                        x-transition:enter-start="opacity-0 scale-95 -translate-y-1"
                        x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                        @click.outside="open=false"
                        class="absolute right-0 mt-2 w-52 bg-white rounded-2xl shadow-xl border border-gray-100 py-2 z-50 origin-top-right">
                        <div class="px-4 py-2.5 border-b border-gray-100 mb-1">
                            <p class="text-xs font-bold text-gray-800 truncate">{{ auth()->user()->name }}</p>
                            <p class="text-[11px] text-gray-400 truncate">{{ auth()->user()->email }}</p>
                        </div>

                        @if(auth()->user()->is_vendor && auth()->user()->vendor_status === 'active')
                        {{-- ═══ VENDOR MENU ═══ --}}
                        <a href="{{ route('user.profile') }}" class="flex items-center gap-3 px-4 py-2 text-sm text-gray-700 hover:bg-primary-50 hover:text-primary transition">
                            <i class="fas fa-user-circle w-4 text-center text-gray-400 text-xs"></i>My Account
                        </a>
                        <div class="border-t border-gray-100 my-1"></div>
                        <p class="px-4 pt-1 pb-0.5 text-[10px] font-black text-teal-600 uppercase tracking-wider flex items-center gap-1.5">
                            <i class="fas fa-store text-[9px]"></i> Vendor Store
                        </p>
                        @foreach([
                            ['vendor.dashboard',    'fas fa-tachometer-alt', 'Dashboard'],
                            ['vendor.wallet',       'fas fa-wallet',         'Vendor Wallet'],
                            ['vendor.products',     'fas fa-box-open',       'My Products'],
                            ['vendor.orders.index', 'fas fa-shopping-bag',   'Customer Orders'],
                            ['vendor.analytics',    'fas fa-chart-line',     'Analytics'],
                            ['vendor.settings',     'fas fa-cog',            'Store Settings'],
                        ] as [$r,$i,$l])
                        <a href="{{ route($r) }}" class="flex items-center gap-3 px-4 py-2 text-sm text-gray-700 hover:bg-teal-50 hover:text-teal-700 transition">
                            <i class="{{ $i }} w-4 text-center text-teal-500 text-xs"></i>{{ $l }}
                        </a>
                        @endforeach
                        <div class="border-t border-gray-100 my-1"></div>
                        <p class="px-4 pt-1 pb-0.5 text-[10px] font-black text-gray-400 uppercase tracking-wider">
                            Shopping
                        </p>
                        <a href="{{ route('user.wallet.index') }}" class="flex items-center gap-3 px-4 py-2 text-sm text-gray-700 hover:bg-primary-50 hover:text-primary transition">
                            <i class="fas fa-wallet w-4 text-center text-gray-400 text-xs"></i>Shopping Wallet
                        </a>
                        <a href="{{ route('user.orders.index') }}" class="flex items-center gap-3 px-4 py-2 text-sm text-gray-700 hover:bg-primary-50 hover:text-primary transition">
                            <i class="fas fa-box w-4 text-center text-gray-400 text-xs"></i>My Orders
                        </a>

                        @else
                        {{-- ═══ BUYER MENU ═══ --}}
                        @foreach([
                            ['route'=>'user.profile',         'icon'=>'fas fa-user-circle',    'label'=>'My Account'],
                            ['route'=>'user.dashboard',       'icon'=>'fas fa-home',            'label'=>'Dashboard'],
                            ['route'=>'user.orders.index',    'icon'=>'fas fa-box',             'label'=>'My Orders'],
                            ['route'=>'tracking.index',       'icon'=>'fas fa-search-location', 'label'=>'Track Order'],
                            ['route'=>'user.wallet.index',    'icon'=>'fas fa-wallet',          'label'=>'Wallet'],
                            ['route'=>'user.supplier.create', 'icon'=>'fab fa-alipay',          'label'=>'Pay Supplier'],
                            ['route'=>'user.referrals.index', 'icon'=>'fas fa-users',           'label'=>'Referrals'],
                        ] as $item)
                        <a href="{{ route($item['route']) }}" class="flex items-center gap-3 px-4 py-2 text-sm text-gray-700 hover:bg-primary-50 hover:text-primary transition">
                            <i class="{{ $item['icon'] }} w-4 text-center text-gray-400 text-xs"></i>{{ $item['label'] }}
                        </a>
                        @endforeach
                        {{-- Spin & Win — shown only when feature is enabled --}}
                        @if(\App\Models\Setting::get('spinwin_enabled','0') === '1')
                        <div class="border-t border-gray-100 mt-1 pt-1">
                            <a href="{{ route('user.spinwin.show') }}"
                               class="flex items-center gap-3 px-4 py-2 text-sm font-semibold hover:bg-pink-50 transition"
                               style="color:#db2777">
                                <i class="fas fa-dharmachakra w-4 text-center text-xs" style="color:#db2777"></i>
                                🎡 Spin &amp; Win
                            </a>
                        </div>
                        @endif

                        {{-- Pending vendor --}}
                        @if(auth()->user()->is_vendor)
                        <div class="border-t border-gray-100 mt-1 pt-1">
                            <a href="{{ route('vendor.dashboard') }}" class="flex items-center gap-3 px-4 py-2 text-sm text-yellow-700 hover:bg-yellow-50 transition">
                                <i class="fas fa-clock w-4 text-center text-yellow-500 text-xs"></i>Vendor Application
                            </a>
                        </div>
                        @elseif(\App\Models\Setting::get('vendor_marketplace_open','0') === '1')
                        <div class="border-t border-gray-100 mt-1 pt-1">
                            <a href="{{ route('vendor.apply') }}" class="flex items-center gap-3 px-4 py-2 text-sm text-accent hover:bg-orange-50 transition font-semibold">
                                <i class="fas fa-store w-4 text-center text-accent text-xs"></i>Become a Vendor 🏪
                            </a>
                        </div>
                        @endif
                        @endif
                        {{-- ═══ END MENUS ═══ --}}

                        @if(auth()->user()->is_admin)
                        <div class="border-t border-gray-100 mt-1 pt-1">
                            <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 px-4 py-2 text-sm text-accent font-semibold hover:bg-orange-50 transition">
                                <i class="fas fa-shield-alt w-4 text-center text-xs"></i>Admin Panel
                            </a>
                        </div>
                        @endif
                        <div class="border-t border-gray-100 mt-1 pt-1">
                            <a href="{{ route('hub.home') }}"
                               class="flex items-center gap-3 px-4 py-2 text-sm font-semibold hover:bg-hub-50 transition"
                               style="color:#7C3AED">
                                <i class="fas fa-graduation-cap w-4 text-center text-xs" style="color:#7C3AED"></i>🎓 BYBLEARN
                            </a>
                        </div>
                        <div class="border-t border-gray-100 mt-1 pt-1">
                            <form action="{{ route('logout') }}" method="POST">
                                @csrf
                                <button class="flex items-center gap-3 w-full px-4 py-2 text-sm text-red-500 hover:bg-red-50 transition">
                                    <i class="fas fa-sign-out-alt w-4 text-center text-xs"></i>Sign Out
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                @else
                <a href="{{ route('login') }}" class="hidden md:block text-sm font-semibold text-gray-600 hover:text-primary transition px-3 py-2">Sign In</a>
                <a href="{{ route('register') }}" class="bg-accent hover:bg-accent-dark text-white text-sm font-bold px-5 py-2.5 rounded-xl transition btn-glow">Get Started</a>
                @endauth
                <button @click="mob=!mob" class="lg:hidden w-9 h-9 flex items-center justify-center rounded-xl text-gray-500 hover:bg-gray-100 ml-1">
                    <i class="fas text-sm" :class="mob?'fa-times':'fa-bars'"></i>
                </button>
            </div>
        </div>

        {{-- Mobile search bar --}}
        <div x-show="search" x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 -translate-y-2"
            x-transition:enter-end="opacity-100 translate-y-0"
            class="pb-3 lg:hidden" x-data="{ q2: '', isUrl2: false }" x-init="$watch('q2', v => isUrl2 = v.startsWith('http'))">
            <div class="flex gap-2">
                <div class="relative flex-1">
                    <i class="fas absolute left-3.5 top-1/2 -translate-y-1/2 text-sm z-10 pointer-events-none" :class="isUrl2 ? 'fa-link text-accent' : 'fa-search text-gray-400'"></i>
                    <input type="text" x-model="q2"
                        @keydown.enter.prevent="if(isUrl2){ @auth $refs.murlF.submit() @else window.location='{{ route('register') }}' @endauth }else{ $refs.msrchF.submit() }"
                        placeholder="Search or paste product URL..."
                        class="w-full pl-10 pr-4 py-2.5 bg-gray-50 border-2 border-gray-200 rounded-xl text-sm focus:outline-none focus:border-primary transition">
                </div>
                <button @click="if(isUrl2){ @auth $refs.murlF.submit() @else window.location='{{ route('register') }}' @endauth }else{ $refs.msrchF.submit() }"
                    class="bg-primary text-white px-4 py-2.5 rounded-xl text-sm font-bold transition" x-text="isUrl2?'Fetch':'Search'"></button>
                <form x-ref="msrchF" action="{{ route('products.index') }}" method="GET" class="hidden"><input type="text" name="q" :value="q2"></form>
                @auth
                <form x-ref="murlF" action="{{ route('user.scraper.fetch') }}" method="POST" class="hidden">@csrf<input type="text" name="url" :value="q2"></form>
                @endauth
            </div>
        </div>

        {{-- Mobile menu --}}
        <div x-show="mob" x-transition class="lg:hidden pb-4 border-t border-gray-100 pt-3">
            {{-- Public links --}}
            <a href="{{ route('hub.home') }}" class="block px-3 py-2.5 rounded-xl text-sm font-bold hover:bg-hub-50 transition" style="color:#7C3AED">🎓 BYBLEARN — Learn & Earn</a>
            <a href="{{ route('home') }}" class="block px-3 py-2.5 rounded-xl text-sm font-medium text-gray-700 hover:bg-primary-50 hover:text-primary transition">🏠 {{ __('app.home') }}</a>
            <a href="{{ route('products.index') }}" class="block px-3 py-2.5 rounded-xl text-sm font-medium text-gray-700 hover:bg-primary-50 hover:text-primary transition">🛍️ {{ __('app.products') }}</a>
            <a href="{{ route('tracking.index') }}" class="block px-3 py-2.5 rounded-xl text-sm font-medium text-gray-700 hover:bg-primary-50 hover:text-primary transition">📦 {{ __('app.track_order') }}</a>
            <a href="{{ route('shipping.calculator') }}" class="block px-3 py-2.5 rounded-xl text-sm font-medium text-gray-700 hover:bg-primary-50 hover:text-primary transition">🚢 {{ __('app.shipping_calc') }}</a>
            <a href="{{ route('user.scraper.index') }}" class="block px-3 py-2.5 rounded-xl text-sm font-medium text-gray-700 hover:bg-primary-50 hover:text-primary transition">🔗 {{ __('app.scraper') }}</a>
            <a href="{{ route('about') }}" class="block px-3 py-2.5 rounded-xl text-sm font-medium text-gray-700 hover:bg-primary-50 hover:text-primary transition">ℹ️ {{ __('app.about') }}</a>
            <a href="{{ route('contact') }}" class="block px-3 py-2.5 rounded-xl text-sm font-medium text-gray-700 hover:bg-primary-50 hover:text-primary transition">📞 {{ __('app.contact') }}</a>
            <a href="https://elearn.bybimport.com" target="_blank" class="block px-3 py-2.5 rounded-xl text-sm font-bold text-purple-700 hover:bg-purple-50 transition">🎓 eLearning</a>

            @auth
            {{-- Logged-in account links --}}
            <div class="border-t border-gray-100 mt-2 pt-2 space-y-0.5">
                @if(auth()->user()->is_vendor && auth()->user()->vendor_status === 'active')
                {{-- VENDOR mobile links --}}
                <p class="px-3 pt-1 pb-0.5 text-[10px] font-black text-teal-600 uppercase tracking-wider">Vendor Store</p>
                <a href="{{ route('vendor.dashboard') }}" class="block px-3 py-2.5 rounded-xl text-sm font-semibold text-teal-700 hover:bg-teal-50 transition">🏪 Vendor Dashboard</a>
                <a href="{{ route('vendor.wallet') }}" class="block px-3 py-2.5 rounded-xl text-sm font-medium text-teal-700 hover:bg-teal-50 transition">💰 Vendor Wallet</a>
                <a href="{{ route('vendor.orders.index') }}" class="block px-3 py-2.5 rounded-xl text-sm font-medium text-gray-700 hover:bg-gray-50 transition">🛒 Customer Orders</a>
                <a href="{{ route('vendor.products') }}" class="block px-3 py-2.5 rounded-xl text-sm font-medium text-gray-700 hover:bg-gray-50 transition">📦 My Products</a>
                <div class="border-t border-gray-100 mt-1 pt-1"></div>
                <p class="px-3 pt-1 pb-0.5 text-[10px] font-black text-gray-400 uppercase tracking-wider">Shopping</p>
                <a href="{{ route('user.orders.index') }}" class="block px-3 py-2.5 rounded-xl text-sm font-medium text-gray-700 hover:bg-primary-50 hover:text-primary transition">📋 My Orders</a>
                <a href="{{ route('user.wallet.index') }}" class="block px-3 py-2.5 rounded-xl text-sm font-medium text-gray-700 hover:bg-primary-50 hover:text-primary transition">👛 Shopping Wallet</a>
                @else
                {{-- BUYER mobile links --}}
                <a href="{{ route('user.dashboard') }}" class="block px-3 py-2.5 rounded-xl text-sm font-semibold text-primary hover:bg-primary-50 transition">📊 Dashboard</a>
                <a href="{{ route('user.orders.index') }}" class="block px-3 py-2.5 rounded-xl text-sm font-medium text-gray-700 hover:bg-primary-50 hover:text-primary transition">📋 My Orders</a>
                <a href="{{ route('user.wallet.index') }}" class="block px-3 py-2.5 rounded-xl text-sm font-medium text-gray-700 hover:bg-primary-50 hover:text-primary transition">👛 Wallet</a>
                <a href="{{ route('user.supplier.create') }}" class="block px-3 py-2.5 rounded-xl text-sm font-medium text-gray-700 hover:bg-primary-50 hover:text-primary transition">💳 Pay Supplier</a>
                <a href="{{ route('user.referrals.index') }}" class="block px-3 py-2.5 rounded-xl text-sm font-medium text-gray-700 hover:bg-primary-50 hover:text-primary transition">👥 Referrals</a>
                @if(\App\Models\Setting::get('spinwin_enabled','0') === '1')
                <a href="{{ route('user.spinwin.show') }}" class="block px-3 py-2.5 rounded-xl text-sm font-bold hover:bg-pink-50 transition" style="color:#db2777">🎡 Spin &amp; Win</a>
                @endif
                @if(auth()->user()->is_vendor)
                <a href="{{ route('vendor.dashboard') }}" class="block px-3 py-2.5 rounded-xl text-sm text-yellow-700 hover:bg-yellow-50 transition">⏳ Vendor Application</a>
                @elseif(\App\Models\Setting::get('vendor_marketplace_open','0') === '1')
                <a href="{{ route('vendor.apply') }}" class="block px-3 py-2.5 rounded-xl text-sm font-semibold text-accent hover:bg-orange-50 transition">🏪 Become a Vendor</a>
                @endif
                @endif
                <div class="border-t border-gray-100 mt-1 pt-1">
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button class="block w-full text-left px-3 py-2.5 rounded-xl text-sm text-red-500 hover:bg-red-50 transition font-medium">🚪 Sign Out</button>
                    </form>
                </div>
            </div>
            @else
            <div class="flex gap-2 pt-2">
                <a href="{{ route('login') }}" class="flex-1 text-center border-2 border-primary text-primary font-bold py-2.5 rounded-xl text-sm transition hover:bg-primary hover:text-white">Sign In</a>
                <a href="{{ route('register') }}" class="flex-1 text-center bg-accent text-white font-bold py-2.5 rounded-xl text-sm btn-glow transition">Get Started</a>
            </div>
            @endauth
        </div>
    </div>
</nav>

{{-- ── ANIMATED CATEGORY NAV STRIP — All devices ─────────────────────────── --}}
@php
    $navCats = \App\Models\Category::whereNull('parent_id')
        ->where('is_active', true)
        ->withCount(['products' => fn($q) => $q->where('is_active',true)->where('is_approved',true)])
        ->orderBy('sort_order')
        ->limit(12)
        ->get();

    // Category emoji icons
    $catIcons = [
        'electronics'  => '💻',
        'fashion'      => '👗',
        'home-garden'  => '🏠',
        'health-beauty'=> '💄',
        'sports'       => '⚽',
        'toys'         => '🎮',
        'auto-parts'   => '🚗',
        'tools'        => '🔧',
        'baby-kids'    => '👶',
        'food'         => '🍎',
        'office'       => '📚',
        'phones'       => '📱',
    ];

    $currentCatSlug = request()->route('category') instanceof \App\Models\Category
        ? request()->route('category')->slug
        : null;
@endphp

<div class="bg-white border-b border-gray-100 sticky top-[70px] z-40 shadow-sm">
    {{-- Desktop: full horizontal scrollable pill nav --}}
    <div class="max-w-7xl mx-auto px-4 lg:px-6 hidden md:block">
        <div class="flex items-center gap-1 py-2 overflow-x-auto scrollbar-hide"
             style="-webkit-overflow-scrolling:touch;scrollbar-width:none">
            {{-- All Products --}}
            <a href="{{ route('products.index') }}"
               class="flex-shrink-0 flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-bold transition whitespace-nowrap group
                   {{ !request()->routeIs('products.category') ? 'bg-primary text-white shadow-md' : 'text-gray-500 hover:text-primary hover:bg-primary-50' }}">
                <i class="fas fa-border-all text-[11px]"></i>
                <span>All Products</span>
            </a>

            @foreach($navCats as $cat)
            @php $emoji = $catIcons[$cat->slug] ?? '📦'; $isActive = $currentCatSlug === $cat->slug; @endphp
            <a href="{{ route('products.category', $cat->slug) }}"
               class="flex-shrink-0 flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-bold transition whitespace-nowrap group
                   {{ $isActive ? 'bg-primary text-white shadow-md' : 'text-gray-600 hover:text-primary hover:bg-primary-50' }}">
                <span class="text-sm" style="line-height:1">{{ $emoji }}</span>
                <span>{{ $cat->getTranslatedName() }}</span>
                @if($cat->products_count > 0)
                <span class="text-[9px] {{ $isActive ? 'bg-white/20' : 'bg-gray-100 group-hover:bg-primary-50' }} px-1.5 py-0.5 rounded-full font-semibold transition {{ $isActive ? 'text-white' : 'text-gray-400' }}">
                    {{ $cat->products_count }}
                </span>
                @endif
            </a>
            @endforeach

            {{-- Paste Link shortcut --}}
            <a href="{{ route('user.scraper.index') }}"
               class="flex-shrink-0 ml-auto flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs font-black text-accent hover:bg-accent/10 transition whitespace-nowrap border-2 border-accent/30 hover:border-accent/60">
                <i class="fas fa-link text-[11px]"></i>
                Paste Link
            </a>
        </div>
    </div>

    {{-- Mobile: horizontal scroll with emoji icons --}}
    <div class="md:hidden overflow-x-auto py-2 px-3" style="-webkit-overflow-scrolling:touch;scrollbar-width:none">
        <div class="flex items-center gap-2 w-max">
            {{-- All --}}
            <a href="{{ route('products.index') }}"
               class="flex flex-col items-center gap-1 px-3 py-2 rounded-2xl text-center transition flex-shrink-0 min-w-[58px]
                   {{ !request()->routeIs('products.category') ? 'bg-primary text-white shadow-md' : 'bg-gray-50 text-gray-600 hover:bg-primary-50' }}">
                <span class="text-xl">🛒</span>
                <span class="text-[9px] font-bold leading-tight">All</span>
            </a>

            @foreach($navCats as $cat)
            @php $emoji = $catIcons[$cat->slug] ?? '📦'; $isActive = $currentCatSlug === $cat->slug; @endphp
            <a href="{{ route('products.category', $cat->slug) }}"
               class="flex flex-col items-center gap-1 px-3 py-2 rounded-2xl text-center transition flex-shrink-0 min-w-[58px]
                   {{ $isActive ? 'bg-primary text-white shadow-md' : 'bg-gray-50 text-gray-600 active:bg-primary-50' }}">
                <span class="text-xl" style="line-height:1.2">{{ $emoji }}</span>
                <span class="text-[9px] font-bold leading-tight truncate max-w-[52px]">{{ Str::words($cat->getTranslatedName(), 1, '') }}</span>
            </a>
            @endforeach
        </div>
    </div>
</div>

{{-- Flash messages --}}
<div class="max-w-7xl mx-auto px-4 lg:px-6" id="flash-container">
    @if(session('success'))
    <div class="mt-3 flex items-center gap-3 bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-2xl text-sm shadow-sm animate-fade-up" x-data x-init="setTimeout(()=>$el.remove(),5000)">
        <div class="w-7 h-7 bg-green-100 rounded-lg flex items-center justify-center flex-shrink-0"><i class="fas fa-check text-green-600 text-xs"></i></div>
        <span class="font-medium">{!! session('success') !!}</span>
        <button @click="$el.remove()" class="ml-auto text-green-400 hover:text-green-600"><i class="fas fa-times text-xs"></i></button>
    </div>
    @endif
    @if(session('warning'))
    <div class="mt-3 flex items-start gap-3 bg-amber-50 border border-amber-300 text-amber-800 px-4 py-3 rounded-2xl text-sm shadow-sm animate-fade-up" x-data x-init="setTimeout(()=>$el.remove(),12000)">
        <div class="w-7 h-7 bg-amber-100 rounded-lg flex items-center justify-center flex-shrink-0 mt-0.5"><i class="fas fa-exclamation-triangle text-amber-600 text-xs"></i></div>
        <span class="font-medium leading-relaxed">{!! session('warning') !!}</span>
        <button @click="$el.remove()" class="ml-auto text-amber-400 hover:text-amber-600 flex-shrink-0"><i class="fas fa-times text-xs"></i></button>
    </div>
    @endif
    @if(session('error'))
    <div class="mt-3 flex items-center gap-3 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-2xl text-sm shadow-sm animate-fade-up" x-data x-init="setTimeout(()=>$el.remove(),6000)">
        <div class="w-7 h-7 bg-red-100 rounded-lg flex items-center justify-center flex-shrink-0"><i class="fas fa-exclamation text-red-600 text-xs"></i></div>
        <span class="font-medium">{{ session('error') }}</span>
        <button @click="$el.remove()" class="ml-auto text-red-400 hover:text-red-600"><i class="fas fa-times text-xs"></i></button>
    </div>
    @endif
    @if(session('info'))
    <div class="mt-3 flex items-center gap-3 bg-blue-50 border border-blue-200 text-blue-700 px-4 py-3 rounded-2xl text-sm shadow-sm animate-fade-up" x-data x-init="setTimeout(()=>$el.remove(),5000)">
        <div class="w-7 h-7 bg-blue-100 rounded-lg flex items-center justify-center flex-shrink-0"><i class="fas fa-info text-blue-600 text-xs"></i></div>
        <span class="font-medium">{{ session('info') }}</span>
        <button @click="$el.remove()" class="ml-auto text-blue-400 hover:text-blue-600"><i class="fas fa-times text-xs"></i></button>
    </div>
    @endif
    @if($errors->any())
    <div class="mt-3 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-2xl text-sm shadow-sm animate-fade-up">
        <ul class="space-y-0.5">@foreach($errors->all() as $e)<li class="flex items-center gap-2"><i class="fas fa-circle-exclamation text-xs"></i>{{ $e }}</li>@endforeach</ul>
    </div>
    @endif
</div>

{{-- Page content --}}
<main class="max-w-7xl mx-auto px-4 lg:px-6 min-h-screen @yield('body_pad','py-7') pb-24 sm:pb-7">
    @yield('content')
</main>

{{-- FOOTER --}}
<footer class="mt-16" style="background:linear-gradient(180deg,#0A1628 0%,#06101e 100%)">
    <div class="max-w-7xl mx-auto px-4 lg:px-6 pt-14 pb-8">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-10 mb-12">
            {{-- Brand --}}
            <div>
                <div class="inline-block bg-white rounded-xl p-2 mb-4 shadow-md">
                    <img src="{{ asset('images/logo.jpg') }}" alt="BYBHUB" style="height:40px;width:120px;object-fit:contain;display:block;">
                </div>
                <p class="text-gray-400 text-sm leading-relaxed mb-5">Your trusted partner for sourcing and shipping quality products directly from China to West Africa.</p>
                <div class="flex gap-2.5">
                    <a href="#" class="w-9 h-9 rounded-xl flex items-center justify-center transition hover:bg-accent" style="background:rgba(255,255,255,0.08)"><i class="fab fa-whatsapp text-white text-sm"></i></a>
                    <a href="#" class="w-9 h-9 rounded-xl flex items-center justify-center transition hover:bg-accent" style="background:rgba(255,255,255,0.08)"><i class="fab fa-telegram text-white text-sm"></i></a>
                    <a href="#" class="w-9 h-9 rounded-xl flex items-center justify-center transition hover:bg-accent" style="background:rgba(255,255,255,0.08)"><i class="fab fa-instagram text-white text-sm"></i></a>
                    <a href="#" class="w-9 h-9 rounded-xl flex items-center justify-center transition hover:bg-accent" style="background:rgba(255,255,255,0.08)"><i class="fab fa-facebook text-white text-sm"></i></a>
                </div>
            </div>
            {{-- Quick Links --}}
            <div>
                <h4 class="font-bold text-sm uppercase tracking-wider mb-4 text-gray-300">Quick Links</h4>
                <ul class="space-y-2.5 text-sm text-gray-400">
                    @foreach([
                        ['route'=>'home','label'=>'Home'],
                        ['route'=>'products.index','label'=>'Browse Products'],
                        ['route'=>'tracking.index','label'=>'Track My Order'],
                        ['route'=>'shipping.calculator','label'=>'Shipping Calculator'],
                        ['route'=>'user.scraper.index','label'=>'Product Finder'],
                        ['route'=>'about','label'=>'About Us'],
                        ['route'=>'contact','label'=>'Contact Us'],
                        ['route'=>'terms','label'=>'Terms & Conditions'],
                        ['route'=>'register','label'=>'Create Account'],
                    ] as $link)
                    <li><a href="{{ route($link['route']) }}" class="hover:text-white transition inline-flex items-center gap-1.5 hover:translate-x-1 transform">
                        <i class="fas fa-chevron-right text-[10px] text-accent"></i>{{ $link['label'] }}
                    </a></li>
                    @endforeach
                </ul>
            </div>
            {{-- Shipping --}}
            <div>
                <h4 class="font-bold text-sm uppercase tracking-wider mb-4 text-gray-300">Shipping Methods</h4>
                <ul class="space-y-3 text-sm text-gray-400">
                    <li class="flex items-start gap-2.5"><div class="w-7 h-7 rounded-lg bg-accent/20 flex items-center justify-center flex-shrink-0 mt-0.5"><i class="fas fa-plane text-accent text-xs"></i></div><div><p class="text-white font-semibold text-xs">Express Air</p><p class="text-gray-500 text-xs">3–7 business days</p></div></li>
                    <li class="flex items-start gap-2.5"><div class="w-7 h-7 rounded-lg bg-green-500/20 flex items-center justify-center flex-shrink-0 mt-0.5"><i class="fas fa-truck text-green-400 text-xs"></i></div><div><p class="text-white font-semibold text-xs">Cargo GZ</p><p class="text-gray-500 text-xs">5–10 business days</p></div></li>
                    <li class="flex items-start gap-2.5"><div class="w-7 h-7 rounded-lg bg-blue-500/20 flex items-center justify-center flex-shrink-0 mt-0.5"><i class="fas fa-ship text-blue-400 text-xs"></i></div><div><p class="text-white font-semibold text-xs">Sea Freight</p><p class="text-gray-500 text-xs">50–90 days, best rates</p></div></li>
                </ul>
            </div>
            {{-- Countries --}}
            <div>
                <h4 class="font-bold text-sm uppercase tracking-wider mb-4 text-gray-300">Countries We Serve</h4>
                <ul class="space-y-2.5 text-sm text-gray-400 mb-5">
                    <li class="flex items-center gap-2.5"><span class="text-xl">🇳🇬</span><div><p class="text-white font-semibold text-xs">Nigeria</p><p class="text-gray-500 text-xs">Naira (NGN ₦)</p></div></li>
                    <li class="flex items-center gap-2.5"><span class="text-xl">🇬🇭</span><div><p class="text-white font-semibold text-xs">Ghana</p><p class="text-gray-500 text-xs">Cedis (GHS ₵)</p></div></li>
                    <li class="flex items-center gap-2.5"><span class="text-xl">🇳🇪</span><div><p class="text-white font-semibold text-xs">Niger</p><p class="text-gray-500 text-xs">CFA Franc (XOF Fr)</p></div></li>
                </ul>
                <div class="p-3 rounded-xl text-xs text-gray-400 space-y-1.5" style="background:rgba(255,255,255,0.05)">
                    <p><i class="fas fa-envelope mr-1.5 text-accent"></i>info@bybimport.com</p>
                    <p><i class="fab fa-whatsapp mr-1.5 text-accent"></i>WhatsApp Support</p>
                </div>
            </div>
        </div>
        <div class="pt-6 flex flex-col md:flex-row items-center justify-between gap-3 text-xs text-gray-500" style="border-top:1px solid rgba(255,255,255,0.08)">
            <p>&copy; {{ date('Y') }} BYBHUB. All rights reserved. | bybimport.com</p>
            <div class="flex items-center gap-4">
                <a href="{{ route('hub.home') }}"
                   class="flex items-center gap-1.5 text-gray-400 hover:text-white transition font-semibold"
                   style="color:#A78BFA">
                  <i class="fas fa-graduation-cap text-[11px]"></i> BYBLEARN
                </a>
                <a href="https://elearn.bybimport.com"
                   target="_blank"
                   class="flex items-center gap-1.5 hover:text-white transition font-semibold"
                   style="color:#c4b5fd">
                  <i class="fas fa-graduation-cap text-[11px]"></i> eLearning
                </a>
                <span class="flex items-center gap-1"><i class="fas fa-shield-alt text-green-400"></i> Secured</span>
                <span class="flex items-center gap-1"><i class="fas fa-lock text-green-400"></i> SSL Encrypted</span>
                <span class="flex items-center gap-1"><i class="fas fa-star text-accent"></i> Trusted</span>
            </div>
        </div>
    </div>
</footer>

<script>
const revealObserver = new IntersectionObserver((entries) => {
    entries.forEach(e => { if(e.isIntersecting){ e.target.classList.add('visible'); } });
}, { threshold: 0.07, rootMargin: '0px 0px -30px 0px' });
document.querySelectorAll('.section-reveal').forEach(el => revealObserver.observe(el));
</script>
@stack('scripts')

{{-- ── Global Confirm Modal (replaces all browser confirm() dialogs) ────── --}}
<div x-data="confirmModal" x-cloak>
    <template x-if="show">
        <div class="fixed inset-0 z-[9999] flex items-center justify-center px-4"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100">
            {{-- Backdrop --}}
            <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="cancel()"></div>
            {{-- Dialog --}}
            <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-sm p-6 z-10"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 scale-95 translate-y-2"
                 x-transition:enter-end="opacity-100 scale-100 translate-y-0">
                {{-- Icon --}}
                <div class="w-12 h-12 rounded-2xl flex items-center justify-center mx-auto mb-4"
                     :class="iconBg">
                    <i class="fas text-xl" :class="iconClass"></i>
                </div>
                <h3 class="text-center font-black text-gray-900 text-lg mb-2" style="font-family:Poppins,sans-serif" x-text="title"></h3>
                <p class="text-center text-gray-500 text-sm mb-6 leading-relaxed" x-text="message"></p>
                <div class="flex gap-3">
                    <button @click="cancel()"
                        class="flex-1 border-2 border-gray-200 text-gray-600 font-bold py-3 rounded-xl text-sm hover:bg-gray-50 transition"
                        x-text="cancelText">
                    </button>
                    <button @click="confirm()"
                        class="flex-1 font-bold py-3 rounded-xl text-sm transition text-white"
                        :class="confirmBg"
                        x-text="confirmText">
                    </button>
                </div>
            </div>
        </div>
    </template>
</div>

<script>
// Global confirm modal store — call window.confirmModal.ask({...}) anywhere
document.addEventListener('alpine:init', () => {
    Alpine.data('confirmModal', () => ({
        show: false,
        title: 'Are you sure?',
        message: '',
        confirmText: 'Confirm',
        cancelText: 'Cancel',
        iconClass: 'fa-question',
        iconBg: 'bg-primary/10',
        confirmBg: 'bg-primary hover:bg-primary-dark',
        _resolve: null,

        ask(opts = {}) {
            this.title       = opts.title       ?? 'Are you sure?';
            this.message     = opts.message      ?? '';
            this.confirmText = opts.confirmText  ?? 'Confirm';
            this.cancelText  = opts.cancelText   ?? 'Cancel';
            this.iconClass   = opts.iconClass    ?? 'fa-question';
            this.iconBg      = opts.iconBg       ?? 'bg-primary/10';
            this.confirmBg   = opts.confirmBg    ?? 'bg-primary hover:bg-primary-dark';
            this.show = true;
            return new Promise(resolve => { this._resolve = resolve; });
        },

        confirm() { this.show = false; this._resolve?.(true); },
        cancel()  { this.show = false; this._resolve?.(false); },
    }));

    // Make it globally accessible
    Alpine.store('confirmModal', Alpine.reactive({
        _data: null,
        ask(opts) {
            return document.querySelector('[x-data="confirmModal"]')
                ?._x_dataStack?.[0]?.ask(opts);
        }
    }));
});

// Helper: attach to any form with data-confirm-* attributes
// Usage: <form data-confirm-title="..." data-confirm-msg="..." data-confirm-type="danger">
document.addEventListener('DOMContentLoaded', () => {
    document.addEventListener('submit', async (e) => {
        const form = e.target;
        if (!form.dataset.confirm) return;

        e.preventDefault();
        e.stopImmediatePropagation();

        const type    = form.dataset.confirmType ?? 'default';
        const isDanger = type === 'danger';

        const modal = document.querySelector('[x-data="confirmModal"]')?._x_dataStack?.[0];
        if (!modal) { form.submit(); return; }

        const ok = await modal.ask({
            title:       form.dataset.confirmTitle   ?? 'Are you sure?',
            message:     form.dataset.confirmMsg     ?? form.dataset.confirm,
            confirmText: form.dataset.confirmOk      ?? (isDanger ? 'Yes, proceed' : 'Confirm'),
            cancelText:  form.dataset.confirmCancel  ?? 'Cancel',
            iconClass:   isDanger ? 'fa-exclamation-triangle text-red-500'
                                  : 'fa-question text-primary',
            iconBg:      isDanger ? 'bg-red-100' : 'bg-primary/10',
            confirmBg:   isDanger ? 'bg-red-500 hover:bg-red-600'
                                  : 'bg-accent hover:bg-accent-dark',
        });

        if (ok) form.submit();
    }, true);
});
</script>

{{-- ── PROMOTIONAL POPUP BANNER ──────────────────────────────────────────── --}}
@if($popupEnabled && $popupTitle)
@php
    $popupProduct = $popupShowBuyNow && $popupProductId
        ? \App\Models\Product::find($popupProductId)
        : null;
    $popupKey = 'bybhub_popup_' . md5($popupTitle . $popupMessage); // changes when content changes
@endphp
<div x-data="{
        show: false,
        key: '{{ $popupKey }}',
        frequency: '{{ $popupFrequency }}',
        init() {
            const delay = {{ $popupDelay }} * 1000;
            if (this.frequency === 'always') {
                setTimeout(() => this.show = true, delay);
            } else if (this.frequency === 'session') {
                if (!sessionStorage.getItem(this.key)) {
                    setTimeout(() => { this.show = true; sessionStorage.setItem(this.key, '1'); }, delay);
                }
            } else {
                // once — localStorage
                if (!localStorage.getItem(this.key)) {
                    setTimeout(() => { this.show = true; localStorage.setItem(this.key, '1'); }, delay);
                }
            }
        },
        close() { this.show = false; }
     }"
     x-show="show"
     x-transition:enter="transition ease-out duration-400"
     x-transition:enter-start="opacity-0 scale-90"
     x-transition:enter-end="opacity-100 scale-100"
     x-transition:leave="transition ease-in duration-250"
     x-transition:leave-start="opacity-100 scale-100"
     x-transition:leave-end="opacity-0 scale-90"
     class="fixed inset-0 z-[99] flex items-center justify-center p-4"
     style="background:rgba(0,0,0,0.6);backdrop-filter:blur(4px)"
     @click.self="close()">

    <div class="relative max-w-sm w-full rounded-3xl overflow-hidden shadow-2xl"
         style="background:{{ $settings['popup_bg_color'] ?? '#1A3A6B' }}">

        {{-- Close button --}}
        <button @click="close()" class="absolute top-3 right-3 z-10 w-8 h-8 bg-white/20 hover:bg-white/30 rounded-full flex items-center justify-center transition">
            <i class="fas fa-times text-white text-xs"></i>
        </button>

        {{-- Badge --}}
        @if($settings['popup_badge'] ?? '')
        <div class="absolute top-4 left-4 z-10">
            <span class="text-xs font-black bg-accent text-white px-3 py-1 rounded-full shadow-lg">
                {{ $settings['popup_badge'] }}
            </span>
        </div>
        @endif

        {{-- Image --}}
        @if($popupImage)
        <div class="w-full h-40 overflow-hidden">
            <img src="{{ $popupImage }}" alt="{{ $popupTitle }}" class="w-full h-full object-cover">
        </div>
        @elseif($popupProduct && count($popupProduct->images ?? []))
        <div class="w-full h-44 overflow-hidden">
            <img src="{{ Storage::url($popupProduct->images[0]) }}" alt="{{ $popupProduct->title }}" class="w-full h-full object-cover">
        </div>
        @else
        {{-- Decorative gradient header --}}
        <div class="h-2" style="background:linear-gradient(90deg,#F47B20,#F59E0B,#10B981)"></div>
        @endif

        {{-- Content --}}
        <div class="p-6 text-white">
            <h2 class="font-black text-xl mb-2 leading-tight" style="font-family:Poppins,sans-serif">
                {{ $popupTitle }}
            </h2>
            @if($popupMessage)
            <p class="text-white/75 text-sm leading-relaxed mb-4">{{ $popupMessage }}</p>
            @endif

            {{-- Product info if Buy Now --}}
            @if($popupProduct)
            <div class="flex items-center gap-3 bg-white/10 rounded-2xl p-3 mb-4">
                @if(count($popupProduct->images ?? []))
                <img src="{{ Storage::url($popupProduct->images[0]) }}" class="w-14 h-14 rounded-xl object-cover flex-shrink-0">
                @endif
                <div class="flex-1 min-w-0">
                    <p class="text-white font-bold text-sm line-clamp-1">{{ $popupProduct->title }}</p>
                    <p class="text-accent font-black text-lg" style="font-family:Poppins,sans-serif">
                        @if($popupProduct->product_type === 'local')
                        ₦{{ number_format((float)$popupProduct->price_ngn, 0) }}
                        @else
                        {{ session('currency','NGN') === 'USD' ? '$' : '₦' }}{{ number_format($popupProduct->getPriceInCurrency(session('currency','NGN')), 0) }}
                        @endif
                    </p>
                </div>
            </div>
            @endif

            {{-- Action buttons --}}
            <div class="flex gap-2">
                @if($popupShowBuyNow && $popupProduct)
                <a href="{{ $popupProduct->product_type === 'local' ? route('local.checkout', $popupProduct->id) : route('products.show', $popupProduct) }}"
                   class="flex-1 font-black py-3 rounded-xl text-sm text-center transition hover:opacity-90"
                   style="background:linear-gradient(135deg,#F47B20,#D4660E);color:white;box-shadow:0 4px 16px rgba(244,123,32,0.4)">
                    🛍️ Buy Now
                </a>
                @elseif($popupButtonText && ($popupButtonUrl || true))
                <a href="{{ $popupButtonUrl ?: route('products.index') }}"
                   class="flex-1 font-black py-3 rounded-xl text-sm text-center transition hover:opacity-90"
                   style="background:linear-gradient(135deg,#F47B20,#D4660E);color:white;box-shadow:0 4px 16px rgba(244,123,32,0.4)">
                    {{ $popupButtonText }}
                </a>
                @endif
                <button @click="close()"
                    class="px-5 py-3 rounded-xl text-sm font-bold transition"
                    style="background:rgba(255,255,255,0.15);color:white;border:1px solid rgba(255,255,255,0.2)">
                    Later
                </button>
            </div>
        </div>
    </div>
</div>
@endif

{{-- ── AI CHAT ASSISTANT (replaces plain WhatsApp bubble) ──────────────────── --}}
@php $waNumber = preg_replace('/[^0-9]/', '', $whatsappSupport ?? \App\Models\Setting::get('contact_whatsapp','')); @endphp
<div class="fixed bottom-[80px] sm:bottom-6 right-5 z-[100] flex flex-col items-end gap-2"
     x-data="{
        open: false,
        loading: false,
        messages: [
            { from: 'bot', text: '👋 Hi! I\'m the BYBHUB Assistant. Ask me anything!' }
        ],
        input: '',
        showTooltip: false,
        init() { setTimeout(() => this.showTooltip = true, 4000); setTimeout(() => this.showTooltip = false, 9000); },
        async send() {
            const q = this.input.trim();
            if (!q || this.loading) return;
            this.messages.push({ from: 'user', text: q });
            this.input = '';
            this.loading = true;
            this.$nextTick(() => { const el = this.$refs.msgs; if(el) el.scrollTop = el.scrollHeight; });
            try {
                const r = await fetch('/api/ai-chat', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content },
                    body: JSON.stringify({ q })
                });
                const d = await r.json();
                this.messages.push({ from: 'bot', text: d.answer || 'Sorry, I couldn\'t understand that. Try asking differently!' });
            } catch(e) {
                this.messages.push({ from: 'bot', text: '⚠️ Something went wrong. Try again or contact us on WhatsApp.' });
            }
            this.loading = false;
            this.$nextTick(() => { const el = this.$refs.msgs; if(el) el.scrollTop = el.scrollHeight; });
        },
        quickAsk(q) { this.input = q; this.send(); }
     }"
     x-show="true">

    {{-- Chat window --}}
    <div x-show="open"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 scale-90 translate-y-4"
         x-transition:enter-end="opacity-100 scale-100 translate-y-0"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0 translate-y-2"
         class="bg-white rounded-3xl shadow-2xl overflow-hidden w-80 mb-1 flex flex-col"
         style="max-height:480px;box-shadow:0 20px 60px rgba(0,0,0,0.2)">

        {{-- Header --}}
        <div class="px-4 py-3 flex items-center gap-3 flex-shrink-0"
             style="background:linear-gradient(135deg,#1A3A6B,#2451A3)">
            <div class="w-9 h-9 rounded-full bg-white/20 flex items-center justify-center flex-shrink-0">
                <i class="fas fa-robot text-white text-base"></i>
            </div>
            <div class="flex-1 min-w-0">
                <p class="font-black text-white text-sm">BYBHUB Assistant</p>
                <div class="flex items-center gap-1.5">
                    <span class="w-1.5 h-1.5 bg-green-400 rounded-full animate-pulse"></span>
                    <p class="text-blue-200 text-[10px]">AI-powered · Always on</p>
                </div>
            </div>
            <button @click="open=false" class="text-white/60 hover:text-white transition flex-shrink-0">
                <i class="fas fa-times text-sm"></i>
            </button>
        </div>

        {{-- Messages --}}
        <div x-ref="msgs" class="flex-1 overflow-y-auto p-3 space-y-2.5 bg-gray-50" style="min-height:200px">
            <template x-for="(m,i) in messages" :key="i">
                <div :class="m.from==='user' ? 'flex justify-end' : 'flex justify-start'">
                    <div :class="m.from==='user'
                        ? 'bg-primary text-white rounded-2xl rounded-tr-sm px-3.5 py-2.5 max-w-[85%] text-xs leading-relaxed'
                        : 'bg-white border border-gray-100 text-gray-800 rounded-2xl rounded-tl-sm px-3.5 py-2.5 max-w-[90%] text-xs leading-relaxed shadow-sm'"
                         x-text="m.text"></div>
                </div>
            </template>
            <div x-show="loading" class="flex justify-start">
                <div class="bg-white border border-gray-100 rounded-2xl rounded-tl-sm px-4 py-2.5 shadow-sm">
                    <div class="flex gap-1 items-center h-4">
                        <span class="w-1.5 h-1.5 bg-gray-400 rounded-full animate-bounce" style="animation-delay:0ms"></span>
                        <span class="w-1.5 h-1.5 bg-gray-400 rounded-full animate-bounce" style="animation-delay:150ms"></span>
                        <span class="w-1.5 h-1.5 bg-gray-400 rounded-full animate-bounce" style="animation-delay:300ms"></span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Quick questions --}}
        <div class="px-3 py-2 flex gap-1.5 flex-wrap bg-white border-t border-gray-100 flex-shrink-0">
            @foreach(['How to order?','Shipping cost?','What do you sell?','Track my order'] as $q)
            <button @click="quickAsk('{{ $q }}')"
                    class="text-[10px] font-semibold border border-primary/30 text-primary bg-primary/5 hover:bg-primary hover:text-white px-2 py-1 rounded-lg transition whitespace-nowrap">
                {{ $q }}
            </button>
            @endforeach
        </div>

        {{-- Input --}}
        <div class="p-3 bg-white border-t border-gray-100 flex gap-2 flex-shrink-0">
            <input type="text" x-model="input"
                   @keydown.enter="send()"
                   placeholder="Ask anything..."
                   class="flex-1 border-2 border-gray-100 bg-gray-50 rounded-xl px-3 py-2 text-xs focus:outline-none focus:border-primary transition">
            <button @click="send()" :disabled="loading"
                    class="w-9 h-9 bg-primary hover:bg-primary-dark text-white rounded-xl flex items-center justify-center transition flex-shrink-0 disabled:opacity-50">
                <i class="fas fa-paper-plane text-xs"></i>
            </button>
        </div>

        {{-- WhatsApp fallback --}}
        @if($waNumber)
        <div class="px-3 pb-3 bg-white flex-shrink-0">
            <a href="https://wa.me/{{ $waNumber }}?text={{ urlencode('Hi BYBHUB! I need help.') }}"
               target="_blank"
               class="flex items-center justify-center gap-2 w-full py-2 rounded-xl text-xs font-bold text-white transition"
               style="background:linear-gradient(135deg,#25D366,#128C7E)">
                <i class="fab fa-whatsapp"></i> Talk to a real person on WhatsApp
            </a>
        </div>
        @endif
    </div>

    {{-- Auto-tooltip --}}
    <div x-show="showTooltip && !open"
         x-transition.opacity.duration.500ms
         class="bg-gray-900 text-white text-xs font-semibold px-3 py-2 rounded-xl shadow-lg mb-1 whitespace-nowrap">
        🤖 Ask our AI Assistant!
    </div>

    {{-- Floating button --}}
    <button @click="open=!open; showTooltip=false"
            class="relative w-14 h-14 rounded-full text-white shadow-2xl transition hover:scale-110 active:scale-95 flex items-center justify-center"
            style="background:linear-gradient(135deg,#1A3A6B,#F47B20);box-shadow:0 6px 30px rgba(26,58,107,0.5)">
        <span class="absolute inset-0 rounded-full animate-ping opacity-15" style="background:#1A3A6B"></span>
        <i class="fas text-2xl transition-all duration-200" :class="open ? 'fa-times scale-90' : 'fa-robot'"></i>
        <span x-show="!open" class="absolute -top-0.5 -right-0.5 w-3.5 h-3.5 bg-accent rounded-full border-2 border-white"></span>
    </button>
</div>

    {{-- Chat popup card --}}
{{-- old whatsapp widget removed --}}

{{-- ── v1.1.0 Welcome Popup (shows once after login) ───────────────────── --}}
@if(session('show_update_popup'))
<?php session()->forget('show_update_popup'); ?>
<div x-data="{ show: true, pwa: window.matchMedia('(display-mode: standalone)').matches }"
     x-show="show"
     x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="opacity-0 translate-y-6"
     x-transition:enter-end="opacity-100 translate-y-0"
     style="display:none"
     class="fixed inset-x-0 bottom-0 z-[200] sm:inset-auto sm:bottom-6 sm:right-6 sm:max-w-xs px-0 sm:px-0">

    <div class="bg-white sm:rounded-2xl shadow-2xl border border-gray-100 overflow-hidden"
         style="box-shadow:0 8px 40px rgba(0,0,0,0.18)">
        {{-- Accent strip --}}
        <div class="h-1" style="background:linear-gradient(90deg,#1A3A6B,#F47B20,#7c3aed)"></div>

        <div class="p-5">
            {{-- Header --}}
            <div class="flex items-start justify-between mb-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0 text-lg"
                         style="background:linear-gradient(135deg,#1A3A6B,#2451A3)">
                        🎉
                    </div>
                    <div>
                        <p class="font-black text-gray-900 text-sm leading-tight" style="font-family:Poppins,sans-serif">
                            BYBHUB v1.1.0 is live!
                        </p>
                        <p class="text-gray-400 text-[11px] mt-0.5">Here's what's new</p>
                    </div>
                </div>
                <button @click="show=false"
                        class="text-gray-300 hover:text-gray-500 transition flex-shrink-0 ml-2">
                    <i class="fas fa-times text-sm"></i>
                </button>
            </div>

            {{-- Feature list --}}
            <div class="space-y-2 mb-4">
                @foreach([
                    ['🛒', 'New cart — Save for Later & weight estimate'],
                    ['💳', 'Klump BNPL — pay orders in instalments'],
                    ['🤖', 'AI Assistant — ask anything, get instant answers'],
                    ['📊', 'Dashboard — badge counts on every card'],
                    ['🎓', 'eLearning — join the waitlist at elearn.bybimport.com'],
                ] as [$e, $t])
                <div class="flex items-center gap-2.5">
                    <span class="text-base flex-shrink-0 leading-none">{{ $e }}</span>
                    <span class="text-xs text-gray-600 leading-snug">{{ $t }}</span>
                </div>
                @endforeach
            </div>

            {{-- PWA notice --}}
            <div x-show="pwa"
                 class="bg-blue-50 border border-blue-100 rounded-xl px-3 py-2 mb-4 flex items-center gap-2">
                <i class="fas fa-sync text-blue-400 text-xs flex-shrink-0"></i>
                <p class="text-blue-700 text-[11px] font-medium">
                    App update available. Close and reopen to get the latest version.
                </p>
            </div>

            <button @click="show=false"
                    class="w-full text-white font-bold py-3 rounded-xl text-sm transition active:scale-95"
                    style="background:linear-gradient(135deg,#1A3A6B,#2451A3)">
                Got it, let's go! 🚀
            </button>
        </div>
    </div>
</div>
@endif

{{-- ── PWA Splash Screen (shows ONLY when launched from home screen) ─────── --}}
<div id="pwa-splash"
     style="position:fixed;inset:0;z-index:9999;display:none;flex-direction:column;
            align-items:center;justify-content:center;
            background:linear-gradient(160deg,#0A1628 0%,#1A3A6B 60%,#2451A3 100%);
            transition:opacity 0.4s ease,visibility 0.4s ease"
     aria-hidden="true">
    <div style="text-align:center;padding:24px">
        <img src="{{ asset('images/icon-512.png') }}"
             style="width:96px;height:96px;border-radius:22px;margin:0 auto 20px;
                    box-shadow:0 8px 32px rgba(0,0,0,0.4);
                    animation:splashPop 0.6s cubic-bezier(0.34,1.56,0.64,1) forwards"
             alt="BYBHUB">
        <h1 style="color:white;font-family:Poppins,sans-serif;font-size:26px;font-weight:900;
                   margin:0 0 6px;letter-spacing:-0.5px">BYBHUB</h1>
        <p style="color:rgba(147,197,253,0.9);font-size:14px;margin:0 0 32px">
            China to Your Door 🇨🇳 → 🇳🇬
        </p>
        {{-- Loading bar --}}
        <div style="width:160px;height:3px;background:rgba(255,255,255,0.15);border-radius:99px;overflow:hidden;margin:0 auto">
            <div id="pwa-splash-bar"
                 style="height:100%;width:0%;background:linear-gradient(90deg,#F47B20,#FFB347);
                        border-radius:99px;transition:width 0.8s ease"></div>
        </div>
    </div>
</div>
<style>
@keyframes splashPop {
    0%   { transform: scale(0.5) rotate(-5deg); opacity: 0; }
    70%  { transform: scale(1.1) rotate(1deg); opacity: 1; }
    100% { transform: scale(1) rotate(0deg); opacity: 1; }
}
</style>
<script>
(function () {
    // Check FIRST — before showing anything
    const isStandalone = window.matchMedia('(display-mode: standalone)').matches
                      || window.navigator.standalone === true;

    // Only show splash when truly running as installed PWA from home screen
    if (!isStandalone) return;

    const splash = document.getElementById('pwa-splash');
    const bar    = document.getElementById('pwa-splash-bar');
    if (!splash) return;

    // Now show it
    splash.style.display = 'flex';

    // Animate loading bar
    requestAnimationFrame(() => { bar.style.width = '70%'; });
    window.addEventListener('load', () => {
        bar.style.width = '100%';
        setTimeout(() => {
            splash.style.opacity = '0';
            splash.style.visibility = 'hidden';
            setTimeout(() => splash.remove(), 400);
        }, 300);
    });
    // Safety fallback — always remove after 3s
    setTimeout(() => {
        if (splash.parentNode) {
            splash.style.opacity = '0';
            splash.style.visibility = 'hidden';
            setTimeout(() => splash.remove(), 400);
        }
    }, 3000);
})();
</script>

{{-- ── PWA Bottom Navigation Bar (mobile only, shows when logged in) ─────── --}}
@auth
<nav id="pwa-bottom-nav"
     class="fixed bottom-0 left-0 right-0 z-40 sm:hidden bg-white border-t border-gray-100 shadow-[0_-2px_16px_rgba(0,0,0,0.08)]"
     style="padding-bottom: env(safe-area-inset-bottom, 0px)">
    @php
        $currentPath = request()->path();
        $isVendor    = auth()->user()->is_vendor && auth()->user()->vendor_status === 'active';
        $unread      = auth()->user()->unreadNotificationsCount();
        $cartCount   = \App\Models\CartItem::where('user_id', auth()->id())->count();
    @endphp
    <div class="flex items-stretch h-[56px]">

        {{-- Home --}}
        @php $homeActive = ($currentPath === '' || $currentPath === '/'); @endphp
        <a href="{{ route('home') }}"
           class="flex-1 flex flex-col items-center justify-center gap-0.5 relative transition-colors
                  {{ $homeActive ? 'text-primary' : 'text-gray-400 hover:text-primary' }}">
            @if($homeActive)
            <span class="absolute top-0 left-1/2 -translate-x-1/2 w-8 h-0.5 bg-primary rounded-full"></span>
            @endif
            <i class="fas fa-home text-lg leading-none"></i>
            <span class="text-[10px] font-semibold leading-none mt-0.5">Home</span>
        </a>

        {{-- Shop --}}
        @php $shopActive = str_starts_with($currentPath, 'products') || str_starts_with($currentPath, 'categories'); @endphp
        <a href="{{ route('products.index') }}"
           class="flex-1 flex flex-col items-center justify-center gap-0.5 relative transition-colors
                  {{ $shopActive ? 'text-primary' : 'text-gray-400 hover:text-primary' }}">
            @if($shopActive)
            <span class="absolute top-0 left-1/2 -translate-x-1/2 w-8 h-0.5 bg-primary rounded-full"></span>
            @endif
            <i class="fas fa-shopping-bag text-lg leading-none"></i>
            <span class="text-[10px] font-semibold leading-none mt-0.5">Shop</span>
        </a>

        {{-- Import FAB — centre accent button --}}
        @if($isVendor)
        @php $importActive = str_starts_with($currentPath, 'dashboard/vendor/orders'); @endphp
        <a href="{{ route('vendor.orders.index') }}"
           class="flex-1 flex flex-col items-center justify-center gap-0.5 relative transition-colors
                  {{ $importActive ? 'text-accent' : 'text-gray-400 hover:text-accent' }}">
            <div class="relative -mt-5">
                <div class="w-12 h-12 rounded-full flex items-center justify-center shadow-lg"
                     style="background:linear-gradient(135deg,#F47B20,#D4660E);box-shadow:0 4px 16px rgba(244,123,32,0.45)">
                    <i class="fas fa-store text-white text-lg"></i>
                </div>
            </div>
            <span class="text-[10px] font-semibold leading-none mt-0.5">Store</span>
        </a>
        @else
        @php $importActive = str_starts_with($currentPath, 'dashboard/scraper') || str_starts_with($currentPath, 'dashboard/find-product'); @endphp
        <a href="{{ route('user.scraper.index') }}"
           class="flex-1 flex flex-col items-center justify-center gap-0.5 relative transition-colors
                  {{ $importActive ? 'text-accent' : 'text-gray-400 hover:text-accent' }}">
            <div class="relative -mt-5">
                <div class="w-12 h-12 rounded-full flex items-center justify-center shadow-lg"
                     style="background:linear-gradient(135deg,#F47B20,#D4660E);box-shadow:0 4px 16px rgba(244,123,32,0.45)">
                    <i class="fas fa-link text-white text-lg"></i>
                </div>
            </div>
            <span class="text-[10px] font-semibold leading-none mt-0.5">Import</span>
        </a>
        @endif

        {{-- Orders --}}
        @php $ordersActive = str_starts_with($currentPath, 'dashboard/orders'); @endphp
        <a href="{{ route('user.orders.index') }}"
           class="flex-1 flex flex-col items-center justify-center gap-0.5 relative transition-colors
                  {{ $ordersActive ? 'text-primary' : 'text-gray-400 hover:text-primary' }}">
            @if($ordersActive)
            <span class="absolute top-0 left-1/2 -translate-x-1/2 w-8 h-0.5 bg-primary rounded-full"></span>
            @endif
            <div class="relative">
                <i class="fas fa-box text-lg leading-none"></i>
                {{-- Active-orders badge: shows count of in-progress orders --}}
                <span x-data
                      x-show="$store.badge.activeOrders > 0"
                      x-text="$store.badge.activeOrders > 9 ? '9+' : $store.badge.activeOrders"
                      class="byb-badge byb-badge--sm absolute -top-1 -right-2"
                      @php $ao = \App\Models\Order::where('user_id', auth()->id())->whereNotIn('status',['delivered','cancelled','refunded',''])->whereNotIn('local_order_status',['delivered','completed','cancelled','refunded','disputed',null])->count(); @endphp
                      style="{{ $ao > 0 ? '' : 'display:none' }}">{{ $ao > 9 ? '9+' : $ao }}</span>
            </div>
            <span class="text-[10px] font-semibold leading-none mt-0.5">Orders</span>
        </a>

        {{-- Account --}}
        @php $accountActive = str_starts_with($currentPath, 'dashboard/profile') || str_starts_with($currentPath, 'dashboard/account'); @endphp
        <a href="{{ route('user.profile') }}"
           class="flex-1 flex flex-col items-center justify-center gap-0.5 relative transition-colors
                  {{ $accountActive ? 'text-primary' : 'text-gray-400 hover:text-primary' }}"
           @click="$store.badge.clearUnread()">
            @if($accountActive)
            <span class="absolute top-0 left-1/2 -translate-x-1/2 w-8 h-0.5 bg-primary rounded-full"></span>
            @endif
            <div class="relative">
                <i class="fas fa-user-circle text-lg leading-none"></i>
                {{-- Numeric unread badge (replaces the old plain dot) --}}
                @php $unread = auth()->user()->unreadNotificationsCount(); @endphp
                <span x-data
                      x-show="$store.badge.unread > 0"
                      x-text="$store.badge.unread > 9 ? '9+' : $store.badge.unread"
                      class="byb-badge byb-badge--dot absolute -top-1 -right-1.5"
                      style="{{ $unread > 0 ? '' : 'display:none' }}"></span>
            </div>
            <span class="text-[10px] font-semibold leading-none mt-0.5">Account</span>
        </a>

    </div>
</nav>
@endauth

{{-- ── PWA: Install prompt + Service Worker registration ──────────────── --}}
<div id="pwa-install-banner"
     class="fixed bottom-[88px] sm:bottom-20 left-4 right-4 z-50"
     x-data="{ show: false }"
     x-show="show"
     style="display:none"
     x-init="
        window.addEventListener('beforeinstallprompt', e => {
            e.preventDefault();
            window.__pwaPrompt = e;
            // Only show if not already dismissed in last 7 days
            const dismissed = localStorage.getItem('pwa_banner_dismissed');
            if (!dismissed || (Date.now() - parseInt(dismissed)) > 7*24*60*60*1000) {
                show = true;
            }
        });
     "
     x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="opacity-0 translate-y-4"
     x-transition:enter-end="opacity-100 translate-y-0">
    <div class="bg-primary rounded-2xl shadow-2xl p-4 flex items-center gap-4">
        <img src="{{ asset('images/logo.jpg') }}" class="w-12 h-12 rounded-xl flex-shrink-0 object-cover" alt="BYBHUB">
        <div class="flex-1 min-w-0">
            <p class="font-bold text-white text-sm leading-tight">Add BYBHUB to Home Screen</p>
            <p class="text-blue-300 text-xs mt-0.5">Shop faster — works offline too</p>
        </div>
        <div class="flex items-center gap-2 flex-shrink-0">
            <button @click="if(window.__pwaPrompt){window.__pwaPrompt.prompt();window.__pwaPrompt.userChoice.then(()=>{show=false})}" class="bg-accent hover:bg-accent-dark text-white font-bold text-xs px-3 py-2 rounded-xl transition">
                Install
            </button>
            <button @click="show=false; localStorage.setItem('pwa_banner_dismissed', Date.now())" class="text-blue-300 hover:text-white text-xs px-2 py-2 transition">
                <i class="fas fa-times"></i>
            </button>
        </div>
    </div>
</div>

<script>
// ── BYBHUB PWA: Service Worker + Push + Periodic Sync ────────────────────────
(function () {
    if (!('serviceWorker' in navigator)) return;

    let swReg = null;

    window.addEventListener('load', async () => {
        try {
            swReg = await navigator.serviceWorker.register('/sw.js', { scope: '/' });

            // ── Periodic Background Sync (order status + notifications) ──────
            if ('periodicSync' in swReg) {
                const status = await navigator.permissions.query({ name: 'periodic-background-sync' });
                if (status.state === 'granted') {
                    const tags = await swReg.periodicSync.getTags();
                    if (!tags.includes('bybhub-orders')) {
                        await swReg.periodicSync.register('bybhub-orders',
                            { minInterval: 60 * 60 * 1000 }); // every hour
                    }
                    if (!tags.includes('bybhub-notifications')) {
                        await swReg.periodicSync.register('bybhub-notifications',
                            { minInterval: 30 * 60 * 1000 }); // every 30 min
                    }
                }
            }
        } catch (e) { /* silent — server may not have sw.js yet */ }
    });

    // ── Push notification subscribe (called when user enables notifications) ─
    window.bybPushSubscribe = async function () {
        if (!swReg) return { ok: false, error: 'SW not ready' };
        try {
            const vapidKey = document.head.querySelector('meta[name="vapid-public-key"]')?.content;
            if (!vapidKey) return { ok: false, error: 'No VAPID key' };

            const sub = await swReg.pushManager.subscribe({
                userVisibleOnly: true,
                applicationServerKey: urlBase64ToUint8Array(vapidKey),
            });

            const res = await fetch('/api/push/subscribe', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.head.querySelector('meta[name="csrf-token"]')?.content,
                },
                body: JSON.stringify(sub.toJSON()),
            });
            return { ok: res.ok };
        } catch (e) {
            return { ok: false, error: e.message };
        }
    };

    function urlBase64ToUint8Array(base64String) {
        const padding = '='.repeat((4 - base64String.length % 4) % 4);
        const base64 = (base64String + padding).replace(/-/g, '+').replace(/_/g, '/');
        const raw = window.atob(base64);
        return Uint8Array.from([...raw].map(c => c.charCodeAt(0)));
    }

    // ── App installed event — track analytics ────────────────────────────────
    window.addEventListener('appinstalled', () => {
        console.log('BYBHUB installed as PWA');
        fetch('/api/pwa/installed', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': document.head.querySelector('meta[name="csrf-token"]')?.content },
        }).catch(() => {});
    });
})();
</script>

</body>
</html>
