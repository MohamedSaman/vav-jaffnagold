<?php

namespace Tests\Feature;

use App\Livewire\Admin\CustomProductManagement;
use App\Livewire\Admin\CustomProductRequests;
use App\Models\CustomProduct;
use App\Models\CustomProductInquiry;
use App\Models\StaffPermission;
use App\Models\StaffTypePermission;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class AdminCustomProductsTest extends TestCase
{
    use DatabaseTransactions;

    protected $adminUser;

    protected $sampleProduct;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminUser = User::create([
            'name' => 'Super Admin',
            'email' => 'admin_'.uniqid().'@jaffnagold.lk',
            'contact' => '0777123999',
            'role' => 'admin',
            'password' => bcrypt('password123'),
        ]);

        $this->sampleProduct = CustomProduct::firstOrCreate(
            ['slug' => 'star-kodi'],
            [
                'name' => 'Star Kodi Traditional Chain',
                'tamil_name' => 'ஸ்டார் கொடி',
                'category' => 'kodi',
                'category_label' => 'Kodi (Traditional Chains)',
                'purity' => 'Gold Covering (உயர் கவரிங்)',
                'pown_options' => ['20', '22', '24', '26'],
                'min_pown' => 0.00,
                'max_pown' => 0.00,
                'base_pown' => 0.00,
                'price_per_pown_estimate' => 0.00,
                'price_range_min' => 4500.00,
                'price_range_max' => 8500.00,
                'making_days' => 7,
                'is_active' => true,
                'is_featured' => true,
            ]
        );
    }

    /**
     * Test admin can access both Custom Requests and Custom Products pages.
     */
    public function test_admin_can_access_custom_requests_and_products_pages(): void
    {
        $this->actingAs($this->adminUser);

        $responseRequests = $this->get(route('admin.custom-requests'));
        $responseRequests->assertStatus(200);
        $responseRequests->assertSee('Custom Jewelry Requests');

        $responseProducts = $this->get(route('admin.custom-products-manage'));
        $responseProducts->assertStatus(200);
        $responseProducts->assertSee('Custom Products', false);
        $responseProducts->assertSee('Pricing Setup', false);
    }

    /**
     * Test admin can view an inquiry, update the quote, notes, and status.
     */
    public function test_admin_can_view_inquiries_and_update_quote_and_status(): void
    {
        $this->actingAs($this->adminUser);

        $inquiry = CustomProductInquiry::create([
            'inquiry_number' => CustomProductInquiry::generateInquiryNumber(),
            'custom_product_id' => $this->sampleProduct->id,
            'product_name' => $this->sampleProduct->name,
            'category' => $this->sampleProduct->category,
            'customer_name' => 'Kajanthan S.',
            'customer_phone' => '0771234567',
            'customer_email' => 'kajan@example.com',
            'city' => 'Jaffna Town',
            'selected_pown' => 0.0,
            'selected_grams' => 0.0,
            'size_specifications' => 'Length: 26 inches',
            'gold_purity' => 'Gold Covering',
            'estimated_price' => 7500.00,
            'customer_notes' => 'Please make sure it has heavy duty traditional lock',
            'status' => 'new',
        ]);

        Livewire::test(CustomProductRequests::class)
            ->call('openDetail', $inquiry->id)
            ->assertSet('showDetailModal', true)
            ->assertSet('selectedInquiryId', $inquiry->id)
            ->set('quotePrice', 8000.00)
            ->set('adminNotes', 'Assigned to Master Goldsmith Raman. Making timeframe: 6 days.')
            ->set('updateStatus', 'quoted')
            ->call('saveQuoteAndNotes')
            ->assertHasNoErrors();

        $inquiry->refresh();
        $this->assertEquals('quoted', $inquiry->status);
        $this->assertEquals(8000.00, (float) $inquiry->quoted_price);
        $this->assertEquals('Assigned to Master Goldsmith Raman. Making timeframe: 6 days.', $inquiry->admin_notes);
    }

    /**
     * Test admin can update a custom product's price range and options directly without pown calculation.
     */
    public function test_admin_can_update_product_price_range_directly(): void
    {
        $this->actingAs($this->adminUser);

        Livewire::test(CustomProductManagement::class)
            ->call('openEditModal', $this->sampleProduct->id)
            ->assertSet('isEditing', true)
            ->assertSet('editingProductId', $this->sampleProduct->id)
            ->set('price_range_min', 5500.00)
            ->set('price_range_max', 9500.00)
            ->set('pown_options_input', '20, 22, 24, 26, 28')
            ->call('saveProduct')
            ->assertHasNoErrors();

        $this->sampleProduct->refresh();
        $this->assertEquals(5500.00, (float) $this->sampleProduct->price_range_min);
        $this->assertEquals(9500.00, (float) $this->sampleProduct->price_range_max);
        $this->assertEquals(['20', '22', '24', '26', '28'], $this->sampleProduct->pown_options);
    }

    /**
     * Test admin can upload a product photograph when creating or editing a custom product.
     */
    public function test_admin_can_upload_custom_product_image(): void
    {
        Storage::fake('public');
        $this->actingAs($this->adminUser);

        $fakeImage = UploadedFile::fake()->image('custom_star_kodi.jpg', 600, 600);

        Livewire::test(CustomProductManagement::class)
            ->call('openEditModal', $this->sampleProduct->id)
            ->set('image_file', $fakeImage)
            ->call('saveProduct')
            ->assertHasNoErrors();

        $this->sampleProduct->refresh();
        $this->assertNotNull($this->sampleProduct->image);
        $this->assertStringStartsWith('storage/custom-products/', $this->sampleProduct->image);
    }

    /**
     * Test admin can upload more than 2 gallery images and preview them.
     */
    public function test_admin_can_upload_more_than_two_gallery_images(): void
    {
        Storage::fake('public');
        $this->actingAs($this->adminUser);

        $this->sampleProduct->update(['gallery_images' => []]);

        $fakeCover = UploadedFile::fake()->image('cover.jpg', 600, 600);
        $fakeImg1 = UploadedFile::fake()->image('gallery1.jpg', 600, 600);
        $fakeImg2 = UploadedFile::fake()->image('gallery2.jpg', 600, 600);
        $fakeImg3 = UploadedFile::fake()->image('gallery3.jpg', 600, 600);
        $fakeImg4 = UploadedFile::fake()->image('gallery4.jpg', 600, 600);

        Livewire::test(CustomProductManagement::class)
            ->call('openEditModal', $this->sampleProduct->id)
            ->set('image_file', $fakeCover)
            ->set('gallery_files', [$fakeImg1, $fakeImg2, $fakeImg3, $fakeImg4])
            ->call('saveProduct')
            ->assertHasNoErrors();

        $this->sampleProduct->refresh();
        $this->assertNotNull($this->sampleProduct->image);
        $this->assertIsArray($this->sampleProduct->gallery_images);
        $this->assertCount(4, $this->sampleProduct->gallery_images);
        $this->assertGreaterThanOrEqual(5, count($this->sampleProduct->all_images));
        $this->assertGreaterThanOrEqual(5, count($this->sampleProduct->all_image_urls));

        // Test removing a gallery image option
        Livewire::test(CustomProductManagement::class)
            ->call('openEditModal', $this->sampleProduct->id)
            ->call('removeGalleryImage', 0)
            ->call('saveProduct')
            ->assertHasNoErrors();

        $this->sampleProduct->refresh();
        $this->assertCount(3, $this->sampleProduct->gallery_images);
    }

    /**
     * Test custom jewelry permissions are properly registered for access control.
     */
    public function test_permissions_exist_in_staff_type_and_staff_permission(): void
    {
        $staffTypeAvailable = StaffTypePermission::availablePermissions();
        $this->assertArrayHasKey('view_custom_products', $staffTypeAvailable);
        $this->assertArrayHasKey('manage_custom_products', $staffTypeAvailable);
        $this->assertArrayHasKey('view_custom_requests', $staffTypeAvailable);
        $this->assertArrayHasKey('manage_custom_requests', $staffTypeAvailable);

        $staffAvailable = StaffPermission::availablePermissions();
        $this->assertArrayHasKey('menu_custom_products', $staffAvailable);
        $this->assertArrayHasKey('menu_custom_requests', $staffAvailable);
    }
}
