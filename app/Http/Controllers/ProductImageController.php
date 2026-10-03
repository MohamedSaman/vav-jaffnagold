<?php

namespace App\Http\Controllers;

use App\Jobs\SyncProductImageIndex;
use App\Models\ProductDetail;
use App\Services\WebpConverter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;

class ProductImageController extends Controller
{
    /**
     * Show the product image upload page for a given product.
     * Supports both barcode and product ID lookup.
     * URL: /product-image/{identifier}
     */
    public function show($identifier)
    {
        // Try finding by ID first (for QR code scans), then by barcode
        $product = ProductDetail::find($identifier);

        if (!$product) {
            $product = ProductDetail::where('barcode', $identifier)->first();
        }

        if (!$product) {
            return view('product-image-upload', [
                'product' => null,
                'identifier' => $identifier,
                'error' => 'No product found with identifier: ' . $identifier,
            ]);
        }

        // Load relationships for display
        $product->load(['brand', 'category', 'supplier']);

        return view('product-image-upload', [
            'product' => $product,
            'identifier' => $identifier,
            'error' => null,
        ]);
    }

    /**
     * Show the QR scanner page for rapid scan-and-upload workflow.
     * URL: /product-image
     */
    public function scanner()
    {
        return view('product-image-scanner');
    }

