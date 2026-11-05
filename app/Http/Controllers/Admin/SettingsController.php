<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    private array $settingsMap = [
        'general'      => ['site_name','site_tagline','contact_email','contact_phone','contact_whatsapp','about_us_text'],
        'payment'      => ['funding_monnify_enabled','funding_paystack_enabled','funding_manual_enabled',
                           'funding_bank_name','funding_bank_account_number','funding_bank_account_name',
                           'funding_bank_instructions','funding_manual_min_amount',
                           'virtual_account_status','virtual_account_message'],
        'fees'         => ['service_fee_percent','supplier_payment_flat_fee','withdrawal_flat_fee',
                           'service_fee_photo','service_fee_packaging',
                           'supplier_payments_enabled','supplier_payment_min_rmb','supplier_payment_max_rmb','duty_vat_percent'],
        'storage'      => ['storage_fine_enabled','storage_fine_free_days','storage_fine_per_day'],
        'telegram'     => ['telegram_bot_token','telegram_channel_id','telegram_bot_username','telegram_webhook_secret'],
        'sms'          => ['termii_api_key','termii_sender_id','termii_enabled'],
        'monnify'      => ['monnify_api_key','monnify_secret_key','monnify_contract_code','monnify_base_url'],
        'paystack'     => ['paystack_public_key','paystack_secret_key','paystack_preferred_bank'],
        'scraper'      => ['scraper_require_approval','scraper_auto_refresh','scraper_daily_limit','scraper_weekly_limit','scraper_default_weight_kg'],
        'referral'     => ['referral_bonus_amount','referral_min_deposit'],
        // ── Announcements & Coming Soon ─────────────────────────────────────
        'announcement' => ['announcement_enabled','announcement_text',
                           'coming_soon_mode','coming_soon_title','coming_soon_message',
                           'launch_datetime','launch_progress',
                           'social_instagram','social_whatsapp_group','social_telegram_channel',
                           'whatsapp_support_number',
                           'local_delivery_fee_info'],
        // ── Vendor Marketplace ──────────────────────────────────────────────
        'vendor'       => ['vendor_marketplace_open','vendor_activation_fee','vendor_commission_percent',
                           'default_domestic_freight_cny',
                           'vendor_min_withdrawal','vendor_escrow_auto_release_hours',
                           'vendor_require_product_approval'],
        // ── Popup Banner ────────────────────────────────────────────────────
        'popup'        => ['popup_enabled','popup_title','popup_message','popup_image',
                           'popup_button_text','popup_button_url','popup_show_buy_now',
                           'popup_product_id','popup_frequency','popup_delay_seconds',
                           'popup_bg_color','popup_badge'],
        // ── Spin & Win ──────────────────────────────────────────────────────
        'spinwin'      => ['spinwin_enabled','spinwin_daily_limit','spinwin_cooldown_hours'],
        // ── BNPL Providers ──────────────────────────────────────────────────
        'bnpl'         => [
            'bnpl_klump_enabled','bnpl_klump_public_key','bnpl_klump_secret_key','bnpl_klump_env','bnpl_klump_min_amount',
            'bnpl_credpal_enabled','bnpl_credpal_public_key','bnpl_credpal_secret_key','bnpl_credpal_env','bnpl_credpal_min_amount',
            'bnpl_zilla_enabled','bnpl_zilla_public_key','bnpl_zilla_secret_key','bnpl_zilla_env','bnpl_zilla_min_amount',
        ],
    ];

    /**
     * Checkboxes that belong to each settings tab.
     * Only zero-out checkboxes for the tab currently being saved.
     */
    private array $tabCheckboxes = [
        'general'       => [],
        'payment'       => ['funding_monnify_enabled','funding_paystack_enabled','funding_manual_enabled'],
        'fees'          => ['storage_fine_enabled','supplier_payments_enabled'],
        'notifications' => ['termii_enabled'],
        'launch'        => ['announcement_enabled','coming_soon_mode','popup_enabled','popup_show_buy_now'],
        'vendor'        => ['vendor_marketplace_open','vendor_require_product_approval',
                            'scraper_require_approval','scraper_auto_refresh'],
        'spinwin'       => ['spinwin_enabled'],
        'bnpl'          => ['bnpl_klump_enabled','bnpl_credpal_enabled','bnpl_zilla_enabled'],
    ];

    public function index()
    {
        $groups   = $this->settingsMap;
        $settings = [];
        foreach (array_merge(...array_values($groups)) as $key) {
            $settings[$key] = Setting::get($key, '');
        }
        return view('admin.settings.index', compact('groups','settings'));
    }

    public function update(Request $request)
    {
        // Determine which tab is being saved (passed as hidden field from the form)
        $tab = $request->input('_tab', 'general');

        // Keys that should be saved to both DB (Settings) and .env file
        $envKeys = [
            'monnify_api_key'         => 'MONNIFY_API_KEY',
            'monnify_secret_key'      => 'MONNIFY_SECRET_KEY',
            'monnify_contract_code'   => 'MONNIFY_CONTRACT_CODE',
            'monnify_base_url'        => 'MONNIFY_BASE_URL',
            'paystack_public_key'     => 'PAYSTACK_PUBLIC_KEY',
            'paystack_secret_key'     => 'PAYSTACK_SECRET_KEY',
            'paystack_preferred_bank' => 'PAYSTACK_PREFERRED_BANK',
            'telegram_bot_token'      => 'TELEGRAM_BOT_TOKEN',
            'telegram_channel_id'     => 'TELEGRAM_CHANNEL_ID',
            'telegram_webhook_secret' => 'TELEGRAM_WEBHOOK_SECRET',
            'termii_api_key'          => 'TERMII_API_KEY',
            'termii_sender_id'        => 'TERMII_SENDER_ID',
            'termii_enabled'          => 'TERMII_ENABLED',
            // BNPL
            'bnpl_klump_public_key'   => 'KLUMP_PUBLIC_KEY',
            'bnpl_klump_secret_key'   => 'KLUMP_SECRET_KEY',
            'bnpl_credpal_public_key' => 'CREDPAL_PUBLIC_KEY',
            'bnpl_credpal_secret_key' => 'CREDPAL_SECRET_KEY',
            'bnpl_zilla_public_key'   => 'ZILLA_PUBLIC_KEY',
            'bnpl_zilla_secret_key'   => 'ZILLA_SECRET_KEY',
        ];

        foreach ($request->except(['_token', '_method', '_tab']) as $key => $value) {
            $group = 'general';
            foreach ($this->settingsMap as $g => $keys) {
                if (in_array($key, $keys)) { $group = $g; break; }
            }
            Setting::set($key, $value ?? '', $group);

            // Also update .env for critical API keys
            if (isset($envKeys[$key]) && $value !== null) {
                $this->updateEnvKey($envKeys[$key], $value);
            }
        }

        // Only zero-out checkboxes that belong to the tab being saved.
        // This prevents checkboxes from OTHER tabs from being wiped when they
        // aren't present in the submitted form data.
        $checkboxesForTab = $this->tabCheckboxes[$tab] ?? [];
        foreach ($checkboxesForTab as $ck) {
            if (!$request->has($ck)) {
                Setting::set($ck, '0');
            }
        }

        // Clear config + setting cache so new values take effect immediately
        \Illuminate\Support\Facades\Artisan::call('config:clear');
        \Illuminate\Support\Facades\Cache::flush();

        return back()->with('success', '✅ Settings saved successfully.');
    }

    private function updateEnvKey(string $envKey, string $value): void
    {
        $envPath = base_path('.env');
        if (!file_exists($envPath)) return;

        $content = file_get_contents($envPath);
        $value   = str_contains($value, ' ') ? "\"{$value}\"" : $value;

        if (str_contains($content, "{$envKey}=")) {
            // Replace existing key
            $content = preg_replace(
                "/^{$envKey}=.*/m",
                "{$envKey}={$value}",
                $content
            );
        } else {
            // Append new key
            $content .= "\n{$envKey}={$value}\n";
        }

        file_put_contents($envPath, $content);
    }
}
