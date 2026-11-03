<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with('category')->latest();
        if ($request->filled('q'))        $query->where('title','like','%'.$request->q.'%');
        if ($request->filled('category')) $query->where('category_id',$request->category);
        if ($request->filled('source'))   $query->where('source',$request->source);
        if ($request->filled('status'))   $query->where('stock_status',$request->status);
        if ($request->get('unapproved'))  $query->where('is_approved', false);
        $products   = $query->paginate(25)->withQueryString();
        $categories = Category::where('is_active',true)->get();
        return view('admin.products.index', compact('products','categories'));
    }

    public function create()
    {
        $categories = Category::where('is_active',true)->get();
        return view('admin.products.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title'       => 'required|string|max:300',
            'category_id' => 'nullable|exists:categories,id',
            'price_cny'   => 'required|numeric|min:0',
            'weight_kg'   => 'nullable|numeric|min:0',
            'images.*'    => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
            'source_url'  => 'nullable|url',
        ]);

        $images = [];
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $file) {
                $images[] = $file->store('products','public');
            }
        }

        Product::create(array_merge($request->except('images'), [
            'images'     => $images,
            'added_by'   => auth()->id(),
            'is_approved'=> true,
            'source'     => $request->source ?? 'manual',
        ]));

        return redirect()->route('admin.products.index')->with('success', 'Product created.');
    }

    public function edit(Product $product)
    {
        $categories = Category::where('is_active',true)->get();
        return view('admin.products.edit', compact('product','categories'));
    }

    public function update(Request $request, Product $product)
    {
        $request->validate(['title'=>'required|string|max:300','price_cny'=>'nullable|numeric|min:0']);

        // Handle image URLs textarea (replaces image list)
        $images = $product->images ?? [];
        if ($request->filled('image_urls')) {
            $images = array_values(array_filter(
                array_map('trim', explode("\n", $request->image_urls))
            ));
        }
        if ($request->hasFile('new_images')) {
            foreach ($request->file('new_images') as $file) {
                $images[] = $file->store('products','public');
            }
        }

        // Handle featured_sections checkboxes
        $featuredSections = $request->input('featured_sections', []);
        $isFeatured = !empty($featuredSections);

        // Handle price_tiers JSON
        $priceTiers = null;
        if ($request->filled('price_tiers')) {
            $decoded = json_decode($request->price_tiers, true);
            if (json_last_error() === JSON_ERROR_NONE) $priceTiers = $decoded;
        }

        // Handle variants JSON
        $variants = $product->variants;
        if ($request->filled('variants')) {
            $decoded = json_decode($request->variants, true);
            if (json_last_error() === JSON_ERROR_NONE) $variants = $decoded;
        }

        $product->update(array_merge(
            $request->except(['new_images','image_urls','featured_sections','price_tiers','variants','_token','_method']),
            [
                'images'            => $images,
                'featured_sections' => $featuredSections,
                'is_featured'       => $isFeatured,
                'price_tiers'       => $priceTiers,
                'variants'          => $variants,
                // Checkboxes — unchecked = false
                'is_active'         => $request->boolean('is_active'),
                'is_approved'       => $request->boolean('is_approved'),
                'is_featured'       => $isFeatured,
                'has_battery'       => $request->boolean('has_battery'),
                'has_liquid'        => $request->boolean('has_liquid'),
                'is_powder'         => $request->boolean('is_powder'),
                'is_food'           => $request->boolean('is_food'),
                'is_fragile'        => $request->boolean('is_fragile'),
                'is_oversized'      => $request->boolean('is_oversized'),
                'sample_available'  => $request->boolean('sample_available'),
                'is_flash_sale'     => $request->boolean('is_flash_sale'),
            ]
        ));

        return redirect()->route('admin.products.index')->with('success', "Product \"{$product->title}\" updated.");
    }

    public function destroy(Product $product)
    {
        $product->delete();
        return redirect()->route('admin.products.index')->with('success', 'Product deleted.');
    }

    // ── CSV Import (AliExpress product export CSV) ────────────────────────────

    public function csvImportForm()
    {
        $categories = Category::where('is_active', true)->get();
        return view('admin.products.csv-import', compact('categories'));
    }

    /**
     * Process AliExpress CSV export.
     *
     * CSV columns used:
     *  Title, Product Image Url, Sale price, Sale price formatted,
     *  Product description html, Main images url, Product Id, Product url,
     *  Attribute 1 name, Attribute 1 value(s), Attribute 2 name, Attribute 2 value(s)
     *
     * Price is in NGN in the CSV. We divide by the live NGN/CNY rate to store as CNY.
     */
    public function csvImport(\Illuminate\Http\Request $request)
    {
        $request->validate([
            'csv_file'    => 'required|file|mimes:csv,txt|max:10240',
            'category_id' => 'nullable|exists:categories,id',
        ]);

        // Live NGN→CNY rate for price conversion
        $ngnRate = (float) (\App\Models\ExchangeRate::where('from_currency', 'CNY')
            ->where('to_currency', 'NGN')
            ->value('rate') ?? 222);

        $file    = $request->file('csv_file');
        $handle  = fopen($file->getRealPath(), 'r');

        // Read header row
        $header = fgetcsv($handle);
        // Normalise headers: lowercase + trim
        $header = array_map(fn($h) => strtolower(trim(preg_replace('/[^a-z0-9 ]/i', '', $h))), $header);

        $col = fn(string $name) => array_search($name, $header);

        $imported = 0;
        $skipped  = 0;
        $errors   = [];

        while (($row = fgetcsv($handle)) !== false) {
            try {
                // Skip empty rows
                if (empty(array_filter($row))) { $skipped++; continue; }

                // Map by column index
                $get = function (string $colName) use ($row, $col) {
                    $idx = $col($colName);
                    return ($idx !== false && isset($row[$idx])) ? trim($row[$idx]) : '';
                };

                $title = $get('title');
                if (!$title) { $skipped++; continue; }

                // Price: "Sale price" column is numeric, "Sale price formatted" is human readable
                $salePriceNgn = (float) ($get('sale price') ?: preg_replace('/[^\d.]/', '', $get('sale price formatted')));
                $priceCny = $ngnRate > 0 && $salePriceNgn > 0
                    ? round($salePriceNgn / $ngnRate, 2)
                    : 0.01;

                // Images: "Main images url" is comma-separated protocol-relative URLs
                $rawImgs = $get('main images url');
                $images  = [];
                foreach (explode(',', $rawImgs) as $img) {
                    $img = trim($img);
                    if (!$img) continue;
                    if (!str_starts_with($img, 'http')) $img = 'https:' . $img;
                    $images[] = $img;
                }
                // Fallback to single Product Image Url
                if (empty($images)) {
                    $single = $get('product image url');
                    if ($single) {
                        if (!str_starts_with($single, 'http')) $single = 'https:' . $single;
                        $images[] = $single;
                    }
                }

                // Description: strip HTML tags from product description html
                $descHtml = $get('product description html');
                $desc     = $descHtml
                    ? mb_substr(strip_tags(html_entity_decode($descHtml)), 0, 2000)
                    : '';

                // Variants from Attribute 1 / Attribute 2
                $variants = [];
                foreach ([['attribute 1 name','attribute 1 values'],['attribute 2 name','attribute 2 values']] as [$nameCol,$valCol]) {
                    $attrName = $get($nameCol);
                    $attrVals = $get($valCol);
                    if ($attrName && $attrVals) {
                        $opts = array_map(
                            fn($v) => ['label' => trim($v), 'image' => null, 'price_cny' => null],
                            array_filter(array_map('trim', explode(',', $attrVals)))
                        );
                        if (count($opts) > 0) {
                            $variants[] = ['name' => $attrName, 'options' => array_values($opts)];
                        }
                    }
                }

                // Source URL
                $sourceUrl = $get('product url');
                $productId = $get('product id');
                if (!$sourceUrl && $productId) {
                    $sourceUrl = "https://www.aliexpress.com/item/{$productId}.html";
                }

                // Skip duplicate by source_url
                if ($sourceUrl && \App\Models\Product::where('source_url', $sourceUrl)->exists()) {
                    $skipped++;
                    continue;
                }

                $slug = \Illuminate\Support\Str::slug(mb_substr($title, 0, 80));
                if ($productId) $slug .= '-' . $productId;
                if (\App\Models\Product::where('slug', $slug)->exists()) {
                    $slug .= '-' . substr(md5(uniqid()), 0, 6);
                }

                \App\Models\Product::create([
                    'title'        => $title,
                    'slug'         => $slug,
                    'price_cny'    => $priceCny,
                    'images'       => array_slice($images, 0, 12),
                    'description'  => $desc,
                    'variants'     => $variants,
                    'source'       => 'aliexpress',
                    'source_url'   => $sourceUrl ?: null,
                    'category_id'  => $request->category_id ?: null,
                    'is_active'    => true,
                    'is_approved'  => true,
                    'stock_status' => 'in_stock',
                    'added_by'     => auth()->id(),
                ]);

                $imported++;
            } catch (\Exception $e) {
                $errors[] = "Row " . ($imported + $skipped + 1) . ": " . $e->getMessage();
                $skipped++;
            }
        }

        fclose($handle);

        $msg = "Import complete: {$imported} imported, {$skipped} skipped.";
        if ($errors) $msg .= " Errors: " . implode('; ', array_slice($errors, 0, 3));

        return redirect()->route('admin.products.index')
            ->with('success', $msg);
    }

    /**
     * Show the admin scrape-import page.
     */
    public function scrapeImport()
    {
        $categories = Category::where('is_active', true)->get();
        return view('admin.products.scrape-import', compact('categories'));
    }

    /**
     * Fetch product data from a URL and return as JSON (for the live-preview form).
     * Called by the admin scrape import UI via AJAX.
     */
    public function scrapePreview(\Illuminate\Http\Request $request)
    {
        $request->validate(['url' => 'required|url|max:2000']);

        $service = app(\App\Services\ScraperService::class);
        $result  = $service->scrape($request->url);

        if (!$result['success']) {
            return response()->json([
                'ok'      => false,
                'message' => $result['message'] ?? 'Could not fetch product data.',
                'manual'  => $result['can_manual'] ?? true,
                'source'  => $result['source']  ?? null,
                'url'     => $result['url']      ?? $request->url,
            ]);
        }

        $data = $result['data'];
        return response()->json([
            'ok'          => true,
            'source'      => $data['source']      ?? null,
            'source_url'  => $data['source_url']  ?? $request->url,
            'title'       => $data['title']        ?? '',
            'price_cny'   => $data['price_min']    ?? null,
            'price_max'   => $data['price_max']    ?? null,
            'description' => $data['description']  ?? '',
            'images'      => array_slice((array)($data['images'] ?? []), 0, 8),
            'variants'    => $data['variants']      ?? [],
            'moq'         => $data['moq']           ?? 1,
            'weight_kg'   => $data['weight_kg']     ?? null,
            'seller_name' => $data['seller_name']   ?? '',
            'price_note'  => $data['price_note']    ?? null,
        ]);
    }

    /**
     * Save the scraped product directly into the products table.
     */
    public function scrapeStore(\Illuminate\Http\Request $request)
    {
        $request->validate([
            'title'       => 'required|string|max:300',
            'price_cny'   => 'required|numeric|min:0',
            'source'      => 'required|string|max:50',
            'source_url'  => 'required|url|max:2000',
            'category_id' => 'nullable|exists:categories,id',
            'images'      => 'nullable|string',        // JSON array of image URLs
            'description' => 'nullable|string|max:5000',
            'weight_kg'   => 'nullable|numeric|min:0',
            'moq'         => 'nullable|integer|min:1',
            'variants'    => 'nullable|string',        // JSON
        ]);

        $images   = json_decode($request->images ?? '[]', true) ?: [];
        $variants = json_decode($request->variants ?? '[]', true) ?: [];

        $product = Product::create([
            'title'        => $request->title,
            'price_cny'    => (float) $request->price_cny,
            'source'       => $request->source,
            'source_url'   => $request->source_url,
            'category_id'  => $request->category_id,
            'images'       => $images,
            'description'  => $request->description,
            'weight_kg'    => $request->weight_kg ? (float) $request->weight_kg : null,
            'moq'          => max(1, (int) ($request->moq ?? 1)),
            'variants'     => $variants,
            'added_by'     => auth()->id(),
            'is_approved'  => true,
            'is_active'    => true,
            'stock_status' => 'in_stock',
        ]);

        return redirect()
            ->route('admin.products.edit', $product)
            ->with('success', "Product \"{$product->title}\" imported successfully. Review and save any changes.");
    }
}
