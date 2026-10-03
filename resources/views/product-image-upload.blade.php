<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="theme-color" content="#161b97">
    <title>{{ $product ? $product->name . ' - Upload Image' : 'Product Image Upload' }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: #f4f5fa;
            min-height: 100vh;
            color: #1a1a2e;
        }

        /* Header */
        .page-header {
            background: linear-gradient(135deg, #161b97 0%, #12167d 100%);
            padding: 20px 16px;
            text-align: center;
            position: sticky;
            top: 0;
            z-index: 100;
            box-shadow: 0 4px 20px rgba(22, 27, 151, 0.3);
        }

        .page-header h1 {
            color: #fff;
            font-size: 1.1rem;
            font-weight: 800;
            letter-spacing: 1px;
            text-transform: uppercase;
            margin-bottom: 4px;
        }

        .page-header p {
            color: rgba(255, 255, 255, 0.65);
            font-size: 0.7rem;
            font-weight: 600;
            letter-spacing: 2px;
            text-transform: uppercase;
        }

        /* Main Content */
        .main-content {
            padding: 16px;
            max-width: 680px;
            margin: 0 auto;
        }

        /* Accumulated Preview Grid for 23+ Images */
        .preview-grid-accumulated {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(110px, 1fr));
            gap: 10px;
            margin-top: 12px;
            max-height: 420px;
            overflow-y: auto;
            padding: 4px;
        }

        .preview-card {
            position: relative;
            background: #fff;
            border: 1.5px solid #e2e8f0;
            border-radius: 10px;
            padding: 6px;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.04);
            transition: transform 0.15s, border-color 0.15s;
        }

        .preview-card:hover {
            border-color: #161b97;
            transform: translateY(-2px);
        }

        .preview-card .img-thumb {
            width: 100%;
            height: 90px;
            border-radius: 6px;
            object-fit: cover;
            background: #f1f5f9;
            display: block;
        }

        .preview-card .index-badge {
            position: absolute;
            top: 9px;
            left: 9px;
            background: rgba(22, 27, 151, 0.9);
            color: #fff;
            font-size: 0.65rem;
            font-weight: 800;
            padding: 2px 6px;
            border-radius: 6px;
            z-index: 2;
        }

        .preview-card .remove-btn {
            position: absolute;
            top: 4px;
            right: 4px;
            width: 22px;
            height: 22px;
            border-radius: 50%;
            background: rgba(239, 68, 68, 0.95);
            color: #fff;
            border: none;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.75rem;
            z-index: 2;
            transition: background 0.2s;
        }

        .preview-card .remove-btn:hover {
            background: #dc2626;
        }

        .preview-card .file-meta {
            font-size: 0.62rem;
            font-weight: 600;
            color: #64748b;
            margin-top: 4px;
            text-align: center;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .add-more-tile {
            border: 2px dashed #93c5fd;
            background: #eff6ff;
            border-radius: 10px;
            min-height: 110px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s;
            color: #1e40af;
            gap: 4px;
        }

        .add-more-tile:hover {
            background: #dbeafe;
            border-color: #2563eb;
        }

        .add-more-tile i {
            font-size: 1.6rem;
        }

        .add-more-tile span {
            font-size: 0.75rem;
            font-weight: 700;
        }

        /* Upload Progress */
        .upload-progress-box {
            display: none;
            margin-top: 14px;
            padding: 14px 16px;
            border-radius: 12px;
            background: #f8fafc;
            border: 1.5px solid #e2e8f0;
        }

        .progress-bar-track {
            height: 10px;
            background: #e2e8f0;
            border-radius: 6px;
            overflow: hidden;
            margin: 8px 0;
        }

        .progress-bar-fill {
            height: 100%;
            width: 0%;
            background: linear-gradient(90deg, #10b981, #059669);
            border-radius: 6px;
            transition: width 0.3s ease;
        }

        .progress-status-text {
            font-size: 0.8rem;
            font-weight: 700;
            color: #1e293b;
            display: flex;
            justify-content: space-between;
        }

        /* Product Info Card */
        .product-card {
            background: #fff;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.06);
            margin-bottom: 16px;
        }

        .product-info {
            padding: 20px;
        }

        .product-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: #f0f2ff;
            color: #161b97;
            font-size: 0.65rem;
            font-weight: 800;
            padding: 5px 12px;
            border-radius: 20px;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            margin-bottom: 12px;
        }

        .product-name {
            font-size: 1.3rem;
            font-weight: 900;
            color: #0f172a;
            line-height: 1.3;
            margin-bottom: 6px;
        }

        .product-code {
            font-size: 0.8rem;
            font-weight: 700;
            color: #64748b;
            font-family: 'JetBrains Mono', inter;
            letter-spacing: 0.5px;
        }

        .product-meta {
            display: flex;
            gap: 16px;
            margin-top: 14px;
            padding-top: 14px;
            border-top: 1px solid #f1f5f9;
        }

        .meta-item {
            display: flex;
            flex-direction: column;
            gap: 2px;
        }

        .meta-label {
            font-size: 0.6rem;
            font-weight: 800;
            color: #94a3b8;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .meta-value {
            font-size: 0.85rem;
            font-weight: 700;
            color: #334155;
        }

        /* Current Image Preview */
        .current-image-section {
            padding: 0 20px 20px;
        }

        .current-image-label {
            font-size: 0.65rem;
            font-weight: 800;
            color: #94a3b8;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 8px;
        }

        .current-image-wrap {
            background: #f8fafc;
            border: 2px dashed #e2e8f0;
            border-radius: 12px;
            padding: 12px;
            text-align: center;
        }

        .current-image-wrap img {
            max-width: 100%;
            max-height: 200px;
            border-radius: 8px;
            object-fit: contain;
            display: block;
            margin: 0 auto;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .current-image-wrap img[src*="product.jpg"] {
            opacity: 0.7;
            filter: grayscale(20%);
        }

        .no-image-text {
            color: #94a3b8;
            font-size: 0.8rem;
            font-weight: 600;
            padding: 30px 0;
        }

        .no-image-text i {
            font-size: 2rem;
            display: block;
            margin-bottom: 8px;
            opacity: 0.5;
        }

        /* Upload Section */
        .upload-card {
            background: #fff;
            border-radius: 16px;
            padding: 20px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.06);
            margin-bottom: 16px;
        }

        .upload-title {
            font-size: 0.75rem;
            font-weight: 800;
            color: #161b97;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            margin-bottom: 16px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .upload-title i {
            font-size: 1.1rem;
        }

        /* Drop Zone */
        .drop-zone {
            border: 2px dashed #d1d5db;
            border-radius: 16px;
            padding: 30px 20px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
            background: #fafbff;
            position: relative;
            overflow: hidden;
        }

        .drop-zone:hover,
        .drop-zone.active {
            border-color: #161b97;
            background: #f0f2ff;
        }

        .drop-zone.has-file {
            border-color: #10b981;
            background: #f0fdf4;
        }

        .drop-zone-icon {
            width: 56px;
            height: 56px;
            background: linear-gradient(135deg, #161b97, #12167d);
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 12px;
            color: #fff;
            font-size: 1.5rem;
        }

        .drop-zone-text {
            font-size: 0.9rem;
            font-weight: 700;
            color: #334155;
            margin-bottom: 4px;
        }

        .drop-zone-hint {
            font-size: 0.7rem;
            color: #94a3b8;
            font-weight: 500;
        }

        .drop-zone input[type="file"] {
            position: absolute;
            inset: 0;
            opacity: 0;
            cursor: pointer;
        }

        /* Image Preview */
        .image-preview {
            margin-top: 16px;
            display: none;
        }

        .image-preview.show {
            display: block;
        }

        .preview-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 8px;
        }

        .preview-label {
            font-size: 0.65rem;
            font-weight: 800;
            color: #10b981;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .preview-clear {
            background: none;
            border: none;
            color: #ef4444;
            font-size: 0.7rem;
            font-weight: 700;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .preview-img-wrap {
            background: #f8fafc;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            padding: 12px;
            text-align: center;
        }

        .preview-img-wrap img {
            max-width: 100%;
            max-height: 250px;
            border-radius: 8px;
            object-fit: contain;
        }

        .file-info {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-top: 8px;
            padding: 8px 12px;
            background: #f0fdf4;
            border-radius: 8px;
            font-size: 0.7rem;
            font-weight: 600;
            color: #166534;
        }

        /* Submit Button */
        .submit-btn {
            width: 100%;
            padding: 16px;
            margin-top: 10px;
            background: linear-gradient(135deg, #161b97 0%, #12167d 100%);
            color: #fff;
            border: none;
            border-radius: 14px;
            font-size: 0.9rem;
            font-weight: 800;
            letter-spacing: 1px;
            text-transform: uppercase;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            box-shadow: 0 6px 20px rgba(22, 27, 151, 0.3);
        }

        .submit-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(22, 27, 151, 0.4);
        }

        .submit-btn:active {
            transform: translateY(0);
        }

        .submit-btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }

        .submit-btn i {
            font-size: 1.1rem;
        }

        /* Camera Button (Mobile) */
        .camera-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            width: 100%;
            padding: 14px;
            background: #f8fafc;
            border: 2px solid #e2e8f0;
            border-radius: 14px;
            font-size: 0.85rem;
            font-weight: 700;
            color: #475569;
            cursor: pointer;
            transition: all 0.3s;
            margin-top: 10px;
        }

        .camera-btn:hover {
            background: #f0f2ff;
            border-color: #161b97;
            color: #161b97;
        }

        .camera-btn i {
            font-size: 1.2rem;
        }

        /* Alert Messages */
        .alert {
            padding: 16px 18px;
            border-radius: 12px;
            font-size: 0.85rem;
            font-weight: 600;
            margin-bottom: 16px;
            display: flex;
            align-items: start;
            gap: 12px;
            animation: slideInDown 0.4s ease;
        }

        @keyframes slideInDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .alert i {
            font-size: 1.3rem;
            flex-shrink: 0;
            margin-top: 2px;
        }

        .alert div {
            flex: 1;
        }

        .alert strong {
            display: block;
            font-weight: 800;
            margin-bottom: 2px;
        }

        .alert-success {
            background: #f0fdf4;
            color: #166534;
            border: 2px solid #86efac;
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.2);
        }

        .alert-error {
            background: #fef2f2;
            color: #991b1b;
            border: 2px solid #fecaca;
            box-shadow: 0 4px 12px rgba(239, 68, 68, 0.2);
        }

        /* Error Card */
        .error-card {
            background: #fff;
            border-radius: 16px;
            padding: 40px 20px;
            text-align: center;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.06);
        }

        .error-icon {
            width: 64px;
            height: 64px;
            background: #fef2f2;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 16px;
            color: #ef4444;
            font-size: 1.8rem;
        }

        .error-card h2 {
            font-size: 1.1rem;
            font-weight: 800;
            color: #0f172a;
            margin-bottom: 8px;
        }

        .error-card p {
            font-size: 0.85rem;
            color: #64748b;
            font-weight: 500;
            line-height: 1.5;
        }

        /* Scan another link */
        .scan-another {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            margin-top: 20px;
            color: #161b97;
            font-size: 0.8rem;
            font-weight: 700;
            text-decoration: none;
        }

        .scan-another:hover {
            text-decoration: underline;
        }

        /* Loading spinner */
        .spinner {
            display: none;
            width: 20px;
            height: 20px;
            border: 3px solid rgba(255, 255, 255, 0.3);
            border-top-color: #fff;
            border-radius: 50%;
            animation: spin 0.8s ease-in-out infinite;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        .submit-btn.loading .spinner {
            display: inline-block;
        }

        .submit-btn.loading .btn-text {
            display: none;
        }
    </style>
</head>

<body>


    <!-- Header -->
    <div class="page-header">
        <h1><i class="bi bi-camera"></i> Product Image Upload</h1>
        <p>Jaffna Gold (PVT) LTD</p>
    </div>

    <div class="main-content">
        @if(session('success'))
        <div class="alert alert-success">
            <i class="bi bi-check-circle-fill"></i>
            <div>
                <strong>Success!</strong><br>
                {{ session('success') }}
            </div>
        </div>
        <script>
            // Auto-reload to show new image after 2 seconds
            setTimeout(function() {
                window.location.reload();
            }, 2000);
        </script>
        @endif

        @if(session('error'))
        <div class="alert alert-error">
            <i class="bi bi-exclamation-triangle-fill"></i>
            <div>
                <strong>⚠️ Upload Error!</strong><br>
                {{ session('error') }}
                @php
                $error = session('error');
                $isPermissionError = strpos($error, 'directory') !== false ||
                strpos($error, 'writable') !== false ||
                strpos($error, 'Unable to write') !== false;
                @endphp
                @if($isPermissionError)
                <div style="margin-top: 12px; padding-top: 12px; border-top: 2px solid rgba(220, 53, 69, 0.3);">
                    <small style="display: block; margin-bottom: 8px;">
                        <strong>🔧 To fix this, administrator should run ONE of these commands:</strong>
                    </small>
                    <div style="background: rgba(0,0,0,0.15); padding: 8px; border-radius: 6px; font-family: 'Courier New', monospace; font-size: 0.75rem; overflow-x: auto; margin-bottom: 8px;">
                        <div style="margin-bottom: 6px;">
                            <strong>Option 1 (Recommended):</strong><br>
                            <code style="color: #10b981;">php artisan images:setup</code>
                        </div>
                        <div style="margin-bottom: 6px; border-top: 1px solid rgba(220, 53, 69, 0.2); padding-top: 6px;">
                            <strong>Option 2:</strong><br>
                            <code style="color: #10b981;">php artisan storage:link</code>
                        </div>
                        <div style="border-top: 1px solid rgba(220, 53, 69, 0.2); padding-top: 6px;">
                            <strong>Option 3 (Manual SSH):</strong><br>
                            <code style="color: #10b981;">chmod -R 777 public/images/</code>
                        </div>
                    </div>
                    <small style="color: #ef4444; display: block;">
                        💡 System will automatically try fallback methods. If error persists after running above commands, clear the browser cache and try again.
                    </small>
                </div>
                @endif
            </div>
        </div>
        @endif

        @if($error)
        <!-- Product Not Found -->
        <div class="error-card">
            <div class="error-icon">
                <i class="bi bi-box-seam"></i>
            </div>
            <h2>Product Not Found</h2>
            <p>{{ $error }}</p>
            <p style="margin-top: 8px; font-size: 0.75rem; color: #94a3b8;">
                Please check the barcode and try scanning again.
            </p>
        </div>
        @elseif($product)
        <!-- Product Info -->
        <div class="product-card">
            <div class="product-info">
                <div class="product-badge">
                    <i class="bi bi-qr-code"></i> #{{ $product->id }} — {{ $product->code }}
                </div>
                <h2 class="product-name">{{ $product->name }}</h2>
                <div class="product-code">{{ $product->code }}</div>

                <div class="product-meta">
                    @if($product->brand)
                    <div class="meta-item">
                        <span class="meta-label">Brand</span>
                        <span class="meta-value">{{ $product->brand->brand_name ?? '—' }}</span>
                    </div>
                    @endif
                    @if($product->category)
                    <div class="meta-item">
                        <span class="meta-label">Category</span>
                        <span class="meta-value">{{ $product->category->category_name ?? '—' }}</span>
                    </div>
                    @endif
                    <div class="meta-item">
                        <span class="meta-label">Status</span>
                        <span class="meta-value" style="color: {{ $product->status === 'active' ? '#10b981' : '#ef4444' }};">
                            {{ ucfirst($product->status) }}
                        </span>
                    </div>
                </div>

                <!-- Storefront Live Status Bar -->
                <div style="margin-top: 14px; padding-top: 14px; border-top: 1px solid #f1f5f9; display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 10px;">
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <span class="meta-label" style="font-size: 0.7rem; font-weight: 800; color: #475569;">Storefront:</span>
                        <span id="websiteLiveBadge" class="badge" style="font-size: 0.72rem; font-weight: 800; padding: 4px 10px; border-radius: 20px; background: {{ $product->website_live ? '#dcfce7' : '#f1f5f9' }}; color: {{ $product->website_live ? '#15803d' : '#64748b' }}; border: 1px solid {{ $product->website_live ? '#86efac' : '#cbd5e1' }};">
                            <i class="bi {{ $product->website_live ? 'bi-broadcast' : 'bi-eye-slash' }}"></i>
                            <span id="websiteLiveText">{{ $product->website_live ? 'LIVE ON STOREFRONT' : 'HIDDEN FROM STOREFRONT' }}</span>
                        </span>
                    </div>
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <button type="button" onclick="toggleWebsiteLive()" id="liveToggleBtn" style="background: {{ $product->website_live ? '#fee2e2' : '#f0fdf4' }}; color: {{ $product->website_live ? '#b91c1c' : '#15803d' }}; border: 1px solid {{ $product->website_live ? '#fca5a5' : '#86efac' }}; border-radius: 8px; font-size: 0.72rem; font-weight: 700; padding: 6px 12px; cursor: pointer;">
                            <i class="bi {{ $product->website_live ? 'bi-eye-slash' : 'bi-check-circle-fill' }}"></i>
                            <span id="liveToggleBtnText">{{ $product->website_live ? 'Set to Hidden' : 'Make Live Now' }}</span>
                        </button>
                        @if($product->slug)
                        <a href="{{ url('/ecommerce/product/' . $product->slug) }}" target="_blank" style="background: #f0f2ff; color: #161b97; border: 1px solid #c7d2fe; border-radius: 8px; font-size: 0.72rem; font-weight: 700; padding: 6px 12px; text-decoration: none; display: inline-flex; align-items: center; gap: 4px;">
                            <i class="bi bi-box-arrow-up-right"></i> Customer View
                        </a>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Product Images Gallery Section -->
            <div class="current-image-section">
                @php
                $rawImage = $product->getRawOriginal('image');
                $isDefaultImage = !$rawImage || $rawImage === '' || $rawImage === 'images/product.jpg' || $rawImage === 'images/products/product.jpg';
                $galleryImages = is_array($product->images) ? $product->images : (json_decode($product->images ?? '[]', true) ?: []);
                $allImageUrls = $product->all_image_urls;
                @endphp
                <div class="current-image-label" style="display: flex; justify-content: space-between; align-items: center;">
                    <span>Product Gallery ({{ count($allImageUrls) }} image{{ count($allImageUrls) > 1 ? 's' : '' }})</span>
                    <span class="badge" style="background: #161b97; color: #fff; font-size: 0.65rem; padding: 3px 8px; border-radius: 12px;">Customer View</span>
                </div>

                <!-- Gallery Grid -->
                <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(130px, 1fr)); gap: 12px; margin-top: 10px;">
                    @foreach($allImageUrls as $gIdx => $gUrl)
                        @php
                            $isPrimary = ($gIdx === 0 && !$isDefaultImage) || (str_contains($gUrl, basename($rawImage ?? '')));
                            $pathForAction = $galleryImages[$gIdx] ?? ($isPrimary ? $rawImage : '');
                        @endphp
                        <div style="background: #fff; border: 2px solid {{ $isPrimary ? '#10b981' : '#e2e8f0' }}; border-radius: 12px; padding: 8px; position: relative; display: flex; flex-direction: column; align-items: center; box-shadow: 0 2px 8px rgba(0,0,0,0.04);">
                            @if($isPrimary)
                                <div style="position: absolute; top: 5px; left: 5px; background: #10b981; color: #fff; font-size: 0.62rem; font-weight: 800; padding: 2px 6px; border-radius: 6px; z-index: 2;">
                                    Cover
                                </div>
                            @endif
                            <div style="width: 100%; height: 110px; border-radius: 8px; overflow: hidden; background: #f8fafc; display: flex; align-items: center; justify-content: center;">
                                <img src="{{ $gUrl }}" alt="Product Image {{ $gIdx + 1 }}" style="max-width: 100%; max-height: 100%; object-fit: contain;">
                            </div>
                            
                            @if($pathForAction)
                                <div style="display: flex; gap: 4px; margin-top: 8px; width: 100%;">
                                    @if(!$isPrimary)
                                        <form action="{{ url('/product-image/' . $product->id . '/set-primary') }}" method="POST" style="flex: 1;">
                                            @csrf
                                            <input type="hidden" name="image_path" value="{{ $pathForAction }}">
                                            <button type="submit" style="width: 100%; background: #f0f2ff; color: #161b97; border: 1px solid #161b97; border-radius: 6px; padding: 4px 2px; font-size: 0.65rem; font-weight: 700; cursor: pointer;" title="Set as main cover image">
                                                Cover
                                            </button>
                                        </form>
                                    @endif
                                    <form action="{{ url('/product-image/' . $product->id . '/delete') }}" method="POST" onsubmit="return confirm('Remove this image?')" style="flex: {{ $isPrimary ? '1' : '0 0 32px' }};">
                                        @csrf
                                        <input type="hidden" name="image_path" value="{{ $pathForAction }}">
                                        <button type="submit" style="width: 100%; background: #fee2e2; color: #dc2626; border: 1px solid #ef4444; border-radius: 6px; padding: 4px 0; font-size: 0.65rem; font-weight: 700; cursor: pointer;" title="Delete image">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Upload Form -->
        <div class="upload-card">
            <div class="upload-title">
                <i class="bi bi-cloud-arrow-up-fill"></i>
                Upload Product Photos (23+ Images Supported)
            </div>
            <p style="font-size: 0.75rem; color: #64748b; margin-top: -8px; margin-bottom: 14px;">
                Add multiple photos from your device or camera. Uploads run smoothly in safe batches with automatic WebP conversion.
            </p>

            <form action="{{ url('/product-image/' . $product->id . '/upload') }}" method="POST" enctype="multipart/form-data" id="uploadForm">
                @csrf

                <!-- Drop Zone for multiple gallery pick -->
                <div class="drop-zone" id="dropZone" onclick="document.getElementById('galleryInput').click()">
                    <div class="drop-zone-icon">
                        <i class="bi bi-images"></i>
                    </div>
                    <div class="drop-zone-text">Tap to select photos</div>
                    <div class="drop-zone-hint">Pick 1, 10, 23, or 50+ photos (JPG, PNG, WebP, HEIC)</div>
                    <input type="file" id="galleryInput" accept="image/*" multiple style="display:none;" onchange="handleFileInputChange(this)">
                </div>

                <!-- Camera Button (mobile / tablet) -->
                <label class="camera-btn" for="cameraInput">
                    <i class="bi bi-camera-fill"></i>
                    Snap Photo with Camera (+ Add)
                </label>
                <input type="file" id="cameraInput" accept="image/*" capture="environment" style="display:none;" onchange="handleFileInputChange(this)">

                <!-- Accumulated Preview Section -->
                <div class="image-preview" id="imagePreview">
                    <div class="preview-header">
                        <span class="preview-label">
                            <i class="bi bi-check-circle-fill"></i> Selected Photos (<span id="fileCountBadge">0</span>)
                        </span>
                        <button type="button" class="preview-clear" onclick="clearPreview()">
                            <i class="bi bi-trash"></i> Clear All
                        </button>
                    </div>

                    <!-- Accumulated thumbnails grid -->
                    <div class="preview-grid-accumulated" id="previewGrid"></div>

                    <div class="file-info" id="fileInfo" style="margin-top: 10px;">
                        <i class="bi bi-file-earmark-image"></i>
                        <span id="fileSummary">0 image(s) selected</span>
                    </div>
                </div>

                <!-- Batched Upload Progress Box -->
                <div class="upload-progress-box" id="uploadProgressBox">
                    <div class="progress-status-text">
                        <span id="progressTitle"><i class="bi bi-arrow-repeat"></i> Preparing upload...</span>
                        <span id="progressPercent">0%</span>
                    </div>
                    <div class="progress-bar-track">
                        <div class="progress-bar-fill" id="progressBarFill"></div>
                    </div>
                    <div style="font-size: 0.72rem; color: #64748b; margin-top: 4px;" id="progressSubtitle">
                        Converting photos to WebP & appending to gallery...
                    </div>
                </div>

                @error('images')
                <div class="alert alert-error" style="margin-top: 12px;">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                    {{ $message }}
                </div>
                @enderror

                <!-- Submit Button -->
                <button type="submit" class="submit-btn" id="submitBtn" disabled>
                    <div class="spinner"></div>
                    <span class="btn-text" id="submitBtnText">
                        <i class="bi bi-cloud-arrow-up-fill"></i>
                        Upload to Gallery
                    </span>
                </button>

                <!-- Feedback Notification Banner -->
                <div id="uploadFeedback" style="display:none; margin-top: 12px; border-radius: 12px; padding: 12px 16px; font-weight: 600; font-size: 0.85rem; text-align: center;"></div>
            </form>
        </div>

        <!-- Help Section -->
        <div style="background: #fff; border-radius: 16px; padding: 16px; box-shadow: 0 4px 20px rgba(0, 0, 0, 0.06); margin-top: 16px;">
            <div style="font-size: 0.7rem; font-weight: 800; color: #161b97; text-transform: uppercase; letter-spacing: 1.5px; margin-bottom: 10px;">
                <i class="bi bi-info-circle-fill"></i> Multi-Image Guide
            </div>
            <div style="font-size: 0.8rem; color: #475569; line-height: 1.6;">
                <div style="margin-bottom: 8px; display: flex; align-items: start; gap: 8px;">
                    <i class="bi bi-1-circle-fill" style="color: #161b97; margin-top: 2px;"></i>
                    <span><strong>Select multiple times:</strong> Tap "+ Add More" to combine photos from camera, WhatsApp, and gallery up to 23+ or 50+ photos.</span>
                </div>
                <div style="margin-bottom: 8px; display: flex; align-items: start; gap: 8px;">
                    <i class="bi bi-2-circle-fill" style="color: #161b97; margin-top: 2px;"></i>
                    <span><strong>Smart Batching:</strong> Files upload automatically in safe batches so large collections never timeout or crash.</span>
                </div>
                <div style="display: flex; align-items: start; gap: 8px;">
                    <i class="bi bi-3-circle-fill" style="color: #161b97; margin-top: 2px;"></i>
                    <span><strong>WebP Conversion:</strong> All images are converted to optimized WebP and displayed on the customer storefront with full gallery carousel navigation.</span>
                </div>
            </div>
        </div>
        @endif
    </div>

    <script>
        // Array of accumulated File objects
        let accumulatedFiles = [];
        const BATCH_SIZE = 6;
        const uploadUrl = "{{ isset($product) ? url('/product-image/' . $product->id . '/upload') : '' }}";
        const csrfToken = "{{ csrf_token() }}";

        // Drag and drop support on dropZone
        const dropZone = document.getElementById('dropZone');
        if (dropZone) {
            ['dragenter', 'dragover'].forEach(eventName => {
                dropZone.addEventListener(eventName, (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                    dropZone.classList.add('active');
                }, false);
            });

            ['dragleave', 'drop'].forEach(eventName => {
                dropZone.addEventListener(eventName, (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                    dropZone.classList.remove('active');
                }, false);
            });

            dropZone.addEventListener('drop', (e) => {
                const dt = e.dataTransfer;
                if (dt && dt.files && dt.files.length) {
                    handleFilesAdded(dt.files);
                }
            });
        }

        // File input change handler (appends without overwriting existing files)
        function handleFileInputChange(input) {
            if (input && input.files && input.files.length) {
                handleFilesAdded(input.files);
                // Reset input so selecting the same file again triggers change event
                input.value = '';
            }
        }

        // Add files to accumulated list (ignoring duplicate file signatures)
        function handleFilesAdded(fileList) {
            for (let i = 0; i < fileList.length; i++) {
                const f = fileList[i];
                // Check duplicate by name, size, lastModified
                const isDuplicate = accumulatedFiles.some(af =>
                    af.name === f.name && af.size === f.size && af.lastModified === f.lastModified
                );
                if (!isDuplicate) {
                    accumulatedFiles.push(f);
                }
            }
            renderAccumulatedPreviews();
        }

        // Remove a single file from accumulated list
        function removeAccumulatedFile(index) {
            if (index >= 0 && index < accumulatedFiles.length) {
                accumulatedFiles.splice(index, 1);
                renderAccumulatedPreviews();
            }
        }

        // Clear all selected files
        function clearPreview() {
            accumulatedFiles = [];
            renderAccumulatedPreviews();
        }

        // Format bytes into human readable string
        function formatBytes(bytes) {
            if (bytes === 0) return '0 B';
            const k = 1024;
            const sizes = ['B', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(1)) + ' ' + sizes[i];
        }

        // Re-render thumbnail grid
        function renderAccumulatedPreviews() {
            const previewSection = document.getElementById('imagePreview');
            const previewGrid = document.getElementById('previewGrid');
            const submitBtn = document.getElementById('submitBtn');
            const submitBtnText = document.getElementById('submitBtnText');
            const fileCountBadge = document.getElementById('fileCountBadge');
            const fileSummary = document.getElementById('fileSummary');
            const dropZone = document.getElementById('dropZone');

            if (!previewSection || !previewGrid) return;

            previewGrid.innerHTML = '';
            const count = accumulatedFiles.length;

            if (count === 0) {
                previewSection.classList.remove('show');
                if (dropZone) dropZone.classList.remove('has-file');
                if (submitBtn) submitBtn.disabled = true;
                if (submitBtnText) {
                    submitBtnText.innerHTML = '<i class="bi bi-cloud-arrow-up-fill"></i> Upload to Gallery';
                }
                return;
            }

            previewSection.classList.add('show');
            if (dropZone) dropZone.classList.add('has-file');
            if (submitBtn) submitBtn.disabled = false;
            if (fileCountBadge) fileCountBadge.textContent = count;

            let totalBytes = 0;

            accumulatedFiles.forEach((file, idx) => {
                totalBytes += file.size;
                const card = document.createElement('div');
                card.className = 'preview-card';

                const objectUrl = URL.createObjectURL(file);

                card.innerHTML = `
                    <span class="index-badge">#${idx + 1}</span>
                    <button type="button" class="remove-btn" onclick="removeAccumulatedFile(${idx})" title="Remove this photo">
                        <i class="bi bi-x"></i>
                    </button>
                    <img src="${objectUrl}" alt="Photo ${idx + 1}" class="img-thumb">
                    <div class="file-meta" title="${file.name}">${formatBytes(file.size)}</div>
                `;

                previewGrid.appendChild(card);
            });

            // Append "+ Add More" tile
            const addMoreTile = document.createElement('div');
            addMoreTile.className = 'add-more-tile';
            addMoreTile.title = 'Select more images to add to the upload queue';
            addMoreTile.onclick = () => document.getElementById('galleryInput').click();
            addMoreTile.innerHTML = `
                <i class="bi bi-plus-circle-fill"></i>
                <span>Add More</span>
            `;
            previewGrid.appendChild(addMoreTile);

            if (fileSummary) {
                fileSummary.textContent = `${count} photo${count > 1 ? 's' : ''} ready (${formatBytes(totalBytes)})`;
            }

            if (submitBtnText) {
                submitBtnText.innerHTML = `<i class="bi bi-cloud-arrow-up-fill"></i> Upload ${count} Image${count > 1 ? 's' : ''} to Gallery`;
            }
        }

        // Batched Upload execution
        document.getElementById('uploadForm')?.addEventListener('submit', async function(e) {
            e.preventDefault();

            if (!accumulatedFiles.length) {
                alert('Please select at least one photo to upload.');
                return;
            }

            const submitBtn = document.getElementById('submitBtn');
            const submitBtnText = document.getElementById('submitBtnText');
            const progressBox = document.getElementById('uploadProgressBox');
            const progressBarFill = document.getElementById('progressBarFill');
            const progressTitle = document.getElementById('progressTitle');
            const progressPercent = document.getElementById('progressPercent');
            const progressSubtitle = document.getElementById('progressSubtitle');
            const feedback = document.getElementById('uploadFeedback');

            submitBtn.classList.add('loading');
            submitBtn.disabled = true;
            if (feedback) feedback.style.display = 'none';
            if (progressBox) progressBox.style.display = 'block';

            const totalFiles = accumulatedFiles.length;
            const totalBatches = Math.ceil(totalFiles / BATCH_SIZE);
            let totalUploadedSuccessfully = 0;

            try {
                for (let batchIdx = 0; batchIdx < totalBatches; batchIdx++) {
                    const start = batchIdx * BATCH_SIZE;
                    const end = Math.min(start + BATCH_SIZE, totalFiles);
                    const chunk = accumulatedFiles.slice(start, end);

                    const percent = Math.round((batchIdx / totalBatches) * 100);
                    if (progressBarFill) progressBarFill.style.width = percent + '%';
                    if (progressPercent) progressPercent.textContent = percent + '%';
                    if (progressTitle) {
                        progressTitle.innerHTML = `<i class="bi bi-arrow-repeat spin-icon"></i> Batch ${batchIdx + 1} of ${totalBatches} (${start + 1}-${end} of ${totalFiles} photos)...`;
                    }
                    if (progressSubtitle) {
                        progressSubtitle.textContent = `Uploading ${chunk.length} photo(s) & converting to WebP...`;
                    }

                    const formData = new FormData();
                    formData.append('_token', csrfToken);
                    chunk.forEach(file => {
                        formData.append('images[]', file, file.name);
                    });

                    const response = await fetch(uploadUrl, {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        }
                    });

                    let data = {};
                    try {
                        data = await response.json();
                    } catch (err) {
                        data = { success: response.ok, message: 'Server responded with status ' + response.status };
                    }

                    if (!response.ok || !data.success) {
                        throw new Error(data.message || `Batch ${batchIdx + 1} failed with status ${response.status}`);
                    }

                    totalUploadedSuccessfully += chunk.length;
                }

                // All batches finished!
                if (progressBarFill) progressBarFill.style.width = '100%';
                if (progressPercent) progressPercent.textContent = '100%';
                if (progressTitle) {
                    progressTitle.innerHTML = '<i class="bi bi-check-circle-fill" style="color:#10b981;"></i> Upload complete!';
                }
                if (progressSubtitle) {
                    progressSubtitle.textContent = `All ${totalFiles} photos converted to WebP and added to gallery.`;
                }

                if (feedback) {
                    feedback.style.display = 'block';
                    feedback.style.background = '#d1fae5';
                    feedback.style.color = '#065f46';
                    feedback.style.border = '1px solid #a7f3d0';
                    feedback.innerHTML = `<i class="bi bi-check-circle-fill me-1"></i> 🎉 Successfully uploaded ${totalUploadedSuccessfully} photo(s)! Reloading gallery...`;
                }

                setTimeout(() => window.location.reload(), 1400);

            } catch (error) {
                console.error('Upload batch error:', error);
                submitBtn.classList.remove('loading');
                submitBtn.disabled = false;

                if (feedback) {
                    feedback.style.display = 'block';
                    feedback.style.background = '#fee2e2';
                    feedback.style.color = '#991b1b';
                    feedback.style.border = '1px solid #fecaca';
                    feedback.innerHTML = `<i class="bi bi-exclamation-triangle-fill me-1"></i> Upload interrupted: ${error.message}. ${totalUploadedSuccessfully > 0 ? `(${totalUploadedSuccessfully} photos were already saved to gallery).` : ''}`;
                } else {
                    alert('Upload interrupted: ' + error.message);
                }

                // Retain remaining un-uploaded files in accumulatedFiles so user can retry
                if (totalUploadedSuccessfully > 0) {
                    accumulatedFiles = accumulatedFiles.slice(totalUploadedSuccessfully);
                    renderAccumulatedPreviews();
                }
            }
        });

        // Quick Storefront Website Live Toggle
        async function toggleWebsiteLive() {
            const toggleBtn = document.getElementById('liveToggleBtn');
            const toggleBtnText = document.getElementById('liveToggleBtnText');
            const badge = document.getElementById('websiteLiveBadge');
            const badgeText = document.getElementById('websiteLiveText');

            if (!toggleBtn) return;
            toggleBtn.disabled = true;

            try {
                const response = await fetch("{{ isset($product) ? url('/product-image/' . $product->id . '/toggle-website-live') : '' }}", {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                });

                const data = await response.json();
                if (data.success) {
                    const isLive = data.website_live;
                    if (isLive) {
                        badge.style.background = '#dcfce7';
                        badge.style.color = '#15803d';
                        badge.style.borderColor = '#86efac';
                        badge.querySelector('i').className = 'bi bi-broadcast';
                        badgeText.textContent = 'LIVE ON STOREFRONT';

                        toggleBtn.style.background = '#fee2e2';
                        toggleBtn.style.color = '#b91c1c';
                        toggleBtn.style.borderColor = '#fca5a5';
                        toggleBtn.querySelector('i').className = 'bi bi-eye-slash';
                        toggleBtnText.textContent = 'Set to Hidden';
                    } else {
                        badge.style.background = '#f1f5f9';
                        badge.style.color = '#64748b';
                        badge.style.borderColor = '#cbd5e1';
                        badge.querySelector('i').className = 'bi bi-eye-slash';
                        badgeText.textContent = 'HIDDEN FROM STOREFRONT';

                        toggleBtn.style.background = '#f0fdf4';
                        toggleBtn.style.color = '#15803d';
                        toggleBtn.style.borderColor = '#86efac';
                        toggleBtn.querySelector('i').className = 'bi bi-check-circle-fill';
                        toggleBtnText.textContent = 'Make Live Now';
                    }
                } else {
                    alert(data.message || 'Unable to update status.');
                }
            } catch (err) {
                alert('Network error while toggling website live status.');
            } finally {
                toggleBtn.disabled = false;
            }
        }
    </script>
</body>

</html>