<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="theme-color" content="#161b97">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Product Image Scanner — Jaffna Gold</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }

        body {
            font-family: 'Inter', sans-serif;
            background: #0a0e2a;
            min-height: 100vh;
            min-height: 100dvh;
            color: #e2e8f0;
            overflow-x: hidden;
        }

        /* ─── Header ─── */
        .app-header {
            background: linear-gradient(135deg, #161b97 0%, #0d1066 100%);
            padding: 16px 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 200;
            box-shadow: 0 4px 24px rgba(22, 27, 151, 0.5);
        }

        .app-header .logo {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .app-header .logo-icon {
            width: 36px; height: 36px;
            background: rgba(255,255,255,0.15);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
            color: #fff;
        }

        .app-header h1 {
            color: #fff;
            font-size: 0.95rem;
            font-weight: 800;
            letter-spacing: 0.5px;
        }

        .app-header .subtitle {
            color: rgba(255,255,255,0.5);
            font-size: 0.6rem;
            font-weight: 700;
            letter-spacing: 2px;
            text-transform: uppercase;
        }

        .session-counter {
            background: rgba(255,255,255,0.12);
            border: 1px solid rgba(255,255,255,0.15);
            border-radius: 12px;
            padding: 6px 14px;
            text-align: center;
        }

        .session-counter .count {
            font-size: 1.3rem;
            font-weight: 900;
            color: #4ade80;
        }

        .session-counter .label {
            font-size: 0.55rem;
            font-weight: 700;
            color: rgba(255,255,255,0.5);
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        /* ─── States ─── */
        .state { display: none; }
        .state.active { display: block; }

        /* ─── Scanner State ─── */
        .scanner-container {
            padding: 20px 16px;
            max-width: 600px;
            margin: 0 auto;
        }

        .scanner-title {
            text-align: center;
            margin-bottom: 20px;
        }

        .scanner-title h2 {
            font-size: 1.2rem;
            font-weight: 800;
            color: #fff;
            margin-bottom: 4px;
        }

        .scanner-title p {
            font-size: 0.78rem;
            color: rgba(255,255,255,0.5);
            font-weight: 500;
        }

        .scanner-box {
            position: relative;
            background: #111432;
            border-radius: 20px;
            overflow: hidden;
            border: 2px solid rgba(255,255,255,0.08);
            box-shadow: 0 8px 40px rgba(0,0,0,0.4);
        }

        #qr-reader {
            width: 100% !important;
            border: none !important;
        }

        #qr-reader video {
            border-radius: 18px;
            object-fit: cover;
        }

        #qr-reader__scan_region {
            min-height: 280px;
        }

        #qr-reader__dashboard {
            padding: 12px !important;
        }

        #qr-reader__dashboard_section_csr button {
            background: linear-gradient(135deg, #161b97, #0d1066) !important;
            color: #fff !important;
            border: none !important;
            border-radius: 12px !important;
            padding: 12px 20px !important;
            font-family: 'Inter', sans-serif !important;
            font-weight: 700 !important;
            font-size: 0.85rem !important;
            cursor: pointer !important;
        }

        #qr-reader__dashboard_section_csr select {
            background: #1a1f4a !important;
            color: #e2e8f0 !important;
            border: 1px solid rgba(255,255,255,0.15) !important;
            border-radius: 10px !important;
            padding: 8px 12px !important;
            font-family: 'Inter', sans-serif !important;
            font-weight: 600 !important;
            font-size: 0.8rem !important;
        }

        #qr-reader__status_span {
            color: rgba(255,255,255,0.6) !important;
            font-size: 0.75rem !important;
        }

        #qr-reader__dashboard_section_fsr {
            display: none !important;
        }

        #qr-reader img[alt="Info icon"] {
            display: none !important;
        }

        #qr-reader__header_message {
            color: rgba(255,255,255,0.5) !important;
            font-size: 0.7rem !important;
            border: none !important;
            padding: 6px !important;
        }

        /* Manual entry */
        .manual-entry {
            margin-top: 20px;
            background: rgba(255,255,255,0.04);
            border: 1px solid rgba(255,255,255,0.08);
            border-radius: 16px;
            padding: 18px;
        }

        .manual-entry label {
            font-size: 0.7rem;
            font-weight: 800;
            color: rgba(255,255,255,0.5);
            text-transform: uppercase;
            letter-spacing: 1.5px;
            display: block;
            margin-bottom: 10px;
        }

        .manual-entry .input-row {
            display: flex;
            gap: 10px;
        }

        .manual-entry input {
            flex: 1;
            background: #111432;
            border: 1.5px solid rgba(255,255,255,0.12);
            border-radius: 12px;
            padding: 12px 16px;
            color: #fff;
            font-family: 'Inter', sans-serif;
            font-size: 0.9rem;
            font-weight: 600;
            outline: none;
            transition: border-color 0.2s;
        }

        .manual-entry input:focus {
            border-color: #4f6df5;
        }

        .manual-entry input::placeholder {
            color: rgba(255,255,255,0.25);
        }

        .manual-entry button {
            background: linear-gradient(135deg, #4f6df5, #161b97);
            color: #fff;
            border: none;
            border-radius: 12px;
            padding: 12px 20px;
            font-weight: 800;
            font-size: 0.85rem;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 6px;
            white-space: nowrap;
            transition: transform 0.15s;
        }

        .manual-entry button:active {
            transform: scale(0.96);
        }

        /* ─── Upload State ─── */
        .upload-container {
            padding: 20px 16px;
            max-width: 600px;
            margin: 0 auto;
        }

        .back-btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: rgba(255,255,255,0.08);
            border: 1px solid rgba(255,255,255,0.1);
            color: rgba(255,255,255,0.7);
            border-radius: 10px;
            padding: 8px 16px;
            font-size: 0.78rem;
            font-weight: 700;
            cursor: pointer;
            margin-bottom: 16px;
            transition: all 0.2s;
        }

        .back-btn:active { transform: scale(0.96); }
        .back-btn:hover { background: rgba(255,255,255,0.12); color: #fff; }

        /* Product Card */
        .product-card {
            background: linear-gradient(145deg, #161b4a, #111432);
            border: 1.5px solid rgba(255,255,255,0.08);
            border-radius: 20px;
            overflow: hidden;
            margin-bottom: 16px;
            box-shadow: 0 8px 32px rgba(0,0,0,0.3);
        }

        .product-card-header {
            padding: 20px;
        }

        .product-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: rgba(79,109,245,0.15);
            color: #7c9aff;
            font-size: 0.62rem;
            font-weight: 800;
            padding: 5px 12px;
            border-radius: 20px;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            margin-bottom: 10px;
        }

        .product-name {
            font-size: 1.2rem;
            font-weight: 900;
            color: #fff;
            line-height: 1.3;
            margin-bottom: 4px;
        }

        .product-code {
            font-size: 0.78rem;
            font-weight: 700;
            color: rgba(255,255,255,0.4);
            font-family: 'JetBrains Mono', monospace;
            letter-spacing: 0.5px;
        }

        .product-meta-row {
            display: flex;
            gap: 16px;
            margin-top: 14px;
            padding-top: 14px;
            border-top: 1px solid rgba(255,255,255,0.06);
            flex-wrap: wrap;
        }

        .meta-item {
            display: flex;
            flex-direction: column;
            gap: 2px;
        }

        .meta-label {
            font-size: 0.58rem;
            font-weight: 800;
            color: rgba(255,255,255,0.3);
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .meta-value {
            font-size: 0.82rem;
            font-weight: 700;
            color: rgba(255,255,255,0.8);
        }

        .image-status {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 12px 20px;
            background: rgba(0,0,0,0.2);
            border-top: 1px solid rgba(255,255,255,0.05);
        }

        .image-status .dot {
            width: 8px; height: 8px;
            border-radius: 50%;
            flex-shrink: 0;
        }

        .image-status .dot.has-image { background: #4ade80; box-shadow: 0 0 8px rgba(74,222,128,0.5); }
        .image-status .dot.no-image { background: #f97316; box-shadow: 0 0 8px rgba(249,115,22,0.5); }

        .image-status span {
            font-size: 0.72rem;
            font-weight: 700;
            color: rgba(255,255,255,0.5);
        }

        /* Upload Actions */
        .upload-actions {
            background: linear-gradient(145deg, #161b4a, #111432);
            border: 1.5px solid rgba(255,255,255,0.08);
            border-radius: 20px;
            padding: 20px;
            margin-bottom: 16px;
        }

        .upload-actions-title {
            font-size: 0.7rem;
            font-weight: 800;
            color: rgba(255,255,255,0.4);
            text-transform: uppercase;
            letter-spacing: 1.5px;
            margin-bottom: 16px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .action-btn {
            width: 100%;
            padding: 18px 20px;
            border: 2px solid rgba(255,255,255,0.08);
            border-radius: 16px;
            font-family: 'Inter', sans-serif;
            font-size: 0.9rem;
            font-weight: 700;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 14px;
            transition: all 0.2s;
            margin-bottom: 10px;
            position: relative;
            overflow: hidden;
        }

        .action-btn:active { transform: scale(0.97); }

        .action-btn.camera-action {
            background: linear-gradient(135deg, #161b97, #1e40af);
            color: #fff;
            border-color: rgba(79,109,245,0.3);
        }

        .action-btn.camera-action:hover {
            border-color: #4f6df5;
            box-shadow: 0 4px 20px rgba(79,109,245,0.3);
        }

        .action-btn.gallery-action {
            background: rgba(255,255,255,0.04);
            color: rgba(255,255,255,0.8);
        }

        .action-btn.gallery-action:hover {
            background: rgba(255,255,255,0.08);
            border-color: rgba(255,255,255,0.15);
        }

        .action-btn .btn-icon {
            width: 44px; height: 44px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.3rem;
            flex-shrink: 0;
        }

        .camera-action .btn-icon { background: rgba(255,255,255,0.15); }
        .gallery-action .btn-icon { background: rgba(255,255,255,0.06); }

        .action-btn .btn-label {
            display: flex;
            flex-direction: column;
            gap: 2px;
            text-align: left;
        }

        .action-btn .btn-label .primary { font-weight: 800; }
        .action-btn .btn-label .secondary {
            font-size: 0.68rem;
            font-weight: 500;
            opacity: 0.6;
        }

        .action-btn input[type="file"] {
            position: absolute;
            inset: 0;
            opacity: 0;
            cursor: pointer;
        }

        /* Upload preview */
        .upload-preview {
            display: none;
            background: linear-gradient(145deg, #161b4a, #111432);
            border: 1.5px solid rgba(255,255,255,0.08);
            border-radius: 20px;
            padding: 20px;
            margin-bottom: 16px;
        }

        .upload-preview.show { display: block; }

        .preview-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 14px;
        }

        .preview-header .title {
            font-size: 0.7rem;
            font-weight: 800;
            color: #4ade80;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .preview-header .clear-btn {
            background: none;
            border: none;
            color: #f87171;
            font-size: 0.72rem;
            font-weight: 700;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .preview-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(90px, 1fr));
            gap: 8px;
            max-height: 300px;
            overflow-y: auto;
        }

        .preview-thumb {
            position: relative;
            background: rgba(0,0,0,0.3);
            border-radius: 10px;
            overflow: hidden;
            aspect-ratio: 1;
        }

        .preview-thumb img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .preview-thumb .remove-btn {
            position: absolute;
            top: 4px;
            right: 4px;
            width: 20px;
            height: 20px;
            border-radius: 50%;
            background: rgba(239, 68, 68, 0.9);
            color: #fff;
            border: none;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.65rem;
        }

        /* Upload button */
        .upload-btn {
            width: 100%;
            padding: 18px;
            margin-top: 14px;
            background: linear-gradient(135deg, #10b981, #059669);
            color: #fff;
            border: none;
            border-radius: 16px;
            font-family: 'Inter', sans-serif;
            font-size: 0.95rem;
            font-weight: 800;
            letter-spacing: 0.5px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            box-shadow: 0 6px 24px rgba(16, 185, 129, 0.35);
            transition: all 0.2s;
        }

        .upload-btn:active { transform: scale(0.97); }
        .upload-btn:disabled { opacity: 0.4; cursor: not-allowed; transform: none; box-shadow: none; }

        .upload-btn .spinner {
            display: none;
            width: 20px; height: 20px;
            border: 3px solid rgba(255,255,255,0.3);
            border-top-color: #fff;
            border-radius: 50%;
            animation: spin 0.8s ease-in-out infinite;
        }

        .upload-btn.loading .spinner { display: inline-block; }
        .upload-btn.loading .btn-text { display: none; }

        @keyframes spin { to { transform: rotate(360deg); } }

        /* Progress */
        .upload-progress {
            display: none;
            margin-top: 12px;
            padding: 14px;
            background: rgba(0,0,0,0.2);
            border-radius: 12px;
        }

        .upload-progress.show { display: block; }

        .progress-track {
            height: 8px;
            background: rgba(255,255,255,0.08);
            border-radius: 4px;
            overflow: hidden;
            margin: 8px 0;
        }

        .progress-fill {
            height: 100%;
            width: 0%;
            background: linear-gradient(90deg, #4ade80, #10b981);
            border-radius: 4px;
            transition: width 0.3s;
        }

        .progress-text {
            display: flex;
            justify-content: space-between;
            font-size: 0.72rem;
            font-weight: 700;
            color: rgba(255,255,255,0.6);
        }

        /* Toast */
        .toast {
            display: none;
            position: fixed;
            top: 80px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 999;
            padding: 16px 28px;
            border-radius: 16px;
            font-weight: 800;
            font-size: 0.9rem;
            text-align: center;
            box-shadow: 0 8px 32px rgba(0,0,0,0.4);
            animation: toastIn 0.4s ease;
            max-width: 90vw;
        }

        .toast.success {
            background: linear-gradient(135deg, #065f46, #047857);
            color: #d1fae5;
            border: 1px solid rgba(74,222,128,0.3);
        }

        .toast.error {
            background: linear-gradient(135deg, #7f1d1d, #991b1b);
            color: #fee2e2;
            border: 1px solid rgba(248,113,113,0.3);
        }

        @keyframes toastIn {
            from { opacity: 0; transform: translateX(-50%) translateY(-20px); }
            to { opacity: 1; transform: translateX(-50%) translateY(0); }
        }

        /* Scan next pill */
        .scan-next-pill {
            display: none;
            position: fixed;
            bottom: 30px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 500;
            background: linear-gradient(135deg, #161b97, #1e40af);
            color: #fff;
            border: none;
            border-radius: 50px;
            padding: 16px 32px;
            font-family: 'Inter', sans-serif;
            font-size: 0.9rem;
            font-weight: 800;
            cursor: pointer;
            box-shadow: 0 8px 32px rgba(22,27,151,0.5);
            animation: pulseGlow 2s ease-in-out infinite;
        }

        .scan-next-pill.show { display: flex; align-items: center; gap: 10px; }

        @keyframes pulseGlow {
            0%, 100% { box-shadow: 0 8px 32px rgba(22,27,151,0.5); }
            50% { box-shadow: 0 8px 48px rgba(79,109,245,0.6); }
        }

        /* Detail link */
        .detail-link {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            margin-top: 10px;
            color: rgba(255,255,255,0.35);
            font-size: 0.72rem;
            font-weight: 600;
            text-decoration: none;
            transition: color 0.2s;
        }

        .detail-link:hover { color: rgba(255,255,255,0.6); }

        /* ─── Existing Images Section ─── */
        .existing-images-card {
            background: linear-gradient(145deg, #161b4a, #111432);
            border: 1.5px solid rgba(255,255,255,0.08);
            border-radius: 20px;
            padding: 20px;
            margin-bottom: 16px;
            box-shadow: 0 8px 32px rgba(0,0,0,0.3);
        }

        .existing-images-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 14px;
        }

        .existing-images-header .title {
            font-size: 0.72rem;
            font-weight: 800;
            color: #7c9aff;
            text-transform: uppercase;
            letter-spacing: 1.2px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .existing-images-header .badge {
            font-size: 0.65rem;
            font-weight: 800;
            background: rgba(74,222,128,0.15);
            color: #4ade80;
            padding: 4px 10px;
            border-radius: 20px;
            letter-spacing: 0.5px;
        }

        .existing-images-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(95px, 1fr));
            gap: 10px;
        }

        .existing-thumb {
            position: relative;
            background: rgba(0,0,0,0.3);
            border-radius: 14px;
            overflow: hidden;
            aspect-ratio: 1;
            border: 2px solid rgba(255,255,255,0.1);
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .existing-thumb:hover, .existing-thumb:active {
            transform: scale(1.03);
            border-color: #4f6df5;
            box-shadow: 0 4px 16px rgba(79,109,245,0.3);
        }

        .existing-thumb img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        .existing-thumb .primary-tag {
            position: absolute;
            top: 6px;
            left: 6px;
            background: rgba(22, 27, 151, 0.9);
            color: #ffd700;
            font-size: 0.55rem;
            font-weight: 800;
            padding: 2px 6px;
            border-radius: 6px;
            backdrop-filter: blur(4px);
            display: flex;
            align-items: center;
            gap: 3px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.4);
        }

        .existing-thumb .delete-btn {
            position: absolute;
            top: 6px;
            right: 6px;
            width: 24px;
            height: 24px;
            border-radius: 50%;
            background: rgba(239, 68, 68, 0.9);
            color: #fff;
            border: none;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.75rem;
            box-shadow: 0 2px 6px rgba(0,0,0,0.4);
            transition: transform 0.15s;
        }

        .existing-thumb .delete-btn:active {
            transform: scale(0.85);
        }

        .no-existing-images {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 24px 16px;
            background: rgba(0,0,0,0.15);
            border: 1px dashed rgba(255,255,255,0.1);
            border-radius: 14px;
            text-align: center;
            color: rgba(255,255,255,0.4);
            gap: 8px;
        }

        .no-existing-images i {
            font-size: 1.6rem;
            color: rgba(255,255,255,0.25);
        }

        .no-existing-images span {
            font-size: 0.78rem;
            font-weight: 600;
        }

        /* ─── Lightbox Modal ─── */
        .lightbox-modal {
            display: none;
            position: fixed;
            inset: 0;
            z-index: 1000;
            background: rgba(0, 0, 0, 0.92);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            align-items: center;
            justify-content: center;
            padding: 20px;
            animation: fadeIn 0.2s ease;
        }

        .lightbox-modal.show {
            display: flex;
        }

        .lightbox-container {
            position: relative;
            max-width: 95vw;
            max-height: 85vh;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .lightbox-container img {
            max-width: 100%;
            max-height: 75vh;
            border-radius: 18px;
            box-shadow: 0 16px 48px rgba(0,0,0,0.7);
            object-fit: contain;
        }

        .lightbox-close-btn {
            position: absolute;
            top: -48px;
            right: 0;
            background: rgba(255,255,255,0.18);
            color: #fff;
            border: none;
            width: 38px;
            height: 38px;
            border-radius: 50%;
            font-size: 1.2rem;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background 0.2s;
        }

        .lightbox-close-btn:active {
            background: rgba(255,255,255,0.3);
        }

        .lightbox-caption {
            margin-top: 14px;
            font-size: 0.82rem;
            font-weight: 700;
            color: rgba(255,255,255,0.75);
            text-align: center;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
    </style>
</head>

<body>
    <!-- Header -->
    <div class="app-header">
        <div class="logo">
            <div class="logo-icon"><i class="bi bi-qr-code-scan"></i></div>
            <div>
                <h1>Product Scanner</h1>
                <div class="subtitle">Jaffna Gold</div>
            </div>
        </div>
        <div class="session-counter">
            <div class="count" id="sessionCount">0</div>
            <div class="label">Uploaded</div>
        </div>
    </div>

    <!-- Toast -->
    <div class="toast" id="toast"></div>

    <!-- Scan Next Pill (floating) -->
    <button class="scan-next-pill" id="scanNextPill" onclick="switchToScanner()">
        <i class="bi bi-qr-code-scan"></i> Scan Next Product
    </button>

    <!-- ═══════════════ STATE 1: SCANNER ═══════════════ -->
    <div class="state active" id="stateScanner">
        <div class="scanner-container">
            <div class="scanner-title">
                <h2><i class="bi bi-viewfinder"></i> Scan Product QR</h2>
                <p>Point your camera at the product tag QR code</p>
            </div>

            <div class="scanner-box">
                <div id="qr-reader"></div>
            </div>

            <!-- Manual barcode entry -->
            <div class="manual-entry">
                <label><i class="bi bi-keyboard"></i> Or type barcode manually</label>
                <div class="input-row">
                    <input type="text" id="manualBarcodeInput" placeholder="Enter barcode or product code..."
                           enterkeyhint="search" autocomplete="off" autocorrect="off" autocapitalize="off" spellcheck="false">
                    <button type="button" onclick="lookupManual()">
                        <i class="bi bi-search"></i> Find
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- ═══════════════ STATE 2: UPLOAD ═══════════════ -->
    <div class="state" id="stateUpload">
        <div class="upload-container">
            <button class="back-btn" onclick="switchToScanner()">
                <i class="bi bi-arrow-left"></i> Back to Scanner
            </button>

            <!-- Product Info Card -->
            <div class="product-card" id="productCard">
                <div class="product-card-header">
                    <div class="product-badge" id="prodBadge"></div>
                    <h2 class="product-name" id="prodName"></h2>
                    <div class="product-code" id="prodCode"></div>
                    <div class="product-meta-row">
                        <div class="meta-item" id="metaBrand" style="display:none;">
                            <span class="meta-label">Brand</span>
                            <span class="meta-value" id="prodBrand"></span>
                        </div>
                        <div class="meta-item" id="metaCategory" style="display:none;">
                            <span class="meta-label">Category</span>
                            <span class="meta-value" id="prodCategory"></span>
                        </div>
                        <div class="meta-item">
                            <span class="meta-label">Images</span>
                            <span class="meta-value" id="prodImageCount">0</span>
                        </div>
                    </div>
                </div>
                <div class="image-status" id="imageStatus">
                    <span class="dot" id="imageStatusDot"></span>
                    <span id="imageStatusText"></span>
                </div>
            </div>

            <!-- Existing Product Photos Section -->
            <div class="existing-images-card" id="existingImagesCard">
                <div class="existing-images-header">
                    <div class="title">
                        <i class="bi bi-images"></i> Product Photos (<span id="existingImagesCount">0</span>)
                    </div>
                    <span class="badge" id="existingStatusBadge">In System</span>
                </div>
                
                <div class="existing-images-grid" id="existingImagesGrid"></div>

                <div class="no-existing-images" id="noExistingImages" style="display:none;">
                    <i class="bi bi-camera"></i>
                    <span>No photos uploaded yet for this product. Add one below!</span>
                </div>
            </div>

            <!-- Upload Actions -->
            <div class="upload-actions">
                <div class="upload-actions-title">
                    <i class="bi bi-camera-fill"></i> Add Photos
                </div>

                <!-- Camera capture -->
                <label class="action-btn camera-action">
                    <div class="btn-icon"><i class="bi bi-camera-fill"></i></div>
                    <div class="btn-label">
                        <span class="primary">Take Photo</span>
                        <span class="secondary">Open camera to snap a picture</span>
                    </div>
                    <input type="file" accept="image/*" capture="environment" onchange="handlePhotos(this)">
                </label>

                <!-- Gallery pick -->
                <label class="action-btn gallery-action">
                    <div class="btn-icon"><i class="bi bi-images"></i></div>
                    <div class="btn-label">
                        <span class="primary">Pick from Gallery</span>
                        <span class="secondary">Select one or more photos</span>
                    </div>
                    <input type="file" accept="image/*" multiple onchange="handlePhotos(this)">
                </label>
            </div>

            <!-- Preview -->
            <div class="upload-preview" id="uploadPreview">
                <div class="preview-header">
                    <span class="title"><i class="bi bi-check-circle-fill"></i> Ready (<span id="previewCount">0</span>)</span>
                    <button class="clear-btn" onclick="clearPhotos()"><i class="bi bi-trash"></i> Clear</button>
                </div>
                <div class="preview-grid" id="previewGrid"></div>

                <!-- Upload btn -->
                <button class="upload-btn" id="uploadBtn" onclick="startUpload()" disabled>
                    <div class="spinner"></div>
                    <span class="btn-text" id="uploadBtnText">
                        <i class="bi bi-cloud-arrow-up-fill"></i> Upload Photos
                    </span>
                </button>

                <!-- Progress -->
                <div class="upload-progress" id="uploadProgress">
                    <div class="progress-text">
                        <span id="progressTitle">Uploading...</span>
                        <span id="progressPercent">0%</span>
                    </div>
                    <div class="progress-track">
                        <div class="progress-fill" id="progressFill"></div>
                    </div>
                </div>
            </div>

            <!-- Full detail link -->
            <a class="detail-link" id="detailLink" href="#" target="_blank">
                <i class="bi bi-box-arrow-up-right"></i> Open full product page
            </a>
        </div>
    </div>

    <!-- html5-qrcode library -->
    <script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>

    <script>
        // ─── State ───
        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
        const lookupUrl = "{{ route('product-image.lookup') }}";
        let currentProduct = null;
        let pendingFiles = [];
        let sessionUploaded = 0;
        let html5QrCode = null;
        let isScanning = false;
        let scanCooldown = false;
        const BATCH_SIZE = 6;

        // ─── Scanner Init ───
        function initScanner() {
            if (html5QrCode) return;

            html5QrCode = new Html5Qrcode("qr-reader");

            const config = {
                fps: 10,
                qrbox: { width: 220, height: 220 },
                aspectRatio: 1.0,
                formatsToSupport: [
                    Html5QrcodeSupportedFormats.QR_CODE,
                    Html5QrcodeSupportedFormats.CODE_128,
                    Html5QrcodeSupportedFormats.CODE_39,
                    Html5QrcodeSupportedFormats.EAN_13,
                    Html5QrcodeSupportedFormats.EAN_8,
                    Html5QrcodeSupportedFormats.DATA_MATRIX,
                ],
            };

            html5QrCode.start(
                { facingMode: "environment" },
                config,
                onScanSuccess,
                () => {} // ignore scan failures
            ).then(() => {
                isScanning = true;
            }).catch(err => {
                console.warn("Camera start failed:", err);
            });
        }

        function stopScanner() {
            if (html5QrCode && isScanning) {
                html5QrCode.stop().then(() => {
                    isScanning = false;
                }).catch(() => {});
            }
        }

        // ─── Scan Success ───
        function onScanSuccess(decodedText) {
            if (scanCooldown) return;
            scanCooldown = true;

            // Haptic feedback
            if (navigator.vibrate) navigator.vibrate(100);

            lookupProduct(decodedText.trim());

            setTimeout(() => { scanCooldown = false; }, 2500);
        }

        // ─── Manual Entry ───
        document.getElementById('manualBarcodeInput')?.addEventListener('keydown', (e) => {
            if (e.key === 'Enter') {
                e.preventDefault();
                lookupManual();
            }
        });

        function lookupManual() {
            const input = document.getElementById('manualBarcodeInput');
            const code = (input?.value || '').trim();
            if (!code) { input?.focus(); return; }
            lookupProduct(code);
        }

        // ─── Product Lookup (AJAX) ───
        async function lookupProduct(code) {
            try {
                const resp = await fetch(lookupUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ code }),
                });

                const data = await resp.json();

                if (data.success && data.product) {
                    currentProduct = data.product;
                    showUploadState(data.product);
                } else {
                    showToast(data.message || 'Product not found', 'error');
                }
            } catch (err) {
                showToast('Network error — check your connection', 'error');
            }
        }

        // ─── Switch States ───
        function showUploadState(product) {
            stopScanner();
            clearPhotos();

            // Fill product card
            document.getElementById('prodBadge').innerHTML =
                `<i class="bi bi-qr-code"></i> #${product.id} — ${product.barcode || product.code}`;
            document.getElementById('prodName').textContent = product.name;
            document.getElementById('prodCode').textContent = product.code;

            if (product.brand) {
                document.getElementById('metaBrand').style.display = '';
                document.getElementById('prodBrand').textContent = product.brand;
            } else {
                document.getElementById('metaBrand').style.display = 'none';
            }

            if (product.category) {
                document.getElementById('metaCategory').style.display = '';
                document.getElementById('prodCategory').textContent = product.category;
            } else {
                document.getElementById('metaCategory').style.display = 'none';
            }

            document.getElementById('prodImageCount').textContent = product.image_count;

            const dot = document.getElementById('imageStatusDot');
            const statusText = document.getElementById('imageStatusText');
            if (product.has_image) {
                dot.className = 'dot has-image';
                statusText.textContent = `${product.image_count} image(s) in gallery`;
            } else {
                dot.className = 'dot no-image';
                statusText.textContent = 'No photos yet — add one now!';
            }

            document.getElementById('detailLink').href = product.detail_url;

            // Render existing images
            renderExistingImages(product.existing_images || []);

            // Switch states
            document.getElementById('stateScanner').classList.remove('active');
            document.getElementById('stateUpload').classList.add('active');
            document.getElementById('scanNextPill').classList.remove('show');
        }

        // ─── Render Existing Product Photos ───
        function renderExistingImages(images) {
            const grid = document.getElementById('existingImagesGrid');
            const countEl = document.getElementById('existingImagesCount');
            const noImagesEl = document.getElementById('noExistingImages');
            const badgeEl = document.getElementById('existingStatusBadge');

            grid.innerHTML = '';

            if (!images || images.length === 0) {
                countEl.textContent = '0';
                badgeEl.style.display = 'none';
                noImagesEl.style.display = 'flex';
                return;
            }

            noImagesEl.style.display = 'none';
            badgeEl.style.display = 'inline-block';
            countEl.textContent = images.length;

            images.forEach((imgObj, idx) => {
                const thumb = document.createElement('div');
                thumb.className = 'existing-thumb';

                const primaryTag = imgObj.is_primary ?
                    `<div class="primary-tag"><i class="bi bi-star-fill"></i> Cover</div>` : '';

                thumb.innerHTML = `
                    <img src="${imgObj.url}" alt="Existing Photo ${idx + 1}" onclick="openLightbox('${imgObj.url}', 'Photo ${idx + 1} of ${images.length}')">
                    ${primaryTag}
                    <button class="delete-btn" onclick="deleteExistingPhoto(event, '${imgObj.path}')" title="Delete Photo"><i class="bi bi-trash-fill"></i></button>
                `;

                grid.appendChild(thumb);
            });
        }

        // ─── Delete Existing Photo ───
        async function deleteExistingPhoto(event, path) {
            event.stopPropagation();
            if (!currentProduct || !path) return;

            if (!confirm('Are you sure you want to delete this photo from the product?')) {
                return;
            }

            try {
                const resp = await fetch(currentProduct.delete_url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ image_path: path }),
                });

                const data = await resp.json();

                if (data.success) {
                    showToast('Photo deleted', 'success');
                    const updatedImages = data.existing_images || [];
                    currentProduct.existing_images = updatedImages;
                    renderExistingImages(updatedImages);

                    // Update header counters
                    document.getElementById('prodImageCount').textContent = updatedImages.length;
                    const dot = document.getElementById('imageStatusDot');
                    const statusText = document.getElementById('imageStatusText');
                    if (updatedImages.length > 0) {
                        dot.className = 'dot has-image';
                        statusText.textContent = `${updatedImages.length} image(s) in gallery`;
                    } else {
                        dot.className = 'dot no-image';
                        statusText.textContent = 'No photos yet — add one now!';
                    }
                } else {
                    showToast(data.message || 'Failed to delete photo', 'error');
                }
            } catch (err) {
                showToast('Network error while deleting photo', 'error');
            }
        }

        // ─── Lightbox Preview ───
        function openLightbox(url, caption) {
            const modal = document.getElementById('lightboxModal');
            const img = document.getElementById('lightboxImage');
            const cap = document.getElementById('lightboxCaption');

            img.src = url;
            cap.textContent = caption || '';
            modal.classList.add('show');
        }

        function closeLightbox(event) {
            if (event.target.id === 'lightboxModal') {
                closeLightboxDirect();
            }
        }

        function closeLightboxDirect() {
            document.getElementById('lightboxModal').classList.remove('show');
        }

        function switchToScanner() {
            currentProduct = null;
            clearPhotos();

            document.getElementById('stateUpload').classList.remove('active');
            document.getElementById('stateScanner').classList.add('active');
            document.getElementById('scanNextPill').classList.remove('show');
            document.getElementById('manualBarcodeInput').value = '';

            // Re-initialize scanner
            setTimeout(() => {
                if (html5QrCode) {
                    if (isScanning) {
                        html5QrCode.stop().then(() => {
                            isScanning = false;
                            html5QrCode = null;
                            document.getElementById('qr-reader').innerHTML = '';
                            initScanner();
                        }).catch(() => {
                            html5QrCode = null;
                            document.getElementById('qr-reader').innerHTML = '';
                            initScanner();
                        });
                    } else {
                        html5QrCode = null;
                        document.getElementById('qr-reader').innerHTML = '';
                        initScanner();
                    }
                } else {
                    initScanner();
                }
            }, 300);
        }

        // ─── Photo Handling ───
        function handlePhotos(input) {
            if (!input || !input.files || !input.files.length) return;

            for (let i = 0; i < input.files.length; i++) {
                const f = input.files[i];
                const isDuplicate = pendingFiles.some(pf =>
                    pf.name === f.name && pf.size === f.size && pf.lastModified === f.lastModified
                );
                if (!isDuplicate) pendingFiles.push(f);
            }

            input.value = '';
            renderPreviews();
        }

        function removePhoto(idx) {
            pendingFiles.splice(idx, 1);
            renderPreviews();
        }

        function clearPhotos() {
            pendingFiles = [];
            renderPreviews();
        }

        function renderPreviews() {
            const preview = document.getElementById('uploadPreview');
            const grid = document.getElementById('previewGrid');
            const countSpan = document.getElementById('previewCount');
            const uploadBtn = document.getElementById('uploadBtn');
            const uploadBtnText = document.getElementById('uploadBtnText');

            grid.innerHTML = '';
            const count = pendingFiles.length;

            if (count === 0) {
                preview.classList.remove('show');
                uploadBtn.disabled = true;
                return;
            }

            preview.classList.add('show');
            uploadBtn.disabled = false;
            countSpan.textContent = count;

            pendingFiles.forEach((file, idx) => {
                const url = URL.createObjectURL(file);
                const thumb = document.createElement('div');
                thumb.className = 'preview-thumb';
                thumb.innerHTML = `
                    <img src="${url}" alt="Photo ${idx + 1}">
                    <button class="remove-btn" onclick="removePhoto(${idx})" title="Remove"><i class="bi bi-x"></i></button>
                `;
                grid.appendChild(thumb);
            });

            uploadBtnText.innerHTML = `<i class="bi bi-cloud-arrow-up-fill"></i> Upload ${count} Photo${count > 1 ? 's' : ''}`;
        }

        // ─── Upload ───
        async function startUpload() {
            if (!currentProduct || !pendingFiles.length) return;

            const uploadBtn = document.getElementById('uploadBtn');
            const progressBox = document.getElementById('uploadProgress');
            const progressFill = document.getElementById('progressFill');
            const progressTitle = document.getElementById('progressTitle');
            const progressPercent = document.getElementById('progressPercent');

            uploadBtn.classList.add('loading');
            uploadBtn.disabled = true;
            progressBox.classList.add('show');

            const totalFiles = pendingFiles.length;
            const totalBatches = Math.ceil(totalFiles / BATCH_SIZE);
            let uploaded = 0;

            let lastData = null;

            try {
                for (let b = 0; b < totalBatches; b++) {
                    const start = b * BATCH_SIZE;
                    const end = Math.min(start + BATCH_SIZE, totalFiles);
                    const chunk = pendingFiles.slice(start, end);

                    const pct = Math.round((b / totalBatches) * 100);
                    progressFill.style.width = pct + '%';
                    progressPercent.textContent = pct + '%';
                    progressTitle.textContent = `Batch ${b + 1}/${totalBatches} (${end} of ${totalFiles})`;

                    const formData = new FormData();
                    formData.append('_token', csrfToken);
                    chunk.forEach(f => formData.append('images[]', f, f.name));

                    const resp = await fetch(currentProduct.upload_url, {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json',
                        },
                    });

                    const data = await resp.json().catch(() => ({ success: resp.ok }));

                    if (!resp.ok || !data.success) {
                        throw new Error(data.message || `Batch ${b + 1} failed`);
                    }

                    lastData = data;
                    uploaded += chunk.length;
                }

                // Success!
                progressFill.style.width = '100%';
                progressPercent.textContent = '100%';
                progressTitle.textContent = 'Upload complete!';

                sessionUploaded += uploaded;
                document.getElementById('sessionCount').textContent = sessionUploaded;

                showToast(`✅ ${uploaded} photo${uploaded > 1 ? 's' : ''} uploaded!`, 'success');

                // Update existing photos display
                if (lastData && lastData.existing_images) {
                    currentProduct.existing_images = lastData.existing_images;
                    renderExistingImages(lastData.existing_images);
                    document.getElementById('prodImageCount').textContent = lastData.existing_images.length;
                } else {
                    const imgCountEl = document.getElementById('prodImageCount');
                    imgCountEl.textContent = parseInt(imgCountEl.textContent || '0') + uploaded;
                }

                document.getElementById('imageStatusDot').className = 'dot has-image';
                document.getElementById('imageStatusText').textContent =
                    document.getElementById('prodImageCount').textContent + ' image(s) in gallery';

                // Show "Scan Next" pill
                setTimeout(() => {
                    document.getElementById('scanNextPill').classList.add('show');
                }, 800);

                // Reset upload UI
                clearPhotos();
                uploadBtn.classList.remove('loading');
                progressBox.classList.remove('show');

            } catch (err) {
                uploadBtn.classList.remove('loading');
                uploadBtn.disabled = false;
                showToast('Upload failed: ' + err.message, 'error');

                if (uploaded > 0) {
                    pendingFiles = pendingFiles.slice(uploaded);
                    renderPreviews();
                }
            }
        }

        // ─── Toast ───
        function showToast(msg, type = 'success') {
            const toast = document.getElementById('toast');
            toast.textContent = msg;
            toast.className = `toast ${type}`;
            toast.style.display = 'block';
            setTimeout(() => { toast.style.display = 'none'; }, 3000);
        }

        // ─── Init on page load ───
        document.addEventListener('DOMContentLoaded', () => {
            initScanner();
        });
    </script>
    <!-- Lightbox Modal for Viewing Photos Fullscreen -->
    <div class="lightbox-modal" id="lightboxModal" onclick="closeLightbox(event)">
        <div class="lightbox-container" onclick="event.stopPropagation()">
            <button class="lightbox-close-btn" onclick="closeLightboxDirect()"><i class="bi bi-x-lg"></i></button>
            <img id="lightboxImage" src="" alt="Product Photo Preview">
            <div class="lightbox-caption" id="lightboxCaption"></div>
        </div>
    </div>
</body>

</html>
