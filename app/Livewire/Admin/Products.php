<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use App\Models\ProductDetail;
use App\Models\ProductPrice;
use App\Models\ProductStock;
use App\Models\ProductVariant;
use App\Models\BrandList;
use App\Models\CategoryList;
use App\Models\ProductSupplier;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Quotation;
use App\Models\ReturnsProduct;
use App\Models\StaffProduct;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use App\Imports\ProductsImportWithVariants;
use App\Exports\ProductsImportTemplateExport;
use Maatwebsite\Excel\Facades\Excel;
use App\Http\Controllers\ProductApiController;
use App\Models\ProductBatch;
use Illuminate\Support\Facades\DB;
use App\Livewire\Concerns\WithDynamicLayout;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

#[Title("Product List")]
class Products extends Component
{
    use WithDynamicLayout;
    use WithPagination, WithFileUploads;

    public $search = '';

    // Create form fields
    public $code, $name, $model, $brand, $category, $image, $description, $barcode, $status, $supplier, $website_live = false;
    public $supplier_price, $retail_price, $wholesale_price, $discount_price, $available_stock, $damage_stock;

    // Barcode lookup mode: 'create' or 'edit'
    public $barcodeMode = 'create';
    public $barcodeLookupProductId = null;

    // Track if barcode was auto-generated (needs printing) or manually entered
    public $barcodeGenerated = false;

    // Purchase date for auto-creating purchase records
    public $purchaseDate;

    // Pricing mode: 'single' or 'variant'
    public $pricing_mode = 'single';

    // Variant configuration
    public $variant_id = null; // Selected variant for this product

    // Variant prices - each value has its own price set
    public $variant_prices = []; // e.g., ['v_4' => ['supplier' => 100, 'retail' => 200...], 'v_6' => [...]]

    // Map sanitized variant key => original display value (e.g. 'v_4_5' => '4.5')
    public $variant_key_map = []; // used to display original labels when keys are sanitized

    // Available variants to select from
    public $availableVariants = [];

    // Import file
    public $importFile;

    // Edit form fields
    public $editId, $editCode, $editName, $editModel, $editBrand, $editCategory, $editImage, $existingImage,
        $editDescription, $editBarcode, $editStatus, $editSupplier, $editSupplierPrice, $editRetailPrice, $editWholesalePrice,
        $editDiscountPrice, $editDamageStock, $editAvailableStock, $editWebsiteLive;

    // Track original pricing mode when opening edit modal so we don't accidentally delete variant rows
    public $original_pricing_mode = 'single';

    // Stock Adjustment fields
    public $adjustmentProductId, $adjustmentProductName, $adjustmentAvailableStock, $adjustmentDamageStock,
        $damageQuantity, $availableQuantity;

    // View Product
    public $viewProduct;

    // History fields
    public $historyProductId, $historyProductName, $historyTab = 'sales';
    public $salesHistory = [], $purchasesHistory = [], $returnsHistory = [], $quotationsHistory = [];

    // Default IDs for brand, category, and supplier
    public $defaultBrandId, $defaultCategoryId, $defaultSupplierId;
    public $perPage = 10;

    public function mount()
    {
        $this->setDefaultIds();
        $this->setDefaultValues();
        $this->loadAvailableVariants();
    }

    /**
     * Load available variants from database
     */
    public function loadAvailableVariants()
    {
        $this->availableVariants = ProductVariant::where('status', 'active')->get()->toArray();
    }

    /**
     * Handle variant selection change
     */
    public function updatedVariantId($value)
    {
        // Keep lifecycle behavior (when Livewire sets the property)
        $this->initializeVariant($value);
    }

    /**
     * Public wrapper to be called from the template when a user selects a variant.
     * Avoids calling a lifecycle hook directly from Blade (Livewire restriction).
     */
    public function selectVariant($value)
    {
        // Ensure the property is updated so other lifecycle hooks / bindings remain consistent
        $this->variant_id = $value;

        // Delegate to shared initializer
        $this->initializeVariant($value);
    }

    /**
     * Initialize variant state (used by lifecycle and wrapper).
     */
    private function initializeVariant($value)
    {
        if (empty($value)) {
            $this->variant_prices = [];
            $this->variant_key_map = [];
            return;
        }

        // Load selected variant
        $variant = ProductVariant::find($value);

        // Debug: log variant selection for troubleshooting
        Log::info('initializeVariant called', [
            'variant_id' => $value,
            'found_variant' => $variant ? true : false,
            'variant_values' => $variant ? $variant->variant_values : null,
        ]);

        if ($variant) {
            // Get variant values and sort them (numeric when possible)
            $values = is_array($variant->variant_values) ? $variant->variant_values : [];
            $sorted = $this->sortVariantValues($values);

            // Initialize prices for selected variant values in sorted order
            $this->variant_prices = [];
            $this->variant_key_map = [];
            foreach ($sorted as $val) {
                $key = $this->sanitizeVariantKey($val);
                $this->variant_key_map[$key] = (string) $val;
                $this->variant_prices[$key] = [
                    'supplier_price' => 0,
                    'retail_price' => 0,
                    'wholesale_price' => 0,
                    'stock' => 0,
                ];
            }
        } else {
            $this->variant_prices = [];
            $this->variant_key_map = [];
        }
    }

    /**
     * Reset component state when pagination changes
     * This fixes the issue where wrong product shows in modal on different pages
     */
    public function updatingSearch()
    {
        $this->resetPage();
    }

    /**
     * Set default IDs for brand, category, and supplier
     */
    private function setDefaultIds()
    {
        // Get or create default brand
        $defaultBrand = BrandList::where('brand_name', 'Default Brand')->first();
        if (!$defaultBrand) {
            $defaultBrand = BrandList::create([
                'brand_name' => 'Default Brand',
                'status' => 'active'
            ]);
        }
        $this->defaultBrandId = $defaultBrand->id;

        // Get or create default category
        $defaultCategory = CategoryList::where('category_name', 'Default Category')->first();
        if (!$defaultCategory) {
            $defaultCategory = CategoryList::create([
                'category_name' => 'Default Category',
                'status' => 'active'
            ]);
        }
        $this->defaultCategoryId = $defaultCategory->id;

        // Get or create default supplier
        $defaultSupplier = ProductSupplier::where('name', 'Default Supplier')->first();
        if (!$defaultSupplier) {
            $defaultSupplier = ProductSupplier::create([
                'name' => 'Default Supplier',
                'phone' => '0000000000',
                'email' => 'default@supplier.com',
                'address' => 'Default Address',
                'status' => 'active'
            ]);
        }
        $this->defaultSupplierId = $defaultSupplier->id;
    }

    /**
     * Set default values for brand, category, and supplier
     */
    private function setDefaultValues()
    {
        // Set default brand
        $this->brand = $this->defaultBrandId;

        // Set default category
        $this->category = $this->defaultCategoryId;

        // Set default supplier
        $this->supplier = $this->defaultSupplierId;

        // Set default status
        $this->status = 'active';

        // Set default stock values (null = empty input, placeholder shows 0)
        $this->available_stock = null;
        $this->damage_stock = null;

        // Set default prices (null = empty input, placeholder shows 0)
        $this->supplier_price = null;
        $this->retail_price = null;
        $this->wholesale_price = null;
        $this->discount_price = null;

        // Set default purchase date - use session-stored date if available, otherwise today
        $this->purchaseDate = Session::get('product_purchase_date', date('Y-m-d'));
    }

    /**
     * Listen for purchase date changes and persist to session
     */
    public function updatedPurchaseDate($value)
    {
        Session::put('product_purchase_date', $value);
    }

    /**
     * Sort variant values for consistent display order.
     * Numeric-like values are sorted numerically (4, 4.5, 6), otherwise natural case-insensitive order.
     *
     * @param array $values
     * @return array
     */
    private function sortVariantValues(array $values): array
    {
        // Preserve the order as stored in the database / model.
        // Previously this function performed numeric or natural sorting; per request we
        // must use the DB-stored order for display and editing.
        return array_values($values);
    }

    /**
     * Convert a variant display value to a sanitized key safe for property access and Livewire binding.
     * Examples: '4.5' => 'v_4_5', 'Small/Medium' => 'v_Small_Medium'
     *
     * @param mixed $value
     * @return string
     */
    private function sanitizeVariantKey($value): string
    {
        $str = (string) $value;
        // replace non-alphanumeric with underscore
        // Use a stable hash to ensure uniqueness even when the display value
        // contains non-alphanumeric characters (e.g. quotes, +, -) which
        // would otherwise collapse to identical sanitized strings.
        // Keep a short readable suffix derived from the sanitized label for
        // easier debugging but rely on the hash for uniqueness.
        $sanitized = preg_replace('/[^A-Za-z0-9]+/', '_', $str);
        $hash = substr(md5($str), 0, 8);
        return 'v_' . $hash . '_' . trim($sanitized, '_');
    }

