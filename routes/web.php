<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\User\DashboardController;
use App\Http\Controllers\User\WalletController;
use App\Http\Controllers\User\WithdrawalController;
use App\Http\Controllers\User\SupplierPaymentController;
use App\Http\Controllers\User\ScraperController;
use App\Http\Controllers\User\ProductController;
use App\Http\Controllers\User\CartController;
use App\Http\Controllers\User\OrderController;
use App\Http\Controllers\User\ShippingCalculatorController;
use App\Http\Controllers\User\ReferralController;
use App\Http\Controllers\User\NotificationController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Admin\ExchangeRateController;
use App\Http\Controllers\Admin\ShippingRateController;
use App\Http\Controllers\Admin\SupplierPaymentController as AdminSupplierController;
use App\Http\Controllers\Admin\WithdrawalController as AdminWithdrawalController;
use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\FeaturedProductController;
use App\Http\Controllers\Admin\DiscountController;
use App\Http\Controllers\Admin\CouponController;
use App\Http\Controllers\Admin\BannerController;
use App\Http\Controllers\Admin\LocalFreightController;
use App\Http\Controllers\Admin\PurchaseFeeController;
use App\Http\Controllers\Admin\ManualFundingController;
use App\Http\Controllers\Admin\TelegramController;
use App\Http\Controllers\Admin\SettingsController;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/
Route::get('/img-proxy', [\App\Http\Controllers\ImageProxyController::class, 'proxy'])->name('img.proxy');
Route::get('/', [HomeController::class, 'index'])->name('home');

// ── SEO ────────────────────────────────────────────────────────────────────
// ── PWA / TWA ─────────────────────────────────────────────────────────────
// Digital Asset Links — required for TWA Android app to hide the URL bar
// After generating your APK via PWABuilder, update the SHA256 fingerprint in
// public/.well-known/assetlinks.json with the key from Google Play Console.
Route::get('/.well-known/assetlinks.json', function () {
    return response()->file(public_path('.well-known/assetlinks.json'), [
        'Content-Type' => 'application/json',
        'Access-Control-Allow-Origin' => '*',
        'Cache-Control' => 'no-cache',
    ]);
})->name('assetlinks');

Route::get('/robots.txt', function () {
    $content = "User-agent: *\n";
    $content .= "Disallow: /admin/\n";
    $content .= "Disallow: /dashboard/\n";
    $content .= "Disallow: /cart\n";
    $content .= "Disallow: /checkout\n";
    $content .= "Disallow: /img-proxy\n";
    $content .= "Allow: /products\n";
    $content .= "Allow: /store/\n";
    $content .= "Allow: /categories/\n";
    $content .= "Allow: /about\n";
    $content .= "Allow: /contact\n";
    $content .= "Sitemap: " . url('/sitemap.xml') . "\n";
    return response($content, 200)->header('Content-Type', 'text/plain');
})->name('robots');

Route::get('/sitemap.xml', function () {
    $urls = [];
    // Static pages
    $staticUrls = ['home','products.index','about','contact','terms','shipping.calculator','tracking.index'];
    foreach ($staticUrls as $r) {
        try { $urls[] = ['loc' => route($r), 'changefreq' => 'weekly', 'priority' => '0.8']; } catch (\Exception $e) {}
    }
    // Categories
    \App\Models\Category::where('is_active', true)->get()->each(function ($cat) use (&$urls) {
        $urls[] = ['loc' => route('products.category', $cat->slug), 'changefreq' => 'daily', 'priority' => '0.9'];
    });
    // Products
    \App\Models\Product::where('is_active', true)->where('is_approved', true)
        ->select('id', 'slug', 'updated_at')->latest()->limit(500)->get()->each(function ($p) use (&$urls) {
            $urls[] = ['loc' => route('products.show', $p), 'changefreq' => 'weekly', 'priority' => '0.7', 'lastmod' => $p->updated_at->toDateString()];
        });
    // Vendor stores
    \App\Models\VendorProfile::where('status', 'active')->select('store_slug')->get()->each(function ($v) use (&$urls) {
        $urls[] = ['loc' => route('store.show', $v->store_slug), 'changefreq' => 'daily', 'priority' => '0.8'];
    });
    $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
    $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
    foreach ($urls as $u) {
        $xml .= "  <url>\n";
        $xml .= "    <loc>" . htmlspecialchars($u['loc']) . "</loc>\n";
        if (!empty($u['lastmod'])) $xml .= "    <lastmod>{$u['lastmod']}</lastmod>\n";
        $xml .= "    <changefreq>{$u['changefreq']}</changefreq>\n";
        $xml .= "    <priority>{$u['priority']}</priority>\n";
        $xml .= "  </url>\n";
    }
    $xml .= '</urlset>';
    return response($xml, 200)->header('Content-Type', 'application/xml');
})->name('sitemap');
Route::get('/track', [\App\Http\Controllers\TrackingController::class, 'index'])->name('tracking.index');
Route::post('/track', [\App\Http\Controllers\TrackingController::class, 'track'])->name('tracking.track');
Route::get('/terms', [\App\Http\Controllers\TermsController::class, 'index'])->name('terms');
Route::get('/about', fn() => view('about'))->name('about');
Route::get('/contact', fn() => view('contact'))->name('contact');
Route::get('/offline', fn() => view('offline'))->name('offline');
Route::get('/elearn', fn() => redirect('https://elearn.bybimport.com'))->name('elearn');
Route::post('/waitlist', [\App\Http\Controllers\WaitlistController::class, 'store'])->name('waitlist.store')->middleware('throttle:5,1');

// Public vendor store pages
Route::get('/store/{slug}', [\App\Http\Controllers\StoreController::class, 'show'])->name('store.show');
Route::get('/shipping-calculator', [ShippingCalculatorController::class, 'index'])->name('shipping.calculator');
Route::post('/shipping-calculator', [ShippingCalculatorController::class, 'calculate'])->name('shipping.calculate');
Route::get('/products', [ProductController::class, 'index'])->name('products.index');
Route::get('/products/{product}', [ProductController::class, 'show'])->name('products.show');
Route::get('/categories/{category}', [ProductController::class, 'byCategory'])->name('products.category');

// Local vendor order checkout (requires auth)
Route::middleware(['auth','set.locale'])->group(function () {
    Route::get('/products/{product}/buy', [\App\Http\Controllers\User\LocalOrderController::class, 'checkoutForm'])->name('local.checkout');
    Route::post('/products/{product}/buy', [\App\Http\Controllers\User\LocalOrderController::class, 'placeOrder'])->name('local.order.place');
});

// Language switcher — persists to DB for logged-in users
Route::get('/language/{locale}', function ($locale) {
    if (in_array($locale, ['en', 'fr'])) {
        session(['locale' => $locale]);
        session()->save();
        app()->setLocale($locale);
        // Persist to user account if logged in
        if (auth()->check()) {
            auth()->user()->update(['language' => $locale]);
        }
    }
    return redirect()->back()->withHeaders(['Cache-Control' => 'no-store']);
})->name('language.switch');

