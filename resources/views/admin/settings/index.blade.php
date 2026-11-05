@extends('layouts.admin')

@section('title','Settings')
@section('content')
@php $tab = request('tab','general'); @endphp

{{-- Tab navigation --}}
<div class="flex flex-wrap gap-2 mb-6">
@foreach([
    'general'       => ['fas fa-globe',         'General',              'gray'],
    'payment'       => ['fas fa-credit-card',    'Payments',             'green'],
    'fees'          => ['fas fa-percent',        'Fees',                 'blue'],
    'bnpl'          => ['fas fa-hand-holding-usd','BNPL',               'purple'],
    'notifications' => ['fas fa-bell',           'Notifications',        'purple'],
    'launch'        => ['fas fa-rocket',         'Launch & Promo',       'orange'],
    'vendor'        => ['fas fa-store',          'Vendor Marketplace',   'teal'],
    'spinwin'       => ['fas fa-dharmachakra',   'Spin & Win',           'pink'],
] as $key => [$icon,$label,$color])
<a href="?tab={{ $key }}"
   class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold border transition
          {{ $tab===$key ? 'bg-primary text-white border-primary shadow-sm' : 'bg-white text-gray-500 border-gray-200 hover:border-primary hover:text-primary' }}">
    <i class="{{ $icon }} text-xs"></i> {{ $label }}
</a>
@endforeach
</div>

<form action="{{ route('admin.settings.update') }}" method="POST" class="max-w-3xl space-y-5">
@csrf
{{-- Pass current tab so the controller only zeroes checkboxes belonging to this tab --}}
<input type="hidden" name="_tab" value="{{ $tab }}">

{{-- ══════════════ GENERAL ══════════════ --}}
@if($tab==='general')
<div class="bg-white rounded-2xl shadow-card p-6">
    <h3 class="font-bold text-primary text-base mb-5 flex items-center gap-2"><i class="fas fa-globe text-gray-400 text-sm"></i> Site Information</h3>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        @foreach(['site_name'=>['Site Name','BYBHUB'],'site_tagline'=>['Tagline','Bringing China Closer to You'],'contact_email'=>['Contact Email','hello@bybimport.com'],'contact_phone'=>['Support Phone','+2348012345678'],'contact_whatsapp'=>['WhatsApp Number (with country code)','2348012345678'],'whatsapp_support_number'=>['WhatsApp Chat Bubble Number','2348012345678']] as $k=>[$lbl,$ph])
        <div>
            <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1.5">{{ $lbl }}</label>
            <input type="text" name="{{ $k }}" value="{{ $settings[$k]??'' }}" placeholder="{{ $ph }}"
                class="w-full border-2 border-gray-100 bg-gray-50 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-primary focus:bg-white transition">
        </div>
        @endforeach
        <div class="md:col-span-2">
            <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1.5">About Us Text</label>
            <textarea name="about_us_text" rows="3" placeholder="Short description shown on About page..."
                class="w-full border-2 border-gray-100 bg-gray-50 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-primary focus:bg-white transition resize-none">{{ $settings['about_us_text']??'' }}</textarea>
        </div>
    </div>
</div>
<div class="bg-white rounded-2xl shadow-card p-6">
    <h3 class="font-bold text-primary text-base mb-5 flex items-center gap-2"><i class="fas fa-share-alt text-gray-400 text-sm"></i> Social Media Links</h3>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        @foreach(['social_instagram'=>['fab fa-instagram text-pink-500','Instagram URL','https://instagram.com/bybhub'],'social_whatsapp_group'=>['fab fa-whatsapp text-green-500','WhatsApp Group Link','https://chat.whatsapp.com/...'],'social_telegram_channel'=>['fab fa-telegram text-blue-500','Telegram Channel','https://t.me/bybhub'],'social_facebook'=>['fab fa-facebook text-blue-600','Facebook Page URL','https://facebook.com/bybhub'],'social_twitter'=>['fab fa-twitter text-sky-500','Twitter / X URL','https://x.com/bybhub'],'social_tiktok'=>['fab fa-tiktok','TikTok URL','https://tiktok.com/@bybhub']] as $k=>[$ico,$lbl,$ph])
        <div>
            <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1.5 flex items-center gap-1.5"><i class="{{ $ico }} text-xs"></i> {{ $lbl }}</label>
            <input type="url" name="{{ $k }}" value="{{ $settings[$k]??'' }}" placeholder="{{ $ph }}"
                class="w-full border-2 border-gray-100 bg-gray-50 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-primary focus:bg-white transition">
        </div>
        @endforeach
    </div>
</div>
@endif

{{-- ══════════════ PAYMENTS ══════════════ --}}
@if($tab==='payment')
<div class="bg-white rounded-2xl shadow-card p-6">
    <div class="flex items-start justify-between mb-5">
        <div><h3 class="font-bold text-primary text-base flex items-center gap-2"><i class="fas fa-university text-green-500 text-sm"></i> Manual Bank Transfer</h3>
        <p class="text-xs text-gray-400 mt-0.5">Users transfer to this account and upload proof. Admin approves manually.</p></div>
        <label class="flex items-center gap-2 cursor-pointer shrink-0"><span class="text-xs font-bold text-gray-500">Enable</span>
        <input type="hidden" name="funding_manual_enabled" value="0">
        <input type="checkbox" name="funding_manual_enabled" value="1" {{ ($settings['funding_manual_enabled']??'0')==='1'?'checked':'' }} class="w-5 h-5 accent-green-500"></label>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        @foreach(['funding_bank_name'=>['Bank Name','GTBank'],'funding_bank_account_number'=>['Account Number','0123456789'],'funding_bank_account_name'=>['Account Name','BYBHUB Limited'],'funding_manual_min_amount'=>['Min Transfer Amount (₦)','500']] as $k=>[$lbl,$ph])
        <div><label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1.5">{{ $lbl }}</label>
        <input type="text" name="{{ $k }}" value="{{ $settings[$k]??'' }}" placeholder="{{ $ph }}"
            class="w-full border-2 border-gray-100 bg-gray-50 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-primary focus:bg-white transition"></div>
        @endforeach
        <div class="md:col-span-2"><label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1.5">Transfer Instructions (shown to user)</label>
        <textarea name="funding_bank_instructions" rows="2" placeholder="e.g. Use your email address as payment reference..."
            class="w-full border-2 border-gray-100 bg-gray-50 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-primary focus:bg-white transition resize-none">{{ $settings['funding_bank_instructions']??'' }}</textarea></div>
    </div>
