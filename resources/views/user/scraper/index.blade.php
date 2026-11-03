@extends('layouts.app')
@section('title', __('app.scraper'))
@section('content')

<div class="max-w-2xl mx-auto space-y-5 px-0 sm:px-0">

    {{-- ── Hero Card ──────────────────────────────────────────────────────── --}}
    <div class="rounded-2xl text-white relative overflow-hidden"
         style="background:linear-gradient(135deg,#1A3A6B 0%,#2451A3 100%)">

        {{-- Decorative glow --}}
        <div class="absolute top-0 right-0 w-48 h-48 rounded-full opacity-10 pointer-events-none"
             style="background:radial-gradient(circle,#F47B20,transparent);transform:translate(30%,-30%)"></div>

        <div class="relative z-10 p-5 sm:p-7">

            {{-- Header row --}}
            <div class="flex items-center gap-3 mb-5">
                <div class="w-10 h-10 bg-white/15 rounded-xl flex items-center justify-center flex-shrink-0">
                    <i class="fas fa-search text-white text-base"></i>
                </div>
                <div class="flex-1 min-w-0">
                    <h2 class="text-lg font-bold leading-tight" style="font-family:Poppins,sans-serif">
                        Product Finder
                    </h2>
                    <p class="text-blue-200 text-xs mt-0.5">Paste a 1688, Taobao or Alibaba link
                        @auth
                        @if(auth()->user()->is_admin)
                        (+ AliExpress for admins)
                        @endif
                        @endauth
                    </p>
                </div>
                @if($dailyLimit > 0)
                <div class="flex-shrink-0 text-center bg-white/10 border {{ $remaining === 0 ? 'border-red-400/60' : 'border-white/20' }} rounded-xl px-3 py-1.5 min-w-[52px]">
                    <p class="font-black text-base leading-tight {{ $remaining === 0 ? 'text-red-300' : 'text-white' }}"
                       style="font-family:Poppins,sans-serif">{{ $remaining }}</p>
                    <p class="text-[9px] text-blue-300 font-semibold leading-tight">left today</p>
                </div>
                @endif
            </div>

            {{-- Tab switcher --}}
            <div x-data="{
                tab: 'url',
                detectedUrl: '',
                detectedHost: '',
                showDetected: false,
                clipboardSupported: typeof navigator !== 'undefined' && !!navigator.clipboard && typeof navigator.clipboard.readText === 'function',

                isProductLink(text) {
                    if (!text) return null;
                    const m = String(text).match(/https?:\/\/[^\s<>()\]\x22']+/i);
                    if (!m) return null;
                    const url = m[0];
                    if (!/(1688|taobao|tmall|alibaba|aliexpress)\.com/i.test(url)) return null;
                    let host = url;
                    try { host = new URL(url).hostname.replace(/^www\./, ''); } catch(e) {}
                    return { url, host };
                },

                async readClipboard() {
                    if (!this.clipboardSupported) return;
                    try {
                        const text = await navigator.clipboard.readText();
                        const found = this.isProductLink(text);
                        if (found) {
                            this.detectedUrl = found.url;
                            this.detectedHost = found.host;
                            this.showDetected = true;
                        }
                    } catch(e) { /* permission denied or needs user gesture — ignore silently */ }
                },

                async pasteFromClipboard() {
                    const input = document.getElementById('scraper-url-input');
                    if (!this.clipboardSupported) { input.focus(); return; }
                    try {
                        const text = await navigator.clipboard.readText();
                        const found = this.isProductLink(text);
                        if (found) { input.value = found.url; this.showDetected = false; }
                        else if (text) { input.value = text.trim(); }
                        input.focus();
                    } catch(e) { input.focus(); }
                },

                applyDetected() {
                    document.getElementById('scraper-url-input').value = this.detectedUrl;
                    this.showDetected = false;
                    document.getElementById('scraper-form').submit();
                }
            }" x-init="$nextTick(() => {
                setTimeout(() => readClipboard(), 500);
                document.addEventListener('visibilitychange', () => { if(!document.hidden) readClipboard(); });
            })">
                <div class="flex gap-2 mb-4">
                    <button type="button" @click="tab='url'"
                        :class="tab==='url' ? 'bg-white text-primary shadow' : 'bg-white/15 text-white'"
                        class="flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl text-xs font-bold transition flex-1 active:scale-95">
                        <i class="fas fa-link text-[11px]"></i> Paste URL
                    </button>
                    <button type="button" @click="tab='image'"
                        :class="tab==='image' ? 'bg-white text-primary shadow' : 'bg-white/15 text-white'"
                        class="flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl text-xs font-bold transition flex-1 active:scale-95">
                        <i class="fas fa-camera text-[11px]"></i> Image Search
                    </button>
                </div>

                {{-- URL tab --}}
                <div x-show="tab==='url'" x-transition>
                    {{-- Clipboard-detected product link banner --}}
                    <div x-show="showDetected" x-cloak x-transition style="display:none"
                         class="mb-3 bg-green-400/15 border border-green-300/40 rounded-2xl p-3 flex items-center gap-3">
                        <div class="w-9 h-9 bg-green-400/25 rounded-xl flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-clipboard-check text-green-200 text-sm"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-xs font-bold text-white leading-tight">Product link detected on your clipboard</p>
                            <p class="text-[11px] text-blue-200 truncate" x-text="detectedHost"></p>
                        </div>
                        <button type="button" @click="applyDetected()"
                                class="flex-shrink-0 bg-accent hover:bg-accent-dark active:scale-95 text-white font-bold text-xs px-4 py-2.5 rounded-xl transition flex items-center gap-1.5">
                            <i class="fas fa-bolt text-[10px]"></i> Fetch
                        </button>
                        <button type="button" @click="showDetected=false" class="text-white/60 hover:text-white text-xs px-1" aria-label="Dismiss">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>

                    <form id="scraper-form"
                          action="{{ route('user.scraper.fetch') }}"
                          method="POST"
                          autocomplete="off">
                        @csrf

                        {{-- URL input + button stacked on mobile, side-by-side on desktop --}}
                        <div class="flex flex-col sm:flex-row gap-2 mb-3">
                            <div class="relative flex-1">
                                <i class="fas fa-link absolute left-4 top-1/2 -translate-y-1/2 text-blue-300 text-xs pointer-events-none"></i>
                                <input type="url"
                                       name="url"
                                       id="scraper-url-input"
                                       required
                                       value="{{ old('url', request('url')) }}"
                                       inputmode="url"
                                       autocapitalize="off"
                                       autocorrect="off"
                                       spellcheck="false"
                                       placeholder="https://detail.1688.com/offer/..."
                                       class="w-full pl-10 pr-4 py-3.5 bg-white/15 border border-white/25 rounded-2xl text-sm outline-none placeholder-blue-300/70 text-white focus:bg-white/20 focus:border-white/50 transition"
                                       style="-webkit-text-fill-color:white">
                            </div>
                            <button type="submit"
                                    id="scraper-btn"
                                    class="bg-accent hover:bg-accent-dark active:scale-95 text-white px-6 py-3.5 rounded-2xl font-bold text-sm flex items-center justify-center gap-2 transition btn-glow flex-shrink-0 w-full sm:w-auto">
                                <i class="fas fa-arrow-right" id="scraper-btn-icon"></i>
                                <span id="scraper-btn-text">Fetch Product</span>
                            </button>
                        </div>

                        {{-- Category picker --}}
                        <div class="flex flex-wrap items-center gap-2">
                            <label class="text-xs text-blue-300 font-medium flex-shrink-0">Category:</label>
                            <select name="category_id"
                                    class="flex-1 min-w-0 bg-white/15 border border-white/20 rounded-xl px-3 py-2 text-xs text-white focus:outline-none focus:border-white/40 transition">
                                <option value="">— detect automatically —</option>
                                @foreach($categories as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->getTranslatedName() }}</option>
                                @endforeach
                            </select>
                        </div>
                    </form>

                    {{-- Manual paste-from-clipboard (reliable on iOS Safari which requires a user gesture) --}}
                    <button type="button" @click="pasteFromClipboard()"
                            x-show="clipboardSupported"
                            class="mt-2 w-full inline-flex items-center justify-center gap-2 bg-white/10 hover:bg-white/15 active:scale-95 text-white text-xs font-semibold py-2.5 rounded-xl transition">
                        <i class="fas fa-clipboard text-[11px]"></i> Paste from clipboard
                    </button>
                </div>

                {{-- Image Search tab --}}
                <div x-show="tab==='image'" x-transition class="text-center py-3">
                    <div class="w-12 h-12 bg-white/15 rounded-2xl flex items-center justify-center mx-auto mb-3">
                        <i class="fas fa-camera text-white text-xl"></i>
                    </div>
                    <p class="text-sm text-blue-100 mb-4 leading-relaxed px-2">
                        Take or upload a product photo on 1688's visual search to find the exact item — then paste the URL here.
                    </p>
                    <a href="https://s.1688.com/youyuan/index.htm" target="_blank" rel="noopener"
                       class="inline-flex items-center gap-2 bg-accent hover:bg-accent-dark text-white font-bold px-6 py-3 rounded-2xl text-sm transition btn-glow active:scale-95">
                        <i class="fas fa-camera"></i> Open 1688 Image Search
                        <i class="fas fa-external-link-alt text-[10px] opacity-70"></i>
                    </a>
                    <p class="text-xs text-blue-300 mt-3 px-4">
                        Find your product → copy the URL → switch to "Paste URL" tab
                    </p>
                </div>
            </div>

        </div>
    </div>

    {{-- ── Supported Platforms ─────────────────────────────────────────────── --}}
    @php
    $platforms = [
        ['domain'=>'1688.com',    'emoji'=>'🏭','label'=>'Wholesale',   'color'=>'from-red-50 to-orange-50',  'border'=>'border-red-100'],
        ['domain'=>'Taobao.com',  'emoji'=>'🛍', 'label'=>'Consumer',   'color'=>'from-orange-50 to-yellow-50','border'=>'border-orange-100'],
        ['domain'=>'Alibaba.com', 'emoji'=>'🌐','label'=>'B2B Global',  'color'=>'from-yellow-50 to-amber-50', 'border'=>'border-yellow-100'],
    ];
    @endphp
    @auth @if(auth()->user()->is_admin)
    @php $platforms[] = ['domain'=>'AliExpress','emoji'=>'⚡','label'=>'Admin Only','color'=>'from-purple-50 to-indigo-50','border'=>'border-purple-200']; @endphp
    @endif @endauth
    <div class="grid grid-cols-3 gap-3">
        @foreach($platforms as $src)
        <div class="bg-gradient-to-br {{ $src['color'] }} border {{ $src['border'] }} rounded-2xl p-3 text-center">
            <div class="text-2xl mb-1.5">{{ $src['emoji'] }}</div>
            <p class="font-bold text-primary text-xs leading-tight">{{ $src['domain'] }}</p>
            <p class="text-[10px] text-gray-400 mt-0.5">{{ $src['label'] }}</p>
            <span class="inline-flex items-center gap-0.5 mt-1.5 text-[10px] text-green-600 font-semibold">
                <i class="fas fa-check-circle text-green-500 text-[9px]"></i> Ready
            </span>
        </div>
        @endforeach
    </div>

    {{-- ── How it works ────────────────────────────────────────────────────── --}}
    <div class="bg-white rounded-2xl shadow-card p-5">
        <h3 class="font-bold text-primary mb-4 flex items-center gap-2 text-sm">
            <div class="w-7 h-7 bg-yellow-100 rounded-lg flex items-center justify-center flex-shrink-0">
                <i class="fas fa-lightbulb text-yellow-500 text-xs"></i>
            </div>
            How to Order from China
        </h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            @foreach([
                ['n'=>'1','title'=>'Find Your Product','desc'=>'Go to 1688.com or Taobao.com and find what you want to buy.'],
                ['n'=>'2','title'=>'Copy the Link','desc'=>'Tap Share → Copy link, or copy the URL from your browser.'],
                ['n'=>'3','title'=>'Paste & Fetch','desc'=>'Paste the link above and tap "Fetch Product" to load full details.'],
                ['n'=>'4','title'=>'Add to Cart','desc'=>'Pick your size/colour, set quantity, add to cart and checkout.'],
            ] as $step)
            <div class="flex items-start gap-3">
                <div class="w-8 h-8 bg-gradient-to-br from-primary to-primary-light text-white rounded-xl flex items-center justify-center font-black text-xs flex-shrink-0">{{ $step['n'] }}</div>
                <div>
                    <p class="font-semibold text-gray-800 text-sm">{{ $step['title'] }}</p>
                    <p class="text-xs text-gray-500 mt-0.5 leading-relaxed">{{ $step['desc'] }}</p>
                </div>
            </div>
            @endforeach
        </div>
    </div>

