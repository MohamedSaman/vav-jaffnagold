<?php

namespace App\Livewire\Ecommerce;

use App\Livewire\Ecommerce\Concerns\InteractsWithStorefront;
use App\Models\CustomProduct;
use App\Models\CustomProductInquiry;
use App\Services\SmsService;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Title('Custom Gold Covering & Micro Plated Jewelry | Jaffna Gold')]
#[Layout('components.layouts.webapp')]
class CustomProducts extends Component
{
    use InteractsWithStorefront;
    use WithFileUploads;

    #[Url(as: 'category')]
    public string $selectedCategory = 'all';

    #[Url(as: 'price')]
    public string $selectedPriceRange = 'all';

    #[Url(as: 'pown')]
    public string $selectedPownRange = 'all';

    #[Url(as: 'search')]
    public string $search = '';

    // ─── Custom Gold Covering Design & Price Estimator ─────────────────
    public string $calcCategory = 'kodi';

    public string $calcLengthSize = '24"';

    public string $calcPlatingType = 'standard';

    public float $calcPriceMin = 4500.0;

    public float $calcPriceMax = 8500.0;

    // ─── Customizer & Inquiry Modal State ─────────────────────────────
    public bool $showInquiryModal = false;

    public ?int $selectedProductId = null;

    public ?CustomProduct $activeProduct = null;

    public string $inquirySize = '24 inches';

    public string $inquiryPlating = 'standard';

    public string $inquiryCustomerName = '';

    public string $inquiryCustomerPhone = '';

    public string $inquiryCustomerEmail = '';

    public string $inquiryCity = 'Jaffna';

    public string $inquiryNotes = '';

    public $reference_image = null;

    public array $citySuggestions = [];

    public bool $showCitySuggestions = false;

    public bool $inquirySubmitted = false;

    public string $submittedInquiryNumber = '';

    public string $whatsAppUrl = '';

    // Sri Lankan major cities list with focus on Northern Province hubs
    protected array $sriLankanCities = [
        'Jaffna', 'Nallur', 'Chavakachcheri', 'Point Pedro', 'Kopay', 'Manipay', 'Chankanai', 'Tellippalai',
        'Karainagar', 'Velanai', 'Kayts', 'Vaddukoddai', 'Kilinochchi', 'Vavuniya', 'Mullaitivu', 'Mannar',
        'Colombo', 'Dehiwala-Mount Lavinia', 'Wellawatte', 'Kotahena', 'Wattala', 'Negombo', 'Kandy',
        'Galle', 'Trincomalee', 'Batticaloa', 'Gampaha', 'Kurunegala', 'Anuradhapura', 'Matara', 'Ratnapura',
    ];

    public function mount(): void
    {
        $this->updateCalculator();
    }

    public function updatedCalcCategory(): void
    {
        // Adjust default size based on category
        $this->calcLengthSize = match ($this->calcCategory) {
            'bangle' => '2.6',
            'ring' => '16',
            'bridal_thali' => 'Bridal Set (26")',
            default => '24"',
        };
        $this->updateCalculator();
    }

    public function updatedCalcLengthSize(): void
    {
        $this->updateCalculator();
    }

    public function updatedCalcPlatingType(): void
    {
        $this->updateCalculator();
    }

    public function updateCalculator(): void
    {
        $base = match ($this->calcCategory) {
            'ring' => ['min' => 1600.0, 'max' => 3200.0],
            'bangle' => ['min' => 3200.0, 'max' => 6500.0],
            'bridal_thali' => ['min' => 12500.0, 'max' => 24500.0],
            default => ['min' => 4500.0, 'max' => 8500.0], // kodi
        };

        $platingMultiplier = match ($this->calcPlatingType) {
            'double_coat' => 1.25,  // 1-Year color guarantee double coat
            'royal_finish' => 1.40, // Royal antique / high luster
            default => 1.00,        // Standard micro gold covering
        };

        $sizeMultiplier = 1.00;
        if ($this->calcCategory === 'kodi') {
            $sizeMultiplier = match ($this->calcLengthSize) {
                '20"' => 0.90,
                '22"' => 0.95,
                '24"' => 1.00,
                '26"' => 1.08,
                '28"', '30"' => 1.15,
                default => 1.00,
            };
        }

        $this->calcPriceMin = round($base['min'] * $platingMultiplier * $sizeMultiplier);
        $this->calcPriceMax = round($base['max'] * $platingMultiplier * $sizeMultiplier);
    }

    public function setCategory(string $category): void
    {
        $this->selectedCategory = $category;
    }

    public function setPriceRange(string $range): void
    {
        $this->selectedPriceRange = $range;
    }

    public function resetFilters(): void
    {
        $this->selectedCategory = 'all';
        $this->selectedPriceRange = 'all';
        $this->selectedPownRange = 'all';
        $this->search = '';
    }