    public function render()
    {
        $brands = BrandList::orderBy('brand_name')->get();
        $categories = CategoryList::orderBy('category_name')->get();
        $suppliers = ProductSupplier::orderBy('name')->get();

        // For staff, show all products (same as admin but read-only access)
        if ($this->isStaff()) {
            $products = ProductDetail::leftJoin('brand_lists', 'product_details.brand_id', '=', 'brand_lists.id')
                ->leftJoin('category_lists', 'product_details.category_id', '=', 'category_lists.id')
                ->leftJoin('product_stocks', 'product_details.id', '=', 'product_stocks.product_id')
                ->leftJoin('product_prices', function ($join) {
                    $join->on('product_details.id', '=', 'product_prices.product_id')
                        ->where('product_prices.pricing_mode', '=', 'single')
                        ->whereNull('product_prices.variant_id');
                })
                ->select(
                    'product_details.id',
                    'product_details.code',
                    'product_details.name as product_name',
                    'product_details.model',
                    'product_details.image',
                    'product_details.description',
                    'product_details.barcode',
                    'product_details.status',
                    'product_details.website_live',
                    DB::raw('COALESCE(product_prices.supplier_price, 0) as supplier_price'),
                    DB::raw('COALESCE(product_prices.wholesale_price, 0) as wholesale_price'),
                    DB::raw('COALESCE(product_prices.distributor_price, 0) as distributor_price'),
                    DB::raw('COALESCE(product_prices.retail_price, 0) as retail_price'),
                    DB::raw('COALESCE(product_prices.discount_price, 0) as discount_price'),
                    DB::raw('SUM(product_stocks.available_stock) as available_stock'),
                    DB::raw('SUM(product_stocks.damage_stock) as damage_stock'),
                    DB::raw('SUM(product_stocks.total_stock) as total_stock'),
                    'brand_lists.brand_name as brand',
                    'category_lists.category_name as category'
                )
                ->where(function ($query) {
                    $query->where('product_details.name', 'like', '%' . $this->search . '%')
                        ->orWhere('product_details.code', 'like', '%' . $this->search . '%')
                        ->orWhere('product_details.model', 'like', '%' . $this->search . '%')
                        ->orWhere('brand_lists.brand_name', 'like', '%' . $this->search . '%')
                        ->orWhere('category_lists.category_name', 'like', '%' . $this->search . '%')
                        ->orWhere('product_details.status', 'like', '%' . $this->search . '%')
                        ->orWhere('product_details.barcode', 'like', '%' . $this->search . '%');
                })
                ->groupBy(
                    'product_details.id',
                    'product_details.code',
                    'product_details.name',
                    'product_details.model',
                    'product_details.image',
                    'product_details.description',
                    'product_details.barcode',
                    'product_details.status',
                    'product_details.website_live',
                    'product_prices.supplier_price',
                    'product_prices.wholesale_price',
                    'product_prices.distributor_price',
                    'product_prices.retail_price',
                    'product_prices.discount_price',
                    'brand_lists.brand_name',
                    'category_lists.category_name'
                )
                ->orderBy('product_details.id', 'desc')
                ->paginate($this->perPage);
        } else {
            // Admin sees all products - group by product to avoid duplicates from variants
            $products = ProductDetail::leftJoin('brand_lists', 'product_details.brand_id', '=', 'brand_lists.id')
                ->leftJoin('category_lists', 'product_details.category_id', '=', 'category_lists.id')
                ->leftJoin('product_stocks', 'product_details.id', '=', 'product_stocks.product_id')
                ->leftJoin('product_prices', function ($join) {
                    $join->on('product_details.id', '=', 'product_prices.product_id')
                        ->where('product_prices.pricing_mode', '=', 'single')
                        ->whereNull('product_prices.variant_id');
                })
                ->select(
                    'product_details.id',
                    'product_details.code',
                    'product_details.name as product_name',
                    'product_details.model',
                    'product_details.image',
                    'product_details.description',
                    'product_details.barcode',
                    'product_details.status',
                    'product_details.website_live',
                    DB::raw('COALESCE(product_prices.supplier_price, 0) as supplier_price'),
                    DB::raw('COALESCE(product_prices.wholesale_price, 0) as wholesale_price'),
                    DB::raw('COALESCE(product_prices.distributor_price, 0) as distributor_price'),
                    DB::raw('COALESCE(product_prices.retail_price, 0) as retail_price'),
                    DB::raw('COALESCE(product_prices.discount_price, 0) as discount_price'),
                    DB::raw('SUM(product_stocks.available_stock) as available_stock'),
                    DB::raw('SUM(product_stocks.damage_stock) as damage_stock'),
                    DB::raw('SUM(product_stocks.total_stock) as total_stock'),
                    'brand_lists.brand_name as brand',
                    'category_lists.category_name as category'
                )
                ->where(function ($query) {
                    $query->where('product_details.name', 'like', '%' . $this->search . '%')
                        ->orWhere('product_details.code', 'like', '%' . $this->search . '%')
                        ->orWhere('product_details.model', 'like', '%' . $this->search . '%')
                        ->orWhere('brand_lists.brand_name', 'like', '%' . $this->search . '%')
                        ->orWhere('category_lists.category_name', 'like', '%' . $this->search . '%')
                        ->orWhere('product_details.status', 'like', '%' . $this->search . '%')
                        ->orWhere('product_details.barcode', 'like', '%' . $this->search . '%');
                })
                ->groupBy(
                    'product_details.id',
                    'product_details.code',
                    'product_details.name',
                    'product_details.model',
                    'product_details.image',
                    'product_details.description',
                    'product_details.barcode',
                    'product_details.status',
                    'product_details.website_live',
                    'product_prices.supplier_price',
                    'product_prices.wholesale_price',
                    'product_prices.distributor_price',
                    'product_prices.retail_price',
                    'product_prices.discount_price',
                    'brand_lists.brand_name',
                    'category_lists.category_name'
                )
                ->orderBy('product_details.id', 'desc')
                ->paginate($this->perPage);
        }

        return view('livewire.admin.Productes', [
            'products' => $products,
            'brands' => $brands,
            'categories' => $categories,
            'suppliers' => $suppliers,
            'isStaff' => $this->isStaff(),
            'staffType' => Auth::user()->staff_type ?? null,
        ])->layout($this->layout);
    }

    public function updatedPerPage()
    {
        $this->resetPage();
    }

    public function toggleStatus($id)
    {
        $product = ProductDetail::find($id);
        if ($product) {
            $newStatus = $product->status === 'active' ? 'inactive' : 'active';
            $product->status = $newStatus;

            // Website Live must never remain ON when the product is no longer Live.
            // When status is turned OFF, automatically turn Website Live OFF too.
            // (When status is turned ON, Website Live is left untouched — it can only be re-enabled once the admin explicitly turns it on.
            if ($newStatus !== 'active' && $product->website_live) {
                $product->website_live = false;
            }

            $product->save();

            // Clear API cache
            ProductApiController::clearCache();

            // Do not re-render the whole table. Toggling a switch must never
            $this->skipRender();

            $this->dispatch('showToast', message: 'Product website status updated.');
            $this->dispatch('product-status-updated', productId: $product->id, status: $product->status);

            // If the status change automatically turned Website Live off, sync the switch.
            $this->dispatch('product-website-live-updated', productId: $product->id, websiteLive: $product->website_live);
        }
    }

        /**
     * Collect website-visibility issues based on the three raw conditions.
     * Returns an array of human-readable issue strings (empty when all OK).
     */
    private function getWebsiteVisibilityIssuesFromValues($hasImage, $status, $availableStock)
    {
        $issues = [];

        if (!$hasImage) {
            $issues[] = 'Add a product image.';
        }

        if ($status !== 'active') {
            $issues[] = 'Change the product status to Live.';
        }

        if ((int) ($availableStock ?? 0) <= 0) {
            $issues[] = 'Add stock greater than 0.';
        }

        return $issues;
    }

    /**
     * Collect website-visibility issues for a ProductDetail model.
     */
    private function getWebsiteVisibilityIssues($product)
    {
        // A real product image is required. The accessor returns the default
        // placeholder ('images/product.jpg') for missing images, and the barcode
        // update flow may have persisted it — so we must exclude it explicitly.
        $rawImage = trim((string) ($product->getRawOriginal('image') ?? ''));
        $hasImage = $rawImage !== '' && $rawImage !== 'images/product.jpg';
        $status   = $product->status ?? '';

        $availableStock = 0;
        if ($product->stock) {
            $availableStock = (int) ($product->stock->available_stock ?? 0);
        }

        return $this->getWebsiteVisibilityIssuesFromValues($hasImage, $status, $availableStock);
    }

    /**
     * Check whether a product can be made website live.
     * Returns [bool $canBeLive, array $issues].
     */
    private function checkWebsiteLiveEligibility($requested, $hasImage, $status, $availableStock)
    {
        if (!$requested) {
            return [false, []];
        }

        $issues = $this->getWebsiteVisibilityIssuesFromValues($hasImage, $status, $availableStock);

        if (empty($issues)) {
            return [true, []];
        }

        return [false, $issues];
    }

