@extends('layouts.app')
@section('title', __('app.wallet'))
@section('content')
<div class="space-y-6">

    {{-- Balance Hero --}}
    <div class="rounded-3xl p-6 md:p-8 text-white relative overflow-hidden" style="background:linear-gradient(135deg,#1A3A6B 0%,#2451A3 100%)">
        <div class="absolute top-0 right-0 w-64 h-64 rounded-full -translate-y-1/2 translate-x-1/3 opacity-10" style="background:radial-gradient(circle,#F47B20,transparent)"></div>
        <div class="relative z-10 flex flex-wrap items-center justify-between gap-5">
            <div>
                <p class="text-blue-300 text-xs font-semibold uppercase tracking-widest mb-2">{{ $user->currency }} Wallet Balance</p>
                <p class="text-5xl font-black leading-tight" style="font-family:Poppins,sans-serif">{{ $symbol }}{{ number_format($wallet, 2) }}</p>
                <p class="text-blue-300 text-sm mt-2">{{ $user->name }} · {{ $user->country }}</p>
            </div>
            <div class="flex flex-wrap gap-3 flex-col sm:flex-row">
                @if(!empty($fundingMethods))
                <a href="{{ route('user.wallet.fund') }}" class="inline-flex items-center gap-2 bg-accent hover:bg-accent-dark text-white font-bold px-5 py-2.5 rounded-xl text-sm transition btn-glow">
                    <i class="fas fa-plus-circle"></i> Fund Wallet
                </a>
                @endif
                <a href="{{ route('user.wallet.transfer.form') }}" class="inline-flex items-center gap-2 text-white font-bold px-5 py-2.5 rounded-xl text-sm transition" style="background:rgba(255,255,255,0.12)">
                    <i class="fas fa-paper-plane"></i> Send Money
                </a>
                <a href="{{ route('user.wallet.transactions') }}" class="inline-flex items-center gap-2 text-white font-bold px-5 py-2.5 rounded-xl text-sm transition" style="background:rgba(255,255,255,0.08)">
                    <i class="fas fa-history"></i> History
                </a>
            </div>
        </div>
    </div>

    {{-- Wallet usage info --}}
    <div class="bg-gradient-to-r from-primary-50 to-blue-50 border border-primary/10 rounded-2xl p-5">
        <h3 class="font-bold text-primary text-sm mb-3 flex items-center gap-2">
            <i class="fas fa-info-circle text-primary/60 text-sm"></i> What you can do with your wallet
        </h3>
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
            @foreach([
                ['products.index',          'fa-shopping-bag',    'Buy Products',      'bg-primary text-white'],
                ['user.orders.index',        'fa-ship',            'Pay Shipping',       'bg-blue-600 text-white'],
                ['user.wallet.transfer.form','fa-paper-plane',     'Send to User',       'bg-teal-600 text-white'],
                ['hub.courses.index',        'fa-graduation-cap',  'Buy Hub Courses',    'bg-purple-600 text-white'],
            ] as [$route, $icon, $label, $cls])
            @if(Route::has($route))
            <a href="{{ route($route) }}"
               class="flex flex-col items-center gap-2 {{ $cls }} rounded-2xl p-3 text-center text-xs font-bold hover:opacity-90 transition">
                <i class="fas {{ $icon }} text-lg"></i>
                {{ $label }}
            </a>
            @endif
            @endforeach
        </div>
        <p class="text-[10px] text-gray-400 mt-3 flex items-center gap-1">
            <i class="fas fa-lock text-gray-300"></i>
            Wallet balance is for spending on BYBHUB — cash withdrawals are not available.
            <a href="{{ route('terms') }}#wallet" class="underline hover:text-primary">Learn more</a>
        </p>
    </div>

    {{-- Virtual Account --}}
    @if($monnify)
    <div class="bg-white rounded-2xl shadow-card p-6">
        <div class="flex items-center justify-between mb-5">
            <h3 class="font-bold text-primary flex items-center gap-2">
                <div class="w-8 h-8 bg-primary/10 rounded-lg flex items-center justify-center"><i class="fas fa-university text-primary text-xs"></i></div>
                Your Dedicated Bank Accounts
            </h3>
            <span class="inline-flex items-center gap-1.5 bg-green-50 text-green-700 text-xs font-semibold px-3 py-1 rounded-full">
                <span class="w-1.5 h-1.5 bg-green-500 rounded-full animate-pulse"></span> Auto-credit
            </span>
        </div>
        <p class="text-sm text-gray-500 mb-4">Transfer any amount to any account below — your wallet credits within seconds.</p>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
            @foreach($monnify->accountsList() as $acct)
            <div class="border-2 border-primary/10 rounded-2xl p-4 bg-gradient-to-r from-primary-50 to-blue-50" x-data="{ copied: false }">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <p class="text-[11px] font-bold text-gray-500 uppercase tracking-wider">{{ $acct['bank'] }}</p>
                        <p class="text-2xl font-black text-primary tracking-wider mt-1 font-mono">{{ $acct['number'] }}</p>
                        <p class="text-xs text-gray-600 mt-0.5">{{ $acct['name'] }}</p>
                    </div>
                    <button @click="navigator.clipboard.writeText('{{ $acct['number'] }}').then(()=>{copied=true;setTimeout(()=>copied=false,2500)})"
                        class="flex-shrink-0 text-xs font-bold px-3 py-2 rounded-xl transition flex items-center gap-1.5"
                        :class="copied ? 'bg-green-500 text-white' : 'bg-primary text-white hover:bg-primary-dark'">
                        <i class="fas" :class="copied ? 'fa-check' : 'fa-copy'"></i>
                        <span x-text="copied ? 'Copied!' : 'Copy'"></span>
                    </button>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @else
    @if($user->country === 'NG')
    <div class="bg-white rounded-2xl shadow-card p-6 text-center">
        <div class="w-16 h-16 bg-primary/5 rounded-2xl flex items-center justify-center mx-auto mb-4">
            <i class="fas fa-university text-primary text-2xl"></i>
        </div>
        <h3 class="font-bold text-gray-800 text-base mb-1">Get Your Virtual Account</h3>
        <p class="text-sm text-gray-500 mb-4 max-w-sm mx-auto">Set up a dedicated Monnify virtual account to fund your wallet instantly via bank transfer.</p>
        <a href="{{ route('user.wallet.request-virtual-account') }}"
            class="inline-flex items-center gap-2 bg-primary text-white font-bold px-6 py-3 rounded-xl hover:bg-primary-dark transition text-sm shadow-glow-primary">
            <i class="fas fa-plus"></i> Request Virtual Account
        </a>
    </div>
    @endif
    @endif

    {{-- Recent Transactions --}}
    <div class="bg-white rounded-2xl shadow-card p-6">
        <div class="flex justify-between items-center mb-5">
            <h3 class="font-bold text-primary flex items-center gap-2">
                <div class="w-7 h-7 bg-primary/10 rounded-lg flex items-center justify-center"><i class="fas fa-receipt text-primary text-xs"></i></div>
                Recent Transactions
            </h3>
            <a href="{{ route('user.wallet.transactions') }}" class="text-sm text-accent hover:underline font-semibold flex items-center gap-1">
                View All <i class="fas fa-arrow-right text-xs"></i>
            </a>
        </div>
        @if($recentTxns->isEmpty())
            <div class="text-center py-10 text-gray-400">
                <div class="w-12 h-12 bg-gray-100 rounded-xl flex items-center justify-center mx-auto mb-3"><i class="fas fa-receipt text-gray-300 text-lg"></i></div>
                <p class="text-sm font-medium">No transactions yet</p>
            </div>
        @else
        <div class="space-y-0 divide-y divide-gray-50">
            @foreach($recentTxns as $txn)
            <div class="flex items-center justify-between py-3.5 hover:bg-gray-50/50 -mx-2 px-2 rounded-xl transition">
                <div class="flex items-center gap-3.5">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0 {{ $txn->type === 'credit' ? 'bg-green-100' : 'bg-red-100' }}">
                        <i class="fas {{ $txn->type === 'credit' ? 'fa-arrow-down-left text-green-600' : 'fa-arrow-up-right text-red-600' }} text-sm"></i>
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-gray-800 leading-tight">{{ Str::limit($txn->description, 45) }}</p>
                        <p class="text-xs text-gray-400 mt-0.5">{{ $txn->created_at->format('M d, Y · h:i A') }}</p>
                    </div>
                </div>
                <div class="text-right ml-3 flex-shrink-0">
                    <p class="font-black text-sm {{ $txn->type === 'credit' ? 'text-green-600' : 'text-red-600' }}">
                        {{ $txn->type === 'credit' ? '+' : '-' }}{{ $symbol }}{{ number_format($txn->amount, 2) }}
                    </p>
                    <p class="text-[11px] text-gray-400">Bal: {{ $symbol }}{{ number_format($txn->balance_after, 2) }}</p>
                </div>
            </div>
            @endforeach
        </div>
        @endif
    </div>
</div>
@endsection