// Currency switcher — persists to DB for logged-in users
Route::get('/currency/{currency}', function ($currency) {
    $allowed = array_keys(config('bybhub.display_currencies', []));
    if (in_array($currency, $allowed)) {
        session(['currency' => $currency]);
        session()->save();
        // Persist to user account if logged in
        if (auth()->check()) {
            auth()->user()->update(['currency' => $currency]);
        }
    }
    return redirect()->back()->withHeaders(['Cache-Control' => 'no-store']);
})->name('currency.switch');

/*
|--------------------------------------------------------------------------
| Auth Routes
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    Route::get('/register', [RegisterController::class, 'showForm'])->name('register');
    Route::post('/register', [RegisterController::class, 'register'])->middleware('throttle:10,1');
    Route::get('/login', [LoginController::class, 'showForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])->middleware('throttle:5,1');
    // Forgot password
    Route::get('/forgot-password', [\App\Http\Controllers\Auth\ForgotPasswordController::class, 'showForm'])->name('password.request');
    Route::post('/forgot-password', [\App\Http\Controllers\Auth\ForgotPasswordController::class, 'send'])->middleware('throttle:3,5')->name('password.email');
    Route::get('/reset-password/{token}', [\App\Http\Controllers\Auth\ResetPasswordController::class, 'showForm'])->name('password.reset');
    Route::post('/reset-password', [\App\Http\Controllers\Auth\ResetPasswordController::class, 'reset'])->middleware('throttle:5,1')->name('password.update');
});
Route::post('/logout', [LogoutController::class, 'logout'])->name('logout')->middleware('auth');

// ── Email Verification Routes ──────────────────────────────────────────────
Route::middleware('auth')->group(function () {
    Route::get('/email/verify', function () {
        if (auth()->user()->hasVerifiedEmail()) {
            return redirect()->route('user.dashboard');
        }
        return view('auth.verify-email');
    })->name('verification.notice');

    Route::get('/email/verify/{id}/{hash}', function (\Illuminate\Foundation\Auth\EmailVerificationRequest $request) {
        $request->fulfill();
        return redirect()->route('user.dashboard')->with('success', '✅ Your email has been verified! Welcome to BYBHUB.');
    })->middleware('signed')->name('verification.verify');

    Route::post('/email/verification-notification', function (\Illuminate\Http\Request $request) {
        if ($request->user()->hasVerifiedEmail()) {
            return back()->with('info', 'Email already verified.');
        }
        $request->user()->sendEmailVerificationNotification();
        return back()->with('success', 'Verification link sent! Check your inbox.');
    })->middleware('throttle:3,1')->name('verification.send');
});

/*
|--------------------------------------------------------------------------
| User Routes (authenticated)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'set.locale'])->prefix('dashboard')->name('user.')->group(function () {

    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // Order placed confirmation
    Route::get('/order-placed/{order}', function (\App\Models\Order $order) {
        abort_if($order->user_id !== auth()->id(), 403);
        return view('user.order-placed', compact('order'));
    })->name('order.placed');

    // Profile
    Route::get('/profile', [\App\Http\Controllers\User\ProfileController::class, 'edit'])->name('profile');
    Route::patch('/profile', [\App\Http\Controllers\User\ProfileController::class, 'update'])->name('profile.update');
    Route::patch('/profile/password', [\App\Http\Controllers\User\ProfileController::class, 'changePassword'])->name('profile.password');

    // Wallet
    Route::prefix('wallet')->name('wallet.')->group(function () {
        Route::get('/', [WalletController::class, 'index'])->name('index');
        Route::get('/fund', [WalletController::class, 'fundForm'])->name('fund');
        Route::get('/request-virtual-account', [WalletController::class, 'requestVirtualAccountForm'])->name('request-virtual-account');
        Route::post('/request-virtual-account', [WalletController::class, 'requestVirtualAccount'])->name('request-virtual-account.store')->middleware('throttle:3,1');
        Route::post('/fund/paystack', [WalletController::class, 'fundPaystack'])->name('fund.paystack')->middleware('throttle:10,1');
        Route::get('/fund/paystack/callback', [WalletController::class, 'paystackCallback'])->name('fund.paystack.callback');
        Route::get('/fund/manual', [WalletController::class, 'manualTransferForm'])->name('fund.manual');
        Route::post('/fund/manual', [WalletController::class, 'submitManualTransfer'])->name('fund.manual.submit')->middleware('throttle:5,1');
        Route::get('/transactions', [WalletController::class, 'transactions'])->name('transactions');
    });

    // Withdrawals — replaced by Wallet Transfer
    // (withdrawal routes kept for admin to view legacy records)

    // Wallet Transfer (user-to-user)
    Route::prefix('wallet/transfer')->name('wallet.transfer.')->group(function () {
        Route::get('/', [\App\Http\Controllers\User\WalletTransferController::class, 'form'])->name('form');
        Route::post('/', [\App\Http\Controllers\User\WalletTransferController::class, 'send'])->name('send');
    });

    // Supplier Payments (Pay via Alipay)
    Route::prefix('supplier-payments')->name('supplier.')->group(function () {
        Route::get('/', [SupplierPaymentController::class, 'index'])->name('index');
        Route::get('/create', [SupplierPaymentController::class, 'create'])->name('create');
        Route::post('/', [SupplierPaymentController::class, 'store'])->name('store')->middleware('throttle:5,1');
        Route::get('/{payment}', [SupplierPaymentController::class, 'show'])->name('show');
    });

    // Product Scraper
    Route::prefix('scraper')->name('scraper.')->group(function () {
        Route::get('/', [ScraperController::class, 'index'])->name('index');
        Route::post('/fetch', [ScraperController::class, 'fetch'])->name('fetch')->middleware('throttle:30,1');
        Route::post('/add-to-cart', [ScraperController::class, 'addToCart'])->name('add-to-cart');
        Route::get('/manual', [ScraperController::class, 'manualForm'])->name('manual');
        Route::post('/manual', [ScraperController::class, 'manualStore'])->name('manual.store');
    });

    // Cart
    Route::prefix('cart')->name('cart.')->group(function () {
        Route::get('/', [CartController::class, 'index'])->name('index');
        Route::post('/add', [CartController::class, 'add'])->name('add');
        Route::patch('/{item}', [CartController::class, 'update'])->name('update');
        Route::delete('/{item}', [CartController::class, 'remove'])->name('remove');
        Route::delete('/', [CartController::class, 'clear'])->name('clear');
        Route::post('/{item}/save-later', [CartController::class, 'saveLater'])->name('save-later');
        Route::post('/move-to-cart/{itemId}', [CartController::class, 'moveToCart'])->name('move-to-cart');
    });

    // Checkout (GET — dedicated checkout page)
    Route::get('/checkout', [OrderController::class, 'checkoutShow'])->name('checkout');

    // Orders
    Route::prefix('orders')->name('orders.')->group(function () {
        Route::get('/', [OrderController::class, 'index'])->name('index');
        Route::post('/checkout', [OrderController::class, 'checkout'])->name('checkout');
        Route::post('/klump-complete', [OrderController::class, 'klumpComplete'])->name('klump-complete');
        Route::post('/consolidate', [OrderController::class, 'consolidate'])->name('consolidate');
        Route::get('/{order}', [OrderController::class, 'show'])->name('show');
        Route::post('/{order}/cancel', [OrderController::class, 'cancel'])->name('cancel');
        Route::post('/{order}/photo', [OrderController::class, 'requestPhoto'])->name('photo');
        Route::post('/{order}/packaging', [OrderController::class, 'requestPackaging'])->name('packaging');
        Route::post('/{order}/pay-shipping', [OrderController::class, 'payShippingFee'])->name('pay-shipping');
        Route::post('/{order}/doorstep', [OrderController::class, 'requestDoorstep'])->name('doorstep');
        Route::post('/{order}/reorder', [OrderController::class, 'reOrder'])->name('reorder');
        Route::post('/{order}/confirm-pickup', [OrderController::class, 'confirmPickup'])->name('confirm-pickup');
        // Local vendor order buyer actions
        Route::post('/{order}/confirm-delivery',   [\App\Http\Controllers\Vendor\LocalOrderController::class, 'buyerConfirmDelivery'])->name('confirm-delivery');
        Route::post('/{order}/dispute',            [\App\Http\Controllers\Vendor\LocalOrderController::class, 'dispute'])->name('dispute');
        Route::post('/{order}/pay-local-delivery', [\App\Http\Controllers\User\LocalOrderController::class, 'payDeliveryFee'])->name('pay-local-delivery');
        // Messages
        Route::post('/{order}/messages', [\App\Http\Controllers\User\OrderMessageController::class, 'store'])->name('messages.store');
        Route::post('/{order}/messages/read', [\App\Http\Controllers\User\OrderMessageController::class, 'markRead'])->name('messages.read');
        // Rating
        Route::post('/{order}/rating', [\App\Http\Controllers\User\OrderRatingController::class, 'store'])->name('rating.store');
    });

    // Shipments (consolidated orders)
    Route::post('/shipments/{shipment}/pay', [OrderController::class, 'payShipmentFee'])->name('shipments.pay');

    // Referrals
    Route::get('/referrals', [ReferralController::class, 'index'])->name('referrals.index');

    // Notifications
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markRead'])->name('notifications.read');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllRead'])->name('notifications.read-all');

    // Telegram link
    Route::get('/link-telegram', [DashboardController::class, 'linkTelegram'])->name('link-telegram');

    // ── Vendor Marketplace (routes here get user.vendor.xxx names — keep for backward compat) ──
    // NOTE: actual vendor.xxx names are defined below, outside this group


    // Coupon validation (AJAX at checkout)
    Route::post('/coupon/validate', [\App\Http\Controllers\User\CartController::class, 'validateCoupon'])->name('coupon.validate');

    // Spin & Win
    Route::get('/spin-and-win', [\App\Http\Controllers\User\SpinwinController::class, 'show'])->name('spinwin.show');
    Route::post('/spin-and-win/spin', [\App\Http\Controllers\User\SpinwinController::class, 'spin'])->name('spinwin.spin')->middleware('throttle:5,1');

    // Admin returns from impersonation
    Route::get('/stop-impersonation', [\App\Http\Controllers\Admin\UserController::class, 'stopImpersonation'])
        ->name('stop-impersonation')->withoutMiddleware(['admin']);
});

/*
|--------------------------------------------------------------------------
| Vendor Routes (authenticated, named vendor.xxx)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'set.locale'])->prefix('dashboard/vendor')->name('vendor.')->group(function () {
    Route::get('/apply',      [\App\Http\Controllers\Vendor\VendorController::class, 'applyForm'])->name('apply');
    Route::post('/apply',     [\App\Http\Controllers\Vendor\VendorController::class, 'applyStore'])->name('apply.store');
    Route::get('/dashboard',  [\App\Http\Controllers\Vendor\VendorController::class, 'dashboard'])->name('dashboard');
    Route::get('/wallet',     [\App\Http\Controllers\Vendor\VendorController::class, 'wallet'])->name('wallet');
    Route::get('/analytics',  [\App\Http\Controllers\Vendor\VendorController::class, 'analytics'])->name('analytics');
    Route::get('/settings',   [\App\Http\Controllers\Vendor\VendorController::class, 'settingsForm'])->name('settings');
    Route::patch('/settings', [\App\Http\Controllers\Vendor\VendorController::class, 'settingsUpdate'])->name('settings.update');
    Route::get('/withdrawals',[\App\Http\Controllers\Vendor\VendorController::class, 'withdrawalHistory'])->name('withdrawal-history');
    Route::get('/earnings',   [\App\Http\Controllers\Vendor\VendorController::class, 'earningsHistory'])->name('earnings-history');
    Route::delete('/products/{product}/images/{index}', [\App\Http\Controllers\Vendor\VendorController::class, 'deleteProductImage'])->name('products.delete-image');
    Route::get('/products',   [\App\Http\Controllers\Vendor\VendorController::class, 'productsIndex'])->name('products');
    Route::get('/products/create',             [\App\Http\Controllers\Vendor\VendorController::class, 'productsCreate'])->name('products.create');
    Route::post('/products',                   [\App\Http\Controllers\Vendor\VendorController::class, 'productsStore'])->name('products.store');
    Route::get('/products/{product}/edit',     [\App\Http\Controllers\Vendor\VendorController::class, 'productsEdit'])->name('products.edit');
    Route::patch('/products/{product}',        [\App\Http\Controllers\Vendor\VendorController::class, 'productsUpdate'])->name('products.update');
    Route::delete('/products/{product}',       [\App\Http\Controllers\Vendor\VendorController::class, 'productsDestroy'])->name('products.destroy');
    Route::post('/withdrawal', [\App\Http\Controllers\Vendor\VendorController::class, 'withdrawalRequest'])->name('withdrawal.request')->middleware('throttle:5,1');
    // Local orders
    Route::get('/orders',                      [\App\Http\Controllers\Vendor\LocalOrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}',              [\App\Http\Controllers\Vendor\LocalOrderController::class, 'show'])->name('orders.show');
    Route::post('/orders/{order}/confirm',     [\App\Http\Controllers\Vendor\LocalOrderController::class, 'confirm'])->name('orders.confirm');
    Route::post('/orders/{order}/prepare',     [\App\Http\Controllers\Vendor\LocalOrderController::class, 'preparing'])->name('orders.preparing');
    Route::post('/orders/{order}/dispatch',    [\App\Http\Controllers\Vendor\LocalOrderController::class, 'dispatch'])->name('orders.dispatch');
});

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'admin', 'set.locale'])->prefix('admin')->name('admin.')->group(function () {

    Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');

    // Orders
    Route::prefix('orders')->name('orders.')->group(function () {
        Route::get('/', [AdminOrderController::class, 'index'])->name('index');
        Route::get('/{order}', [AdminOrderController::class, 'show'])->name('show');
        Route::patch('/{order}/status', [AdminOrderController::class, 'updateStatus'])->name('status');
        Route::patch('/{order}/tracking', [AdminOrderController::class, 'updateTracking'])->name('tracking');
        Route::post('/{order}/assign-shipping-fee', [AdminOrderController::class, 'assignShippingFee'])->name('assign-shipping-fee');
        Route::post('/{order}/assign-doorstep-fee', [AdminOrderController::class, 'assignDoorstepFee'])->name('assign-doorstep-fee');
        Route::post('/{order}/mark-fee-paid', [AdminOrderController::class, 'markFeePaid'])->name('mark-fee-paid');
        Route::post('/{order}/release-local-escrow', [\App\Http\Controllers\Vendor\LocalOrderController::class, 'releaseEscrow'])->name('release-local-escrow');
        Route::post('/{order}/assign-local-delivery-fee', [\App\Http\Controllers\Admin\OrderController::class, 'assignLocalDeliveryFee'])->name('assign-local-delivery-fee');
        Route::post('/{order}/cancel', [AdminOrderController::class, 'cancelOrder'])->name('cancel');
        Route::post('/{order}/note', [AdminOrderController::class, 'addNote'])->name('note');
        Route::post('/{order}/items/{item}/procurement', [AdminOrderController::class, 'updateProcurement'])->name('procurement.update');
        Route::post('/{order}/items/{item}/cancel', [AdminOrderController::class, 'cancelItem'])->name('item.cancel');
        Route::post('/{order}/message', [\App\Http\Controllers\Admin\OrderMessageController::class, 'reply'])->name('message.reply');
        Route::post('/{order}/messages/{message}/unpin', [\App\Http\Controllers\Admin\OrderMessageController::class, 'unpin'])->name('message.unpin');
        Route::post('/service-requests/{serviceRequest}/fulfill', [AdminOrderController::class, 'fulfillServiceRequest'])->name('service-request.fulfill');
    });

    // Shipments
    Route::prefix('shipments')->name('shipments.')->group(function () {
        Route::get('/', [AdminOrderController::class, 'shipmentsIndex'])->name('index');
        Route::get('/{shipment}', [AdminOrderController::class, 'shipmentShow'])->name('show');
        Route::post('/create', [AdminOrderController::class, 'createShipment'])->name('create');
        Route::post('/{shipment}/assign-fee', [AdminOrderController::class, 'assignShipmentFee'])->name('assign-fee');
        Route::patch('/{shipment}/status', [AdminOrderController::class, 'updateShipmentStatus'])->name('status');
    });

    // AJAX: calculate shipping fee from weight
    Route::get('/shipping-fee-calc', [AdminOrderController::class, 'calcShippingFee'])->name('shipping-fee-calc');

    // Waitlist
    Route::prefix('waitlist')->name('waitlist.')->group(function () {
        Route::get('/',        [\App\Http\Controllers\Admin\WaitlistController::class, 'index'])->name('index');
        Route::get('/export',  [\App\Http\Controllers\Admin\WaitlistController::class, 'export'])->name('export');
        Route::post('/notify', [\App\Http\Controllers\Admin\WaitlistController::class, 'notifyAll'])->name('notify');
    });

    // ── Financial Reports ──────────────────────────────────────────────────
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/financial',      [\App\Http\Controllers\Admin\FinancialReportController::class, 'index'])->name('financial');
        Route::get('/financial/csv',  [\App\Http\Controllers\Admin\FinancialReportController::class, 'exportCsv'])->name('financial.csv');
        Route::get('/financial/pdf',  [\App\Http\Controllers\Admin\FinancialReportController::class, 'exportPdf'])->name('financial.pdf');
    });

    // ── Abandoned Carts ────────────────────────────────────────────────────
    Route::prefix('abandoned-carts')->name('abandoned-carts.')->group(function () {
        Route::get('/',                [\App\Http\Controllers\Admin\AbandonedCartController::class, 'index'])->name('index');
        Route::post('/{user}/remind',  [\App\Http\Controllers\Admin\AbandonedCartController::class, 'remind'])->name('remind');
    });

    // Vendors
    Route::prefix('vendors')->name('vendors.')->group(function () {
        Route::get('/',                                  [\App\Http\Controllers\Admin\VendorController::class, 'index'])->name('index');
        Route::get('/withdrawals',                       [\App\Http\Controllers\Admin\VendorController::class, 'withdrawalIndex'])->name('withdrawals');
        Route::get('/{vendor}',                          [\App\Http\Controllers\Admin\VendorController::class, 'show'])->name('show');
        Route::get('/{vendor}/edit',                     [\App\Http\Controllers\Admin\VendorController::class, 'edit'])->name('edit');
        Route::patch('/{vendor}',                        [\App\Http\Controllers\Admin\VendorController::class, 'update'])->name('update');
        Route::delete('/{vendor}',                       [\App\Http\Controllers\Admin\VendorController::class, 'destroy'])->name('destroy');
        Route::post('/{vendor}/approve',                 [\App\Http\Controllers\Admin\VendorController::class, 'approve'])->name('approve');
        Route::post('/{vendor}/reject',                  [\App\Http\Controllers\Admin\VendorController::class, 'reject'])->name('reject');
        Route::post('/{vendor}/suspend',                 [\App\Http\Controllers\Admin\VendorController::class, 'suspend'])->name('suspend');
        Route::post('/{vendor}/reinstate',               [\App\Http\Controllers\Admin\VendorController::class, 'reinstate'])->name('reinstate');
        Route::post('/{vendor}/force-activate',          [\App\Http\Controllers\Admin\VendorController::class, 'forceActivate'])->name('force-activate');
        Route::post('/{vendor}/toggle-block',            [\App\Http\Controllers\Admin\VendorController::class, 'toggleBlock'])->name('toggle-block');
        Route::post('/{vendor}/adjust-balance',          [\App\Http\Controllers\Admin\VendorController::class, 'adjustBalance'])->name('adjust-balance');
        Route::post('/{vendor}/notes',                   [\App\Http\Controllers\Admin\VendorController::class, 'saveNotes'])->name('notes');
        Route::post('/{vendor}/release-earnings',        [\App\Http\Controllers\Admin\VendorController::class, 'releaseEarnings'])->name('release-earnings');
        Route::post('/{vendor}/kyc/verify',              [\App\Http\Controllers\Admin\VendorController::class, 'verifyKyc'])->name('kyc.verify');
        Route::post('/{vendor}/kyc/reject',              [\App\Http\Controllers\Admin\VendorController::class, 'rejectKyc'])->name('kyc.reject');
        Route::get('/{vendor}/kyc/document',             [\App\Http\Controllers\Admin\VendorController::class, 'viewKycDocument'])->name('kyc.document');
        Route::post('/products/{product}/approve',       [\App\Http\Controllers\Admin\VendorController::class, 'approveProduct'])->name('products.approve');
        Route::post('/products/{product}/reject',        [\App\Http\Controllers\Admin\VendorController::class, 'rejectProduct'])->name('products.reject');
        Route::post('/withdrawals/{withdrawal}/approve', [\App\Http\Controllers\Admin\VendorController::class, 'withdrawalApprove'])->name('withdrawals.approve');
        Route::post('/withdrawals/{withdrawal}/paid',    [\App\Http\Controllers\Admin\VendorController::class, 'withdrawalMarkPaid'])->name('withdrawals.paid');
        Route::post('/withdrawals/{withdrawal}/reject',  [\App\Http\Controllers\Admin\VendorController::class, 'withdrawalReject'])->name('withdrawals.reject');
    });

    // Users
    Route::prefix('users')->name('users.')->group(function () {
        Route::get('/', [AdminUserController::class, 'index'])->name('index');
        Route::get('/{user}', [AdminUserController::class, 'show'])->name('show');
        Route::get('/{user}/edit', [AdminUserController::class, 'edit'])->name('edit');
        Route::patch('/{user}', [AdminUserController::class, 'update'])->name('update');
        Route::patch('/{user}/toggle-block', [AdminUserController::class, 'toggleBlock'])->name('toggle-block');
        Route::post('/{user}/adjust-wallet', [AdminUserController::class, 'adjustWallet'])->name('adjust-wallet');
        Route::post('/{user}/login-as', [AdminUserController::class, 'loginAs'])->name('login-as');
        Route::get('/{user}/create-order', [AdminUserController::class, 'createOrderForm'])->name('create-order');
        Route::post('/{user}/create-order', [AdminUserController::class, 'createOrder'])->name('create-order.store');
        Route::post('/{user}/resend-verification', [AdminUserController::class, 'resendVerification'])->name('resend-verification');
    });

    // Promotions
    Route::prefix('promotions')->name('promotions.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\PromotionController::class, 'index'])->name('index');
        Route::get('/create', [\App\Http\Controllers\Admin\PromotionController::class, 'create'])->name('create');
        Route::post('/', [\App\Http\Controllers\Admin\PromotionController::class, 'store'])->name('store');
        Route::get('/{promotion}/edit', [\App\Http\Controllers\Admin\PromotionController::class, 'edit'])->name('edit');
        Route::patch('/{promotion}', [\App\Http\Controllers\Admin\PromotionController::class, 'update'])->name('update');
        Route::delete('/{promotion}', [\App\Http\Controllers\Admin\PromotionController::class, 'destroy'])->name('destroy');
        Route::patch('/{promotion}/toggle', [\App\Http\Controllers\Admin\PromotionController::class, 'toggle'])->name('toggle');
        Route::get('/{promotion}/usages', [\App\Http\Controllers\Admin\PromotionController::class, 'usages'])->name('usages');
        Route::post('/{promotion}/apply-to-user', [\App\Http\Controllers\Admin\PromotionController::class, 'applyToUser'])->name('apply-to-user');
    });

    // Exchange Rates
    Route::prefix('exchange-rates')->name('rates.')->group(function () {
        Route::get('/', [ExchangeRateController::class, 'index'])->name('index');
        Route::post('/', [ExchangeRateController::class, 'update'])->name('update');
        Route::get('/history', [ExchangeRateController::class, 'history'])->name('history');
    });

    // Shipping Rates
    Route::prefix('shipping-rates')->name('shipping.')->group(function () {
        Route::get('/', [ShippingRateController::class, 'index'])->name('index');
        Route::post('/', [ShippingRateController::class, 'store'])->name('store');
        Route::patch('/{rate}', [ShippingRateController::class, 'update'])->name('update');
        Route::delete('/{rate}', [ShippingRateController::class, 'destroy'])->name('destroy');
    });

    // Supplier Payments
    Route::prefix('supplier-payments')->name('supplier.')->group(function () {
        Route::get('/', [AdminSupplierController::class, 'index'])->name('index');
        Route::get('/{payment}', [AdminSupplierController::class, 'show'])->name('show');
        Route::post('/{payment}/approve', [AdminSupplierController::class, 'approve'])->name('approve');
        Route::post('/{payment}/reject', [AdminSupplierController::class, 'reject'])->name('reject');
    });

    // Withdrawals
    Route::prefix('withdrawals')->name('withdrawals.')->group(function () {
        Route::get('/', [AdminWithdrawalController::class, 'index'])->name('index');
        Route::get('/{withdrawal}', [AdminWithdrawalController::class, 'show'])->name('show');
        Route::post('/{withdrawal}/approve', [AdminWithdrawalController::class, 'approve'])->name('approve');
        Route::post('/{withdrawal}/reject', [AdminWithdrawalController::class, 'reject'])->name('reject');
    });

    // Products
    Route::prefix('products')->name('products.')->group(function () {
        Route::get('/', [AdminProductController::class, 'index'])->name('index');
        Route::get('/create', [AdminProductController::class, 'create'])->name('create');
        Route::post('/', [AdminProductController::class, 'store'])->name('store');
        Route::get('/scrape', [AdminProductController::class, 'scrapeImport'])->name('scrape');
        Route::post('/scrape/preview', [AdminProductController::class, 'scrapePreview'])->name('scrape.preview');
        Route::post('/scrape/save', [AdminProductController::class, 'scrapeStore'])->name('scrape.store');
        Route::get('/{product}/edit', [AdminProductController::class, 'edit'])->name('edit');
        Route::patch('/{product}', [AdminProductController::class, 'update'])->name('update');
        Route::delete('/{product}', [AdminProductController::class, 'destroy'])->name('destroy');
    });

    // Categories
    Route::resource('categories', CategoryController::class)->names('categories');

    // Featured Products
    Route::prefix('featured')->name('featured.')->group(function () {
        Route::get('/', [FeaturedProductController::class, 'index'])->name('index');
        Route::post('/toggle/{product}', [FeaturedProductController::class, 'toggle'])->name('toggle');
        Route::post('/sections/{product}', [FeaturedProductController::class, 'updateSections'])->name('sections');
        Route::post('/reorder', [FeaturedProductController::class, 'reorder'])->name('reorder');
    });

    // Discounts
    Route::prefix('discounts')->name('discounts.')->group(function () {
        Route::get('/', [DiscountController::class, 'index'])->name('index');
        Route::get('/create', [DiscountController::class, 'create'])->name('create');
        Route::post('/', [DiscountController::class, 'store'])->name('store');
        Route::get('/{discount}/edit', [DiscountController::class, 'edit'])->name('edit');
        Route::patch('/{discount}', [DiscountController::class, 'update'])->name('update');
        Route::delete('/{discount}', [DiscountController::class, 'destroy'])->name('destroy');
        Route::patch('/{discount}/toggle', [DiscountController::class, 'toggle'])->name('toggle');
    });

    // Coupons
    Route::prefix('coupons')->name('coupons.')->group(function () {
        Route::get('/', [CouponController::class, 'index'])->name('index');
        Route::get('/create', [CouponController::class, 'create'])->name('create');
        Route::post('/', [CouponController::class, 'store'])->name('store');
        Route::get('/{coupon}/edit', [CouponController::class, 'edit'])->name('edit');
        Route::patch('/{coupon}', [CouponController::class, 'update'])->name('update');
        Route::delete('/{coupon}', [CouponController::class, 'destroy'])->name('destroy');
        Route::patch('/{coupon}/toggle', [CouponController::class, 'toggle'])->name('toggle');
        Route::get('/{coupon}/usages', [CouponController::class, 'usages'])->name('usages');
    });

    // Banners
    Route::prefix('banners')->name('banners.')->group(function () {
        Route::get('/', [BannerController::class, 'index'])->name('index');
        Route::get('/create', [BannerController::class, 'create'])->name('create');
        Route::post('/', [BannerController::class, 'store'])->name('store');
        Route::get('/{banner}/edit', [BannerController::class, 'edit'])->name('edit');
        Route::patch('/{banner}', [BannerController::class, 'update'])->name('update');
        Route::delete('/{banner}', [BannerController::class, 'destroy'])->name('destroy');
        Route::post('/reorder', [BannerController::class, 'reorder'])->name('reorder');
    });

    // Purchase Fees
    Route::prefix('purchase-fees')->name('fees.')->group(function () {
        Route::get('/', [PurchaseFeeController::class, 'index'])->name('index');
        Route::get('/create', [PurchaseFeeController::class, 'create'])->name('create');
        Route::post('/', [PurchaseFeeController::class, 'store'])->name('store');
        Route::get('/{fee}/edit', [PurchaseFeeController::class, 'edit'])->name('edit');
        Route::patch('/{fee}', [PurchaseFeeController::class, 'update'])->name('update');
        Route::delete('/{fee}', [PurchaseFeeController::class, 'destroy'])->name('destroy');
    });

    // Local Freight Rates
    Route::prefix('local-freight')->name('freight.')->group(function () {
        Route::get('/', [LocalFreightController::class, 'index'])->name('index');
        Route::get('/create', [LocalFreightController::class, 'create'])->name('create');
        Route::post('/', [LocalFreightController::class, 'store'])->name('store');
        Route::get('/{rate}/edit', [LocalFreightController::class, 'edit'])->name('edit');
        Route::patch('/{rate}', [LocalFreightController::class, 'update'])->name('update');
        Route::delete('/{rate}', [LocalFreightController::class, 'destroy'])->name('destroy');
    });

    // Telegram
    Route::prefix('telegram')->name('telegram.')->group(function () {
        Route::get('/', [TelegramController::class, 'index'])->name('index');
        Route::post('/broadcast', [TelegramController::class, 'broadcast'])->name('broadcast');
        Route::get('/test-poll', [TelegramController::class, 'testPoll'])->name('test-poll');
        // Group Bot management
        Route::patch('/groups/{group}/toggle', [TelegramController::class, 'toggleGroup'])->name('groups.toggle');
        Route::post('/groups/{group}/post', [TelegramController::class, 'postToGroup'])->name('groups.post');
        Route::post('/post-product', [TelegramController::class, 'postProduct'])->name('post-product');
        Route::get('/settings', [TelegramController::class, 'settings'])->name('settings');
    });

    // Email Broadcast
    Route::prefix('broadcast')->name('broadcast.')->group(function () {
        Route::get('/',        [\App\Http\Controllers\Admin\BroadcastController::class, 'index'])->name('index');
        Route::post('/send',   [\App\Http\Controllers\Admin\BroadcastController::class, 'send'])->name('send');
        Route::post('/preview',[\App\Http\Controllers\Admin\BroadcastController::class, 'preview'])->name('preview');
    });

    // Settings
    Route::prefix('settings')->name('settings.')->group(function () {
        Route::get('/', [SettingsController::class, 'index'])->name('index');
        Route::post('/', [SettingsController::class, 'update'])->name('update');
    });

    // Spin & Win Prize Management
    Route::prefix('spin-and-win')->name('spinwin.')->group(function () {
        Route::get('/',              [\App\Http\Controllers\Admin\SpinwinController::class, 'index'])->name('index');
        Route::post('/',             [\App\Http\Controllers\Admin\SpinwinController::class, 'store'])->name('store');
        Route::patch('/{spinwin}',   [\App\Http\Controllers\Admin\SpinwinController::class, 'update'])->name('update');
        Route::delete('/{spinwin}',  [\App\Http\Controllers\Admin\SpinwinController::class, 'destroy'])->name('destroy');
        Route::patch('/{spinwin}/toggle', [\App\Http\Controllers\Admin\SpinwinController::class, 'toggle'])->name('toggle');
    });

    // Manual Funding Approvals
    Route::prefix('manual-funding')->name('manual-funding.')->group(function () {
        Route::get('/',                                  [ManualFundingController::class, 'index'])->name('index');
        Route::get('/{fundingRequest}',                  [ManualFundingController::class, 'show'])->name('show');
        Route::get('/{fundingRequest}/proof',            [ManualFundingController::class, 'viewProof'])->name('proof');
        Route::post('/{fundingRequest}/approve',         [ManualFundingController::class, 'approve'])->name('approve');
        Route::post('/{fundingRequest}/reject',          [ManualFundingController::class, 'reject'])->name('reject');
    });

    // Coupon AJAX validation
    Route::post('/coupon/validate', [CouponController::class, 'validateCoupon'])->name('coupon.validate');
});


/*
|--------------------------------------------------------------------------
| BYBLEARN — Hub eLearning Routes
| Accessible at hub.bybimport.com OR /hub on main domain
|--------------------------------------------------------------------------
*/
use App\Http\Controllers\Hub\HubController;
use App\Http\Controllers\Hub\CourseController;
use App\Http\Controllers\Hub\EnrollmentController;
use App\Http\Controllers\Hub\QuizController;
use App\Http\Controllers\Hub\StudentController;
use App\Http\Controllers\Hub\DiscussionController;
use App\Http\Controllers\Hub\Instructor\InstructorController;
use App\Http\Controllers\Hub\Admin\HubAdminController;