    /**
     * Build a Swal HTML string from a list of website-visibility issues.
     */
    private function buildVisibilityWarningHtml(array $issues)
    {
        $html  = '<p class="mb-2"><strong>This product cannot be made live.</strong></p>';
        $html .= '<p class="mb-2" style="color:#f39c12">The following requirements must be satisfied before Website Live can be turned on:</p>';
        $html .= '<ul class="mb-0" style="text-align:left">';
        foreach ($issues as $issue) {
            $html .= '<li>' . htmlspecialchars($issue, ENT_QUOTES) . '</li>';
        }
        $html .= '</ul>';

        return $html;
    }

    /**
     * Show a Swal warning dialog listing why Website Live could not be enabled.
     */
    private function showWebsiteLiveWarning(array $issues, $title = 'Cannot Make Product Live')
    {
        $html = $this->buildVisibilityWarningHtml($issues);

        $this->js("Swal.fire({icon: 'warning', title: " . json_encode($title) . ", html: " . json_encode($html) . ", confirmButtonText: 'OK', width: 460})");
    }

    public function toggleWebsiteLive($id)
    {
        $product = ProductDetail::with('stock')->find($id);
        if ($product) {
            // Turning OFF is always allowed — the administrator may hide a product from the website at any time without meeting the other conditions.
            // Turning ON requires all conditions to be satisfied.
            if (!$product->website_live) {
                $issues = $this->getWebsiteVisibilityIssues($product);
                if (!empty($issues)) {
                    // Do not change the stored value — the switch reverts visually.
                    $this->dispatch('product-website-live-updated', productId: $product->id, websiteLive: false);
                    $this->skipRender();
                    $this->showWebsiteLiveWarning($issues);
                    return;
                }
            }

            $product->website_live = !$product->website_live;
            $product->save();

            // Clear API cache
            ProductApiController::clearCache();

            // See toggleStatus() for why the table is not re-rendered here.
            $this->skipRender();

            $this->dispatch('showToast', message: 'Product website live status updated.');
            $this->dispatch('product-website-live-updated', productId: $product->id, websiteLive: $product->website_live);
        }
    }

    // 🔹 Validation Rules for Create
    protected function rules()
    {
        // Enforce single pricing mode only
        $this->pricing_mode = 'single';
        $this->variant_id = null;
        $this->variant_prices = [];
        $this->variant_key_map = [];

        // If in barcode edit mode, adjust unique rules to exclude current product
        $excludeId = $this->barcodeLookupProductId;
        $codeUniqueRule = $excludeId
            ? 'required|string|max:100|unique:product_details,code,' . $excludeId
            : 'required|string|max:100|unique:product_details,code';
        $barcodeUniqueRule = $excludeId
            ? 'nullable|string|max:255|unique:product_details,barcode,' . $excludeId
            : 'nullable|string|max:255|unique:product_details,barcode';

        $rules = [
            'name' => 'required|string|max:255',
            'code' => $codeUniqueRule,
            'model' => 'nullable|string|max:255',
            'brand' => 'required|exists:brand_lists,id',
            'category' => 'required|exists:category_lists,id',
            'supplier' => 'nullable|exists:product_suppliers,id',
            'image' => 'nullable|string|max:10000',
            'description' => 'nullable|string|max:1000',
            'barcode' => $barcodeUniqueRule,
            'pricing_mode' => 'required|in:single',
            'website_live' => 'nullable|boolean',
        ];

        $rules = array_merge($rules, [
            'supplier_price' => 'required|numeric|min:0',
            'retail_price' => 'required|numeric|min:0|gte:supplier_price',
            'wholesale_price' => 'required|numeric|min:0|gte:supplier_price',
            'discount_price' => 'nullable|numeric|min:0|lte:retail_price',
            'available_stock' => 'nullable|integer|min:0',
            'damage_stock' => 'nullable|integer|min:0',
        ]);

        return $rules;
    }

    // 🔹 Validation Messages
    protected function messages()
    {
        return [
            'name.required' => 'Product name is required.',
            'name.max' => 'Product name must not exceed 255 characters.',
            'code.required' => 'Product code is required.',
            'code.unique' => 'This product code already exists.',
            'brand.required' => 'Please select a brand.',
            'brand.exists' => 'Selected brand is invalid.',
            'category.required' => 'Please select a category.',
            'category.exists' => 'Selected category is invalid.',
            'supplier_price.required' => 'Supplier price is required.',
            'supplier_price.numeric' => 'Supplier price must be a number.',
            'supplier_price.min' => 'Supplier price cannot be negative.',
            'retail_price.required' => 'Retail price is required.',
            'retail_price.numeric' => 'Retail price must be a number.',
            'retail_price.min' => 'Retail price cannot be negative.',
            'retail_price.gte' => 'Retail price must be greater than or equal to supplier price.',
            'variant_prices.*.retail_price.gte' => 'Retail price for each variant must be greater than or equal to its supplier price.',
            'wholesale_price.required' => 'Wholesale price is required.',
            'variant_prices.*.wholesale_price.gte' => 'Wholesale price for each variant must be greater than or equal to its supplier price.',
            'wholesale_price.numeric' => 'Wholesale price must be a number.',
            'wholesale_price.min' => 'Wholesale price cannot be negative.',
            'wholesale_price.gte' => 'Wholesale price must be greater than or equal to supplier price.',
            'discount_price.lte' => 'Discount price cannot be greater than retail price.',
            'available_stock.integer' => 'Available stock must be a whole number.',
            'available_stock.min' => 'Available stock cannot be negative.',
            'damage_stock.integer' => 'Damage stock must be a whole number.',
            'damage_stock.min' => 'Damage stock cannot be negative.',
            'image.url' => 'Please provide a valid image URL.',
            'barcode.unique' => 'This barcode already exists.',
        ];
    }

    // 🔹 Validation Attributes
    protected function attributes()
    {
        $attrs = [
            'supplier_price' => 'supplier price',
            'retail_price' => 'retail price',
            'wholesale_price' => 'wholesale price',
            'stock' => 'stock',
            'website_live' => 'website live',
            'editWebsiteLive' => 'website live',
        ];

        // Add dynamic per-variant friendly labels
        foreach ($this->variant_key_map as $k => $label) {
            $attrs["variant_prices.{$k}.supplier_price"] = "Supplier price ({$label})";
            $attrs["variant_prices.{$k}.retail_price"] = "Retail price ({$label})";
            $attrs["variant_prices.{$k}.wholesale_price"] = "Wholesale price ({$label})";
            $attrs["variant_prices.{$k}.stock"] = "Stock ({$label})";
        }

        return $attrs;
    }

    // 🔹 Open Create Modal
    public function openCreateModal()
    {
        $this->resetForm();
        $this->resetValidation();

        // Set default values (like walking customer in sales system)
        $this->setDefaultValues();
        $this->barcodeMode = 'create';
        $this->barcodeLookupProductId = null;

        $this->js("$('#createProductModal').modal('show'); setTimeout(() => { document.getElementById('barcode_input') && document.getElementById('barcode_input').focus(); }, 500);");
    }

    /**
     * Auto-lookup barcode when it changes (debounced from blade).
     * Triggers lookupBarcode automatically without needing a button.
     */
    public function updatedBarcode($value)
    {
        // If barcode is manually typed/scanned, mark as not generated
        $this->barcodeGenerated = false;
        $this->lookupBarcode();
    }

    /**
     * Generate a unique barcode (13-digit EAN-style).
     * Sets barcodeGenerated = true so barcode_printed will be 'No'.
     */
    public function generateBarcode()
    {
        do {
            // Generate a 12-digit number and calculate EAN-13 check digit
            $code = '200' . str_pad(mt_rand(0, 999999999), 9, '0', STR_PAD_LEFT);
            $sum = 0;
            for ($i = 0; $i < 12; $i++) {
                $sum += (int) $code[$i] * ($i % 2 === 0 ? 1 : 3);
            }
            $checkDigit = (10 - ($sum % 10)) % 10;
            $barcode = $code . $checkDigit;
        } while (ProductDetail::where('barcode', $barcode)->exists());

        $this->barcode = $barcode;
        $this->barcodeGenerated = true;
        $this->barcodeMode = 'create';
        $this->barcodeLookupProductId = null;
    }