</div>

<div class="bg-white rounded-2xl shadow-card p-6 border-l-4 border-primary">
    <div class="flex items-start justify-between mb-5">
        <div><h3 class="font-bold text-primary text-base flex items-center gap-2"><i class="fas fa-university text-primary text-sm"></i> Monnify — Virtual Accounts</h3>
        <p class="text-xs text-gray-400 mt-0.5">Dedicated virtual accounts for Nigerian users. Auto-credits wallet on transfer.</p></div>
        <label class="flex items-center gap-2 cursor-pointer shrink-0"><span class="text-xs font-bold text-gray-500">Enable</span>
        <input type="hidden" name="funding_monnify_enabled" value="0">
        <input type="checkbox" name="funding_monnify_enabled" value="1" {{ ($settings['funding_monnify_enabled']??'1')==='1'?'checked':'' }} class="w-5 h-5 accent-primary"></label>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        @foreach(['monnify_api_key'=>'API Key','monnify_secret_key'=>'Secret Key','monnify_contract_code'=>'Contract Code'] as $k=>$lbl)
        <div><label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1.5">{{ $lbl }}</label>
        <input type="password" name="{{ $k }}" value="{{ $settings[$k]??'' }}" autocomplete="off"
            class="w-full border-2 border-gray-100 bg-gray-50 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-primary focus:bg-white transition font-mono"></div>
        @endforeach
        <div><label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1.5">Environment</label>
        <select name="monnify_base_url" class="w-full border-2 border-gray-100 bg-gray-50 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-primary">
            <option value="https://sandbox.monnify.com" {{ ($settings['monnify_base_url']??'')==='https://sandbox.monnify.com'?'selected':'' }}>🧪 Sandbox (Testing)</option>
            <option value="https://api.monnify.com" {{ ($settings['monnify_base_url']??'')==='https://api.monnify.com'?'selected':'' }}>🚀 Live (Production)</option>
        </select></div>
    </div>
</div>

<div class="bg-white rounded-2xl shadow-card p-6 border-l-4 border-accent">
    <div class="flex items-start justify-between mb-5">
        <div><h3 class="font-bold text-primary text-base flex items-center gap-2"><i class="fas fa-credit-card text-accent text-sm"></i> Paystack — Card & Bank Payments</h3>
        <p class="text-xs text-gray-400 mt-0.5">Card payments and virtual accounts for Nigeria & Ghana. Get keys from <a href="https://dashboard.paystack.com/#/settings/developer" target="_blank" class="text-accent underline">Paystack dashboard</a>.</p></div>
        <label class="flex items-center gap-2 cursor-pointer shrink-0"><span class="text-xs font-bold text-gray-500">Enable</span>
        <input type="hidden" name="funding_paystack_enabled" value="0">
        <input type="checkbox" name="funding_paystack_enabled" value="1" {{ ($settings['funding_paystack_enabled']??'1')==='1'?'checked':'' }} class="w-5 h-5 accent-accent"></label>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div><label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1.5">Public Key</label>
        <input type="text" name="paystack_public_key" value="{{ $settings['paystack_public_key']??'' }}" placeholder="pk_live_... or pk_test_..." autocomplete="off"
            class="w-full border-2 border-gray-100 bg-gray-50 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-accent focus:bg-white transition font-mono"></div>
        <div><label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1.5">Secret Key</label>
        <input type="password" name="paystack_secret_key" value="{{ $settings['paystack_secret_key']??'' }}" placeholder="sk_live_... or sk_test_..." autocomplete="off"
            class="w-full border-2 border-gray-100 bg-gray-50 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-accent focus:bg-white transition font-mono"></div>
        <div><label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1.5">Preferred DVA Bank</label>
        <select name="paystack_preferred_bank" class="w-full border-2 border-gray-100 bg-gray-50 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-accent">
            @foreach(['wema-bank'=>'Wema Bank','titan-paystack'=>'Titan Bank','sterling-bank'=>'Sterling Bank','test-bank'=>'Test Bank (Sandbox)'] as $v=>$l)
            <option value="{{ $v }}" {{ ($settings['paystack_preferred_bank']??'wema-bank')===$v?'selected':'' }}>{{ $l }}</option>
            @endforeach
        </select></div>
    </div>
    @if(str_starts_with($settings['paystack_public_key']??'','pk_test_'))
    <div class="mt-3 bg-yellow-50 border border-yellow-200 rounded-xl px-4 py-2.5 text-xs text-yellow-700 flex items-center gap-2">
        <i class="fas fa-exclamation-triangle text-yellow-500"></i> You are using <strong class="ml-1">test keys</strong>. Switch to live keys before going live.
    </div>
    @endif
</div>
@endif

