@extends('layouts.auth')
@section('title', 'Reset Password')
@section('content')

<div class="w-full max-w-md anim-fade-up"
     x-data="{
        showPass: false, showConf: false, loading: false, strength: 0, pass: '',
        checkStrength(v){ this.pass=v; let s=0; if(v.length>=8)s++; if(/[A-Z]/.test(v))s++; if(/[0-9]/.test(v))s++; if(/[^A-Za-z0-9]/.test(v))s++; this.strength=s; }
     }">

    {{-- Back link --}}
    <div class="mb-4 text-center">
        <a href="{{ route('home') }}" class="inline-flex items-center gap-1.5 text-blue-300/70 hover:text-white text-xs font-medium transition">
            <i class="fas fa-arrow-left text-[10px]"></i> Back to BYBHUB
        </a>
    </div>

    {{-- Logo + headline --}}
    <div class="text-center mb-7">
        <a href="{{ route('home') }}" class="inline-block mb-3">
            <img src="{{ asset('images/logo.jpg') }}" alt="BYBHUB"
                 class="h-14 w-14 mx-auto rounded-2xl shadow-2xl object-cover ring-4 ring-white/15">
        </a>
        <h1 class="text-2xl font-black text-white" style="font-family:Poppins,sans-serif">
            Create New Password 🔑
        </h1>
        <p class="text-blue-300 text-sm mt-1">Make it strong and memorable</p>
    </div>

    {{-- Card --}}
    <div class="bg-white rounded-3xl overflow-hidden shadow-2xl" style="box-shadow:0 24px 80px rgba(0,0,0,0.35)">
        <div class="h-1.5" style="background:linear-gradient(90deg,#10B981,#1A3A6B,#F47B20)"></div>

        <div class="p-6 sm:p-8">
            <div class="text-center mb-6">
                <div class="w-16 h-16 bg-green-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-lock-open text-green-500 text-2xl"></i>
                </div>
                <h2 class="font-black text-gray-900 text-lg mb-1" style="font-family:Poppins,sans-serif">
                    Set a new password
                </h2>
                <p class="text-gray-500 text-xs">Enter your new password below</p>
            </div>

            @if($errors->any())
            <div class="bg-red-50 border-b border-red-100 px-5 py-3 flex items-start gap-2">
                <i class="fas fa-exclamation-circle text-red-400 text-sm flex-shrink-0 mt-0.5"></i>
                <div>
                    @foreach($errors->all() as $e)
                    <p class="text-red-700 text-xs font-medium">{{ $e }}</p>
                    @endforeach
                </div>
            </div>
            @endif

            <form action="{{ route('password.update') }}" method="POST" @submit="loading=true" class="space-y-5">
                @csrf
                <input type="hidden" name="token" value="{{ $token }}">
                <input type="hidden" name="email" value="{{ $email }}">

                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-1.5">New Password</label>
                    <div class="relative">
                        <i class="fas fa-lock absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 text-sm pointer-events-none"></i>
                        <input :type="showPass ? 'text' : 'password'" name="password" required
                            @input="checkStrength($event.target.value)"
                            placeholder="Minimum 8 characters"
                            class="w-full border-2 border-gray-100 bg-gray-50 rounded-2xl pl-10 pr-11 py-3.5 text-sm focus:border-primary focus:bg-white transition-colors">
                        <button type="button" @click="showPass=!showPass" class="absolute right-3.5 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 transition w-7 h-7 flex items-center justify-center">
                            <i :class="showPass ? 'fas fa-eye-slash' : 'fas fa-eye'" class="text-sm"></i>
                        </button>
                    </div>
                    {{-- Strength indicator --}}
                    <div class="flex gap-1 mt-2" x-show="pass.length > 0" x-transition>
                        @foreach([1,2,3,4] as $i)
                        <div class="flex-1 h-1.5 rounded-full transition-all duration-300"
                             :class="strength >= {{ $i }}
                                ? (strength <= 1 ? 'bg-red-400' : (strength <= 2 ? 'bg-yellow-400' : (strength <= 3 ? 'bg-blue-400' : 'bg-green-400')))
                                : 'bg-gray-200'"></div>
                        @endforeach
                    </div>
                    <p class="text-[10px] text-gray-400 mt-1" x-show="pass.length > 0" x-transition
                       x-text="['','Weak','Fair','Good','Strong'][strength] + ' password'"></p>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-1.5">Confirm Password</label>
                    <div class="relative">
                        <i class="fas fa-lock absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 text-sm pointer-events-none"></i>
                        <input :type="showConf ? 'text' : 'password'" name="password_confirmation" required
                            placeholder="Repeat your new password"
                            class="w-full border-2 border-gray-100 bg-gray-50 rounded-2xl pl-10 pr-11 py-3.5 text-sm focus:border-primary focus:bg-white transition-colors">
                        <button type="button" @click="showConf=!showConf" class="absolute right-3.5 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 transition w-7 h-7 flex items-center justify-center">
                            <i :class="showConf ? 'fas fa-eye-slash' : 'fas fa-eye'" class="text-sm"></i>
                        </button>
                    </div>
                </div>

                <button type="submit" :disabled="loading"
                    class="w-full text-white font-bold py-4 rounded-2xl text-sm transition active:scale-95 disabled:opacity-70 flex items-center justify-center gap-2"
                    style="background:linear-gradient(135deg,#10B981,#059669);box-shadow:0 4px 20px rgba(16,185,129,0.3)">
                    <i class="fas text-sm" :class="loading ? 'fa-spinner fa-spin' : 'fa-check-circle'"></i>
                    <span x-text="loading ? 'Saving…' : 'Reset My Password'"></span>
                </button>
            </form>
        </div>
    </div>

    {{-- Trust row --}}
    <div class="flex justify-center gap-6 mt-5 text-blue-300/40 text-xs">
        <span class="flex items-center gap-1.5"><i class="fas fa-shield-alt text-green-400/70"></i> Secure Reset</span>
        <span class="flex items-center gap-1.5"><i class="fas fa-lock text-green-400/70"></i> Encrypted</span>
    </div>

</div>
@endsection