    /**
     * Lookup product by barcode. If exists, load into form as edit mode.
     * If not, stay in create mode.
     */
    public function lookupBarcode()
    {
        if (empty($this->barcode)) {
            $this->barcodeMode = 'create';
            $this->barcodeLookupProductId = null;
            return;
        }

        $product = ProductDetail::with(['price', 'stock', 'variant', 'prices', 'stocks'])
            ->where('barcode', $this->barcode)
            ->first();

        if ($product) {
            // Product found - switch to edit mode
            $this->barcodeMode = 'edit';
            $this->barcodeLookupProductId = $product->id;

            // Load product data into create form fields
            $this->code = $product->code;
            $this->name = $product->name;
            $this->model = $product->model;
            $this->brand = $product->brand_id;
            $this->category = $product->category_id;
            $this->image = $product->image;
            $this->description = $product->description;
            $this->supplier = $product->supplier_id;
            $this->status = $product->status;

            // Load pricing (prefer single row, fallback to first available price for legacy records)
            $singlePrice = $product->price
                ?? $product->prices->where('pricing_mode', 'single')->first()
                ?? $product->prices->first();

            if ($singlePrice) {
                $this->supplier_price = $singlePrice->supplier_price ?? 0;
                $this->retail_price = $singlePrice->retail_price ?? 0;
                $this->wholesale_price = $singlePrice->wholesale_price ?? 0;
                $this->discount_price = $singlePrice->discount_price ?? 0;
            }

            // Load stock - reset to 0 so user enters NEW quantity to add (will be incremented)
            $this->available_stock = 0;
            $this->damage_stock = 0;

            // Force single mode only
            $this->pricing_mode = 'single';
            $this->variant_id = null;
            $this->variant_prices = [];
            $this->variant_key_map = [];

            $this->js("Swal.fire({icon: 'info', title: 'Product Found', text: 'Barcode matched an existing product. You are now in Update mode.', timer: 2000, showConfirmButton: false})");
        } else {
            // Not found - create mode
            $this->barcodeMode = 'create';
            $this->barcodeLookupProductId = null;
        }
    }

    /**
     * Apply 5X System Cost and 11X Retail Price formula
     */
    public function applyPricingFormula()
    {
        $cost = max(0, floatval($this->supplier_price));
        if ($cost > 0) {
            $this->supplier_price = round($cost * 5, 2);
            $this->retail_price = round($cost * 11, 2);
            $this->wholesale_price = round($cost * 11, 2);
        }
    }

    /**
     * Save product - handles both create and update via barcode lookup
     */
    public function saveProduct()
    {
        if ($this->barcodeMode === 'edit' && $this->barcodeLookupProductId) {
            $this->updateProductFromBarcode();
        } else {
            $this->createProduct();
        }
    }

    /**
     * Update product found via barcode lookup
     */
    private function updateProductFromBarcode()
    {
        try {
            DB::beginTransaction();

            $product = ProductDetail::with('stock')->findOrFail($this->barcodeLookupProductId);

            // ── Website Live visibility check ──
            // A product is visible on the website ONLY when ALL conditions are met:
            //   1. Has a real product image
            //   2. Status is active (Live)
            //   3. Available stock > 0
            $barcodeHasImage = !empty($this->image) && $this->image !== 'images/product.jpg';
            if (!$barcodeHasImage) {
                $barcodeHasImage = !empty(trim((string) ($product->getRawOriginal('image') ?? '')));
            }
            $barcodeStatus    = $this->status ?? 'active';
            $existingStockVal = $product->stock ? (int) ($product->stock->available_stock ?? 0) : 0;
            $barcodeStock     = $existingStockVal + (int) ($this->available_stock ?? 0);
            [$barcodeCanBeLive, $barcodeLiveIssues] = $this->checkWebsiteLiveEligibility(
                (bool) ($this->website_live ?? false), $barcodeHasImage, $barcodeStatus, $barcodeStock
            );
            $barcodeWebsiteLive = $barcodeCanBeLive ? true : false;

            // Update product details
            $product->update([
                'code' => $this->code,
                'name' => $this->name,
                'model' => $this->model,
                'brand_id' => $this->brand,
                'category_id' => $this->category,
                'image' => $this->image ?: $product->image,
                'description' => $this->description,
                'barcode' => $this->barcode,
                'barcode_printed' => $this->barcodeGenerated ? 'No' : ($product->barcode_printed ?? 'Yes'),
                'status' => $this->status ?? 'active',
                'supplier_id' => $this->supplier,
                'website_live' => $barcodeWebsiteLive,
            ]);

            // Force product to single mode and clean variant rows
            $product->update(['variant_id' => null]);
            ProductPrice::where('product_id', $product->id)
                ->where('pricing_mode', 'variant')
                ->delete();
            ProductStock::where('product_id', $product->id)
                ->whereNotNull('variant_id')
                ->delete();

            // Update single pricing
            ProductPrice::updateOrCreate(
                [
                    'product_id' => $product->id,
                    'pricing_mode' => 'single',
                    'variant_id' => null,
                    'variant_value' => null,
                ],
                [
                    'supplier_price' => $this->supplier_price ?? 0,
                    'selling_price' => $this->retail_price ?? 0,
                    'retail_price' => $this->retail_price ?? 0,
                    'wholesale_price' => $this->wholesale_price ?? 0,
                    'distributor_price' => null,
                    'discount_price' => $this->discount_price ?? 0,
                ]
            );

            // Update stock - INCREMENT existing stock instead of replacing
            $existingStock = ProductStock::where('product_id', $product->id)
                ->whereNull('variant_id')
                ->whereNull('variant_value')
                ->first();

            if ($existingStock) {
                $newAvailable = ($existingStock->available_stock ?? 0) + ($this->available_stock ?? 0);
                $newDamage = ($existingStock->damage_stock ?? 0) + ($this->damage_stock ?? 0);
                $existingStock->update([
                    'available_stock' => $newAvailable,
                    'damage_stock' => $newDamage,
                    'total_stock' => $newAvailable + $newDamage,
                ]);
            } else {
                ProductStock::create([
                    'product_id' => $product->id,
                    'variant_id' => null,
                    'variant_value' => null,
                    'available_stock' => $this->available_stock ?? 0,
                    'damage_stock' => $this->damage_stock ?? 0,
                    'total_stock' => ($this->available_stock ?? 0) + ($this->damage_stock ?? 0),
                    'sold_count' => 0,
                    'restocked_quantity' => 0,
                ]);
            }

            // ── Auto-create Purchase Order + Items ──
            $this->createPurchaseRecord($product);

            DB::commit();

            $this->resetForm();
            $this->js("$('#createProductModal').modal('hide')");

            if (($this->website_live ?? false) && !$barcodeCanBeLive) {
                $warningHtml = $this->buildVisibilityWarningHtml($barcodeLiveIssues);
                $warningHtml = '<p class="mb-2">Product updated successfully!</p>' . $warningHtml;
                $this->js("Swal.fire({icon: 'warning', title: 'Product Updated', html: " . json_encode($warningHtml) . ", confirmButtonText: 'OK', width: 460})");
            } else {
                $this->js("Swal.fire('Success!', 'Product updated successfully!', 'success')");
            }

            ProductApiController::clearCache();
            $this->dispatch('refreshPage');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Product update via barcode failed: ' . $e->getMessage());
            $this->js("Swal.fire('Error!', 'Failed to update product: " . addslashes($e->getMessage()) . "', 'error')");
        }
    }

    /**
     * Auto-create a PurchaseOrder and PurchaseOrderItems when saving a product.
     * Status = 'received', due_amount = 0 (Cash Paid).
     */
    private function createPurchaseRecord($product)
    {
        $orderDate = $this->purchaseDate ?: date('Y-m-d');
        $supplierId = $this->supplier ?? $this->defaultSupplierId;

        // Generate order code: PO-YYYYMMDD-XXXXX
        $orderCode = 'PO-' . date('Ymd', strtotime($orderDate)) . '-' . strtoupper(\Illuminate\Support\Str::random(5));

        $totalAmount = 0;

        // Single mode only
        $qty = $this->available_stock ?? 0;
        $unitPrice = $this->supplier_price ?? 0;
        $totalAmount = $qty * $unitPrice;

        // Create the purchase order
        $purchaseOrder = PurchaseOrder::create([
            'order_code' => $orderCode,
            'supplier_id' => $supplierId,
            'order_date' => $orderDate,
            'received_date' => $orderDate,
            'status' => 'received',
            'total_amount' => $totalAmount,
            'due_amount' => 0, // Cash Paid
        ]);

        // Create purchase order items
        if ($qty > 0) {
            PurchaseOrderItem::create([
                'order_id' => $purchaseOrder->id,
                'product_id' => $product->id,
                'variant_id' => null,
                'variant_value' => null,
                'quantity' => $qty,
                'received_quantity' => $qty,
                'unit_price' => $this->supplier_price ?? 0,
                'discount' => 0,
                'discount_type' => 'fixed',
                'status' => 'received',
            ]);
        }
    }