    // ─── Modal Actions ────────────────────────────────────────────────
    public function openInquiryModal(int $productId, $size = null): void
    {
        $this->activeProduct = CustomProduct::find($productId);
        if (! $this->activeProduct) {
            return;
        }

        $this->selectedProductId = $productId;

        if ($size) {
            $this->inquirySize = is_numeric($size) ? ($size.($this->activeProduct->category === 'kodi' ? ' inches' : '')) : (string) $size;
        } else {
            $this->inquirySize = match ($this->activeProduct->category) {
                'bangle' => 'Size 2.6',
                'ring' => 'Size 16',
                'bridal_thali' => 'Standard Ceremonial 26"',
                default => '24 inches',
            };
        }

        $this->inquiryPlating = 'standard';
        $this->inquiryNotes = '';
        $this->reference_image = null;
        $this->inquirySubmitted = false;
        $this->submittedInquiryNumber = '';
        $this->whatsAppUrl = '';

        // Auto-fill customer info if logged in
        if (auth()->check()) {
            $user = auth()->user();
            $this->inquiryCustomerName = $user->name ?? '';
            $this->inquiryCustomerPhone = $user->contact ?? '';
            $this->inquiryCustomerEmail = $user->email ?? '';
        }

        $this->showInquiryModal = true;
    }

    public function closeInquiryModal(): void
    {
        $this->showInquiryModal = false;
        $this->activeProduct = null;
        $this->selectedProductId = null;
        $this->reference_image = null;
        $this->inquirySubmitted = false;
        $this->showCitySuggestions = false;
    }

    public function selectSizeOption(string $size): void
    {
        $this->inquirySize = $size;
    }

    public function updatedInquiryCity(): void
    {
        $query = trim($this->inquiryCity);
        if (strlen($query) < 1) {
            $this->citySuggestions = [];
            $this->showCitySuggestions = false;

            return;
        }

        $this->citySuggestions = array_values(array_filter($this->sriLankanCities, function ($city) use ($query) {
            return str_starts_with(strtolower($city), strtolower($query)) || str_contains(strtolower($city), strtolower($query));
        }));

        $this->showCitySuggestions = count($this->citySuggestions) > 0;
    }

    public function selectCity(string $city): void
    {
        $this->inquiryCity = $city;
        $this->citySuggestions = [];
        $this->showCitySuggestions = false;
    }

    public function calculateEstimatedPrice(): float
    {
        if (! $this->activeProduct) {
            return $this->calcPriceMin;
        }

        $basePrice = (float) $this->activeProduct->price_range_min ?: 4500.0;

        $platingMultiplier = match ($this->inquiryPlating) {
            'double_coat' => 1.25,
            'royal_finish' => 1.40,
            default => 1.00,
        };

        return round($basePrice * $platingMultiplier);
    }

