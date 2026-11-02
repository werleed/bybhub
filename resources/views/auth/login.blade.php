@extends('layouts.auth')
@section('title', 'Sign In')
@section('content')

<div class="w-full max-w-sm anim-fade-up">

    {{-- Back link --}}
    <div class="mb-4 text-center">
        <a href="{{ route('home') }}" class="inline-flex items-center gap-1.5 text-blue-300/70 hover:text-white text-xs font-medium transition">
            <i class="fas fa-arrow-left text-[10px]"></i> Back to BYBHUB
        </a>
    </div>

    {{-- Logo + headline --}}
    <div class="text-center mb-7">
        <a href="{{ route('home') }}" class="inline-block mb-4">
            <img src="{{ asset('images/logo.jpg') }}" alt="BYBHUB"
                 class="h-16 w-16 mx-auto rounded-2xl shadow-2xl object-cover ring-4 ring-white/15">
        </a>
        <h1 class="text-2xl font-black text-white" style="font-family:Poppins,sans-serif">
            Welcome back 👋
        </h1>
        <p class="text-blue-300 text-sm mt-1">Sign in to your BYBHUB account</p>
    </div>

    {{-- Card --}}
    <div class="bg-white rounded-3xl overflow-hidden shadow-2xl" style="box-shadow:0 24px 80px rgba(0,0,0,0.35)">

        {{-- Error --}}
        @if($errors->any())
        <div class="bg-red-50 border-b border-red-100 px-5 py-3 flex items-center gap-2">
            <i class="fas fa-exclamation-circle text-red-400 text-sm flex-shrink-0"></i>
            <p class="text-red-700 text-xs font-semibold">{{ $errors->first() }}</p>
        </div>
        @endif

        <div class="p-6 sm:p-8" x-data="{ showPass: false, loading: false }">
            <form action="{{ route('login') }}" method="POST" class="space-y-4" @submit="loading=true">
                @csrf

                {{-- Email --}}
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
                                      focus:border-primary focus:bg-white transition-colors
                                      @error('email') border-red-300 bg-red-50 @enderror">
                    </div>
                </div>

                {{-- Password --}}
                <div>
                    <div class="flex justify-between items-center mb-1.5">
                        <label class="text-xs font-bold text-gray-500 uppercase tracking-wide">Password</label>
                        <a href="{{ route('password.request') }}" class="text-xs text-accent font-semibold hover:underline">
                            Forgot?
                        </a>
                    </div>
                    <div class="relative">
                        <i class="fas fa-lock absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 text-sm pointer-events-none"></i>
                        <input :type="showPass ? 'text' : 'password'" name="password"
                               required autocomplete="current-password"
                               placeholder="Your password"
                               class="w-full border-2 border-gray-100 bg-gray-50 rounded-2xl pl-10 pr-12 py-3.5 text-sm
                                      focus:border-primary focus:bg-white transition-colors">
                        <button type="button" @click="showPass=!showPass"
                                class="absolute right-3.5 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 transition w-7 h-7 flex items-center justify-center">
                            <i :class="showPass ? 'fas fa-eye-slash' : 'fas fa-eye'" class="text-sm"></i>
                        </button>
                    </div>
                </div>

                {{-- Remember me --}}
                <label class="flex items-center gap-2.5 cursor-pointer select-none">
                    <input type="checkbox" name="remember" class="w-4 h-4 rounded accent-primary flex-shrink-0">
                    <span class="text-sm text-gray-500">Keep me signed in</span>
                </label>

                {{-- Submit --}}
                <button type="submit" :disabled="loading"
                        class="w-full text-white font-bold py-4 rounded-2xl text-sm transition active:scale-95 disabled:opacity-70
                               flex items-center justify-center gap-2"
                        style="background:linear-gradient(135deg,#1A3A6B,#2451A3);box-shadow:0 4px 20px rgba(26,58,107,0.3)">
                    <i class="fas text-sm" :class="loading ? 'fa-spinner fa-spin' : 'fa-sign-in-alt'"></i>
                    <span x-text="loading ? 'Signing in…' : 'Sign In'"></span>
                </button>
            </form>
        </div>

        {{-- Card footer --}}
        <div class="border-t border-gray-100 bg-gray-50/70 px-6 py-4 text-center">
            <p class="text-sm text-gray-500">
                Don't have an account?
                <a href="{{ route('register') }}" class="text-accent font-bold hover:underline ml-1">
                    Create one free →
                </a>
            </p>
        </div>
    </div>

</div>
@endsection