{{-- ══════════════ FEES ══════════════ --}}
@if($tab==='fees')
<div class="bg-white rounded-2xl shadow-card p-6">
    <h3 class="font-bold text-primary text-base mb-5 flex items-center gap-2"><i class="fas fa-percent text-gray-400 text-sm"></i> Transaction & Service Fees</h3>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        @foreach([
            'service_fee_percent'       =>['China Service Fee %','5','Applied on each China order product value at checkout.'],
            'supplier_payment_flat_fee' =>['Supplier Payment Fee (₦)','0','Flat fee per Alipay supplier payment. Set 0 for free.'],
            'withdrawal_flat_fee'       =>['Wallet Withdrawal Fee (₦)','0','Flat fee per user wallet withdrawal. Set 0 for free.'],
            'service_fee_photo'         =>['Product Photo Request (₦)','500','Fee for photo requests at China warehouse.'],
            'service_fee_packaging'     =>['Special Packaging (₦)','300','Fee for packaging requests at China warehouse.'],
            'duty_vat_percent'          =>['Duty / VAT % (estimate)','0','Optional flat duty/VAT % applied to product value in the landed-cost estimator. Set 0 to hide duty.'],
            'referral_bonus_amount'     =>['Referral Bonus (₦)','500','Credit paid to referrer when referral qualifies.'],
            'referral_min_deposit'      =>['Min. Spend for Referral (₦)','10000','Referred user must reach this spend total first.'],
        ] as $k=>[$lbl,$ph,$hint])
        <div>
            <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1.5">{{ $lbl }}</label>
            <input type="number" name="{{ $k }}" value="{{ $settings[$k]??$ph }}" step="any" min="0"
                class="w-full border-2 border-gray-100 bg-gray-50 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-primary focus:bg-white transition">
            <p class="text-[10px] text-gray-400 mt-1">{{ $hint }}</p>
        </div>
        @endforeach
    </div>
</div>

<div class="bg-white rounded-2xl shadow-card p-6 border-l-4 border-orange-400">
    <div class="flex items-start justify-between mb-5">
        <div><h3 class="font-bold text-primary text-base flex items-center gap-2"><i class="fas fa-warehouse text-orange-500 text-sm"></i> Nigeria Warehouse Storage Fines</h3>
        <p class="text-xs text-gray-400 mt-0.5">Charged daily after the grace period. Cron runs every morning at 9AM.</p></div>
        <label class="flex items-center gap-2 cursor-pointer shrink-0"><span class="text-xs font-bold text-gray-500">Enable</span>
        <input type="hidden" name="storage_fine_enabled" value="0">
        <input type="checkbox" name="storage_fine_enabled" value="1" {{ ($settings['storage_fine_enabled']??'1')==='1'?'checked':'' }} class="w-5 h-5 accent-orange-500"></label>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div><label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1.5">Free Days (Grace Period)</label>
        <input type="number" name="storage_fine_free_days" value="{{ $settings['storage_fine_free_days']??7 }}" min="1" step="1"
            class="w-full border-2 border-gray-100 bg-gray-50 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-orange-400 focus:bg-white transition">
        <p class="text-[10px] text-gray-400 mt-1">Days before fines start. Recommended: 7.</p></div>
        <div><label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1.5">Fine Per Day (₦)</label>
        <input type="number" name="storage_fine_per_day" value="{{ $settings['storage_fine_per_day']??200 }}" min="0" step="50"
            class="w-full border-2 border-gray-100 bg-gray-50 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-orange-400 focus:bg-white transition">
        <p class="text-[10px] text-gray-400 mt-1">Amount charged per day after grace period ends.</p></div>
    </div>
</div>

{{-- ── Supplier Payments (Alipay) — enable + min/max limits ── --}}
<div class="bg-white rounded-2xl shadow-card p-6 border-l-4 border-blue-500">
    <div class="flex items-start justify-between mb-5">
        <div><h3 class="font-bold text-primary text-base flex items-center gap-2"><i class="fab fa-alipay text-blue-500 text-sm"></i> Supplier Payments (Alipay)</h3>
        <p class="text-xs text-gray-400 mt-0.5">Let users pay Chinese suppliers via Alipay from their wallet. Limits are in CNY (¥). Set max to 0 for no maximum.</p></div>
        <label class="flex items-center gap-2 cursor-pointer shrink-0"><span class="text-xs font-bold text-gray-500">Enable</span>
        <input type="hidden" name="supplier_payments_enabled" value="0">
        <input type="checkbox" name="supplier_payments_enabled" value="1" {{ ($settings['supplier_payments_enabled']??'1')==='0'?'':'checked' }} class="w-5 h-5 accent-blue-500"></label>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div><label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1.5">Min Supplier Payment (¥ CNY)</label>
        <input type="number" name="supplier_payment_min_rmb" value="{{ $settings['supplier_payment_min_rmb'] ?? '' }}" min="0" step="0.01" placeholder="10"
            class="w-full border-2 border-gray-100 bg-gray-50 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-blue-500 focus:bg-white transition">
        <p class="text-[10px] text-gray-400 mt-1">Lowest RMB amount a user can pay a supplier in one request. Set 0 for no minimum.</p></div>
        <div><label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1.5">Max Supplier Payment (¥ CNY)</label>
        <input type="number" name="supplier_payment_max_rmb" value="{{ $settings['supplier_payment_max_rmb'] ?? '' }}" min="0" step="0.01" placeholder="0"
            class="w-full border-2 border-gray-100 bg-gray-50 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-blue-500 focus:bg-white transition">
        <p class="text-[10px] text-gray-400 mt-1">Highest RMB amount allowed. Set 0 for no maximum.</p></div>
    </div>
</div>
@endif

{{-- ══════════════ BNPL PROVIDERS ══════════════ --}}
@if($tab==='bnpl')

