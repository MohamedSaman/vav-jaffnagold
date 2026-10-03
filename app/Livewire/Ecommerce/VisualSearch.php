<?php

namespace App\Livewire\Ecommerce;

use App\Livewire\Ecommerce\Concerns\InteractsWithStorefront;
use App\Livewire\Ecommerce\Concerns\PresentsProductCards;
use App\Models\CategoryList;
use App\Models\ProductDetail;
use App\Services\ImageSearch\ImageSearchClient;
use App\Services\ImageSearch\ImageSearchException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;

/**
 * "Search by photo" — the customer-facing entry point for AI visual search.
 *
 * A customer uploads a picture they found on Facebook, Instagram, WhatsApp or
 * TikTok; the Python CLIP service returns the most visually similar products from
 * the catalogue, and they are rendered with the same cards used on the shop page.
 */
#[Title('Search by Photo')]
class VisualSearch extends Component
{
    use PresentsProductCards;
    use WithFileUploads;
    use InteractsWithStorefront;

    public ?TemporaryUploadedFile $photo = null;

    /** Optional category narrowing, chosen before or after the upload. */
    public string $categoryId = '';

    /** @var array<int,array<string,mixed>> Product cards in similarity order. */
    public array $results = [];

    public bool $hasSearched = false;

    public ?string $errorMessage = null;

    /** What the AI thinks the uploaded photo shows, e.g. "necklace". */
    public ?string $detectedType = null;

    public ?float $detectedConfidence = null;

    /** Preview URL of the uploaded photo, so it stays visible beside the results. */
    public ?string $previewUrl = null;

    public ?float $tookMs = null;

    public int $totalIndexed = 0;

    /**
     * Keeps the route resolvable (nav links, canonical URLs) while making the page
     * behave as if it does not exist when the feature is switched off.
     */
    public function mount(): void
    {
        abort_unless((bool) config('image_search.enabled'), 404);
    }

    /**
     * Validation runs before the file is ever handed to the AI service, so a
     * malformed or oversized upload is rejected by Laravel first.
     */
    protected function rules(): array
    {
        $upload = (array) config('image_search.upload');

        return [
            'photo' => [
                'required',
                'image',
                'mimes:' . implode(',', $upload['mimes'] ?? ['jpeg', 'png', 'webp']),
                'mimetypes:' . implode(',', $upload['mime_types'] ?? ['image/jpeg']),
                'max:' . ($upload['max_kilobytes'] ?? 5120),
                'dimensions:min_width=' . ($upload['min_dimension'] ?? 32)
                    . ',min_height=' . ($upload['min_dimension'] ?? 32)
                    . ',max_width=' . ($upload['max_dimension'] ?? 8000)
                    . ',max_height=' . ($upload['max_dimension'] ?? 8000),
            ],
            'categoryId' => ['nullable', 'string'],
        ];
    }

    protected function messages(): array
    {
        $maxMb = round(((int) config('image_search.upload.max_kilobytes', 5120)) / 1024, 1);

        return [
            'photo.required' => 'Please choose a photo to search with.',
            'photo.image' => 'That file is not an image. Please upload a JPG, PNG or WEBP photo.',
            'photo.mimes' => 'Only JPG, PNG, WEBP, GIF and BMP images are supported.',
            'photo.mimetypes' => 'That file does not look like a real image.',
            'photo.max' => "Please upload a photo smaller than {$maxMb} MB.",
            'photo.dimensions' => 'That image is too small or too large to search with.',
        ];
    }

    /**
     * Searching as soon as a file is chosen removes a click; a customer who has
     * just picked a photo has no other intent.
     */
    public function updatedPhoto(): void
    {
        $this->search();
    }

    /** Re-run the current photo against a different category. */
    public function updatedCategoryId(): void
    {
        if ($this->photo !== null) {
            $this->search();
        }
    }

    public function search(): void
    {
        $this->reset(['errorMessage', 'results', 'detectedType', 'detectedConfidence', 'tookMs']);

        try {
            $this->validate();
        } catch (ValidationException $exception) {
            // Drop the rejected file so the form does not sit in a broken state.
            $this->photo = null;
            $this->previewUrl = null;
            throw $exception;
        }

        if (! $this->withinRateLimit()) {
            return;
        }

        /** @var ImageSearchClient $client */
        $client = app(ImageSearchClient::class);

        try {
            $response = $client->searchByImage($this->photo, array_filter([
                'top_k' => (int) config('image_search.search.top_k', 20),
                'category_id' => $this->categoryId !== '' ? (int) $this->categoryId : null,
                'min_similarity' => config('image_search.search.min_similarity'),
                'strategy' => config('image_search.search.strategy'),
            ], static fn ($value) => $value !== null));
        } catch (ImageSearchException $exception) {
            Log::warning('Visual search failed', [
                'error' => $exception->errorCode,
                'message' => $exception->getMessage(),
            ]);

            $this->errorMessage = $exception->userMessage();
            $this->hasSearched = true;

            return;
        }

        $this->applyResponse($response);
    }

