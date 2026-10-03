<?php

namespace Tests\Feature;

use App\Livewire\Ecommerce\CustomProducts;
use App\Models\CustomProduct;
use App\Models\CustomProductInquiry;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use Tests\TestCase;

class CustomProductsWorkflowTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        if (CustomProduct::count() === 0) {
            $this->seed(\Database\Seeders\CustomProductSeeder::class);
        }
    }

    /**
     * Verify custom products storefront page loads successfully with 200 OK.
     */
    public function test_custom_products_page_loads_with_status_200(): void
    {
        $response = $this->get(route('custom-products'));

        $response->assertStatus(200);
        $response->assertSee('Custom Masterpieces');
        $response->assertSee('Gold Covering');
        $response->assertSee('Star Kodi');
    }

    /**
     * Verify custom products table contains all authentic Sri Lankan items in Gold Covering.
     */
    public function test_custom_products_table_has_all_sri_lankan_items(): void
    {
        // 1. Kodi category (Star Kodi, Veera Kodi)
        $starKodi = CustomProduct::where('slug', 'star-kodi')->first();
        $this->assertNotNull($starKodi);
        $this->assertEquals('kodi', $starKodi->category);
        $this->assertStringContainsString('Micro Gold Plated', $starKodi->purity);
        $this->assertGreaterThan(1000.0, $starKodi->price_range_min);
        $this->assertLessThan(20000.0, $starKodi->price_range_max);

        $veeraKodi = CustomProduct::where('slug', 'veera-kodi')->first();
        $this->assertNotNull($veeraKodi);
        $this->assertEquals('kodi', $veeraKodi->category);

        // 2. Bangles category (Lock SL Bangle, Spring SL Bangle)
        $lockBangle = CustomProduct::where('slug', 'lock-sl-bangle')->first();
        $this->assertNotNull($lockBangle);
        $this->assertEquals('bangle', $lockBangle->category);

        $springBangle = CustomProduct::where('slug', 'spring-sl-bangle')->first();
        $this->assertNotNull($springBangle);
        $this->assertEquals('bangle', $springBangle->category);

        // 3. Rings category (Thakar Ring)
        $thakarRing = CustomProduct::where('slug', 'thakar-ring')->first();
        $this->assertNotNull($thakarRing);
        $this->assertEquals('ring', $thakarRing->category);
    }

    /**
     * Verify Livewire category filters reactivity.
     */
    public function test_category_and_weight_filters_reactivity(): void
    {
        Livewire::test(CustomProducts::class)
            ->set('selectedCategory', 'kodi')
            ->assertSee('Singapore Kodi')
            ->assertDontSee('Spring SL Bangle')
            ->assertDontSee('Navarathinam Traditional Ring')
            ->set('selectedCategory', 'bangle')
            ->assertSee('Spring SL Bangle')
            ->assertDontSee('Singapore Kodi')
            ->assertDontSee('Navarathinam Traditional Ring')
            ->set('selectedCategory', 'ring')
            ->assertSee('Navarathinam Traditional Ring')
            ->assertDontSee('Singapore Kodi')
            ->assertDontSee('Spring SL Bangle');
    }

    /**
     * Verify custom covering design and price estimator computes piece prices (no gram rate).
     */
    public function test_live_gold_and_pown_calculator_computes_grams_and_prices(): void
    {
        $component = Livewire::test(CustomProducts::class);

        // Kodi piece pricing
        $component->set('calcCategory', 'kodi')
            ->set('calcLengthSize', '24"')
            ->set('calcPlatingType', 'standard');

        $this->assertGreaterThan(3000.0, $component->get('calcPriceMin'));
        $this->assertLessThan(15000.0, $component->get('calcPriceMax'));

        // Double coat plating tier increases piece price
        $component->set('calcPlatingType', 'double_coat');
        $this->assertGreaterThan(4500.0, $component->get('calcPriceMin'));

        // Rings piece pricing
        $component->set('calcCategory', 'ring')
            ->set('calcPlatingType', 'standard');
        $this->assertGreaterThan(1000.0, $component->get('calcPriceMin'));
        $this->assertLessThan(5000.0, $component->get('calcPriceMax'));
    }

    /**
     * Verify customer can submit a custom order inquiry and triggers SMS dispatch.
     */
    public function test_customer_can_submit_custom_inquiry_and_dispatches_sms(): void
    {
        Http::fake([
            'https://smslenz.lk/api/send-sms' => Http::response([
                'status' => 'success',
                'message' => 'SMS queued successfully',
            ], 200),
        ]);

        $starKodi = CustomProduct::where('slug', 'star-kodi')->firstOrFail();

        Livewire::test(CustomProducts::class)
            ->call('openInquiryModal', $starKodi->id, '24 inches')
            ->assertSet('showInquiryModal', true)
            ->assertSet('inquirySize', '24 inches')
            ->set('inquiryCustomerName', 'Tharshan Maheswaran')
            ->set('inquiryCustomerPhone', '0775566778')
            ->set('inquiryCity', 'Nallur, Jaffna')
            ->set('inquiryPlating', 'double_coat')
            ->set('inquiryNotes', 'Please provide 24 inches star kodi with extra high polish finish')
            ->call('submitInquiry')
            ->assertSet('inquirySubmitted', true)
            ->assertHasNoErrors();

        // Verify record in custom_product_inquiries table
        $inquiry = CustomProductInquiry::where('customer_phone', '0775566778')->latest()->first();
        $this->assertNotNull($inquiry);
        $this->assertStringStartsWith('CUST-', $inquiry->inquiry_number);
        $this->assertEquals($starKodi->id, $inquiry->custom_product_id);
        $this->assertEquals('Tharshan Maheswaran', $inquiry->customer_name);
        $this->assertEquals('Nallur, Jaffna', $inquiry->city);
        $this->assertStringContainsString('24 inches', $inquiry->size_specifications);
        $this->assertGreaterThan(3000.0, (float) $inquiry->estimated_price);
        $this->assertEquals('new', $inquiry->status);

        // Verify SMS was sent to customer
        Http::assertSent(function ($request) {
            return str_contains($request->url(), 'smslenz.lk/api/send-sms')
                && $request['contact'] === '+94775566778'
                && str_contains($request['message'], 'Jaffna Gold: Vanakkam Tharshan Maheswaran!')
                && str_contains($request['message'], 'Star Kodi');
        });
    }
}