{{-- ── Klump ── --}}
<div class="bg-white rounded-2xl shadow-card p-6 border-l-4 border-green-500">
    <div class="flex items-start justify-between mb-4">
        <div>
            <h3 class="font-bold text-primary text-base flex items-center gap-2">
                <i class="fas fa-hand-holding-usd text-green-500 text-sm"></i> Klump — Buy Now Pay Later
            </h3>
            <p class="text-xs text-gray-400 mt-0.5">
                Instalment payments for Nigerian users. Get API keys at
                <a href="https://dashboard.useklump.com" target="_blank" class="text-accent underline">dashboard.useklump.com</a>.
                SDK: <code class="bg-gray-100 text-xs px-1 rounded">https://js.useklump.com/klump.js</code>
            </p>
        </div>
        <label class="flex items-center gap-2 cursor-pointer shrink-0">
            <span class="text-xs font-bold text-gray-500">Enable</span>
            <input type="hidden" name="bnpl_klump_enabled" value="0">
            <input type="checkbox" name="bnpl_klump_enabled" value="1"
                {{ ($settings['bnpl_klump_enabled']??'0')==='1'?'checked':'' }} class="w-5 h-5 accent-green-500">
        </label>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1.5">Public Key</label>
            <input type="text" name="bnpl_klump_public_key"
                value="{{ $settings['bnpl_klump_public_key']??'' }}"
                placeholder="klp_pk_live_xxxxxxxxxxxxxxxx"
                autocomplete="off"
                class="w-full border-2 border-gray-100 bg-gray-50 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-green-400 focus:bg-white transition font-mono">
            <p class="text-[10px] text-gray-400 mt-1">Used in the browser to initialise the Klump widget.</p>
        </div>
        <div>
            <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1.5">Secret Key</label>
            <input type="password" name="bnpl_klump_secret_key"
                value="{{ $settings['bnpl_klump_secret_key']??'' }}"
                placeholder="klp_sk_live_xxxxxxxxxxxxxxxx"
                autocomplete="off"
                class="w-full border-2 border-gray-100 bg-gray-50 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-green-400 focus:bg-white transition font-mono">
            <p class="text-[10px] text-gray-400 mt-1">Used server-side to verify callbacks.</p>
        </div>
        <div>
            <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1.5">Environment</label>
            <select name="bnpl_klump_env" class="w-full border-2 border-gray-100 bg-gray-50 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-green-400">
                <option value="test"  {{ ($settings['bnpl_klump_env']??'test')==='test' ?'selected':'' }}>🧪 Test / Sandbox</option>
                <option value="live"  {{ ($settings['bnpl_klump_env']??'')==='live'     ?'selected':'' }}>🚀 Live / Production</option>
            </select>
        </div>
        <div>
            <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1.5">Min. Order Amount ({{ $settings['site_currency']??'NGN' }})</label>
            <input type="number" name="bnpl_klump_min_amount"
                value="{{ $settings['bnpl_klump_min_amount']??5000 }}" min="0" step="100"
                class="w-full border-2 border-gray-100 bg-gray-50 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-green-400 focus:bg-white transition">
            <p class="text-[10px] text-gray-400 mt-1">Klump widget won't show for orders below this amount.</p>
        </div>
    </div>
    @if(($settings['bnpl_klump_env']??'test')==='test')
    <div class="mt-3 bg-yellow-50 border border-yellow-200 rounded-xl px-4 py-2.5 text-xs text-yellow-700 flex items-center gap-2">
        <i class="fas fa-exclamation-triangle text-yellow-500"></i>
        Using <strong>test keys</strong>. Switch to Live before going live with real customers.
    </div>
    @endif
</div>

{{-- ── Credpal ── --}}
<div class="bg-white rounded-2xl shadow-card p-6 border-l-4 border-indigo-500">
    <div class="flex items-start justify-between mb-4">
        <div>
            <h3 class="font-bold text-primary text-base flex items-center gap-2">
                <i class="fas fa-credit-card text-indigo-500 text-sm"></i> Credpal — Credit-Based BNPL
            </h3>
            <p class="text-xs text-gray-400 mt-0.5">
                Credit financing for Nigerian consumers. Docs at
                <a href="https://developer.credpal.com" target="_blank" class="text-accent underline">developer.credpal.com</a>.
            </p>
        </div>
        <label class="flex items-center gap-2 cursor-pointer shrink-0">
            <span class="text-xs font-bold text-gray-500">Enable</span>
            <input type="hidden" name="bnpl_credpal_enabled" value="0">
            <input type="checkbox" name="bnpl_credpal_enabled" value="1"
                {{ ($settings['bnpl_credpal_enabled']??'0')==='1'?'checked':'' }} class="w-5 h-5 accent-indigo-500">
        </label>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1.5">Public Key</label>
            <input type="text" name="bnpl_credpal_public_key"
                value="{{ $settings['bnpl_credpal_public_key']??'' }}"
                placeholder="cpk_live_xxxxxxxxxxxxxxxx"
                autocomplete="off"
                class="w-full border-2 border-gray-100 bg-gray-50 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-indigo-400 focus:bg-white transition font-mono">
        </div>
        <div>
            <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1.5">Secret Key</label>
            <input type="password" name="bnpl_credpal_secret_key"
                value="{{ $settings['bnpl_credpal_secret_key']??'' }}"
                placeholder="csk_live_xxxxxxxxxxxxxxxx"
                autocomplete="off"
                class="w-full border-2 border-gray-100 bg-gray-50 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-indigo-400 focus:bg-white transition font-mono">
        </div>
        <div>
            <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1.5">Environment</label>
            <select name="bnpl_credpal_env" class="w-full border-2 border-gray-100 bg-gray-50 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-indigo-400">
                <option value="test" {{ ($settings['bnpl_credpal_env']??'test')==='test'?'selected':'' }}>🧪 Test / Sandbox</option>
                <option value="live" {{ ($settings['bnpl_credpal_env']??'')==='live'   ?'selected':'' }}>🚀 Live / Production</option>
            </select>
        </div>
        <div>
            <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1.5">Min. Order Amount</label>
            <input type="number" name="bnpl_credpal_min_amount"
                value="{{ $settings['bnpl_credpal_min_amount']??5000 }}" min="0" step="100"
                class="w-full border-2 border-gray-100 bg-gray-50 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-indigo-400 focus:bg-white transition">
        </div>
    </div>
