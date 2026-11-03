<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Category;
use GuzzleHttp\Client;
use Symfony\Component\DomCrawler\Crawler;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class ScraperService
{
    private array $userAgents = [
        'Mozilla/5.0 (Linux; Android 10; SM-G975F) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Mobile Safari/537.36',
        'Mozilla/5.0 (iPhone; CPU iPhone OS 16_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/16.0 Mobile/15E148 Safari/604.1',
        'Mozilla/5.0 (Linux; Android 12; Pixel 6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Mobile Safari/537.36',
    ];

    // 1688 topCategoryId → our category slug mapping
    private array $categoryMap = [
        '201547801' => 'home',          // Kitchen/Home
        '50009316'  => 'electronics',   // Electronics
        '1498'      => 'fashion',        // Clothing
        '50013866'  => 'fashion',        // Women fashion
        '100006'    => 'fashion',        // Men fashion
        '116'       => 'phones',         // Mobile phones
        '44'        => 'electronics',    // Computer
        '1512'      => 'electronics',    // Digital
        '281'       => 'sports',         // Sports
        '57'        => 'food',           // Food
        '50016068'  => 'baby-kids',      // Baby
        '200219201' => 'health',         // Health/Beauty
        '200425408' => 'auto-parts',     // Auto parts
        '1004'      => 'tools',          // Tools/Hardware
    ];

    public function detectSource(string $url): ?string
    {
        if (str_contains($url, '1688.com'))        return '1688';
        if (str_contains($url, 'taobao.com'))      return 'taobao';
        if (str_contains($url, 'tmall.com'))       return 'taobao';
        if (str_contains($url, 'alibaba.com'))     return 'alibaba';
        if (str_contains($url, 'aliexpress.com'))  return 'aliexpress';
        return null;
    }

    /**
     * Resolve short / redirect URLs (qr.1688.com, t.cn, etc.) to their final destination.
     * Returns the resolved URL or the original if resolution fails.
     *
     * qr.1688.com uses a JavaScript redirect (window.location = decodeURIComponent('...'))
     * rather than an HTTP Location header, so we parse the JS body directly.
     */
    private function resolveRedirect(string $url): string
    {
        $shortDomains = ['qr.1688.com', 't.cn', 'dwz.cn', 'url.cn'];
        $host = parse_url($url, PHP_URL_HOST) ?? '';
        if (!in_array($host, $shortDomains)) return $url;

        try {
            $client = new Client([
                'timeout'         => 12,
                'connect_timeout' => 8,
                'verify'          => false,
                'allow_redirects' => ['max' => 10, 'track_redirects' => true],
                'headers'         => ['User-Agent' => $this->userAgents[0]],
            ]);

            $response = $client->get($url);

            // ── Check HTTP redirect history first ──────────────────────────
            $history = $response->getHeader(\GuzzleHttp\RedirectMiddleware::HISTORY_HEADER);
            if (!empty($history)) {
                $final = trim(end($history));
                if ($this->detectSource($final)) return $final;
            }

            $body = (string) $response->getBody();

            // ── qr.1688.com: extract URL from JS redirect ──────────────────
            // The page sets: var androidWapUrl = decodeURIComponent('https%3A%2F%2Fm.1688.com...')
            // or: window.location = decodeURIComponent('...')
            // or: window.location = "https://m.1688.com/..."
            // Also: offerId=\d+ appears directly in the encoded URL strings

            // Strategy 1: androidWapUrl variable (most reliable)
            if (preg_match('/var\s+androidWapUrl\s*=\s*decodeURIComponent\([\'"]([^\'"]+)[\'"]\)/', $body, $m)) {
                $decoded = urldecode($m[1]);
                if ($this->detectSource($decoded)) return $decoded;
            }

            // Strategy 2: window.location = decodeURIComponent('...')
            if (preg_match('/window\.location\s*=\s*decodeURIComponent\([\'"]([^\'"]{20,})[\'"]\)/', $body, $m)) {
                $decoded = urldecode($m[1]);
                if ($this->detectSource($decoded)) return $decoded;
            }

            // Strategy 3: window.location = "direct URL"
            if (preg_match('/window\.location\s*=\s*[\'"]([^\'"]{20,})[\'"]/', $body, $m)) {
                if ($this->detectSource($m[1])) return $m[1];
            }

            // Strategy 4: iframe src containing wireless1688:// with encoded offer URL
            if (preg_match('/url=(https?%3A%2F%2F[^"\'&\s]+)/', $body, $m)) {
                $decoded = urldecode($m[1]);
                if ($this->detectSource($decoded)) return $decoded;
            }

            // Strategy 5: extract offer ID directly from any encoded URL in the body
            if (preg_match('/offerId%3D(\d{10,})|offer%2F(\d{10,})|offerId=(\d{10,})|offer\/(\d{10,})/', $body, $m)) {
                $itemId = $m[1] ?: $m[2] ?: $m[3] ?: $m[4];
                if ($itemId) return "https://m.1688.com/offer/{$itemId}.html";
            }

        } catch (\Exception $e) {
            Log::warning("Redirect resolution failed for [$url]: " . $e->getMessage());
        }

        return $url;
    }

    private function extractItemId(string $url, string $source): ?string
    {
        if ($source === '1688') {
            if (preg_match('/offer[\/=](\d{10,})/', $url, $m)) return $m[1];
            if (preg_match('/offerId=(\d+)/', $url, $m))        return $m[1];
        }
        if ($source === 'taobao') {
            if (preg_match('/[?&]id=(\d+)/', $url, $m)) return $m[1];
        }
        if ($source === 'alibaba') {
            if (preg_match('/_(\d{8,})\.html/', $url, $m)) return $m[1];
        }
        if ($source === 'aliexpress') {
            if (preg_match('/item\/(\d+)\.html/', $url, $m))   return $m[1];
            if (preg_match('/[?&]id=(\d+)/', $url, $m))        return $m[1];
            if (preg_match('/\/(\d{10,})(?:\.html|$|\?)/', $url, $m)) return $m[1];
        }
        return null;
    }

    public function scrape(string $url): array
    {
        // Strip 1688 mobile share parameters that contain special chars (+, =, /)
        // which can break PCRE regex patterns when embedded in HTML content
        $url = preg_replace('/[?&](share_token|__share_id|__zhi_token|cbu_sv_id|src_cna|visitorId|callByHgJs|__removesafearea__)[^&]*/i', '', $url);
        $url = rtrim($url, '?&');

        // Step 1: resolve short/redirect links (qr.1688.com, etc.)
        $resolved = $this->resolveRedirect(trim($url));

        // If qr.1688.com resolution gave us back a non-1688 result or failed,
        // try fetching the short URL directly and following the Location header
        if (str_contains($url, 'qr.1688.com') && !str_contains($resolved, 'detail.1688.com') && !str_contains($resolved, 'm.1688.com') && !str_contains($resolved, 'offer')) {
            // Manual redirect follow via fetch
            try {
                $client = new Client([
                    'timeout' => 10, 'verify' => false,
                    'allow_redirects' => ['max' => 10],
                    'headers' => ['User-Agent' => $this->userAgents[0]],
                ]);
                $resp     = $client->get($url);
                $finalUri = (string) $resp->getBody();
                // The effective URI after all redirects
                if (method_exists($resp->getBody(), '__toString')) {
                    // Use request URI from history if available
                }
                // Try to extract 1688 offer ID from the HTML if redirected to a page
                if (preg_match('/offer[\/=](\d{10,})/', $finalUri, $m) ||
                    preg_match('/offer[\/=](\d{10,})/', (string) $resp->getBody(), $m)) {
                    $resolved = "https://m.1688.com/offer/{$m[1]}.html";
                }
            } catch (\Exception $e) {
                Log::warning("qr.1688.com fallback failed: " . $e->getMessage());
            }
        }

        $source = $this->detectSource($resolved);

        // If resolved URL is still undetectable, try original
        if (!$source) {
            $source = $this->detectSource($url);
            if (!$source) {
                return ['success'=>false,'message'=>'Unsupported link. Please use a 1688, Taobao, Alibaba, or AliExpress product link.','can_manual'=>false];
            }
            $resolved = $url;
        }

        try {
            $data = match($source) {
                '1688'       => $this->scrape1688($resolved),
                'taobao'     => $this->scrapeTaobao($resolved),
                'alibaba'    => $this->scrapeAlibaba($resolved),
                'aliexpress' => $this->scrapeAliExpress($resolved),
            };

            // ── Detect bot-block sentinel from scrape1688() ───────────────
            $botBlocked = is_array($data) && !empty($data['__bot_blocked__']);
            if ($botBlocked) $data = null;

            if (!$data) {
                // Build a source-specific, actionable error message
                if ($botBlocked || $source === '1688') {
                    $message = $botBlocked
                        ? '1688 is blocking automated access from this server (bot protection / x5sec). '
                          . 'This is a server IP restriction — not a problem with the link. '
                          . 'Please enter the product details manually: copy the title, price, and images directly from the 1688 page.'
                        : 'Could not read the 1688 product page. The link may have expired or the product may be unavailable. '
                          . 'Please check the link or enter the product details manually.';
                    return [
                        'success'    => false,
                        'message'    => $message,
                        'can_manual' => true,
                        'url'        => $resolved,
                        'source'     => $source,
                        'bot_blocked'=> $botBlocked,
                    ];
                }

                if ($source === 'alibaba') {
                    $meta = $this->extractAlibabaMetaFromUrl($resolved);
                    return [
                        'success'    => false,
                        'message'    => 'Alibaba protects its pages from automated access. Please enter the product details manually — paste the link and fill in the title, price and images from the Alibaba product page.',
                        'can_manual' => true,
                        'url'        => $resolved,
                        'source'     => $source,
                        'title_hint' => $meta['title_hint'],
                    ];
                }

                if ($source === 'aliexpress') {
                    return [
                        'success'    => false,
                        'message'    => 'Could not auto-fetch this AliExpress product (bot protection active). Please enter the product details manually — copy the title, price, images, and description from the AliExpress page.',
                        'can_manual' => true,
                        'url'        => $resolved,
                        'source'     => $source,
                        'title_hint' => '',
                    ];
                }

                // Taobao / generic fallback
                return [
                    'success'    => false,
                    'message'    => 'Could not extract product details automatically. The page may require login or the product is unavailable. Please enter the product details manually.',
                    'can_manual' => true,
                    'url'        => $resolved,
                    'source'     => $source,
                ];
            }

            $data['source']     = $source;
            $data['source_url'] = $resolved;
            return ['success' => true, 'data' => $data];

        } catch (\Exception $e) {
            Log::error('ScraperService error', ['url' => $resolved, 'err' => $e->getMessage()]);
            return [
                'success'    => false,
                'message'    => 'Something went wrong while fetching the product. Please enter the details manually.',
                'can_manual' => true,
                'url'        => $resolved,
                'source'     => $source ?? 'manual',
            ];
        }
    }

    private function scrape1688(string $url): ?array
    {
        $itemId = $this->extractItemId($url, '1688');
        if (!$itemId) return null;

        // ── Step 0: Direct mobile fetch — m.1688.com works from most servers ──
        // Mobile endpoint is less aggressively bot-protected than desktop.
        // Try this first before spending any Zenrows credits.
        $mobileHtml = $this->fetch(
            "https://m.1688.com/offer/{$itemId}.html",
            ['Referer' => 'https://www.1688.com/', 'Accept-Language' => 'zh-CN,zh;q=0.9'],
            true
        );
        if ($mobileHtml && $mobileHtml !== '__BOT_CHALLENGE__' && strlen($mobileHtml) >= 5000) {
            $parsed = $this->parse1688Html($mobileHtml, $itemId);
            if ($parsed) return $parsed;
        }

        // ── Step 1: Zenrows (residential proxy + JS render) ───────────────────
        // Falls back gracefully if ZENROWS_ENABLED=false or no key set.
        $zenrowsHtml = $this->fetchViaZenrows("https://detail.1688.com/offer/{$itemId}.html");
        if ($zenrowsHtml && $zenrowsHtml !== '__BOT_CHALLENGE__' && strlen($zenrowsHtml) >= 5000) {
            $parsed = $this->parse1688Html($zenrowsHtml, $itemId);
            if ($parsed) return $parsed;
        }

        // ── Step 2: Try the detailskip JSON API ───────────────────────────────
        $apiData = $this->fetch1688ApiData($itemId);
        if ($apiData) return $apiData;

        // ── Step 3: Try embedded page JSON (window.__INIT_DATA__ etc) ─────────
        $apiData = $this->extract1688EmbeddedData($itemId);
        if ($apiData) return $apiData;

        // ── Step 4: Desktop direct fetch with cookie warmup ───────────────────
        $result = $this->fetch(
            "https://detail.1688.com/offer/{$itemId}.html",
            ['Referer' => 'https://www.1688.com/', 'Accept-Language' => 'zh-CN,zh;q=0.9'],
            true
        );
        if ($result === '__BOT_CHALLENGE__') return ['__bot_blocked__' => true];
        if ($result && strlen($result) >= 5000) {
            $parsed = $this->parse1688Html($result, $itemId);
            if ($parsed) return $parsed;
        }

        return ['__bot_blocked__' => true];
    }

    /**
     * Parse all product data from a fetched 1688 HTML page.
     * Extracted from scrape1688() so both the direct-fetch and Zenrows paths share the same parser.
     */
    private function parse1688Html(string $html, string $itemId): ?array
    {
        // ── Title ──────────────────────────────────────────────────────────
        $titleCn = null;
        foreach (['"offerTitle"', '"subject"', '"title"'] as $key) {
            if (preg_match('/' . $key . '\s*:\s*"([^"]{5,})"/', $html, $m)) {
                $titleCn = $m[1];
                break;
            }
        }
        if (!$titleCn) return null;

        $title = $this->translateToEnglish($titleCn);

        // ── Category detection from topCategoryId ──────────────────────────
        $categoryId = null;
        if (preg_match('/"topCategoryId"\s*:\s*"?(\d+)"?/', $html, $m)) {
            $topId = $m[1];
            $slug  = $this->categoryMap[$topId] ?? null;
            if (!$slug) {
                // Try postCategoryId as fallback
                if (preg_match('/"postCategoryId"\s*:\s*"?(\d+)"?/', $html, $m2)) {
                    $slug = $this->categoryMap[$m2[1]] ?? null;
                }
            }
            if ($slug) {
                $cat = Category::where('slug', $slug)->first();
                $categoryId = $cat?->id;
            }
        }

        // ── Main product images ─────────────────────────────────────────────
        $mainImages = [];
        if (preg_match('/"offerImgList"\s*:\s*(\[[^\]]+\])/', $html, $m)) {
            $arr = json_decode($m[1], true);
            if (is_array($arr)) {
                foreach ($arr as $img) {
                    if (str_starts_with($img, 'http') && $this->isRealProductImage($img)) {
                        $mainImages[] = $img;
                    }
                }
            }
        }
        // Fallback: cbu01 images
        if (empty($mainImages)) {
            preg_match_all('/"imgUrl"\s*:\s*"(https?:\/\/cbu01[^"]+\.jpg)"/', $html, $im);
            foreach (array_values(array_unique($im[1])) as $img) {
                if ($this->isRealProductImage($img)) $mainImages[] = $img;
            }
        }
        $mainImages = array_slice($mainImages, 0, 8);

        // ── SKU Variants with images and prices ────────────────────────────
        $variants    = [];
        $skuImages   = [];

        // Strategy A: skuImages (mobile — color swatch with image)
        if (preg_match('/"skuImages"\s*:\s*(\[.{20,5000}?\])\s*[,}]/s', $html, $m)) {
            $arr = json_decode($m[1], true);
            if (is_array($arr)) {
                $optionsList = [];
                foreach ($arr as $sku) {
                    $name  = $sku['name'] ?? '';
                    $img   = $sku['imgUrl'] ?? '';
                    $price = (float)($sku['price'] ?? $sku['salePrice'] ?? 0);
                    $stock = isset($sku['quantity']) ? (int)$sku['quantity'] : null;
                    if (!$name) continue;
                    $nameEn = $this->translateToEnglish($name);
                    $skuImages[$name] = $img;
                    $optionsList[] = [
                        'label'     => $nameEn,
                        'label_cn'  => $name,
                        'image'     => $this->isRealProductImage($img) ? $img : '',
                        'price_cny' => $price > 0 ? $price : null,
                        'stock'     => $stock,
                    ];
                    if ($img && $this->isRealProductImage($img) && !in_array($img, $mainImages)) $mainImages[] = $img;
                }
                if (!empty($optionsList)) {
                    $variants[] = ['name' => 'Color / Style', 'options' => $optionsList];
                }
            }
        }

        // Strategy B: propsList / skuProps / props — multi-level (Color + Size etc.)
        foreach ([
            '/"propsList"\s*:\s*(\[.{10,5000}?\])\s*[,}]/s',
            '/"skuProps"\s*:\s*(\[.{10,5000}?\])\s*[,}]/s',
            '/"props"\s*:\s*(\[.{10,5000}?\])\s*[,}]/s',
        ] as $pattern) {
            if (preg_match($pattern, $html, $m)) {
                $arr = json_decode($m[1], true);
                if (!is_array($arr) || empty($arr)) continue;

                $parsed = [];
                foreach ($arr as $prop) {
                    $propNameCn = $prop['name'] ?? '';
                    if (!$propNameCn) continue;
                    $propNameEn = $this->translateToEnglish($propNameCn);

                    $opts     = [];
                    $rawVals  = $prop['values'] ?? $prop['options'] ?? [];
                    foreach ($rawVals as $val) {
                        if (is_string($val)) {
                            $opts[] = [
                                'label'     => $this->translateToEnglish($val),
                                'label_cn'  => $val,
                                'image'     => null,
                                'price_cny' => null,
                            ];
                        } elseif (is_array($val)) {
                            $nameCn = $val['name'] ?? $val['value'] ?? '';
                            if (!$nameCn) continue;
                            $img    = $val['imageUrl'] ?? $val['imgUrl'] ?? '';
                            $price  = (float)($val['price'] ?? 0);
                            $opts[] = [
                                'label'     => $this->translateToEnglish($nameCn),
                                'label_cn'  => $nameCn,
                                'image'     => $this->isRealProductImage($img) ? $img : null,
                                'price_cny' => $price > 0 ? $price : null,
                            ];
                            if ($img && $this->isRealProductImage($img) && !in_array($img, $mainImages)) {
                                $mainImages[] = $img;
                            }
                        }
                    }
                    if (!empty($opts)) {
                        // Don't duplicate a prop already extracted via skuImages
                        $alreadyHas = collect($variants)->contains(fn($v) => strtolower($v['name']) === strtolower($propNameEn));
                        if (!$alreadyHas) {
                            $parsed[] = ['name' => $propNameEn, 'name_cn' => $propNameCn, 'options' => $opts];
                        }
                    }
                }
                if (!empty($parsed)) {
                    $variants = array_merge($variants, $parsed);
                    break; // found a good props list, stop trying other patterns
                }
            }
        }

        // Strategy C: skuAttributes / attributes / skuModel — newer 1688 format
        if (empty($variants)) {
            foreach ([
                '/"skuAttributes"\s*:\s*(\[.{10,5000}?\])\s*[,}]/s',
                '/"attributes"\s*:\s*(\[.{10,5000}?\])\s*[,}]/s',
                '/"attrList"\s*:\s*(\[.{10,5000}?\])\s*[,}]/s',
                '/"specList"\s*:\s*(\[.{10,5000}?\])\s*[,}]/s',
                '/"offerAttributes"\s*:\s*(\[.{10,5000}?\])\s*[,}]/s',
            ] as $pattern) {
                if (!preg_match($pattern, $html, $m)) continue;
                $arr = json_decode($m[1], true);
                if (!is_array($arr) || empty($arr)) continue;

                $parsed = [];
                foreach ($arr as $prop) {
                    $propNameCn = $prop['attrName'] ?? $prop['name'] ?? $prop['specName'] ?? '';
                    if (!$propNameCn) continue;
                    $propNameEn = $this->translateToEnglish($propNameCn);

                    $rawVals = $prop['attrValues'] ?? $prop['values'] ?? $prop['specValues'] ?? [];
                    $opts    = [];
                    foreach ($rawVals as $val) {
                        if (is_string($val)) {
                            $opts[] = ['label' => $this->translateToEnglish($val), 'label_cn' => $val, 'image' => null, 'price_cny' => null];
                        } elseif (is_array($val)) {
                            $nameCn = $val['attrValue'] ?? $val['name'] ?? $val['value'] ?? '';
                            if (!$nameCn) continue;
                            $img   = $val['imageUrl'] ?? $val['imgUrl'] ?? $val['image'] ?? '';
                            $price = (float)($val['price'] ?? $val['skuPrice'] ?? 0);
                            $opts[] = [
                                'label'     => $this->translateToEnglish($nameCn),
                                'label_cn'  => $nameCn,
                                'image'     => $this->isRealProductImage($img) ? $img : null,
                                'price_cny' => $price > 0 ? $price : null,
                            ];
                            if ($img && $this->isRealProductImage($img) && !in_array($img, $mainImages)) {
                                $mainImages[] = $img;
                            }
                        }
                    }
                    if (!empty($opts)) {
                        $alreadyHas = collect($variants)->contains(fn($v) => strtolower($v['name']) === strtolower($propNameEn));
                        if (!$alreadyHas) {
                            $parsed[] = ['name' => $propNameEn, 'name_cn' => $propNameCn, 'options' => $opts];
                        }
                    }
                }
                if (!empty($parsed)) { $variants = array_merge($variants, $parsed); break; }
            }
        }

        // Strategy D: skuModel — extract from flat sku list (most complete)
        if (empty($variants)) {
            if (preg_match('/"skuModel"\s*:\s*(\[.{10,5000}?\])\s*[,}]/s', $html, $m) ||
                preg_match('/"skus"\s*:\s*(\[.{10,5000}?\])\s*[,}]/s', $html, $m)) {
                $skuArr = json_decode($m[1], true);
                if (is_array($skuArr) && !empty($skuArr)) {
                    // Collect all unique prop names and values from sku list
                    $propMap = [];
                    foreach ($skuArr as $sku) {
                        $props = $sku['properties'] ?? $sku['attrs'] ?? $sku['specValueIds'] ?? [];
                        if (is_array($props)) {
                            foreach ($props as $pk => $pv) {
                                if (is_array($pv)) {
                                    $pName = $pv['propName'] ?? $pv['name'] ?? (string)$pk;
                                    $pVal  = $pv['valueName'] ?? $pv['value'] ?? '';
                                } else {
                                    $pName = (string)$pk;
                                    $pVal  = (string)$pv;
                                }
                                if ($pName && $pVal && !in_array($pVal, $propMap[$pName] ?? [])) {
                                    $propMap[$pName][] = $pVal;
                                }
                            }
                        }
                    }
                    foreach ($propMap as $pName => $pVals) {
                        $pNameEn = $this->translateToEnglish($pName);
                        $opts    = array_map(fn($v) => ['label' => $this->translateToEnglish($v), 'label_cn' => $v, 'image' => null, 'price_cny' => null], $pVals);
                        $variants[] = ['name' => $pNameEn, 'name_cn' => $pName, 'options' => $opts];
                    }
                }
            }
        }

        // Strategy E: Simple regex for attribute lines like "颜色:红色|蓝色|绿色"
        if (empty($variants)) {
            preg_match_all('/"propName"\s*:\s*"([^"]+)"[^}]{0,200}"propValue"\s*:\s*"([^"]+)"/s', $html, $pm);
            if (!empty($pm[1])) {
                $propMap = [];
                for ($i = 0; $i < count($pm[1]); $i++) {
                    $pName = $pm[1][$i];
                    $pVal  = $pm[2][$i];
                    if (!in_array($pVal, $propMap[$pName] ?? [])) $propMap[$pName][] = $pVal;
                }
                foreach ($propMap as $pName => $pVals) {
                    if (count($pVals) < 2) continue; // skip single-value props
                    $variants[] = ['name' => $this->translateToEnglish($pName), 'options' => array_map(fn($v) => ['label' => $this->translateToEnglish($v), 'label_cn' => $v, 'image' => null, 'price_cny' => null], $pVals)];
                }
            }
        }
        $prices = [];
        if (preg_match('/"skuRangePrices"\s*:\s*(\[.{10,500}?\])/s', $html, $m)) {
            $arr = json_decode($m[1], true);
            if (is_array($arr)) {
                foreach ($arr as $tier) {
                    $p = (float)($tier['price'] ?? 0);
                    if ($p > 0 && $p < 99999) $prices[] = $p;
                }
            }
        }
        // Grab "price":"X" values — prefer whole-number prices over .XX bulk discount prices
        // e.g. ¥780.12 is a bulk discount tier, ¥788 is the real base SKU price
        preg_match_all('/"price"\s*:\s*"([\d.]+)"/', $html, $pm);
        $wholePrices = []; $allExtracted = [];
        foreach ($pm[1] as $p) {
            $fp = (float)$p;
            if ($fp <= 0 || $fp >= 99999) continue;
            $allExtracted[] = $fp;
            // Whole number OR .00 = real base price
            if (fmod($fp * 100, 1) < 0.01) $wholePrices[] = $fp;
        }
        // Use whole prices if available, otherwise fall back to all prices
        $prices = array_merge($prices, !empty($wholePrices) ? $wholePrices : $allExtracted);
        $prices = array_values(array_unique($prices));
        sort($prices);
        $priceMin = !empty($prices) ? $prices[0] : 0;
        $priceMax = count($prices) > 1 ? end($prices) : null;

        // ── Quantity price tiers ────────────────────────────────────────────
        $priceTiers = [];
        if (preg_match_all('/"price"\s*:\s*"([\d.]+)"\s*,\s*"beginAmount"\s*:\s*"?(\d+)"?/', $html, $tm)) {
            for ($i=0;$i<count($tm[1]);$i++) {
                $p=(float)$tm[1][$i];
                $qty=(int)$tm[2][$i];
                if($p>0&&$p<99999&&$qty>0) $priceTiers[]=['min_qty'=>$qty,'price'=>$p];
            }
        }
        if (empty($priceTiers)) {
            if (preg_match_all('/"beginAmount"\s*:\s*(\d+)[^}]{0,100}"price"\s*:\s*"([\d.]+)"/s',$html,$tm)) {
                for($i=0;$i<count($tm[1]);$i++){$p=(float)$tm[2][$i];$qty=(int)$tm[1][$i];if($p>0&&$p<99999&&$qty>0)$priceTiers[]=['min_qty'=>$qty,'price'=>$p];}
            }
        }
        // Deduplicate tiers
        $seen=[]; $priceTiers=array_values(array_filter($priceTiers,function($t)use(&$seen){$k=$t['min_qty'].'_'.$t['price'];if(isset($seen[$k]))return false;$seen[$k]=true;return true;}));
        // If ALL tiers have the same min_qty (e.g. all 1), they are SKU variant prices, NOT quantity tiers
        // Discard them — the SKU matrix / variants handle the pricing
        if (!empty($priceTiers)) {
            $uniqueQtys = array_unique(array_column($priceTiers, 'min_qty'));
            if (count($uniqueQtys) === 1) {
                $priceTiers = []; // not real quantity tiers
            }
        }

        // ── Seller / Stock / MOQ / Sold ─────────────────────────────────────
        $seller = null;
        if (preg_match('/"companyName"\s*:\s*"([^"]+)"/', $html, $m)) $seller = $m[1];

        $supplierId  = null;
        if (preg_match('/"memberId"\s*:\s*"?(\w+)"?/', $html, $m)) $supplierId = $m[1];
        if (!$supplierId && preg_match('/"userId"\s*:\s*"?(\d+)"?/', $html, $m)) $supplierId = $m[1];

        $supplierVerified = str_contains($html, '"isAuth":1')
            || str_contains($html, '"isVerified":true')
            || str_contains($html, '"isTrust":1');

        $supplierLevel = null;
        if (preg_match('/"supplierLevel"\s*:\s*"([^"]+)"/', $html, $m))  $supplierLevel = $m[1];
        elseif (preg_match('/"tradeScore"\s*:\s*(\d+)/', $html, $m))      $supplierLevel = 'score-'.$m[1];

        $supplierYears = null;
        if (preg_match('/"yearOfEstablish"\s*:\s*(\d{4})/', $html, $m))
            $supplierYears = (int)(date('Y') - $m[1]);
        elseif (preg_match('/"tradeYears"\s*:\s*(\d+)/', $html, $m))
            $supplierYears = (int)$m[1];

        $responseRate     = null;
        $repeatBuyerRate  = null;
        $disputeRate      = null;
        $reviewCount      = 0;
        if (preg_match('/"responseRate"\s*:\s*"?([\d.]+)"?/', $html, $m))    $responseRate    = (float)$m[1];
        if (preg_match('/"repeatBuyerRate"\s*:\s*"?([\d.]+)"?/', $html, $m)) $repeatBuyerRate = (float)$m[1];
        if (preg_match('/"disputePercent"\s*:\s*"?([\d.]+)"?/', $html, $m))  $disputeRate     = (float)$m[1];
        if (preg_match('/"evaluateCount"\s*:\s*(\d+)/', $html, $m))          $reviewCount     = (int)$m[1];

        $hasCustomisation   = str_contains($html, '"customizedLabel"') || str_contains($html, '"isCustomize":1');
        $exportOriented     = str_contains($html, '"isExport":1') || str_contains($html, '"exportAttr"');

        $supplierCity = null;
        if (preg_match('/"province"\s*:\s*"([^"]+)"/', $html, $m)) {
            $supplierCity = strtolower($this->translateToEnglish($m[1]));
        }
        // Chat URL
        $supplierChatUrl = $supplierId
            ? "https://amos.alibaba.com/msg.htm?scheme=amos&interactiveId=&fromSite=1688&memberId={$supplierId}"
            : null;

        $stock = 'in_stock'; $stockQty = null;
        if (preg_match('/"canBookedAmount"\s*:\s*(\d+)/', $html, $m)) {
            $stockQty = (int)$m[1];
            if ($stockQty === 0) $stock = 'out_of_stock';
            elseif ($stockQty < 10) $stock = 'low_stock';
        }

        $moq = 1;
        if (preg_match('/"beginNum"\s*:\s*(\d+)/', $html, $m) && (int)$m[1] > 0) $moq = (int)$m[1];

        // ── Unit label & quantity step (yard/metre/kg-based products) ────────
        // Detect from title, variants text, and page content.
        $unitLabel = null;
        $qtyStep   = 1;

        $unitHints = strtolower($titleCn . ' ' . $html);

        // Yard detection — common for Bazin, African prints, fabric
        if (preg_match('/1\s*[=＝]\s*1\s*(yard|码|yard)/ui', $unitHints) ||
            preg_match('/(卖码|按码|码装|每码|1码)/u', $unitHints) ||
            preg_match('/\b(yard|yards)\b/i', $titleCn)) {
            $unitLabel = 'yard';
            // Extract step from "multiples of X" instructions in variant text
            if (preg_match('/(?:multiples?\s+of|倍数|每次)\s*(\d+)/ui', $unitHints, $ms)) {
                $qtyStep = max(1, (int)$ms[1]);
            } elseif ($moq > 1) {
                $qtyStep = $moq; // default step = MOQ for yard products
            }
        }
        // Metre detection
        elseif (preg_match('/1\s*[=＝]\s*1\s*(metre|meter|米)/ui', $unitHints) ||
                preg_match('/按米|每米|卖米/u', $unitHints)) {
            $unitLabel = 'metre';
            if ($moq > 1) $qtyStep = $moq;
        }
        // KG detection
        elseif (preg_match('/1\s*[=＝]\s*1\s*kg/ui', $unitHints) ||
                preg_match('/按斤|每斤|按公斤|每公斤/u', $unitHints)) {
            $unitLabel = 'kg';
        }

        // Filter out text-instruction "variants" — the seller puts ordering notes
        // (e.g. "Quantity 1=1yard please take 5 yards or multiples of 5") as a
        // fake variant option. Strip any option whose label looks like a sentence.
        foreach ($variants as $vi => $variant) {
            $cleanOpts = array_filter($variant['options'] ?? [], function ($opt) {
                $label = $opt['label'] ?? '';
                // Real variant options are short (≤40 chars) and don't contain digits + text mixed instruction
                return strlen($label) <= 40
                    && !preg_match('/\d+\s*(yard|metre|meter|yard|码|米|kg|gram|piece|pcs)/i', $label)
                    && !preg_match('/please|take|multiple|quantity\s*1\s*=/i', $label);
            });
            if (empty($cleanOpts)) {
                unset($variants[$vi]); // entire dimension was just instructions
            } else {
                $variants[$vi]['options'] = array_values($cleanOpts);
            }
        }
        $variants = array_values($variants);

        // ── Build the comprehensive SKU price map ──────────────────────────
        // Do this AFTER extracting variants so we have the full HTML context.
        // The map covers all JSON price sources: skuModel, priceRangeList,
        // propsCombination, skuImages, skuRangePrices.
        $skuPriceMap = $this->buildSkuPriceMap($html);

        // ── Use the price map for the product-level min/max ────────────────
        if (!empty($skuPriceMap['all_prices'])) {
            $prices   = $skuPriceMap['all_prices'];
            $priceMin = $prices[0];
            $priceMax = count($prices) > 1 ? end($prices) : null;
        }

        // ── Backfill per-option prices from the SKU price map ─────────────
        // Many products have different prices per variant (e.g. shoes: size 36=¥45,
        // size 42=¥55). Without this backfill, all options show the product base price.
        foreach ($variants as &$variant) {
            $propNameCn = $variant['name_cn'] ?? $variant['name'] ?? '';
            foreach ($variant['options'] as &$opt) {
                $resolved = $this->resolveOptionPrice($opt, $propNameCn, $skuPriceMap);
                if ($resolved !== null) {
                    $opt['price_cny'] = $resolved;
                }
            }
            unset($opt);
        }
        unset($variant);

        // ── Also batch-translate any variant labels still in Chinese ────────
        // Collect all untranslated strings then translate in one batch call
        $toTranslate = [];
        foreach ($variants as $vi => $variant) {
            foreach ($variant['options'] as $oi => $opt) {
                $label = $opt['label'] ?? '';
                if (preg_match('/[\x{4e00}-\x{9fff}]/u', $label)) {
                    $toTranslate["v{$vi}_o{$oi}"] = $label;
                }
            }
        }
        if (!empty($toTranslate)) {
            $translated = $this->batchTranslate($toTranslate);
            foreach ($variants as $vi => &$variant) {
                foreach ($variant['options'] as $oi => &$opt) {
                    $key = "v{$vi}_o{$oi}";
                    if (isset($translated[$key])) {
                        $opt['label'] = $translated[$key];
                    }
                }
                unset($opt);
            }
            unset($variant);
        }

        $soldCount = 0;
        if (preg_match('/"saledCount"\s*:\s*(\d+)/', $html, $m)) $soldCount = (int)$m[1];

        // ── Sample price ────────────────────────────────────────────────────
        $sampleAvailable = false; $samplePrice = null;
        if (preg_match('/"samplePrice"\s*:\s*"?([\d.]+)"?/', $html, $m)) {
            $samplePrice     = (float)$m[1];
            $sampleAvailable = $samplePrice > 0;
        }
        if (!$sampleAvailable) {
            $sampleAvailable = str_contains($html, '"isSupportSample":1')
                || str_contains($html, '"sampleSwitch":1');
        }

        // ── Dimensions & weight ─────────────────────────────────────────────
        $weightKg = null; $lengthCm = null; $widthCm = null; $heightCm = null;
        if (preg_match('/"weight"\s*:\s*"?([\d.]+)"?\s*(?:kg)?/i', $html, $m)) $weightKg = (float)$m[1];
        if (preg_match('/"packageWeight"\s*:\s*"?([\d.]+)"?/', $html, $m))      $weightKg = (float)$m[1];
        if (preg_match('/"length"\s*:\s*"?([\d.]+)"?/', $html, $m))             $lengthCm = (float)$m[1];
        if (preg_match('/"width"\s*:\s*"?([\d.]+)"?/', $html, $m))              $widthCm  = (float)$m[1];
        if (preg_match('/"height"\s*:\s*"?([\d.]+)"?/', $html, $m))             $heightCm = (float)$m[1];

        $volumetricWeight = ($lengthCm && $widthCm && $heightCm)
            ? round(($lengthCm * $widthCm * $heightCm) / 6000, 3)
            : null;

        // ── Domestic freight (China to warehouse) ───────────────────────────
        $domesticFreight = null;
        // Try multiple 1688 JSON patterns for domestic shipping cost
        $freightPatterns = [
            '/"freightAmount"\s*:\s*"?([\d.]+)"?/',
            '/"postFee"\s*:\s*"?([\d.]+)"?/',
            '/"freightFee"\s*:\s*"?([\d.]+)"?/',
            '/"logisticFee"\s*:\s*"?([\d.]+)"?/',
            '/"shippingFee"\s*:\s*"?([\d.]+)"?/',
            '/"yunfei"\s*:\s*"?([\d.]+)"?/',
            '/快递.*?([\d.]+)\s*元/',
            '/运费[：:]\s*¥?([\d.]+)/',
        ];
        foreach ($freightPatterns as $pattern) {
            if (preg_match($pattern, $html, $m) && isset($m[1]) && (float)$m[1] > 0) {
                $val = (float)$m[1];
                // Domestic freight in China is never more than ¥50
                // Anything higher is the product price or international shipping being matched
                if ($val <= 50) {
                    $domesticFreight = $val;
                    break;
                }
            }
        }
        // If still null, try to get from the freight info block (often "包邮" = free shipping)
        if ($domesticFreight === null) {
            if (str_contains($html, '包邮') || str_contains($html, '"freeShipping":1') || str_contains($html, '"isFreeShipping":1')) {
                $domesticFreight = 0; // explicitly free
            } else {
                // Default domestic freight — admin can override per product
                $domesticFreight = (float) \App\Models\Setting::get('default_domestic_freight_cny', 15);
            }
        }

        // ── Shipping restrictions ────────────────────────────────────────────
        $hasBattery  = str_contains($html, '"hasBattery":1')  || preg_match('/电池|锂电/u', $titleCn);
        $hasLiquid   = str_contains($html, '"hasLiquid":1')   || preg_match('/液体|液态|乳液|精华/u', $titleCn);
        $isPowder    = str_contains($html, '"isPowder":1')    || preg_match('/粉末|粉剂|粉状/u', $titleCn);
        $isFood      = str_contains($html, '"isFood":1')      || preg_match('/食品|零食|饮料|糖果/u', $titleCn);
        $isFragile   = str_contains($html, '"isFragile":1')   || preg_match('/玻璃|陶瓷|易碎/u', $titleCn);

        // ── HS code ──────────────────────────────────────────────────────────
        $hsCode = null;
        if (preg_match('/"hsCode"\s*:\s*"([^"]+)"/', $html, $m)) $hsCode = $m[1];
        if (!$hsCode && preg_match('/"customsCode"\s*:\s*"([^"]+)"/', $html, $m)) $hsCode = $m[1];

        // ── Refund policy ────────────────────────────────────────────────────
        $refundPolicy = 'no_return';
        if (str_contains($html, '7天无理由退换') || str_contains($html, '"sevenDayReturn":1')) {
            $refundPolicy = '7_day_return';
        } elseif (str_contains($html, '质量保证') || str_contains($html, '"qualityAssurance":1')) {
            $refundPolicy = 'quality_guarantee';
        }

        // ── Build flat SKU matrix ────────────────────────────────────────────
        $skuMatrix = $this->buildSkuMatrix($html);

        // ── Description ──────────────────────────────────────────────────────
        $description = $this->buildDescription($html, $titleCn, $title, $priceTiers, $variants, $seller);

        return [
            'title'                     => $title,
            'title_cn'                  => $titleCn,
            'images'                    => array_slice($mainImages, 0, 8),
            'price_cny'                 => $priceMin,
            'price_cny_max'             => $priceMax,
            'price_tiers'               => $priceTiers,
            'sku_matrix'                => $skuMatrix,
            'stock_status'              => $stock,
            'stock_quantity'            => $stockQty,
            'seller_name'               => $seller,
            'supplier_id'               => $supplierId,
            'supplier_chat_url'         => $supplierChatUrl,
            'supplier_verified'         => $supplierVerified,
            'supplier_level'            => $supplierLevel,
            'supplier_years'            => $supplierYears,
            'supplier_response_rate'    => $responseRate,
            'supplier_repeat_buyer_rate'=> $repeatBuyerRate,
            'supplier_dispute_rate'     => $disputeRate,
            'supplier_review_count'     => $reviewCount,
            'supplier_has_customisation'=> $hasCustomisation,
            'supplier_export_oriented'  => $exportOriented,
            'supplier_city'             => $supplierCity,
            'description'               => $description,
            'variants'                  => $variants,
            'moq'                       => $moq,
            'unit_label'                => $unitLabel,
            'qty_step'                  => $qtyStep,
            'sold_count'                => $soldCount,
            'sample_available'          => $sampleAvailable,
            'sample_price_cny'          => $samplePrice,
            'weight_kg'                 => $weightKg,
            'length_cm'                 => $lengthCm,
            'width_cm'                  => $widthCm,
            'height_cm'                 => $heightCm,
            'volumetric_weight_kg'      => $volumetricWeight,
            'domestic_freight_cny'      => $domesticFreight,
            'has_battery'               => $hasBattery,
            'has_liquid'                => $hasLiquid,
            'is_powder'                 => $isPowder,
            'is_food'                   => $isFood,
            'is_fragile'                => $isFragile,
            'hs_code'                   => $hsCode,
            'refund_policy'             => $refundPolicy,
            'auto_category_id'          => $categoryId,
        ];
    }

    /**
     * Extract full product data including variants from 1688's embedded page JSON.
     * 1688 embeds the complete SKU/variant data inside <script> tags.
     * This works without authentication — falls back to HTML scraper if not found.
     */
    private function extract1688EmbeddedData(string $itemId): ?array
    {
        // Try all fetch strategies, cookie-warmed, to beat x5sec/TMD bot detection
        $fetchHeaders = ['Referer' => 'https://www.1688.com/', 'Accept-Language' => 'zh-CN,zh;q=0.9'];
        $endpoints = [
            "https://m.1688.com/offer/{$itemId}.html",
            "https://detail.1688.com/offer/{$itemId}.html",
        ];

        $html = null;
        foreach ($endpoints as $endpoint) {
            $result = $this->fetch($endpoint, $fetchHeaders, true /* use 1688 cookies */);
            if ($result && $result !== '__BOT_CHALLENGE__' && strlen($result) >= 5000) {
                $html = $result;
                break;
            }
        }
        if (!$html || strlen($html) < 5000) return null;
        if (str_contains($html, 'login.1688.com')) return null;

        $json = null;
        foreach ([
            '/window\.__INIT_DATA__\s*=\s*(\{.{500,}\})\s*;?\s*<\/script>/sU',
            '/window\.__GLOBAL_STATE__\s*=\s*(\{.{500,}\})\s*;?\s*<\/script>/sU',
            '/window\.detailData\s*=\s*(\{.{500,}\})\s*;?\s*<\/script>/sU',
        ] as $pattern) {
            if (preg_match($pattern, $html, $m)) {
                $decoded = json_decode($m[1], true);
                if ($decoded) { $json = $decoded; break; }
            }
        }
        if (!$json) return null;

        $offerData = $json['offerDetail'] ?? $json['data'] ?? $json['result'] ?? $json;
        $titleCn = $offerData['subject'] ?? $offerData['offerTitle'] ?? $offerData['title'] ?? null;
        if (!$titleCn) return null;
        $title = $this->translateToEnglish($titleCn);

        $mainImages = [];
        foreach ($offerData['offerImages'] ?? $offerData['images'] ?? [] as $img) {
            $u = is_string($img) ? $img : ($img['imgUrl'] ?? $img['url'] ?? '');
            if ($u && $this->isRealProductImage($u)) $mainImages[] = $u;
        }

        $priceMin = (float)($offerData['price'] ?? $offerData['priceInfo']['price'] ?? 0);
        $priceMax = null;
        $variants  = [];
        $skuMatrix = [];

        foreach ($offerData['skuProps'] ?? $offerData['propsList'] ?? [] as $prop) {
            $propName = $prop['propName'] ?? $prop['name'] ?? '';
            if (!$propName) continue;
            $propNameEn = $this->translateToEnglish($propName);
            $opts = [];
            foreach ($prop['propValues'] ?? $prop['values'] ?? [] as $val) {
                $vn = $val['displayName'] ?? $val['name'] ?? '';
                $vi = $val['imageUrl'] ?? $val['imgUrl'] ?? '';
                $vp = (float)($val['price'] ?? 0);
                if (!$vn) continue;
                $opts[] = ['label'=>$this->translateToEnglish($vn),'label_cn'=>$vn,'image'=>$this->isRealProductImage($vi)?$vi:null,'price_cny'=>$vp>0?$vp:null];
                if ($vi && $this->isRealProductImage($vi) && !in_array($vi, $mainImages)) $mainImages[] = $vi;
            }
            if (!empty($opts)) $variants[] = ['name'=>$propNameEn,'name_cn'=>$propName,'options'=>$opts];
        }

        foreach ($offerData['skus'] ?? $offerData['skuInfos'] ?? [] as $sku) {
            $sp = (float)($sku['price'] ?? $sku['salePrice'] ?? 0);
            $ss = isset($sku['quantity']) ? (int)$sku['quantity'] : null;
            $si = (string)($sku['skuId'] ?? $sku['id'] ?? '');
            $attrs = [];
            foreach ($sku['specValues'] ?? $sku['propValues'] ?? [] as $sv) {
                $pn = $sv['propName'] ?? $sv['name'] ?? '';
                $vn = $sv['displayName'] ?? $sv['value'] ?? '';
                if ($pn && $vn) $attrs[$this->translateToEnglish($pn)] = $this->translateToEnglish($vn);
            }
            if (!empty($attrs) && $sp > 0) {
                $skuMatrix[] = ['attributes'=>$attrs,'supplier_sku_id'=>$si?:null,'price_cny'=>$sp,'stock'=>$ss,'image'=>null];
                $priceMin = $priceMin > 0 ? min($priceMin, $sp) : $sp;
                if ($sp > $priceMin) $priceMax = max($priceMax ?? 0, $sp);
            }
        }

        if ($priceMin <= 0 || (empty($variants) && empty($skuMatrix))) return null;

        return [
            'source'=>'1688','title'=>$title,'title_cn'=>$titleCn,
            'images'=>array_slice($mainImages,0,8),
            'price_cny'=>$priceMin,'price_cny_max'=>$priceMax?:null,
            'price_tiers'=>[],'sku_matrix'=>$skuMatrix,'variants'=>$variants,
            'stock_status'=>'in_stock','stock_quantity'=>null,
            'moq'=>(int)($offerData['batchNumber']??$offerData['minOrderQuantity']??1)?:1,
            'domestic_freight_cny'=>0,
            'seller_name'=>$offerData['sellerInfo']['companyName']??null,
            'sold_count'=>(int)($offerData['tradeInfo']['soldCount']??0),
            'source_url'=>"https://detail.1688.com/offer/{$itemId}.html",
            'description'=>null,'has_battery'=>false,'has_liquid'=>false,'has_powder'=>false,'has_food'=>false,
        ];
    }

    /**
     * Try 1688's internal detailskip / offer-detail JSON APIs.
     * These are lighter endpoints that sometimes bypass the x5sec TMD challenge
     * because they return JSON rather than a full browser-rendered page.
     *
     * Returns a normalised product array on success, null on failure.
     */
    private function fetch1688ApiData(string $itemId): ?array
    {
        // Warm up cookies first so the API sees a valid session
        $this->warmup1688CookieJar();

        $endpoints = [
            // detailskip — internal API used by some 1688 app clients
            "https://detailskip.1688.com/service/getData/1/p/offerdetail/skumod.htm?offerId={$itemId}&__aplus&asyncFloor=1",
            // offer JSON endpoint (sometimes publicly accessible)
            "https://www.1688.com/page/offerdetail.htm?offerId={$itemId}",
        ];

        foreach ($endpoints as $endpoint) {
            try {
                $client = new Client([
                    'timeout'         => 15,
                    'connect_timeout' => 10,
                    'verify'          => false,
                    'cookies'         => $this->cookieJar,
                    'allow_redirects' => ['max' => 3],
                    'headers'         => [
                        'User-Agent'      => $this->userAgents[0],
                        'Accept'          => 'application/json, text/javascript, */*; q=0.01',
                        'Accept-Language' => 'zh-CN,zh;q=0.9',
                        'Referer'         => "https://detail.1688.com/offer/{$itemId}.html",
                        'X-Requested-With'=> 'XMLHttpRequest',
                    ],
                ]);

                $resp = $client->get($endpoint);
                $body = (string) $resp->getBody();

                if (strlen($body) < 100) continue;
                if ($this->isBotChallengePage($body)) continue;

                // Try to decode as JSON
                $json = json_decode($body, true);
                if (!$json) {
                    // Some endpoints wrap in JSONP: callback({...})
                    if (preg_match('/\w+\((\{.+\})\)\s*;?\s*$/s', $body, $m)) {
                        $json = json_decode($m[1], true);
                    }
                }
                if (!$json) continue;

                // Navigate to the offer data node — varies by endpoint
                $offer = $json['value']['offerPrice'] ?? null;
                $offer = $json['value']['offerDetail'] ?? $json['offerDetail'] ?? $json['data']['offerDetail'] ?? $json;

                $titleCn = $offer['subject'] ?? $offer['offerTitle'] ?? $offer['title'] ?? null;
                if (!$titleCn) continue;

                $title = $this->translateToEnglish($titleCn);

                // Images
                $mainImages = [];
                foreach ($offer['offerImages'] ?? $offer['images'] ?? [] as $img) {
                    $u = is_string($img) ? $img : ($img['imgUrl'] ?? $img['url'] ?? '');
                    if ($u && $this->isRealProductImage($u)) $mainImages[] = $u;
                }

                // Price
                $priceMin = (float)($offer['price'] ?? $offer['priceInfo']['price'] ?? 0);
                $priceMax = null;
                $variants  = [];
                $skuMatrix = [];

                // Variants from skuProps / propsList
                foreach ($offer['skuProps'] ?? $offer['propsList'] ?? [] as $prop) {
                    $propNameCn = $prop['propName'] ?? $prop['name'] ?? '';
                    if (!$propNameCn) continue;
                    $propNameEn = $this->translateToEnglish($propNameCn);
                    $opts = [];
                    foreach ($prop['propValues'] ?? $prop['values'] ?? [] as $val) {
                        $vn = $val['displayName'] ?? $val['name'] ?? '';
                        $vi = $val['imageUrl'] ?? $val['imgUrl'] ?? '';
                        $vp = (float)($val['price'] ?? 0);
                        if (!$vn) continue;
                        $opts[] = [
                            'label'     => $this->translateToEnglish($vn),
                            'label_cn'  => $vn,
                            'image'     => $this->isRealProductImage($vi) ? $vi : null,
                            'price_cny' => $vp > 0 ? $vp : null,
                        ];
                        if ($vi && $this->isRealProductImage($vi) && !in_array($vi, $mainImages)) {
                            $mainImages[] = $vi;
                        }
                    }
                    if (!empty($opts)) $variants[] = ['name' => $propNameEn, 'name_cn' => $propNameCn, 'options' => $opts];
                }

                // SKU matrix
                foreach ($offer['skus'] ?? $offer['skuInfos'] ?? [] as $sku) {
                    $sp    = (float)($sku['price'] ?? $sku['salePrice'] ?? 0);
                    $ss    = isset($sku['quantity']) ? (int)$sku['quantity'] : null;
                    $si    = (string)($sku['skuId'] ?? $sku['id'] ?? '');
                    $attrs = [];
                    foreach ($sku['specValues'] ?? $sku['propValues'] ?? [] as $sv) {
                        $pn = $sv['propName'] ?? $sv['name'] ?? '';
                        $vn = $sv['displayName'] ?? $sv['value'] ?? '';
                        if ($pn && $vn) $attrs[$this->translateToEnglish($pn)] = $this->translateToEnglish($vn);
                    }
                    if (!empty($attrs) && $sp > 0) {
                        $skuMatrix[] = ['attributes' => $attrs, 'supplier_sku_id' => $si ?: null, 'price_cny' => $sp, 'stock' => $ss, 'image' => null];
                        $priceMin = $priceMin > 0 ? min($priceMin, $sp) : $sp;
                        if ($sp > $priceMin) $priceMax = max($priceMax ?? 0, $sp);
                    }
                }

                if ($priceMin <= 0) continue;

                Log::info("1688 API success via detailskip for offerId={$itemId}");

                return [
                    'source'              => '1688',
                    'title'               => $title,
                    'title_cn'            => $titleCn,
                    'images'              => array_slice($mainImages, 0, 8),
                    'price_cny'           => $priceMin,
                    'price_cny_max'       => $priceMax ?: null,
                    'price_tiers'         => [],
                    'sku_matrix'          => $skuMatrix,
                    'variants'            => $variants,
                    'stock_status'        => 'in_stock',
                    'stock_quantity'      => null,
                    'moq'                 => (int)($offer['batchNumber'] ?? $offer['minOrderQuantity'] ?? 1) ?: 1,
                    'domestic_freight_cny'=> 0,
                    'seller_name'         => $offer['sellerInfo']['companyName'] ?? null,
                    'sold_count'          => (int)($offer['tradeInfo']['soldCount'] ?? 0),
                    'source_url'          => "https://detail.1688.com/offer/{$itemId}.html",
                    'description'         => null,
                    'has_battery'         => false,
                    'has_liquid'          => false,
                    'is_powder'           => false,
                    'is_food'             => false,
                    'is_fragile'          => false,
                ];
            } catch (\Exception $e) {
                Log::debug("1688 API endpoint failed [{$endpoint}]: " . $e->getMessage());
            }
        }

        return null;
    }

    /**
     * Build a comprehensive skuId→price AND attributeKey→price map from ALL
     * JSON blocks present in the page HTML.
     *
     * 1688 pages embed pricing in multiple different locations depending on
     * whether it's a mobile page, desktop page, single-sku or multi-sku product.
     * This method consolidates all of them so price lookups never fail.
     *
     * Returns: [
     *   'by_sku_id'  => ['skuId' => price],
     *   'by_attrs'   => ['Color:Black|Size:L' => price],   // sorted attr key
     *   'global_min' => float,
     *   'global_max' => float|null,
     * ]
     */
    private function buildSkuPriceMap(string $html): array
    {
        $bySkuId = [];
        $byAttrs = [];
        $allPrices = [];

        // ── Source 1: skuModel / skus array — most complete ───────────────
        foreach ([
            '/"skuModel"\s*:\s*(\[.{10,5000}?\])\s*[,}]/s',
            '/"skus"\s*:\s*(\[.{10,5000}?\])\s*[,}]/s',
            '/"skuInfos"\s*:\s*(\[.{10,5000}?\])\s*[,}]/s',
        ] as $pattern) {
            if (!preg_match($pattern, $html, $m)) continue;
            $arr = json_decode($m[1], true);
            if (!is_array($arr)) continue;

            foreach ($arr as $sku) {
                $price = (float)($sku['price'] ?? $sku['salePrice'] ?? $sku['skuPrice'] ?? 0);
                if ($price <= 0 || $price >= 99999) continue;

                $allPrices[] = $price;

                // Index by skuId
                $skuId = (string)($sku['skuId'] ?? $sku['id'] ?? $sku['specId'] ?? '');
                if ($skuId) $bySkuId[$skuId] = $price;

                // Index by normalised attribute string
                $attrs = [];
                foreach ($sku['specValues'] ?? $sku['propValues'] ?? $sku['properties'] ?? [] as $sv) {
                    if (is_array($sv)) {
                        $pName = $sv['propName'] ?? $sv['name'] ?? '';
                        $vName = $sv['valueName'] ?? $sv['displayName'] ?? $sv['value'] ?? '';
                    } else {
                        continue;
                    }
                    if ($pName && $vName) $attrs[$pName] = $vName;
                }
                if (!empty($attrs)) {
                    ksort($attrs);
                    $key = implode('|', array_map(fn($k, $v) => "$k:$v", array_keys($attrs), $attrs));
                    $byAttrs[$key] = $price;

                    // Also index each individual attr value for single-dim lookup
                    foreach ($attrs as $pName => $vName) {
                        $singleKey = "$pName:$vName";
                        if (!isset($byAttrs[$singleKey])) {
                            $byAttrs[$singleKey] = $price;
                        }
                    }
                }
            }
            if (!empty($bySkuId)) break; // found a good source
        }

        // ── Source 2: priceRangeList / skuPrices ──────────────────────────
        foreach ([
            '/"priceRangeList"\s*:\s*(\[.{10,5000}?\])/s',
            '/"skuPrices"\s*:\s*(\[.{10,5000}?\])/s',
            '/"skuRangePrices"\s*:\s*(\[.{10,5000}?\])/s',
        ] as $pattern) {
            if (!preg_match($pattern, $html, $m)) continue;
            $arr = json_decode($m[1], true);
            if (!is_array($arr)) continue;

            foreach ($arr as $entry) {
                $price = (float)($entry['price'] ?? $entry['salePrice'] ?? 0);
                if ($price <= 0 || $price >= 99999) continue;
                $allPrices[] = $price;
                $sid = (string)($entry['skuId'] ?? $entry['skuID'] ?? $entry['id'] ?? $entry['specId'] ?? '');
                if ($sid && !isset($bySkuId[$sid])) $bySkuId[$sid] = $price;
            }
        }

        // ── Source 3: skuImages inline prices ─────────────────────────────
        if (preg_match('/"skuImages"\s*:\s*(\[.{20,5000}?\])\s*[,}]/s', $html, $m)) {
            $arr = json_decode($m[1], true);
            if (is_array($arr)) {
                foreach ($arr as $sku) {
                    $price = (float)($sku['price'] ?? $sku['salePrice'] ?? 0);
                    if ($price <= 0 || $price >= 99999) continue;
                    $allPrices[] = $price;
                    $sid = (string)($sku['skuId'] ?? $sku['id'] ?? '');
                    if ($sid && !isset($bySkuId[$sid])) $bySkuId[$sid] = $price;
                    $name = $sku['name'] ?? '';
                    if ($name && !isset($byAttrs["name:{$name}"])) {
                        $byAttrs["name:{$name}"] = $price;
                    }
                }
            }
        }

        // ── Source 4: propsCombination (desktop forcePC) ──────────────────
        if (preg_match('/"propsCombination"\s*:\s*(\[.{10,5000}?\])\s*[,}]/s', $html, $m)) {
            $combos = json_decode($m[1], true);
            if (is_array($combos)) {
                foreach ($combos as $combo) {
                    $price = (float)($combo['price'] ?? 0);
                    if ($price <= 0 || $price >= 99999) continue;
                    $allPrices[] = $price;
                    $sid = (string)($combo['skuId'] ?? $combo['sku_id'] ?? '');
                    if ($sid && !isset($bySkuId[$sid])) $bySkuId[$sid] = $price;
                    $attrs = [];
                    foreach ($combo['propValues'] ?? [] as $pv) {
                        if ($pv['name'] ?? '' && $pv['value'] ?? '') {
                            $attrs[$pv['name']] = $pv['value'];
                        }
                    }
                    if (!empty($attrs)) {
                        ksort($attrs);
                        $key = implode('|', array_map(fn($k, $v) => "$k:$v", array_keys($attrs), $attrs));
                        if (!isset($byAttrs[$key])) $byAttrs[$key] = $price;
                    }
                }
            }
        }

        sort($allPrices);
        $allPrices = array_values(array_unique(array_filter($allPrices, fn($p) => $p > 0 && $p < 99999)));

        return [
            'by_sku_id'  => $bySkuId,
            'by_attrs'   => $byAttrs,
            'global_min' => !empty($allPrices) ? $allPrices[0] : 0,
            'global_max' => count($allPrices) > 1 ? end($allPrices) : null,
            'all_prices' => $allPrices,
        ];
    }

    /**
     * Given a variant option (with label_cn / skuId), look up its price
     * from the price map. Falls back to global_min if nothing found.
     */
    private function resolveOptionPrice(array $opt, string $propNameCn, array $priceMap): ?float
    {
        // 1. Inline price on the option itself (most reliable)
        if (!empty($opt['price_cny']) && $opt['price_cny'] > 0) {
            return (float)$opt['price_cny'];
        }

        // 2. By skuId
        $skuId = (string)($opt['sku_id'] ?? $opt['skuId'] ?? '');
        if ($skuId && isset($priceMap['by_sku_id'][$skuId])) {
            return $priceMap['by_sku_id'][$skuId];
        }

        // 3. By attribute key (propName:value)
        $nameCn = $opt['label_cn'] ?? '';
        if ($nameCn) {
            $attrKey = "{$propNameCn}:{$nameCn}";
            if (isset($priceMap['by_attrs'][$attrKey])) {
                return $priceMap['by_attrs'][$attrKey];
            }
            // Also try by display name directly
            $nameKey = "name:{$nameCn}";
            if (isset($priceMap['by_attrs'][$nameKey])) {
                return $priceMap['by_attrs'][$nameKey];
            }
        }

        return null; // will fall back to product base price in the view
    }

    /**
     * Build a flat SKU matrix from the 1688 HTML.
     * Handles both mobile and desktop (forcePC) page structures.
     * Returns array of: [attributes => [...], supplier_sku_id, price_cny, stock, image]
     */
    private function buildSkuMatrix(string $html): array
    {
        $matrix = [];

        // ── Strategy 1: skuProps block (mobile API format) ─────────────────
        if (preg_match('/"skuProps"\s*:\s*(\[.{10,5000}?\])\s*[,}]/s', $html, $m)) {
            $skuProps = json_decode($m[1], true);
            if (is_array($skuProps) && !empty($skuProps)) {
                return $this->buildMatrixFromSkuProps($skuProps, $html);
            }
        }

        // ── Strategy 1b: skuImages block (mobile — single-dimension variants) ──
        // Format: [{name:"8G+128G", imgUrl:"...", price:"45.00", skuId:"989A", quantity:0}]
        // This is the primary format for single-dimension products like this phone.
        if (preg_match('/"skuImages"\s*:\s*(\[.{20,5000}?\])\s*[,}]/s', $html, $m)) {
            $arr = json_decode($m[1], true);
            if (is_array($arr) && !empty($arr)) {
                $skuMatrix = [];
                foreach ($arr as $sku) {
                    $nameCn = $sku['name'] ?? '';
                    if (!$nameCn) continue;
                    $nameEn = $this->translateToEnglish($nameCn);
                    $img    = $sku['imgUrl'] ?? $sku['imageUrl'] ?? '';
                    $skuId  = (string)($sku['skuId'] ?? $sku['id'] ?? '');
                    $stock  = isset($sku['quantity']) ? (int)$sku['quantity'] : null;
                    // Price can be inline or need lookup
                    $price  = (float)($sku['price'] ?? $sku['salePrice'] ?? 0);

                    $skuMatrix[] = [
                        'attributes'      => ['Variant' => $nameEn, 'Variant_cn' => $nameCn],
                        'supplier_sku_id' => $skuId ?: null,
                        'price_cny'       => $price,
                        'stock'           => $stock,
                        'image'           => $this->isRealProductImage($img) ? $img : null,
                    ];
                }
                // Only return if at least some rows have prices
                $hasAnyPrice = collect($skuMatrix)->contains(fn($r) => $r['price_cny'] > 0);
                if (!empty($skuMatrix) && $hasAnyPrice) return $skuMatrix;
            }
        }

        // ── Strategy 2: propsCombination (desktop forcePC format) ──────────
        if (preg_match('/"propsCombination"\s*:\s*(\[.{10,5000}?\])\s*[,}]/s', $html, $m)) {
            $combinations = json_decode($m[1], true);
            if (is_array($combinations)) {
                foreach ($combinations as $combo) {
                    $skuId  = (string)($combo['skuId'] ?? $combo['sku_id'] ?? '');
                    $price  = (float)($combo['price'] ?? 0);
                    $stock  = $combo['quantity'] ?? $combo['amountOnSale'] ?? null;
                    $img    = $combo['imgUrl'] ?? $combo['imageUrl'] ?? '';
                    $attrs  = [];

                    // propValues contains [{name, value}] pairs
                    if (!empty($combo['propValues'])) {
                        foreach ($combo['propValues'] as $pv) {
                            $name    = $this->translateToEnglish($pv['name'] ?? '');
                            $valueCn = $pv['value'] ?? '';
                            $valueEn = $this->translateToEnglish($valueCn);
                            if ($name && $valueEn) {
                                $attrs[$name] = $valueEn;
                                if ($valueCn !== $valueEn) $attrs[$name . '_cn'] = $valueCn;
                            }
                        }
                    }

                    if (empty($attrs) || $price <= 0) continue;

                    $matrix[] = [
                        'attributes'      => $attrs,
                        'supplier_sku_id' => $skuId ?: null,
                        'price_cny'       => $price,
                        'stock'           => is_numeric($stock) ? (int)$stock : null,
                        'image'           => $this->isRealProductImage($img) ? $img : null,
                    ];
                }
                if (!empty($matrix)) return $matrix;
            }
        }

        // ── Strategy 3: skuInfo / skuArray (another desktop variant) ───────
        if (preg_match('/"skuInfo"\s*:\s*(\{.{10,5000}?\})\s*[,}]/s', $html, $m)) {
            $skuInfo = json_decode($m[1], true);
            if (!empty($skuInfo['skuProps'])) {
                return $this->buildMatrixFromSkuProps($skuInfo['skuProps'], $html);
            }
        }

        // ── Strategy 4: Extract from page's props array (any format) ────────
        // Look for pattern like: "color":["Black","White"] or "size":["S","M","L"]
        foreach (['color','Color','颜色','size','Size','尺寸','款式','style','Style'] as $attr) {
            if (preg_match('/"' . $attr . '"\s*:\s*\[([^\]]{2,500})\]/u', $html, $m)) {
                preg_match_all('/"([^"]{1,50})"/u', $m[1], $vals);
                if (!empty($vals[1])) {
                    $nameEn = $this->translateToEnglish($attr);
                    foreach ($vals[1] as $val) {
                        $matrix[] = [
                            'attributes'      => [$nameEn => $this->translateToEnglish($val)],
                            'supplier_sku_id' => null,
                            'price_cny'       => 0,
                            'stock'           => null,
                            'image'           => null,
                        ];
                    }
                }
            }
        }

        return $matrix;
    }

    /**
     * Build matrix from skuProps array format:
     * [{name: "Color", values: [{name: "Black", imgUrl: "...", skuId: "...", price: "45.00"}]}]
     *
     * Also handles mobile format where price lives directly on the value object
     * or is keyed by skuId in priceRangeList / skuRangePrices.
     */
    private function buildMatrixFromSkuProps(array $skuProps, string $html): array
    {
        $matrix = [];

        // ── Build a price map by skuId (desktop format) ────────────────────
        $skuPriceMap  = [];
        $skuStockMap  = [];
        foreach ([
            '/"priceRangeList"\s*:\s*(\[.{10,5000}?\])/s',
            '/"skuPrices"\s*:\s*(\[.{10,5000}?\])/s',
        ] as $pattern) {
            if (preg_match($pattern, $html, $pm)) {
                $priceList = json_decode($pm[1], true);
                if (is_array($priceList)) {
                    foreach ($priceList as $entry) {
                        $sid = (string)($entry['skuId'] ?? $entry['skuID'] ?? $entry['id'] ?? '');
                        $p   = (float)($entry['price'] ?? $entry['salePrice'] ?? 0);
                        $s   = $entry['quantity'] ?? $entry['stock'] ?? null;
                        if ($sid && $p > 0) $skuPriceMap[$sid] = $p;
                        if ($sid && is_numeric($s)) $skuStockMap[$sid] = (int)$s;
                    }
                }
            }
        }

        // ── Also build a price map by value name (mobile skuRangePrices) ───
        // Mobile format: skuRangePrices is an array of {specId/valueId, price}
        // OR prices live inline on the value object itself
        $valueNamePriceMap = [];
        if (preg_match('/"skuRangePrices"\s*:\s*(\[.{10,5000}?\])/s', $html, $pm)) {
            $arr = json_decode($pm[1], true);
            if (is_array($arr)) {
                foreach ($arr as $entry) {
                    // Some mobile pages key by specId or valueId
                    $vid = (string)($entry['specId'] ?? $entry['valueId'] ?? $entry['skuId'] ?? '');
                    $p   = (float)($entry['price'] ?? 0);
                    if ($vid && $p > 0) $skuPriceMap[$vid] = $p;
                }
            }
        }

        foreach ($skuProps as $prop) {
            $propNameCn = $prop['name'] ?? '';
            $propNameEn = $this->translateToEnglish($propNameCn);
            $values     = $prop['values'] ?? [];

            foreach ($values as $val) {
                $skuId  = (string)($val['skuId'] ?? $val['id'] ?? $val['specId'] ?? $val['valueId'] ?? '');
                $nameCn = $val['name'] ?? '';
                $nameEn = $this->translateToEnglish($nameCn);
                $img    = $val['imageUrl'] ?? $val['imgUrl'] ?? '';
                $stock  = isset($val['quantity']) ? (int)$val['quantity'] : null;

                // Stock from map if not on value directly
                if ($stock === null && $skuId && isset($skuStockMap[$skuId])) {
                    $stock = $skuStockMap[$skuId];
                }

                // Price resolution — try in order:
                // 1. Inline price on the value object (most reliable for mobile)
                // 2. skuId → priceRangeList/skuPrices map
                // 3. Value name → valueNamePriceMap
                $price = (float)($val['price'] ?? $val['salePrice'] ?? $val['skuPrice'] ?? 0);
                if ($price <= 0 && $skuId && isset($skuPriceMap[$skuId])) {
                    $price = $skuPriceMap[$skuId];
                }
                if ($price <= 0 && isset($valueNamePriceMap[$nameCn])) {
                    $price = $valueNamePriceMap[$nameCn];
                }

                if (!$nameCn) continue;

                $attrs = [$propNameEn => $nameEn];
                if ($nameCn !== $nameEn) $attrs[$propNameEn . '_cn'] = $nameCn;

                $matrix[] = [
                    'attributes'      => $attrs,
                    'supplier_sku_id' => $skuId ?: null,
                    'price_cny'       => $price,
                    'stock'           => $stock,
                    'image'           => $this->isRealProductImage($img) ? $img : null,
                ];
            }
        }

        return $matrix;
    }

    // ══════════════════════════════════════════════════════════════════════
    // TASK 1 — Auto category detection from title + 1688 category path
    // TASK 5 — Attribute auto-detect: battery/liquid/powder/food flags
    // ══════════════════════════════════════════════════════════════════════

    /**
     * Public wrapper for category detection (used by controller for preview).
     */
    public function detectCategoryPublic(string $titleEn, string $titleCn = ''): ?int
    {
        return $this->detectCategory($titleEn, $titleCn);
    }

    /**
     * Returns category ID or null if no confident match.
     */
    private function detectCategory(string $titleEn, string $titleCn = '', string $categoryPath = ''): ?int
    {
        $text = strtolower($titleEn . ' ' . $titleCn . ' ' . $categoryPath);

        // Keyword map — ordered from most specific to most general
        // Each entry: [category_slug, keywords[]]
        $rules = [
            // ── Phones & Tablets (ID 12) ─────────────────────────────────
            'phones'       => ['iphone','samsung galaxy','xiaomi','oppo','vivo','realme','tecno','infinix',
                               'smartphone','mobile phone','android phone','tablet','ipad','phone case',
                               'screen protector','tempered glass','手机','平板','phone holder','earphone',
                               'tws','bluetooth headset','airpods','earbuds'],
            // ── Electronics (ID 1) ───────────────────────────────────────
            'electronics'  => ['laptop','computer','monitor','keyboard','mouse','printer','scanner',
                               'hard drive','ssd','usb hub','router','switch','hdmi','cable','adapter',
                               'power bank','charger','led strip','cctv','camera','projector','tv',
                               'television','smart tv','speaker','amplifier','microphone','gaming',
                               'drone','raspberry','arduino','solar panel','inverter','battery',
                               '电脑','充电器','电视','摄像头'],
            // ── Fashion & Clothing (ID 2) ────────────────────────────────
            'fashion'      => ['dress','shirt','blouse','skirt','trouser','jean','hoodie','jacket',
                               'coat','suit','tie','sock','underwear','bra','lingerie','swimwear',
                               'sportswear','ankara','fabric','lace','wig','hair extension','sneaker',
                               'shoe','sandal','heel','boot','handbag','wallet','purse','watch',
                               'jewelry','necklace','bracelet','earring','ring','sunglasses','cap',
                               'hat','scarf','glove','belt','clothing','fashion','apparel','wear',
                               '服装','裙子','鞋','包','手表','珠宝'],
            // ── Home & Garden (ID 3) ─────────────────────────────────────
            'home-garden'  => ['sofa','chair','table','desk','shelf','cabinet','wardrobe','bed frame',
                               'mattress','pillow','blanket','curtain','rug','carpet','lamp','light',
                               'fan','air conditioner','refrigerator','fridge','washing machine',
                               'blender','mixer','pot','pan','kettle','knife set','cutlery','plate',
                               'bowl','mug','glass','storage','organizer','hanger','laundry','broom',
                               'mop','garden','plant pot','flower','seed','fertilizer','hose',
                               '家具','冰箱','洗衣机','厨具'],
            // ── Health & Beauty (ID 4) ────────────────────────────────────
            'health-beauty'=> ['serum','cream','lotion','moisturizer','sunscreen','foundation',
                               'lipstick','mascara','eyeshadow','makeup','cosmetic','skincare',
                               'hair oil','shampoo','conditioner','perfume','deodorant','soap',
                               'body wash','nail polish','nail art','lash','whitening','vitamin',
                               'supplement','face mask','pimple','acne','hair growth','slimming',
                               'massage','electric razor','trimmer','epilator','toothbrush',
                               '护肤','化妆品','美容','洗发'],
            // ── Sports & Outdoors (ID 5) ─────────────────────────────────
            'sports'       => ['gym','fitness','dumbbell','barbell','treadmill','bicycle','bike',
                               'cycling','football','basketball','tennis','badminton','yoga mat',
                               'resistance band','jump rope','protein','sports bottle','camping',
                               'hiking','fishing','tent','backpack','outdoor','running shoe',
                               '健身','运动','瑜伽','自行车'],
            // ── Toys & Games (ID 6) ──────────────────────────────────────
            'toys'         => ['toy','lego','doll','action figure','board game','puzzle','rc car',
                               'remote control','fidget','slime','playset','building block',
                               'stuffed animal','plush','kite','bubble','magic','card game',
                               '玩具','积木','遥控'],
            // ── Auto Parts (ID 7) ─────────────────────────────────────────
            'auto-parts'   => ['car','vehicle','auto','motorcycle','tyre','tire','rim','brake',
                               'engine oil','car charger','car seat','dashboard','steering wheel',
                               'car cover','windshield','exhaust','shock absorber','car mat',
                               '汽车','摩托车','轮胎'],
            // ── Tools & Hardware (ID 8) ───────────────────────────────────
            'tools'        => ['drill','screwdriver','wrench','hammer','saw','grinder','welder',
                               'tape measure','level','pliers','nail','screw','bolt','nut',
                               'power tool','hand tool','toolbox','ladder','sandpaper','paint brush',
                               'caulk','sealant','lock','hinge','pipe','valve','pump','wire',
                               '工具','电钻','螺丝'],
            // ── Baby & Kids (ID 9) ────────────────────────────────────────
            'baby-kids'    => ['baby','infant','toddler','newborn','diaper','stroller','crib',
                               'feeding bottle','pacifier','baby food','baby monitor','teething',
                               'school bag','children','kids clothing','kids shoe',
                               '婴儿','宝宝','儿童','奶瓶'],
            // ── Food & Grocery (ID 10) ────────────────────────────────────
            'food'         => ['food','snack','candy','chocolate','coffee','tea','spice','seasoning',
                               'rice','noodle','biscuit','cookie','juice','sauce','honey','nut',
                               'protein powder','meal replacement','herbal','dried fruit',
                               '食品','零食','茶叶','咖啡'],
            // ── Office & School (ID 11) ───────────────────────────────────
            'office'       => ['notebook','pen','pencil','marker','stapler','file','folder','binder',
                               'printer paper','whiteboard','calculator','office chair','desk lamp',
                               'calendar','planner','sticky note','stamp','tape dispenser','scissors',
                               '文具','办公','笔记本'],
        ];

        // Score each category by keyword hits
        $scores   = [];
        $catBySlug = Category::where('is_active', true)->pluck('id', 'slug');

        foreach ($rules as $slug => $keywords) {
            $score = 0;
            foreach ($keywords as $kw) {
                if (str_contains($text, strtolower($kw))) {
                    // Longer keyword = more specific = higher score
                    $score += strlen($kw) >= 8 ? 3 : (strlen($kw) >= 5 ? 2 : 1);
                }
            }
            if ($score > 0) $scores[$slug] = $score;
        }

        if (empty($scores)) return null;

        // Pick highest scoring category
        arsort($scores);
        $bestSlug = array_key_first($scores);
        return $catBySlug[$bestSlug] ?? null;
    }

    /**
     * Auto-detect product attributes (battery, liquid, powder, food, fragile)
     * from title, description and any extracted flags in HTML.
     */
    private function detectAttributes(string $text): array
    {
        $t = strtolower($text);
        return [
            'has_battery' => (bool) preg_match('/battery|lithium|li-?ion|lipo|batterie|充电|锂电池|电池|power bank/u', $t),
            'has_liquid'  => (bool) preg_match('/liquid|fluid|serum|lotion|cream|perfume|cologne|spray|oil|sauce|juice|shampoo|conditioner|液体|香水|乳液|精华|洗发/u', $t),
            'is_powder'   => (bool) preg_match('/powder|dust|flour|粉末|粉剂|奶粉|蛋白粉/u', $t),
            'is_food'     => (bool) preg_match('/food|snack|candy|chocolate|coffee|tea|rice|noodle|biscuit|cookie|juice|sauce|honey|nut|dried|herbal|spice|seasoning|食品|零食|茶叶|咖啡|食物/u', $t),
            'is_fragile'  => (bool) preg_match('/glass|ceramic|porcelain|crystal|mirror|vase|fragile|玻璃|陶瓷|瓷器|镜子/u', $t),
        ];
    }

    private function buildDescription(string $html, string $titleCn, string $titleEn, array $tiers, array $variants, ?string $seller): string
    {
        $lines = [];
        $lines[] = "📦 Product: $titleEn";
        if ($titleCn !== $titleEn) $lines[] = "   (原名: $titleCn)";

        if ($seller) $lines[] = "\n🏭 Supplier: $seller";

        if (!empty($tiers)) {
            $lines[] = "\n💰 Quantity Pricing:";
            foreach ($tiers as $t) $lines[] = "   • {$t['min_qty']}+ pcs → ¥{$t['price']}";
        }

        if (!empty($variants)) {
            foreach ($variants as $v) {
                $lines[] = "\n🎨 ".$v['name'].":";
                foreach ($v['options'] as $o) {
                    $line = "   • ".$o['label'];
                    if (!empty($o['label_cn']) && $o['label_cn'] !== $o['label']) $line .= " ({$o['label_cn']})";
                    $lines[] = $line;
                }
            }
        }

        if (preg_match('/"saledCount"\s*:\s*(\d+)/', $html, $m) && (int)$m[1]>0)
            $lines[] = "\n✅ Orders placed: ".$m[1];
        if (preg_match('/"beginNum"\s*:\s*(\d+)/', $html, $m) && (int)$m[1]>1)
            $lines[] = "📋 Minimum order: ".$m[1]." pieces";
        if (str_contains($html, '7天无理由'))
            $lines[] = "🔄 7-day return policy supported";
        if (preg_match('/"limitTimeDesc"\s*:\s*"([^"]+)"/', $html, $m))
            $lines[] = "🚚 Shipping: ".$m[1];

        return implode("\n", $lines);
    }

    private function scrapeTaobao(string $url): ?array
    {
        // ── Try Zenrows first (residential proxy + JS render beats Taobao login wall) ──
        $html = $this->fetchViaZenrows($url);
        if ($html && $html !== '__BOT_CHALLENGE__' && strlen($html) > 3000
            && !str_contains($html, 'login.taobao.com')
            && !str_contains($html, 'login.m.taobao.com')) {
            // Zenrows gave us a clean page — skip the direct-fetch loop below
        } else {
            $html = null;
        }

        // ── Fall back to direct mobile endpoints ──────────────────────────
        if (!$html) {
            $endpoints = [
                $url,
                str_replace('item.taobao.com', 'h5.m.taobao.com/awp/core/detail.htm', $url),
            ];

            foreach ($endpoints as $endpoint) {
                $result = $this->fetch($endpoint, [
                    'Referer'         => 'https://www.taobao.com/',
                    'Accept-Language' => 'zh-CN,zh;q=0.9',
                ]);
                if ($result && $result !== '__BOT_CHALLENGE__'
                    && strlen($result) > 3000
                    && !str_contains($result, 'login.taobao.com')
                    && !str_contains($result, 'login.m.taobao.com')
                    && !str_contains($result, 'J_LoginPrompt')
                ) {
                    $html = $result;
                    break;
                }
            }
        }

        if (!$html) {
            return null; // triggers can_manual fallback in scrape()
        }

        $titleCn = null;
        foreach (['"title"', '"defaultTitle"', '"itemTitle"'] as $k) {
            if (preg_match('/' . $k . '\s*:\s*"([^"]{8,})"/', $html, $m)) {
                $titleCn = $m[1];
                break;
            }
        }
        if (!$titleCn && preg_match('/<title>([^<]+)<\/title>/i', $html, $m)) {
            $titleCn = trim(preg_replace('/[-_|].*(淘宝|Taobao).*$/i', '', html_entity_decode($m[1])));
        }
        if (!$titleCn || strlen($titleCn) < 5) return null;

        $price = 0;
        if (preg_match('/"defaultItemPrice"\s*:\s*"([\d.]+)"/', $html, $m))      $price = (float) $m[1];
        elseif (preg_match('/"price"\s*:\s*"([\d.]+)"/', $html, $m))             $price = (float) $m[1];
        elseif (preg_match('/"discountPrice"\s*:\s*"([\d.]+)"/', $html, $m))     $price = (float) $m[1];

        $images = [];
        preg_match_all('/(https?:\/\/[a-z0-9]+\.alicdn\.com\/[^"\'>\s]+\.jpg)/', $html, $im);
        $images = array_slice(array_values(array_unique($im[1])), 0, 8);

        return [
            'title'          => $this->translateToEnglish($titleCn),
            'title_cn'       => $titleCn,
            'images'         => $images,
            'price_cny'      => $price,
            'price_cny_max'  => null,
            'price_tiers'    => [],
            'stock_status'   => 'in_stock',
            'stock_quantity' => null,
            'seller_name'    => null,
            'description'    => $this->translateToEnglish($titleCn),
            'variants'       => [],
            'moq'            => 1,
            'sold_count'     => 0,
            'auto_category_id' => null,
        ];
    }

    private function scrapeAlibaba(string $url): ?array
    {
        // Alibaba uses bot detection (Cloudflare/Akamai JS challenge).
        // Server-side fetching returns a captcha page, not product data.
        // We extract what we can from the URL and return null to trigger
        // manual entry with the URL pre-filled.
        return null;
    }

    /**
     * Scrape AliExpress product page.
     *
     * What's available without JS rendering (no Zenrows):
     * - Title: from og:title meta
     * - Images: from window._d_c_.DCData.imagePathList (6-8 full-res images)
     * - Price: NOT available — AliExpress loads price via CSR JavaScript
     * - Variants: NOT available — also CSR
     *
     * With Zenrows configured, we get the full page including price + variants.
     */
    private function scrapeAliExpress(string $url): ?array
    {
        $itemId = $this->extractItemId($url, 'aliexpress');
        if (!$itemId) return null;

        // ── Strategy A: Zenrows (if configured) — full page with JS render ──────
        $zenHtml = $this->fetchViaZenrows($url);
        if ($zenHtml && $zenHtml !== '__BOT_CHALLENGE__' && strlen($zenHtml) > 5000) {
            $parsed = $this->parseAliExpressHtml($zenHtml, $url, true);
            if ($parsed) return $parsed;
        }

        // ── Strategy B: Direct fetch — can get title + images, but NOT price ────
        // AliExpress is CSR: window.runParams = {} on the HTML. Price and variants
        // are loaded by JavaScript after page render. We extract what's available.
        $html = $this->fetchAliExpressDirect($url);
        if ($html && strlen($html) > 5000 && !$this->isBotChallengePage($html)) {
            $partial = $this->parseAliExpressHtml($html, $url, false);
            if ($partial) return $partial;
        }

        // ── Strategy C: Mobile endpoint ──────────────────────────────────────────
        $mHtml = $this->fetch("https://m.aliexpress.com/item/{$itemId}.html", [
            'User-Agent'      => 'Mozilla/5.0 (iPhone; CPU iPhone OS 17_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.0 Mobile/15E148 Safari/604.1',
            'Accept-Language' => 'en-US,en;q=0.9',
            'Referer'         => 'https://m.aliexpress.com/',
        ]);
        if ($mHtml && strlen($mHtml) > 3000 && !$this->isBotChallengePage($mHtml)) {
            $partial = $this->parseAliExpressHtml($mHtml, $url, false);
            if ($partial) return $partial;
        }

        return null;
    }

    /**
     * Direct fetch of AliExpress page with proper headers + cookie warmup.
     */
    private function fetchAliExpressDirect(string $url): ?string
    {
        try {
            $jar = new \GuzzleHttp\Cookie\CookieJar();
            $client = new \GuzzleHttp\Client([
                'timeout'         => 25,
                'connect_timeout' => 12,
                'verify'          => false,
                'allow_redirects' => ['max' => 5],
                'cookies'         => $jar,
            ]);

            $headers = [
                'User-Agent'                => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/125.0.0.0 Safari/537.36',
                'Accept'                    => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,*/*;q=0.8',
                'Accept-Language'           => 'en-US,en;q=0.9',
                'Accept-Encoding'           => 'gzip, deflate, br',
                'Upgrade-Insecure-Requests' => '1',
                'Sec-Fetch-Dest'            => 'document',
                'Sec-Fetch-Mode'            => 'navigate',
                'Sec-Fetch-Site'            => 'none',
                'Cache-Control'             => 'max-age=0',
            ];

            // Warm up: visit homepage first to get session cookies
            try {
                $client->get('https://www.aliexpress.com/', ['headers' => $headers, 'timeout' => 10]);
            } catch (\Exception $e) { /* non-critical */ }

            $resp = $client->get($url, ['headers' => array_merge($headers, ['Referer' => 'https://www.aliexpress.com/'])]);
            $body = (string) $resp->getBody();

            if (strlen($body) < 3000) return null;
            if ($this->isBotChallengePage($body)) return null;

            return $body;
        } catch (\Exception $e) {
            Log::debug("AliExpress direct fetch failed: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Parse AliExpress HTML using all available extraction strategies.
     *
     * $fullJs = true  when fetched via Zenrows (has real JS-rendered content)
     * $fullJs = false when fetched directly (only static HTML available)
     */
    private function parseAliExpressHtml(string $html, string $url, bool $fullJs = false): ?array
    {
        $title    = null;
        $images   = [];
        $price    = null;
        $variants = [];
        $desc     = '';
        $seller   = null;

        // ── Extract from window._d_c_.DCData (always present in static HTML) ────
        if (preg_match('/window\._d_c_\.DCData\s*=\s*(\{.+?\});\s*\n/s', $html, $m)) {
            $dc = json_decode($m[1], true);
            if ($dc) {
                // Images: full resolution list
                foreach ((array)($dc['imagePathList'] ?? []) as $img) {
                    if ($img && str_contains($img, 'aliexpress-media.com')) {
                        // Upgrade to large size if it has a size suffix
                        $img = preg_replace('/_\d+x\d+\.jpg$/', '.jpg', $img);
                        $images[] = $img;
                    }
                }
            }
        }

        // ── Extract from window.runParams (old pages / Zenrows) ─────────────────
        if ($fullJs || empty($images)) {
            foreach ([
                '/window\.runParams\s*=\s*(\{.+?\});\s*(?:window|var|\()/s',
                '/window\.runParams\s*=\s*\{data:\s*(\{.+?\})\}/s',
            ] as $pat) {
                if (preg_match($pat, $html, $m)) {
                    $rp = json_decode($m[1], true);
                    if (!$rp) continue;
                    $rpData = $rp['data'] ?? $rp;

                    // Title
                    if (!$title) $title = $rpData['titleModule']['subject'] ?? $rpData['subject'] ?? null;

                    // Price
                    if (!$price && isset($rpData['priceModule'])) {
                        $pm = $rpData['priceModule'];
                        $price = (float)($pm['minActivityAmount']['value'] ?? $pm['minAmount']['value'] ?? 0);
                        if (!$price && isset($pm['formatedPrice'])) {
                            $price = (float) preg_replace('/[^\d.]/', '', $pm['formatedPrice']);
                        }
                    }

                    // Images
                    if (empty($images) && isset($rpData['imageModule']['imagePathList'])) {
                        foreach ((array)$rpData['imageModule']['imagePathList'] as $img) {
                            if ($img) $images[] = preg_replace('/_\d+x\d+\.jpg$/', '.jpg', $img);
                        }
                    }

                    // Variants / SKU
                    if (empty($variants) && isset($rpData['skuModule']['productSKUPropertyList'])) {
                        foreach ($rpData['skuModule']['productSKUPropertyList'] as $prop) {
                            $pName = $prop['skuPropertyName'] ?? '';
                            if (!$pName) continue;
                            $opts = [];
                            foreach ($prop['skuPropertyValues'] ?? [] as $v) {
                                $label = $v['propertyValueDisplayName'] ?? $v['skuPropertyValueName'] ?? '';
                                if (!$label) continue;
                                $img = $v['skuPropertyImagePath'] ?? null;
                                if ($img) $img = preg_replace('/_\d+x\d+\.jpg$/', '_960x960.jpg', $img);
                                $opts[] = ['label' => $label, 'image' => $img, 'price_cny' => null];
                            }
                            if (count($opts) > 1) $variants[] = ['name' => $pName, 'options' => $opts];
                        }
                    }

                    // Seller
                    if (!$seller) $seller = $rpData['sellerComponent']['sellerShopName'] ?? null;
                    if (!$seller) $seller = $rpData['storeModule']['storeName'] ?? null;

                    break;
                }
            }
        }

        // ── __NEXT_DATA__ (if Zenrows gave us a Next.js page) ───────────────────
        if ($fullJs && !$title) {
            if (preg_match('/<script id="__NEXT_DATA__" type="application\/json">\s*(\{.+?\})\s*<\/script>/s', $html, $m)) {
                $nxt = json_decode($m[1], true);
                $props = $nxt['props']['pageProps']['ssrProps']['data']
                    ?? $nxt['props']['pageProps']['data']
                    ?? null;
                if ($props) {
                    if (!$title) $title = $props['productTitle'] ?? $props['title'] ?? null;
                    if (!$price) $price = (float)($props['priceMin'] ?? $props['price'] ?? 0);
                    if (empty($images) && !empty($props['images'])) {
                        foreach ((array)$props['images'] as $img) {
                            $imgUrl = is_string($img) ? $img : ($img['url'] ?? '');
                            if ($imgUrl) $images[] = 'https:' . ltrim($imgUrl, ':');
                        }
                    }
                }
            }
        }

        // ── Fallback: OG / meta tags ─────────────────────────────────────────────
        if (!$title && preg_match('/<meta\s+property="og:title"\s+content="([^"]{5,})"/i', $html, $m)) {
            $t = html_entity_decode($m[1]);
            // Strip AliExpress boilerplate suffix like " - AliExpress XXXXXX"
            $t = preg_replace('/\s*[-–]\s*AliExpress\s*\d*\s*$/i', '', $t);
            $title = trim($t);
        }
        if (empty($images) && preg_match('/<meta\s+property="og:image"\s+content="([^"]+)"/i', $html, $m)) {
            $images[] = $m[1];
        }
        if (!$price && preg_match('/<meta\s+property="product:price:amount"\s+content="([^"]+)"/i', $html, $m)) {
            $price = (float)$m[1];
        }
        if (!$desc && preg_match('/<meta\s+property="og:description"\s+content="([^"]+)"/i', $html, $m)) {
            $descRaw = html_entity_decode($m[1]);
            // Skip the generic AliExpress tagline
            if (!str_contains($descRaw, 'Smarter Shopping')) $desc = $descRaw;
        }

        // Need at least a title to return something useful
        if (!$title) return null;

        // Deduplicate images, upgrade thumbnails to full size
        $images = array_values(array_unique(array_filter(
            array_map(function ($img) {
                if (!$img) return null;
                if (!str_starts_with($img, 'http')) $img = 'https:' . $img;
                // Remove _80x80 thumbnail suffixes
                $img = preg_replace('/_\d+x\d+\.(jpg|jpeg|png|webp)$/i', '.$1', $img);
                return $img;
            }, $images)
        )));

        return [
            'title'       => $title,
            'images'      => array_slice($images, 0, 12),
            'price_min'   => $price ?: 0.01,
            'price_max'   => $price ?: 0.01,
            'price_cny'   => $price ?: 0.01,
            'price_tiers' => [],
            'variants'    => $variants,
            'stock_status'=> 'in_stock',
            'seller_name' => $seller,
            'source'      => 'aliexpress',
            'source_url'  => $url,
            'description' => mb_substr($desc, 0, 3000),
            'moq'         => 1,
            'weight_kg'   => null,
            'price_note'  => $price ? null : 'Price not available without Zenrows — please enter manually.',
        ];
    }

    /** Parse AliExpress __NEXT_DATA__ props */
    private function parseAliExpressNextData(array $props, string $url): ?array
    {
        $title     = $props['productTitle'] ?? $props['title'] ?? null;
        $priceMin  = (float) ($props['priceMin'] ?? $props['price'] ?? 0);
        $priceMax  = (float) ($props['priceMax'] ?? $priceMin);
        $images    = [];

        if (!empty($props['images'])) {
            foreach ((array)$props['images'] as $img) {
                $imgUrl = is_string($img) ? $img : ($img['url'] ?? '');
                if ($imgUrl && !str_contains($imgUrl, '_50x50') && !str_contains($imgUrl, '_100x100')) {
                    $images[] = 'https:' . ltrim($imgUrl, ':');
                }
            }
        }

        if (!$title || !$priceMin) return null;

        $titleEn = $this->translateToEnglish($title);

        return [
            'title'          => $titleEn ?: $title,
            'title_cn'       => $title,
            'images'         => array_values(array_unique(array_slice($images, 0, 12))),
            'price_cny'      => $priceMin,
            'price_cny_max'  => $priceMax > $priceMin ? $priceMax : $priceMin,
            'price_tiers'    => [],
            'variants'       => [],
            'stock_status'   => 'in_stock',
            'seller_name'    => $props['storeName'] ?? $props['sellerName'] ?? null,
            'source'         => 'aliexpress',
            'source_url'     => $url,
            'description'    => strip_tags($props['description'] ?? ''),
            'moq'            => 1,
        ];
    }

    /** Parse AliExpress window.runParams */
    private function parseAliExpressRunParams(array $params, string $url): ?array
    {
        $detail = $params['data']['skuModule'] ?? $params['skuModule'] ?? [];
        $info   = $params['data']['productInfoComponent'] ?? $params['productInfoComponent'] ?? [];
        $media  = $params['data']['imageModule'] ?? $params['imageModule'] ?? [];

        $title   = $info['subject'] ?? $params['data']['pageModule']['title'] ?? null;
        $images  = [];

        if (!empty($media['imagePathList'])) {
            foreach ((array)$media['imagePathList'] as $img) {
                $imgUrl = is_string($img) ? $img : ($img['src'] ?? '');
                if ($imgUrl) {
                    $images[] = strpos($imgUrl, 'http') === 0 ? $imgUrl : 'https:' . $imgUrl;
                }
            }
        }

        $priceMin = 0;
        $priceMax = 0;
        if (!empty($detail['skuPriceList'][0]['skuVal']['skuAmount']['value'])) {
            $priceMin = (float) $detail['skuPriceList'][0]['skuVal']['skuAmount']['value'];
        } elseif (!empty($info['tradeComponent']['formatAmount']['value'])) {
            $priceMin = (float) $info['tradeComponent']['formatAmount']['value'];
        }

        if (!$title || !$priceMin) return null;

        $titleEn = $this->translateToEnglish($title);

        return [
            'title'          => $titleEn ?: $title,
            'title_cn'       => $title,
            'images'         => array_values(array_unique(array_slice($images, 0, 12))),
            'price_cny'      => $priceMin,
            'price_cny_max'  => $priceMax > $priceMin ? $priceMax : $priceMin,
            'price_tiers'    => [],
            'variants'       => [],
            'stock_status'   => 'in_stock',
            'seller_name'    => $params['data']['sellerComponent']['sellerShopName'] ?? null,
            'source'         => 'aliexpress',
            'source_url'     => $url,
            'description'    => '',
            'moq'            => 1,
        ];
    }

    /** Parse AliExpress JSON-LD Product schema */
    private function parseAliExpressLdJson(array $ld, string $url): ?array
    {
        $title    = $ld['name'] ?? null;
        $priceMin = (float) ($ld['offers']['price'] ?? $ld['offers']['lowPrice'] ?? 0);
        $priceMax = (float) ($ld['offers']['highPrice'] ?? $priceMin);
        $images   = [];

        if (!empty($ld['image'])) {
            foreach ((array)$ld['image'] as $img) {
                $imgUrl = is_string($img) ? $img : ($img['url'] ?? '');
                if ($imgUrl) $images[] = $imgUrl;
            }
        }

        if (!$title || !$priceMin) return null;

        return [
            'title'          => $title,
            'title_cn'       => $title,
            'images'         => array_values(array_unique(array_slice($images, 0, 12))),
            'price_cny'      => $priceMin,
            'price_cny_max'  => $priceMax > $priceMin ? $priceMax : $priceMin,
            'price_tiers'    => [],
            'variants'       => [],
            'stock_status'   => ($ld['offers']['availability'] ?? '') === 'https://schema.org/InStock' ? 'in_stock' : 'unknown',
            'seller_name'    => $ld['brand']['name'] ?? null,
            'source'         => 'aliexpress',
            'source_url'     => $url,
            'description'    => strip_tags($ld['description'] ?? ''),
            'moq'            => 1,
        ];
    }

    /**
     * Extract any useful metadata from an Alibaba URL without fetching.
     * Used to pre-populate the manual entry form.
     */
    public function extractAlibabaMetaFromUrl(string $url): array
    {
        $productId = $this->extractItemId($url, 'alibaba');

        // Extract product slug from URL path
        // e.g. /product-detail/I3-I-5-I7-Personal-Home_1600725468384.html
        $slug = '';
        if (preg_match('/product-detail\/([^_]+)_\d+\.html/i', $url, $m)) {
            $slug = str_replace('-', ' ', $m[1]);
            $slug = ucwords(strtolower($slug));
        }

        return [
            'product_id' => $productId,
            'title_hint' => $slug,
            'source_url' => $url,
        ];
    }

    /**
     * Alibaba-specific image validation (different CDN from 1688).
     */
    private function isAlibabbaRealImage(string $url): bool
    {
        if (empty($url)) return false;
        // Must be from Alibaba CDN
        if (!str_contains($url, 'alicdn.com') && !str_contains($url, 'alibaba.com')) return false;
        // Reject tiny thumbnails
        foreach (['_50x50.', '_60x60.', '_100x100.', '_80x80.', '50x50', '100x100'] as $tiny) {
            if (str_contains($url, $tiny)) return false;
        }
        // Reject known non-product images
        $path = strtolower(parse_url($url, PHP_URL_PATH) ?? '');
        foreach (['logo', 'icon', 'badge', 'banner', 'default', 'placeholder', 'noimg'] as $bad) {
            if (str_contains($path, $bad)) return false;
        }
        if (!preg_match('/\.(jpg|jpeg|png|webp)(\?|$)/i', $url)) return false;
        return true;
    }

    public function translateToEnglish(string $text): string
    {
        if (empty($text)) return $text;

        // No Chinese characters — return as-is
        if (!preg_match('/[\x{4e00}-\x{9fff}\x{3400}-\x{4dbf}]/u', $text)) return $text;

        $key    = 'trans_v2_' . md5($text);
        $cached = Cache::get($key);
        if ($cached !== null) return $cached;

        // Try Google Translate unofficial endpoint (free, no key needed)
        $translated = $this->callGoogleTranslate($text);

        if ($translated && $translated !== $text) {
            // Clean up common translation artifacts
            $translated = $this->cleanTranslation($translated);
            Cache::put($key, $translated, 86400 * 14); // cache 14 days
            return $translated;
        }

        // Fallback: cache the original for 1 hour to avoid hammering translate API
        Cache::put($key, $text, 3600);
        return $text;
    }

    /**
     * Batch-translate an array of Chinese strings in one API call.
     * Much more efficient than calling translateToEnglish() in a loop.
     * Returns an array keyed the same as input.
     */
    private function batchTranslate(array $texts): array
    {
        if (empty($texts)) return [];

        $result  = [];
        $toFetch = [];

        // Check cache first
        foreach ($texts as $k => $text) {
            if (empty($text) || !preg_match('/[\x{4e00}-\x{9fff}]/u', $text)) {
                $result[$k] = $text; // no translation needed
                continue;
            }
            $cacheKey = 'trans_v2_' . md5($text);
            $cached   = Cache::get($cacheKey);
            if ($cached !== null) {
                $result[$k] = $cached;
            } else {
                $toFetch[$k] = $text;
            }
        }

        if (empty($toFetch)) return $result;

        // Batch: join with newlines so one API call handles all
        // Google Translate preserves newlines in output
        $combined = implode("\n|||SPLIT|||\n", array_values($toFetch));
        $translated = $this->callGoogleTranslate($combined);

        if ($translated) {
            $parts = preg_split('/\n\|\|\|SPLIT\|\|\|\n/', $translated);
            $keys  = array_keys($toFetch);
            foreach ($keys as $i => $k) {
                $t = isset($parts[$i]) ? trim($parts[$i]) : $toFetch[$k];
                $t = $this->cleanTranslation($t);
                $result[$k] = $t;
                Cache::put('trans_v2_' . md5($toFetch[$k]), $t, 86400 * 14);
            }
        } else {
            // Fallback: keep originals
            foreach ($toFetch as $k => $text) {
                $result[$k] = $text;
                Cache::put('trans_v2_' . md5($text), $text, 3600);
            }
        }

        return $result;
    }

    private function callGoogleTranslate(string $text): ?string
    {
        $q = urlencode(mb_substr($text, 0, 400));

        // Method 1: cURL (available on all shared hosting, no Guzzle overhead)
        if (function_exists('curl_init')) {
            $ch = curl_init(
                "https://translate.googleapis.com/translate_a/single?client=gtx&sl=zh-CN&tl=en&dt=t&q={$q}"
            );
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT        => 6,
                CURLOPT_CONNECTTIMEOUT => 4,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_USERAGENT      => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)',
                CURLOPT_HTTPHEADER     => ['Accept: */*'],
            ]);
            $resp = @curl_exec($ch);
            // curl_close() is deprecated since PHP 8.4 — handle is freed when
            // the variable goes out of scope. Unset explicitly to free resources now.
            unset($ch);

            if ($resp) {
                $json = json_decode($resp, true);
                $out  = '';
                if (!empty($json[0]) && is_array($json[0])) {
                    foreach ($json[0] as $part) {
                        if (!empty($part[0])) $out .= $part[0];
                    }
                }
                if ($out && $out !== $text) return trim($out);
            }
        }

        // Method 2: file_get_contents fallback (if curl blocked but fopen allowed)
        try {
            $ctx  = stream_context_create([
                'http' => [
                    'timeout'       => 5,
                    'method'        => 'GET',
                    'header'        => "User-Agent: Mozilla/5.0\r\nAccept: */*\r\n",
                    'ignore_errors' => true,
                ],
                'ssl' => ['verify_peer' => false, 'verify_peer_name' => false],
            ]);
            $url  = "https://translate.googleapis.com/translate_a/single?client=gtx&sl=zh-CN&tl=en&dt=t&q={$q}";
            $resp = @file_get_contents($url, false, $ctx);
            if ($resp) {
                $json = json_decode($resp, true);
                $out  = '';
                if (!empty($json[0]) && is_array($json[0])) {
                    foreach ($json[0] as $part) {
                        if (!empty($part[0])) $out .= $part[0];
                    }
                }
                if ($out && $out !== $text) return trim($out);
            }
        } catch (\Exception $e) {
            // silent
        }

        return null;
    }

    private function cleanTranslation(string $text): string
    {
        // Remove common Google Translate artifacts
        $text = preg_replace('/\s+/', ' ', $text);        // collapse whitespace
        $text = str_replace(['「', '」', '【', '】', '《', '》'], ['[', ']', '[', ']', '[', ']'], $text);
        return trim($text);
    }

    /**
     * Filter out known 1688/Alibaba placeholder/mock images.
     * These are tiny thumbnails, default images, or known dummy URLs.
     */
    private function isRealProductImage(string $url): bool
    {
        if (empty($url)) return false;

        // Known placeholder patterns on 1688/alicdn
        $blacklist = [
            'default',
            'placeholder',
            'noimg',
            'no-img',
            'default_',
            '/defaultImg',
            'watermark',
            'logo',
            // Tiny thumbnails (50x50, 100x100, 60x60)
            '_50x50.',
            '_60x60.',
            '_100x100.',
            '_80x80.',
            // 1688 known mock image hash
            'O1CN01',   // keep these — these are real product images
        ];

        // Positive filter: real 1688 product images contain these CDN patterns
        $goodPatterns = ['cbu01.alicdn.com', 'img.alicdn.com', 'gd3.alicdn.com', 'g.alicdn.com'];
        $hasGoodCdn   = false;
        foreach ($goodPatterns as $p) {
            if (str_contains($url, $p)) { $hasGoodCdn = true; break; }
        }

        // Reject tiny dimension suffixes (not O1CN01 which is a file ID pattern)
        foreach (['_50x50.', '_60x60.', '_100x100.', '_80x80.', '_30x30.'] as $tiny) {
            if (str_contains($url, $tiny)) return false;
        }

        // Reject URLs with "default", "placeholder", "noimg" in the path
        $path = strtolower(parse_url($url, PHP_URL_PATH) ?? '');
        foreach (['default', 'placeholder', 'noimg', 'no-img', 'watermark'] as $bad) {
            if (str_contains($path, $bad)) return false;
        }

        // Must have a real image extension
        if (!preg_match('/\.(jpg|jpeg|png|webp)(\?|$)/i', $url)) return false;

        return true;
    }

    /**
     * Fetch a URL via the Zenrows API.
     *
     * Zenrows uses rotating residential IPs + real headless Chrome, so it
     * solves 1688's x5sec/TMD JS challenge that blocks all direct server requests.
     *
     * Cost: ~75 credits per 1688 request (premium proxy + JS render + CN geo).
     * Free tier: 5,000 credits/month = ~66 1688 scrapes/month at zero cost.
     *
     * Returns the rendered HTML, null if disabled/failed, or '__BOT_CHALLENGE__'
     * if even Zenrows couldn't get through.
     */
    private function fetchViaZenrows(string $url): ?string
    {
        $cfg = config('bybhub.zenrows');

        if (empty($cfg['enabled']) || empty($cfg['api_key'])) {
            return null; // Zenrows not configured — caller falls back to direct fetch
        }

        $params = [
            'apikey'        => $cfg['api_key'],
            'url'           => $url,
            'js_render'     => $cfg['js_render']     ? 'true' : 'false',
            'premium_proxy' => $cfg['premium_proxy'] ? 'true' : 'false',
        ];

        // proxy_country requires a paid Zenrows plan — only add if explicitly set
        // and not on the free tier (free tier ignores it but some plans reject it)
        if (!empty($cfg['proxy_country']) && !empty($cfg['paid_plan'])) {
            $params['proxy_country'] = $cfg['proxy_country'];
        }

        $endpoint = 'https://api.zenrows.com/v1/?' . http_build_query($params);

        try {
            $client = new Client([
                'timeout'         => $cfg['timeout'] ?? 60,
                'connect_timeout' => 20,
                'verify'          => false,
                'headers'         => [
                    'User-Agent' => $this->userAgents[0],
                ],
            ]);

            $resp = $client->get($endpoint);
            $body = (string) $resp->getBody();

            // Zenrows returns HTTP 422 / 429 / 500 as JSON error bodies
            if ($resp->getStatusCode() !== 200) {
                Log::warning("Zenrows HTTP {$resp->getStatusCode()} for [{$url}]: " . substr($body, 0, 200));
                return null;
            }

            if (strlen($body) < 2000) {
                Log::warning("Zenrows returned too-small body (" . strlen($body) . " bytes) for [{$url}]");
                return null;
            }

            // Even through Zenrows, the page might still show a bot challenge
            if ($this->isBotChallengePage($body)) {
                Log::warning("Zenrows: bot challenge still present for [{$url}]");
                return '__BOT_CHALLENGE__';
            }

            Log::info("Zenrows success for [{$url}] — " . strlen($body) . " bytes");
            return $body;

        } catch (\GuzzleHttp\Exception\ClientException $e) {
            // 4xx — quota exceeded, bad key, etc.
            $code = $e->getResponse()->getStatusCode();
            $body = (string) $e->getResponse()->getBody();
            Log::error("Zenrows {$code} error for [{$url}]: " . substr($body, 0, 300));
            return null;
        } catch (\Exception $e) {
            Log::warning("Zenrows fetch failed [{$url}]: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Shared Guzzle cookie jar — seeded once per ScraperService instance.
     * Keeping cookies across requests helps bypass 1688's x5sec/TMD challenge.
     */
    private ?\GuzzleHttp\Cookie\CookieJarInterface $cookieJar = null;

    /**
     * Seed the cookie jar by visiting the 1688 homepage first.
     * This gives us a valid session_id / _tb_token_ / cna cookie
     * that makes subsequent product page requests look more browser-like.
     */
    private function warmup1688CookieJar(): void
    {
        if ($this->cookieJar !== null) return; // already warmed up

        $this->cookieJar = new \GuzzleHttp\Cookie\CookieJar();

        $warmupUrls = [
            'https://www.1688.com/',
            'https://m.1688.com/',
        ];

        foreach ($warmupUrls as $warmUrl) {
            try {
                $client = new Client([
                    'timeout'         => 12,
                    'connect_timeout' => 8,
                    'verify'          => false,
                    'cookies'         => $this->cookieJar,
                    'allow_redirects' => ['max' => 5],
                    'headers'         => [
                        'User-Agent'      => $this->userAgents[0],
                        'Accept'          => 'text/html,application/xhtml+xml,*/*;q=0.9',
                        'Accept-Language' => 'zh-CN,zh;q=0.9',
                    ],
                ]);
                $client->get($warmUrl);
                // Short pause to appear human
                usleep(300_000); // 300ms
            } catch (\Exception $e) {
                Log::debug("1688 warmup failed [$warmUrl]: " . $e->getMessage());
            }
        }
    }

    /**
     * Detect 1688 anti-bot challenge pages (TMD / x5sec punish).
     * These are tiny JS-only pages that redirect to /_____tmd_____/punish.
     */
    private function isBotChallengePage(string $html): bool
    {
        return str_contains($html, '_____tmd_____')
            || str_contains($html, 'x5secdata')
            || str_contains($html, 'punish?')
            || str_contains($html, 'sessionStorage.x5referer')
            || (strlen($html) < 5000 && str_contains($html, 'window.location'));
    }

    private function fetch(string $url, array $extra = [], bool $use1688Cookies = false): ?string
    {
        try {
            // Warm up cookies for 1688 requests to bypass x5sec/TMD bot detection
            if ($use1688Cookies) {
                $this->warmup1688CookieJar();
            }

            $ua = $this->userAgents[array_rand($this->userAgents)];

            $clientConfig = [
                'timeout'         => 25,
                'connect_timeout' => 12,
                'verify'          => false,
                'allow_redirects' => ['max' => 5],
                'headers'         => array_merge([
                    'User-Agent'      => $ua,
                    'Accept'          => 'text/html,application/xhtml+xml,*/*;q=0.8',
                    'Accept-Language' => 'zh-CN,zh;q=0.9,en-US;q=0.8',
                    'Accept-Encoding' => 'gzip, deflate, br',
                ], $extra),
            ];

            if ($this->cookieJar !== null) {
                $clientConfig['cookies'] = $this->cookieJar;
            }

            $client = new Client($clientConfig);
            $resp   = $client->get($url);
            $body   = (string) $resp->getBody();

            // Reject login walls
            if (str_contains($body, 'login.1688.com')) return null;

            // Reject bot challenge / TMD punish pages
            if ($this->isBotChallengePage($body)) {
                Log::warning("Scraper: bot challenge detected for [$url]");
                return '__BOT_CHALLENGE__'; // distinct sentinel — not null
            }

            if (strlen($body) < 2000) return null;

            return $body;
        } catch (\Exception $e) {
            Log::warning("Scraper fetch failed [$url]: " . $e->getMessage());
            return null;
        }
    }

    private function cleanUrl(string $url): string
    {
        if (empty($url)) return '';
        $p = parse_url($url);
        if (!$p) return substr($url,0,500);
        $base = ($p['scheme']??'https').'://'.($p['host']??'').($p['path']??'');
        if (str_contains($url,'1688.com'))    return rtrim($base,'/');
        if (str_contains($url,'taobao.com')||str_contains($url,'tmall.com')) {
            parse_str($p['query']??'',$qs); return isset($qs['id'])?$base.'?id='.$qs['id']:$base;
        }
        if (str_contains($url,'alibaba.com')) return rtrim($base,'/');
        parse_str($p['query']??'',$qs);
        foreach(['share_token','__share_id','rpg-cnt','__zhi_token','spm','forcePC']as$k)unset($qs[$k]);
        return substr($base.(!empty($qs)?'?'.http_build_query($qs):''),0,500);
    }

    public function saveToMarketplace(array $data, ?int $scrapedBy = null, ?int $categoryId = null): Product
    {
        $cleanUrl = $this->cleanUrl($data['source_url'] ?? '');
        $existing = Product::where('source_url', $cleanUrl)->first()
                 ?? Product::where('source_url', $data['source_url'] ?? '')->first();

        if ($existing) {
            $existing->update([
                'price_cny'                 => $data['price_cny'] ?: $existing->price_cny,
                'price_cny_max'             => $data['price_cny_max'] ?? $existing->price_cny_max,
                'price_tiers'               => $data['price_tiers'] ?? $existing->price_tiers,
                'stock_status'              => $data['stock_status'] ?? $existing->stock_status,
                'stock_quantity'            => $data['stock_quantity'] ?? $existing->stock_quantity,
                'variants'                  => $data['variants'] ?? $existing->variants,
                'supplier_response_rate'    => $data['supplier_response_rate'] ?? $existing->supplier_response_rate,
                'supplier_repeat_buyer_rate'=> $data['supplier_repeat_buyer_rate'] ?? $existing->supplier_repeat_buyer_rate,
                'supplier_dispute_rate'     => $data['supplier_dispute_rate'] ?? $existing->supplier_dispute_rate,
                'supplier_review_count'     => $data['supplier_review_count'] ?? $existing->supplier_review_count,
                'stock_checked_at'          => now(),
            ]);
            $this->saveSkuMatrix($existing, $data['sku_matrix'] ?? []);
            return $existing;
        }

        $finalCategoryId = $categoryId ?? $data['auto_category_id'] ?? null;

        // ── TASK 1: Auto-detect category from title if not provided ────────
        if (!$finalCategoryId && !empty($data['title'])) {
            $finalCategoryId = $this->detectCategory(
                $data['title'],
                $data['title_cn'] ?? '',
                $data['category_path'] ?? ''
            );
        }

        // ── TASK 5: Auto-detect product attributes from title + description ─
        $autoAttrs = $this->detectAttributes(
            ($data['title'] ?? '') . ' ' . ($data['title_cn'] ?? '') . ' ' . ($data['description'] ?? '')
        );
        // Merge with any already-extracted flags (scraper-extracted takes precedence)
        $hasBattery = $data['has_battery'] ?? $autoAttrs['has_battery'];
        $hasLiquid  = $data['has_liquid']  ?? $autoAttrs['has_liquid'];
        $isPowder   = $data['is_powder']   ?? $autoAttrs['is_powder'];
        $isFood     = $data['is_food']     ?? $autoAttrs['is_food'];
        $isFragile  = $data['is_fragile']  ?? $autoAttrs['is_fragile'];

        $variants = $data['variants'] ?? [];
        if (empty($variants) && !empty($data['price_tiers'])) {
            $variants = [['name' => 'Price Tier', 'options' => array_map(
                fn($t) => ['label' => $t['min_qty'] . '+ pcs', 'price_cny' => $t['price']],
                $data['price_tiers']
            )]];
        }

        $requireApproval = \App\Models\Setting::get('scraper_require_approval', '0');

        $product = Product::create([
            'category_id'               => $finalCategoryId,
            'title'                     => $data['title'],
            'slug'                      => Product::generateSlug($data['title']),
            'title_cn'                  => $data['title_cn'] ?? null,
            'description'               => $data['description'] ?? null,
            'images'                    => $data['images'] ?? [],
            'price_cny'                 => $data['price_cny'] ?? 0,
            'price_cny_max'             => $data['price_cny_max'] ?? null,
            'price_tiers'               => $data['price_tiers'] ?? null,
            'source_url'                => $cleanUrl,
            'source'                    => $data['source'],
            'seller_name'               => $data['seller_name'] ?? null,
            'supplier_id'               => $data['supplier_id'] ?? null,
            'supplier_chat_url'         => $data['supplier_chat_url'] ?? null,
            'supplier_verified'         => $data['supplier_verified'] ?? false,
            'supplier_level'            => $data['supplier_level'] ?? null,
            'supplier_years'            => $data['supplier_years'] ?? null,
            'supplier_response_rate'    => $data['supplier_response_rate'] ?? null,
            'supplier_repeat_buyer_rate'=> $data['supplier_repeat_buyer_rate'] ?? null,
            'supplier_dispute_rate'     => $data['supplier_dispute_rate'] ?? null,
            'supplier_review_count'     => $data['supplier_review_count'] ?? 0,
            'supplier_has_customisation'=> $data['supplier_has_customisation'] ?? false,
            'supplier_export_oriented'  => $data['supplier_export_oriented'] ?? false,
            'supplier_city'             => $data['supplier_city'] ?? null,
            'weight_kg'                 => $data['weight_kg'] ?? null,
            'package_weight_kg'         => $data['weight_kg'] ?? null,
            'volumetric_weight_kg'      => $data['volumetric_weight_kg'] ?? null,
            'length_cm'                 => $data['length_cm'] ?? null,
            'width_cm'                  => $data['width_cm'] ?? null,
            'height_cm'                 => $data['height_cm'] ?? null,
            'domestic_freight_cny'      => $data['domestic_freight_cny'] ?? null,
            'moq'                       => $data['moq'] ?? 1,
            'unit_label'                => $data['unit_label'] ?? null,
            'qty_step'                  => $data['qty_step'] ?? 1,
            'sample_available'          => $data['sample_available'] ?? false,
            'sample_price_cny'          => $data['sample_price_cny'] ?? null,
            'variants'                  => $variants,
            'hs_code'                   => $data['hs_code'] ?? null,
            'refund_policy'             => $data['refund_policy'] ?? 'no_return',
            'stock_status'              => $data['stock_status'] ?? 'in_stock',
            'stock_quantity'            => $data['stock_quantity'] ?? null,
            'stock_checked_at'          => now(),
            'has_battery'               => $hasBattery,
            'has_liquid'                => $hasLiquid,
            'is_fragile'                => $isFragile,
            'is_powder'                 => $isPowder,
            'is_food'                   => $isFood,
            'order_count'               => $data['sold_count'] ?? 0,
            'is_active'                 => true,
            'is_approved'               => !$requireApproval,
            'added_by'                  => $scrapedBy,
            'scraped_by'                => $scrapedBy,
        ]);

        $this->saveSkuMatrix($product, $data['sku_matrix'] ?? []);

        return $product;
    }

    /**
     * Upsert the flat SKU matrix rows for a product.
     */
    private function saveSkuMatrix(Product $product, array $skuMatrix): void
    {
        if (empty($skuMatrix)) return;

        foreach ($skuMatrix as $sku) {
            if (empty($sku['supplier_sku_id']) && empty($sku['attributes'])) continue;

            \App\Models\ProductSku::updateOrCreate(
                [
                    'product_id'      => $product->id,
                    'supplier_sku_id' => $sku['supplier_sku_id'] ?? null,
                ],
                [
                    'attributes'  => $sku['attributes'] ?? [],
                    'price_cny'   => $sku['price_cny'] ?? $product->price_cny,
                    'stock'       => $sku['stock'] ?? null,
                    'image'       => $sku['image'] ?? null,
                    'is_available'=> true,
                ]
            );
        }
    }
}