    /**
     * Turn the AI response into product cards.
     *
     * The AI knows nothing about price or stock, so the products are re-read from
     * MySQL — and re-filtered by the storefront's own visibility rules, which is
     * what stops an index that has drifted from exposing a hidden product.
     */
    private function applyResponse(array $response): void
    {
        $this->hasSearched = true;
        $this->tookMs = isset($response['took_ms']) ? (float) $response['took_ms'] : null;
        $this->totalIndexed = (int) ($response['total_indexed'] ?? 0);

        if (! empty($response['detected_type']['jewelry_type'])) {
            $this->detectedType = (string) $response['detected_type']['jewelry_type'];
            $this->detectedConfidence = (float) ($response['detected_type']['confidence'] ?? 0);
        }

        $matches = collect($response['results'] ?? [])
            ->filter(fn ($match) => isset($match['product_id']))
            ->keyBy(fn ($match) => (int) $match['product_id']);

        if ($matches->isEmpty()) {
            $this->results = [];

            return;
        }

        $products = ProductDetail::query()
            ->with([
                'category:id,category_name',
                'brand:id,brand_name',
                'price:product_id,retail_price,wholesale_price,selling_price,discount_price',
                'stock:product_id,available_stock',
            ])
            ->whereIn('id', $matches->keys()->all())
            ->where('status', 'active')
            ->where('website_live', true)
            ->get();

        // MySQL returns rows in its own order; similarity order is the whole
        // point of this feature, so re-sort by the AI's ranking.
        $this->results = $products
            ->map(function (ProductDetail $product) use ($matches) {
                $match = $matches->get($product->id, []);

                return array_merge($this->mapProductCard($product), [
                    'similarity' => round(((float) ($match['similarity'] ?? 0)) * 100, 1),
                    'score' => (float) ($match['score'] ?? 0),
                    'same_type' => (bool) ($match['same_type'] ?? false),
                ]);
            })
            ->sortByDesc('score')
            ->values()
            ->all();
    }

    /**
     * Throttle per session and IP.
     *
     * CLIP inference is CPU-bound, so an unthrottled endpoint is an easy way to
     * saturate the server. This keeps a scripted flood from degrading the site
     * for everyone else.
     */
    private function withinRateLimit(): bool
    {
        $limits = (array) config('image_search.search.rate_limit');
        $attempts = (int) ($limits['attempts'] ?? 20);
        $decay = (int) ($limits['decay_minutes'] ?? 1);

        $key = 'visual-search:' . (request()->ip() ?? 'unknown') . ':' . session()->getId();

        if (RateLimiter::tooManyAttempts($key, $attempts)) {
            $seconds = RateLimiter::availableIn($key);

            $this->errorMessage = "You have made a lot of searches. Please wait {$seconds} seconds and try again.";
            $this->hasSearched = true;

            return false;
        }

        RateLimiter::hit($key, $decay * 60);

        return true;
    }

    public function clearSearch(): void
    {
        $this->reset([
            'photo',
            'previewUrl',
            'results',
            'hasSearched',
            'errorMessage',
            'detectedType',
            'detectedConfidence',
            'tookMs',
            'categoryId',
        ]);

        $this->resetValidation();
    }

    /** Category options, limited to categories that have live products. */
    private function categoryOptions()
    {
        return CategoryList::query()
            ->whereHas('products', fn ($query) => $query->where('status', 'active')->where('website_live', true))
            ->orderBy('category_name')
            ->get(['id', 'category_name']);
    }

    public function render()
    {
        $seoData = [
            'title' => 'Search Jewelry by Photo | Jaffna Gold',
            'description' => 'Found a gold jewelry design you love on social media? Upload the photo and instantly find the same or similar pieces at Jaffna Gold.',
            'keywords' => 'search jewelry by image, find gold design by photo, visual jewelry search Sri Lanka, Jaffna Gold',
            'robots' => 'noindex, nofollow',
            'canonical' => route('visual-search'),
        ];

        return view('livewire.ecommerce.visual-search', [
            'categories' => $this->categoryOptions(),
        ])->layout('components.layouts.webapp', $this->sharedLayoutData($seoData));
    }
}
