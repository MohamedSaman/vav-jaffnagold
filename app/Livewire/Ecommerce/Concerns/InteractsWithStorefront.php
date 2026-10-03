<?php

namespace App\Livewire\Ecommerce\Concerns;

use App\Models\BrandList;
use App\Models\CategoryList;
use Illuminate\Support\Facades\Cache;

trait InteractsWithStorefront
{
    /**
     * Build layout data for storefront components to inject into webapp layout.
     *
     * @param array $seoData
     * @return array
     */
    public function sharedLayoutData(array $seoData = []): array
    {
        $defaultTitle = 'Jaffna Gold | Handcrafted 22K & 925 Sterling Silver Jewelry';
        $defaultDesc = 'Discover Jaffna Gold - Premium handcrafted 22K gold and 925 sterling silver jewelry in Sri Lanka. Explore rings, necklaces, earrings, bracelets, and custom designs.';
        $defaultKeywords = 'Jaffna Gold, gold jewelry Sri Lanka, 22K gold, 925 sterling silver, handcrafted jewelry, gold rings, gold necklaces, Jaffna goldsmiths, luxury jewelry';

        $title = $seoData['title'] ?? $defaultTitle;
        $seoTitle = (str_contains($title, 'Jaffna Gold') || str_contains($title, 'JaffnaGoldPOS'))
            ? $title
            : $title . ' | Jaffna Gold';

        $seoDesc = $seoData['description'] ?? $defaultDesc;
        $seoKeywords = $seoData['keywords'] ?? $defaultKeywords;
        $seoRobots = $seoData['robots'] ?? 'index, follow';
        $seoCanonical = $seoData['canonical'] ?? url()->current();
        $ogImage = $seoData['image'] ?? asset('images/jgg.png');
        $ogType = $seoData['og_type'] ?? 'website';
        $schemas = $seoData['schemas'] ?? [];

        $categories = Cache::remember('nav_categories', 3600, function () {
            return CategoryList::all();
        });

        $brands = Cache::remember('nav_brands', 3600, function () {
            return BrandList::all();
        });

        return [
            'title' => $seoTitle,
            'seoTitle' => $seoTitle,
            'seoDesc' => $seoDesc,
            'seoKeywords' => $seoKeywords,
            'seoRobots' => $seoRobots,
            'seoCanonical' => $seoCanonical,
            'ogImage' => $ogImage,
            'ogType' => $ogType,
            'schemas' => $schemas,
            'categories' => $categories,
            'brands' => $brands,
        ];
    }
}
