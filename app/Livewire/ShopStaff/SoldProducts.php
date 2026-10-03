<?php

namespace App\Livewire\ShopStaff;

use App\Models\CategoryList;
use App\Models\ProductDetail;
use App\Models\SaleItem;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

#[Title('Sold Products Analytics - Local vs Indian')]
#[Layout('components.layouts.shop-staff')]
class SoldProducts extends Component
{
    // Filter properties
    public $datePreset = 'this_month';
    public $startDate = '';
    public $endDate = '';
    public $search = '';
    public $categoryFilter = 'all';
    public $sortBy = 'qty_desc'; // 'qty_desc', 'amount_desc', 'name_asc'
    public $activeTab = 'all'; // 'all', 'local', 'indian' (useful on mobile view)

    // History Modal State
    public $showHistoryModal = false;
    public $selectedProductId = null;
    public $selectedProductCode = null;
    public $selectedProductName = null;
    public $selectedProductDetails = null;
    public $selectedProductSales = [];

    public function mount()
    {
        $this->applyDatePreset('this_month');
    }

    public function updatedDatePreset($value)
    {
        $this->applyDatePreset($value);
    }

    public function applyDatePreset($preset)
    {
        $this->datePreset = $preset;
        $today = now();

        switch ($preset) {
            case 'today':
                $this->startDate = $today->format('Y-m-d');
                $this->endDate = $today->format('Y-m-d');
                break;
            case 'yesterday':
                $this->startDate = $today->copy()->subDay()->format('Y-m-d');
                $this->endDate = $today->copy()->subDay()->format('Y-m-d');
                break;
            case 'this_week':
                $this->startDate = $today->copy()->startOfWeek()->format('Y-m-d');
                $this->endDate = $today->copy()->endOfWeek()->format('Y-m-d');
                break;
            case 'this_month':
                $this->startDate = $today->copy()->startOfMonth()->format('Y-m-d');
                $this->endDate = $today->copy()->endOfMonth()->format('Y-m-d');
                break;
            case 'last_month':
                $this->startDate = $today->copy()->subMonth()->startOfMonth()->format('Y-m-d');
                $this->endDate = $today->copy()->subMonth()->endOfMonth()->format('Y-m-d');
                break;
            case 'all_time':
                $this->startDate = '';
                $this->endDate = '';
                break;
            case 'custom':
                // User enters custom dates
                break;
        }
    }

    public function openHistoryModal($productId, $productCode, $productName)
    {
        $this->selectedProductId = $productId;
        $this->selectedProductCode = $productCode;
        $this->selectedProductName = $productName;

        $product = $productId ? ProductDetail::with(['stock', 'category', 'brand'])->find($productId) : null;

        $salesQuery = SaleItem::with(['sale.customer', 'sale.user'])
            ->whereHas('sale', function ($q) {
                $q->where('status', '!=', 'rejected');
            })
            ->when($productId, function ($q) use ($productId) {
                $q->where('sale_items.product_id', $productId);
            }, function ($q) use ($productCode) {
                $q->where('sale_items.product_code', $productCode);
            })
            ->when($this->startDate, function ($q) {
                $q->whereDate('sale_items.created_at', '>=', $this->startDate);
            })
            ->when($this->endDate, function ($q) {
                $q->whereDate('sale_items.created_at', '<=', $this->endDate);
            })
            ->orderBy('sale_items.created_at', 'desc');

        $this->selectedProductSales = $salesQuery->get()->map(function ($item) {
            return [
                'sale_id' => $item->sale_id,
                'invoice_number' => $item->sale->invoice_number ?? "INV-{$item->sale_id}",
                'date' => $item->created_at ? $item->created_at->format('d M Y, h:i A') : 'N/A',
                'customer_name' => $item->sale->customer->name ?? $item->sale->walking_customer_name ?? 'Walk-in Customer',
                'customer_phone' => $item->sale->customer->phone ?? $item->sale->walking_customer_phone ?? '-',
                'staff_name' => $item->sale->user->name ?? 'Shop Staff',
                'quantity' => (int) $item->quantity,
                'unit_price' => (float) $item->unit_price,
                'discount' => (float) ($item->total_discount ?? 0),
                'total' => (float) $item->total,
                'payment_type' => $item->sale->payment_type ?? 'cash',
                'payment_status' => $item->sale->payment_status ?? 'paid',
            ];
        })->toArray();

        $this->selectedProductDetails = [
            'name' => $productName,
            'code' => $productCode,
            'model' => $product->model ?? 'Standard',
            'category' => $product->category->category_name ?? 'General',
            'available_stock' => $product && $product->stock ? $product->stock->available_stock : 'N/A',
            'origin' => ($product && $product->isLocal()) || str_starts_with($productCode, 'SL-') ? 'Local (Handmade)' : 'Indian (Imported)',
            'total_qty_in_period' => array_sum(array_column($this->selectedProductSales, 'quantity')),
            'total_amount_in_period' => array_sum(array_column($this->selectedProductSales, 'total')),
        ];

        $this->showHistoryModal = true;
    }

    public function closeHistoryModal()
    {
        $this->showHistoryModal = false;
        $this->selectedProductId = null;
        $this->selectedProductCode = null;
        $this->selectedProductName = null;
        $this->selectedProductDetails = null;
        $this->selectedProductSales = [];
    }