// ── Public Hub Routes ──────────────────────────────────────────────────────
Route::prefix('hub')->name('hub.')->group(function () {

    // ── Hub Auth ──────────────────────────────────────────────────────────
    Route::middleware('guest')->group(function () {
        Route::get('/login',    [\App\Http\Controllers\Hub\HubAuthController::class, 'showLogin'])->name('auth.login');
        Route::post('/login',   [\App\Http\Controllers\Hub\HubAuthController::class, 'login'])->name('auth.login.post')->middleware('throttle:5,1');
        Route::get('/register', [\App\Http\Controllers\Hub\HubAuthController::class, 'showRegister'])->name('auth.register');
        Route::post('/register',[\App\Http\Controllers\Hub\HubAuthController::class, 'register'])->name('auth.register.post')->middleware('throttle:5,1');
    });
    Route::post('/logout', [\App\Http\Controllers\Hub\HubAuthController::class, 'logout'])->name('auth.logout')->middleware('auth');

    Route::get('/',         [HubController::class, 'home'])->name('home');
    Route::get('/about',    [HubController::class, 'about'])->name('about');
    Route::get('/contact',  [HubController::class, 'contact'])->name('contact');
    Route::post('/contact', [HubController::class, 'contactSend'])->name('contact.send')->middleware('throttle:5,1');
    Route::get('/offline',  fn() => view('hub.offline'))->name('offline');

    // Certificate verification (public — anyone can verify)
    Route::get('/verify/{number}',     [HubController::class, 'verifyCertificate'])->name('certificate.verify');
    Route::get('/certificate/{number}',[HubController::class, 'verifyCertificate'])->name('certificate.show');

    // Courses (public browse)
    Route::get('/courses',             [CourseController::class, 'index'])->name('courses.index');
    Route::get('/courses/{slug}',      [CourseController::class, 'show'])->name('courses.show');

    // Bundles (public browse)
    Route::get('/bundles',             [\App\Http\Controllers\Hub\BundleController::class, 'index'])->name('bundles.index');
    Route::get('/bundles/{slug}',      [\App\Http\Controllers\Hub\BundleController::class, 'show'])->name('bundles.show');

    // Live Sessions (public browse)
    Route::get('/sessions',            [\App\Http\Controllers\Hub\LiveSessionController::class, 'index'])->name('sessions.index');

    // Coupon validation (AJAX)
    Route::post('/coupon/validate',    [EnrollmentController::class, 'validateCoupon'])->name('coupon.validate')->middleware('auth');

    // ── Auth-required Hub Routes ───────────────────────────────────────────
    Route::middleware(['hub.auth', 'set.locale'])->group(function () {

        // Enrollment & payment
        Route::get('/courses/{slug}/enroll',         [EnrollmentController::class, 'checkout'])->name('enroll');
        Route::post('/courses/{slug}/pay',           [EnrollmentController::class, 'pay'])->name('pay')->middleware('throttle:10,1');
        Route::get('/paystack/callback',             [EnrollmentController::class, 'paystackCallback'])->name('paystack.callback');

        // Installment / Study Now Pay Later
        Route::get('/courses/{slug}/installment',    [\App\Http\Controllers\Hub\InstallmentController::class, 'plans'])->name('installment.plans');
        Route::post('/courses/{slug}/installment',   [\App\Http\Controllers\Hub\InstallmentController::class, 'enroll'])->name('installment.enroll')->middleware('throttle:5,1');
        Route::post('/installment/{id}/pay',         [\App\Http\Controllers\Hub\InstallmentController::class, 'payInstallment'])->name('installment.pay')->middleware('throttle:10,1');

        // Lesson player + code editor
        Route::get('/courses/{slug}/learn',                    [CourseController::class, 'learn'])->name('courses.learn');
        Route::get('/courses/{slug}/learn/{lessonId}',         [CourseController::class, 'lesson'])->name('courses.lesson');
        Route::get('/courses/{slug}/code/{lessonId}',          [CourseController::class, 'codelesson'])->name('courses.codelesson');
        Route::post('/courses/{slug}/learn/{lessonId}/done',   [CourseController::class, 'completeLesson'])->name('lesson.complete');

        // Bank Transfer Payment
        Route::get('/courses/{slug}/transfer',                  [\App\Http\Controllers\Hub\TransferPaymentController::class, 'show'])->name('transfer.show');
        Route::post('/courses/{slug}/transfer',                 [\App\Http\Controllers\Hub\TransferPaymentController::class, 'submit'])->name('transfer.submit')->middleware('throttle:5,1');
        Route::get('/courses/{slug}/transfer/status',           [\App\Http\Controllers\Hub\TransferPaymentController::class, 'status'])->name('transfer.status');
        Route::get('/transfer/receipt/{id}',                    [\App\Http\Controllers\Hub\TransferPaymentController::class, 'receipt'])->name('transfer.receipt');

        // Quizzes
        Route::get('/courses/{slug}/quiz/{quizId}',    [QuizController::class, 'show'])->name('quiz.show');
        Route::post('/courses/{slug}/quiz/{quizId}',   [QuizController::class, 'submit'])->name('quiz.submit')->middleware('throttle:30,1');

        // Discussions
        Route::post('/courses/{slug}/lesson/{lessonId}/discuss', [DiscussionController::class, 'store'])->name('discussion.store')->middleware('throttle:10,1');

        // Student dashboard + profile
        Route::get('/learn/dashboard',  [StudentController::class, 'dashboard'])->name('student.dashboard');
        Route::get('/learn/profile',    [StudentController::class, 'profile'])->name('student.profile');
        Route::patch('/learn/profile',  [StudentController::class, 'profileUpdate'])->name('student.profile.update');
        Route::patch('/learn/password', [StudentController::class, 'profilePassword'])->name('student.profile.password');

        // Wallet connect (same or cross-database)
        Route::post('/wallet/connect',  [HubController::class, 'connectWallet'])->name('wallet.connect');
        Route::post('/wallet/dismiss',  [HubController::class, 'dismissWalletBanner'])->name('wallet.dismiss');

        // ── Quiz Result ────────────────────────────────────────────────────
        Route::get('/courses/{slug}/quiz/{quizId}/result/{attemptId}', [\App\Http\Controllers\Hub\QuizController::class, 'result'])->name('quiz.result');

        // ── Waitlist ───────────────────────────────────────────────────────
        Route::post('/courses/{slug}/waitlist', [\App\Http\Controllers\Hub\WaitlistController::class, 'join'])->name('waitlist.join')->middleware('throttle:5,1');
        Route::delete('/courses/{slug}/waitlist', [\App\Http\Controllers\Hub\WaitlistController::class, 'leave'])->name('waitlist.leave');

        // ── Bundles ────────────────────────────────────────────────────────
        Route::get('/bundles/{slug}/checkout', [\App\Http\Controllers\Hub\BundleController::class, 'checkout'])->name('bundles.checkout');
        Route::post('/bundles/{slug}/pay',     [\App\Http\Controllers\Hub\BundleController::class, 'pay'])->name('bundles.pay')->middleware('throttle:10,1');

        // ── Live Sessions ──────────────────────────────────────────────────
        Route::post('/sessions/{id}/rsvp',         [\App\Http\Controllers\Hub\LiveSessionController::class, 'rsvp'])->name('sessions.rsvp')->middleware('throttle:10,1');
        Route::get('/sessions/manage',             [\App\Http\Controllers\Hub\LiveSessionController::class, 'instructorIndex'])->name('sessions.instructor')->middleware('hub.instructor');
        Route::post('/sessions',                   [\App\Http\Controllers\Hub\LiveSessionController::class, 'store'])->name('sessions.store')->middleware('hub.instructor')->middleware('throttle:5,1');
        Route::post('/sessions/{id}/cancel',       [\App\Http\Controllers\Hub\LiveSessionController::class, 'cancel'])->name('sessions.cancel')->middleware('hub.instructor');
        Route::post('/sessions/{id}/recording',    [\App\Http\Controllers\Hub\LiveSessionController::class, 'addRecording'])->name('sessions.recording')->middleware('hub.instructor');

        // ── Notes Export ───────────────────────────────────────────────────
        Route::get('/courses/{slug}/notes/export', [\App\Http\Controllers\Hub\CourseController::class, 'exportNotes'])->name('courses.notes.export');

        // ── Referral link ──────────────────────────────────────────────────
        Route::get('/ref/{code}/{slug}', [\App\Http\Controllers\Hub\EnrollmentController::class, 'referralRedirect'])->name('courses.ref')->withoutMiddleware(['auth','set.locale']);

        // ── Instructor Analytics ───────────────────────────────────────────
        Route::get('/instructor/analytics', [\App\Http\Controllers\Hub\Instructor\AnalyticsController::class, 'index'])->name('instructor.analytics')->middleware('hub.instructor');

        // ── Notifications ──────────────────────────────────────────────────
        Route::prefix('notifications')->name('notifications.')->group(function () {
            Route::get('/',         [\App\Http\Controllers\Hub\HubNotificationController::class, 'index'])->name('index');
            Route::post('/read-all',[\App\Http\Controllers\Hub\HubNotificationController::class, 'markAllRead'])->name('read-all');
            Route::post('/{id}/read',[\App\Http\Controllers\Hub\HubNotificationController::class, 'markRead'])->name('read');
            Route::get('/unread',   [\App\Http\Controllers\Hub\HubNotificationController::class, 'unreadCount'])->name('unread');
        });

        // ── Community ──────────────────────────────────────────────────────
        Route::prefix('community')->name('community.')->group(function () {
            Route::get('/',                        [\App\Http\Controllers\Hub\CommunityController::class, 'index'])->name('index');
            Route::get('/{slug}',                  [\App\Http\Controllers\Hub\CommunityController::class, 'room'])->name('room');
            Route::post('/{slug}/send',            [\App\Http\Controllers\Hub\CommunityController::class, 'sendMessage'])->name('send')->middleware('throttle:30,1');
            Route::get('/{slug}/poll',             [\App\Http\Controllers\Hub\CommunityController::class, 'poll'])->name('poll');
            Route::delete('/messages/{id}',        [\App\Http\Controllers\Hub\CommunityController::class, 'deleteMessage'])->name('message.delete');
            Route::post('/rooms',                  [\App\Http\Controllers\Hub\CommunityController::class, 'createRoom'])->name('rooms.create')->middleware('admin');
        });

        // ── Instructor Routes ──────────────────────────────────────────────
        Route::prefix('instructor')->name('instructor.')->group(function () {

            // Apply (open to all auth users)
            Route::get('/apply',  [InstructorController::class, 'applyForm'])->name('apply');
            Route::post('/apply', [InstructorController::class, 'applyStore'])->name('apply.store')->middleware('throttle:5,60');

            // Instructor-only below
            Route::middleware('hub.instructor')->group(function () {
                Route::get('/dashboard',  [InstructorController::class, 'dashboard'])->name('dashboard');
                Route::get('/profile',    [InstructorController::class, 'profileEdit'])->name('profile');
                Route::patch('/profile',  [InstructorController::class, 'profileUpdate'])->name('profile.update');
                Route::post('/payout',    [InstructorController::class, 'payoutRequest'])->name('payout.request')->middleware('throttle:3,60');
                Route::post('/payment-account', [InstructorController::class, 'updatePaymentAccount'])->name('payment.account');

                Route::post('/courses/{courseId}/installment-plans',      [InstructorController::class, 'addInstallmentPlan'])->name('installment.add');
                Route::post('/installment-plans/{planId}/toggle',          [InstructorController::class, 'toggleInstallmentPlan'])->name('installment.toggle');

                // Course CRUD
                Route::get('/courses/create',         [InstructorController::class, 'courseCreate'])->name('courses.create');
                Route::post('/courses',               [InstructorController::class, 'courseStore'])->name('courses.store');
                Route::get('/courses/{id}/edit',      [InstructorController::class, 'courseEdit'])->name('courses.edit');
                Route::put('/courses/{id}',           [InstructorController::class, 'courseUpdate'])->name('courses.update');

                // Sections
                Route::get('/courses/{courseId}/sections/create',  [InstructorController::class, 'sectionCreate'])->name('sections.create');
                Route::post('/courses/{courseId}/sections',        [InstructorController::class, 'sectionStore'])->name('sections.store');

                // Lessons
                Route::get('/courses/{courseId}/lessons/create',              [InstructorController::class, 'lessonCreate'])->name('lessons.create');
                Route::post('/courses/{courseId}/lessons',                    [InstructorController::class, 'lessonStore'])->name('lessons.store');
                Route::get('/courses/{courseId}/lessons/{lessonId}/edit',     [InstructorController::class, 'lessonEdit'])->name('lessons.edit');
                Route::put('/courses/{courseId}/lessons/{lessonId}',          [InstructorController::class, 'lessonUpdate'])->name('lessons.update');
            });
        });

        // ── Admin Hub Routes ───────────────────────────────────────────────
        Route::middleware('admin')->prefix('admin')->name('admin.')->group(function () {
            Route::get('/',                         [HubAdminController::class, 'dashboard'])->name('dashboard');
            Route::get('/analytics',                [\App\Http\Controllers\Hub\Admin\AdminAnalyticsController::class, 'index'])->name('analytics');
            Route::get('/courses',                  [HubAdminController::class, 'courses'])->name('courses');
            Route::get('/courses/{id}/review',      [HubAdminController::class, 'courseReview'])->name('courses.review');
            Route::post('/courses/{id}/approve',    [HubAdminController::class, 'courseApprove'])->name('courses.approve');
            Route::post('/courses/{id}/reject',     [HubAdminController::class, 'courseReject'])->name('courses.reject');
            Route::get('/instructors',              [HubAdminController::class, 'instructors'])->name('instructors');
            Route::get('/instructors/{id}/review',  [HubAdminController::class, 'instructorReview'])->name('instructors.review');
            Route::post('/instructors/{id}/approve',[HubAdminController::class, 'instructorApprove'])->name('instructors.approve');
            Route::post('/instructors/{id}/reject', [HubAdminController::class, 'instructorReject'])->name('instructors.reject');
            Route::get('/enrollments',              [HubAdminController::class, 'enrollments'])->name('enrollments');
            Route::post('/enrollments/{id}/activate',[HubAdminController::class,'enrollmentActivate'])->name('enrollments.activate');
            Route::get('/certificates',             [HubAdminController::class, 'certificates'])->name('certificates');
            Route::get('/coupons',                  [HubAdminController::class, 'coupons'])->name('coupons');
            Route::post('/coupons',                 [HubAdminController::class, 'couponStore'])->name('coupons.store');
            Route::delete('/coupons/{id}',          [HubAdminController::class, 'couponDelete'])->name('coupons.delete');
            Route::get('/payouts',                  [HubAdminController::class, 'payouts'])->name('payouts');
            Route::post('/payouts/{id}/paid',       [HubAdminController::class, 'payoutMarkPaid'])->name('payouts.paid');
            Route::post('/payouts/{id}/reject',     [HubAdminController::class, 'payoutReject'])->name('payouts.reject');
            Route::get('/settings',                 [HubAdminController::class, 'settings'])->name('settings');
            Route::post('/settings',                [HubAdminController::class, 'settingsUpdate'])->name('settings.update');

            // Bank Transfer Payment Review
            Route::get('/transfers',                [\App\Http\Controllers\Hub\TransferPaymentController::class, 'adminIndex'])->name('transfers');
            Route::post('/transfers/{id}/approve',  [\App\Http\Controllers\Hub\TransferPaymentController::class, 'adminApprove'])->name('transfers.approve');
            Route::post('/transfers/{id}/decline',  [\App\Http\Controllers\Hub\TransferPaymentController::class, 'adminDecline'])->name('transfers.decline');
        });
    });
});
