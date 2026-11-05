<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Services\ExchangeRateService;
use App\Services\ScraperService;
use Illuminate\Http\Request;

class ScraperController extends Controller
{
    public function __construct(
        private ScraperService     $scraper,
        private ExchangeRateService $rateService,
    ) {}

    public function index()
    {
        $user       = auth()->user();
        $categories = Category::where('is_active', true)->orderBy('name')->get();

        // Show daily + weekly limit info
        $dailyLimit  = (int) \App\Models\Setting::get('scraper_daily_limit', 20);
        $cacheKey    = "scrape_count_{$user->id}_" . today()->format('Ymd');
        $todayCount  = $dailyLimit > 0
            ? (int) \Illuminate\Support\Facades\Cache::get($cacheKey, 0)
            : 0;
        $remaining   = $dailyLimit > 0 ? max(0, $dailyLimit - $todayCount) : null;

        $weeklyLimit   = (int) \App\Models\Setting::get('scraper_weekly_limit', 0);
        $weekKey       = "scrape_count_{$user->id}_" . now()->format('o-W');
        $weekCount     = $weeklyLimit > 0 ? (int) \Illuminate\Support\Facades\Cache::get($weekKey, 0) : 0;
        $weekRemaining = $weeklyLimit > 0 ? max(0, $weeklyLimit - $weekCount) : null;

        return view('user.scraper.index', compact('user', 'categories', 'dailyLimit', 'todayCount', 'remaining', 'weeklyLimit', 'weekRemaining'));
    }