</div>

{{-- ── Full-screen Loading Overlay ────────────────────────────────────────── --}}
{{-- Pure CSS/HTML — no Alpine dependency so it works on all mobile browsers --}}
<div id="scraper-overlay"
     style="display:none;position:fixed;inset:0;z-index:9999;
            background:linear-gradient(135deg,rgba(26,58,107,0.97) 0%,rgba(13,29,64,0.97) 100%);
            -webkit-backdrop-filter:blur(8px);backdrop-filter:blur(8px);
            flex-direction:column;align-items:center;justify-content:center;
            padding:24px;text-align:center">

    {{-- Pulse ring + icon --}}
    <div style="position:relative;width:88px;height:88px;margin:0 auto 24px">
        <div style="position:absolute;inset:0;border-radius:50%;background:rgba(244,123,32,0.25);
                    animation:ping 1.4s cubic-bezier(0,0,0.2,1) infinite"></div>
        <div style="position:relative;width:88px;height:88px;background:rgba(255,255,255,0.12);
                    border-radius:50%;border:2px solid rgba(255,255,255,0.25);
                    display:flex;align-items:center;justify-content:center">
            <i class="fas fa-search" style="color:white;font-size:32px"></i>
        </div>
    </div>

    {{-- Title --}}
    <h3 style="color:white;font-size:20px;font-weight:800;margin-bottom:8px;font-family:Poppins,sans-serif">
        Fetching Product
    </h3>

    {{-- Cycling status text --}}
    <p id="scraper-status"
       style="color:rgba(147,197,253,1);font-size:14px;min-height:44px;
              display:flex;align-items:center;justify-content:center;
              transition:opacity .4s ease;padding:0 16px;line-height:1.5">
        Connecting to the supplier platform…
    </p>

    {{-- Progress bar --}}
    <div style="width:100%;max-width:280px;margin:16px auto 0;
                background:rgba(255,255,255,0.15);border-radius:99px;height:4px;overflow:hidden">
        <div id="scraper-progress"
             style="height:100%;background:#F47B20;border-radius:99px;
                    width:5%;transition:width .7s ease"></div>
    </div>

    <p style="color:rgba(147,197,253,0.7);font-size:11px;margin-top:10px">
        This usually takes 5–15 seconds
    </p>

    {{-- Bounce dots --}}
    <div style="display:flex;justify-content:center;gap:6px;margin-top:16px">
        <span style="width:8px;height:8px;background:rgba(255,255,255,0.4);border-radius:50%;
                     animation:bounce 1.2s ease-in-out infinite;animation-delay:0ms;display:inline-block"></span>
        <span style="width:8px;height:8px;background:rgba(255,255,255,0.4);border-radius:50%;
                     animation:bounce 1.2s ease-in-out infinite;animation-delay:200ms;display:inline-block"></span>
        <span style="width:8px;height:8px;background:rgba(255,255,255,0.4);border-radius:50%;
                     animation:bounce 1.2s ease-in-out infinite;animation-delay:400ms;display:inline-block"></span>
    </div>

