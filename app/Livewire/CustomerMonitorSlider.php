<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\ProductDetail;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;

#[Title("Showroom Monitor Display | Jaffna Gold")]
#[Layout("components.layouts.blank")] // We will ensure a clean standalone layout is used or render full standalone HTML
class CustomerMonitorSlider extends Component
{
    public $slideInterval = 6; // seconds per slide
    public $currentIndex = 0;
    public $productsChecksum = '';

    public function nextSlide($total)
    {
        if ($total > 0) {
            $this->currentIndex = ($this->currentIndex + 1) % $total;
        }
    }

    public function prevSlide($total)
    {
        if ($total > 0) {
            $this->currentIndex = ($this->currentIndex - 1 + $total) % $total;
        }
    }

    public function setSlide($index)
    {
        $this->currentIndex = (int) $index;
    }

    public function render()
    {
        // Fetch active products marked for customer monitor slider
        $rawProducts = ProductDetail::where('in_slider', true)
            ->where('status', 'active')
            ->with(['price', 'category', 'brand'])
            ->orderBy('updated_at', 'desc')
            ->get();

        $currentChecksum = md5($rawProducts->map(function ($p) {
            return $p->id . '-' . $p->updated_at . '-' . $p->discount_percent . '-' . ($p->price?->updated_at ?? '');
        })->implode('|'));

        if ($this->productsChecksum !== '' && $this->productsChecksum !== $currentChecksum) {
            $this->dispatch('slider-products-changed');
        }
        $this->productsChecksum = $currentChecksum;

        $products = $rawProducts->map(function ($product) {
            $rawImage = method_exists($product, 'getRawOriginal') ? $product->getRawOriginal('image') : null;
                $imageUrl = ProductDetail::resolveImageUrl($rawImage ?? $product->image);

                $priceRecord = $product->price;
                $origPrice = 0.0;
                if ($priceRecord) {
                    if ((float)$priceRecord->retail_price > 0) {
                        $origPrice = (float)$priceRecord->retail_price;
                    } elseif ((float)$priceRecord->selling_price > 0) {
                        $origPrice = (float)$priceRecord->selling_price;
                    } elseif ((float)$priceRecord->supplier_price > 0) {
                        $origPrice = (float)$priceRecord->supplier_price;
                    } elseif ((float)$priceRecord->system_cost > 0) {
                        $origPrice = (float)$priceRecord->system_cost;
                    }
                }

                $discountPercent = (float) ($product->discount_percent ?? 0);
                $finalPrice = $origPrice;
                $savings = 0;

                if ($discountPercent > 0 && $origPrice > 0) {
                    $finalPrice = round($origPrice * (1 - ($discountPercent / 100)), 2);
                    $savings = $origPrice - $finalPrice;
                } elseif ($product->price?->discount_price > 0 && $product->price->discount_price < $origPrice) {
                    $finalPrice = (float) $product->price->discount_price;
                    $savings = $origPrice - $finalPrice;
                    $discountPercent = round(($savings / $origPrice) * 100, 1);
                }

                $productUrl = (!empty($product->slug) && \Illuminate\Support\Facades\Route::has('product-detail.slug'))
                    ? route('product-detail.slug', ['slug' => $product->slug])
                    : url('/product-detail?id=' . $product->id);

                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'code' => $product->code,
                    'category' => $product->category?->category_name ?? 'Jewelry Collection',
                    'brand' => $product->brand?->brand_name ?? 'Jaffna Gold',
                    'model' => $product->model,
                    'description' => $product->description,
                    'image' => $imageUrl,
                    'original_price' => $origPrice,
                    'discount_percent' => $discountPercent,
                    'final_price' => $finalPrice,
                    'savings' => $savings,
                    'product_url' => $productUrl,
                ];
            });

        $sliderList = $products;
        $totalOriginal = $products->count();

        // Ensure at least 30 slides for Swiper infinite loop without loop warnings on any screen size (TV, 4K, 8K)
        if ($totalOriginal > 0 && $totalOriginal < 30) {
            $multiplier = (int) ceil(30 / $totalOriginal);
            $duplicated = collect();
            for ($i = 0; $i < $multiplier; $i++) {
                $duplicated = $duplicated->concat($products->map(function ($item) use ($i) {
                    $item['slide_key'] = $item['id'] . '-loop-' . $i;
                    return $item;
                }));
            }
            $sliderList = $duplicated;
        } else {
            $sliderList = $sliderList->map(function ($item) {
                $item['slide_key'] = 'slide-' . $item['id'];
                return $item;
            });
        }

        return view('livewire.customer-monitor-slider', [
            'sliderProducts' => $sliderList,
            'totalSlides' => $sliderList->count(),
            'totalOriginal' => $totalOriginal,
            'productsChecksum' => $currentChecksum,
        ])->layout('components.layouts.blank');
    }
}