</div>

{{-- ── ZiLLA ── --}}
<div class="bg-white rounded-2xl shadow-card p-6 border-l-4 border-violet-500">
    <div class="flex items-start justify-between mb-4">
        <div>
            <h3 class="font-bold text-primary text-base flex items-center gap-2">
                <i class="fas fa-bolt text-violet-500 text-sm"></i> ZiLLA — Buy Now Pay Later
            </h3>
            <p class="text-xs text-gray-400 mt-0.5">
                Flexible instalment payments. Docs at
                <a href="https://developers.zilla.africa" target="_blank" class="text-accent underline">developers.zilla.africa</a>.
            </p>
        </div>
        <label class="flex items-center gap-2 cursor-pointer shrink-0">
            <span class="text-xs font-bold text-gray-500">Enable</span>
            <input type="hidden" name="bnpl_zilla_enabled" value="0">
            <input type="checkbox" name="bnpl_zilla_enabled" value="1"
                {{ ($settings['bnpl_zilla_enabled']??'0')==='1'?'checked':'' }} class="w-5 h-5 accent-violet-500">
        </label>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1.5">Public Key</label>
            <input type="text" name="bnpl_zilla_public_key"
                value="{{ $settings['bnpl_zilla_public_key']??'' }}"
                placeholder="zla_pk_live_xxxxxxxxxxxxxxxx"
                autocomplete="off"
                class="w-full border-2 border-gray-100 bg-gray-50 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-violet-400 focus:bg-white transition font-mono">
        </div>
        <div>
            <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1.5">Secret Key</label>
            <input type="password" name="bnpl_zilla_secret_key"
                value="{{ $settings['bnpl_zilla_secret_key']??'' }}"
                placeholder="zla_sk_live_xxxxxxxxxxxxxxxx"
                autocomplete="off"
                class="w-full border-2 border-gray-100 bg-gray-50 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-violet-400 focus:bg-white transition font-mono">
        </div>
        <div>
            <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1.5">Environment</label>
            <select name="bnpl_zilla_env" class="w-full border-2 border-gray-100 bg-gray-50 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-violet-400">
                <option value="test" {{ ($settings['bnpl_zilla_env']??'test')==='test'?'selected':'' }}>🧪 Test / Sandbox</option>
                <option value="live" {{ ($settings['bnpl_zilla_env']??'')==='live'   ?'selected':'' }}>🚀 Live / Production</option>
            </select>
        </div>
        <div>
            <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1.5">Min. Order Amount</label>
            <input type="number" name="bnpl_zilla_min_amount"
                value="{{ $settings['bnpl_zilla_min_amount']??5000 }}" min="0" step="100"
                class="w-full border-2 border-gray-100 bg-gray-50 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-violet-400 focus:bg-white transition">
        </div>
    </div>
</div>

{{-- Info box --}}
<div class="bg-purple-50 border border-purple-200 rounded-2xl px-5 py-4 flex items-start gap-3">
    <i class="fas fa-info-circle text-purple-400 text-lg flex-shrink-0 mt-0.5"></i>
    <div class="text-xs text-purple-700">
        <p class="font-bold mb-1">How BNPL works on checkout</p>
        <p>When a provider is enabled here with a valid public key, it appears as a selectable option on the user checkout page. When disabled (or key is missing), it shows a "Coming Soon" badge instead. Test environment means transactions are simulated — no real money moves. Switch to Live before launch.</p>
    </div>
</div>

@endif

{{-- ══════════════ NOTIFICATIONS ══════════════ --}}
@if($tab==='notifications')
<div class="bg-white rounded-2xl shadow-card p-6 border-l-4 border-blue-500">
    <h3 class="font-bold text-primary text-base mb-1 flex items-center gap-2"><i class="fab fa-telegram text-blue-500 text-sm"></i> Telegram Bot</h3>
    <p class="text-xs text-gray-400 mb-5">Order alerts, user notifications, admin channel broadcasts. Create a bot at <a href="https://t.me/botfather" target="_blank" class="text-accent underline">@BotFather</a>.</p>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        @foreach(['telegram_bot_token'=>'Bot Token (from @BotFather)','telegram_channel_id'=>'Admin Channel ID (@channel or -100xxxxxx)','telegram_bot_username'=>'Bot Username (no @)','telegram_webhook_secret'=>'Webhook Secret (any random string)'] as $k=>$lbl)
        <div><label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1.5">{{ $lbl }}</label>
        <input type="password" name="{{ $k }}" value="{{ $settings[$k]??'' }}" autocomplete="off"
            class="w-full border-2 border-gray-100 bg-gray-50 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-blue-400 focus:bg-white transition font-mono"></div>
        @endforeach
    </div>
    <div class="mt-4 bg-blue-50 border border-blue-200 rounded-xl p-4 text-xs text-blue-700">
        <p class="font-bold mb-2">After saving, register the webhook by visiting this URL in your browser:</p>
        <code class="block bg-white rounded-lg px-3 py-2 text-[11px] break-all">https://api.telegram.org/bot<strong>[TOKEN]</strong>/setWebhook?url={{ url('/') }}/api/telegram/webhook&amp;secret_token=<strong>[SECRET]</strong></code>
    </div>
</div>

