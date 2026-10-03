<?php

namespace App\Http\Controllers;

use App\Models\BrandList;
use App\Models\CategoryList;
use App\Models\ProductDetail;
use Illuminate\Http\Response;
use Illuminate\Support\Str;

class SitemapController extends Controller
{
    /**
     * Generate dynamic UTF-8 XML Sitemap for JaffnaGoldPOS.
     *
     * @return Response
     */
    public function index(): Response
    {
        $products = ProductDetail::with(['category', 'brand'])
            ->where('status', 'active')
            ->where('website_live', true)
            ->get();

        $categories = CategoryList::all();
        $brands = BrandList::all();

        $urls = [];

        // 1. Homepage
        $urls[] = [
            'loc' => route('home'),
            'lastmod' => now()->toAtomString(),
            'changefreq' => 'daily',
            'priority' => '1.0',
        ];

        // 2. Shop Page
        $urls[] = [
            'loc' => route('shop'),
            'lastmod' => now()->toAtomString(),
            'changefreq' => 'daily',
            'priority' => '0.9',
        ];

        // 3. Static CMS Pages
        $urls[] = [
            'loc' => route('about'),
            'lastmod' => now()->toAtomString(),
            'changefreq' => 'monthly',
            'priority' => '0.6',
        ];
        $urls[] = [
            'loc' => route('contact'),
            'lastmod' => now()->toAtomString(),
            'changefreq' => 'monthly',
            'priority' => '0.6',
        ];
        $urls[] = [
            'loc' => route('journal'),
            'lastmod' => now()->toAtomString(),
            'changefreq' => 'monthly',
            'priority' => '0.6',
        ];

        // 4. Category Pages
        foreach ($categories as $category) {
            $urls[] = [
                'loc' => route('shop', ['category' => Str::slug($category->category_name)]),
                'lastmod' => $category->updated_at ? $category->updated_at->toAtomString() : now()->toAtomString(),
                'changefreq' => 'weekly',
                'priority' => '0.8',
            ];
        }

        // 5. Brand Pages
        foreach ($brands as $brand) {
            $urls[] = [
                'loc' => route('shop', ['brand' => Str::slug($brand->brand_name)]),
                'lastmod' => $brand->updated_at ? $brand->updated_at->toAtomString() : now()->toAtomString(),
                'changefreq' => 'weekly',
                'priority' => '0.75',
            ];
        }

        // 6. Active Products
        foreach ($products as $product) {
            $imageUrl = $this->resolveImageUrl($product);
            $urls[] = [
                'loc' => route('product-detail.slug', ['slug' => $product->slug]),
                'lastmod' => $product->updated_at ? $product->updated_at->toAtomString() : now()->toAtomString(),
                'changefreq' => 'weekly',
                'priority' => '0.85',
                'image' => [
                    'loc' => $imageUrl,
                    'title' => $product->name,
                    'caption' => strip_tags($product->description ?: $product->name),
                ],
            ];
        }

        $xml = view('seo.sitemap', ['urls' => $urls])->render();

        return response($xml, 200)
            ->header('Content-Type', 'application/xml; charset=UTF-8')
            ->header('Cache-Control', 'public, max-age=3600');
    }

    private function resolveImageUrl(ProductDetail $product): string
    {
        $raw = trim((string) $product->getRawOriginal('image'));

        if ($raw === '' || $raw === 'images/product.jpg') {
            return asset('images/product.jpg');
        }

        if (str_starts_with($raw, 'http://') || str_starts_with($raw, 'https://')) {
            return $raw;
        }

        if (str_starts_with($raw, 'storage/images/')) {
            $filename = substr($raw, strlen('storage/images/'));
            return url('/product-image-serve/' . $filename);
        }

        if (str_starts_with($raw, 'images/')) {
            return asset($raw);
        }

        return url('/product-image-serve/' . ltrim($raw, '/'));
    }
}
