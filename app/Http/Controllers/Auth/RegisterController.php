<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Wallet;
use App\Services\MonnifyService;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class RegisterController extends Controller
{
    public function __construct(private MonnifyService $monnify) {}

    public function showForm()
    {
        $countries = config('bybhub.countries');
        return view('auth.register', compact('countries'));
    }

    public function register(Request $request)
    {
        $request->validate([
            'name'           => 'required|string|max:100',
            'email'          => 'required|email|unique:users,email',
            'phone'          => 'required|string|max:20',
            'password'       => 'required|min:8|confirmed',
            'country'        => 'required|in:NG,GH,NE',
            'terms_accepted' => 'accepted',
        ]);

        $countryCurrencies = ['NG' => 'NGN', 'GH' => 'GHS', 'NE' => 'XOF'];
        $countryLanguages  = ['NG' => 'en',  'GH' => 'en',  'NE' => 'fr'];

        $currency = $countryCurrencies[$request->country];
        $language = $countryLanguages[$request->country];

        DB::transaction(function () use ($request, $currency, $language) {
            $referralCode = strtoupper(Str::random(8));
            while (User::where('referral_code', $referralCode)->exists()) {
                $referralCode = strtoupper(Str::random(8));
            }

            // Resolve referrer
            $referredBy = null;
            if ($request->filled('ref')) {
                $referrer = User::where('referral_code', $request->ref)->first();
                $referredBy = $referrer?->id;
            }

            $user = User::create([
                'name'              => $request->name,
                'email'             => $request->email,
                'phone'             => $request->phone,
                'password'          => Hash::make($request->password),
                'country'           => $request->country,
                'currency'          => $currency,
                'language'          => $language,
                'kyc_verified'      => false,
                'referral_code'     => $referralCode,
                'referred_by'       => $referredBy,
            ]);

            // Create wallet
            Wallet::create(['user_id' => $user->id, 'currency' => $currency, 'balance' => 0]);

            // Record referral relationship (bonus paid later when referred user spends enough)
            if ($referredBy) {
                \App\Models\Referral::create([
                    'referrer_id'  => $referredBy,
                    'referred_id'  => $user->id,
                    'bonus_amount' => (float) Setting::get('referral_bonus_amount', 500),
                    'currency'     => 'NGN',
                    'bonus_paid'   => false,
                ]);
            }

            // Fire first_registration promotions
            app(\App\Services\PromotionService::class)->fire('first_registration', $user);

            // Welcome notification
            app(\App\Services\NotificationService::class)->send(
                $user,
                'wallet_credit',
                'Welcome to BYBHUB! 🎉',
                'Your account is ready. Browse products, paste a 1688 or Taobao link, fund your wallet, and place your first order. We handle the rest!',
                [],
                route('user.dashboard')
            );
            // Welcome email — non-critical, never crash registration if mail fails
            try {
                app(\App\Services\EmailService::class)->sendWelcome($user);
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning('Welcome email failed for user ' . $user->id . ': ' . $e->getMessage());
            }

            // Send email verification — non-critical, user can resend from dashboard
            try {
                $user->sendEmailVerificationNotification();
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning('Verification email failed for user ' . $user->id . ': ' . $e->getMessage());
            }

            // Create Monnify virtual account (Nigeria only)
            if ($request->country === 'NG' && Setting::get('funding_monnify_enabled', '1') === '1') {
                $this->monnify->createReservedAccount($user);
            }

            auth()->login($user);
        });

        return redirect()->route('user.dashboard')->with('success', __('auth.registered'));
    }
}