    public function fetch(Request $request)
    {
        $request->validate([
            'url' => 'required|url|max:2000',
        ]);

        $user     = auth()->user();
        $currency = \App\Helpers\CurrencyHelper::activeCurrency();
        $rate     = $this->rateService->getRate($currency);
        $symbol   = \App\Helpers\CurrencyHelper::symbol($currency);

        // ── Block AliExpress for regular users — admin only ───────────────
        $isAliExpress = str_contains(strtolower($request->url), 'aliexpress.com');
        if ($isAliExpress && !auth()->user()->is_admin) {
            return back()->with('error',
                'AliExpress products can only be added by BYBHUB admin. Use a 1688, Taobao, or Alibaba link instead.'
            )->withInput();
        }

        // ── Validate that the URL looks like a product page ───────────────
        $source = $this->scraper->detectSource($request->url);
        if (!$source && !$isAliExpress) {
            return back()->with('error',
                'That link doesn\'t look like a product page. Please paste a direct product URL from 1688, Taobao, Alibaba, or AliExpress.'
            )->withInput();
        }

        // ── Scrape limit per user per day ─────────────────────────────────
        $dailyLimit = (int) \App\Models\Setting::get('scraper_daily_limit', 20);
        if ($dailyLimit > 0) {
            $cacheKey = "scrape_count_{$user->id}_" . today()->format('Ymd');
            $todayCount = (int) \Illuminate\Support\Facades\Cache::get($cacheKey, 0);
            if ($todayCount >= $dailyLimit) {
                return back()->with('error',
                    "You've reached your daily limit of {$dailyLimit} product searches. Your limit resets at midnight."
                )->withInput();
            }
        }

        // ── Scrape limit per user per week (rolling ISO-week) ─────────────
        $weeklyLimit = (int) \App\Models\Setting::get('scraper_weekly_limit', 0);
        $weekKey     = "scrape_count_{$user->id}_" . now()->format('o-W');
        if ($weeklyLimit > 0) {
            $weekCount = (int) \Illuminate\Support\Facades\Cache::get($weekKey, 0);
            if ($weekCount >= $weeklyLimit) {
                return back()->with('error',
                    "You've reached your weekly limit of {$weeklyLimit} product searches. Your limit resets next week."
                )->withInput();
            }
        }

        // ── Duplicate URL check — reuse existing product instantly ────────
        $cleanUrl  = rtrim(preg_replace('/[?&](spm|scm|pvid|aff_|utm_|clickid|algo_|from)[^&]*/i', '', $request->url), '?&');
        $existing  = \App\Models\Product::where('source_url', $request->url)->first()
                  ?? \App\Models\Product::where('source_url', $cleanUrl)->first();

        if ($existing) {
            // Build $data from the existing product — no scrape needed
            $data = [
                'title'          => $existing->title,
                'title_cn'       => $existing->title_cn,
                'images'         => $existing->images ?? [],
                'price_cny'      => (float) $existing->price_cny,
                'price_cny_max'  => (float) ($existing->price_cny_max ?? $existing->price_cny),
                'price_tiers'    => $existing->price_tiers ?? [],
                'variants'       => $existing->variants ?? [],
                'stock_status'   => $existing->stock_status ?? 'in_stock',
                'seller_name'    => $existing->seller_name,
                'source'         => $existing->source,
                'source_url'     => $existing->source_url,
                'product_id'     => $existing->id,
                'category_id'    => $existing->category_id,
                'price_local'    => round((float)$existing->price_cny * $rate, 2),
                'price_local_fmt'=> $symbol . number_format(round((float)$existing->price_cny * $rate, 2), 2),
                'currency'       => $currency,
                'symbol'         => $symbol,
                'rate'           => $rate,
                'from_cache'     => true,
            ];

            // Increment scrape counter even for cache hits (counts as a lookup)
            if ($dailyLimit > 0) {
                $cacheKey = "scrape_count_{$user->id}_" . today()->format('Ymd');
                \Illuminate\Support\Facades\Cache::put($cacheKey, $todayCount + 1, now()->endOfDay());
            }
            if ($weeklyLimit > 0) {
                \Illuminate\Support\Facades\Cache::put($weekKey, (int) \Illuminate\Support\Facades\Cache::get($weekKey, 0) + 1, now()->endOfWeek());
            }

            $categories = \App\Models\Category::where('is_active', true)->orderBy('name')->get();
            $detectedCategory = $existing->category_id
                ? $categories->firstWhere('id', $existing->category_id)
                : null;

            session()->flash('info', '✅ Product loaded instantly — already in our database.');
            return view('user.scraper.result', compact('data', 'user', 'categories', 'detectedCategory'));
        }

        // ── Fresh scrape ──────────────────────────────────────────────────
        $result = $this->scraper->scrape($request->url);

        if (!$result['success']) {
            if (!empty($result['can_manual'])) {
                return redirect()->route('user.scraper.manual', [
                    'url'        => $request->url,
                    'source'     => $result['source'] ?? 'unknown',
                    'title_hint' => $result['title_hint'] ?? '',
                ])->with('warning', $result['message'] ?? 'Could not auto-fetch product. Please enter the details manually.');
            }
            return back()->with('error', $result['message'])->withInput();
        }

        $data = $result['data'];

        $data['price_local']     = round(($data['price_cny'] ?? 0) * $rate, 2);
        $data['price_local_fmt'] = $symbol . number_format($data['price_local'], 2);
        $data['currency']        = $currency;
        $data['symbol']          = $symbol;
        $data['rate']            = $rate;

        // Pre-detect category so result page can show it
        $autoCategoryId = $request->category_id
            ?: $this->scraper->detectCategoryPublic(
                $data['title'] ?? '',
                $data['title_cn'] ?? ''
            );
        $data['auto_category_id'] = $autoCategoryId;

        $product = $this->scraper->saveToMarketplace(
            $data,
            scrapedBy:  $user->id,
            categoryId: $request->category_id
        );

        $data['product_id']  = $product->id;
        $data['category_id'] = $product->category_id;

        // Increment daily + weekly scrape counters
        if ($dailyLimit > 0) {
            $cacheKey   = "scrape_count_{$user->id}_" . today()->format('Ymd');
            $todayCount = (int) \Illuminate\Support\Facades\Cache::get($cacheKey, 0);
            \Illuminate\Support\Facades\Cache::put($cacheKey, $todayCount + 1, now()->endOfDay());
        }
        if ($weeklyLimit > 0) {
            \Illuminate\Support\Facades\Cache::put($weekKey, (int) \Illuminate\Support\Facades\Cache::get($weekKey, 0) + 1, now()->endOfWeek());
        }

        $categories = \App\Models\Category::where('is_active', true)->orderBy('name')->get();
        $detectedCategory = $product->category_id
            ? $categories->firstWhere('id', $product->category_id)
            : null;

        if (!empty($data['needs_manual_edit'])) {
            session()->flash('warning', 'Product fetched with limited details. Please review and edit before ordering.');
        }

        return view('user.scraper.result', compact('data', 'user', 'categories', 'detectedCategory'));
    }