    // 🔹 Create Product
    public function createProduct()
    {
        // Enforce single pricing mode only
        $this->pricing_mode = 'single';
        $this->variant_id = null;
        $this->variant_prices = [];
        $this->variant_key_map = [];

                // Clean up image field - treat empty strings as null
        if (empty(trim($this->image ?? ''))) {
            $this->image = null;
        }

        // Ensure we have a product code before validation. The UI may let users omit
        // the code and rely on auto-generation; generate it here so validation for
        // 'code' (required) passes and we keep a stable product code.
        if (empty(trim($this->code ?? ''))) {
            $this->code = 'PROD-' . strtoupper(Str::random(8));
            Log::info('Auto-generated product code for createProduct', ['code' => $this->code]);
        }
        // Validate the form data
        $validatedData = $this->validate();

        // Check website-live eligibility before deciding the stored value.
        // A product is shown on the website ONLY when ALL conditions are met:
        //   1. Has a real product image
        //   2. Status is active (Live)
        //   3. Available stock > 0
        $createHasImage  = !empty($this->image);
        $createStatus    = 'active';
        $createStock     = (int) ($this->available_stock ?? 0);
        [$createCanBeLive, $createLiveIssues] = $this->checkWebsiteLiveEligibility(
            (bool) $this->website_live, $createHasImage, $createStatus, $createStock
        );
        $createWebsiteLive = $createCanBeLive ? true : false;

        try {
            DB::beginTransaction();

            // Generate product code if not provided
            $productCode = $this->code ?: 'PROD-' . strtoupper(Str::random(8));

            // Step 1: Create ProductDetail
            $product = ProductDetail::create([
                'code' => $productCode,
                'name' => $this->name,
                'model' => $this->model,
                'image' => $this->image,
                'description' => $this->description,
                'barcode' => $this->barcode,
                'barcode_printed' => $this->barcodeGenerated ? 'No' : 'Yes',
                'status' => 'active',
                'brand_id' => $this->brand,
                'category_id' => $this->category,
                'supplier_id' => $this->supplier,
                'variant_id' => null,
                'website_live' => $createWebsiteLive,
            ]);

            Log::info('ProductDetail created', ['id' => $product->id ?? null, 'code' => $product->code ?? null]);

            // Step 2: Create ProductPrice with product_id reference
            ProductPrice::create([
                'product_id' => $product->id,
                'variant_id' => null,
                'pricing_mode' => 'single',
                'supplier_price' => $this->supplier_price ?? 0,
                'selling_price' => $this->retail_price ?? $this->supplier_price ?? 0,
                'retail_price' => $this->retail_price ?? 0,
                'wholesale_price' => $this->wholesale_price ?? 0,
                'distributor_price' => null,
                'discount_price' => $this->discount_price ?? 0,
            ]);

            // Step 3: Create ProductStock with product_id reference
            ProductStock::create([
                'product_id' => $product->id,
                'available_stock' => $this->available_stock ?? 0,
                'damage_stock' => $this->damage_stock ?? 0,
                'total_stock' => ($this->available_stock ?? 0) + ($this->damage_stock ?? 0),
                'sold_count' => 0,
                'restocked_quantity' => 0,
            ]);

            Log::info('Created single price and stock for product', ['product_id' => $product->id]);

            // ── Auto-create Purchase Order + Items ──
            $this->createPurchaseRecord($product);

            DB::commit();

            $this->resetForm();
            $this->js("$('#createProductModal').modal('hide')");

            if ((bool) $this->website_live && !$createCanBeLive) {
                $warningHtml = $this->buildVisibilityWarningHtml($createLiveIssues);
                $warningHtml = '<p class="mb-2">Product created successfully!</p>' . $warningHtml;
                $this->js("Swal.fire({icon: 'warning', title: 'Product Created', html: " . json_encode($warningHtml) . ", confirmButtonText: 'OK', width: 460})");
            } else {
                $this->js("Swal.fire('Success!', 'Product created successfully!', 'success')");
            }

            // Clear cache for client-side refresh
            ProductApiController::clearCache();

            $this->dispatch('refreshPage');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Product creation failed: ' . $e->getMessage());
            $this->js("Swal.fire('Error!', 'Failed to create product: " . addslashes($e->getMessage()) . "', 'error')");
        }
    }

