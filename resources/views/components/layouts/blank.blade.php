<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-100">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <title>{{ $title ?? 'Showroom Monitor Display | Jaffna Gold' }}</title>
    <link rel="icon" type="image/webp" href="{{ asset('images/logo.webp') }}">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@400;600;700;800;900&family=Playfair+Display:ital,wght@0,400;0,600;0,700;1,400&family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Bootstrap 5 & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">


    <!-- Swiper 11 3D Carousel Slider -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>

    @livewireStyles
    <style>
        :root {
            --jg-gold: #c59b27;
            --jg-gold-dark: #8e6a10;
            --jg-gold-light: #fffbeb;
            --jg-bg-main: #f8f6f0;
            --jg-bg-card: #ffffff;
            --jg-text-dark: #18181b;
            --jg-border-gold: rgba(197, 155, 39, 0.25);
        }

        html, body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #f7f5ef;
            color: #18181b;
            margin: 0;
            padding: 0;
            overflow: hidden;
            height: 100%;
            height: 100vh;
            height: 100dvh;
            width: 100%;
            width: 100vw;
            width: 100dvw;
            -webkit-font-smoothing: antialiased;
        }

        .font-cinzel {
            font-family: 'Cinzel', serif;
        }

        .font-playfair {
            font-family: 'Playfair Display', serif;
        }

        /* Custom scrollbar */
        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }
        ::-webkit-scrollbar-track {
            background: rgba(0, 0, 0, 0.5);
        }
        ::-webkit-scrollbar-thumb {
            background: rgba(230, 202, 101, 0.3);
            border-radius: 4px;
        }
    </style>
</head>
<body class="h-100">
    {{ $slot }}

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    @livewireScripts
</body>
</html>