</div>

<style>
@keyframes ping {
    75%, 100% { transform: scale(1.8); opacity: 0; }
}
@keyframes bounce {
    0%, 100% { transform: translateY(0); }
    50%       { transform: translateY(-6px); }
}
/* Prevent body scroll when overlay is active */
body.scraper-loading { overflow: hidden; position: fixed; width: 100%; }
</style>

@push('scripts')
<script>
(function () {
    var form     = document.getElementById('scraper-form');
    var overlay  = document.getElementById('scraper-overlay');
    var btn      = document.getElementById('scraper-btn');
    var btnIcon  = document.getElementById('scraper-btn-icon');
    var btnText  = document.getElementById('scraper-btn-text');
    var statusEl = document.getElementById('scraper-status');
    var progEl   = document.getElementById('scraper-progress');

    if (!form || !overlay) return;

    var steps = [
        { msg: 'Connecting to the supplier platform…',  pct: 12 },
        { msg: 'Bypassing bot protection…',              pct: 28 },
        { msg: 'Reading product details…',               pct: 48 },
        { msg: 'Extracting variants and prices…',        pct: 67 },
        { msg: 'Translating to English…',                pct: 85 },
        { msg: 'Saving product — almost done…',          pct: 96 },
    ];
    var durations = [2200, 3000, 3000, 2800, 2500, 99999];

    function startOverlay() {
        // Show overlay
        overlay.style.display = 'flex';
        document.body.classList.add('scraper-loading');

        // Disable button
        btn.disabled = true;
        btn.style.opacity = '0.7';
        btnIcon.className = 'fas fa-spinner fa-spin';
        btnText.textContent = 'Fetching…';

        // Cycle through status messages
        var i = 0;
        function next() {
            if (i >= steps.length) return;
            // Fade out
            statusEl.style.opacity = '0';
            setTimeout(function () {
                statusEl.textContent = steps[i].msg;
                progEl.style.width   = steps[i].pct + '%';
                statusEl.style.opacity = '1';
                i++;
                if (i < steps.length) {
                    setTimeout(next, durations[i - 1]);
                }
            }, 200);
        }
        next();
    }

    form.addEventListener('submit', function (e) {
        var urlInput = document.getElementById('scraper-url-input');
        if (!urlInput || !urlInput.value.trim()) return;
        startOverlay();
        // Don't prevent default — let the form submit naturally
    });

    // Also handle paste-and-go on mobile (some browsers submit on paste)
    var urlInput = document.getElementById('scraper-url-input');
    if (urlInput) {
        // On iOS Safari, tapping the submit button sometimes fires touchend
        // before the form submit event — catch both
        btn.addEventListener('touchend', function () {
            // Small delay to let the form's own submit event fire first
            setTimeout(function () {
                if (!overlay.style.display || overlay.style.display === 'none') {
                    if (urlInput.value.trim()) startOverlay();
                }
            }, 50);
        });
    }
})();
</script>
@endpush
@endsection
