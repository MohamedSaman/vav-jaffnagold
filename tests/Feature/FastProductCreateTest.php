<?php

namespace Tests\Feature;

use App\Livewire\Admin\FastProductCreate;
use App\Models\BrandList;
use App\Models\CategoryList;
use App\Models\ProductDetail;
use App\Models\ProductPrice;
use App\Models\ProductStock;
use App\Models\PurchaseOrder;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class FastProductCreateTest extends TestCase
{
    use DatabaseTransactions;

    protected $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        $userId = DB::table('users')->insertGetId([
            'name' => 'Admin Test User',
            'email' => 'admin_'.uniqid().'@example.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'contact' => '0771234567',
            'email_verified_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->adminUser = User::find($userId);

        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        Auth::login($this->adminUser);
    }

    /** @test */
    public function it_can_initialize_and_update_category()
    {
        $component = new FastProductCreate;
        $component->mount();

        $this->assertGreaterThanOrEqual(0, $component->defaultMarginPercent);

        $cat = CategoryList::create(['category_name' => 'Gold Chains Test']);
        $component->updatedSelectedCategoryId($cat->id);

        $this->assertEquals('Gold Chains Test', $component->selectedItemName);
    }

    /** @test */
    public function it_can_add_multiple_items_with_rapid_entry_and_calculate_margin()
    {
        $component = new FastProductCreate;
        $component->mount();
        $component->costMultiplier = 1.0;
        $component->sellingMultiplier = 1.2;

        // 1st Product: Chain (Cost: 100,000, Margin: 20% -> Selling Price: 120,000, Qty: 2)
        $component->selectedItemName = 'Chain';
        $component->defaultMarginPercent = 20;
        $component->quickCost = 100000;
        $component->quickQuantity = 2;
        $component->addItemToBatch();

        $this->assertCount(1, $component->items);
        $this->assertEquals('Chain', $component->items[0]['name']);
        $this->assertEquals(100000, $component->items[0]['cost_price']);
        $this->assertEquals(1000.0, $component->items[0]['margin_percent']);
        $this->assertEquals(120000, $component->items[0]['selling_price']);
        $this->assertEquals(2, $component->items[0]['quantity']);

        // 2nd Product: Rings (Cost: 50,000, Margin: 20% -> Selling Price: 60,000, Qty: 5)
        $component->selectedItemName = 'Rings';
        $component->quickCost = 50000;
        $component->quickQuantity = 5;
        $component->addItemToBatch();

        $this->assertCount(2, $component->items);
        $this->assertEquals('Rings', $component->items[0]['name']);
        $this->assertEquals(50000, $component->items[0]['cost_price']);
        $this->assertEquals(60000, $component->items[0]['selling_price']);
        $this->assertEquals(5, $component->items[0]['quantity']);
        $this->assertEquals('Chain', $component->items[1]['name']);
    }

    /** @test */
    public function it_supports_manual_barcode_input_and_single_qr_generation()
    {
        $component = new FastProductCreate;
        $component->mount();

        $component->quickCost = 50000;
        $component->quickQuantity = 1;
        $component->addItemToBatch();

        $component->quickCost = 80000;
        $component->quickQuantity = 1;
        $component->addItemToBatch();

        // Manually enter a barcode for the 1st item
        $component->updateRowBarcode(0, 'CUSTOM-BARCODE-123');
        $this->assertEquals('CUSTOM-BARCODE-123', $component->items[0]['barcode']);

        // Click Gen on 2nd item
        $component->generateSingleQR(1);
        $this->assertNotNull($component->items[1]['barcode']);
        $this->assertEquals(13, strlen($component->items[1]['barcode']));
    }

    /** @test */
    public function it_generates_unique_qr_barcodes_for_all_unassigned_rows()
    {
        $component = new FastProductCreate;
        $component->mount();

        $component->quickCost = 75000;
        $component->quickQuantity = 1;
        $component->addItemToBatch();

        $component->quickCost = 125000;
        $component->quickQuantity = 3;
        $component->addItemToBatch();

        $this->assertNull($component->items[0]['barcode']);
        $this->assertNull($component->items[1]['barcode']);

        $component->generateAllQRCodes();

        $this->assertNotNull($component->items[0]['barcode']);
        $this->assertNotNull($component->items[1]['barcode']);
        $this->assertNotEquals($component->items[0]['barcode'], $component->items[1]['barcode']);
        $this->assertEquals(13, strlen($component->items[0]['barcode']));
    }

    /** @test */
    public function it_filters_batch_items_by_with_qr_no_qr_printed_and_not_printed()
    {
        $component = new FastProductCreate;
        $component->mount();

        // Add 3 items
        $component->quickCost = 10000;
        $component->quickQuantity = 1;
        $component->addItemToBatch(); // Row 0: no QR, not printed

        $component->quickCost = 20000;
        $component->quickQuantity = 1;
        $component->addItemToBatch(); // Row 1: will have QR, not printed

        $component->quickCost = 30000;
        $component->quickQuantity = 1;
        $component->addItemToBatch(); // Row 2: will have QR, printed

        // Set QR for item 1 & 2
        $component->updateRowBarcode(1, '2001111111111');
        $component->updateRowBarcode(2, '2002222222222');

        // Mark item 2 as printed
        $component->markRowPrinted(2);

        // Check filter counts
        $counts = $component->getFilterCounts();
        $this->assertEquals(3, $counts['all']);
        $this->assertEquals(2, $counts['with_qr']);
        $this->assertEquals(1, $counts['no_qr']);
        $this->assertEquals(1, $counts['printed']);
        $this->assertEquals(2, $counts['not_printed']);

        // Test Filter: 'all'
        $component->setFilter('all');
        $this->assertEquals(3, $component->getFilteredCount());

        // Test Filter: 'with_qr'
        $component->setFilter('with_qr');
        $this->assertEquals(2, $component->getFilteredCount());

        // Test Filter: 'no_qr'
        $component->setFilter('no_qr');
        $this->assertEquals(1, $component->getFilteredCount());

        // Test Filter: 'printed'
        $component->setFilter('printed');
        $this->assertEquals(1, $component->getFilteredCount());

        // Test Filter: 'not_printed'
        $component->setFilter('not_printed');
        $this->assertEquals(2, $component->getFilteredCount());

        // Test toggle print status
        $component->togglePrintStatus(0);
        $this->assertTrue($component->items[0]['is_printed']);
        $this->assertEquals(2, $component->getFilterCounts()['printed']);

        // Test mark all as printed
        $component->markAllBatchAsPrinted();
        $this->assertTrue($component->items[1]['is_printed']);
        $this->assertTrue($component->items[2]['is_printed']);
    }

    /** @test */
    public function it_saves_batch_products_to_database_with_prices_stocks_and_purchase_orders()
    {
        $component = new FastProductCreate;
        $component->mount();
        $component->costMultiplier = 1.0;
        $component->sellingMultiplier = 1.25;

        $component->selectedItemName = 'Necklace';
        $component->defaultMarginPercent = 25;
        $component->quickCost = 200000;
        $component->quickQuantity = 3;
        $component->addItemToBatch();

        $component->selectedItemName = 'Aaram';
        $component->quickCost = 300000;
        $component->quickQuantity = 1;
        $component->addItemToBatch();

        $component->saveBatch();

        $this->assertTrue($component->isSaved);
        $this->assertEquals(2, $component->savedCount);

        // Verify Necklace in database
        $this->assertDatabaseHas('product_details', [
            'name' => 'Necklace',
            'barcode_printed' => 'No',
        ]);

        // Verify Aaram in database
        $this->assertDatabaseHas('product_details', [
            'name' => 'Aaram',
            'barcode_printed' => 'No',
        ]);

        // Verify ProductPrice
        $this->assertDatabaseHas('product_prices', [
            'supplier_price' => 200000,
            'selling_price' => 250000,
            'retail_price' => 250000,
            'wholesale_price' => 250000,
        ]);

        // Verify ProductStock
        $this->assertDatabaseHas('product_stocks', [
            'available_stock' => 3,
        ]);

        // Verify PurchaseOrder created
        $this->assertDatabaseHas('purchase_orders', [
            'status' => 'received',
        ]);
    }

    /** @test */
    public function it_can_download_sample_template()
    {
        $component = new FastProductCreate;
        $response = $component->downloadSampleTemplate();

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('text/csv', $response->headers->get('content-type'));
    }

    /** @test */
    public function it_handles_duplicate_codes_and_barcodes_in_the_same_batch_gracefully()
    {
        $component = new FastProductCreate;
        $component->mount();

        // 1st Item with specific SKU and Barcode
        $component->items[] = [
            'temp_id' => '1',
            'name' => 'Bangle 1',
            'code' => 'DUPLICATE-SKU',
            'cost_price' => 50000,
            'margin_percent' => 20,
            'selling_price' => 60000,
            'quantity' => 1,
            'barcode' => '2001111111115',
            'is_saved' => false,
            'saved_product_id' => null,
        ];

        // 2nd Item intentionally given the EXACT SAME SKU and Barcode
        $component->items[] = [
            'temp_id' => '2',
            'name' => 'Bangle 2',
            'code' => 'DUPLICATE-SKU',
            'cost_price' => 50000,
            'margin_percent' => 20,
            'selling_price' => 60000,
            'quantity' => 2,
            'barcode' => '2001111111115',
            'is_saved' => false,
            'saved_product_id' => null,
        ];

        // Save batch - should auto-resolve collisions without SQL error
        $component->saveBatch();

        $this->assertTrue($component->isSaved);
        $this->assertEquals(2, $component->savedCount);
        $this->assertEmpty($component->errorMessage);

        // Both products must exist in database with different unique codes and barcodes
        $p1 = ProductDetail::where('name', 'Bangle 1')->first();
        $p2 = ProductDetail::where('name', 'Bangle 2')->first();

        $this->assertNotNull($p1);
        $this->assertNotNull($p2);
        $this->assertNotEquals($p1->code, $p2->code);
        $this->assertNotEquals($p1->barcode, $p2->barcode);
    }

    /** @test */
    public function it_cleans_currency_prefixes_and_clamps_negative_values()
    {
        $component = new FastProductCreate;
        $component->mount();
        $component->costMultiplier = 1.0;
        $component->sellingMultiplier = 1.0;

        $component->quickCost = 10000;
        $component->addItemToBatch();

        // Test updating cost with "Rs. 25,000.50"
        $component->updateRowCost(0, 'Rs. 25,000.50');
        $this->assertEquals(25000.50, $component->items[0]['cost_price']);

        // Test updating with negative value - clamps to 0
        $component->updateRowCost(0, '-500');
        $this->assertEquals(0, $component->items[0]['cost_price']);

        // Test updating quantity with string "3 pcs"
        $component->updateRowQuantity(0, '3 pcs');
        $this->assertEquals(3, $component->items[0]['quantity']);
    }

    /** @test */
    public function it_tracks_unsaved_items_count_correctly()
    {
        $component = new FastProductCreate;
        $component->mount();

        $this->assertEquals(0, $component->getUnsavedItemsCount());

        $component->quickCost = 1000;
        $component->addItemToBatch();
        $this->assertEquals(1, $component->getUnsavedItemsCount());

        $component->quickCost = 2000;
        $component->addItemToBatch();
        $this->assertEquals(2, $component->getUnsavedItemsCount());

        $component->saveBatch();
        $this->assertEquals(0, $component->getUnsavedItemsCount());
        $this->assertTrue($component->isSaved);

        $component->quickCost = 3000;
        $component->addItemToBatch();
        $this->assertEquals(1, $component->getUnsavedItemsCount());
    }

    /** @test */
    public function it_saves_batch_and_redirects_when_navigating_away()
    {
        $component = new FastProductCreate;
        $component->mount();

        $component->selectedItemName = 'Guard Ring';
        $component->quickCost = 1500;
        $component->addItemToBatch();

        $this->assertEquals(1, $component->getUnsavedItemsCount());

        $targetUrl = route('admin.Productes');
        $response = $component->saveBatchAndRedirect($targetUrl);

        $this->assertEquals(0, $component->getUnsavedItemsCount());
        $this->assertTrue($component->isSaved);
        $this->assertEquals(302, $response->getStatusCode());
        $this->assertEquals($targetUrl, $response->getTargetUrl());

        $this->assertDatabaseHas('product_details', [
            'name' => 'Guard Ring',
        ]);
    }

    /** @test */
    public function it_auto_adds_pending_quick_cost_on_save_and_redirect()
    {
        $component = new FastProductCreate;
        $component->mount();

        $component->selectedItemName = 'Pending Cost Item';
        $component->quickCost = 2500;
        // User didn't click Add, but clicked a menu link -> Save & Leave

        $targetUrl = route('admin.Productes');
        $response = $component->saveBatchAndRedirect($targetUrl);

        $this->assertEquals(0, $component->getUnsavedItemsCount());
        $this->assertTrue($component->isSaved);
        $this->assertEquals(302, $response->getStatusCode());
        $this->assertEquals($targetUrl, $response->getTargetUrl());

        $this->assertDatabaseHas('product_details', [
            'name' => 'Pending Cost Item',
        ]);
    }

    /** @test */
    public function it_supports_custom_arbitrary_decimal_multipliers()
    {
        $component = new FastProductCreate;
        $component->mount();

        // User enters custom multipliers like 2.36 and 5.45
        $component->costMultiplier = '2.36';
        $component->updatedCostMultiplier();
        $component->sellingMultiplier = '5.45';
        $component->updatedSellingMultiplier();

        $this->assertEquals(2.36, $component->costMultiplier);
        $this->assertEquals(5.45, $component->sellingMultiplier);

        // Preview with cost = 1000
        $component->quickCost = 1000;
        $component->updatedQuickCost();

        $this->assertEquals(2360.00, $component->quickSystemCostPreview);
        $this->assertEquals(5450.00, $component->quickSellingPricePreview);

        $component->addItemToBatch();
        $this->assertEquals(2360.00, $component->items[0]['cost_price']);
        $this->assertEquals(5450.00, $component->items[0]['selling_price']);
    }

    /** @test */
    public function it_has_default_cost_1_and_sell_0_and_allows_clearing_to_empty()
    {
        $component = new FastProductCreate;
        $component->mount();

        $this->assertEquals(1, $component->costMultiplier);
        $this->assertEquals(0, $component->sellingMultiplier);

        // Clearing costMultiplier to empty string
        $component->costMultiplier = '';
        $component->updatedCostMultiplier();
        $this->assertSame('', $component->costMultiplier);

        // Clearing sellingMultiplier to empty string
        $component->sellingMultiplier = '';
        $component->updatedSellingMultiplier();
        $this->assertSame('', $component->sellingMultiplier);

        // Typing custom decimal values like 2.36 and 5.45
        $component->costMultiplier = '2.36';
        $component->updatedCostMultiplier();
        $this->assertSame('2.36', $component->costMultiplier);

        $component->sellingMultiplier = '5.45';
        $component->updatedSellingMultiplier();
        $this->assertSame('5.45', $component->sellingMultiplier);
    }

    /** @test */
    public function it_allows_editing_and_updating_saved_product_directly_in_batch()
    {
        $component = new FastProductCreate;
        $component->mount();

        $component->selectedItemName = 'Gold Bangle';
        $component->quickCost = 40000;
        $component->quickQuantity = 2;
        $component->addItemToBatch();

        // Save batch
        $component->saveBatch();
        $this->assertTrue($component->items[0]['is_saved']);
        $savedId = $component->items[0]['saved_product_id'];
        $this->assertNotNull($savedId);

        // Start editing saved item (index 0)
        $component->startEditingSaved(0);
        $this->assertEquals(0, $component->editingIndex);
        $this->assertEquals('Gold Bangle', $component->editName);
        $this->assertEquals(2, $component->editQuantity);

        // User changes quantity from 2 to 7, and updates price
        $component->editQuantity = 7;
        $component->editSellingPrice = 55000;
        $component->saveEditedProduct();

        // Verify edit mode exited
        $this->assertNull($component->editingIndex);
        $this->assertEquals(7, $component->items[0]['quantity']);
        $this->assertEquals(55000, $component->items[0]['selling_price']);

        // Verify database updated directly without going to products page
        $this->assertDatabaseHas('product_stocks', [
            'product_id' => $savedId,
            'available_stock' => 7,
            'total_stock' => 7,
        ]);

        $this->assertDatabaseHas('product_prices', [
            'product_id' => $savedId,
            'selling_price' => 55000,
        ]);
    }

    /** @test */
    public function it_can_search_and_filter_batch_items_by_name_code_and_barcode()
    {
        $component = new FastProductCreate;
        $component->mount();

        $component->selectedItemName = 'Earring 22K';
        $component->quickCost = 15000;
        $component->addItemToBatch();

        $component->selectedItemName = 'Nose Ring 18K';
        $component->quickCost = 8000;
        $component->addItemToBatch();

        $this->assertCount(2, $component->getFilteredItems());

        // Search for "Earring"
        $component->searchQuery = 'earring';
        $filtered = $component->getFilteredItems();
        $this->assertCount(1, $filtered);
        $this->assertEquals('Earring 22K', $filtered[0]['name']);

        // Clear search
        $component->searchQuery = '';
        $this->assertCount(2, $component->getFilteredItems());
    }

    /** @test */
    public function it_can_load_existing_inventory_product_into_batch_for_editing()
    {
        $component = new FastProductCreate;
        $component->mount();

        // Create an existing product in inventory
        $product = ProductDetail::create([
            'code' => 'TEST-INV-001',
            'name' => 'Existing Silver Anklet',
            'barcode' => '8901234567890',
            'status' => 'active',
            'brand_id' => $component->defaultBrandId,
            'category_id' => $component->defaultCategoryId,
            'supplier_id' => $component->defaultSupplierId,
        ]);

        ProductStock::create([
            'product_id' => $product->id,
            'available_stock' => 12,
            'total_stock' => 12,
        ]);

        ProductPrice::create([
            'product_id' => $product->id,
            'supplier_price' => 15000,
            'selling_price' => 22000,
            'retail_price' => 22000,
            'wholesale_price' => 22000,
        ]);

        // Search for it
        $component->searchQuery = 'Silver Anklet';
        $searchResults = $component->inventorySearchResults;
        $this->assertNotEmpty($searchResults);
        $this->assertEquals('Existing Silver Anklet', $searchResults[0]['name']);

        // Load into batch
        $component->loadProductIntoBatch($product->id);

        // Verify loaded at index 0 and immediately in edit mode
        $this->assertEquals(0, $component->editingIndex);
        $this->assertEquals('Existing Silver Anklet', $component->items[0]['name']);
        $this->assertEquals(12, $component->items[0]['quantity']);

        // Edit quantity to 20 and save
        $component->editQuantity = 20;
        $component->saveEditedProduct();

        $this->assertDatabaseHas('product_stocks', [
            'product_id' => $product->id,
            'available_stock' => 20,
        ]);
    }

    /** @test */
    public function it_can_import_raw_tax_invoice_1_excel_with_particulars_and_rate()
    {
        $component = new FastProductCreate;
        $component->mount();

        $filePath = base_path('JAF_GOLD_INVOICE_6496.xlsx');
        if (! file_exists($filePath)) {
            $this->markTestSkipped('Sample file JAF_GOLD_INVOICE_6496.xlsx is not present.');
        }

        $file = new \Illuminate\Http\UploadedFile(
            $filePath,
            'JAF_GOLD_INVOICE_6496.xlsx',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            null,
            true
        );

        $component->excelFile = $file;
        $component->importExcel();

        $this->assertEmpty($component->errorMessage);
        $this->assertCount(12, $component->items);

        // First item verification
        $this->assertEquals('Fm Neck Z310', $component->items[0]['name']);
        $this->assertEquals(370.00, $component->items[0]['entered_cost']);
        $this->assertEquals(11, $component->items[0]['quantity']);
        $this->assertEquals('71171990', $component->items[0]['barcode']);

        // Last item verification
        $lastItem = end($component->items);
        $this->assertEquals('20 Ml Casting Ch', $lastItem['name']);
        $this->assertEquals(440.00, $lastItem['entered_cost']);
        $this->assertEquals(3, $lastItem['quantity']);
    }

    /** @test */
    public function it_can_import_raw_tax_invoice_2_excel_and_skips_summary_rows()
    {
        $component = new FastProductCreate;
        $component->mount();

        $filePath = base_path('JAF_GOLD_INVOICE_6497.xlsx');
        if (! file_exists($filePath)) {
            $this->markTestSkipped('Sample file JAF_GOLD_INVOICE_6497.xlsx is not present.');
        }

        $file = new \Illuminate\Http\UploadedFile(
            $filePath,
            'JAF_GOLD_INVOICE_6497.xlsx',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            null,
            true
        );

        $component->excelFile = $file;
        $component->importExcel();

        $this->assertEmpty($component->errorMessage);
        $this->assertCount(26, $component->items);

        // Verify corrected items 18 81 and 18 84
        $this->assertEquals('18 81', $component->items[9]['name']);
        $this->assertEquals(115.00, $component->items[9]['entered_cost']);
        $this->assertEquals(12, $component->items[9]['quantity']);

        $this->assertEquals('18 84', $component->items[13]['name']);
        $this->assertEquals(120.00, $component->items[13]['entered_cost']);
        $this->assertEquals(12, $component->items[13]['quantity']);

        // Verify last item
        $lastItem = end($component->items);
        $this->assertEquals('24 1424 Sp', $lastItem['name']);
        $this->assertEquals(260.00, $lastItem['entered_cost']);
        $this->assertEquals(19, $lastItem['quantity']);
    }

    /** @test */
    public function it_can_download_excel_template()
    {
        $component = new FastProductCreate;
        $response = $component->downloadExcelTemplate();

        $this->assertNotNull($response);
        $this->assertTrue(
            str_contains($response->headers->get('content-type', ''), 'spreadsheetml.sheet') ||
            str_contains($response->headers->get('content-disposition', ''), 'fast_product_import_template.xlsx')
        );
    }

    /** @test */
    public function it_sets_batch_supplier_when_importing_with_selected_supplier()
    {
        $supplier = \App\Models\ProductSupplier::firstOrCreate(
            ['name' => 'Thara Gold'],
            ['phone' => '0761265772', 'status' => 'active']
        );

        $component = new FastProductCreate;
        $component->mount();
        $component->importSupplierId = $supplier->id;

        $filePath = base_path('JAF_GOLD_INVOICE_6496.xlsx');
        if (! file_exists($filePath)) {
            $this->markTestSkipped('Sample file JAF_GOLD_INVOICE_6496.xlsx is not present.');
        }

        $file = new \Illuminate\Http\UploadedFile(
            $filePath,
            'JAF_GOLD_INVOICE_6496.xlsx',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            null,
            true
        );

        $component->excelFile = $file;
        $component->importExcel();

        $this->assertEquals($supplier->id, $component->selectedSupplierId);
        $this->assertCount(12, $component->items);
    }

    /** @test */
    public function it_can_get_presets_and_download_supplier_specific_templates()
    {
        $thara = \App\Models\ProductSupplier::firstOrCreate(
            ['name' => 'Thara Gold'],
            ['phone' => '0761265772', 'status' => 'active']
        );
        $selva = \App\Models\ProductSupplier::firstOrCreate(
            ['name' => 'SELVA'],
            ['phone' => '0761265773', 'status' => 'active']
        );
        $mr = \App\Models\ProductSupplier::firstOrCreate(
            ['name' => 'MR'],
            ['phone' => '0761265774', 'status' => 'active']
        );

        $component = new FastProductCreate;
        $component->mount();

        $presetTG = $component->getSupplierPreset($thara->id);
        $this->assertEquals('thara_gold', $presetTG['type']);
        $this->assertEquals('fast_product_template_thara_gold.xlsx', $presetTG['template_file']);

        $presetSelva = $component->getSupplierPreset($selva->id);
        $this->assertEquals('selva', $presetSelva['type']);
        $this->assertEquals('fast_product_template_selva.xlsx', $presetSelva['template_file']);

        $presetMR = $component->getSupplierPreset($mr->id);
        $this->assertEquals('mr', $presetMR['type']);
        $this->assertEquals('fast_product_template_mr.xlsx', $presetMR['template_file']);

        // Download supplier template
        $response = $component->downloadSupplierTemplate($thara->id);
        $this->assertNotNull($response);
        $this->assertTrue(str_contains($response->headers->get('content-disposition', ''), 'fast_product_template_thara_gold.xlsx'));
    }

    /** @test */
    public function it_can_import_ramesh_invoice_with_empty_barcodes()
    {
        $ramesh = \App\Models\ProductSupplier::firstOrCreate(
            ['name' => 'RAMESH'],
            ['phone' => '0761265775', 'status' => 'active']
        );

        $component = new FastProductCreate;
        $component->mount();
        $component->importSupplierId = $ramesh->id;

        $filePath = base_path('Continuous_Invoice_Data_v3.xlsx');
        if (! file_exists($filePath)) {
            $this->markTestSkipped('Sample file Continuous_Invoice_Data_v3.xlsx is not present.');
        }

        $file = new \Illuminate\Http\UploadedFile(
            $filePath,
            'Continuous_Invoice_Data_v3.xlsx',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            null,
            true
        );

        $component->excelFile = $file;
        $component->importExcel();

        $this->assertEmpty($component->errorMessage);
        $this->assertEquals($ramesh->id, $component->selectedSupplierId);
        $this->assertCount(62, $component->items);

        // First item
        $this->assertEquals('pendant', $component->items[0]['name']);
        $this->assertEquals(300.00, $component->items[0]['entered_cost']);
        $this->assertEquals(12, $component->items[0]['quantity']);
        $this->assertNull($component->items[0]['barcode']);

        // Verify all barcodes are null (empty) as requested
        $barcodes = array_filter(array_column($component->items, 'barcode'));
        $this->assertCount(0, $barcodes);

        // Total quantity verification
        $this->assertEquals(1318, array_sum(array_column($component->items, 'quantity')));

        // Check RAMESH preset
        $preset = $component->getSupplierPreset($ramesh->id);
        $this->assertEquals('ramesh', $preset['type']);
        $this->assertEquals('fast_product_template_ramesh.xlsx', $preset['template_file']);
    }

    /** @test */
    public function it_auto_enables_size_field_for_bangle_category()
    {
        $component = new FastProductCreate;
        $component->mount();

        $this->assertFalse($component->showSizeField);

        $bangleCat = CategoryList::create(['category_name' => 'Ladies Bangles 22K']);
        $component->updatedSelectedCategoryId($bangleCat->id);

        $this->assertTrue($component->showSizeField);
        $this->assertTrue($component->hasSizeColumn);

        // Switch to non-bangle category
        $ringCat = CategoryList::create(['category_name' => 'Diamond Rings']);
        $component->updatedSelectedCategoryId($ringCat->id);

        $this->assertFalse($component->showSizeField);
    }

    /** @test */
    public function it_maintains_sticky_size_across_rapid_entry_items_until_changed()
    {
        $component = new FastProductCreate;
        $component->mount();

        $component->selectedItemName = 'Gold Bangle';
        $component->quickCost = 150000;
        $component->quickQuantity = 2;
        $component->quickSize = '2.2';

        // Add 1st item
        $component->addItemToBatch();

        $this->assertCount(1, $component->items);
        $this->assertEquals('Gold Bangle', $component->items[0]['name']);
        $this->assertEquals('2.2', $component->items[0]['size']);
        $this->assertEquals('2.2', $component->quickSize, 'quickSize should remain sticky after adding product');

        // Add 2nd item without re-entering size
        $component->quickCost = 160000;
        $component->quickQuantity = 1;
        $component->addItemToBatch();

        $this->assertCount(2, $component->items);
        $this->assertEquals('2.2', $component->items[0]['size'], '2nd item should inherit sticky size 2.2');
        $this->assertEquals('2.2', $component->quickSize);

        // Now user changes size to 2.4
        $component->quickCost = 170000;
        $component->quickQuantity = 3;
        $component->quickSize = '2.4';
        $component->addItemToBatch();

        $this->assertCount(3, $component->items);
        $this->assertEquals('2.4', $component->items[0]['size'], '3rd item should have updated size 2.4');
        $this->assertEquals('2.4', $component->quickSize);
    }

    /** @test */
    public function it_saves_batch_products_with_size_to_database()
    {
        $component = new FastProductCreate;
        $component->mount();

        $component->selectedItemName = 'Bangles Spec';
        $component->quickCost = 100000;
        $component->quickQuantity = 1;
        $component->quickSize = '2.6';
        $component->addItemToBatch();

        $component->saveBatch();

        $this->assertTrue($component->isSaved);
        $savedProduct = ProductDetail::where('name', 'Bangles Spec')->latest()->first();
        $this->assertNotNull($savedProduct);
        $this->assertEquals('2.6', $savedProduct->size);
    }

    /** @test */
    public function it_can_update_row_size_and_edit_saved_product_size()
    {
        $component = new FastProductCreate;
        $component->mount();

        $component->selectedItemName = 'Bangle Row Edit';
        $component->quickCost = 120000;
        $component->quickQuantity = 1;
        $component->quickSize = '2.2';
        $component->addItemToBatch();

        // Inline edit draft row size
        $component->updateRowSize(0, '2.4');
        $this->assertEquals('2.4', $component->items[0]['size']);

        // Save batch
        $component->saveBatch();
        $this->assertTrue($component->items[0]['is_saved']);

        // Start editing saved item
        $component->startEditingSaved(0);
        $this->assertEquals('2.4', $component->editSize);

        // Change size to 2.8 and save
        $component->editSize = '2.8';
        $component->saveEditedProduct();

        $this->assertEquals('2.8', $component->items[0]['size']);

        $dbProduct = ProductDetail::find($component->items[0]['saved_product_id']);
        $this->assertNotNull($dbProduct);
        $this->assertEquals('2.8', $dbProduct->size);
    }

    /** @test */
    public function it_retrieves_size_in_barcode_print_product_search()
    {
        $brand = BrandList::firstOrCreate(['brand_name' => 'Test Brand']);
        $cat = CategoryList::firstOrCreate(['category_name' => 'Bangles']);

        $product = ProductDetail::create([
            'brand_id' => $brand->id,
            'category_id' => $cat->id,
            'code' => 'TEST-SZ-001',
            'name' => 'Bangle Test With Size',
            'size' => '2.4',
            'barcode' => '7788990011',
            'barcode_printed' => 'No',
        ]);

        $barcodeComponent = new \App\Livewire\Admin\BarcodePrint;
        $barcodeComponent->lookupCode = '7788990011';
        $barcodeComponent->searchProduct();

        $this->assertNotNull($barcodeComponent->lookupProduct);
        $this->assertEquals('2.4', $barcodeComponent->lookupProduct['size']);
        $this->assertEquals('7788990011', $barcodeComponent->lookupProduct['barcode']);
    }
}