    public function render()
    {
        // 1. Build query to aggregate sold items
        $query = SaleItem::query()
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->leftJoin('product_details', 'sale_items.product_id', '=', 'product_details.id')
            ->where('sales.status', '!=', 'rejected');

        if ($this->startDate) {
            $query->whereDate('sales.created_at', '>=', $this->startDate);
        }
        if ($this->endDate) {
            $query->whereDate('sales.created_at', '<=', $this->endDate);
        }
        if ($this->search) {
            $s = trim($this->search);
            $query->where(function ($q) use ($s) {
                $q->where('sale_items.product_name', 'like', "%{$s}%")
                    ->orWhere('sale_items.product_code', 'like', "%{$s}%")
                    ->orWhere('sale_items.product_model', 'like', "%{$s}%")
                    ->orWhere('product_details.barcode', 'like', "%{$s}%");
            });
        }
        if ($this->categoryFilter && $this->categoryFilter !== 'all') {
            $query->where('product_details.category_id', $this->categoryFilter);
        }

        $rawItems = $query->select([
            'sale_items.product_id',
            'sale_items.product_code',
            'sale_items.product_name',
            'sale_items.product_model',
            DB::raw('COALESCE(product_details.origin, IF(sale_items.product_code LIKE "SL-%" OR sale_items.product_model = "Handmade Jewelry", "local", "indian")) as calculated_origin'),
            DB::raw('SUM(sale_items.quantity) as total_qty_sold'),
            DB::raw('SUM(sale_items.total) as total_amount_sold'),
            DB::raw('SUM(sale_items.total) / NULLIF(SUM(sale_items.quantity), 0) as avg_unit_price'),
            DB::raw('COUNT(DISTINCT sale_items.sale_id) as total_transactions'),
            'product_details.image',
            'product_details.barcode',
            'product_details.category_id',
        ])
        ->groupBy([
            'sale_items.product_id',
            'sale_items.product_code',
            'sale_items.product_name',
            'sale_items.product_model',
            'calculated_origin',
            'product_details.image',
            'product_details.barcode',
            'product_details.category_id',
        ])
        ->get();

        // 2. Fetch stock and categories for active product details
        $productIds = $rawItems->pluck('product_id')->filter()->unique()->toArray();
        $productDetailsMap = empty($productIds) 
            ? collect() 
            : ProductDetail::with(['stock', 'category'])->whereIn('id', $productIds)->get()->keyBy('id');

        $enrichedItems = $rawItems->map(function ($it) use ($productDetailsMap) {
            $productObj = $it->product_id && isset($productDetailsMap[$it->product_id]) 
                ? $productDetailsMap[$it->product_id] 
                : null;

            $it->available_stock = $productObj && $productObj->stock ? (int) $productObj->stock->available_stock : null;
            $it->category_name = $productObj && $productObj->category ? $productObj->category->category_name : 'General';
            $it->display_barcode = $productObj && $productObj->barcode ? $productObj->barcode : $it->product_code;
            $it->total_qty_sold = (int) $it->total_qty_sold;
            $it->total_amount_sold = (float) $it->total_amount_sold;
            $it->avg_unit_price = (float) $it->avg_unit_price;
            $it->total_transactions = (int) $it->total_transactions;
            return $it;
        });

        // 3. Sort items
        if ($this->sortBy === 'amount_desc') {
            $sortedItems = $enrichedItems->sortByDesc('total_amount_sold')->values();
        } elseif ($this->sortBy === 'name_asc') {
            $sortedItems = $enrichedItems->sortBy('product_name', SORT_NATURAL | SORT_FLAG_CASE)->values();
        } else {
            // default: qty_desc
            $sortedItems = $enrichedItems->sortByDesc('total_qty_sold')->values();
        }

        // 4. Split into Local and Indian
        $localList = $sortedItems->where('calculated_origin', 'local')->values();
        $indianList = $sortedItems->where('calculated_origin', 'indian')->values();

        // Assign Rank numbers
        $localProducts = $localList->map(function ($item, $index) {
            $item->rank = $index + 1;
            return $item;
        });
        $indianProducts = $indianList->map(function ($item, $index) {
            $item->rank = $index + 1;
            return $item;
        });

        // 5. Calculate KPIs & Subtotals
        $localTotalQty = (int) $localProducts->sum('total_qty_sold');
        $localTotalAmount = (float) $localProducts->sum('total_amount_sold');
        $localUniqueCount = $localProducts->count();
        $topLocalProduct = $localProducts->first();

        $indianTotalQty = (int) $indianProducts->sum('total_qty_sold');
        $indianTotalAmount = (float) $indianProducts->sum('total_amount_sold');
        $indianUniqueCount = $indianProducts->count();
        $topIndianProduct = $indianProducts->first();

        $overallTotalQty = $localTotalQty + $indianTotalQty;
        $overallTotalAmount = $localTotalAmount + $indianTotalAmount;
        $overallUniqueCount = $localUniqueCount + $indianUniqueCount;

        $localShare = $overallTotalAmount > 0 ? round(($localTotalAmount / $overallTotalAmount) * 100, 1) : 0;
        $indianShare = $overallTotalAmount > 0 ? round(($indianTotalAmount / $overallTotalAmount) * 100, 1) : 0;

        $categories = CategoryList::orderBy('category_name')->get();

        return view('livewire.shop-staff.sold-products', [
            'localProducts' => $localProducts,
            'indianProducts' => $indianProducts,
            'localTotalQty' => $localTotalQty,
            'localTotalAmount' => $localTotalAmount,
            'localUniqueCount' => $localUniqueCount,
            'topLocalProduct' => $topLocalProduct,
            'indianTotalQty' => $indianTotalQty,
            'indianTotalAmount' => $indianTotalAmount,
            'indianUniqueCount' => $indianUniqueCount,
            'topIndianProduct' => $topIndianProduct,
            'overallTotalQty' => $overallTotalQty,
            'overallTotalAmount' => $overallTotalAmount,
            'overallUniqueCount' => $overallUniqueCount,
            'localShare' => $localShare,
            'indianShare' => $indianShare,
            'categories' => $categories,
        ]);
    }
}
