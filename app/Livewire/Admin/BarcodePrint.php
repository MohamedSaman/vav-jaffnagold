<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Title;
use App\Models\ProductDetail;
use App\Models\Setting;
use Illuminate\Support\Facades\Auth;
use App\Livewire\Concerns\WithDynamicLayout;

#[Title("Barcode Print")]
class BarcodePrint extends Component
{
    use WithDynamicLayout;
    use WithPagination;

    public $search = '';
    public $perPage = 25;

    // Selected product IDs for printing
    public $selectedProducts = [];
    public $selectAll = false;

    // Instant barcode print modal
    public $showLookupModal = false;
    public $lookupCode = '';
    public $lookupProduct = null;
    public $lookupProducts = [];

    public function openLookupModal()
    {
        $this->reset(['lookupCode', 'lookupProduct', 'lookupProducts']);
        $this->showLookupModal = true;
        $this->js("setTimeout(() => { const el = document.getElementById('lookup-code-input'); if(el){ el.focus(); el.select(); } }, 120);");
    }

    public function closeLookupModal()
    {
        $this->showLookupModal = false;
        $this->reset(['lookupCode', 'lookupProduct', 'lookupProducts']);
    }

    public function searchProduct()
    {
        $code = trim($this->lookupCode);
        if (empty($code)) {
            $this->lookupProducts = [];
            $this->lookupProduct = null;
            $this->js("setTimeout(() => { const el = document.getElementById('lookup-code-input'); if(el){ el.focus(); el.select(); } }, 80);");
            return;
        }

        $products = ProductDetail::leftJoin('product_prices', function ($join) {
            $join->on('product_details.id', '=', 'product_prices.product_id')
                ->where('product_prices.pricing_mode', '=', 'single')
                ->whereNull('product_prices.variant_id');
        })
            ->leftJoin('product_stocks', function ($join) {
                $join->on('product_details.id', '=', 'product_stocks.product_id')
                    ->whereNull('product_stocks.variant_id');
            })
            ->select('product_details.*', 'product_prices.retail_price', 'product_stocks.available_stock')
            ->where(function ($q) use ($code) {
                $q->where('product_details.code', 'like', "%{$code}%")
                    ->orWhere('product_details.barcode', 'like', "%{$code}%")
                    ->orWhere('product_details.name', 'like', "%{$code}%");
            })
            ->orderBy('product_details.id', 'desc')
            ->get();

        if ($products->isNotEmpty()) {
            $this->lookupProducts = $products->map(function ($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'code' => $product->code,
                    'size' => $product->size ?? null,
                    'barcode' => $product->barcode,
                    'retail_price' => number_format($product->retail_price ?? 0, 2),
                    'available_stock' => $product->available_stock ?? 0,
                    'image' => $product->image,
                ];
            })->toArray();
            $this->lookupProduct = $this->lookupProducts[0] ?? null;
        } else {
            $this->lookupProducts = [];
            $this->lookupProduct = null;
            $this->js("Swal.fire({icon: 'error', title: 'Not Found', text: 'No product found with this name, code, or barcode.', timer: 2000, showConfirmButton: false})");
        }

        $this->js("setTimeout(() => { const el = document.getElementById('lookup-code-input'); if(el){ el.focus(); el.select(); } }, 80);");
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatedSelectAll($value)
    {
        if ($value) {
            // Select all unprinted barcode products (current page visible IDs)
            $this->selectedProducts = $this->getUnprintedProducts()
                ->pluck('id')
                ->map(fn($id) => (string) $id)
                ->toArray();
        } else {
            $this->selectedProducts = [];
        }
    }

