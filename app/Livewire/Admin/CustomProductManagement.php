<?php

namespace App\Livewire\Admin;

use App\Models\CustomProduct;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

#[Title('Custom Jewelry Products & Price Range Management')]
#[Layout('components.layouts.admin')]
class CustomProductManagement extends Component
{
    use WithFileUploads;
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    // Filters & Search
    public $search = '';

    public $selectedCategory = 'all';

    public $perPage = 15;

    // Edit / Create Product Modal
    public $showProductModal = false;

    public $isEditing = false;

    public $editingProductId = null;

    // Form fields
    public $name = '';

    public $tamil_name = '';

    public $category = 'kodi';

    public $category_label = 'Kodi (Traditional Chains)';

    public $purity = '22K (916 Hallmarked)';

    public $description = '';

    public $pown_options_input = '5, 6, 7, 8';

    public $min_pown = 5.00;

    public $max_pown = 8.00;

    public $base_pown = 5.00;

    public $price_per_pown_estimate = 230000.00;

    public $price_range_min = 1150000.00;

    public $price_range_max = 1840000.00;

    public $making_days = 7;

    public $is_active = true;

    public $is_featured = true;

    public $sort_order = 0;

    public $image_file = null;

    public $current_image = '';

    public $gallery_files = [];

    public $current_gallery = [];