<div class="bg-white rounded-2xl shadow-card p-6 border-l-4 border-green-500">
    <div class="flex items-start justify-between mb-5">
        <div><h3 class="font-bold text-primary text-base flex items-center gap-2"><i class="fas fa-sms text-green-500 text-sm"></i> SMS — Termii</h3>
        <p class="text-xs text-gray-400 mt-0.5">SMS to Nigerian users on key events. Get API key at <a href="https://termii.com" target="_blank" class="text-accent underline">termii.com</a>.</p></div>
        <label class="flex items-center gap-2 cursor-pointer shrink-0"><span class="text-xs font-bold text-gray-500">Enable</span>
        <select name="termii_enabled" class="border-2 border-gray-100 bg-gray-50 rounded-xl px-3 py-1.5 text-sm focus:outline-none">
            <option value="true" {{ ($settings['termii_enabled']??'false')==='true'?'selected':'' }}>✅ On</option>
            <option value="false" {{ ($settings['termii_enabled']??'false')!=='true'?'selected':'' }}>❌ Off</option>
        </select></label>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div><label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1.5">Termii API Key</label>
        <input type="password" name="termii_api_key" value="{{ $settings['termii_api_key']??'' }}" autocomplete="off"
            class="w-full border-2 border-gray-100 bg-gray-50 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-green-400 focus:bg-white transition font-mono"></div>
        <div><label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1.5">Sender ID</label>
        <input type="text" name="termii_sender_id" value="{{ $settings['termii_sender_id']??'BYBHUB' }}"
            class="w-full border-2 border-gray-100 bg-gray-50 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-green-400 focus:bg-white transition"></div>
    </div>
</div>
@endif

{{-- ══════════════ LAUNCH & PROMO ══════════════ --}}
@if($tab==='launch')
<div class="bg-white rounded-2xl shadow-card p-6">
    <div class="flex items-start justify-between mb-5">
        <div><h3 class="font-bold text-primary text-base flex items-center gap-2"><i class="fas fa-bullhorn text-accent text-sm"></i> Announcement Bar</h3>
        <p class="text-xs text-gray-400 mt-0.5">Top bar text shown to all visitors. Leave message empty to show the default China sourcing message.</p></div>
        <label class="flex items-center gap-2 cursor-pointer shrink-0"><span class="text-xs font-bold text-gray-500">Enable</span>
        <input type="hidden" name="announcement_enabled" value="0">
        <input type="checkbox" name="announcement_enabled" value="1" {{ ($settings['announcement_enabled']??'0')==='1'?'checked':'' }} class="w-5 h-5 accent-accent"></label>
    </div>
    <input type="text" name="announcement_text" value="{{ $settings['announcement_text']??'' }}"
        placeholder="e.g. 🎉 Free shipping on orders above ₦50,000 this week only!"
        class="w-full border-2 border-gray-100 bg-gray-50 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-accent focus:bg-white transition">
    <p class="text-[10px] text-gray-400 mt-1">Emojis supported. Users can dismiss it — won't reappear unless text changes.</p>
</div>

<div class="bg-white rounded-2xl shadow-card p-6 border-2 border-orange-200">
    <div class="flex items-start justify-between mb-5">
        <div><h3 class="font-bold text-primary text-base flex items-center gap-2"><i class="fas fa-rocket text-orange-500 text-sm"></i> Coming Soon / Launch Countdown</h3>
        <p class="text-xs text-gray-400 mt-0.5">Replaces the site with a countdown page for guests. Admins always have full access.</p></div>
        <label class="flex items-center gap-2 cursor-pointer shrink-0"><span class="text-xs font-bold text-orange-700">Enable</span>
        <input type="hidden" name="coming_soon_mode" value="0">
        <input type="checkbox" name="coming_soon_mode" value="1" {{ ($settings['coming_soon_mode']??'0')==='1'?'checked':'' }} class="w-5 h-5 accent-orange-500"></label>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="md:col-span-2">
            <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1.5">Launch Date & Time</label>
            <input type="datetime-local" name="launch_datetime" value="{{ $settings['launch_datetime']??'' }}"
                class="w-full border-2 border-gray-100 bg-gray-50 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-orange-400 focus:bg-white transition">
            <p class="text-[10px] text-gray-400 mt-1">Leave empty to show banner without countdown. Time is interpreted as Lagos time (UTC+1).</p>
        </div>
        <div>
            <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1.5">Progress % (0–100)</label>
            <input type="number" name="launch_progress" value="{{ $settings['launch_progress']??75 }}" min="0" max="100"
                class="w-full border-2 border-gray-100 bg-gray-50 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-orange-400 focus:bg-white transition">
        </div>
    </div>
    <div class="mt-3 bg-orange-50 border border-orange-200 rounded-xl px-4 py-3 text-xs text-orange-700 flex items-center gap-2">
        <i class="fas fa-info-circle text-orange-400 shrink-0"></i>
        Admins are never shown this page. When the countdown hits zero it disappears automatically.
        <a href="{{ route('admin.waitlist.index') }}" class="font-bold underline ml-auto shrink-0">View Waitlist →</a>
    </div>
</div>