    /**
     * Get products with unprinted barcodes (barcode_printed = 'No')
     */
    private function getUnprintedProducts()
    {
        return ProductDetail::leftJoin('product_prices', function ($join) {
            $join->on('product_details.id', '=', 'product_prices.product_id')
                ->where('product_prices.pricing_mode', '=', 'single')
                ->whereNull('product_prices.variant_id');
        })
            ->leftJoin('product_stocks', function ($join) {
                $join->on('product_details.id', '=', 'product_stocks.product_id')
                    ->whereNull('product_stocks.variant_id');
            })
            ->select('product_details.*', 'product_prices.retail_price', 'product_stocks.available_stock')
            ->where('product_details.barcode_printed', 'No')
            ->whereNotNull('product_details.barcode')
            ->where('product_details.barcode', '!=', '')
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('product_details.name', 'like', '%' . $this->search . '%')
                        ->orWhere('product_details.code', 'like', '%' . $this->search . '%')
                        ->orWhere('product_details.barcode', 'like', '%' . $this->search . '%');
                });
            })
            ->orderBy('product_details.created_at', 'desc')
            ->get();
    }

    /**
     * Mark selected products as printed
     */
    public function markAsPrinted()
    {
        if (empty($this->selectedProducts)) {
            $this->js("Swal.fire({icon: 'warning', title: 'No Selection', text: 'Please select at least one product to mark as printed.', timer: 2000, showConfirmButton: false})");
            return;
        }

        ProductDetail::whereIn('id', $this->selectedProducts)
            ->update(['barcode_printed' => 'Yes']);

        $count = count($this->selectedProducts);
        $this->selectedProducts = [];
        $this->selectAll = false;

        $this->js("Swal.fire({icon: 'success', title: 'Marked as Printed', text: '{$count} product(s) barcode marked as printed.', timer: 2000, showConfirmButton: false})");
    }

    /**
     * Mark a single product as printed
     */
    public function markSingleAsPrinted($productId)
    {
        ProductDetail::where('id', $productId)->update(['barcode_printed' => 'Yes']);

        // Remove from selection if it was selected
        $this->selectedProducts = array_values(array_diff($this->selectedProducts, [(string) $productId]));

        $this->js("Swal.fire({icon: 'success', title: 'Done', text: 'Barcode marked as printed.', timer: 1500, showConfirmButton: false})");
    }

    private function getLabelSettings(): array
    {
        $defaults = [
            'label_width'           => 48,
            'label_height'          => 12,
            'label_padding'         => 1,
            'label_text_width'      => 35,
            'label_tail_width'      => 22,
            'label_tail_height'     => 4,
            'label_font_family'     => 'Courier New',
            'label_font_shop'       => 6,
            'label_font_price'      => 8,
            'label_font_barcode'    => 5,
            'label_qr_size'         => 11,
            'label_barcode_type'    => 'datamatrix', // 'datamatrix' (ECC 200), 'qrcode_l', 'qrcode_m', 'code128'
            'label_show_shop'       => true,
            'label_show_barcode_text' => true,
            'label_show_qr'         => true,
        ];

        $boolKeys   = ['label_show_shop', 'label_show_barcode_text', 'label_show_qr'];
        $stringKeys = ['label_font_family', 'label_barcode_type'];
        $keys = array_keys($defaults);

        $rows = Setting::whereIn('key', $keys)->pluck('value', 'key');

        foreach ($defaults as $key => $def) {
            if ($rows->has($key)) {
                if (in_array($key, $boolKeys)) {
                    $defaults[$key] = (bool) $rows[$key];
                } elseif (in_array($key, $stringKeys)) {
                    $defaults[$key] = $rows[$key];
                } else {
                    $defaults[$key] = (float) $rows[$key];
                }
            }
        }

        return $defaults;
    }

    public function render()
    {
        $products = ProductDetail::leftJoin('product_prices', function ($join) {
            $join->on('product_details.id', '=', 'product_prices.product_id')
                ->where('product_prices.pricing_mode', '=', 'single')
                ->whereNull('product_prices.variant_id');
        })
            ->leftJoin('product_stocks', function ($join) {
                $join->on('product_details.id', '=', 'product_stocks.product_id')
                    ->whereNull('product_stocks.variant_id');
            })
            ->select('product_details.*', 'product_prices.retail_price', 'product_stocks.available_stock')
            ->where('product_details.barcode_printed', 'No')
            ->whereNotNull('product_details.barcode')
            ->where('product_details.barcode', '!=', '')
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('product_details.name', 'like', '%' . $this->search . '%')
                        ->orWhere('product_details.code', 'like', '%' . $this->search . '%')
                        ->orWhere('product_details.barcode', 'like', '%' . $this->search . '%');
                });
            })
            ->orderBy('product_details.created_at', 'desc')
            ->paginate($this->perPage);

        $totalUnprinted = ProductDetail::where('barcode_printed', 'No')
            ->whereNotNull('barcode')
            ->where('barcode', '!=', '')
            ->count();

        return view('livewire.admin.barcode-print', [
            'products' => $products,
            'totalUnprinted' => $totalUnprinted,
            'labelSettings' => $this->getLabelSettings(),
        ])->layout($this->layout);
    }
}
