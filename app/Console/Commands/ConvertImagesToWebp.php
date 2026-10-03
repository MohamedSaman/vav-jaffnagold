<?php

namespace App\Console\Commands;

use App\Models\ProductDetail;
use App\Services\WebpConverter;
use Illuminate\Console\Command;
use Illuminate\Support\Str;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Throwable;

class ConvertImagesToWebp extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'images:convert-webp';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Convert existing JPG/PNG product images to WebP format, rename them using product slugs, and update database records';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Starting product image WebP conversion and product slug renaming...');

        $convertedDbRecords = 0;
        $filesystemConverted = 0;

        // Step 1: Process Database Products - Rename existing product images to {product-slug}.webp
        try {
            $products = ProductDetail::whereNotNull('image')->get();

            foreach ($products as $product) {
                $rawImage = $product->getRawOriginal('image');
                if (!$rawImage) {
                    continue;
                }

                $baseNameNoExt = pathinfo($rawImage, PATHINFO_FILENAME);
                $targetSlugWebp = 'images/' . $product->slug . '.webp';
                $targetFullPath = public_path($targetSlugWebp);

                $possibleNames = array_unique([
                    $baseNameNoExt . '.webp',
                    $baseNameNoExt . '.jpg',
                    $baseNameNoExt . '.jpeg',
                    $baseNameNoExt . '.png',
                    $baseNameNoExt . '.JPG',
                    $baseNameNoExt . '.PNG',
                    basename($rawImage),
                ]);

                $possibleDirs = [
                    public_path('images'),
                    storage_path('app/public/images'),
                    storage_path('app/images'),
                    storage_path('images'),
                ];

                $currentFullPath = null;
                foreach ($possibleDirs as $dir) {
                    foreach ($possibleNames as $name) {
                        $candidate = $dir . DIRECTORY_SEPARATOR . $name;
                        if (file_exists($candidate)) {
                            $currentFullPath = $candidate;
                            break 2;
                        }
                    }
                }

                if ($currentFullPath) {
                    if (WebpConverter::convert($currentFullPath, $targetFullPath)) {
                        if (file_exists($currentFullPath) && $currentFullPath !== $targetFullPath) {
                            @unlink($currentFullPath);
                        }

                        $product->image = $targetSlugWebp;
                        $product->save();
                        $convertedDbRecords++;
                        $this->info("Converted & Renamed [Product ID {$product->id}]: " . basename($currentFullPath) . " -> {$targetSlugWebp}");
                    }
                } elseif (file_exists($targetFullPath)) {
                    if ($rawImage !== $targetSlugWebp) {
                        $product->image = $targetSlugWebp;
                        $product->save();
                        $convertedDbRecords++;
                        $this->info("Updated DB Record [Product ID {$product->id}]: {$targetSlugWebp}");
                    }
                }
            }
        } catch (Throwable $e) {
            $this->warn('Database connection error during product slug rename: ' . $e->getMessage());
        }

        // Step 2: Scan filesystem across all public and storage image paths for remaining non-WebP files
        $scanPaths = [
            public_path('images'),
            storage_path('app/public/images'),
            storage_path('app/images'),
            storage_path('images'),
        ];

        foreach ($scanPaths as $imagesPath) {
            if (!is_dir($imagesPath)) {
                continue;
            }

            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($imagesPath, RecursiveDirectoryIterator::SKIP_DOTS)
            );

            foreach ($iterator as $file) {
                if ($file->isFile()) {
                    $ext = strtolower($file->getExtension());
                    if (in_array($ext, ['jpg', 'jpeg', 'png'], true)) {
                        $fullPath = $file->getRealPath();
                        $dirName = pathinfo($fullPath, PATHINFO_DIRNAME);
                        $fileName = pathinfo($fullPath, PATHINFO_FILENAME);
                        $webpPath = $dirName . '/' . $fileName . '.webp';

                        if (WebpConverter::convert($fullPath, $webpPath)) {
                            $filesystemConverted++;
                            if (file_exists($webpPath) && $fullPath !== $webpPath) {
                                @unlink($fullPath);
                            }
                            $this->line("Filesystem converted: {$file->getFilename()} -> {$fileName}.webp");
                        }
                    }
                }
            }
        }

        $this->info("WebP & Slug Renaming Complete! DB Products Updated: {$convertedDbRecords}, Standalone Files Converted: {$filesystemConverted}");

        return Command::SUCCESS;
    }
}