    public function submitInquiry(): void
    {
        $this->validate([
            'inquiryCustomerName' => 'required|min:2|max:100',
            'inquiryCustomerPhone' => 'required|min:9|max:15',
            'inquiryCity' => 'required|min:2',
            'reference_image' => 'nullable|file|mimes:jpeg,png,jpg,webp,heic|max:10240',
        ], [
            'inquiryCustomerName.required' => 'Please enter your name.',
            'inquiryCustomerPhone.required' => 'Please provide a contact number.',
            'inquiryCity.required' => 'Please provide your city/location.',
            'reference_image.max' => 'The image size should not exceed 10MB.',
            'reference_image.mimes' => 'The uploaded file must be an image (JPEG, PNG, WebP, HEIC).',
        ]);

        if (! $this->activeProduct) {
            return;
        }

        $inquiryNumber = CustomProductInquiry::generateInquiryNumber();
        $estimatedPrice = $this->calculateEstimatedPrice();
        $cleanPhone = preg_replace('/[^0-9+]/', '', $this->inquiryCustomerPhone);

        $platingLabel = match ($this->inquiryPlating) {
            'double_coat' => 'Premium Double-Coat Micro Plated (1-Year Color Guarantee)',
            'royal_finish' => 'Royal High-Luster Antique Plated',
            default => 'Standard Micro Gold Covering',
        };

        $referenceImagePath = null;
        if ($this->reference_image) {
            $stored = $this->reference_image->store('custom-inquiries', 'public');
            $referenceImagePath = 'storage/'.$stored;
        }

        $inquiry = CustomProductInquiry::create([
            'inquiry_number' => $inquiryNumber,
            'custom_product_id' => $this->activeProduct->id,
            'product_name' => $this->activeProduct->name.($this->activeProduct->tamil_name ? ' ('.$this->activeProduct->tamil_name.')' : ''),
            'category' => $this->activeProduct->category,
            'customer_name' => $this->inquiryCustomerName,
            'customer_phone' => $cleanPhone,
            'customer_email' => $this->inquiryCustomerEmail ?: null,
            'city' => $this->inquiryCity,
            'selected_pown' => 0.00,
            'selected_grams' => 0.00,
            'size_specifications' => ($this->inquirySize ? 'Size/Length: '.$this->inquirySize : 'Standard Size').' | Plating: '.$platingLabel,
            'gold_purity' => $this->activeProduct->purity ?: 'Micro Gold Plated (உயர் கவரிங்)',
            'estimated_price' => $estimatedPrice,
            'customer_notes' => $this->inquiryNotes ?: null,
            'reference_image' => $referenceImagePath,
            'status' => 'new',
        ]);

        $this->submittedInquiryNumber = $inquiryNumber;

        // ─── Trigger SMS Notification via SMSlenz ─────────────────────
        try {
            $smsMessage = sprintf(
                'Jaffna Gold: Vanakkam %s! Your custom gold covering inquiry %s for %s (%s) is received. Est: LKR %s. Our team will contact you shortly. Call: 0771234567',
                $this->inquiryCustomerName,
                $inquiryNumber,
                $this->activeProduct->name,
                $this->inquirySize ?: 'Standard',
                number_format($estimatedPrice, 0)
            );

            SmsService::sendSms($cleanPhone, $smsMessage);
        } catch (\Throwable $e) {
            Log::error('Failed sending custom inquiry SMS: '.$e->getMessage());
        }

        // ─── Generate WhatsApp Link ───────────────────────────────────
        $refPhotoNote = $referenceImagePath ? "\n*Reference Photo:* Attached in system inquiry" : '';
        $waText = urlencode(sprintf(
            "Hello Jaffna Gold! I am inquiring about custom gold covering jewelry:\n\n*Reference:* %s\n*Item:* %s\n*Plating:* %s\n*Size/Length:* %s\n*Estimated Price:* LKR %s\n*Notes:* %s%s\n*Name:* %s\n*City:* %s\n*Phone:* %s",
            $inquiryNumber,
            $this->activeProduct->name.($this->activeProduct->tamil_name ? ' ('.$this->activeProduct->tamil_name.')' : ''),
            $platingLabel,
            $this->inquirySize ?: 'Standard',
            number_format($estimatedPrice, 0),
            $this->inquiryNotes ?: 'Standard fit',
            $refPhotoNote,
            $this->inquiryCustomerName,
            $this->inquiryCity,
            $cleanPhone
        ));

        $this->whatsAppUrl = 'https://wa.me/94770000000?text='.$waText;
        $this->inquirySubmitted = true;
    }

    public function render()
    {
        $query = CustomProduct::query()->active()->orderBy('sort_order', 'asc');

        if ($this->selectedCategory !== 'all') {
            $query->where('category', $this->selectedCategory);
        }

        // Price Range Filter
        $priceRange = $this->selectedPriceRange !== 'all' ? $this->selectedPriceRange : $this->selectedPownRange;
        if ($priceRange !== 'all') {
            switch ($priceRange) {
                case 'under_3000':
                case 'under_2':
                    $query->where('price_range_min', '<', 3000.0);
                    break;
                case '3000_to_6000':
                case '2_to_4':
                    $query->where(function ($q) {
                        $q->whereBetween('price_range_min', [3000.0, 6000.0])
                            ->orWhereBetween('price_range_max', [3000.0, 6000.0]);
                    });
                    break;
                case '6000_to_10000':
                case '5_to_7':
                    $query->where(function ($q) {
                        $q->whereBetween('price_range_min', [6000.0, 10000.0])
                            ->orWhereBetween('price_range_max', [6000.0, 10000.0]);
                    });
                    break;
                case '10000_plus':
                case '8_plus':
                    $query->where('price_range_max', '>=', 10000.0);
                    break;
            }
        }

        if (! empty($this->search)) {
            $term = '%'.trim($this->search).'%';
            $query->where(function ($q) use ($term) {
                $q->where('name', 'like', $term)
                    ->orWhere('tamil_name', 'like', $term)
                    ->orWhere('description', 'like', $term)
                    ->orWhere('category_label', 'like', $term);
            });
        }

        $customProducts = $query->get();

        $seoData = [
            'title' => 'Custom Gold Covering & Plated Jewelry | Star Kodi, SL Bangles & Thakar Rings',
            'description' => 'Explore premium handcrafted Sri Lankan gold covering & micro plated jewelry: Traditional Star Kodi, Veera Kodi, Lock SL Bangles, and Thakar Rings crafted in authentic Jaffna designs with long-lasting finish.',
            'keywords' => 'custom gold covering Sri Lanka, gold plated jewelry, star kodi covering, veera kodi plated, lock sl bangle covering, takar ring covering, jaffna gold covering, micro gold plating sri lanka',
            'canonical' => route('custom-products'),
        ];

        return view('livewire.ecommerce.custom-products', [
            'products' => $customProducts,
        ])->layout('components.layouts.webapp', $this->sharedLayoutData($seoData));
    }
}