    /** Manual product entry form */
    public function manualForm(\Illuminate\Http\Request $request)
    {
        $user       = auth()->user();
        $categories = Category::where('is_active', true)->orderBy('name')->get();
        $url        = $request->get('url', '');
        $source     = $request->get('source', 'manual');
        return view('user.scraper.manual', compact('user', 'categories', 'url', 'source'));
    }

    /** Save manually entered product */
    public function manualStore(\Illuminate\Http\Request $request)
    {
        $request->validate([
            'title'       => 'required|string|max:300',
            'price_cny'   => 'required|numeric|min:0.01',
            'category_id' => 'nullable|exists:categories,id',
            'weight_kg'   => 'nullable|numeric|min:0',
            'moq'         => 'nullable|integer|min:1',
            'source_url'  => 'nullable|url|max:2000',
            'images.*'    => 'nullable|image|max:5120',
        ]);

        $user     = auth()->user();
        $currency = \App\Helpers\CurrencyHelper::activeCurrency();
        $rate     = $this->rateService->getRate($currency);
        $symbol   = \App\Helpers\CurrencyHelper::symbol($currency);

        // Handle uploaded images
        $images = [];
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $file) {
                $images[] = asset('storage/' . $file->store('products', 'public'));
            }
        }

        $data = [
            'title'       => $request->title,
            'title_cn'    => $request->title,
            'images'      => $images,
            'price_cny'   => (float) $request->price_cny,
            'stock_status'=> 'in_stock',
            'weight_kg'   => $request->weight_kg,
            'seller_name' => $request->seller_name,
            'description' => $request->description,
            'variants'    => [],
            'moq'         => (int)($request->moq ?? 1),
            'source'      => $request->source ?? 'manual',
            'source_url'  => $request->source_url ?? '',
        ];

        $data['price_local']     = round($data['price_cny'] * $rate, 2);
        $data['price_local_fmt'] = $symbol . number_format($data['price_local'], 2);
        $data['currency']        = $user->currency;
        $data['symbol']          = $symbol;
        $data['rate']            = $rate;

        $product = $this->scraper->saveToMarketplace(
            $data,
            scrapedBy:  $user->id,
            categoryId: $request->category_id
        );

        $data['product_id'] = $product->id;
        $categories = Category::where('is_active', true)->orderBy('name')->get();

        return view('user.scraper.result', compact('data', 'user', 'categories'));
    }

    public function addToCart(Request $request)
    {
        $request->validate([
            'product_id'       => 'required|exists:products,id',
            'quantity'         => 'required|integer|min:1|max:1000',
            'selected_variant' => 'nullable|string|max:200',
        ]);

        $product = \App\Models\Product::findOrFail($request->product_id);

        if ($product->isOutOfStock()) {
            return back()->with('error', __('products.out_of_stock'));
        }

        $user = auth()->user();
        $qty  = (int) $request->quantity;

        // Validate quantity against MOQ and step rule (e.g. multiples of 5 for fabric)
        $qtyError = $product->validateQty($qty);
        if ($qtyError) {
            return back()->with('error', $qtyError);
        }

        // Resolve price: flash sale → per-SKU price → tiered → base price
        $priceCny = $product->resolvePriceCny($request->selected_variant, $qty);

        \App\Models\CartItem::create([
            'user_id'          => $user->id,
            'product_id'       => $product->id,
            'selected_variant' => $request->selected_variant,
            'quantity'         => $qty,
            'unit_price_cny'   => $priceCny,
        ]);

        // Increment view count
        $product->increment('view_count');

        return redirect()->route('user.cart.index')
            ->with('success', __('cart.added'));
    }

    /** Update product weight (AJAX — user corrects scraper-extracted weight) */
    public function updateWeight(Request $request, \App\Models\Product $product)
    {
        $request->validate(['weight_kg' => 'required|numeric|min:0.01|max:999']);
        $product->update([
            'weight_kg'         => round((float) $request->weight_kg, 3),
            'package_weight_kg' => round((float) $request->weight_kg, 3),
        ]);
        return response()->json(['success' => true, 'weight_kg' => $product->weight_kg]);
    }
}