<div class="bg-white rounded-2xl shadow-card p-6 border-l-4 border-pink-500">
    <div class="flex items-start justify-between mb-5">
        <div><h3 class="font-bold text-primary text-base flex items-center gap-2"><i class="fas fa-square text-pink-500 text-sm"></i> Promotional Popup</h3>
        <p class="text-xs text-gray-400 mt-0.5">Show a popup to users — promotion, new product launch, special offer.</p></div>
        <label class="flex items-center gap-2 cursor-pointer shrink-0"><span class="text-xs font-bold text-gray-500">Enable</span>
        <input type="hidden" name="popup_enabled" value="0">
        <input type="checkbox" name="popup_enabled" value="1" {{ ($settings['popup_enabled']??'0')==='1'?'checked':'' }} class="w-5 h-5 accent-pink-500"></label>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div class="md:col-span-2"><label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1.5">Title</label>
        <input type="text" name="popup_title" value="{{ $settings['popup_title']??'' }}" placeholder="e.g. 🎉 Free Shipping This Week!"
            class="w-full border-2 border-gray-100 bg-gray-50 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-pink-400 focus:bg-white transition"></div>
        <div class="md:col-span-2"><label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1.5">Message</label>
        <textarea name="popup_message" rows="2" placeholder="Short message below title..."
            class="w-full border-2 border-gray-100 bg-gray-50 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-pink-400 focus:bg-white transition resize-none">{{ $settings['popup_message']??'' }}</textarea></div>
        <div><label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1.5">Button Text</label>
        <input type="text" name="popup_button_text" value="{{ $settings['popup_button_text']??'Shop Now' }}"
            class="w-full border-2 border-gray-100 bg-gray-50 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-pink-400 focus:bg-white transition"></div>
        <div><label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1.5">Button URL</label>
        <input type="text" name="popup_button_url" value="{{ $settings['popup_button_url']??'' }}" placeholder="/products or https://..."
            class="w-full border-2 border-gray-100 bg-gray-50 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-pink-400 focus:bg-white transition"></div>
        <div><label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1.5">Show Frequency</label>
        <select name="popup_frequency" class="w-full border-2 border-gray-100 bg-gray-50 rounded-xl px-4 py-2.5 text-sm focus:outline-none">
            <option value="once"    {{ ($settings['popup_frequency']??'once')==='once'   ?'selected':'' }}>Once per browser</option>
            <option value="session" {{ ($settings['popup_frequency']??'')==='session'    ?'selected':'' }}>Once per session</option>
            <option value="always"  {{ ($settings['popup_frequency']??'')==='always'     ?'selected':'' }}>Every page load</option>
        </select></div>
        <div><label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1.5">Delay (seconds)</label>
        <input type="number" name="popup_delay_seconds" value="{{ $settings['popup_delay_seconds']??2 }}" min="0" max="30"
            class="w-full border-2 border-gray-100 bg-gray-50 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-pink-400 focus:bg-white transition"></div>
    </div>
</div>
@endif

{{-- ══════════════ VENDOR ══════════════ --}}
@if($tab==='vendor')
<div class="bg-white rounded-2xl shadow-card p-6 border-l-4 border-teal-500">
    <div class="flex items-start justify-between mb-5">
        <div><h3 class="font-bold text-primary text-base flex items-center gap-2"><i class="fas fa-store text-teal-500 text-sm"></i> Vendor Marketplace</h3>
        <p class="text-xs text-gray-400 mt-0.5">Control vendor sign-ups, fees, and commission. <a href="{{ route('admin.vendors.index') }}" class="text-primary font-bold underline">Manage Vendors →</a></p></div>
        <label class="flex items-center gap-2 cursor-pointer shrink-0"><span class="text-xs font-bold text-gray-500">Open Marketplace</span>
        <input type="hidden" name="vendor_marketplace_open" value="0">
        <input type="checkbox" name="vendor_marketplace_open" value="1" {{ ($settings['vendor_marketplace_open']??'0')==='1'?'checked':'' }} class="w-5 h-5 accent-teal-500"></label>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div><label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1.5">Activation Fee (₦)</label>
        <input type="number" name="vendor_activation_fee" value="{{ $settings['vendor_activation_fee']??5000 }}" min="0" step="100"
            class="w-full border-2 border-gray-100 bg-gray-50 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-teal-400 focus:bg-white transition">
        <p class="text-[10px] text-gray-400 mt-1">One-time fee deducted from wallet on application. Set 0 for free.</p></div>
        <div><label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1.5">Global Commission (%)</label>
        <input type="number" name="vendor_commission_percent" value="{{ $settings['vendor_commission_percent']??10 }}" min="0" max="50" step="0.5"
            class="w-full border-2 border-gray-100 bg-gray-50 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-teal-400 focus:bg-white transition">
        <p class="text-[10px] text-gray-400 mt-1">% of each sale kept by BYBHUB. Can be overridden per vendor.</p></div>
        <div><label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1.5">Min. Withdrawal Amount (₦)</label>
        <input type="number" name="vendor_min_withdrawal" value="{{ $settings['vendor_min_withdrawal']??1000 }}" min="0" step="100"
            class="w-full border-2 border-gray-100 bg-gray-50 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-teal-400 focus:bg-white transition">
        <p class="text-[10px] text-gray-400 mt-1">Minimum amount a vendor can withdraw at once.</p></div>
        <div><label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1.5">Auto-Release Escrow After (hours)</label>
        <input type="number" name="vendor_escrow_auto_release_hours" value="{{ $settings['vendor_escrow_auto_release_hours']??72 }}" min="1" step="1"
            class="w-full border-2 border-gray-100 bg-gray-50 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-teal-400 focus:bg-white transition">
        <p class="text-[10px] text-gray-400 mt-1">Hours after buyer confirms delivery before escrow auto-releases. Default: 72h.</p></div>
        <div class="md:col-span-2">
            <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1.5">Require Product Approval</label>
            <label class="flex items-center gap-3 p-3 border-2 border-gray-100 rounded-xl cursor-pointer hover:bg-gray-50">
                <input type="hidden" name="vendor_require_product_approval" value="0">
                <input type="checkbox" name="vendor_require_product_approval" value="1" {{ ($settings['vendor_require_product_approval']??'1')==='1'?'checked':'' }} class="w-5 h-5 accent-teal-500">
                <div><p class="text-sm font-semibold text-gray-700">Vendor products need admin approval before going live</p>
                <p class="text-xs text-gray-400">When enabled, new and edited vendor products won't show on the marketplace until approved.</p></div>
            </label>
        </div>
    </div>
