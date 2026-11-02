@extends('layouts.auth')
@section('title', 'Create Account')
@section('content')

<div class="w-full max-w-md anim-fade-up">

    {{-- Back link --}}
    <div class="mb-4 text-center">
        <a href="{{ route('home') }}" class="inline-flex items-center gap-1.5 text-blue-300/70 hover:text-white text-xs font-medium transition">
            <i class="fas fa-arrow-left text-[10px]"></i> Back to BYBHUB
        </a>
    </div>

    {{-- Logo + headline --}}
    <div class="text-center mb-6">
        <a href="{{ route('home') }}" class="inline-block mb-3">
            <img src="{{ asset('images/logo.jpg') }}" alt="BYBHUB"
                 class="h-14 w-14 mx-auto rounded-2xl shadow-2xl object-cover ring-4 ring-white/15">
        </a>
        <h1 class="text-2xl font-black text-white" style="font-family:Poppins,sans-serif">
            Create your account 🚀
        </h1>
        <p class="text-blue-300 text-sm mt-1">Start shopping from China — it's free</p>
    </div>

    {{-- Card --}}
    <div class="bg-white rounded-3xl overflow-hidden shadow-2xl" style="box-shadow:0 24px 80px rgba(0,0,0,0.35)">

        {{-- Progress bar --}}
        <div style="background:linear-gradient(135deg,#1A3A6B,#2451A3)" class="px-6 pt-4 pb-3">
            <div class="flex items-center gap-1">
                @foreach(['Your Info','Security','Done'] as $i => $step)
                <div class="flex items-center {{ $loop->last ? '' : 'flex-1' }}">
                    <div class="flex items-center gap-1.5 flex-shrink-0">
                        <div class="w-5 h-5 rounded-full text-[10px] font-black flex items-center justify-center
                                    {{ $i === 0 ? 'bg-accent text-white' : 'bg-white/20 text-white/70' }}">
                            {{ $i + 1 }}
                        </div>
                        <span class="text-white/75 text-[10px] font-medium hidden sm:block">{{ $step }}</span>
                    </div>
                    @if(!$loop->last)
                    <div class="flex-1 h-px bg-white/20 mx-2"></div>
                    @endif
                </div>
                @endforeach
            </div>
        </div>

        {{-- Errors --}}
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

        <div class="p-5 sm:p-6"
             x-data="{
               showPass: false, loading: false, pass: '', strength: 0,
               chk(v) {
                 this.pass = v;
                 let s = 0;
                 if (v.length >= 8) s++;
                 if (/[A-Z]/.test(v)) s++;
                 if (/[0-9]/.test(v)) s++;
                 if (/[^A-Za-z0-9]/.test(v)) s++;
                 this.strength = s;
               },
               lbl() { return ['','Weak','Fair','Good','Strong'][this.strength]; },
               clr() { return ['','bg-red-400','bg-amber-400','bg-blue-500','bg-green-500'][this.strength]; }
             }">
            <form action="{{ route('register') }}" method="POST" class="space-y-3.5" @submit="loading=true">
                @csrf
                @if(request()->filled('ref'))
                <input type="hidden" name="ref" value="{{ request('ref') }}">
                @endif

                {{-- Section 1 --}}
                <div class="bg-gray-50 rounded-2xl p-4 space-y-3">
                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">
                        <i class="fas fa-user text-gray-300 mr-1"></i> Your Details
                    </p>

                    {{-- Name --}}
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-1">
                            Full Name <span class="text-red-400">*</span>
                        </label>
                        <div class="relative">
                            <i class="fas fa-user absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs pointer-events-none"></i>
                            <input type="text" name="name" value="{{ old('name') }}" required
                                   autocomplete="name" placeholder="e.g. Amina Johnson"
                                   class="w-full border-2 border-white bg-white rounded-xl pl-9 pr-4 py-2.5 text-sm
                                          focus:border-primary transition-colors @error('name') border-red-300 @enderror">
                        </div>
                    </div>

                    {{-- Phone --}}
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-1">
                            Phone <span class="text-red-400">*</span>
                        </label>
                        <div class="relative">
                            <i class="fas fa-phone absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs pointer-events-none"></i>
                            <input type="tel" name="phone" value="{{ old('phone') }}" required
                                   autocomplete="tel" placeholder="+234 801 234 5678"
                                   class="w-full border-2 border-white bg-white rounded-xl pl-9 pr-4 py-2.5 text-sm
                                          focus:border-primary transition-colors @error('phone') border-red-300 @enderror">
                        </div>
                    </div>

                    {{-- Email --}}
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-1">
                            Email <span class="text-red-400">*</span>
                        </label>
                        <div class="relative">
                            <i class="fas fa-envelope absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs pointer-events-none"></i>
                            <input type="email" name="email" value="{{ old('email') }}" required
                                   autocomplete="email" placeholder="you@email.com"
                                   class="w-full border-2 border-white bg-white rounded-xl pl-9 pr-4 py-2.5 text-sm
                                          focus:border-primary transition-colors @error('email') border-red-300 @enderror">
                        </div>
                    </div>

                    {{-- Country --}}
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-1">
                            Country <span class="text-red-400">*</span>
                        </label>
                        <div class="relative">
                            <i class="fas fa-globe absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs pointer-events-none"></i>
                            <select name="country" required
                                    class="w-full border-2 border-white bg-white rounded-xl pl-9 pr-8 py-2.5 text-sm
                                           focus:border-primary transition-colors appearance-none">
                                @foreach($countries as $code => $c)
                                <option value="{{ $code }}" {{ old('country','NG')===$code?'selected':'' }}>
                                    {{ $c['flag'] }} {{ $c['name'] }}
                                </option>
                                @endforeach
                            </select>
                            <i class="fas fa-chevron-down absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 text-[10px] pointer-events-none"></i>
                        </div>
                    </div>
                </div>

                {{-- Section 2 --}}
                <div class="bg-gray-50 rounded-2xl p-4 space-y-3">
                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">
                        <i class="fas fa-lock text-gray-300 mr-1"></i> Security
                    </p>

                    {{-- Password --}}
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-1">
                            Password <span class="text-red-400">*</span>
                        </label>
                        <div class="relative">
                            <i class="fas fa-lock absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs pointer-events-none"></i>
                            <input :type="showPass ? 'text' : 'password'" name="password" required
                                   autocomplete="new-password" placeholder="Min. 8 characters"
                                   @input="chk($event.target.value)"
                                   class="w-full border-2 border-white bg-white rounded-xl pl-9 pr-10 py-2.5 text-sm
                                          focus:border-primary transition-colors @error('password') border-red-300 @enderror">
                            <button type="button" @click="showPass=!showPass"
                                    class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 w-6 h-6 flex items-center justify-center">
                                <i :class="showPass?'fas fa-eye-slash':'fas fa-eye'" class="text-xs"></i>
                            </button>
                        </div>
                        {{-- Strength --}}
                        <div x-show="pass.length > 0" class="mt-1.5 space-y-1">
                            <div class="flex gap-1">
                                @for($i=1;$i<=4;$i++)
                                <div class="h-1 flex-1 rounded-full transition-all" :class="strength >= {{ $i }} ? clr() : 'bg-gray-200'"></div>
                                @endfor
                            </div>
                            <p class="text-[10px] font-semibold" x-text="lbl()"
                               :class="{'text-red-500':strength===1,'text-amber-500':strength===2,'text-blue-600':strength===3,'text-green-600':strength===4}"></p>
                        </div>
                    </div>

                    {{-- Confirm --}}
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-1">
                            Confirm Password <span class="text-red-400">*</span>
                        </label>
                        <div class="relative">
                            <i class="fas fa-lock absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs pointer-events-none"></i>
                            <input type="password" name="password_confirmation" required
                                   autocomplete="new-password" placeholder="Repeat password"
                                   class="w-full border-2 border-white bg-white rounded-xl pl-9 pr-4 py-2.5 text-sm
                                          focus:border-primary transition-colors">
                        </div>
                    </div>
                </div>

                {{-- Referral (optional) --}}
                @if(!request()->filled('ref'))
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-1">
                        Referral Code <span class="text-gray-400 font-normal normal-case">(optional)</span>
                    </label>
                    <div class="relative">
                        <i class="fas fa-gift absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs pointer-events-none"></i>
                        <input type="text" name="ref" value="{{ old('ref') }}"
                               placeholder="Enter code if you have one"
                               class="w-full border-2 border-gray-100 bg-gray-50 rounded-xl pl-9 pr-4 py-2.5 text-sm
                                      focus:border-primary focus:bg-white transition-colors">
                    </div>
                </div>
                @endif

                {{-- T&C --}}
                <label class="flex items-start gap-3 cursor-pointer select-none bg-amber-50 border border-amber-100 rounded-xl p-3">
                    <input type="checkbox" name="terms_accepted" required
                           class="mt-0.5 w-4 h-4 rounded accent-accent flex-shrink-0">
                    <span class="text-xs text-gray-600 leading-relaxed">
                        I agree to the
                        <a href="{{ route('terms') }}" target="_blank" class="text-primary font-bold hover:underline">Terms & Conditions</a>.
                        I understand BYBHUB is a procurement agent — no returns after purchase.
                    </span>
                </label>

                {{-- Submit --}}
                <button type="submit" :disabled="loading"
                        class="w-full text-white font-bold py-4 rounded-2xl text-sm transition active:scale-95 disabled:opacity-70
                               flex items-center justify-center gap-2"
                        style="background:linear-gradient(135deg,#F47B20,#D4660E);box-shadow:0 4px 20px rgba(244,123,32,0.3)">
                    <i class="fas text-sm" :class="loading ? 'fa-spinner fa-spin' : 'fa-user-plus'"></i>
                    <span x-text="loading ? 'Creating account…' : 'Create My Account'"></span>
                </button>

            </form>
        </div>

        {{-- Card footer --}}
        <div class="border-t border-gray-100 bg-gray-50/70 px-6 py-4 text-center">
            <p class="text-sm text-gray-500">
                Already have an account?
                <a href="{{ route('login') }}" class="text-primary font-bold hover:underline ml-1">
                    Sign In →
                </a>
            </p>
        </div>
    </div>

</div>
@endsection