    protected $queryString = [
        'search' => ['except' => ''],
        'selectedCategory' => ['except' => 'all'],
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingSelectedCategory()
    {
        $this->resetPage();
    }

    public function removeGalleryImage(int $index): void
    {
        if (isset($this->current_gallery[$index])) {
            unset($this->current_gallery[$index]);
            $this->current_gallery = array_values($this->current_gallery);
        }
    }

    public function removeNewGalleryFile(int $index): void
    {
        if (isset($this->gallery_files[$index])) {
            unset($this->gallery_files[$index]);
            $this->gallery_files = array_values($this->gallery_files);
        }
    }

    public function removeMainImage(): void
    {
        $this->current_image = '';
        $this->image_file = null;
    }

    public function openCreateModal()
    {
        $this->resetProductForm();
        $this->isEditing = false;
        $this->showProductModal = true;
    }

    public function openEditModal($id)
    {
        $product = CustomProduct::findOrFail($id);
        $this->editingProductId = $product->id;
        $this->name = $product->name;
        $this->tamil_name = $product->tamil_name ?? '';
        $this->category = $product->category;
        $this->category_label = $product->category_label;
        $this->purity = $product->purity;
        $this->description = $product->description ?? '';
        $this->pown_options_input = is_array($product->pown_options) ? implode(', ', $product->pown_options) : '20, 22, 24, 26';
        $this->min_pown = (float) ($product->min_pown ?? 0);
        $this->max_pown = (float) ($product->max_pown ?? 0);
        $this->base_pown = (float) ($product->base_pown ?? 0);
        $this->price_per_pown_estimate = (float) ($product->price_per_pown_estimate ?? 0);
        $this->price_range_min = (float) $product->price_range_min;
        $this->price_range_max = (float) $product->price_range_max;
        $this->making_days = (int) $product->making_days;
        $this->is_active = (bool) $product->is_active;
        $this->is_featured = (bool) $product->is_featured;
        $this->sort_order = (int) $product->sort_order;
        $this->current_image = $product->image ?? '';
        $this->current_gallery = is_array($product->gallery_images) ? $product->gallery_images : [];
        $this->image_file = null;
        $this->gallery_files = [];

        $this->isEditing = true;
        $this->showProductModal = true;
    }

    public function closeProductModal()
    {
        $this->showProductModal = false;
        $this->resetProductForm();
    }

    public function resetProductForm()
    {
        $this->editingProductId = null;
        $this->name = '';
        $this->tamil_name = '';
        $this->category = 'kodi';
        $this->category_label = 'Kodi (Traditional Chains)';
        $this->purity = 'Gold Covering (உயர் கவரிங்)';
        $this->description = '';
        $this->pown_options_input = '20, 22, 24, 26';
        $this->min_pown = 0;
        $this->max_pown = 0;
        $this->base_pown = 0;
        $this->price_per_pown_estimate = 0;
        $this->price_range_min = 4500.00;
        $this->price_range_max = 8500.00;
        $this->making_days = 7;
        $this->is_active = true;
        $this->is_featured = true;
        $this->sort_order = 0;
        $this->image_file = null;
        $this->current_image = '';
        $this->gallery_files = [];
        $this->current_gallery = [];
        $this->resetErrorBag();
    }

    public function saveProduct()
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'tamil_name' => 'nullable|string|max:255',
            'category' => 'required|string|max:100',
            'category_label' => 'required|string|max:255',
            'purity' => 'required|string|max:100',
            'description' => 'nullable|string',
            'pown_options_input' => 'nullable|string',
            'price_range_min' => 'required|numeric|min:0',
            'price_range_max' => 'required|numeric|gte:price_range_min',
            'making_days' => 'required|integer|min:1|max:90',
            'sort_order' => 'nullable|integer',
            'image_file' => 'nullable|file|mimes:jpeg,png,jpg,webp,gif,heic|max:10240',
            'gallery_files' => 'nullable|array',
            'gallery_files.*' => 'nullable|file|mimes:jpeg,png,jpg,webp,gif,heic|max:10240',
        ]);

        // Parse options into array of sizes/choices
        $pownOptions = array_values(array_filter(array_map(function ($val) {
            $val = trim($val);

            return $val !== '' ? $val : null;
        }, explode(',', (string) $this->pown_options_input))));

        if (empty($pownOptions)) {
            $pownOptions = [$this->min_pown, $this->max_pown];
        }

        $imagePath = $this->current_image;
        if ($this->image_file) {
            $imagePath = $this->image_file->store('custom-products', 'public');
            $imagePath = 'storage/'.$imagePath;
        }

        // Process gallery images
        $galleryPaths = is_array($this->current_gallery) ? $this->current_gallery : [];
        if (! empty($this->gallery_files) && is_array($this->gallery_files)) {
            foreach ($this->gallery_files as $file) {
                if ($file) {
                    $stored = $file->store('custom-products', 'public');
                    $galleryPaths[] = 'storage/'.$stored;
                }
            }
        }
        $galleryPaths = array_values(array_unique(array_filter($galleryPaths)));

        // If main image was not provided, but gallery has images, fallback to the first gallery image
        if (empty($imagePath) && ! empty($galleryPaths)) {
            $imagePath = $galleryPaths[0];
        }

        $slug = Str::slug($this->name);
        if ($this->isEditing) {
            $product = CustomProduct::findOrFail($this->editingProductId);
            if ($product->name !== $this->name) {
                $slug = $this->generateUniqueSlug($this->name, $product->id);
            } else {
                $slug = $product->slug;
            }

            $product->update([
                'name' => $this->name,
                'tamil_name' => $this->tamil_name ?: null,
                'slug' => $slug,
                'category' => $this->category,
                'category_label' => $this->category_label,
                'purity' => $this->purity,
                'description' => $this->description ?: null,
                'pown_options' => $pownOptions,
                'min_pown' => $this->min_pown,
                'max_pown' => $this->max_pown,
                'base_pown' => $this->base_pown,
                'price_per_pown_estimate' => $this->price_per_pown_estimate,
                'price_range_min' => $this->price_range_min,
                'price_range_max' => $this->price_range_max,
                'making_days' => $this->making_days,
                'image' => $imagePath ?: null,
                'gallery_images' => $galleryPaths,
                'is_active' => $this->is_active,
                'is_featured' => $this->is_featured,
                'sort_order' => $this->sort_order ?: 0,
            ]);

            $message = 'Custom Product updated successfully!';
        } else {
            $slug = $this->generateUniqueSlug($this->name);

            CustomProduct::create([
                'name' => $this->name,
                'tamil_name' => $this->tamil_name ?: null,
                'slug' => $slug,
                'category' => $this->category,
                'category_label' => $this->category_label,
                'purity' => $this->purity,
                'description' => $this->description ?: null,
                'pown_options' => $pownOptions,
                'min_pown' => $this->min_pown,
                'max_pown' => $this->max_pown,
                'base_pown' => $this->base_pown,
                'price_per_pown_estimate' => $this->price_per_pown_estimate,
                'price_range_min' => $this->price_range_min,
                'price_range_max' => $this->price_range_max,
                'making_days' => $this->making_days,
                'image' => $imagePath ?: ($galleryPaths[0] ?? 'images/website images/img/img_1.jpeg'),
                'gallery_images' => $galleryPaths,
                'is_active' => $this->is_active,
                'is_featured' => $this->is_featured,
                'sort_order' => $this->sort_order ?: 0,
            ]);

            $message = 'New Custom Product created successfully!';
        }

        $this->closeProductModal();
        $this->js("Swal.fire('Success!', '{$message}', 'success')");
    }

    private function generateUniqueSlug(string $name, $ignoreId = null): string
    {
        $base = Str::slug($name);
        $slug = $base;
        $counter = 1;

        while (CustomProduct::where('slug', $slug)->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))->exists()) {
            $slug = $base.'-'.$counter++;
        }

        return $slug;
    }

    public function toggleActive($id)
    {
        $product = CustomProduct::findOrFail($id);
        $product->is_active = ! $product->is_active;
        $product->save();

        $status = $product->is_active ? 'activated' : 'deactivated';
        $this->dispatch('swal:alert', [
            'icon' => 'success',
            'title' => 'Status Updated',
            'text' => "Product {$product->name} is now {$status}.",
        ]);
    }

    public function toggleFeatured($id)
    {
        $product = CustomProduct::findOrFail($id);
        $product->is_featured = ! $product->is_featured;
        $product->save();
    }

    public function render()
    {
        $query = CustomProduct::withCount('inquiries')->orderBy('sort_order', 'asc')->orderBy('name', 'asc');

        if ($this->search) {
            $s = '%'.trim($this->search).'%';
            $query->where(function ($q) use ($s) {
                $q->where('name', 'like', $s)
                    ->orWhere('tamil_name', 'like', $s)
                    ->orWhere('category_label', 'like', $s);
            });
        }

        if ($this->selectedCategory !== 'all') {
            $query->where('category', $this->selectedCategory);
        }

        $products = $query->paginate($this->perPage);

        return view('livewire.admin.custom-product-management', [
            'products' => $products,
        ]);
    }
}
