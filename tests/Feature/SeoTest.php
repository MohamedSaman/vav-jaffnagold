<?php

namespace Tests\Feature;

use App\Models\BrandList;
use App\Models\CategoryList;
use App\Models\ProductDetail;
use App\Models\ProductPrice;
use App\Models\ProductStock;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeoTest extends TestCase
{
    use RefreshDatabase;

    public function test_homepage_renders_head_meta_and_organization_schema()
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('<title>Jaffna Gold | Handcrafted 22K &amp; 925 Sterling Silver Jewelry</title>', false);
        $response->assertSee('<meta name="robots" content="index, follow">', false);
        $response->assertSee('<link rel="canonical" href="'.url('/').'">', false);
        $response->assertSee('"@type": "JewelryStore"', false);
        $response->assertSee('"@type": "WebSite"', false);
    }

    public function test_product_detail_slug_route_renders_product_schema_and_canonical()
    {
        $category = CategoryList::create(['category_name' => 'Rings']);
        $brand = BrandList::create(['brand_name' => 'Jaffna Artisans']);

        $product = ProductDetail::create([
            'code' => 'JG-RING-01',
            'name' => '22K Gold Classic Ring',
            'description' => 'Fine handcrafted ring',
            'status' => 'active',
            'website_live' => true,
            'category_id' => $category->id,
            'brand_id' => $brand->id,
            'image' => 'images/ring.webp',
        ]);

        ProductPrice::create([
            'product_id' => $product->id,
            'supplier_price' => 80000,
            'retail_price' => 100000,
            'selling_price' => 100000,
        ]);

        ProductStock::create([
            'product_id' => $product->id,
            'available_stock' => 5,
        ]);

        $canonicalUrl = route('product-detail.slug', ['slug' => $product->slug]);
        $response = $this->get($canonicalUrl);

        $response->assertStatus(200);
        $response->assertSee('<link rel="canonical" href="'.$canonicalUrl.'">', false);
        $response->assertSee('"@type": "Product"', false);
        $response->assertSee('"name": "22K Gold Classic Ring"', false);
        $response->assertSee('"@type": "BreadcrumbList"', false);
    }

    public function test_legacy_product_detail_query_url_redirects_301_to_slug_url()
    {
        $category = CategoryList::create(['category_name' => 'Necklaces']);
        $brand = BrandList::create(['brand_name' => 'Jaffna Necklaces']);

        $product = ProductDetail::create([
            'code' => 'JG-NECK-01',
            'name' => 'Royal Gold Necklace',
            'status' => 'active',
            'website_live' => true,
            'category_id' => $category->id,
            'brand_id' => $brand->id,
        ]);

        $legacyUrl = '/product-detail?id='.$product->id;
        $expectedCanonical = route('product-detail.slug', ['slug' => $product->slug]);

        $response = $this->get($legacyUrl);

        $response->assertStatus(301);
        $response->assertRedirect($expectedCanonical);
    }

    public function test_private_pages_contain_noindex_nofollow_robots_meta()
    {
        $cartResponse = $this->get('/cart');
        $cartResponse->assertStatus(200);
        $cartResponse->assertSee('<meta name="robots" content="noindex, nofollow">', false);

        $visualSearchResponse = $this->get('/visual-search');
        $visualSearchResponse->assertStatus(200);
        $visualSearchResponse->assertSee('<meta name="robots" content="noindex, nofollow">', false);
    }

    public function test_sitemap_xml_returns_valid_content_header_and_markup()
    {
        $category = CategoryList::create(['category_name' => 'Bangles']);
        $brand = BrandList::create(['brand_name' => 'Jaffna Bangles']);

        ProductDetail::create([
            'code' => 'JG-BANG-01',
            'name' => 'Gold Bangle',
            'status' => 'active',
            'website_live' => true,
            'category_id' => $category->id,
            'brand_id' => $brand->id,
            'image' => 'images/bangle.webp',
        ]);

        $response = $this->get('/sitemap.xml');

        $response->assertStatus(200);
        $this->assertStringContainsString('application/xml', $response->headers->get('Content-Type'));
        $response->assertSee('<urlset', false);
        $response->assertSee('<loc>', false);
        $response->assertSee('<image:loc>', false);
    }

    public function test_robots_txt_returns_disallow_directives_and_sitemap()
    {
        $response = $this->get('/robots.txt');

        $response->assertStatus(200);
        $response->assertSee('Disallow: /admin/');
        $response->assertSee('Disallow: /cart');
        $response->assertSee('Sitemap:');
    }
}
