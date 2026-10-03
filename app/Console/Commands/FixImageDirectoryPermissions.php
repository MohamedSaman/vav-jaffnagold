<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class FixImageDirectoryPermissions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'images:fix-permissions';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix permissions for the product images directory to enable uploads';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔧 Fixing image directory permissions...');

        $imagePath = public_path('images');

        // Check if directory exists
        if (!is_dir($imagePath)) {
            $this->info("📁 Creating directory: {$imagePath}");
            try {
                @mkdir($imagePath, 0777, true);
                $this->info('✅ Directory created successfully');
            } catch (\Exception $e) {
                $this->error("❌ Failed to create directory: {$e->getMessage()}");
                return 1;
            }
        } else {
            $this->info("📁 Directory exists: {$imagePath}");
        }

        // Get current permissions
        $currentPerms = substr(sprintf('%o', fileperms($imagePath)), -4);
        $this->info("📊 Current permissions: {$currentPerms}");

        // Fix permissions
        $this->info("🔐 Setting permissions to 0777...");
        if (@chmod($imagePath, 0777)) {
            $newPerms = substr(sprintf('%o', fileperms($imagePath)), -4);
            $this->info("✅ Permissions updated to: {$newPerms}");
        } else {
            $this->warn("⚠️  Could not change permissions directly");
            $this->line("   Try running: chmod -R 777 {$imagePath}");
        }

        // Verify it's writable
        if (is_writable($imagePath)) {
            $this->info("✅ Directory is now writable!");
            $this->info("✅ Image uploads should work now.");
            return 0;
        } else {
            $this->error("❌ Directory is still not writable");
            $this->warn("   Your system may be configured restrictively.");
            $this->line("\n   Try these commands manually:");
            $this->line("   sudo chown -R www-data:www-data {$imagePath}");
            $this->line("   sudo chmod -R 755 {$imagePath}");
            $this->line("   sudo chmod -R u+w {$imagePath}");
            return 1;
        }
    }
}