</div>

<div class="bg-white rounded-2xl shadow-card p-6">
    <h3 class="font-bold text-primary text-base mb-5 flex items-center gap-2"><i class="fas fa-tags text-gray-400 text-sm"></i> Product Scraper</h3>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div class="md:col-span-2">
            <label class="flex items-center gap-3 p-4 border-2 border-gray-100 rounded-xl cursor-pointer hover:bg-gray-50">
                <input type="hidden" name="scraper_require_approval" value="0">
                <input type="checkbox" name="scraper_require_approval" value="1" {{ ($settings['scraper_require_approval']??'0')==='1'?'checked':'' }} class="w-5 h-5 accent-primary">
                <div><p class="text-sm font-semibold text-gray-700">Require admin approval for scraped products</p>
                <p class="text-xs text-gray-400">When enabled, scraped products won't appear on the marketplace until an admin approves them.</p></div>
            </label>
        </div>
        <div>
            <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1.5">
                Daily Scrape Limit Per User
            </label>
            <input type="number" name="scraper_daily_limit"
                   value="{{ $settings['scraper_daily_limit']??20 }}" min="0" max="500"
                   class="w-full border-2 border-gray-100 bg-gray-50 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-primary focus:bg-white transition">
            <p class="text-[10px] text-gray-400 mt-1">Max products a user can scrape per day. Set to 0 for unlimited.</p>
        </div>
        <div>
            <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1.5">
                Weekly Scrape Limit Per User
            </label>
            <input type="number" name="scraper_weekly_limit"
                   value="{{ $settings['scraper_weekly_limit']??0 }}" min="0" max="5000"
                   class="w-full border-2 border-gray-100 bg-gray-50 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-primary focus:bg-white transition">
            <p class="text-[10px] text-gray-400 mt-1">Max products a user can scrape per week (rolling ISO-week). Set to 0 for unlimited.</p>
        </div>
        <div>
            <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1.5">
                Default Weight per Product (kg)
            </label>
            <input type="number" name="scraper_default_weight_kg"
                   value="{{ $settings['scraper_default_weight_kg'] ?: '0.5' }}" min="0" step="0.1"
                   class="w-full border-2 border-gray-100 bg-gray-50 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-primary focus:bg-white transition">
            <p class="text-[10px] text-gray-400 mt-1">Fallback estimated weight (kg) when a scraped product doesn't list its own weight. Powers the landed-cost estimator.</p>
        </div>
    </div>
</div>
@endif

{{-- ══════════════ SPIN & WIN ══════════════ --}}
@if($tab==='spinwin')
<div class="bg-white rounded-2xl shadow-card p-6 border-l-4 border-pink-500">
    <div class="flex items-start justify-between mb-5">
        <div>
            <h3 class="font-bold text-primary text-base flex items-center gap-2">
                <i class="fas fa-dharmachakra text-pink-500 text-sm"></i> Spin & Win Wheel
            </h3>
            <p class="text-xs text-gray-400 mt-0.5">Let users spin a wheel to win coupons. Prizes are configured on the
                <a href="{{ route('admin.spinwin.index') }}" class="text-accent font-bold underline">Spin & Win Prizes</a> page.</p>
        </div>
        <label class="flex items-center gap-2 cursor-pointer shrink-0">
            <span class="text-xs font-bold text-gray-500">Enable</span>
            <input type="hidden" name="spinwin_enabled" value="0">
            <input type="checkbox" name="spinwin_enabled" value="1"
                {{ ($settings['spinwin_enabled']??'0')==='1'?'checked':'' }} class="w-5 h-5 accent-pink-500">
        </label>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1.5">Spins Per User Per Day</label>
            <input type="number" name="spinwin_daily_limit" value="{{ $settings['spinwin_daily_limit']??1 }}" min="1" max="10"
                class="w-full border-2 border-gray-100 bg-gray-50 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-pink-400 focus:bg-white transition">
            <p class="text-[10px] text-gray-400 mt-1">How many times a user can spin each day. Default: 1.</p>
        </div>
        <div>
            <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1.5">Cooldown Period (hours)</label>
            <input type="number" name="spinwin_cooldown_hours" value="{{ $settings['spinwin_cooldown_hours']??24 }}" min="1" max="168"
                class="w-full border-2 border-gray-100 bg-gray-50 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-pink-400 focus:bg-white transition">
            <p class="text-[10px] text-gray-400 mt-1">Hours before a user can spin again. Default: 24h.</p>
        </div>
    </div>
    <div class="mt-4 bg-pink-50 border border-pink-200 rounded-xl p-4 text-xs text-pink-700 flex items-center gap-3">
        <i class="fas fa-info-circle text-pink-400 text-base shrink-0"></i>
        <div>
            <p class="font-bold mb-1">How it works</p>
            <p>Each prize slot on the wheel maps to a coupon type (percent off, flat amount, free shipping, or a "try again" loss slot). Add/edit prize segments from the
            <a href="{{ route('admin.spinwin.index') }}" class="font-bold underline">Spin & Win Prizes</a> page.
            Won coupons are auto-generated and credited to the user's account instantly.</p>
        </div>
    </div>
</div>
@endif

{{-- ══════════════ SAVE BUTTON ══════════════ --}}
<div class="sticky bottom-4 z-10">
    <button type="submit"
        class="w-full bg-gradient-to-r from-primary to-primary-light text-white font-black py-4 rounded-2xl text-sm shadow-xl hover:opacity-95 transition flex items-center justify-center gap-2">
        <i class="fas fa-save"></i> Save {{ ucfirst($tab) }} Settings
    </button>
</div>

</form>
@endsection