    // 🔹 Import Products from Excel - UPDATED METHOD
    public function importProducts()
    {
        // Validate file
        $this->validate([
            'importFile' => 'required|mimes:xlsx,xls,csv|max:10240', // Max 10MB
        ], [
            'importFile.required' => 'Please select an Excel file to import.',
            'importFile.mimes' => 'File must be an Excel file (xlsx, xls, or csv).',
            'importFile.max' => 'File size must not exceed 10MB.',
        ]);

        try {
            // Use the new import class with foreign key resolution
            $import = new ProductsImportWithVariants();

            // Import the file
            Excel::import($import, $this->importFile->getRealPath());

            // Get import statistics
            $successCount = $import->getSuccessCount();
            $skipCount = $import->getSkipCount();
            $errors = $import->getErrors();

            // Build success message
            $message = "Import completed! ";
            $message .= "✅ {$successCount} product(s) imported successfully. ";

            if ($skipCount > 0) {
                $message .= "⚠️ {$skipCount} product(s) skipped. ";
            }

            // Log errors for review
            if (!empty($errors)) {
                Log::warning("Import completed with errors", [
                    'success_count' => $successCount,
                    'skip_count' => $skipCount,
                    'errors' => $errors
                ]);
            }

            // Reset file input
            $this->reset(['importFile']);

            // Clear cache for client-side refresh
            ProductApiController::clearCache();

            // Close modal and show success
            $this->js("$('#importProductsModal').modal('hide')");
            $this->js("Swal.fire('Import Complete!', '{$message}', 'success')");

            $this->dispatch('refreshPage');
        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
            $failures = $e->failures();
            $errorMessage = "Import failed due to validation errors: <br>";

            foreach ($failures as $failure) {
                $errorMessage .= "Row {$failure->row()}: " . implode(', ', $failure->errors()) . "<br>";
            }

            $this->js("Swal.fire({
                icon: 'error',
                title: 'Validation Error',
                html: '{$errorMessage}',
                confirmButtonText: 'OK'
            })");
        } catch (\Exception $e) {
            Log::error("Import failed with exception", [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            $this->js("Swal.fire('Error!', 'Failed to import products: " . addslashes($e->getMessage()) . "', 'error')");
        }
    }

    // 🔹 Download Excel Template
    public function downloadTemplate()
    {
        return Excel::download(new ProductsImportTemplateExport(), 'products_import_template.xlsx');
    }

    // 🔹 Reset form fields
    private function resetForm()
    {
        $this->reset([
            'code',
            'name',
            'model',
            'brand',
            'category',
            'image',
            'description',
            'barcode',
            'status',
            'supplier',
            'supplier_price',
            'retail_price',
            'wholesale_price',
            'discount_price',
            'available_stock',
            'damage_stock',
            'pricing_mode',
            'variant_id',
            'variant_prices',
            'website_live'
        ]);

        $this->website_live = false;

        // Reset pricing mode to single
        $this->pricing_mode = 'single';
        $this->original_pricing_mode = 'single';
        $this->variant_id = null;
        $this->variant_prices = [];
        $this->variant_key_map = [];
        $this->barcodeMode = 'create';
        $this->barcodeLookupProductId = null;
        $this->barcodeGenerated = false;
        // Note: DO NOT reset purchaseDate - keep admin's selected date persistent for adding multiple products
        $this->resetValidation();
    }

    // 🔹 Edit Product
    public function editProduct($id)
    {
        // Load prices and stocks (including variant data if any)
        $product = ProductDetail::with([
            'price',
            'stock',
            'variant',
            'prices' => function ($q) {
                $q->orderBy('variant_value');
            },
            'stocks' => function ($q) {
                $q->orderBy('variant_value');
            }
        ])->findOrFail($id);

        $this->editId = $product->id;
        $this->editCode = $product->code;
        $this->editName = $product->name;
        $this->editModel = $product->model;
        $this->editBrand = $product->brand_id;
        $this->editCategory = $product->category_id;
        $this->existingImage = $product->getRawOriginal('image');
        $this->editDescription = $product->description;
        $this->editBarcode = $product->barcode;
        $this->editStatus = $product->status;
        $this->editSupplier = $product->supplier_id;
        $this->editWebsiteLive = $product->website_live ? 1 : 0;
        $singlePrice = $product->price
            ?? $product->prices->where('pricing_mode', 'single')->first()
            ?? $product->prices->first();
        $singleStock = $product->stock
            ?? $product->stocks->whereNull('variant_id')->first()
            ?? $product->stocks->first();

        $this->editSupplierPrice = $singlePrice->supplier_price ?? 0;
        $this->editRetailPrice = $singlePrice->retail_price ?? 0;
        $this->editWholesalePrice = $singlePrice->wholesale_price ?? 0;
        $this->editDiscountPrice = $singlePrice->discount_price ?? 0;
        $this->editAvailableStock = $singleStock->available_stock ?? 0;
        $this->editDamageStock = $singleStock->damage_stock ?? 0;

        // Force single mode for edit
        $this->pricing_mode = 'single';
        $this->variant_id = null;
        $this->variant_prices = [];
        $this->variant_key_map = [];

        // Record original pricing mode so updateProduct can detect an intentional mode switch
        $this->original_pricing_mode = $this->pricing_mode;

        $this->resetValidation();

        $this->js("
            setTimeout(() => {
                const modal = new bootstrap.Modal(document.getElementById('editProductModal'));
                modal.show();
            }, 100);
        ");
    }

    // 🔹 Validation Rules for Update
    protected function updateRules()
    {
        return [
            'editName' => 'required|string|max:255',
            'editCode' => 'required|string|max:100|unique:product_details,code,' . $this->editId,
            'editModel' => 'nullable|string|max:255',
            'editBrand' => 'required|exists:brand_lists,id',
            'editCategory' => 'required|exists:category_lists,id',
            'editSupplier' => 'nullable|exists:product_suppliers,id',
            'editImage' => 'nullable|string|max:100000',
            'editDescription' => 'nullable|string|max:1000',
            'editBarcode' => 'nullable|string|max:255|unique:product_details,barcode,' . $this->editId,
            'editStatus' => 'required|in:active,inactive',
            'editWebsiteLive' => 'nullable|boolean',
            'editSupplierPrice' => 'required|numeric|min:0',
            'editRetailPrice' => 'required|numeric|min:0',
            'editWholesalePrice' => 'required|numeric|min:0',
            'editDiscountPrice' => 'nullable|numeric|min:0|lte:editRetailPrice',
            'editAvailableStock' => 'required|integer|min:0',
            'editDamageStock' => 'required|integer|min:0',
        ];
    }

    // 🔹 Update Product
    public function updateProduct()
    {
        // Enforce single pricing mode only
        $this->pricing_mode = 'single';
        $this->variant_id = null;
        $this->variant_prices = [];
        $this->variant_key_map = [];

        // Clean up image field - treat empty strings as null
        if (empty(trim($this->editImage ?? ''))) {
            $this->editImage = null;
        }

        // Build validation rules and validate the form data
        $rules = $this->updateRules();

        // Use friendly attribute labels for validation messages
        $validatedData = $this->validate($rules, [], $this->attributes());

        try {
            DB::beginTransaction();

            $product = ProductDetail::findOrFail($this->editId);

            // Website Live visibility check
            // A product is visible only when it has a non-blank image, is Live (active), has Website Live enabled, and has available stock > 0.
            $editRawImage  = trim((string) ($product->getRawOriginal('image') ?? ''));
            $editHasImage  = !empty($this->editImage) || ($editRawImage !== '' && $editRawImage !== 'images/product.jpg');
            $editStatus    = $this->editStatus;
            $editStock     = (int) ($this->editAvailableStock ?? 0);
            [$editCanBeLive, $editLiveIssues] = $this->checkWebsiteLiveEligibility(
                (bool) $this->editWebsiteLive, $editHasImage, $editStatus, $editStock
            );
            $editWebsiteLive = $editCanBeLive ? true : false;

            // Update basic product details
            $product->update([
                'code' => $this->editCode,
                'name' => $this->editName,
                'model' => $this->editModel,
                'brand_id' => $this->editBrand,
                'category_id' => $this->editCategory,
                'image' => $this->editImage ?: $this->existingImage,
                'description' => $this->editDescription,
                'barcode' => $this->editBarcode,
                'status' => $this->editStatus,
                'supplier_id' => $this->editSupplier,
                'website_live' => $editWebsiteLive,
            ]);

            // ====== SINGLE PRICING MODE ONLY ======
            $product->update(['variant_id' => null]);

            // Delete variant prices/stocks
            ProductPrice::where('product_id', $product->id)
                ->where('pricing_mode', 'variant')
                ->delete();

            ProductStock::where('product_id', $product->id)
                ->whereNotNull('variant_id')
                ->delete();

            // Update or create single price
            ProductPrice::updateOrCreate(
                [
                    'product_id' => $product->id,
                    'pricing_mode' => 'single',
                    'variant_id' => null,
                    'variant_value' => null,
                ],
                [
                    'supplier_price' => $this->editSupplierPrice ?? 0,
                    'selling_price' => $this->editRetailPrice ?? 0,
                    'retail_price' => $this->editRetailPrice ?? 0,
                    'wholesale_price' => $this->editWholesalePrice ?? 0,
                    'distributor_price' => null,
                    'discount_price' => $this->editDiscountPrice ?? 0,
                ]
            );

            // Update or create single stock
            ProductStock::updateOrCreate(
                [
                    'product_id' => $product->id,
                    'variant_id' => null,
                    'variant_value' => null,
                ],
                [
                    'available_stock' => $this->editAvailableStock ?? 0,
                    'damage_stock' => $this->editDamageStock ?? 0,
                ]
            );

            Log::info('updateProduct: Updated to single pricing mode', [
                'product_id' => $product->id,
            ]);

            DB::commit();

            // Clear cache for client-side refresh
            ProductApiController::clearCache();

            $this->js("$('#editProductModal').modal('hide')");

            if ($this->editWebsiteLive && !$editCanBeLive) {
                $warningHtml = $this->buildVisibilityWarningHtml($editLiveIssues);
                $warningHtml = '<p class="mb-2">Product updated successfully!</p>' . $warningHtml;
                $this->js("Swal.fire({icon: 'warning', title: 'Product Updated', html: " . json_encode($warningHtml) . ", confirmButtonText: 'OK', width: 460})");
            } else {
                $this->js("Swal.fire('Success!', 'Product updated successfully!', 'success')");
            }
            $this->dispatch('refreshPage');
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('updateProduct failed', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'product_id' => $this->editId ?? null,
                'pricing_mode' => $this->pricing_mode,
                'variant_id' => $this->variant_id,
                'variant_prices' => $this->variant_prices,
            ]);

            $this->js("Swal.fire('Error!', 'Failed to update product: " . addslashes($e->getMessage()) . "', 'error')");
        }
    }


    // 🔹 Confirm Delete Product
    public function confirmDeleteProduct($id)
    {
        $this->js("
            Swal.fire({
                title: 'Are you sure?',
                text: 'You won\\'t be able to revert this!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    \$wire.deleteProduct($id);
                }
            });
        ");
    }

    // 🔹 Delete Product
    public function deleteProduct($id)
    {
        try {
            $isUsedInSales = SaleItem::where('product_id', $id)->exists();
            if ($isUsedInSales) {
                $this->js("Swal.fire('Cannot Delete!', 'This product is already used in sales, so it cannot be deleted.', 'error')");
                return;
            }

            $product = ProductDetail::findOrFail($id);

            // Delete related records first
            ProductPrice::where('product_id', $id)->delete();
            ProductStock::where('product_id', $id)->delete();

            // Delete image if exists
            if ($product->image) {
                Storage::disk('public')->delete($product->image);
            }

            // Delete the product
            $product->delete();

            // Clear cache for client-side refresh
            ProductApiController::clearCache();

            $this->js("Swal.fire('Success!', 'Product deleted successfully!', 'success')");
            $this->dispatch('refreshPage');
        } catch (\Exception $e) {
            $this->js("Swal.fire('Error!', 'Failed to delete product. Please try again.', 'error')");
        }
    }

    // 🔹 View Product Details
    public function viewProductDetails($id)
    {
        // For staff users, show the same product details as admin (full product with variants)
        if ($this->isStaff()) {
            $product = ProductDetail::with([
                'price',
                'stock',
                'variant',
                'prices' => function ($q) {
                    $q->where('pricing_mode', 'variant')->orderBy('variant_value');
                },
                'stocks' => function ($q) {
                    $q->orderBy('variant_value');
                },
                'brand',
                'category'
            ])->find($id);

            if ($product) {
                // Maintain backward compatible attributes for the blade (was using brand/category strings)
                $product->brand = $product->brand->brand_name ?? null;
                $product->category = $product->category->category_name ?? null;

                // Prepare a sorted list of variant values for predictable display order
                $variantValues = [];
                if ($product->variant && is_array($product->variant->variant_values)) {
                    $variantValues = $product->variant->variant_values;
                } elseif ($product->prices && $product->prices->isNotEmpty()) {
                    $variantValues = $product->prices->pluck('variant_value')->unique()->toArray();
                }

                $product->sorted_variant_values = $this->sortVariantValues($variantValues);
            }

            $this->viewProduct = $product;
        } else {
            // For admin users, show full product details (including variant prices/stocks if any)
            $product = ProductDetail::with([
                'price',
                'stock',
                'variant',
                'prices' => function ($q) {
                    $q->where('pricing_mode', 'variant')->orderBy('variant_value');
                },
                'stocks' => function ($q) {
                    $q->orderBy('variant_value');
                },
                'brand',
                'category'
            ])->find($id);

            if ($product) {
                // Maintain backward compatible attributes for the blade (was using brand/category strings)
                $product->brand = $product->brand->brand_name ?? null;
                $product->category = $product->category->category_name ?? null;

                // Prepare a sorted list of variant values for predictable display order
                $variantValues = [];
                if ($product->variant && is_array($product->variant->variant_values)) {
                    $variantValues = $product->variant->variant_values;
                } elseif ($product->prices && $product->prices->isNotEmpty()) {
                    $variantValues = $product->prices->pluck('variant_value')->unique()->toArray();
                }

                $product->sorted_variant_values = $this->sortVariantValues($variantValues);
            }

            $this->viewProduct = $product;
        }

        $this->js("$('#viewProductModal').modal('show')");
    }

    // 🔹 Open Stock Adjustment Modal
    public function openStockAdjustment($id)
    {
        $product = ProductDetail::with(['stock'])->findOrFail($id);

        $this->adjustmentProductId = $product->id;
        $this->adjustmentProductName = $product->name;
        $this->adjustmentAvailableStock = $product->stock->available_stock ?? 0;
        $this->adjustmentDamageStock = $product->stock->damage_stock ?? 0;
        $this->damageQuantity = null; // Clear damage input
        $this->availableQuantity = null; // Clear available input

        $this->resetValidation();
        $this->js("$('#stockAdjustmentModal').modal('show')");
    }

    // 🔹 Stock Adjustment Validation Rules
    protected function adjustmentRules()
    {
        return [
            'adjustmentQuantity' => 'required|integer|min:1',
        ];
    }


    // 🔹 Add Damage Stock (Deduct from Available Stock and Batches using FIFO)
    public function addDamageStock()
    {
        $this->validate([
            'damageQuantity' => 'required|integer|min:1',
        ], [
            'damageQuantity.required' => 'Please enter damage quantity.',
            'damageQuantity.min' => 'Damage quantity must be at least 1.',
        ]);

        DB::beginTransaction();
        try {
            $product = ProductDetail::with(['stock', 'price'])->findOrFail($this->adjustmentProductId);
            $stock = $product->stock;

            if (!$stock) {
                $stock = ProductStock::create([
                    'product_id' => $product->id,
                    'available_stock' => 0,
                    'damage_stock' => 0,
                    'total_stock' => 0,
                    'sold_count' => 0,
                ]);
            }

            $damageQty = (int) $this->damageQuantity;
            $currentAvailable = $stock->available_stock;
            $currentDamage = $stock->damage_stock;

            // 🔹 Deduct from batches using FIFO (First In, First Out)
            $remainingDamage = $damageQty;

            $batches = ProductBatch::where('product_id', $product->id)
                ->where('status', 'active')
                ->where('remaining_quantity', '>', 0)
                ->orderBy('received_date', 'asc')
                ->orderBy('id', 'asc')
                ->get();

            if ($batches->isNotEmpty()) {
                foreach ($batches as $batch) {
                    if ($remainingDamage <= 0)
                        break;

                    $deductQty = min($remainingDamage, $batch->remaining_quantity);
                    $batch->remaining_quantity -= $deductQty;

                    if ($batch->remaining_quantity == 0) {
                        $batch->status = 'depleted';
                    }

                    $batch->save();
                    $remainingDamage -= $deductQty;

                    Log::info("Damage added: Deducted {$deductQty} from batch {$batch->batch_number}");
                }

                // Check if we have enough stock in batches
                if ($remainingDamage > 0) {
                    // Not enough in batches, but still allow if available_stock covers it
                    if ($damageQty > $currentAvailable) {
                        DB::rollBack();
                        $this->js("Swal.fire('Error!', 'Not enough available stock! Available: {$currentAvailable}, Required: {$damageQty}', 'error')");
                        return;
                    }
                    Log::info("Batch stock insufficient by {$remainingDamage}, but available_stock covers it.");
                }
            } else {
                // No batches exist - just check available stock directly
                if ($damageQty > $currentAvailable) {
                    DB::rollBack();
                    $this->js("Swal.fire('Error!', 'Not enough available stock! Available: {$currentAvailable}, Required: {$damageQty}', 'error')");
                    return;
                }
                Log::info("No batches found for product {$product->id}. Deducting directly from stock.");
            }

            // Update stock table
            $newAvailableStock = max(0, $currentAvailable - $damageQty);
            $newDamageStock = $currentDamage + $damageQty;

            $stock->available_stock = $newAvailableStock;
            $stock->damage_stock = $newDamageStock;
            $stock->total_stock = $newAvailableStock + $newDamageStock;
            $stock->save();

            // 🔹 Update product prices based on the oldest active batch with stock
            $oldestActiveBatch = ProductBatch::where('product_id', $product->id)
                ->where('status', 'active')
                ->where('remaining_quantity', '>', 0)
                ->orderBy('received_date', 'asc')
                ->orderBy('id', 'asc')
                ->first();

            if ($oldestActiveBatch && $product->price) {
                // Update the product_prices table with the batch prices
                $product->price->supplier_price = $oldestActiveBatch->supplier_price;
                $product->price->selling_price = $oldestActiveBatch->selling_price;
                $product->price->save();

                Log::info("Prices updated: Supplier={$oldestActiveBatch->supplier_price}, Selling={$oldestActiveBatch->selling_price} from batch {$oldestActiveBatch->batch_number}");
            }

            DB::commit();

            // Clear cache for client-side refresh
            ProductApiController::clearCache();

            // Reset and refresh
            $this->damageQuantity = null;
            $this->adjustmentAvailableStock = $newAvailableStock;
            $this->adjustmentDamageStock = $newDamageStock;

            $this->js("Swal.fire('Success!', 'Damage stock added successfully! {$damageQty} units marked as damaged.', 'success')");
            $this->dispatch('refreshPage');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Add damage stock failed: " . $e->getMessage());
            $this->js("Swal.fire('Error!', 'Failed to add damage stock: " . addslashes($e->getMessage()) . "', 'error')");
        }
    }

    // 🔹 Adjust Available Stock (Increase Stock and Add to Oldest Batch)
    public function adjustAvailableStock()
    {
        $this->validate([
            'availableQuantity' => 'required|integer|min:1',
        ], [
            'availableQuantity.required' => 'Please enter quantity to add.',
            'availableQuantity.min' => 'Quantity must be at least 1.',
        ]);

        DB::beginTransaction();
        try {
            $product = ProductDetail::with(['stock'])->findOrFail($this->adjustmentProductId);
            $stock = $product->stock;

            if (!$stock) {
                $stock = ProductStock::create([
                    'product_id' => $product->id,
                    'available_stock' => 0,
                    'damage_stock' => 0,
                    'total_stock' => 0,
                    'sold_count' => 0,
                ]);
            }

            $addQty = (int) $this->availableQuantity;
            $currentAvailable = $stock->available_stock;

            // 🔹 Add to oldest active batch OR create new batch
            $oldestBatch = ProductBatch::where('product_id', $product->id)
                ->where('status', 'active')
                ->orderBy('received_date', 'asc')
                ->orderBy('id', 'asc')
                ->first();

            if ($oldestBatch) {
                // Add to existing oldest batch
                $oldestBatch->remaining_quantity += $addQty;
                $oldestBatch->quantity += $addQty;
                $oldestBatch->save();

                Log::info("Available stock increased: Added {$addQty} to batch {$oldestBatch->batch_number}");
            } else {
                // No batch exists, create a manual adjustment batch
                $productPrice = ProductPrice::where('product_id', $product->id)->first();

                // Try to merge into an existing batch with same prices
                $matchingBatch = ProductBatch::where('product_id', $product->id)
                    ->where('status', 'active')
                    ->where('supplier_price', $productPrice->supplier_price ?? 0)
                    ->where('wholesale_price', $productPrice->wholesale_price ?? 0)
                    ->where('retail_price', $productPrice->retail_price ?? 0)
                    ->where('distributor_price', $productPrice->distributor_price ?? 0)
                    ->orderBy('received_date', 'desc')
                    ->orderBy('id', 'desc')
                    ->first();

                if ($matchingBatch) {
                    $matchingBatch->quantity += $addQty;
                    $matchingBatch->remaining_quantity += $addQty;
                    $matchingBatch->save();
                    Log::info("Added {$addQty} to existing batch {$matchingBatch->batch_number} for product {$product->id}");
                } else {
                    ProductBatch::create([
                        'product_id' => $product->id,
                        'batch_number' => ProductBatch::generateBatchNumber($product->id),
                        'purchase_order_id' => null,
                        'supplier_price' => $productPrice->supplier_price ?? 0,
                        'selling_price' => $productPrice->selling_price ?? 0,
                        'wholesale_price' => $productPrice->wholesale_price ?? 0,
                        'retail_price' => $productPrice->retail_price ?? 0,
                        'distributor_price' => $productPrice->distributor_price ?? 0,
                        'quantity' => $addQty,
                        'remaining_quantity' => $addQty,
                        'received_date' => now(),
                        'status' => 'active',
                    ]);

                    Log::info("Created new batch for {$product->id} with qty {$addQty}");
                }

                Log::info("Available stock increased: Created new batch for {$addQty} units");
            }

            // Update stock table - only increase available stock
            $newAvailableStock = $currentAvailable + $addQty;

            $stock->available_stock = $newAvailableStock;
            $stock->total_stock = $newAvailableStock + $stock->damage_stock;
            $stock->save();

            DB::commit();

            // Clear cache for client-side refresh
            ProductApiController::clearCache();

            // Reset and refresh
            $this->availableQuantity = null;
            $this->adjustmentAvailableStock = $newAvailableStock;

            $this->js("Swal.fire('Success!', 'Available stock increased successfully! Added {$addQty} units.', 'success')");
            $this->dispatch('refreshPage');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Adjust available stock failed: " . $e->getMessage());
            $this->js("Swal.fire('Error!', 'Failed to adjust available stock: " . addslashes($e->getMessage()) . "', 'error')");
        }
    }

    /**
     * Open Product History Modal
     */
    public function openProductHistory($id)
    {
        try {
            $product = ProductDetail::findOrFail($id);

            $this->historyProductId = $product->id;
            $this->historyProductName = $product->name;

            // Set default tab
            $this->historyTab = 'sales';

            // Load ALL history data at once
            $this->loadSalesHistory();
            $this->loadPurchasesHistory();
            $this->loadReturnsHistory();
            $this->loadQuotationsHistory();

            // Log for debugging
            Log::info('Product History Loaded', [
                'product_id' => $this->historyProductId,
                'sales' => count($this->salesHistory),
                'purchases' => count($this->purchasesHistory),
                'returns' => count($this->returnsHistory),
                'quotations' => count($this->quotationsHistory)
            ]);

            // Show modal using Bootstrap JavaScript
            $this->js("
                setTimeout(() => {
                    const modal = new bootstrap.Modal(document.getElementById('productHistoryModal'));
                    modal.show();
                }, 100);
            ");
        } catch (\Exception $e) {
            $this->js("Swal.fire('Error!', 'Failed to load product history: " . addslashes($e->getMessage()) . "', 'error')");
        }
    }

    /**
     * Switch History Tab
     */
    public function switchHistoryTab($tab)
    {
        // Validate tab name
        $validTabs = ['sales', 'purchases', 'returns', 'quotations'];
        if (!in_array($tab, $validTabs)) {
            $tab = 'sales';
        }

        // Simply update the active tab
        $this->historyTab = $tab;

        // Log for debugging
        Log::info('Tab switched', [
            'tab' => $tab,
            'sales_count' => count($this->salesHistory),
            'purchases_count' => count($this->purchasesHistory),
            'returns_count' => count($this->returnsHistory),
            'quotations_count' => count($this->quotationsHistory)
        ]);

        // Dispatch event for debugging
        $this->dispatch('historyTabSwitched', ['tab' => $tab]);
    }

    /**
     * Load Sales History
     */
    private function loadSalesHistory()
    {
        try {
            $salesItems = SaleItem::with(['sale.customer', 'sale.user'])
                ->where('sale_items.product_id', $this->historyProductId)
                ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
                ->select(
                    'sale_items.*',
                    'sales.invoice_number',
                    'sales.sale_type',
                    'sales.customer_type',
                    'sales.payment_type',
                    'sales.payment_status',
                    'sales.status as sale_status',
                    'sales.created_at as sale_date'
                )
                ->orderBy('sales.created_at', 'desc')
                ->get();

            $this->salesHistory = $salesItems->map(function ($sale) {
                return [
                    'id' => $sale->id,
                    'invoice_number' => $sale->invoice_number,
                    'sale_type' => $sale->sale_type ?? 'regular',
                    'customer_type' => $sale->customer_type ?? 'walk-in',
                    'quantity' => $sale->quantity,
                    'unit_price' => $sale->unit_price,
                    'discount_per_unit' => $sale->discount_per_unit ?? 0,
                    'total_discount' => $sale->total_discount ?? 0,
                    'total' => $sale->total,
                    'payment_type' => $sale->payment_type ?? 'cash',
                    'payment_status' => $sale->payment_status ?? 'unpaid',
                    'sale_status' => $sale->sale_status ?? 'completed',
                    'sale_date' => $sale->sale_date,
                    'customer_name' => $sale->sale && $sale->sale->customer ? $sale->sale->customer->name : 'Walk-in Customer',
                    'customer_phone' => $sale->sale && $sale->sale->customer ? $sale->sale->customer->phone : 'N/A',
                    'user_name' => $sale->sale && $sale->sale->user ? $sale->sale->user->name : 'N/A'
                ];
            })->toArray();
        } catch (\Exception $e) {
            $this->salesHistory = [];
        }
    }

    /**
     * Load Purchases History
     */
    private function loadPurchasesHistory()
    {
        try {
            $purchaseItems = PurchaseOrderItem::with(['order.supplier'])
                ->where('purchase_order_items.product_id', $this->historyProductId)
                ->join('purchase_orders', 'purchase_order_items.order_id', '=', 'purchase_orders.id')
                ->select(
                    'purchase_order_items.*',
                    'purchase_orders.order_code',
                    'purchase_orders.order_date',
                    'purchase_orders.received_date',
                    'purchase_orders.status as order_status'
                )
                ->orderBy('purchase_orders.order_date', 'desc')
                ->get();

            $this->purchasesHistory = $purchaseItems->map(function ($purchase) {
                $total = $purchase->received_quantity * $purchase->unit_price;
                if (isset($purchase->discount) && $purchase->discount > 0) {
                    $total -= $purchase->discount;
                }

                return [
                    'id' => $purchase->id,
                    'order_code' => $purchase->order_code,
                    'order_date' => $purchase->order_date,
                    'received_date' => $purchase->received_date ?? 'Pending',
                    'quantity' => $purchase->quantity,
                    'received_quantity' => $purchase->received_quantity,
                    'unit_price' => $purchase->unit_price,
                    'discount' => $purchase->discount ?? 0,
                    'total' => $total,
                    'order_status' => $purchase->order_status ?? 'pending',
                    'supplier_name' => $purchase->order && $purchase->order->supplier ? $purchase->order->supplier->name : 'N/A',
                    'supplier_phone' => $purchase->order && $purchase->order->supplier ? $purchase->order->supplier->phone : 'N/A'
                ];
            })->toArray();
        } catch (\Exception $e) {
            $this->purchasesHistory = [];
        }
    }

    /**
     * Load Returns History
     */
    private function loadReturnsHistory()
    {
        try {
            $returns = ReturnsProduct::with(['sale.customer', 'product'])
                ->where('returns_products.product_id', $this->historyProductId)
                ->join('sales', 'returns_products.sale_id', '=', 'sales.id')
                ->select(
                    'returns_products.*',
                    'sales.invoice_number'
                )
                ->orderBy('returns_products.created_at', 'desc')
                ->get();

            $this->returnsHistory = $returns->map(function ($return) {
                return [
                    'id' => $return->id,
                    'invoice_number' => $return->invoice_number,
                    'return_quantity' => $return->return_quantity,
                    'selling_price' => $return->selling_price ?? 0,
                    'total_amount' => $return->total_amount ?? 0,
                    'notes' => $return->notes ?? 'No notes provided',
                    'return_date' => $return->created_at,
                    'customer_name' => $return->sale && $return->sale->customer ? $return->sale->customer->name : 'Walk-in Customer',
                    'customer_phone' => $return->sale && $return->sale->customer ? $return->sale->customer->phone : 'N/A'
                ];
            })->toArray();
        } catch (\Exception $e) {
            $this->returnsHistory = [];
        }
    }

    /**
     * Load Quotations History
     */
    private function loadQuotationsHistory()
    {
        try {
            $quotations = Quotation::with(['creator', 'customer'])
                ->where('status', '!=', 'draft')
                ->orderBy('quotation_date', 'desc')
                ->get();

            $this->quotationsHistory = [];

            foreach ($quotations as $quotation) {
                $items = is_array($quotation->items) ? $quotation->items : json_decode($quotation->items, true);

                if (!empty($items)) {
                    foreach ($items as $item) {
                        if (isset($item['product_id']) && $item['product_id'] == $this->historyProductId) {
                            $this->quotationsHistory[] = [
                                'id' => $quotation->id,
                                'quotation_number' => $quotation->quotation_number,
                                'reference_number' => $quotation->reference_number ?? 'N/A',
                                'customer_name' => $quotation->customer_name ?? ($quotation->customer->name ?? 'N/A'),
                                'customer_phone' => $quotation->customer_phone ?? ($quotation->customer->phone ?? 'N/A'),
                                'customer_email' => $quotation->customer_email ?? 'N/A',
                                'quotation_date' => $quotation->quotation_date,
                                'valid_until' => $quotation->valid_until,
                                'status' => $quotation->status,
                                'quantity' => $item['quantity'] ?? 0,
                                'unit_price' => $item['unit_price'] ?? 0,
                                'discount' => $item['discount'] ?? 0,
                                'total' => $item['total'] ?? 0,
                                'product_name' => $item['product_name'] ?? 'N/A',
                                'product_code' => $item['product_code'] ?? 'N/A',
                                'created_by_name' => $quotation->creator->name ?? 'N/A'
                            ];
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            $this->quotationsHistory = [];
            // Log error for debugging
        }
    }

    // 🔹 Real-time validation for specific fields
    public function updated($propertyName)
    {
        // Clear view/edit state when page changes to fix modal showing wrong product
        if ($propertyName === 'page' || $propertyName === 'search') {
            $this->viewProduct = null;
            $this->editId = null;
            $this->historyProductId = null;
            $this->adjustmentProductId = null;
        }

        // Only validate specific fields in real-time to improve performance
        if (
            in_array($propertyName, [
                'name',
                'code',
                'brand',
                'category',
                'supplier_price',
                'selling_price',
                'available_stock',
                'editName',
                'editCode',
                'editBrand',
                'editCategory',
                'editSupplierPrice',
                'editSellingPrice',
                'damageQuantity',
                'availableQuantity'
            ])
        ) {
            $this->validateOnly($propertyName);
        }
    }
}
