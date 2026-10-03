<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class SetupImageStorage extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'images:setup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Set up image storage with proper permissions and symlinks';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔧 Setting up image storage...');

        // Step 1: Create storage/app/public/images directory
        $storagePath = storage_path('app/public/images');
        $this->line("\n📁 Step 1: Create storage directory...");

        if (!is_dir($storagePath)) {
            try {
                @mkdir($storagePath, 0777, true);
                $this->info("✅ Created: {$storagePath}");
            } catch (\Exception $e) {
                $this->error("❌ Failed to create directory: {$e->getMessage()}");
                return 1;
            }
        } else {
            $this->info("✅ Directory already exists");
        }

        // Step 2: Fix permissions on storage directory
        $this->line("\n🔐 Step 2: Fix directory permissions...");
        $permissions = [0777, 0775, 0755];
        $success = false;

        foreach ($permissions as $perm) {
            if (@chmod($storagePath, $perm)) {
                if (is_writable($storagePath)) {
                    $this->info("✅ Directory is writable (permissions: " . decoct($perm) . ")");
                    $success = true;
                    break;
                }
            }
        }

        if (!$success) {
            $this->warn("⚠️  Could not make directory writable with PHP");
            $this->line("   Try running manually:");
            $this->line("   sudo chmod -R 777 {$storagePath}");
        }

        // Step 3: Ensure storage symlink exists
        $this->line("\n🔗 Step 3: Create storage symlink...");
        $publicStoragePath = public_path('storage');
        $storageAppPath = storage_path('app/public');

        if (is_link($publicStoragePath)) {
            $this->info("✅ Symlink already exists");
        } else {
            try {
                // Remove if exists but not symlink
                if (is_dir($publicStoragePath)) {
                    @rmdir($publicStoragePath);
                }

                // Create symlink
                if (@symlink($storageAppPath, $publicStoragePath)) {
                    $this->info("✅ Symlink created successfully");
                } else {
                    $this->warn("⚠️  Could not create symlink with PHP");
                    $this->line("   Try running manually:");
                    $this->line("   ln -s {$storageAppPath} {$publicStoragePath}");
                }
            } catch (\Exception $e) {
                $this->error("❌ Symlink creation failed: {$e->getMessage()}");
            }
        }

        // Step 4: Try to fix public/images permissions as backup
        $this->line("\n📁 Step 4: Also fix public/images directory...");
        $publicImagesPath = public_path('images');

        if (!is_dir($publicImagesPath)) {
            try {
                @mkdir($publicImagesPath, 0777, true);
                $this->info("✅ Created: {$publicImagesPath}");
            } catch (\Exception $e) {
                $this->warn("⚠️  Could not create public images dir");
            }
        }

        foreach ($permissions as $perm) {
            if (@chmod($publicImagesPath, $perm)) {
                if (is_writable($publicImagesPath)) {
                    $this->info("✅ public/images is writable");
                    break;
                }
            }
        }

        // Step 5: Summary
        $this->line("\n" . str_repeat("=", 50));
        $this->info("✅ Setup Complete!");
        $this->line("\nImage uploads can now be saved to:");
        $this->line("1. public/images/ (if writable)");
        $this->line("2. storage/app/public/images/ (via /storage/ symlink)");
        $this->line("\n📸 Try uploading an image now!");
        $this->line(str_repeat("=", 50));

        return 0;
    }
}
