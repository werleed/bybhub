@extends('layouts.auth')
@section('title', 'Reset Password')
@section('content')

<div class="w-full max-w-sm anim-fade-up"
     x-data="{ sent: {{ session('status') ? 'true' : 'false' }}, loading: false }">

    {{-- Back link --}}
    <div class="mb-4 text-center">
        <a href="{{ route('home') }}" class="inline-flex items-center gap-1.5 text-blue-300/70 hover:text-white text-xs font-medium transition">
            <i class="fas fa-arrow-left text-[10px]"></i> Back to BYBHUB
        </a>
    </div>

    {{-- Logo --}}
    <div class="text-center mb-7">
        <a href="{{ route('home') }}" class="inline-block mb-3">
            <img src="{{ asset('images/logo.jpg') }}" alt="BYBHUB"
                 class="h-14 w-14 mx-auto rounded-2xl shadow-2xl object-cover ring-4 ring-white/15">
        </a>
        <h1 class="text-2xl font-black text-white" style="font-family:Poppins,sans-serif">
            Reset your password 🔑
        </h1>
        <p class="text-blue-300 text-sm mt-1">We'll send a secure reset link to your email</p>
    </div>

    {{-- Card --}}
    <div class="bg-white rounded-3xl overflow-hidden shadow-2xl" style="box-shadow:0 24px 80px rgba(0,0,0,0.35)">

        {{-- Sent state --}}
        <div x-show="sent" x-transition class="p-8 text-center">
            <div class="w-16 h-16 bg-green-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-paper-plane text-green-500 text-2xl"></i>
            </div>
            <h2 class="font-black text-gray-900 text-lg mb-2" style="font-family:Poppins,sans-serif">
                Check your inbox
            </h2>
            <p class="text-gray-500 text-sm leading-relaxed mb-6">
                {{ session('status') ?? 'A reset link has been sent to your email address.' }}
            </p>
            <button @click="sent=false"
                    class="w-full border-2 border-gray-200 text-gray-700 font-bold py-3 rounded-2xl text-sm hover:border-primary hover:text-primary transition mb-2">
                Try a different email
            </button>
            <a href="{{ route('login') }}" class="block text-sm text-gray-400 hover:text-primary transition py-1">
                ← Back to Sign In
            </a>
        </div>

        {{-- Form state --}}
        <div x-show="!sent">
            @if($errors->any())
            <div class="bg-red-50 border-b border-red-100 px-5 py-3 flex items-center gap-2">
                <i class="fas fa-exclamation-circle text-red-400 text-sm flex-shrink-0"></i>
                <p class="text-red-700 text-xs font-semibold">{{ $errors->first() }}</p>
            </div>
            @endif

            <div class="p-6 sm:p-8">
                <form action="{{ route('password.email') }}" method="POST" @submit="loading=true" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-1.5">
                            Email Address
                        </label>
                        <div class="relative">
                            <i class="fas fa-envelope absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 text-sm pointer-events-none"></i>
                            <input type="email" name="email" value="{{ old('email') }}"
                                   required autofocus autocomplete="email"
                                   placeholder="you@email.com"
                                   class="w-full border-2 border-gray-100 bg-gray-50 rounded-2xl pl-10 pr-4 py-3.5 text-sm
                                          focus:border-primary focus:bg-white transition-colors">
                        </div>
                    </div>
                    <button type="submit" :disabled="loading"
                            class="w-full text-white font-bold py-4 rounded-2xl text-sm transition active:scale-95 disabled:opacity-70
                                   flex items-center justify-center gap-2"
                            style="background:linear-gradient(135deg,#1A3A6B,#2451A3);box-shadow:0 4px 20px rgba(26,58,107,0.3)">
                        <i class="fas text-sm" :class="loading ? 'fa-spinner fa-spin' : 'fa-paper-plane'"></i>
                        <span x-text="loading ? 'Sending…' : 'Send Reset Link'"></span>
                    </button>
                </form>
            </div>

            <div class="border-t border-gray-100 bg-gray-50/70 px-6 py-4 text-center">
                <a href="{{ route('login') }}" class="text-sm text-gray-500 hover:text-primary font-medium transition">
                    ← Back to Sign In
                </a>
            </div>
        </div>
    </div>

</div>
@endsection