    /**
     * AJAX lookup: find product by scanned barcode or ID.
     * Returns JSON with product info for the scanner page.
     */
    public function lookup(Request $request)
    {
        $code = trim($request->input('code', ''));

        if (empty($code)) {
            return response()->json(['success' => false, 'message' => 'No code provided.'], 422);
        }

        // Try by ID first, then barcode, then product code
        $product = ProductDetail::find($code);

        if (!$product) {
            $product = ProductDetail::where('barcode', $code)->first();
        }

        if (!$product) {
            $product = ProductDetail::where('code', $code)->first();
        }

        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'No product found for: ' . $code,
            ], 404);
        }

        $product->load(['brand', 'category']);

        $existingImages = $this->formatExistingImages($product);
        $imageCount = count($existingImages);
        $hasRealImage = $imageCount > 0;

        return response()->json([
            'success' => true,
            'product' => [
                'id' => $product->id,
                'name' => $product->name,
                'code' => $product->code,
                'barcode' => $product->barcode,
                'brand' => $product->brand->brand_name ?? null,
                'category' => $product->category->category_name ?? null,
                'status' => $product->status,
                'image_count' => $imageCount,
                'has_image' => $hasRealImage,
                'primary_image_url' => $hasRealImage ? $existingImages[0]['url'] : asset('images/product.jpg'),
                'existing_images' => $existingImages,
                'upload_url' => url('/product-image/' . $product->id . '/upload'),
                'delete_url' => url('/product-image/' . $product->id . '/delete'),
                'detail_url' => url('/product-image/' . $product->id),
            ],
        ]);
    }

    /**
     * Format all existing images for JSON responses.
     */
    protected function formatExistingImages(ProductDetail $product): array
    {
        $rawPrimary = $product->getRawOriginal('image');
        $galleryImages = is_array($product->images) ? $product->images : (json_decode($product->images ?? '[]', true) ?: []);

        $hasRealPrimary = $rawPrimary && $rawPrimary !== 'images/product.jpg';

        $existingImages = [];
        $seenPaths = [];

        if ($hasRealPrimary) {
            $existingImages[] = [
                'url' => ProductDetail::resolveImageUrl($rawPrimary),
                'path' => $rawPrimary,
                'is_primary' => true,
            ];
            $seenPaths[] = $rawPrimary;
        }

        foreach ($galleryImages as $gPath) {
            if ($gPath && !in_array($gPath, $seenPaths)) {
                $existingImages[] = [
                    'url' => ProductDetail::resolveImageUrl($gPath),
                    'path' => $gPath,
                    'is_primary' => !$hasRealPrimary && empty($existingImages),
                ];
                $seenPaths[] = $gPath;
            }
        }

        return $existingImages;
    }

    /**
     * Test endpoint to diagnose upload issues
     */
    public function testUpload()
    {
        $uploadPath = public_path('images');
        $exists = is_dir($uploadPath);
        $writable = is_writable($uploadPath);
        $permissions = fileperms($uploadPath);

        return response()->json([
            'timestamp' => now()->toIso8601String(),
            'upload_path' => $uploadPath,
            'directory_exists' => $exists,
            'directory_writable' => $writable,
            'directory_permissions' => decoct($permissions & 0777),
            'php_user' => function_exists('posix_getpwuid') ?
                posix_getpwuid(posix_geteuid())['name'] : 'unknown',
            'can_create_file' => $this->canCreateTestFile($uploadPath),
            'disk_free_space' => disk_free_space($uploadPath) . ' bytes',
            'temp_dir' => sys_get_temp_dir(),
            'temp_dir_writable' => is_writable(sys_get_temp_dir()),
        ]);
    }

    /**
     * Test if we can create a file in the upload directory
     */
    private function canCreateTestFile($path)
    {
        try {
            $testFile = $path . '/test-' . time() . '.txt';
            if (@file_put_contents($testFile, 'test')) {
                @unlink($testFile);
                return true;
            }
            return false;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Handle the image upload for a product.
     */
    /**
     * Handle single or multiple image uploads for a product.
     */
    public function upload(Request $request, $identifier)
    {
        @ini_set('memory_limit', '512M');
        @set_time_limit(300);

        try {
            $imageCheck = function ($attribute, $value, $fail) {
                if (!$value instanceof \Illuminate\Http\UploadedFile || !$value->isValid()) {
                    return $fail("Invalid image file.");
                }
                $mime = strtolower($value->getMimeType() ?: $value->getClientMimeType() ?: '');
                $ext = strtolower($value->getClientOriginalExtension());
                $allowedExts = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'avif', 'heic', 'heif', 'bmp', 'svg'];
                
                if (!str_starts_with($mime, 'image/') && !in_array($ext, $allowedExts)) {
                    return $fail("The file must be an image (JPG, PNG, WebP, HEIC, AVIF).");
                }
            };

            $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
                'images' => 'nullable|array',
                'images.*' => ['file', 'max:25600', $imageCheck],
                'image' => ['nullable', 'file', 'max:25600', $imageCheck],
            ]);

            $isAjax = $request->expectsJson() || $request->ajax() || $request->header('X-Requested-With') === 'XMLHttpRequest';

            if ($validator->fails()) {
                $errMsg = $validator->errors()->first();
                if ($isAjax) {
                    return response()->json([
                        'success' => false,
                        'message' => $errMsg,
                        'errors' => $validator->errors()->all(),
                    ], 422);
                }
                return back()->withErrors($validator)->with('error', $errMsg);
            }

            $files = [];
            if ($request->hasFile('images')) {
                $files = $request->file('images');
            } elseif ($request->hasFile('image')) {
                $files = [$request->file('image')];
            }

            if (empty($files)) {
                if ($isAjax) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Please select at least one image to upload.',
                    ], 422);
                }
                return back()->with('error', 'Please select at least one image to upload.');
            }

            // Find product by ID or barcode
            $product = ProductDetail::find($identifier) ?: ProductDetail::where('barcode', $identifier)->first();

            if (!$product) {
                $message = 'Product not found.';
                if ($isAjax) {
                    return response()->json(['success' => false, 'message' => $message], 404);
                }
                return back()->with('error', $message);
            }

            // Prepare directory
            $uploadPath = public_path('images');
            $useStorageFallback = false;

            if (!is_dir($uploadPath)) {
                @mkdir($uploadPath, 0777, true);
            }

            if (!is_writable($uploadPath)) {
                $storageDir = storage_path('app/public/images');
                if (!is_dir($storageDir)) {
                    @mkdir($storageDir, 0777, true);
                }
                $uploadPath = $storageDir;
                $useStorageFallback = true;
            }

            $currentGallery = is_array($product->images) ? $product->images : (json_decode($product->images ?? '[]', true) ?: []);
            $uploadedPaths = [];
            $primaryImage = $product->getRawOriginal('image');

            foreach ($files as $index => $file) {
                if (!$file) continue;

                $uniqueSuffix = time() . '-' . Str::random(5);
                $ext = $file->getClientOriginalExtension();
                $filename = $product->slug . '-' . $uniqueSuffix . '.' . $ext;
                $fullPath = $uploadPath . DIRECTORY_SEPARATOR . $filename;

                $fileMoved = false;
                try {
                    if ($file->move($uploadPath, $filename)) {
                        $fileMoved = true;
                    }
                } catch (\Exception $e) {}

                if (!$fileMoved) {
                    try {
                        if (@copy($file->getRealPath(), $fullPath)) {
                            $fileMoved = true;
                        }
                    } catch (\Exception $e) {}
                }

                if (!$fileMoved) continue;

                @chmod($fullPath, 0666);

                // Convert to WebP
                if (!Str::endsWith(strtolower($filename), '.webp')) {
                    $webpFilename = pathinfo($filename, PATHINFO_FILENAME) . '.webp';
                    $webpFullPath = $uploadPath . DIRECTORY_SEPARATOR . $webpFilename;
                    if (\App\Services\WebpConverter::convert($fullPath, $webpFullPath)) {
                        @unlink($fullPath);
                        $filename = $webpFilename;
                        $fullPath = $webpFullPath;
                        @chmod($fullPath, 0666);
                    }
                }

                $savedPath = $useStorageFallback ? 'storage/images/' . $filename : 'images/' . $filename;
                $uploadedPaths[] = $savedPath;

                if (!in_array($savedPath, $currentGallery)) {
                    $currentGallery[] = $savedPath;
                }

                // If no primary image set yet or using default, set first uploaded as primary
                if (empty($primaryImage) || $primaryImage === 'images/product.jpg') {
                    $primaryImage = $savedPath;
                }
            }

            // Update product in DB
            $product->images = array_values($currentGallery);
            if ($primaryImage) {
                $product->image = $primaryImage;
            }
            $product->save();

            // Trigger Visual Search indexing if enabled
            if (config('image_search.enabled') && config('image_search.sync.enabled')) {
                try {
                    SyncProductImageIndex::dispatch($product->id, SyncProductImageIndex::ACTION_UPSERT);
                } catch (\Throwable $e) {}
            }

            $successMessage = count($uploadedPaths) . ' image(s) uploaded successfully!';
            $existingImages = $this->formatExistingImages($product);

            if ($isAjax || $request->wantsJson() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
                return response()->json([
                    'success' => true,
                    'message' => $successMessage,
                    'uploaded_count' => count($uploadedPaths),
                    'total_gallery_count' => count($existingImages),
                    'existing_images' => $existingImages,
                    'all_images' => $product->all_image_urls,
                ]);
            }

            return back()->with('success', $successMessage);
        } catch (\Exception $e) {
            Log::error('Product image upload failed', ['error' => $e->getMessage()]);
            if ($isAjax) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
            }
            return back()->with('error', 'Upload failed: ' . $e->getMessage());
        }
    }

    /**
     * Delete an image from product gallery.
     */
    public function deleteImage(Request $request, $identifier)
    {
        $request->validate(['image_path' => 'required|string']);
        $targetPath = $request->input('image_path');

        $product = ProductDetail::find($identifier) ?: ProductDetail::where('barcode', $identifier)->firstOrFail();
        $gallery = is_array($product->images) ? $product->images : (json_decode($product->images ?? '[]', true) ?: []);

        // Filter out target path
        $gallery = array_values(array_filter($gallery, fn($p) => $p !== $targetPath));
        $product->images = $gallery;

        // If deleted image was primary, assign next available or default
        if ($product->getRawOriginal('image') === $targetPath) {
            $product->image = $gallery[0] ?? 'images/product.jpg';
        }

        $product->save();

        // Remove file from disk if exists
        $localPath = public_path($targetPath);
        if (file_exists($localPath)) {
            @unlink($localPath);
        }

        $isAjax = $request->expectsJson() || $request->ajax() || $request->header('X-Requested-With') === 'XMLHttpRequest';
        $existingImages = $this->formatExistingImages($product);

        if ($isAjax) {
            return response()->json([
                'success' => true,
                'message' => 'Image removed successfully.',
                'existing_images' => $existingImages,
                'image_count' => count($existingImages),
            ]);
        }

        return back()->with('success', 'Image removed successfully.');
    }

    /**
     * Set a gallery image as the primary cover photo.
     */
    public function setPrimaryImage(Request $request, $identifier)
    {
        $request->validate(['image_path' => 'required|string']);
        $targetPath = $request->input('image_path');

        $product = ProductDetail::find($identifier) ?: ProductDetail::where('barcode', $identifier)->firstOrFail();
        $product->image = $targetPath;
        $product->save();

        return back()->with('success', 'Cover image updated.');
    }

    /**
     * Serve a product image directly from storage (no symlink required).
     * Automatically converts requested JPG/PNG files to WebP on the fly and resolves product slug images.
     * URL: /product-image-serve/{filename}
     */
    public function serveImage($filename)
    {
        // Sanitize filename to prevent directory traversal
        $filename = basename($filename);
        $baseNameWithoutExt = pathinfo($filename, PATHINFO_FILENAME);
        $webpFilename = $baseNameWithoutExt . '.webp';

        // Check for WebP version first in all storage and public locations
        $candidatePaths = [
            storage_path('app/public/images/' . $webpFilename),
            storage_path('images/' . $webpFilename),
            public_path('images/' . $webpFilename),
            storage_path('app/public/images/' . $filename),
            storage_path('images/' . $filename),
            public_path('images/' . $filename),
        ];

        $targetPath = null;
        foreach ($candidatePaths as $candidate) {
            if (file_exists($candidate)) {
                $targetPath = $candidate;
                break;
            }
        }

        // If no WebP version exists yet, but original JPG/PNG exists, auto-convert on the fly to WebP
        if ($targetPath && !\Illuminate\Support\Str::endsWith(strtolower($targetPath), '.webp')) {
            $dir = dirname($targetPath);
            $newWebpPath = $dir . DIRECTORY_SEPARATOR . $webpFilename;
            if (\App\Services\WebpConverter::convert($targetPath, $newWebpPath)) {
                @unlink($targetPath);
                $targetPath = $newWebpPath;
            }
        }

        // If not found directly by filename, check database lookup by product code, barcode, or image string
        if (!$targetPath) {
            $product = ProductDetail::where('code', $baseNameWithoutExt)
                ->orWhere('barcode', $baseNameWithoutExt)
                ->orWhere('image', 'like', '%' . $baseNameWithoutExt . '%')
                ->first();

            if ($product && $product->slug) {
                $slugWebp = public_path('images/' . $product->slug . '.webp');
                if (file_exists($slugWebp)) {
                    $targetPath = $slugWebp;
                }
            }
        }

        if (!$targetPath) {
            // Return default WebP/JPG image
            $defaultWebp = public_path('images/product.webp');
            $defaultJpg = public_path('images/product.jpg');
            if (file_exists($defaultWebp)) {
                return response()->file($defaultWebp, ['Content-Type' => 'image/webp']);
            } elseif (file_exists($defaultJpg)) {
                return response()->file($defaultJpg, ['Content-Type' => 'image/jpeg']);
            }
            abort(404);
        }

        $mimeType = \Illuminate\Support\Str::endsWith(strtolower($targetPath), '.webp')
            ? 'image/webp'
            : (mime_content_type($targetPath) ?: 'image/jpeg');

        return response()->file($targetPath, [
            'Content-Type' => $mimeType,
            'Cache-Control' => 'public, max-age=86400',
        ]);
    }

    /**
     * Toggle product's website_live status
     */
    public function toggleWebsiteLive(Request $request, $identifier)
    {
        $product = ProductDetail::find($identifier) ?: ProductDetail::where('barcode', $identifier)->firstOrFail();
        $product->website_live = !$product->website_live;
        $product->save();

        if ($request->wantsJson() || $request->ajax() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
            return response()->json([
                'success' => true,
                'website_live' => (bool)$product->website_live,
                'message' => $product->website_live ? 'Product is now LIVE on customer website!' : 'Product is now hidden from customer website.'
            ]);
        }

        return back()->with('success', $product->website_live ? 'Product is now LIVE on customer website!' : 'Product is now hidden from customer website.');
    }
}
