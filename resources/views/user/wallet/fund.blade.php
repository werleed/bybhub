@extends('layouts.app')
@section('title', 'Fund Your Wallet')
@section('content')
<div class="max-w-2xl mx-auto space-y-5">

    {{-- Back link --}}
    <a href="{{ route('user.wallet.index') }}" class="inline-flex items-center gap-1.5 text-sm text-gray-600 font-medium hover:text-primary transition">
        <i class="fas fa-arrow-left text-xs"></i> Back to Wallet
    </a>

    {{-- Balance hero --}}
    <div class="rounded-2xl p-5 text-white flex items-center justify-between"
         style="background:linear-gradient(135deg,#1A3A6B,#2451A3)">
        <div>
            <p class="text-blue-200 text-xs font-semibold uppercase tracking-wider">Current Balance</p>
            <p class="text-3xl font-black mt-0.5" style="font-family:Poppins,sans-serif">
                {{ $symbol }}{{ number_format(auth()->user()->getWalletBalance(),2) }}
            </p>
            <p class="text-blue-200 text-xs mt-1">{{ auth()->user()->currency }} Wallet</p>
        </div>
        <div class="w-14 h-14 rounded-2xl flex items-center justify-center" style="background:rgba(255,255,255,0.12)">
            <i class="fas fa-wallet text-white text-2xl"></i>
        </div>
    </div>

    @if(empty($fundingMethods))
    {{-- No methods enabled --}}
    <div class="bg-white rounded-2xl shadow-card p-10 text-center text-gray-400">
        <i class="fas fa-times-circle text-5xl mb-3 text-red-300"></i>
        <p class="font-semibold text-gray-600">Funding temporarily unavailable</p>
        <p class="text-sm mt-1">Contact support for assistance</p>
    </div>

    @else
    <div x-data="{ method: '{{ in_array('monnify',$fundingMethods) ? 'monnify' : ($fundingMethods[0]??'manual') }}' }">

        {{-- Method selector tabs --}}
        <div class="bg-white rounded-2xl shadow-card p-1.5 flex gap-1.5">
            @if(in_array('monnify',$fundingMethods))
            <button @click="method='monnify'"
                :class="method==='monnify' ? 'bg-primary text-white shadow-md' : 'text-gray-500 hover:bg-gray-100'"
                class="flex-1 py-2.5 rounded-xl text-xs font-bold transition flex items-center justify-center gap-1.5">
                <i class="fas fa-university"></i>
                <span class="hidden sm:block">Virtual Account</span>
                <span class="sm:hidden">Virtual Acct</span>
            </button>
            @endif
            @if(in_array('paystack',$fundingMethods))
            <button @click="method='paystack'"
                :class="method==='paystack' ? 'bg-primary text-white shadow-md' : 'text-gray-500 hover:bg-gray-100'"
                class="flex-1 py-2.5 rounded-xl text-xs font-bold transition flex items-center justify-center gap-1.5">
                <i class="fas fa-credit-card"></i>
                <span class="hidden sm:block">Card / Bank</span>
                <span class="sm:hidden">Card</span>
            </button>
            @endif
            @if(in_array('manual',$fundingMethods))
            <button @click="method='manual'"
                :class="method==='manual' ? 'bg-primary text-white shadow-md' : 'text-gray-500 hover:bg-gray-100'"
                class="flex-1 py-2.5 rounded-xl text-xs font-bold transition flex items-center justify-center gap-1.5">
                <i class="fas fa-money-bill-wave"></i>
                <span class="hidden sm:block">Bank Transfer</span>
                <span class="sm:hidden">Transfer</span>
            </button>
            @endif
        </div>

        {{-- ─────────────── MONNIFY VIRTUAL ACCOUNT ─────────────── --}}
        @if(in_array('monnify',$fundingMethods))
        <div x-show="method==='monnify'" class="bg-white rounded-2xl shadow-card p-6">
            @php $monnify = auth()->user()->monnifyAccount; @endphp

            @if($monnify)
            {{-- Has virtual account --}}
            <div class="flex items-center gap-3 mb-5">
                <div class="w-10 h-10 bg-green-100 rounded-xl flex items-center justify-center">
                    <i class="fas fa-check-circle text-green-600"></i>
                </div>
                <div>
                    <h3 class="font-bold text-gray-900">Your Dedicated Account Numbers</h3>
                    <p class="text-xs text-gray-400">Transfer any amount — wallet credits automatically within seconds</p>
                </div>
            </div>
            <div class="bg-blue-50 border border-blue-100 rounded-xl p-3 mb-5 flex items-start gap-2.5">
                <i class="fas fa-info-circle text-blue-500 mt-0.5 flex-shrink-0"></i>
                <p class="text-xs text-blue-700 leading-relaxed">
                    Make a bank transfer to any of these accounts. Once payment hits the bank, your wallet is credited <strong>instantly and automatically</strong> — no need to upload any proof.
                </p>
            </div>
            <div class="space-y-3">
                @foreach($monnify->accountsList() as $acct)
                <div class="border-2 border-primary/10 rounded-2xl p-4 bg-gradient-to-r from-primary-50 to-blue-50" x-data="{ copied: false }">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">{{ $acct['bank'] }}</p>
                            <p class="text-2xl font-black text-primary tracking-widest font-mono">{{ $acct['number'] }}</p>
                            <p class="text-xs text-gray-500 mt-0.5">{{ $acct['name'] }}</p>
                        </div>
                        <button @click="navigator.clipboard.writeText('{{ $acct['number'] }}').then(()=>{copied=true;setTimeout(()=>copied=false,2500)})"
                            class="flex-shrink-0 text-xs font-bold px-4 py-2.5 rounded-xl transition flex items-center gap-1.5 min-w-[80px] justify-center"
                            :class="copied ? 'bg-green-500 text-white' : 'bg-primary text-white hover:bg-primary-dark'">
                            <i class="fas" :class="copied ? 'fa-check' : 'fa-copy'"></i>
                            <span x-text="copied ? 'Copied!' : 'Copy'"></span>
                        </button>
                    </div>
                </div>
                @endforeach
            </div>
            <div class="mt-4 p-3 bg-gray-50 rounded-xl text-xs text-gray-500 flex items-start gap-2">
                <i class="fas fa-clock text-gray-400 mt-0.5"></i>
                <p>Transfers typically reflect within <strong>5–30 seconds</strong>. If it takes longer than 5 minutes, contact support with your transfer receipt.</p>
            </div>

            @else
            {{-- No virtual account yet --}}
            <div class="text-center py-6">
                <div class="w-20 h-20 bg-primary/5 rounded-3xl flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-university text-primary text-3xl"></i>
                </div>
                <h3 class="font-black text-gray-900 text-lg mb-2" style="font-family:Poppins,sans-serif">Get Your Virtual Account</h3>
                <p class="text-sm text-gray-500 max-w-sm mx-auto mb-6 leading-relaxed">
                    Set up a dedicated bank account number just for you. Transfer from any Nigerian bank — your wallet credits instantly. No manual uploads needed.
                </p>
                <div class="grid grid-cols-1 gap-3 max-w-xs mx-auto mb-6">
                    @foreach(['Instant wallet credit','No manual proof needed','Works with any Nigerian bank','Dedicated number just for you'] as $benefit)
                    <div class="flex items-center gap-2 text-sm text-gray-600 bg-green-50 rounded-xl px-3 py-2">
                        <i class="fas fa-check-circle text-green-500 flex-shrink-0"></i> {{ $benefit }}
                    </div>
                    @endforeach
                </div>
                @if(auth()->user()->country === 'NG')
                <a href="{{ route('user.wallet.request-virtual-account') }}"
                   class="inline-flex items-center gap-2 bg-primary hover:bg-primary-dark text-white font-bold px-8 py-3.5 rounded-xl transition shadow-glow-primary text-sm">
                    <i class="fas fa-university"></i> Request Virtual Account (Free)
                </a>
                <p class="text-xs text-gray-400 mt-3">Requires BVN or NIN for identity verification (CBN requirement)</p>
                @else
                <p class="text-sm text-gray-400">Virtual accounts are available for Nigerian users only.</p>
                @endif
            </div>
            @endif
        </div>
        @endif

        {{-- ─────────────── PAYSTACK ─────────────── --}}
        @if(in_array('paystack',$fundingMethods))
        <div x-show="method==='paystack'" class="bg-white rounded-2xl shadow-card p-6">
            <div class="flex items-center gap-3 mb-5">
                <div class="w-10 h-10 bg-blue-100 rounded-xl flex items-center justify-center">
                    <i class="fas fa-credit-card text-blue-600"></i>
                </div>
                <div>
                    <h3 class="font-bold text-gray-900">Pay with Card or Bank Transfer</h3>
                    <p class="text-xs text-gray-400">Secure payment powered by Paystack</p>
                </div>
            </div>
            <form action="{{ route('user.wallet.fund.paystack') }}" method="POST" class="space-y-4"
                  x-data="{ amt: '', loading: false }" @submit="loading=true">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Amount ({{ $symbol }})</label>
                    <div class="relative">
                        <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-500 font-bold">{{ $symbol }}</span>
                        <input type="number" name="amount" x-model="amt" min="100" required placeholder="0.00"
                            class="w-full border-2 border-gray-100 bg-gray-50 rounded-xl pl-8 pr-4 py-3 text-sm focus:outline-none focus:border-primary focus:bg-white transition font-bold text-lg text-gray-800">
                    </div>
                </div>
                <div>
                    <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Quick Select</p>
                    <div class="grid grid-cols-4 gap-2">
                        @foreach([1000,2000,5000,10000] as $preset)
                        <button type="button" @click="amt={{ $preset }}"
                            :class="amt=={{ $preset }} ? 'border-primary bg-primary text-white' : 'border-gray-200 text-gray-600 hover:border-primary hover:text-primary'"
                            class="border-2 rounded-xl py-2.5 text-xs font-bold transition">
                            {{ $symbol }}{{ number_format($preset) }}
                        </button>
                        @endforeach
                    </div>
                </div>
                <div class="bg-gray-50 rounded-xl p-3 text-xs text-gray-500 space-y-1">
                    <p class="flex items-center gap-1.5"><i class="fas fa-shield-alt text-green-500"></i> 256-bit SSL encrypted</p>
                    <p class="flex items-center gap-1.5"><i class="fas fa-credit-card text-blue-500"></i> Supports card, bank transfer & USSD</p>
                    <p class="flex items-center gap-1.5"><i class="fas fa-bolt text-accent"></i> Instant wallet credit after payment</p>
                </div>
                <button type="submit" :disabled="loading || !amt"
                    class="w-full text-white font-black py-3.5 rounded-xl text-sm transition flex items-center justify-center gap-2 btn-glow"
                    :class="(loading || !amt) ? 'opacity-60 cursor-not-allowed bg-accent' : 'bg-accent hover:bg-accent-dark'"
                    style="background-color:#F47B20">
                    <i class="fas" :class="loading ? 'fa-spinner fa-spin' : 'fa-lock'"></i>
                    <span x-text="loading ? 'Redirecting to Paystack...' : 'Pay Securely with Paystack'"></span>
                </button>
            </form>
        </div>
        @endif

        {{-- ─────────────── MANUAL BANK TRANSFER ─────────────── --}}
        @if(in_array('manual',$fundingMethods))
        <div x-show="method==='manual'" class="bg-white rounded-2xl shadow-card p-6 space-y-5">
            <div class="flex items-center gap-3 mb-1">
                <div class="w-10 h-10 bg-amber-100 rounded-xl flex items-center justify-center">
                    <i class="fas fa-money-bill-wave text-amber-600"></i>
                </div>
                <div>
                    <h3 class="font-bold text-gray-900">Manual Bank Transfer</h3>
                    <p class="text-xs text-gray-400">For all countries — transfer to our account and upload proof</p>
                </div>
            </div>

            {{-- Company bank details --}}
            <div class="bg-gradient-to-r from-amber-50 to-orange-50 border border-amber-200 rounded-2xl p-5">
                <p class="text-xs font-black text-amber-700 uppercase tracking-widest mb-3 flex items-center gap-1.5">
                    <i class="fas fa-university text-amber-600"></i> BYBHUB Bank Details
                </p>
                <div class="space-y-2.5">
                    <div class="flex justify-between items-center">
                        <span class="text-xs text-gray-500 font-semibold">Bank Name</span>
                        <span class="text-sm font-bold text-gray-800">{{ $bankDetails['bank_name'] ?: 'Contact Support' }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-xs text-gray-500 font-semibold">Account Number</span>
                        <div class="flex items-center gap-2" x-data="{ c: false }">
                            <span class="text-lg font-black text-primary font-mono tracking-wider">{{ $bankDetails['account_number'] ?: '—' }}</span>
                            @if($bankDetails['account_number'])
                            <button @click="navigator.clipboard.writeText('{{ $bankDetails['account_number'] }}').then(()=>{c=true;setTimeout(()=>c=false,2000)})"
                                class="text-xs px-2 py-1 rounded-lg transition"
                                :class="c ? 'bg-green-500 text-white' : 'bg-primary text-white hover:bg-primary-dark'">
                                <span x-text="c ? '✓' : 'Copy'"></span>
                            </button>
                            @endif
                        </div>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-xs text-gray-500 font-semibold">Account Name</span>
                        <span class="text-sm font-bold text-gray-800">{{ $bankDetails['account_name'] ?: '—' }}</span>
                    </div>
                    @if($bankDetails['instructions'])
                    <div class="border-t border-amber-200 pt-2.5 mt-2.5">
                        <p class="text-xs text-amber-700">{{ $bankDetails['instructions'] }}</p>
                    </div>
                    @endif
                </div>
            </div>

            <div class="bg-blue-50 border border-blue-100 rounded-xl p-3 flex items-start gap-2.5">
                <i class="fas fa-info-circle text-blue-500 mt-0.5 flex-shrink-0"></i>
                <p class="text-xs text-blue-700 leading-relaxed">
                    <strong>How it works:</strong> Transfer the exact amount to the account above, then fill in the form below and upload your transfer receipt. Our team will verify and credit your wallet within <strong>1–24 hours</strong>.
                </p>
            </div>

            {{-- Upload form --}}
            <form action="{{ route('user.wallet.fund.manual.submit') }}" method="POST" enctype="multipart/form-data"
                  class="space-y-4" x-data="{ loading: false, preview: null }" @submit="loading=true">
                @csrf
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1.5">Amount ({{ $symbol }}) *</label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm font-bold">{{ $symbol }}</span>
                            <input type="number" name="amount" min="1000" required placeholder="Enter amount"
                                class="w-full border-2 border-gray-100 bg-gray-50 rounded-xl pl-7 pr-4 py-3 text-sm focus:outline-none focus:border-primary focus:bg-white transition">
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1.5">Transfer Date *</label>
                        <input type="date" name="transfer_date" required max="{{ date('Y-m-d') }}"
                            class="w-full border-2 border-gray-100 bg-gray-50 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-primary focus:bg-white transition">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1.5">Your Bank Name *</label>
                    <input type="text" name="sender_bank_name" required placeholder="e.g. Zenith Bank, GTBank, Ecobank..."
                        class="w-full border-2 border-gray-100 bg-gray-50 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-primary focus:bg-white transition">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1.5">Account Name on Receipt *</label>
                    <input type="text" name="sender_account_name" required placeholder="Name as it appears on your bank account"
                        class="w-full border-2 border-gray-100 bg-gray-50 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-primary focus:bg-white transition">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1.5">Transfer Receipt / Screenshot *</label>
                    <div class="border-2 border-dashed border-gray-200 rounded-xl p-4 text-center hover:border-primary transition cursor-pointer relative"
                         onclick="document.getElementById('proofFile').click()">
                        <div x-show="!preview">
                            <i class="fas fa-cloud-upload-alt text-3xl text-gray-300 mb-2 block"></i>
                            <p class="text-sm text-gray-400 font-medium">Click to upload receipt screenshot</p>
                            <p class="text-xs text-gray-300 mt-1">JPG, PNG, GIF up to 5MB</p>
                        </div>
                        <div x-show="preview">
                            <img :src="preview" class="max-h-32 mx-auto rounded-lg mb-2">
                            <p class="text-xs text-green-600 font-semibold">Receipt uploaded ✓</p>
                        </div>
                        <input type="file" id="proofFile" name="proof_image" accept="image/*" required class="hidden"
                            @change="const f=$event.target.files[0];if(f){const r=new FileReader();r.onload=e=>preview=e.target.result;r.readAsDataURL(f)}">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1.5">Additional Notes (optional)</label>
                    <textarea name="notes" rows="2" placeholder="Transaction reference, any other info..."
                        class="w-full border-2 border-gray-100 bg-gray-50 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-primary focus:bg-white transition resize-none"></textarea>
                </div>
                <button type="submit" :disabled="loading"
                    class="w-full text-white font-black py-3.5 rounded-xl text-sm transition flex items-center justify-center gap-2 shadow-glow-primary"
                    :class="loading ? 'opacity-70 cursor-not-allowed' : 'hover:bg-primary-dark'"
                    style="background:linear-gradient(135deg,#1A3A6B,#2451A3)">
                    <i class="fas" :class="loading ? 'fa-spinner fa-spin' : 'fa-paper-plane'"></i>
                    <span x-text="loading ? 'Submitting...' : 'Submit Transfer Proof'"></span>
                </button>
                <p class="text-xs text-center text-gray-400">Your wallet will be credited within 1–24 hours after admin verification.</p>
            </form>
        </div>
        @endif

    </div>
    @endif

</div>
@endsection
