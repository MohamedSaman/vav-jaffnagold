<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Primary Meta Tags -->
    <title>{{ $seoTitle ?? ($title ? (str_contains($title, 'Jaffna') ? $title : $title . ' | Jaffna Gold') : 'Jaffna Gold | Handcrafted 22K & 925 Sterling Silver Jewelry') }}</title>
    <meta name="title" content="{{ $seoTitle ?? ($title ? (str_contains($title, 'Jaffna') ? $title : $title . ' | Jaffna Gold') : 'Jaffna Gold | Handcrafted 22K & 925 Sterling Silver Jewelry') }}">
    <meta name="description" content="{{ $seoDesc ?? ($metaDescription ?? 'Discover Jaffna Gold - Premium handcrafted 22K gold and 925 sterling silver jewelry in Sri Lanka. Explore rings, necklaces, earrings, bracelets, and custom designs.') }}">
    <meta name="keywords" content="{{ $seoKeywords ?? ($metaKeywords ?? 'Jaffna Gold, gold jewelry Sri Lanka, 22K gold, 925 sterling silver, handcrafted jewelry, gold rings, gold necklaces, Jaffna goldsmiths, luxury jewelry') }}">
    <meta name="robots" content="{{ $seoRobots ?? ($metaRobots ?? 'index, follow') }}">
    <link rel="canonical" href="{{ $seoCanonical ?? ($canonicalUrl ?? url()->current()) }}">

    <!-- Favicon / Site Icon -->
    <link rel="icon" type="image/png" href="{{ asset('images/logo.webp') }}">
    <link rel="shortcut icon" type="image/png" href="{{ asset('images/logo.webp') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/logo.webp') }}">

    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="{{ $ogType ?? 'website' }}">
    <meta property="og:url" content="{{ $seoCanonical ?? ($canonicalUrl ?? url()->current()) }}">
    <meta property="og:title" content="{{ $seoTitle ?? ($title ? $title . ' | Jaffna Gold' : 'Jaffna Gold | Handcrafted 22K & 925 Sterling Silver Jewelry') }}">
    <meta property="og:description" content="{{ $seoDesc ?? ($metaDescription ?? 'Discover Jaffna Gold - Premium handcrafted 22K gold and 925 sterling silver jewelry in Sri Lanka.') }}">
    <meta property="og:image" content="{{ $ogImage ?? ($metaImage ?? asset('images/jgg.webp')) }}">
    <meta property="og:site_name" content="Jaffna Gold">
    <meta property="og:locale" content="en_US">

    <!-- Twitter Cards -->
    <meta property="twitter:card" content="summary_large_image">
    <meta property="twitter:url" content="{{ $seoCanonical ?? ($canonicalUrl ?? url()->current()) }}">
    <meta property="twitter:title" content="{{ $seoTitle ?? ($title ? $title . ' | Jaffna Gold' : 'Jaffna Gold | Handcrafted 22K & 925 Sterling Silver Jewelry') }}">
    <meta property="twitter:description" content="{{ $seoDesc ?? ($metaDescription ?? 'Discover Jaffna Gold - Premium handcrafted 22K gold and 925 sterling silver jewelry in Sri Lanka.') }}">
    <meta property="twitter:image" content="{{ $ogImage ?? ($metaImage ?? asset('images/jgg.webp')) }}">

    <!-- Global JSON-LD Schema.org Data (JewelryStore & WebSite) -->
    <script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@type": "JewelryStore",
      "name": "Jaffna Gold",
      "url": "{{ url('/') }}",
      "logo": "{{ asset('images/jgg.webp') }}",
      "image": "{{ asset('images/jgg.webp') }}",
      "description": "Premium handcrafted 22K gold and 925 sterling silver jewelry by master artisans in Jaffna, Sri Lanka.",
      "telephone": "+94-77-123-4567",
      "priceRange": "LKR",
      "address": {
        "@type": "PostalAddress",
        "addressLocality": "Jaffna",
        "addressCountry": "LK"
      }
    }
    </script>
    <script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@type": "WebSite",
      "name": "Jaffna Gold",
      "url": "{{ url('/') }}",
      "potentialAction": {
        "@type": "SearchAction",
        "target": "{{ url('/shop') }}?search={search_term_string}",
        "query-input": "required name=search_term_string"
      }
    }
    </script>

    @if(!empty($schemas) && is_array($schemas))
        @foreach($schemas as $schema)
    <script type="application/ld+json">
    {!! is_array($schema) ? json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) : $schema !!}
    </script>
        @endforeach
    @endif

    @if(!empty($schemaJson))
    <script type="application/ld+json">
    {!! $schemaJson !!}
    </script>
    @endif

    @stack('seo')

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,500;0,600;0,700;1,400;1,500&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        /* JAFFNA GOLD - ECOMMERCE STYLES */

        :root {
            --navy: #27211b;
            --cream: #f8f6f2; /* Cleaner luxury warm white */
            --cream-soft: #fdfcfb;
            --tan: #ba9d73;
            --accent: #cca43b; /* Vibrant luxury gold */
            --accent-hover: #ad8a2e;
            --border: #e3dcd1; /* Soft elegant border */
            --body: #595149;
            --white: #ffffff;
            --olive: #616e50;
            --charcoal: #2a221a;
            --header-offset: 120px;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            font-family: 'Inter', 'Helvetica', 'Arial', sans-serif;
            color: var(--body);
            background-color: var(--cream);
            overflow-x: hidden;
        }

        h1, h2, h3, h4, h5, h6 {
            font-family: 'Playfair Display', 'Georgia', 'Times New Roman', serif;
            color: var(--navy);
            letter-spacing: 0.02em;
        }

        .announcement-bar {
            background: #c9a84c;
            color: var(--white);
            text-align: center;
            padding: 0.5rem 1rem;
            font-size: 0.64rem;
            font-weight: 600;
            letter-spacing: 0.28em;
            text-transform: uppercase;
            max-height: 40px;
            opacity: 1;
            overflow: hidden;
            transition: max-height 0.35s ease, padding 0.35s ease, opacity 0.3s ease;
        }

        .announcement-bar-inner {
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }

        .announcement-marquee {
            display: flex;
            align-items: center;
            justify-content: center;
            min-width: 100%;
        }

        .announcement-marquee-track {
            display: flex;
            align-items: center;
            justify-content: center;
            min-width: 100%;
        }

        .announcement-marquee-item {
            display: inline-flex;
            align-items: center;
            gap: 0.8rem;
            white-space: nowrap;
        }

        .announcement-marquee-item[aria-hidden="true"] {
            display: none;
        }

        .announcement-bar.is-hidden {
            max-height: 0;
            padding-top: 0;
            padding-bottom: 0;
            opacity: 0;
        }

        .announcement-bar span {
            display: inline-flex;
            align-items: center;
            gap: 0.8rem;
            flex-wrap: wrap;
            justify-content: center;
        }

        .announcement-bar i {
            font-size: 0.7rem;
        }

        .site-chrome {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
        }

        .navbar-jg {
            background: rgba(247, 241, 230, 0.98);
            backdrop-filter: blur(14px);
            -webkit-backdrop-filter: blur(14px);
            border-bottom: 1px solid rgba(201, 168, 76, 0.18);
            transition: box-shadow 0.3s ease, background 0.3s ease;
        }

        .navbar-jg .nav-shell {
            max-width: 1520px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: auto minmax(0, 1fr) auto;
            align-items: center;
            column-gap: 2rem;
            min-height: 92px;
            padding: 0 2.5rem;
        }

        .navbar-jg.transparent,
        .navbar-jg.scrolled {
            background: rgba(247, 241, 230, 0.98);
            box-shadow: none;
        }

        .nav-brand-row {
            display: flex;
            align-items: center;
            justify-content: flex-start;
            padding: 0;
            background: transparent;
            z-index: 2;
            min-width: 0;
        }

        .nav-brand-spacer {
            display: none;
        }

        .nav-logo-wrap {
            display: flex;
            align-items: center;
            gap: 1rem;
            text-decoration: none;
            color: inherit;
        }

        .nav-logo-mark {
            width: 85px;
            height: 85px;
            object-fit: contain;
            flex: 0 0 auto;
        }

        .nav-logo-copy {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            text-align: left;
        }

        .nav-tagline {
            display: inline-flex;
            align-items: center;
            gap: 0.55rem;
            margin-top: 0.55rem;
            color: #aa8a40;
            font-size: 0.56rem;
            font-weight: 600;
            letter-spacing: 0.42em;
            text-transform: uppercase;
        }

        .nav-tagline i {
            color: var(--accent);
            font-size: 0.68rem;
        }

        .nav-logo {
            font-family: 'Playfair Display', serif;
            font-size: 2.2rem;
            font-weight: 600;
            letter-spacing: 0.22em;
            transition: color 0.3s ease, transform 0.3s ease;
            color: var(--charcoal);
            text-transform: uppercase;
            line-height: 1;
        }

        .nav-logo:hover {
            color: var(--accent) !important;
            transform: translateY(-1px);
        }

        .nav-logo-word {
            display: inline-block;
            vertical-align: baseline;
        }

        .nav-logo-sep {
            display: inline-block;
            width: 0.3em;
            max-width: 2ch;
        }

        .nav-menu-row {
            border-top: none;
            padding: 1.15rem 0;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 2rem;
            background: transparent;
            min-height: 92px;
            min-width: 0;
        }

        .nav-center {
            display: flex;
            align-items: center;
            gap: 2rem;
            justify-content: center;
            flex-wrap: wrap;
            min-width: 0;
            margin: 0 auto;
        }

        .nav-menu-tools {
            display: flex;
            align-items: center;
            gap: 0.9rem;
            justify-content: flex-end;
        }

        .nav-link {
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            font-size: 0.84rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.22em;
            text-decoration: none;
            position: relative;
            padding: 0 0 0.6rem;
            transition: color 0.3s ease, opacity 0.3s ease;
            color: #312821;
        }

        .nav-link::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 2px;
            background: var(--accent);
            transform: scaleX(0);
            transform-origin: left center;
            transition: transform 0.3s ease;
        }

        .nav-link::before {
            content: none;
            display: none;
        }

        .nav-link:hover::after,
        .nav-link.active::after {
            transform: scaleX(1);
        }

        .nav-link:hover {
            color: #312821 !important;
        }

        .nav-link.active {
            color: #312821;
            font-weight: 700;
        }

        .nav-menu-row .nav-link::after {
            background: #c9a84c;
        }

        .nav-right {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 0.85rem;
        }

        .header-search {
            position: relative;
            display: flex;
            align-items: center;
            flex: 0 0 auto;
            gap: 0.75rem;
        }

        .header-search-panel {
            width: 0;
            max-width: min(42vw, 380px);
            opacity: 0;
            overflow: hidden;
            visibility: hidden;
            pointer-events: none;
            transition:
                width 0.3s ease-in-out,
                opacity 0.3s ease-in-out,
                visibility 0s linear 0.3s;
            z-index: 5;
        }

        .header-search.is-open .header-search-panel {
            width: clamp(280px, 22vw, 380px);
            visibility: visible;
            pointer-events: auto;
            opacity: 1;
            transition-delay: 0s;
        }

        .header-search-form {
            position: relative;
            display: flex;
            align-items: center;
            width: 100%;
            height: 2.35rem;
            background: var(--white);
            border: 1px solid rgba(201, 168, 76, 0.45);
            border-radius: 999px;
            overflow: hidden;
            box-shadow: none;
        }

        .header-search-input {
            width: 100%;
            min-width: 0;
            border: none;
            background: transparent;
            color: var(--charcoal);
            font-size: 0.9rem;
            padding: 0.55rem 2.85rem 0.55rem 1rem;
            outline: none;
        }

        .header-search-input::placeholder {
            color: rgba(49, 40, 33, 0.52);
        }

        .header-search-input:focus {
            box-shadow: inset 0 0 0 1px rgba(184, 138, 43, 0.55);
        }

        .header-search-submit {
            position: absolute;
            top: 50%;
            right: 0.1rem;
            transform: translateY(-50%);
            border: none;
            background: transparent;
            color: #312821;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            font-size: 1rem;
            width: 2.1rem;
            height: 2.1rem;
            border-radius: 999px;
            transition: background-color 0.2s ease, color 0.2s ease;
        }

        .header-search-submit:hover,
        .header-search-submit:focus-visible {
            background: rgba(201, 168, 76, 0.1);
            color: var(--accent);
            outline: none;
        }

        .header-search.is-open .nav-icon-button {
            opacity: 0;
            pointer-events: none;
        }

        .header-search-toggle {
            flex: 0 0 auto;
        }

        .nav-menu-row .nav-right .nav-icon,
        .nav-menu-row .hamburger {
            color: #312821;
        }

        .nav-icon {
            position: relative;
            width: 2.35rem;
            height: 2.35rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: 1px solid rgba(201, 168, 76, 0.45);
            border-radius: 9999px;
            background: transparent;
            color: inherit;
            font-size: 1.05rem;
            text-decoration: none;
            transition: color 0.3s ease, border-color 0.3s ease, background 0.3s ease, transform 0.3s ease;
        }

        .nav-icon-button {
            appearance: none;
            cursor: pointer;
        }

        .nav-icon:hover {
            color: #312821 !important;
            border-color: rgba(201, 168, 76, 0.75);
            background: rgba(201, 168, 76, 0.08);
            transform: translateY(-1px);
        }

        .badge-jg {
            position: absolute;
            top: -5px;
            right: -6px;
            background: var(--accent);
            color: var(--white);
            font-size: 0.6rem;
            font-weight: 600;
            width: 17px;
            height: 17px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 0 0 3px rgba(247, 241, 230, 0.98);
        }

        .hamburger {
            display: none;
            background: none;
            border: none;
            cursor: pointer;
            color: #312821;
            padding: 0;
            width: 2.5rem;
            height: 2.5rem;
            align-items: center;
            justify-content: center;
            position: relative;
            z-index: 2200;
        }

        .hamburger-line {
            position: absolute;
            width: 1.45rem;
            height: 2px;
            border-radius: 999px;
            background: currentColor;
            transition: transform 0.28s ease, opacity 0.2s ease, top 0.28s ease;
        }

        .hamburger-line:nth-child(1) {
            top: 0.72rem;
        }

        .hamburger-line:nth-child(2) {
            top: 1.17rem;
        }

        .hamburger-line:nth-child(3) {
            top: 1.62rem;
        }

        .hamburger.is-open .hamburger-line:nth-child(1) {
            top: 1.17rem;
            transform: rotate(45deg);
        }

        .hamburger.is-open .hamburger-line:nth-child(2) {
            opacity: 0;
        }

        .hamburger.is-open .hamburger-line:nth-child(3) {
            top: 1.17rem;
            transform: rotate(-45deg);
        }

        main {
            padding-top: var(--header-offset);
        }

        /* ===== MOBILE MENU ===== */
        .mobile-menu-overlay {
            position: fixed;
            inset: 0;
            background: rgba(32, 24, 17, 0.34);
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.28s ease;
            z-index: 1980;
        }

        .mobile-menu-overlay.open {
            opacity: 1;
            pointer-events: auto;
        }

        .mobile-menu {
            position: fixed;
            top: 5.15rem;
            right: 1rem;
            width: min(19.5rem, calc(100vw - 1.4rem));
            background: rgba(247, 241, 230, 0.98);
            z-index: 2100;
            flex-direction: column;
            align-items: stretch;
            gap: 0.2rem;
            padding: 1.15rem 1rem 1rem;
            border: 1px solid rgba(201, 168, 76, 0.2);
            border-radius: 1.1rem;
            box-shadow: 0 20px 44px rgba(37, 27, 19, 0.16);
            opacity: 0;
            visibility: hidden;
            pointer-events: none;
            transform: translate3d(0, -10px, 0) scale(0.96);
            transform-origin: top right;
            transition: opacity 0.28s ease, transform 0.28s ease, visibility 0s linear 0.28s;
        }

        .mobile-menu.open {
            opacity: 1;
            visibility: visible;
            pointer-events: auto;
            transform: translate3d(0, 0, 0) scale(1);
            transition: opacity 0.28s ease, transform 0.28s ease, visibility 0s linear;
        }

        .mobile-menu::before {
            content: '';
            position: absolute;
            top: -0.45rem;
            right: 1.05rem;
            width: 0.95rem;
            height: 0.95rem;
            background: rgba(247, 241, 230, 0.98);
            border-top: 1px solid rgba(201, 168, 76, 0.2);
            border-left: 1px solid rgba(201, 168, 76, 0.2);
            transform: rotate(45deg);
            border-top-left-radius: 0.2rem;
        }

        .mobile-menu .nav-link {
            font-size: 0.82rem;
            color: #312821;
            letter-spacing: 0.16em;
            padding: 0.78rem 0.2rem;
            border-bottom: 1px solid rgba(201, 168, 76, 0.12);
            text-transform: uppercase;
        }

        .mobile-menu .nav-link::after {
            content: none;
        }

        .mobile-menu .nav-link.active {
            color: var(--accent);
            font-weight: 700;
        }

        .mobile-menu .nav-link:last-of-type {
            border-bottom: 0;
        }

        .mobile-menu-meta {
            display: grid;
            gap: 0.15rem;
            padding-top: 0.55rem;
        }

        .mobile-menu-text-link {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            text-decoration: none;
            color: #312821;
            font-size: 0.78rem;
            font-weight: 600;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            padding: 0.7rem 0.2rem;
        }

        .mobile-menu-text-link.is-active {
            color: var(--accent);
        }

        .mobile-menu-text-link .badge-jg {
            position: static;
            width: auto;
            min-width: 1.2rem;
            height: 1.2rem;
            padding: 0 0.35rem;
            font-size: 0.58rem;
            box-shadow: none;
        }

        body.mobile-menu-open {
            overflow: hidden;
            touch-action: none;
        }

        /* ===== BUTTONS ===== */
        .btn-jg {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            padding: 0.85rem 2.25rem;
            border-radius: 9999px;
            font-size: 0.8rem;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            text-decoration: none;
            border: none;
            cursor: pointer;
            transition: all 0.3s ease;
            font-family: 'Inter', sans-serif;
        }

        .btn-jg-primary {
            background: var(--accent);
            color: var(--white);
        }

        .btn-jg-primary:hover {
            background: var(--accent-hover);
            transform: scale(1.03);
            box-shadow: 0 8px 25px rgba(222, 127, 47, 0.35);
            color: var(--white);
        }

        .btn-jg-outline {
            background: transparent;
            color: var(--navy);
            border: 2px solid var(--navy);
        }

        .btn-jg-outline:hover {
            background: var(--navy);
            color: var(--white);
        }

        .btn-jg-light {
            background: transparent;
            color: var(--white);
            border: 2px solid var(--white);
        }

        .btn-jg-light:hover {
            background: var(--white);
            color: var(--navy);
        }

        .btn-jg-sm {
            padding: 0.55rem 1.25rem;
            font-size: 0.7rem;
        }

        /* ===== EYEBROW ===== */
        .eyebrow {
            font-size: 0.7rem;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.15em;
            color: var(--accent);
            margin-bottom: 0.75rem;
            display: block;
        }

        /* ===== SECTION ===== */
        .section-jg {
            padding: 5rem 2rem;
        }

        .section-jg-cream {
            background: var(--cream);
        }

        .section-jg-white {
            background: var(--white);
        }

        .container-jg {
            max-width: 1440px;
            margin: 0 auto;
        }

        /* ===== PAGE HEADER BAND ===== */
        .page-header {
            padding: 3rem 2rem 3.2rem;
            background: #2a221a; /* Dark premium background */
            text-align: center;
            border-bottom: 1px solid rgba(201, 168, 76, 0.15);
        }

        .page-header h1 {
            color: #FAF6F0 !important;
            font-family: 'Playfair Display', serif;
            font-size: 2.2rem;
            font-weight: 500;
            letter-spacing: 0.04em;
        }

        .page-header .breadcrumb-jg {
            margin-top: 0.4rem;
        }

        .page-header .breadcrumb-jg span {
            color: rgba(250, 246, 240, 0.72) !important;
            font-size: 0.76rem;
            letter-spacing: 0.15em;
            text-transform: uppercase;
        }

        .page-header.page-header-hero {
            position: relative;
            overflow: hidden;
            background:
                linear-gradient(rgba(48, 39, 32, 0.5), rgba(48, 39, 32, 0.5)),
                url('{{ asset('images/website images/hero/hero_6.webp') }}') center/cover no-repeat;
            color: var(--white);
        }

        .page-header.page-header-hero::after {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(180deg, rgba(0, 0, 0, 0.04), rgba(0, 0, 0, 0.18));
            pointer-events: none;
        }

        .page-header.page-header-hero > * {
            position: relative;
            z-index: 1;
            color: #ffffff;
        }

        .page-header h1 {
            font-size: 3.5rem;
            margin-bottom: 0.5rem;
        }

        .breadcrumb-jg {
            font-size: 0.8rem;
            color: var(--tan);
        }

        .breadcrumb-jg a {
            color: var(--tan);
            text-decoration: none;
        }

        .breadcrumb-jg a:hover {
            color: var(--accent);
        }

        .breadcrumb-jg span {
            color: var(--body);
        }

        .page-header.page-header-hero .breadcrumb-jg,
        .page-header.page-header-hero .breadcrumb-jg span,
        .page-header.page-header-hero .breadcrumb-jg a {
            color: rgba(255,255,255,0.82);
        }

        /* ===== PRODUCT CARD ===== */
        .product-card {
            background: var(--white);
            border-radius: 12px;
            overflow: hidden;
            border: 1px solid rgba(201, 168, 76, 0.22);
            box-shadow: 0 2px 15px rgba(0,0,0,0.06);
            transition: all 0.4s ease;
            cursor: pointer;
            position: relative;
            display: flex;
            flex-direction: column;
            height: 100%;
        }

        .product-card:hover {
            transform: translateY(-6px) scale(1.01);
            border-color: rgba(201, 168, 76, 0.95);
            box-shadow: 0 12px 35px rgba(0,0,0,0.1);
        }

        .product-card .img-wrap {
            position: relative;
            aspect-ratio: 1;
            overflow: hidden;
            background: var(--cream);
        }

        .product-card .img-wrap img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.6s ease;
        }

        .product-card:hover .img-wrap img {
            transform: scale(1.05);
        }

        .product-card .tag {
            position: absolute;
            top: 12px;
            left: 12px;
            background: var(--accent);
            color: var(--white);
            font-size: 0.6rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            padding: 4px 10px;
            border-radius: 4px;
        }

        .product-card .card-tools {
            display: none;
        }

        .product-card .card-tool-btn {
            background: #ffffff;
            border: none;
            width: 44px;
            height: 44px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            font-size: 1rem;
            color: var(--body);
            box-shadow: 0 6px 18px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }

        .product-card .card-tool-btn:hover {
            transform: translateY(-1px);
        }

        .product-card .wishlist-btn.active {
            color: #ff0d00;
            background: #ffffff;
        }

        .product-card .wishlist-btn:hover {
            color: #ff0d00;
        }

        .product-card .cart-btn.active {
            background: #fdfacd;
            color: var(--body);
        }

        .product-card .info {
            padding: 1.25rem;
            display: flex;
            flex-direction: column;
            flex: 1 1 auto;
        }

        .product-card .info h3 {
            display: block;
            width: 100%;
            font-family: 'Playfair Display', serif;
            font-size: 1.12rem;
            font-weight: 600;
            line-height: 1.4;
            padding: 0;
            margin-bottom: 0.4rem;
            color: var(--navy);
            transition: color 0.3s ease;
            overflow: hidden;
            white-space: nowrap;
            text-overflow: ellipsis;
        }

        .product-card:hover .info h3 {
            color: var(--accent);
        }

        .product-card .info .details {
            font-size: 0.84rem;
            color: var(--body);
            line-height: 1.75;
            margin-bottom: 1.2rem;
            overflow: hidden;
            display: -webkit-box;
            -webkit-box-orient: vertical;
            -webkit-line-clamp: 2;
            line-clamp: 2;
            min-height: calc(1.7em * 2);
        }

        .product-card .info .price {
            display: flex;
            align-items: baseline;
            justify-content: flex-start;
            gap: 1.1rem;
            font-size: 1rem;
            font-weight: 600;
            color: var(--navy);
            margin-bottom: 1.25rem;
            margin-top: auto;
            flex-wrap: nowrap;
            white-space: nowrap;
        }

        .product-card .info .price .original {
            font-size: 0.88rem;
            color: var(--body);
            text-decoration: line-through;
            font-weight: 500;
            white-space: nowrap;
        }

        .product-card .info .actions {
            display: grid;
            grid-template-columns: 44px 44px minmax(0, 1fr);
            align-items: center;
            gap: 0.5rem;
            margin-top: 0;
            min-width: 0;
        }

        .product-card .info .actions .btn-jg {
            justify-content: center;
            text-align: center;
            border-radius: 999px;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            min-height: 44px;
            padding: 0.75rem 0.7rem;
            font-size: 0.73rem;
            white-space: nowrap;
        }

        .product-card .info .actions .btn-jg-primary {
            width: 100%;
            min-width: 0;
        }

        .product-card .info .actions .action-icon-btn {
            width: 44px;
            height: 44px;
            min-width: 44px;
            min-width: 44px;
            padding: 0;
            border-radius: 50%;
            border: 1px solid rgba(201, 168, 76, 0.22);
            background: var(--cream);
            color: var(--body);
            box-shadow: none;
            transition: all 0.3s ease;
        }

        .product-card .info .actions .action-icon-btn i {
            font-size: 1rem;
        }

        .product-card .info .actions .action-icon-btn:hover {
            background: rgba(201, 168, 76, 0.1);
            border-color: rgba(201, 168, 76, 0.45);
            color: var(--navy);
        }

        .product-card .info .actions .action-icon-btn.wishlist-btn.active {
            color: #ff0d00;
            background: var(--cream);
        }

        .product-card .info .actions .action-icon-btn.cart-btn.active {
            color: var(--accent);
            background: rgba(201, 168, 76, 0.12);
            border-color: var(--accent);
        }

        .product-card .info .actions .btn-jg-outline {
            background: #ffffff;
            border: 2px solid #4a3d36;
            color: #312821;
        }

        /* ===== FOOTER ===== */
        .footer-jg {
            background: var(--navy);
            color: rgba(255,255,255,0.7);
            padding: 4rem 2rem 0;
        }

        .footer-jg .container-jg {
            display: grid;
            grid-template-columns: 1.5fr 1fr 1fr 1fr;
            gap: 3rem;
        }

        .footer-brand-column {
            display: flex;
            flex-direction: column;
        }

        .footer-jg h4 {
            color: var(--white);
            font-size: 1rem;
            margin-bottom: 1.25rem;
        }

        .footer-jg .brand {
            font-family: 'Playfair Display', serif;
            font-size: 1.5rem;
            font-weight: 600;
            letter-spacing: 0.12em;
            color: var(--white);
            margin-bottom: 0.75rem;
        }

        .footer-jg .mission {
            font-size: 0.85rem;
            line-height: 1.6;
            margin-bottom: 1.5rem;
        }

        .footer-social-row {
            display: flex;
            align-items: center;
            gap: 0.7rem;
            flex-wrap: wrap;
            margin-top: 0.35rem;
        }

        .footer-social-link {
            width: 42px;
            height: 42px;
            border-radius: 5px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: var(--white) !important;
            border: 1px solid rgba(255,255,255,0.14);
            box-shadow: 0 8px 16px rgba(0,0,0,0.12);
            transition: transform 0.25s ease, filter 0.25s ease, box-shadow 0.25s ease;
        }

        .footer-social-link:hover,
        .footer-social-link:focus-visible {
            transform: translateY(-1px);
            filter: brightness(1.06);
            box-shadow: 0 10px 20px rgba(0,0,0,0.16);
            outline: none;
        }

        .footer-social-link i {
            font-size: 1.15rem;
        }

         .footer-social-link.is-whatsapp,
        .footer-social-link.is-facebook,
        .footer-social-link.is-instagram,
        .footer-social-link.is-youtube,
        .footer-social-link.is-tiktok {
            background: #2F251D;
        }

        .footer-jg a {
            color: rgba(255,255,255,0.6);
            text-decoration: none;
            display: block;
            font-size: 0.85rem;
            padding: 8px 12px;
            transition: color 0.3s ease;
        }

        .footer-jg a:hover {
            color: var(--accent);
        }

        .footer-bottom {
            border-top: 1px solid rgba(255,255,255,0.1);
            margin-top: 3rem;
            padding: 1.5rem 0;
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            align-items: center;
            font-size: 0.8rem;
            gap: 1rem;
            color: var(--white);
        }

        .footer-bottom a {
            display: inline-flex;
            padding: 0;
        }

        .footer-bottom > span,
        .footer-bottom > a {
            justify-self: center;
            text-align: center;
            white-space: nowrap;
        }

        .footer-bottom > span,
        .footer-bottom > a,
        .footer-link-main,
        .footer-link-accent {
            color: var(--white);
        }

        .footer-bottom-link {
            display: inline-flex;
            align-items: center;
            text-decoration: none;
        }

        .footer-bottom-link:hover,
        .footer-bottom-link:focus-visible {
            color: var(--white);
        }

        .footer-webxkey-link .footer-link-accent {
            transition: color 0.25s ease;
        }

        .footer-webxkey-link:hover .footer-link-accent,
        .footer-webxkey-link:focus-visible .footer-link-accent {
            color: #5683e9;
        }

        .footer-webxkey-link:focus-visible {
            outline: 1px solid rgba(255, 255, 255, 0.7);
            outline-offset: 4px;
        }

        .footer-mobile-shell {
            display: none;
        }

        @media (prefers-reduced-motion: reduce) {
            .footer-webxkey-link .footer-link-accent {
                transition: none;
            }
        }

        /* ===== TOAST ===== */
        .toast-jg {
            position: fixed;
            bottom: 2rem;
            right: 2rem;
            background: var(--navy);
            color: var(--white);
            padding: 0.85rem 1.5rem;
            border-radius: 8px;
            font-size: 0.85rem;
            box-shadow: 0 8px 30px rgba(0,0,0,0.15);
            z-index: 9999;
            transform: translateY(100px);
            opacity: 0;
            transition: all 0.4s ease;
        }

        .toast-jg.show {
            transform: translateY(0);
            opacity: 1;
        }

        /* ===== SCROLL REVEAL ===== */
        .reveal {
            opacity: 0;
            transform: translateY(30px);
            transition: all 0.6s ease;
        }

        .reveal.visible {
            opacity: 1;
            transform: translateY(0);
        }

        .reveal-delay-1 { transition-delay: 0.1s; }
        .reveal-delay-2 { transition-delay: 0.2s; }
        .reveal-delay-3 { transition-delay: 0.3s; }
        .reveal-delay-4 { transition-delay: 0.4s; }
        .reveal-delay-5 { transition-delay: 0.5s; }
        .reveal-delay-6 { transition-delay: 0.6s; }

        /* Product cards render inside Livewire-driven grids; keep them fully
           visible so the scroll-reveal never replays/flashes when Livewire
           updates a card (e.g. toggling the cart/wishlist icon). */
        .product-card.reveal {
            opacity: 1;
            transform: none;
        }

        /* ===== RESPONSIVE ===== */
        @media (max-width: 1024px) {
            :root {
                --header-offset: 168px;
            }

            .nav-center { display: none; }
            .hamburger { display: inline-flex; }
            .navbar-jg .nav-shell {
                min-height: auto;
                padding: 0;
                display: flex;
                flex-direction: column;
            }
            .nav-brand-row {
                position: static;
                transform: none;
                padding: 1rem 1.5rem;
                justify-content: center;
            }
            .nav-brand-spacer {
                display: none;
            }
            .nav-logo-wrap {
                align-items: center;
                gap: 0.7rem;
            }
            .nav-logo-mark {
                width: 56px;
                height: 100px;
            }
            .nav-logo-copy {
                align-items: center;
                text-align: center;
            }
            .nav-logo {
                font-size: 1.7rem;
            }
            .nav-tagline {
                letter-spacing: 0.24em;
            }
            .nav-menu-row {
                min-height: auto;
                padding: 0.95rem 1.5rem 0.85rem;
                justify-content: space-between;
            }
            .nav-menu-tools {
                position: static;
                transform: none;
                width: 100%;
                justify-content: space-between;
            }

            .header-search-panel {
                position: absolute;
                top: calc(100% + 0.65rem);
                right: 0;
                max-width: min(calc(100vw - 2rem), 360px);
            }

            .header-search.is-open .header-search-panel {
                width: min(calc(100vw - 2rem), 360px);
            }

            .nav-right {
                gap: 0.65rem;
            }
            .footer-jg .container-jg {
                grid-template-columns: 1fr 1fr;
            }
        }

        @media (max-width: 768px) {
            :root {
                --header-offset: 118px;
            }

            .announcement-bar {
                font-size: 0.56rem;
                letter-spacing: 0.2em;
                padding-left: 0;
                padding-right: 0;
            }
            .announcement-bar-inner {
                justify-content: flex-start;
            }
            .announcement-marquee {
                overflow: hidden;
            }
            .announcement-marquee-track {
                width: max-content;
                min-width: max-content;
                justify-content: flex-start;
                gap: 2rem;
                animation: mobileAnnouncementMarquee 12s linear infinite;
            }
            .announcement-marquee-item {
                padding-right: 2rem;
            }
            .announcement-marquee-item[aria-hidden="true"] {
                display: inline-flex;
            }
            .nav-brand-row {
                grid-column: 1;
                padding: 0.85rem 0 0.85rem 1rem;
                justify-content: flex-start;
                min-width: 0;
            }
            .navbar-jg .nav-shell {
                display: grid;
                grid-template-columns: minmax(0, 1fr) auto;
                align-items: center;
                min-height: 76px;
                padding: 0;
            }
            .nav-menu-row {
                grid-column: 2;
                min-height: 76px;
                padding: 0.85rem 1rem 0.85rem 0.5rem;
                justify-content: flex-end;
                min-width: 0;
            }
            .nav-right {
                gap: 0.5rem;
                flex-wrap: nowrap;
            }
            .nav-icon {
                width: 2.35rem;
                height: 2.35rem;
                font-size: 0.95rem;
            }
            .nav-menu-tools {
                align-items: center;
                width: auto;
                justify-content: flex-end;
                gap: 0.5rem;
                min-width: 0;
            }
            .header-search {
                position: static;
                flex: 0 0 auto;
            }
            .header-search-panel {
                position: absolute;
                top: calc(100% + 0.5rem);
                left: 0;
                right: auto;
                max-width: min(calc(100vw - 2rem), 320px);
            }
            .header-search.is-open .header-search-panel {
                width: min(calc(100vw - 2rem), 320px);
            }
            .nav-logo-wrap {
                gap: 0.65rem;
            }
            .nav-logo-mark {
                width: 48px;
                height: 48px;
            }
            .nav-logo-copy {
                align-items: flex-start;
                text-align: left;
            }
            .nav-logo {
                font-size: 1.15rem;
                letter-spacing: 0.18em;
                display: flex;
                flex-direction: column;
                align-items: flex-start;
                justify-content: center;
                line-height: 1.02;
                row-gap: 0.08em;
                white-space: nowrap;
            }

            .nav-logo-sep {
                display: none;
            }

            .nav-tagline {
                display: none;
                margin-top: 0.28rem;
                font-size: 0.43rem;
                letter-spacing: 0.22em;
                gap: 0.28rem;
                white-space: nowrap;
            }
            .nav-account-link {
                display: none;
            }
            .nav-wishlist-link {
                display: none;
            }
            .mobile-menu {
                top: calc(100% + 0.25rem);
                right: 0.75rem;
                width: min(18rem, calc(100vw - 1.5rem));
                max-height: calc(100vh - 7rem);
                overflow-y: auto;
                overscroll-behavior: contain;
                padding: 0.9rem 0.95rem 1rem;
            }
            .mobile-menu .nav-link,
            .mobile-menu-text-link {
                min-height: 44px;
                align-items: center;
                padding-top: 0.8rem;
                padding-bottom: 0.8rem;
            }
            .mobile-menu .nav-link {
                display: flex;
            }
            .mobile-menu-meta {
                gap: 0;
            }
            .mobile-menu-overlay {
                backdrop-filter: blur(2px);
                -webkit-backdrop-filter: blur(2px);
            }
            .section-jg { padding: 3rem 1rem; }
            .page-header { padding: 6rem 1rem 2rem; }
            .page-header h1 { font-size: 1.8rem; }
            .footer-jg {
                background: #251F18;
                padding: 0;
            }
            .footer-jg > .container-jg,
            .footer-jg > .footer-bottom {
                display: none;
            }
            .footer-mobile-shell {
                display: block;
                width: 100%;
                padding: 24px 20px 20px;
            }
            .footer-mobile-brand-row {
                display: flex;
                align-items: center;
                gap: 10px;
                margin-bottom: 20px;
            }
            .footer-mobile-brand-mark {
                width: 28px;
                height: 28px;
                border-radius: 999px;
                object-fit: cover;
                background: #26201900;
                flex: 0 0 28px;
            }
            .footer-mobile-brand-name {
                color: #EBE4D4;
                font-size: 16px;
                font-weight: 600;
                line-height: 1.2;
            }
            .footer-mobile-accordion {
                border-top: 1px solid #4A4030;
            }
            .footer-mobile-section {
                border-bottom: 1px solid #4A4030;
            }
            .footer-mobile-trigger {
                width: 100%;
                min-height: 52px;
                padding: 14px 0;
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 1rem;
                border: 0;
                background: transparent;
                color: #EBE4D4;
                text-align: left;
                font-size: 14px;
                font-weight: 600;
            }
            .footer-mobile-trigger i {
                font-size: 18px;
                transition: transform 0.2s ease;
                flex: 0 0 auto;
            }
            .footer-mobile-trigger[aria-expanded="true"] i {
                transform: rotate(180deg);
            }
            .footer-mobile-trigger:focus-visible {
                outline: 1px solid rgba(235, 228, 212, 0.75);
                outline-offset: 4px;
                border-radius: 4px;
            }
            .footer-mobile-panel {
                display: grid;
                grid-template-rows: 0fr;
                transition: grid-template-rows 0.24s ease;
            }
            .footer-mobile-panel.is-open {
                grid-template-rows: 1fr;
            }
            .footer-mobile-panel-inner {
                overflow: hidden;
            }
            .footer-mobile-links {
                list-style: none;
                padding: 0 0 10px;
                margin: 0;
                display: flex;
                flex-direction: column;
                gap: 8px;
            }
            .footer-mobile-links li {
                margin: 0;
            }
            .footer-mobile-links a {
                padding: 0;
                min-height: 0;
                display: inline-flex;
                align-items: center;
                justify-content: flex-start;
                color: #B7AC98;
                font-size: 13px;
                line-height: 1.45;
                text-decoration: none;
            }
            .footer-mobile-links a:hover,
            .footer-mobile-links a:focus-visible {
                color: #EBE4D4;
            }
            .footer-mobile-social-wrap {
                border-top: 0px solid #4A4030;
                margin-top: 8px;
                padding: 16px 0 10px;
            }
            .footer-mobile-social-row {
                display: flex;
                justify-content: center;
                align-items: center;
                gap: 18px;
                flex-wrap: wrap;
            }
            .footer-mobile-social-row .footer-social-link {
                width: auto;
                height: auto;
                border: 0;
                border-radius: 0;
                padding: 0;
                background: transparent !important;
                box-shadow: none;
                color: #B7AC98 !important;
            }
            .footer-mobile-social-row .footer-social-link i {
                font-size: 18px;
            }
            .footer-mobile-social-row .footer-social-link:hover,
            .footer-mobile-social-row .footer-social-link:focus-visible {
                transform: none;
                filter: none;
                box-shadow: none;
                color: #EBE4D4 !important;
            }
            .footer-mobile-meta {
                display: flex;
                flex-direction: column;
                align-items: center;
                gap: 6px;
                text-align: center;
                padding-top: 2px;
            }
            .footer-mobile-meta-line {
                color: #7A7060;
                font-size: 11px;
                line-height: 1.35;
            }
            .footer-mobile-meta-credit {
                color: #5C5648;
                font-size: 11px;
                line-height: 1.35;
                text-decoration: none;
            }
            .footer-mobile-meta-credit .footer-link-main,
            .footer-mobile-meta-credit .footer-link-accent {
                color: inherit;
            }
            .footer-mobile-meta-credit:hover,
            .footer-mobile-meta-credit:focus-visible {
                color: #7A7060;
            }
            .toast-jg {
                left: 1rem;
                right: 1rem;
                bottom: 1rem;
                text-align: center;
            }
        }

        @media (max-width: 480px) {
            :root {
                --header-offset: 112px;
            }

            .nav-logo { font-size: 1.05rem; }
            .nav-tagline {
                font-size: 0.4rem;
                gap: 0.25rem;
            }
            .nav-right { gap: 0.42rem; }
            .header-search.is-open .header-search-panel {
                width: min(calc(100vw - 2rem), 290px);
            }
            .nav-icon {
                width: 2.15rem;
                height: 2.15rem;
                font-size: 0.88rem;
            }
            .hamburger {
                width: 2.2rem;
                height: 2.2rem;
            }
            .mobile-menu {
                right: 0.7rem;
                top: 4.6rem;
                width: min(18rem, calc(100vw - 1rem));
                max-height: calc(100vh - 5.6rem);
                padding-inline: 0.85rem;
            }
            .nav-brand-row {
                padding-left: 0.75rem;
            }
            .nav-menu-row {
                padding-right: 0.75rem;
                padding-left: 0.35rem;
            }
            .nav-logo-mark {
                width: 44px;
                height: 44px;
            }
            .nav-logo {
                font-size: 0.96rem;
                letter-spacing: 0.14em;
            }
            .nav-tagline {
                letter-spacing: 0.18em;
            }
            .footer-jg .container-jg {
                gap: 1.5rem;
            }
            .footer-jg .mission {
                font-size: 0.8rem;
            }
            .footer-bottom {
                font-size: 0.76rem;
            }
        }

        @keyframes mobileAnnouncementMarquee {
            from {
                transform: translateX(0);
            }

            to {
                transform: translateX(calc(-50% - 1rem));
            }
        }

        @media (prefers-reduced-motion: reduce) {
            .header-search-panel,
            .header-search-submit,
            .nav-icon,
            .mobile-menu,
            .mobile-menu-overlay,
            .hamburger-line,
            .announcement-marquee-track {
                transition: none !important;
            }

            .announcement-marquee-track {
                animation: none !important;
            }
        }

        /* ===== FORM STYLES ===== */
        .form-jg {
            display: flex;
            flex-direction: column;
            gap: 1.25rem;
        }

        .form-jg .form-group {
            display: flex;
            flex-direction: column;
            gap: 0.35rem;
        }

        .form-jg label {
            font-size: 0.8rem;
            font-weight: 500;
            color: var(--navy);
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .form-jg input,
        .form-jg textarea,
        .form-jg select {
            padding: 0.75rem 1rem;
            border: 1.5px solid var(--border);
            border-radius: 8px;
            font-size: 0.9rem;
            font-family: 'Inter', sans-serif;
            background: var(--white);
            transition: border-color 0.3s ease;
            outline: none;
        }

        .form-jg input:focus,
        .form-jg textarea:focus,
        .form-jg select:focus {
            border-color: var(--accent);
        }

        /* ===== CARD ===== */
        .card-jg {
            background: var(--white);
            border-radius: 12px;
            padding: 2rem;
            box-shadow: 0 2px 15px rgba(0,0,0,0.06);
        }

        /* ===== QUANTITY STEPPER ===== */
        .qty-stepper {
            display: inline-flex;
            align-items: center;
            border: 1.5px solid var(--border);
            border-radius: 8px;
            overflow: hidden;
        }

        .qty-stepper button {
            background: none;
            border: none;
            padding: 0.5rem 0.85rem;
            font-size: 1rem;
            cursor: pointer;
            color: var(--navy);
            transition: background 0.2s ease;
        }

        .qty-stepper button:hover {
            background: var(--cream);
        }

        .qty-stepper span {
            padding: 0.5rem 1rem;
            font-size: 0.9rem;
            font-weight: 500;
            min-width: 40px;
            text-align: center;
            border-left: 1.5px solid var(--border);
            border-right: 1.5px solid var(--border);
        }

        /* ===== SIZE SELECTOR ===== */
        .size-selector {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
        }

        .size-btn {
            padding: 0.5rem 1.25rem;
            border: 1.5px solid var(--border);
            border-radius: 8px;
            background: var(--white);
            font-size: 0.85rem;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .size-btn.active {
            border-color: var(--navy);
            background: var(--navy);
            color: var(--white);
        }

        .size-btn:hover:not(.active) {
            border-color: var(--navy);
        }

        /* ===== TABS ===== */
        .tabs-jg {
            display: flex;
            gap: 2rem;
            border-bottom: 1.5px solid var(--border);
            padding-bottom: 0.75rem;
            margin-bottom: 1.5rem;
        }

        .tab-jg {
            background: none;
            border: none;
            font-size: 0.85rem;
            font-weight: 500;
            color: var(--body);
            cursor: pointer;
            padding-bottom: 0.75rem;
            margin-bottom: -0.75rem;
            border-bottom: 2px solid transparent;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .tab-jg.active {
            color: var(--navy);
            border-bottom-color: var(--accent);
        }

        .tab-jg:hover {
            color: var(--navy);
        }

        .tab-panel {
            display: none;
        }

        .tab-panel.active {
            display: block;
        }

        /* ===== STATUS PILL ===== */
        .status-pill {
            display: inline-block;
            padding: 0.3rem 0.85rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 500;
        }

        .status-pill.delivered {
            background: #d4edda;
            color: #155724;
        }

        .status-pill.in-transit {
            background: #fff3cd;
            color: #856404;
        }

        .status-pill.processing {
            background: #cce5ff;
            color: #004085;
        }

        /* ===== HERO ===== */
        .hero-jg {
            padding: 0;
        }

        .hero-frame {
            position: relative;
            min-height: min(80vh, 760px);
            width: 100vw;
            max-width: none;
            margin-left: calc(50% - 50vw);
            margin-right: calc(50% - 50vw);
            border-radius: 0;
            overflow: hidden;
            box-shadow: none;
            background: #1f1a16;
            isolation: isolate;
        }

        .hero-frame::after {
            content: none;
        }

        .hero-media {
            position: absolute;
            inset: 0;
            border-radius: 0;
            overflow: hidden;
            z-index: 0;
        }

        .hero-slide {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            object-position: center;
            opacity: 0;
            transition: opacity 1.2s ease;
            will-change: opacity;
        }

        .hero-slide.is-active {
            opacity: 1;
        }

        .hero-jg .overlay {
            position: absolute;
            inset: 0;
            border-radius: 0;
            z-index: 1;
            background: linear-gradient(90deg, rgba(0, 0, 0, 0.48) 0%, rgba(0, 0, 0, 0.35) 38%, rgba(0, 0, 0, 0.18) 68%, rgba(0, 0, 0, 0.12) 100%);
        }

        .hero-jg .content {
            position: relative;
            z-index: 2;
            text-align: left;
            color: var(--white);
            max-width: 760px;
            padding: 5.5rem 4.5rem;
            min-height: min(78vh, 760px);
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .hero-jg .content .eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 0.95rem;
            color: #d8b66e;
            margin-bottom: 1.4rem;
            letter-spacing: 0.32em;
            font-size: 0.7rem;
            font-weight: 600;
        }

        .hero-jg .content .eyebrow::before {
            content: '';
            width: 68px;
            height: 1px;
            background: currentColor;
            opacity: 0.95;
        }

        .hero-jg .content h1 .accent-script {
            display: block;
            font-style: italic;
            font-weight: 500;
            color: #f3e7cf;
        }

        .hero-jg .content h1 {
            font-size: clamp(3rem, 5vw, 5.4rem);
            color: #f9f3ea;
            margin-bottom: 1.35rem;
            line-height: 0.98;
            letter-spacing: 0.01em;
            max-width: 9ch;
        }

        .hero-jg .content p {
            font-size: 1.02rem;
            margin-bottom: 2.25rem;
            color: rgba(249, 243, 234, 0.86);
            line-height: 1.75;
            max-width: 34rem;
        }

        .hero-jg .content .ctas {
            display: flex;
            gap: 1rem;
            justify-content: flex-start;
            flex-wrap: wrap;
        }

        .scroll-cue {
            position: absolute;
            bottom: 2rem;
            right: 2.5rem;
            z-index: 2;
            color: var(--white);
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 0.15em;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0.5rem;
            opacity: 0.7;
            animation: bounce 2s infinite;
        }

        .scroll-cue i {
            font-size: 1.25rem;
        }

        @keyframes bounce {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(8px); }
        }

        @media (max-width: 768px) {
            .hero-jg {
                padding: 0;
            }

            .hero-frame {
                min-height: 72vh;
                margin-left: calc(50% - 50vw);
                margin-right: calc(50% - 50vw);
            }

            .hero-jg .content {
                padding: 4rem 1.5rem 4.5rem;
                min-height: 620px;
            }

            .hero-jg .content .eyebrow {
                gap: 0.65rem;
                letter-spacing: 0.24em;
            }

            .hero-jg .content .eyebrow::before {
                width: 44px;
            }

            .hero-jg .content h1 {
                font-size: 2.7rem;
                max-width: 11ch;
            }

            .hero-jg .content p {
                font-size: 0.94rem;
                max-width: 100%;
            }

            .scroll-cue {
                right: 1.35rem;
                bottom: 1.35rem;
            }
        }

        /* ===== PRESS STRIP ===== */
        .press-strip {
            padding: 1.25rem 0;
            background: var(--white);
            overflow: hidden;
            border-top: 1px solid rgba(201, 168, 76, 0.16);
            border-bottom: 1px solid rgba(201, 168, 76, 0.16);
        }

        .press-strip .inner {
            display: flex;
            align-items: center;
            gap: 0;
            width: max-content;
            white-space: nowrap;
            animation: ticker-scroll 26s linear infinite;
        }

        .press-strip:hover .inner {
            animation-play-state: paused;
        }

        .press-strip-item {
            display: inline-flex;
            align-items: center;
            gap: 1rem;
            padding: 0 1.75rem;
            font-size: 0.72rem;
            text-transform: uppercase;
            letter-spacing: 0.18em;
            color: #9c7b3f;
            font-weight: 700;
        }

        .press-strip-item i {
            color: #c9a84c;
            font-size: 0.72rem;
        }

        @keyframes ticker-scroll {
            from { transform: translateX(0); }
            to { transform: translateX(-50%); }
        }

        /* ===== FEATURES CAROUSEL ===== */
        .features-showcase {
            position: relative;
            min-height: 300px;
            padding: 2.5rem 1.75rem;
            border-radius: 26px;
            overflow: hidden;
            background:
                linear-gradient(180deg, rgba(247, 241, 230, 0.84), rgba(247, 241, 230, 0.9)),
                url('{{ asset('images/website images/img/img_1.webpe') }}') center/cover no-repeat;
            box-shadow: inset 0 0 0 1px rgba(201, 168, 76, 0.18);
        }

        .features-showcase::before {
            content: '';
            position: absolute;
            inset: 0;
            background: rgba(247, 241, 230, 0.5);
        }

        .features-carousel {
            position: relative;
            z-index: 1;
        }

        .features-carousel-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .features-controls {
            display: flex;
            align-items: center;
            gap: 0.8rem;
        }

        .feature-viewport {
            overflow: hidden;
        }

        .feature-track {
            display: flex;
            transition: transform 0.9s ease;
            will-change: transform;
        }

        .feature-slide {
            min-width: 100%;
            flex: 0 0 100%;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .feature-item {
            text-align: center;
            padding: 1.5rem;
            max-width: 760px;
            margin: 0 auto;
        }

        .feature-item i {
            font-size: 2.2rem;
            color: var(--accent);
            margin-bottom: 1rem;
        }

        .feature-item h4 {
            font-size: 1.5rem;
            margin-bottom: 0.7rem;
        }

        .feature-item p {
            font-size: 0.98rem;
            color: var(--body);
            line-height: 1.7;
            max-width: 42rem;
            margin: 0 auto;
        }

        .feature-nav-btn {
            position: relative;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 126px;
            padding: 0.85rem 1.2rem;
            border: 1px solid rgba(92, 134, 140, 0.26);
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.5);
            overflow: hidden;
            cursor: pointer;
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
        }

        .feature-nav-btn::before {
            content: '';
            position: absolute;
            inset: -30%;
            background: radial-gradient(circle, rgba(112, 238, 255, 0.24) 0%, rgba(112, 238, 255, 0) 70%);
            opacity: 0;
            transition: opacity 0.5s ease;
        }

        .feature-nav-btn::after {
            content: '';
            position: absolute;
            left: 1rem;
            right: 1rem;
            bottom: 0.55rem;
            height: 1px;
            background: linear-gradient(90deg, #5fdde4, #c6f8ff);
            transform: scaleX(0);
            transform-origin: left center;
            transition: transform 0.5s ease;
        }

        .feature-nav-btn:hover {
            border-color: rgba(95, 221, 228, 0.45);
            box-shadow: 0 10px 22px rgba(95, 221, 228, 0.12);
        }

        .feature-nav-btn:hover::before {
            opacity: 0.1;
        }

        .feature-nav-btn:hover::after {
            transform: scaleX(1);
        }

        .feature-nav-btn-text {
            position: relative;
            z-index: 1;
            display: inline-flex;
            gap: 0.2rem;
            text-transform: uppercase;
            letter-spacing: 0.18em;
            font-size: 0.72rem;
            font-weight: 700;
        }

        .feature-nav-btn-text .base {
            color: rgba(51, 123, 132, 0.7);
            transition: color 0.3s ease;
        }

        .feature-nav-btn-text .shine {
            color: rgba(51, 123, 132, 0.7);
            background: linear-gradient(90deg, #5fdde4, #c6f8ff);
            background-size: 0% 100%;
            background-repeat: no-repeat;
            -webkit-background-clip: text;
            background-clip: text;
            transition: color 0.3s ease, background-size 0.5s ease;
        }

        .feature-nav-btn:hover .feature-nav-btn-text .base {
            color: #4ac6d0;
        }

        .feature-nav-btn:hover .feature-nav-btn-text .shine {
            color: transparent;
            background-size: 100% 100%;
        }

        @media (max-width: 768px) {
            .features-showcase {
                padding: 1.5rem 1rem;
                min-height: 260px;
            }

            .features-carousel-header {
                flex-direction: column;
                align-items: stretch;
            }

            .features-controls {
                justify-content: center;
            }

            .feature-nav-btn {
                min-width: 112px;
            }
        }

        @media (max-width: 480px) {
            .feature-item h4 {
                font-size: 1.2rem;
            }

            .feature-item p {
                font-size: 0.9rem;
            }
        }

        /* ===== TWO COLUMN ===== */
        .two-col {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 4rem;
            align-items: center;
        }

        .two-col .image-block {
            border-radius: 12px;
            overflow: hidden;
        }

        .two-col .image-block img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        .two-col .text-block h2 {
            font-size: 2rem;
            margin-bottom: 1rem;
        }

        .two-col .text-block p {
            line-height: 1.7;
            margin-bottom: 1.5rem;
        }

        .stats-row {
            display: flex;
            gap: 2rem;
            margin-bottom: 1.5rem;
        }

        .stat-item {
            text-align: center;
        }

        .stat-item .number {
            font-family: 'Playfair Display', serif;
            font-size: 1.75rem;
            font-weight: 600;
            color: var(--navy);
        }

        .stat-item .label {
            font-size: 0.75rem;
            color: var(--tan);
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        @media (max-width: 768px) {
            .two-col {
                grid-template-columns: 1fr;
                gap: 2rem;
            }
            .two-col .text-block h2 { font-size: 1.5rem; }
            .stats-row { justify-content: center; }

            .home-product-grid .product-card,
            .home-explore-grid .product-card,
            .shop-product-grid .product-card {
                border-radius: 14px;
            }

            .home-product-grid .product-card .img-wrap,
            .home-explore-grid .product-card .img-wrap,
            .shop-product-grid .product-card .img-wrap {
                aspect-ratio: 1 / 0.92;
                display: block;
                padding: 0;
                background: #fffdf9;
            }

            .home-product-grid .product-card .img-wrap img,
            .home-explore-grid .product-card .img-wrap img,
            .shop-product-grid .product-card .img-wrap img {
                display: block;
                width: 100%;
                height: 100%;
                object-fit: cover;
                object-position: center;
            }

            .home-product-grid .product-card .tag,
            .home-explore-grid .product-card .tag,
            .shop-product-grid .product-card .tag {
                top: 0.75rem;
                left: 0.75rem;
                z-index: 2;
                max-width: calc(100% - 1.5rem);
                font-size: clamp(0.48rem, 1.5vw, 0.56rem);
                padding: 0.21rem 0.36rem;
                border-radius: 0.225rem;
                letter-spacing: 0.05em;
                line-height: 1.2;
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
            }

            .home-product-grid,
            .home-explore-grid,
            .shop-product-grid {
                gap: 0.75rem;
            }

            .home-product-grid .product-card .info,
            .home-explore-grid .product-card .info,
            .shop-product-grid .product-card .info {
                padding: 0.36rem 0.425rem 0.41rem;
                border-top: 1px solid rgba(49, 40, 33, 0.18);
            }

            .home-product-grid .product-card .info h3,
            .home-explore-grid .product-card .info h3,
            .shop-product-grid .product-card .info h3 {
                font-size: 0.9rem;
                line-height: 1.2;
                margin-bottom: 0.14rem;
                white-space: normal;
                text-overflow: clip;
                display: -webkit-box;
                -webkit-line-clamp: 2;
                -webkit-box-orient: vertical;
                min-height: calc(1.2em * 2);
            }

            .home-product-grid .product-card .info .details,
            .home-explore-grid .product-card .info .details,
            .shop-product-grid .product-card .info .details {
                font-size: 0.76rem;
                line-height: 1.35;
                margin-bottom: 0.24rem;
                min-height: calc(1.35em * 2);
            }

            .home-product-grid .product-card .info .price,
            .home-explore-grid .product-card .info .price,
            .shop-product-grid .product-card .info .price {
                display: flex;
                flex-direction: column;
                align-items: flex-start;
                gap: 0.06rem;
                margin-top: 0;
                margin-bottom: 0.36rem;
                white-space: normal;
                line-height: 1.12;
            }

            .home-product-grid .product-card .info .price .current,
            .home-explore-grid .product-card .info .price .current,
            .shop-product-grid .product-card .info .price .current {
                font-size: 0.82rem;
                font-weight: 600;
                color: var(--navy);
            }

            .home-product-grid .product-card .info .price .original,
            .home-explore-grid .product-card .info .price .original,
            .shop-product-grid .product-card .info .price .original {
                order: -1;
                font-size: 0.64rem;
                color: #7d746b;
                line-height: 1.08;
            }

            .home-product-grid .product-card .info .actions,
            .home-explore-grid .product-card .info .actions,
            .shop-product-grid .product-card .info .actions {
                display: grid;
                grid-template-columns: max-content max-content minmax(0, 1fr);
                align-items: center;
                gap: 0.21rem;
                min-width: 0;
                margin-top: 0;
            }

            .home-product-grid .product-card .info .actions .action-icon-btn,
            .home-explore-grid .product-card .info .actions .action-icon-btn,
            .shop-product-grid .product-card .info .actions .action-icon-btn {
                width: 40px;
                min-width: 40px;
                height: 40px;
                min-height: 40px;
                flex-shrink: 0;
                border-radius: 999px;
                box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
                background: #fffdf9;
            }

            .home-product-grid .product-card .info .actions .action-icon-btn i,
            .home-explore-grid .product-card .info .actions .action-icon-btn i,
            .shop-product-grid .product-card .info .actions .action-icon-btn i {
                font-size: 0.88rem;
            }

            .home-product-grid .product-card .info .actions .btn-jg,
            .home-explore-grid .product-card .info .actions .btn-jg,
            .shop-product-grid .product-card .info .actions .btn-jg {
                display: inline-flex !important;
                min-height: 40px;
                padding: 0.65rem 0.5rem;
                font-size: clamp(0.56rem, 1.8vw, 0.62rem);
                letter-spacing: 0.06em;
                justify-content: center;
                width: 100%;
                min-width: 0;
                overflow: hidden;
                text-overflow: ellipsis;
                border-radius: 12px;
                white-space: nowrap;
                line-height: 1;
            }
        }

        /* ===== CATEGORY EXPLORER ===== */
        .category-list {
            margin-top: 2rem;
        }

        .category-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 1.25rem 0;
            border-bottom: 1px solid var(--border);
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            color: var(--navy);
        }

        .category-item:hover {
            padding-left: 1rem;
            color: var(--accent);
        }

        .category-item .num {
            font-family: 'Playfair Display', serif;
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--tan);
            margin-right: 1.5rem;
        }

        .category-item .name {
            flex: 1;
            font-family: 'Playfair Display', serif;
            font-size: 1.25rem;
        }

        .category-item i {
            font-size: 1.25rem;
            color: var(--tan);
        }

        /* ===== PRODUCT GRID ===== */
        .product-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 1.5rem;
            margin-top: 2rem;
        }

        @media (max-width: 1024px) {
            .product-grid { grid-template-columns: repeat(2, 1fr); }
        }

        @media (max-width: 480px) {
            .product-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }

            .home-product-grid .product-card .tag,
            .home-explore-grid .product-card .tag,
            .shop-product-grid .product-card .tag {
                top: 0.68rem;
                left: 0.68rem;
                max-width: calc(100% - 1.36rem);
                font-size: clamp(0.42rem, 2.2vw, 0.5rem);
                padding: 0.17rem 0.31rem;
                border-radius: 0.225rem;
                letter-spacing: 0.04em;
            }

            .home-product-grid .product-card .info,
            .home-explore-grid .product-card .info,
            .shop-product-grid .product-card .info {
                padding: 0.32rem 0.36rem 0.37rem;
            }

            .home-product-grid .product-card .info h3,
            .home-explore-grid .product-card .info h3,
            .shop-product-grid .product-card .info h3 {
                font-size: 0.8rem;
                margin-bottom: 0.11rem;
            }

            .home-product-grid .product-card .info .details,
            .home-explore-grid .product-card .info .details,
            .shop-product-grid .product-card .info .details {
                font-size: 0.68rem;
                line-height: 1.3;
                margin-bottom: 0.19rem;
                min-height: calc(1.3em * 2);
            }

            .home-product-grid .product-card .info .price,
            .home-explore-grid .product-card .info .price,
            .shop-product-grid .product-card .info .price {
                gap: 0.04rem;
                margin-bottom: 0.31rem;
            }

            .home-product-grid .product-card .info .price .current,
            .home-explore-grid .product-card .info .price .current,
            .shop-product-grid .product-card .info .price .current {
                font-size: 0.76rem;
            }

            .home-product-grid .product-card .info .price .original,
            .home-explore-grid .product-card .info .price .original,
            .shop-product-grid .product-card .info .price .original {
                font-size: 0.58rem;
            }

            .home-product-grid .product-card .info .actions,
            .home-explore-grid .product-card .info .actions,
            .shop-product-grid .product-card .info .actions {
                grid-template-columns: max-content max-content minmax(0, 1fr);
                gap: 0.18rem;
            }

            .home-product-grid .product-card .info .actions .action-icon-btn,
            .home-explore-grid .product-card .info .actions .action-icon-btn,
            .shop-product-grid .product-card .info .actions .action-icon-btn {
                width: 38px;
                min-width: 38px;
                height: 38px;
                min-height: 38px;
                flex-shrink: 0;
            }

            .home-product-grid .product-card .info .actions .action-icon-btn i,
            .home-explore-grid .product-card .info .actions .action-icon-btn i,
            .shop-product-grid .product-card .info .actions .action-icon-btn i {
                font-size: 0.82rem;
            }

            .home-product-grid .product-card .info .actions .btn-jg,
            .home-explore-grid .product-card .info .actions .btn-jg,
            .shop-product-grid .product-card .info .actions .btn-jg {
                min-height: 38px;
                padding: 0.58rem 0.42rem;
                font-size: clamp(0.5rem, 2vw, 0.56rem);
                letter-spacing: 0.04em;
                border-radius: 11px;
                line-height: 1;
            }
        }

        /* ===== TESTIMONIALS ===== */
        .testimonial-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 2rem;
            margin-top: 2rem;
        }

        .testimonial-card {
            background: var(--white);
            padding: 2rem;
            border-radius: 12px;
            box-shadow: 0 2px 15px rgba(0,0,0,0.06);
        }

        .testimonial-card .quote {
            font-size: 0.9rem;
            line-height: 1.7;
            font-style: italic;
            margin-bottom: 1.5rem;
        }

        .testimonial-card .author {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .testimonial-card .avatar {
            width: 44px;
            height: 44px;
            border-radius: 50%;
            background: var(--accent);
            color: var(--white);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 1rem;
        }

        .testimonial-card .author-info .name {
            font-weight: 600;
            font-size: 0.9rem;
            color: var(--navy);
        }

        .testimonial-card .author-info .product {
            font-size: 0.8rem;
            color: var(--tan);
        }

        @media (max-width: 768px) {
            .testimonial-grid { grid-template-columns: 1fr; }
        }

        /* ===== CTA BANNER ===== */
        .cta-banner {
            background:#c9a84c;
            padding: 2rem 2rem;
            text-align: center;
            color: var(--white);
        }

        .cta-banner h2 {
            color: var(--white);
            font-size: 2.5rem;
            margin-bottom: 0.75rem;
        }

        .cta-banner p {
            font-size: 1rem;
            opacity: 0.8;
            margin-bottom: 2rem;
        }

        @media (max-width: 768px) {
            .cta-banner h2 { font-size: 1.8rem; }
        }

        /* ===== SHOP LAYOUT ===== */
        .shop-layout {
            display: grid;
            grid-template-columns: 280px 1fr;
            gap: 2rem;
            align-items: start;
        }

        .filter-sidebar {
            background: var(--white);
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 2px 15px rgba(0,0,0,0.06);
            position: sticky;
            top: 100px;
        }

        .filter-sidebar h4 {
            font-size: 0.9rem;
            margin-bottom: 1rem;
            padding-bottom: 0.75rem;
            border-bottom: 1px solid var(--border);
        }

        .filter-group {
            margin-bottom: 1.5rem;
        }

        .filter-group label {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.85rem;
            padding: 0.35rem 0;
            cursor: pointer;
        }

        .filter-group input[type="radio"],
        .filter-group input[type="checkbox"] {
            accent-color: var(--accent);
        }

        .price-range {
            display: flex;
            gap: 0.75rem;
            align-items: center;
        }

        .price-range input {
            width: 100%;
            padding: 0.5rem;
            border: 1.5px solid var(--border);
            border-radius: 6px;
            font-size: 0.8rem;
        }

        .shop-toolbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .shop-toolbar .count {
            font-size: 0.85rem;
            color: var(--body);
        }

        .shop-toolbar select {
            padding: 0.5rem 1rem;
            border: 1.5px solid var(--border);
            border-radius: 8px;
            font-size: 0.8rem;
            background: var(--white);
        }

        @media (max-width: 768px) {
            .shop-layout {
                grid-template-columns: 1fr;
            }
            .filter-sidebar {
                position: static;
            }
        }

        /* ===== PRODUCT DETAIL ===== */
        .pd-layout {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 3rem;
            align-items: start;
        }

        .pd-images .main-img {
            width: 100%;
            aspect-ratio: 1;
            border-radius: 12px;
            overflow: hidden;
            background: var(--cream);
            margin-bottom: 1rem;
        }

        .pd-images .main-img img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .pd-thumbs {
            display: flex;
            gap: 0.75rem;
        }

        .pd-thumbs .thumb {
            width: 80px;
            height: 80px;
            border-radius: 8px;
            overflow: hidden;
            cursor: pointer;
            border: 2px solid transparent;
            transition: border-color 0.3s ease;
        }

        .pd-thumbs .thumb.active {
            border-color: var(--accent);
        }

        .pd-thumbs .thumb img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .pd-info .badge {
            display: inline-block;
            background: var(--cream);
            color: var(--accent);
            font-size: 0.7rem;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            padding: 4px 12px;
            border-radius: 4px;
            margin-bottom: 0.75rem;
        }

        .pd-info h1 {
            font-size: 2rem;
            margin-bottom: 0.5rem;
        }

        .pd-info .subtitle {
            color: var(--tan);
            font-size: 0.9rem;
            margin-bottom: 1rem;
        }

        .pd-info .price-block {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--navy);
            margin-bottom: 1.5rem;
        }

        .pd-info .price-block .original {
            font-size: 1.1rem;
            color: var(--border);
            text-decoration: line-through;
            margin-left: 0.75rem;
            font-weight: 400;
        }

        .pd-info .section {
            margin-bottom: 1.5rem;
        }

        .pd-info .section h4 {
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 0.5rem;
        }

        .pd-info .cta-row {
            display: flex;
            gap: 1rem;
            margin-bottom: 1.5rem;
            flex-wrap: wrap;
        }

        .pd-info .cta-row .btn-jg i {
            flex-shrink: 0;
        }

        .pd-info .pd-action-group .pd-buy-now-wrap {
            margin-top: 1rem;
        }

        .pd-info .pd-action-group .pd-mobile-buy-now-wrap {
            display: block;
        }

        .pd-info .trust-row {
            display: flex;
            gap: 1.5rem;
            flex-wrap: wrap;
            padding: 1rem 0;
            border-top: 1px solid var(--border);
            border-bottom: 1px solid var(--border);
            margin-bottom: 1.5rem;
        }

        .pd-info .trust-row span {
            font-size: 0.8rem;
            color: var(--body);
            display: flex;
            align-items: center;
            gap: 0.4rem;
        }

        .pd-info .trust-row i {
            color: var(--accent);
        }

        @media (min-width: 769px) {
            .pd-layout {
                grid-template-columns: minmax(360px, 430px) minmax(0, 500px);
                gap: 2.4rem;
                justify-content: center;
            }

            .pd-images {
                max-width: 430px;
            }

            .pd-images .main-img {
                max-width: 430px;
                margin-bottom: 0;
                background: var(--white);
            }

            .pd-info {
                max-width: 500px;
                padding-top: 0.35rem;
            }

            .pd-info .badge {
                font-size: 0.68rem;
                font-weight: 600;
                letter-spacing: 0.08em;
                margin-bottom: 0.35rem;
            }

            .pd-info h1 {
                font-size: 2.08rem;
                line-height: 1.08;
                margin-bottom: 0.25rem;
            }

            .pd-info .subtitle {
                font-size: 0.82rem;
                margin-bottom: 1rem;
            }

            .pd-info .price-block {
                font-size: 1.42rem;
                margin-bottom: 1rem;
            }

            .pd-info .section {
                margin-bottom: 0.9rem;
            }

            .pd-info .section h4 {
                font-size: 0.74rem;
                letter-spacing: 0.08em;
                margin-bottom: 0.35rem;
            }

            .pd-info .pd-action-group {
                display: flex;
                flex-direction: column;
                gap: 0.75rem;
                margin: 1rem 0 0.95rem;
            }

            .pd-info .pd-action-group .pd-buy-now-wrap {
                order: 1;
                width: 100%;
                margin-top: 0 !important;
            }

            .pd-info .cta-row {
                order: 2;
                width: 100%;
                display: flex;
                flex-wrap: nowrap;
                gap: 0.8rem;
                margin-top: 0 !important;
                margin-bottom: 0;
            }

            .pd-info .pd-add-cart-btn,
            .pd-info .pd-wishlist-btn {
                flex: 1 1 0;
                min-width: 0;
                min-height: 48px;
                justify-content: center;
                padding-inline: 1rem;
                border-radius: 999px;
                box-shadow: none;
            }

            .pd-info .pd-buy-now-btn {
                min-height: 54px;
                border-radius: 999px;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                font-size: 0.88rem;
                letter-spacing: 0.07em;
                text-transform: uppercase;
            }

            .pd-info .pd-action-group .pd-mobile-buy-now-wrap {
                display: block;
            }

            .pd-info .trust-row {
                justify-content: space-between;
                gap: 0.75rem;
                padding: 0.9rem 0;
                margin-bottom: 1rem;
            }

            .pd-info .trust-row span {
                flex: 1 1 0;
                min-width: 0;
                flex-direction: column;
                align-items: center;
                gap: 0.35rem;
                text-align: center;
                font-size: 0.72rem;
            }

            .pd-info .trust-row i {
                font-size: 0.86rem;
            }

            .pd-info .tab-container {
                margin-top: 0;
            }

            .pd-info .tabs-jg {
                gap: 1.6rem;
                padding-bottom: 0.8rem;
                margin-bottom: 0.95rem;
            }

            .pd-info .tab-jg {
                padding-bottom: 0.78rem;
                margin-bottom: -0.8rem;
                font-size: 0.66rem;
                letter-spacing: 0.07em;
            }
        }

        @media (max-width: 768px) {
            .pd-layout {
                grid-template-columns: 1fr;
                gap: 2rem;
            }
            .pd-info h1 { font-size: 1.5rem; }

            .pd-info .trust-row {
                display: flex;
                flex-wrap: wrap;
                align-items: center;
                gap: 0.75rem 1rem;
                margin-top: 0.5rem;
                padding: 0.875rem 0;
                margin-bottom: 1.25rem;
            }

            .pd-info .trust-row span {
                gap: 6px;
                font-size: 0.78rem;
                line-height: 1.35;
            }

            .pd-info .trust-row i {
                font-size: 0.78rem;
                line-height: 1;
            }

            .pd-info .tab-container {
                margin-top: 0;
            }

            .pd-info .tabs-jg {
                display: grid;
                grid-template-columns: repeat(3, minmax(0, 1fr));
                align-items: end;
                gap: 0.5rem;
                padding-bottom: 0.625rem;
                margin-bottom: 1rem;
            }

            .pd-info .tab-jg {
                width: 100%;
                min-width: 0;
                padding: 0.1rem 0 0.625rem;
                margin-bottom: -0.625rem;
                text-align: center;
                white-space: nowrap;
                font-size: clamp(0.56rem, 1.95vw, 0.68rem);
                letter-spacing: 0.02em;
                line-height: 1.2;
            }

            .pd-info .tab-panel.active {
                padding-bottom: 0.25rem;
            }

            .product-card .info .actions .action-icon-btn,
            .pd-info .pd-add-cart-btn,
            .pd-info .pd-wishlist-btn {
                border-width: 1px;
                border-style: solid;
                border-color: rgba(201, 168, 76, 0.22);
                background: var(--cream);
                color: var(--body);
            }

            .product-card .info .actions .action-icon-btn.wishlist-btn.active,
            .pd-info .pd-wishlist-btn.active {
                color: #f60002;
                border-color: #f60002;
                background: #f6ded2;
            }

            .product-card .info .actions .action-icon-btn.cart-btn.active,
            .pd-info .pd-add-cart-btn.cart-btn.active {
                color: #cca43b;
                border-color: #cca43b;
                background: #f8f4e9;
            }

            .pd-info .pd-action-group {
                display: flex;
                gap: 0.5rem;
                align-items: center;
                justify-content: stretch;
                min-width: 0;
            }

            .pd-info .cta-row {
                display: contents;
                margin-top: 0 !important;
                margin-bottom: 0;
            }

            .pd-info .pd-action-group .pd-buy-now-wrap {
                display: flex;
                align-items: center;
                align-self: center;
                flex: 1 1 auto;
                margin-top: 0 !important;
                min-width: 0;
            }

            .pd-info .pd-action-group .pd-mobile-buy-now-wrap {
                display: flex;
            }

            .pd-info .pd-add-cart-btn,
            .pd-info .pd-wishlist-btn {
                display: inline-flex;
                align-items: center;
                width: 44px;
                min-width: 44px;
                height: 44px;
                min-height: 44px;
                flex: 0 0 44px;
                padding: 0;
                border-radius: 999px;
                justify-content: center;
                gap: 0;
                font-size: 0;
                letter-spacing: 0;
                box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
                flex-shrink: 0;
            }

            .pd-info .pd-add-cart-btn span,
            .pd-info .pd-wishlist-btn span {
                display: none;
            }

            .pd-info .pd-add-cart-btn i,
            .pd-info .pd-wishlist-btn i {
                font-size: 0.96rem;
                line-height: 1;
            }

            .pd-info .pd-buy-now-btn {
                width: 100%;
                min-width: 0;
                height: 44px;
                min-height: 44px;
                border-radius: 12px;
                background: var(--accent) !important;
                color: #ffffff !important;
                border-color: var(--accent) !important;
                border-width: 1px;
                display: inline-flex;
                justify-content: center;
                align-items: center;
                align-self: center;
                padding: 0 0.78rem;
                font-size: clamp(0.62rem, 1.9vw, 0.7rem);
                letter-spacing: 0.06em;
                line-height: 1;
                white-space: nowrap;
                text-transform: uppercase;
            }

            .pd-related-section .container-jg {
                box-sizing: border-box;
                padding-inline: 0.5rem;
            }

            .pd-related-section .product-grid {
                margin-top: 1.5rem;
            }
        }

        @media (max-width: 480px) {
            .pd-info .trust-row {
                gap: 0.625rem 0.875rem;
                margin-top: 0.4rem;
            }

            .pd-info .trust-row span {
                font-size: 0.74rem;
            }

            .pd-info .trust-row i {
                font-size: 0.74rem;
            }

            .pd-info .tabs-jg {
                gap: 0.35rem;
                padding-bottom: 0.55rem;
                margin-bottom: 0.9rem;
            }

            .pd-info .tab-jg {
                padding-bottom: 0.55rem;
                margin-bottom: -0.55rem;
                font-size: clamp(0.52rem, 1.9vw, 0.62rem);
                letter-spacing: 0.01em;
            }

            .pd-info .pd-action-group {
                gap: 0.36rem;
            }

            .pd-info .pd-add-cart-btn,
            .pd-info .pd-wishlist-btn {
                width: 40px;
                min-width: 40px;
                height: 40px;
                min-height: 40px;
                flex: 0 0 40px;
            }

            .pd-info .pd-add-cart-btn i,
            .pd-info .pd-wishlist-btn i {
                font-size: 0.88rem;
            }

            .pd-info .pd-buy-now-btn {
                height: 40px;
                min-height: 40px;
                padding: 0 0.62rem;
                font-size: clamp(0.56rem, 2vw, 0.62rem);
                border-radius: 11px;
                letter-spacing: 0.04em;
            }

            .pd-related-section .container-jg {
                padding-inline: 0.45rem;
            }

            .pd-related-section .product-grid {
                gap: 0.75rem;
            }
        }

        /* ===== CART LAYOUT ===== */
        .cart-layout {
            display: grid;
            grid-template-columns: 1.5fr 1fr;
            gap: 2rem;
            align-items: start;
        }

        .cart-item-row {
            display: flex;
            gap: 1rem;
            padding: 1.5rem 0;
            border-bottom: 1px solid var(--border);
            align-items: center;
        }

        .cart-item-row .thumb {
            width: 100px;
            height: 100px;
            border-radius: 8px;
            overflow: hidden;
            flex-shrink: 0;
        }

        .cart-item-row .thumb img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .cart-item-row .details {
            flex: 1;
        }

        .cart-item-row .details h4 {
            font-size: 1rem;
            margin-bottom: 0.25rem;
        }

        .cart-item-row .details .options {
            font-size: 0.8rem;
            color: var(--tan);
        }

        .cart-item-row .details .unit-price {
            font-size: 0.85rem;
            font-weight: 500;
            color: var(--navy);
            margin-top: 0.25rem;
        }

        .cart-item-row .right {
            text-align: right;
        }

        .cart-item-row .right .line-total {
            font-weight: 600;
            color: var(--navy);
            margin-bottom: 0.5rem;
        }

        .cart-item-row .right .remove-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 40px;
            padding: 0.65rem 1rem;
            border: 1px solid #ff0d00;
            border-radius: 9px;
            background: #ff0d00;
            color: #ffffff;
            font-size: 0.62rem;
            font-weight: 600;
            letter-spacing: 0.06em;
            line-height: 1;
            text-transform: uppercase;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .cart-item-row .right .remove-btn:hover {
            background: #b2332d;
            border-color: #b2332d;
            color: #ffffff;
        }

        .order-summary {
            background: var(--white);
            border-radius: 12px;
            padding: 2rem;
            box-shadow: 0 2px 15px rgba(0,0,0,0.06);
            position: sticky;
            top: 100px;
        }

        .order-summary h3 {
            font-size: 1.25rem;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid var(--border);
        }

        .order-summary .row {
            display: flex;
            justify-content: space-between;
            padding: 0.5rem 0;
            font-size: 0.9rem;
        }

        .order-summary .row.total {
            font-weight: 600;
            font-size: 1.1rem;
            color: var(--navy);
            border-top: 1px solid var(--border);
            padding-top: 1rem;
            margin-top: 0.5rem;
        }

        .order-summary .coupon {
            display: flex;
            gap: 0.5rem;
            margin: 1rem 0;
        }

        .order-summary .coupon input {
            flex: 1;
            padding: 0.6rem 1rem;
            border: 1.5px solid var(--border);
            border-radius: 8px;
            font-size: 0.8rem;
        }

        .order-summary .btn-jg {
            width: 100%;
            margin-top: 1rem;
        }

        @media (max-width: 768px) {
            .cart-layout {
                grid-template-columns: 1fr;
            }
            .cart-item-row {
                flex-wrap: wrap;
            }
            .cart-item-row .right {
                width: 100%;
                text-align: left;
                display: flex;
                justify-content: space-between;
                align-items: center;
            }
            .cart-continue-row {
                justify-content: center;
            }
        }

        /* ===== CHECKOUT ===== */
        .checkout-layout {
            display: grid;
            grid-template-columns: 1.5fr 1fr;
            gap: 2rem;
            align-items: start;
        }

        .checkout-form-section {
            background: var(--white);
            border-radius: 12px;
            padding: 2rem;
            box-shadow: 0 2px 15px rgba(0,0,0,0.06);
            margin-bottom: 1.5rem;
        }

        .checkout-form-section h3 {
            font-size: 1.1rem;
            margin-bottom: 1.25rem;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }

        @media (max-width: 768px) {
            .checkout-layout {
                grid-template-columns: 1fr;
            }
            .form-row {
                grid-template-columns: 1fr;
            }
        }

        /* ===== CONFIRMATION ===== */
        .confirmation {
            text-align: center;
            max-width: 550px;
            margin: 0 auto;
            padding: 3rem 0;
        }

        .confirmation .check-icon {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: #d4edda;
            color: #155724;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.5rem;
            margin: 0 auto 1.5rem;
        }

        .confirmation h1 {
            font-size: 2rem;
            margin-bottom: 0.75rem;
        }

        .confirmation p {
            margin-bottom: 0.5rem;
        }

        .confirmation .order-num {
            font-family: 'Playfair Display', serif;
            font-size: 1.25rem;
            color: var(--accent);
            margin-bottom: 2rem;
        }

        /* ===== ACCOUNT ===== */
        .account-layout {
            display: grid;
            grid-template-columns: 280px 1fr;
            gap: 2rem;
            align-items: start;
        }

        .account-sidebar {
            background: var(--white);
            border: 1px solid rgba(201, 168, 76, 0.16);
            border-radius: 16px;
            padding: 2.2rem 1.8rem;
            box-shadow: 0 8px 30px rgba(42, 34, 26, 0.05);
            position: sticky;
            top: 140px;
            text-align: center;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .account-sidebar:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 40px rgba(42, 34, 26, 0.08);
        }

        .account-sidebar .avatar-lg {
            width: 72px;
            height: 72px;
            border-radius: 50%;
            background: var(--accent);
            color: var(--white);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.75rem;
            font-weight: 600;
            margin: 0 auto 1rem;
        }

        .account-sidebar .name {
            font-weight: 600;
            color: var(--navy);
            margin-bottom: 0.25rem;
        }

        .account-sidebar .email {
            font-size: 0.8rem;
            color: var(--tan);
            margin-bottom: 1.5rem;
        }

        .account-nav {
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
        }

        .account-nav a {
            padding: 0.65rem 1rem;
            border-radius: 8px;
            text-decoration: none;
            color: var(--body);
            font-size: 0.85rem;
            transition: all 0.3s ease;
            text-align: left;
        }

        .account-nav a:hover,
        .account-nav a.active {
            background: var(--cream);
            color: var(--navy);
        }

        .account-content {
            background: var(--white);
            border: 1px solid rgba(201, 168, 76, 0.16);
            border-radius: 16px;
            padding: 2.5rem;
            box-shadow: 0 8px 30px rgba(42, 34, 26, 0.05);
        }

        .order-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1.25rem 0;
            border-bottom: 1px solid rgba(201, 168, 76, 0.12);
        }

        .order-row:last-child {
            border-bottom: none;
        }

        .order-row .order-info .order-num {
            font-weight: 600;
            color: var(--navy);
        }

        .order-row .order-info .order-meta {
            font-size: 0.8rem;
            color: var(--tan);
        }

        @media (max-width: 768px) {
            .account-layout {
                grid-template-columns: 1fr;
            }
            .account-sidebar {
                position: static;
            }
        }

        /* ===== SIGN IN / REGISTER ===== */
        .auth-card {
            max-width: 440px;
            margin: 0 auto;
            padding: 2rem 0;
        }

        .auth-card .card-jg {
            padding: 2.5rem;
        }

        .auth-card h2 {
            text-align: center;
            margin-bottom: 0.5rem;
        }

        .auth-card .subtitle {
            text-align: center;
            color: var(--tan);
            font-size: 0.9rem;
            margin-bottom: 2rem;
        }

        .auth-card .link {
            text-align: center;
            font-size: 0.85rem;
            margin-top: 1rem;
        }

        .auth-card .link a {
            color: var(--accent);
            text-decoration: none;
        }

        .auth-card .link a:hover {
            text-decoration: underline;
        }

        /* ===== CONTACT ===== */
        .contact-layout {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 3rem;
            align-items: start;
        }

        .contact-info h3 {
            font-size: 1.25rem;
            margin-bottom: 1rem;
        }

        .contact-info .detail {
            margin-bottom: 1.5rem;
        }

        .contact-info .detail h4 {
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 0.35rem;
        }

        .contact-info .detail p {
            font-size: 0.9rem;
            line-height: 1.5;
        }

        @media (max-width: 768px) {
            .contact-layout {
                grid-template-columns: 1fr;
                gap: 2rem;
            }
        }

        /* ===== JOURNAL ===== */
        .journal-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 2rem;
            margin-top: 2rem;
        }

        .journal-card {
            background: var(--white);
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 2px 15px rgba(0,0,0,0.06);
            transition: all 0.4s ease;
            cursor: pointer;
        }

        .journal-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 35px rgba(0,0,0,0.1);
        }

        .journal-card .img-wrap {
            aspect-ratio: 16/10;
            overflow: hidden;
        }

        .journal-card .img-wrap img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.6s ease;
        }

        .journal-card:hover .img-wrap img {
            transform: scale(1.05);
        }

        .journal-card .info {
            padding: 1.5rem;
        }

        .journal-card .info .category {
            font-size: 0.65rem;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            color: var(--accent);
            font-weight: 500;
            margin-bottom: 0.5rem;
        }

        .journal-card .info h3 {
            font-size: 1.1rem;
            line-height: 1.4;
        }

        @media (max-width: 1024px) {
            .journal-grid { grid-template-columns: repeat(2, 1fr); }
        }

        @media (max-width: 480px) {
            .journal-grid { grid-template-columns: 1fr; }
        }

        /* ===== EMPTY STATE ===== */
        .empty-state {
            text-align: center;
            padding: 3rem 1rem;
        }

        .empty-state i {
            font-size: 3rem;
            color: var(--tan);
            margin-bottom: 1rem;
        }

        .empty-state h3 {
            margin-bottom: 0.5rem;
        }

        .empty-state p {
            color: var(--tan);
            margin-bottom: 1.5rem;
        }
    </style>

</head>
<body>
    @php
        $siteName = App\Models\WebsiteContent::getValue('company_name', 'Jaffna Gold');
        $announcement = App\Models\WebsiteContent::getValue('announcement_text', '✨ INSTANT 10% DISCOUNT ON ALL HANDCRAFTED PIECES | COMPLIMENTARY SHIPPING FROM JAFFNA ✨');
        $cartCount = count(session('cart', []));
        $wishlistCount = count(session('wishlist', []));

        // Split the brand name into words so it can wrap onto two lines on mobile
        $siteNameWords = preg_split('/\s+/', trim($siteName));

        // If logged in as admin/staff, route them to their role-specific dashboard.
        // Regular customers keep the account page; guests are sent to login.
        if (Auth::check() && (Auth::user()->isAdmin() || Auth::user()->isStaff())) {
            $accountUrl = route('dashboard');
        } else {
            $accountUrl = Auth::check() ? route('account') : url('/login');
        }
    @endphp

    <div class="site-chrome">
        <div class="announcement-bar">
            <div class="announcement-bar-inner">
                <div class="announcement-marquee" aria-label="Site announcement">
                    <div class="announcement-marquee-track">
                        <span class="announcement-marquee-item"><i class="bi bi-stars"></i> {{ $announcement }} <i class="bi bi-stars"></i></span>
                        <span class="announcement-marquee-item" aria-hidden="true"><i class="bi bi-stars"></i> {{ $announcement }} <i class="bi bi-stars"></i></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- ===== NAVBAR ===== -->
        <nav class="navbar-jg transparent" id="mainNav">
            <div class="nav-shell">
                <div class="nav-brand-row">
                    <a href="{{ route('home') }}" class="nav-logo-wrap">
                        <img src="{{ asset('images/website images/logohero.webp') }}" alt="Jaffna Gold logo" class="nav-logo-mark">
                        <span class="nav-logo-copy">
                            <span class="nav-logo" aria-label="{{ $siteName }}">
                                @foreach ($siteNameWords as $i => $word)
                                    @if ($i > 0)
                                        <span class="nav-logo-sep" aria-hidden="true"></span>
                                    @endif
                                    <span class="nav-logo-word">{{ $word }}</span>
                                @endforeach
                            </span>
                            <span class="nav-tagline"><i class="bi bi-stars"></i> Handcrafted Covering <i class="bi bi-stars"></i></span>
                        </span>
                    </a>
                </div>

                <div class="nav-menu-row">
                    <div class="nav-center">
                        <a href="{{ route('home') }}" class="nav-link {{ request()->routeIs('home') ? 'active' : '' }}">Home</a>
                        <a href="{{ route('shop') }}" class="nav-link {{ request()->routeIs('shop') ? 'active' : '' }}">Shop</a>
                        <a href="{{ route('custom-products') }}" class="nav-link {{ request()->routeIs('custom-products') ? 'active' : '' }}"><i class="bi bi-gem"></i> Custom Jewelry</a>
                        <a href="{{ route('about') }}" class="nav-link {{ request()->routeIs('about') ? 'active' : '' }}">Our World</a>
                        <a href="{{ route('contact') }}" class="nav-link {{ request()->routeIs('contact') ? 'active' : '' }}">Contact</a>
                    </div>

                    <div class="nav-menu-tools">
                        <div class="nav-right">
                            <div class="header-search" data-header-search>
                                <button class="nav-icon nav-icon-button header-search-toggle" type="button" title="Search" aria-label="Open search" aria-expanded="false" data-header-search-toggle>
                                    <i class="bi bi-search"></i>
                                </button>
                                <div class="header-search-panel">
                                    <form class="header-search-form" action="{{ route('shop') }}" method="GET" role="search">
                                        <input class="header-search-input" type="search" name="search" placeholder="Search item" aria-label="Search item" autocomplete="off">
                                        <button class="header-search-submit" type="submit" aria-label="Submit search">
                                            <i class="bi bi-search"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                            @if (config('image_search.enabled'))
                                <a href="{{ route('visual-search') }}" class="nav-icon nav-visual-search-link {{ request()->routeIs('visual-search') ? 'active' : '' }}" title="Search by Photo" aria-label="Search by photo">
                                    <i class="bi bi-camera"></i>
                                </a>
                            @endif
                            <a href="{{ $accountUrl }}" class="nav-icon nav-account-link" title="Account" aria-label="Account">
                                <i class="bi bi-person{{ Auth::check() ? '-fill' : '' }}"></i>
                            </a>
                            <a href="{{ route('wishlist') }}" class="nav-icon nav-wishlist-link" title="Wishlist" aria-label="Wishlist">
                                <i class="bi bi-heart"></i>
                                @if($wishlistCount > 0)
                                    <span class="badge-jg">{{ $wishlistCount }}</span>
                                @endif
                            </a>
                            <a href="{{ route('cart') }}" class="nav-icon" title="Cart" aria-label="Cart">
                                <i class="bi bi-bag"></i>
                                @if($cartCount > 0)
                                    <span class="badge-jg">{{ $cartCount }}</span>
                                @endif
                            </a>
                        </div>

                        <button class="hamburger" id="hamburgerBtn" aria-label="Open menu" aria-expanded="false" aria-controls="mobileMenu" type="button">
                            <span class="hamburger-line"></span>
                            <span class="hamburger-line"></span>
                            <span class="hamburger-line"></span>
                        </button>
                    </div>
                </div>
            </div>
        </nav>
    </div>

    <!-- ===== MOBILE MENU ===== -->
    <div class="mobile-menu-overlay" id="mobileMenuOverlay" hidden></div>
    <div class="mobile-menu" id="mobileMenu">
        <a href="{{ route('home') }}" class="nav-link {{ request()->routeIs('home') ? 'active' : '' }}">Home</a>
        <a href="{{ route('shop') }}" class="nav-link {{ request()->routeIs('shop') ? 'active' : '' }}">Shop</a>
        <a href="{{ route('custom-products') }}" class="nav-link {{ request()->routeIs('custom-products') ? 'active' : '' }}"><i class="bi bi-gem"></i> Custom Jewelry</a>
        <a href="{{ route('about') }}" class="nav-link {{ request()->routeIs('about') ? 'active' : '' }}">Our World</a>
        @if (config('image_search.enabled'))
            <a href="{{ route('visual-search') }}" class="nav-link {{ request()->routeIs('visual-search') ? 'active' : '' }}"><i class="bi bi-camera"></i> Search by Photo</a>
        @endif
        <a href="{{ route('contact') }}" class="nav-link {{ request()->routeIs('contact') ? 'active' : '' }}">Contact</a>
        <div class="mobile-menu-meta">
            <a href="{{ $accountUrl }}" class="mobile-menu-text-link {{ request()->routeIs('account') ? 'is-active' : '' }}">Account</a>
            <a href="{{ route('wishlist') }}" class="mobile-menu-text-link {{ request()->routeIs('wishlist') ? 'is-active' : '' }}">
                <span>Wishlist</span>
                @if($wishlistCount > 0)
                    <span class="badge-jg">{{ $wishlistCount }}</span>
                @endif
            </a>
            <a href="{{ route('cart') }}" class="mobile-menu-text-link {{ request()->routeIs('cart') ? 'is-active' : '' }}">
                <span>Cart</span>
                @if($cartCount > 0)
                    <span class="badge-jg">{{ $cartCount }}</span>
                @endif
            </a>
        </div>
    </div>

    <!-- ===== MAIN CONTENT ===== -->
    <main>
        {{ $slot }}
    </main>

    <!-- ===== FOOTER ===== -->
    <footer class="footer-jg">
        <div class="container-jg">
            <div class="footer-brand-column">
                <div class="brand">Jaffna Gold</div>
                <p class="mission">Crafting timeless jewelry pieces that celebrate life's most precious moments, with uncompromising quality and artistry since 2014.</p>
                <div class="footer-social-row" aria-label="Social media links">
                    <a href="https://wa.me/94761919650" class="footer-social-link is-whatsapp" target="_blank" rel="noopener noreferrer" aria-label="WhatsApp">
                        <i class="bi bi-whatsapp"></i>
                    </a>
                    <a href="https://web.facebook.com/jafnagold/?_rdc=1&_rdr#" class="footer-social-link is-facebook" target="_blank" rel="noopener noreferrer" aria-label="Facebook">
                        <i class="bi bi-facebook"></i>
                    </a>
                    <a href="https://www.instagram.com/jaffna.gold/?hl=en" class="footer-social-link is-instagram" target="_blank" rel="noopener noreferrer" aria-label="Instagram">
                        <i class="bi bi-instagram"></i>
                    </a>
                    <a href="https://www.youtube.com/@safrinsrilanka9993" class="footer-social-link is-youtube" target="_blank" rel="noopener noreferrer" aria-label="YouTube">
                        <i class="bi bi-youtube"></i>
                    </a>
                    <a href="https://www.tiktok.com/@jaffna.gold.237" class="footer-social-link is-tiktok" target="_blank" rel="noopener noreferrer" aria-label="TikTok">
                        <i class="bi bi-tiktok"></i>
                    </a>
                </div>
            </div>
            <div>
                <h4>Shop</h4>
                <a href="{{ route('shop') }}?category=rings">Rings</a>
                <a href="{{ route('shop') }}?category=necklaces">Necklaces</a>
                <a href="{{ route('shop') }}?category=earrings">Earrings</a>
                <a href="{{ route('shop') }}?category=bridal">Bridal</a>
            </div>
            <div>
                <h4>Company</h4>
                <a href="{{ route('about') }}">About</a>
                <a href="{{ route('custom-products') }}">Custom Jewelry</a>
                <a href="{{ route('contact') }}">Contact</a>
            </div>
            <div>
                <h4>Account</h4>
                <a href="{{ url('/login') }}">Sign In</a>
                <a href="{{ route('wishlist') }}">Wishlist</a>
                <a href="{{ route('cart') }}">Cart</a>
            </div>
        </div>
         <div class="footer-bottom">
                <span>{{ App\Models\WebsiteContent::getValue('footer_copyright', '© 2026 Jaffna Gold. All rights reserved.') }}</span>
                <a href="{{ Route::has('privacy-policy') ? route('privacy-policy') : url('/privacy-policy') }}" class="footer-bottom-link">
                    <span class="footer-link-main">Privacy Policy</span>
                </a>
                <a href="{{ Route::has('terms-of-service') ? route('terms-of-service') : url('/terms-of-service') }}" class="footer-bottom-link">
                    <span class="footer-link-main">Terms of Service</span>
                </a>
                <a href="https://webxkey.com/" class="footer-bottom-link footer-webxkey-link" target="_blank" rel="noreferrer">
                    <span class="footer-link-main">Design &amp; Developed by </span><span class="footer-link-accent">Webxkey</span>
                </a>
            </div>

        <div class="footer-mobile-shell">
            <div class="footer-mobile-brand-row">
                <img src="{{ asset('images/website images/logohero.webp') }}" alt="Jaffna Gold logo" class="footer-mobile-brand-mark">
                <span class="footer-mobile-brand-name">{{ $siteName }}</span>
            </div>

            <nav class="footer-mobile-accordion" aria-label="Footer navigation">
                <section class="footer-mobile-section">
                    <button class="footer-mobile-trigger" type="button" aria-expanded="false" aria-controls="footer-mobile-account">
                        <span>Account</span>
                        <i class="bi bi-chevron-down"></i>
                    </button>
                    <div class="footer-mobile-panel" id="footer-mobile-account">
                        <div class="footer-mobile-panel-inner">
                            <ul class="footer-mobile-links">
                                <li><a href="{{ url('/login') }}">Sign In</a></li>
                                <li><a href="{{ route('wishlist') }}">Wishlist</a></li>
                                <li><a href="{{ route('cart') }}">Cart</a></li>
                            </ul>
                        </div>
                    </div>
                </section>

                <section class="footer-mobile-section">
                    <button class="footer-mobile-trigger" type="button" aria-expanded="false" aria-controls="footer-mobile-company">
                        <span>Shop</span>
                        <i class="bi bi-chevron-down"></i>
                    </button>
                    <div class="footer-mobile-panel" id="footer-mobile-company">
                        <div class="footer-mobile-panel-inner">
                            <ul class="footer-mobile-links">
                                <li><a href="{{ route('about') }}">About</a></li>
                                <li><a href="{{ route('custom-products') }}">Custom Jewelry</a></li>
                                <li><a href="{{ route('contact') }}">Contact</a></li>
                                <li><a href="{{ route('shop') }}?category=rings">Rings</a></li>
                                <li><a href="{{ route('shop') }}?category=necklaces">Necklaces</a></li>
                                <li><a href="{{ route('shop') }}?category=earrings">Earrings</a></li>
                                <li><a href="{{ route('shop') }}?category=bridal">Bridal</a></li>
                            </ul>
                        </div>
                    </div>
                </section>

                <section class="footer-mobile-section">
                    <button class="footer-mobile-trigger" type="button" aria-expanded="false" aria-controls="footer-mobile-legal">
                        <span>Legal</span>
                        <i class="bi bi-chevron-down"></i>
                    </button>
                    <div class="footer-mobile-panel" id="footer-mobile-legal">
                        <div class="footer-mobile-panel-inner">
                            <ul class="footer-mobile-links">
                                <li>
                                    <a href="{{ Route::has('privacy-policy') ? route('privacy-policy') : url('/privacy-policy') }}" class="footer-bottom-link">
                                        <span class="footer-link-main">Privacy Policy</span>
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ Route::has('terms-of-service') ? route('terms-of-service') : url('/terms-of-service') }}" class="footer-bottom-link">
                                        <span class="footer-link-main">Terms of Service</span>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </section>
            </nav>

            <div class="footer-mobile-social-wrap">
                <div class="footer-social-row footer-mobile-social-row" aria-label="Social media links">
                    <a href="https://wa.me/94761919650" class="footer-social-link is-whatsapp" target="_blank" rel="noopener noreferrer" aria-label="WhatsApp">
                        <i class="bi bi-whatsapp"></i>
                    </a>
                    <a href="https://web.facebook.com/jafnagold/?_rdc=1&_rdr#" class="footer-social-link is-facebook" target="_blank" rel="noopener noreferrer" aria-label="Facebook">
                        <i class="bi bi-facebook"></i>
                    </a>
                    <a href="https://www.instagram.com/jaffna.gold/?hl=en" class="footer-social-link is-instagram" target="_blank" rel="noopener noreferrer" aria-label="Instagram">
                        <i class="bi bi-instagram"></i>
                    </a>
                    <a href="https://www.youtube.com/@safrinsrilanka9993" class="footer-social-link is-youtube" target="_blank" rel="noopener noreferrer" aria-label="YouTube">
                        <i class="bi bi-youtube"></i>
                    </a>
                    <a href="https://www.tiktok.com/@jaffna.gold.237" class="footer-social-link is-tiktok" target="_blank" rel="noopener noreferrer" aria-label="TikTok">
                        <i class="bi bi-tiktok"></i>
                    </a>
                </div>
            </div>

            <div class="footer-mobile-meta">
                <span class="footer-mobile-meta-line">{{ App\Models\WebsiteContent::getValue('footer_copyright', '© 2026 Jaffna Gold. All rights reserved.') }}</span>
                <a href="https://webxkey.com/" class="footer-mobile-meta-credit footer-bottom-link footer-webxkey-link" target="_blank" rel="noreferrer">
                    <span class="footer-link-main">Design &amp; Developed by </span><span class="footer-link-accent">Webxkey</span>
                </a>
            </div>
        </div>
    </footer>

    <!-- ===== TOAST ===== -->
    <div class="toast-jg" id="toast"></div>

    <script>
        // ===== DYNAMIC HEADER OFFSET =====
        // Keep --header-offset in sync with the actual rendered height of .site-chrome
        // so that fixed-position child elements (e.g. the shop filter panel) sit flush beneath.
        function syncHeaderOffset() {
            const siteChrome = document.querySelector('.site-chrome');
            if (!siteChrome) return;
            requestAnimationFrame(function() {
                const h = siteChrome.offsetHeight;
                document.documentElement.style.setProperty('--header-offset', h + 'px');
            });
        }

        // Run on every scroll/resize to handle announcement bar show/hide
        window.addEventListener('scroll', syncHeaderOffset, { passive: true });
        window.addEventListener('resize', syncHeaderOffset, { passive: true });

        // ===== NAVBAR SCROLL =====
        const nav = document.getElementById('mainNav');
        const announcementBar = document.querySelector('.announcement-bar');
        const heroSection = document.querySelector('.hero-jg');
        let lastScrollY = window.scrollY;

        function updateNav() {
            if (!nav) {
                return;
            }

            const currentScrollY = window.scrollY;

            if (heroSection) {
                const heroBottom = heroSection.offsetTop + heroSection.offsetHeight;
                if (currentScrollY > heroBottom - 80) {
                    nav.classList.remove('transparent');
                    nav.classList.add('scrolled');
                } else {
                    nav.classList.remove('scrolled');
                    nav.classList.add('transparent');
                }
            } else {
                if (currentScrollY > 80) {
                    nav.classList.remove('transparent');
                    nav.classList.add('scrolled');
                } else {
                    nav.classList.remove('scrolled');
                    nav.classList.add('transparent');
                }
            }

            if (announcementBar) {
                if (window.innerWidth <= 768) {
                    announcementBar.classList.remove('is-hidden');
                } else if (currentScrollY <= 24 || currentScrollY < lastScrollY) {
                    announcementBar.classList.remove('is-hidden');
                } else if (currentScrollY > 80) {
                    announcementBar.classList.add('is-hidden');
                }
            }

            lastScrollY = currentScrollY;
        }

        window.addEventListener('scroll', updateNav);
        window.addEventListener('load', updateNav);

        // ===== HERO SLIDESHOW =====
        const heroSlides = Array.from(document.querySelectorAll('.hero-slide'));
        if (heroSlides.length > 1) {
            let activeHeroSlide = 0;

            window.setInterval(() => {
                heroSlides[activeHeroSlide].classList.remove('is-active');
                activeHeroSlide = (activeHeroSlide + 1) % heroSlides.length;
                heroSlides[activeHeroSlide].classList.add('is-active');
            }, 3000);
        }

        // ===== MOBILE MENU =====
        const hamburgerBtn = document.getElementById('hamburgerBtn');
        const mobileMenu = document.getElementById('mobileMenu');
        const mobileMenuOverlay = document.getElementById('mobileMenuOverlay');

        if (hamburgerBtn && mobileMenu && mobileMenuOverlay) {
            const setMobileMenuOpen = (shouldOpen) => {
                mobileMenu.classList.toggle('open', shouldOpen);
                mobileMenuOverlay.classList.toggle('open', shouldOpen);
                hamburgerBtn.classList.toggle('is-open', shouldOpen);
                hamburgerBtn.setAttribute('aria-expanded', shouldOpen ? 'true' : 'false');
                hamburgerBtn.setAttribute('aria-label', shouldOpen ? 'Close menu' : 'Open menu');
                document.body.classList.toggle('mobile-menu-open', shouldOpen);
                mobileMenuOverlay.hidden = !shouldOpen;
            };

            hamburgerBtn.addEventListener('click', function (event) {
                event.preventDefault();
                setMobileMenuOpen(!mobileMenu.classList.contains('open'));
            });

            mobileMenuOverlay.addEventListener('click', function () {
                setMobileMenuOpen(false);
            });

            document.addEventListener('keydown', function (event) {
                if (event.key === 'Escape' && mobileMenu.classList.contains('open')) {
                    setMobileMenuOpen(false);
                    hamburgerBtn.focus();
                }
            });

            document.addEventListener('click', function (event) {
                if (window.innerWidth > 1024 || !mobileMenu.classList.contains('open')) {
                    return;
                }

                if (!mobileMenu.contains(event.target) && !hamburgerBtn.contains(event.target)) {
                    setMobileMenuOpen(false);
                }
            });

            mobileMenu.querySelectorAll('a').forEach((link) => {
                link.addEventListener('click', function () {
                    setMobileMenuOpen(false);
                });
            });

            window.addEventListener('resize', function () {
                if (window.innerWidth > 1024 && mobileMenu.classList.contains('open')) {
                    setMobileMenuOpen(false);
                }
            });
        }

        // ===== MOBILE FOOTER ACCORDION =====
        document.querySelectorAll('.footer-mobile-trigger').forEach((trigger) => {
            const panelId = trigger.getAttribute('aria-controls');
            const panel = panelId ? document.getElementById(panelId) : null;

            if (!panel) {
                return;
            }

            trigger.addEventListener('click', () => {
                const shouldOpen = trigger.getAttribute('aria-expanded') !== 'true';
                trigger.setAttribute('aria-expanded', shouldOpen ? 'true' : 'false');
                panel.classList.toggle('is-open', shouldOpen);
            });
        });

        // ===== HEADER SEARCH =====
        document.querySelectorAll('[data-header-search]').forEach((search) => {
            const toggle = search.querySelector('[data-header-search-toggle]');
            const input = search.querySelector('.header-search-input');
            const reduceMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

            if (!toggle || !input) {
                return;
            }

            const setSearchOpen = (shouldOpen) => {
                search.classList.toggle('is-open', shouldOpen);
                toggle.setAttribute('aria-expanded', shouldOpen ? 'true' : 'false');

                if (shouldOpen) {
                    const focusDelay = reduceMotion ? 0 : 60;
                    setTimeout(() => input.focus(), focusDelay);
                }
            };

            toggle.addEventListener('click', function (e) {
                e.preventDefault();
                const isOpen = !search.classList.contains('is-open');
                setSearchOpen(isOpen);
            });

            document.addEventListener('click', function (e) {
                if (!search.contains(e.target) && input.value.trim() === '') {
                    setSearchOpen(false);
                }
            });

            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape' && search.classList.contains('is-open')) {
                    setSearchOpen(false);
                    toggle.focus();
                }
            });

            input.addEventListener('focus', function () {
                setSearchOpen(true);
            });

            input.form?.addEventListener('submit', function () {
                toggle.setAttribute('aria-expanded', 'false');
            });

            input.addEventListener('keydown', function (e) {
                if (e.key === 'Escape') {
                    e.preventDefault();
                    setSearchOpen(false);
                    toggle.focus();
                }
            });

            input.addEventListener('search', function () {
                if (input.value.trim() === '' && document.activeElement !== input) {
                    setSearchOpen(false);
                }
            });

            input.addEventListener('blur', function () {
                if (input.value.trim() === '' && !search.matches(':hover')) {
                    window.setTimeout(() => {
                        if (!search.contains(document.activeElement)) {
                            setSearchOpen(false);
                        }
                    }, 0);
                }
            });

            if (reduceMotion) {
                search.classList.add('reduce-motion');
            }
        });

        // ===== SCROLL REVEAL =====
        function revealOnScroll() {
            const reveals = document.querySelectorAll('.reveal');
            reveals.forEach(el => {
                const windowHeight = window.innerHeight;
                const elementTop = el.getBoundingClientRect().top;
                const revealPoint = 100;
                if (elementTop < windowHeight - revealPoint) {
                    el.classList.add('visible');
                }
            });
        }

        window.addEventListener('scroll', revealOnScroll);
        window.addEventListener('load', revealOnScroll);

        // ===== LIVE WIRE RE-RENDER: RE-APPLY REVEAL =====
        // Livewire re-renders strip the dynamically-added `visible` class from
        // `.reveal` elements, which would make content flash/“reload” after every
        // cart/wishlist toggle. Re-run the reveal pass after each DOM morph so
        // elements already on screen stay visible immediately.
        let revealReapplyRaf = null;
        const reapplyRevealOnLivewire = () => {
            if (revealReapplyRaf !== null) return;
            revealReapplyRaf = requestAnimationFrame(() => {
                revealReapplyRaf = null;
                revealOnScroll();
            });
        };
        if (window.Livewire && typeof window.Livewire.hook === 'function') {
            try {
                Livewire.hook('morph.updated', reapplyRevealOnLivewire);
            } catch (e) { /* noop */ }
        }

        // ===== TOAST =====
        function showToast(message, bgColor = '#161d34') {
            const toast = document.getElementById('toast');
            toast.textContent = message;
            toast.style.background = bgColor;
            toast.classList.add('show');
            setTimeout(() => {
                toast.classList.remove('show');
            }, 2500);
        }

        window.addEventListener('showToast', event => {
            const msg = typeof event.detail === 'string' ? event.detail : (event.detail.message || (event.detail[0] && event.detail[0].message) || 'Success');
            showToast(msg);
        });

        // ===== CART / WISHLIST HEADER BADGE UPDATES =====
        // addToCart/toggleWishlist/toggleCart already re-render the product cards
        // via Livewire, so here we only keep the nav badges in sync — we must
        // never trigger a full page reload.
        function updateNavBadge(link, count) {
            if (!link) return;
            let badge = link.querySelector('.badge-jg');
            if (count > 0) {
                if (!badge) {
                    badge = document.createElement('span');
                    badge.className = 'badge-jg';
                    link.appendChild(badge);
                }
                badge.textContent = count;
            } else if (badge) {
                badge.remove();
            }
        }

        function updateMobileMenuBadge(routePart, count) {
            document.querySelectorAll('.mobile-menu-text-link').forEach((link) => {
                if (link.getAttribute('href') && link.getAttribute('href').includes(routePart)) {
                    updateNavBadge(link, count);
                }
            });
        }

        window.addEventListener('cartUpdated', (event) => {
            const detail = event.detail || {};
            const count = typeof detail.count === 'number' ? detail.count : null;
            if (count === null) return;
            updateNavBadge(document.querySelector('.nav-right a[title="Cart"]'), count);
            updateMobileMenuBadge('/cart', count);
        });

        window.addEventListener('wishlistUpdated', (event) => {
            const detail = event.detail || {};
            const count = typeof detail.count === 'number' ? detail.count : null;
            if (count === null) return;
            updateNavBadge(document.querySelector('.nav-right a[title="Wishlist"]'), count);
            updateMobileMenuBadge('/wishlist', count);
        });

        // ===== QUANTITY STEPPER =====
        document.addEventListener('click', function(e) {
            const btn = e.target.closest('.qty-stepper button');
            if (btn) {
                const stepper = btn.closest('.qty-stepper');
                const span = stepper.querySelector('span');
                let val = parseInt(span.textContent);
                if (btn.classList.contains('minus') && val > 1) {
                    val--;
                } else if (btn.classList.contains('plus')) {
                    val++;
                }
                span.textContent = val;
            }
        });

        // ===== SIZE SELECTOR =====
        document.addEventListener('click', function(e) {
            const btn = e.target.closest('.size-btn');
            if (btn) {
                const parent = btn.closest('.size-selector');
                parent.querySelectorAll('.size-btn').forEach(b => b.classList.remove('active'));
                btn.classList.add('active');
            }
        });

        // ===== TABS =====
        document.addEventListener('click', function(e) {
            const tab = e.target.closest('.tab-jg');
            if (tab) {
                const parent = tab.closest('.tabs-jg');
                parent.querySelectorAll('.tab-jg').forEach(t => t.classList.remove('active'));
                tab.classList.add('active');
                const panels = tab.closest('.tab-container').querySelectorAll('.tab-panel');
                panels.forEach(p => p.classList.remove('active'));
                const target = tab.getAttribute('data-tab');
                if (target) {
                    document.getElementById(target).classList.add('active');
                }
            }
        });

        // ===== THUMBNAIL SWAP =====
        document.addEventListener('click', function(e) {
            const thumb = e.target.closest('.pd-thumbs .thumb');
            if (thumb) {
                const mainImg = thumb.closest('.pd-images').querySelector('.main-img img');
                mainImg.src = thumb.querySelector('img').src;
                thumb.closest('.pd-thumbs').querySelectorAll('.thumb').forEach(t => t.classList.remove('active'));
                thumb.classList.add('active');
            }
        });

        // ===== ACCOUNT TABS =====
        document.addEventListener('click', function(e) {
            const link = e.target.closest('.account-nav a');
            if (link) {
                e.preventDefault();
                const parent = link.closest('.account-nav');
                parent.querySelectorAll('a').forEach(a => a.classList.remove('active'));
                link.classList.add('active');
                const target = link.getAttribute('data-tab');
                if (target) {
                    const content = document.getElementById('accountContent');
                    if (content) {
                        content.querySelectorAll('.tab-panel').forEach(p => p.classList.remove('active'));
                        document.getElementById(target).classList.add('active');
                    }
                }
            }
        });
    </script>

    <!-- ===== DISCOUNT POPUP MODAL ===== -->
    <style>
        .discount-modal-overlay {
            position: fixed;
            inset: 0;
            background: rgba(15, 12, 9, 0.78);
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.25rem;
            z-index: 99999;
            opacity: 0;
            visibility: hidden;
            pointer-events: none;
            transition: opacity 0.35s ease, visibility 0s linear 0.35s;
        }

        .discount-modal-overlay.is-active {
            opacity: 1;
            visibility: visible;
            pointer-events: auto;
            transition-delay: 0s;
        }

        .discount-modal-card {
            position: relative;
            width: min(500px, 94vw);
            background: linear-gradient(145deg, #27211b 0%, #3a2e24 60%, #1f1a16 100%);
            border: 1.5px solid rgba(204, 164, 59, 0.75);
            border-radius: 20px;
            padding: 2.5rem 2rem;
            text-align: center;
            color: #ffffff;
            box-shadow: 0 25px 60px rgba(0, 0, 0, 0.6), 0 0 35px rgba(204, 164, 59, 0.25);
            transform: scale(0.9) translateY(15px);
            transition: transform 0.35s cubic-bezier(0.34, 1.56, 0.64, 1);
            overflow: hidden;
        }

        .discount-modal-overlay.is-active .discount-modal-card {
            transform: scale(1) translateY(0);
        }

        .discount-modal-card::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(204, 164, 59, 0.15) 0%, transparent 65%);
            pointer-events: none;
        }

        .discount-modal-close {
            position: absolute;
            top: 1rem;
            right: 1rem;
            width: 36px;
            height: 36px;
            border-radius: 50%;
            border: 1px solid rgba(255, 255, 255, 0.2);
            background: rgba(255, 255, 255, 0.08);
            color: #f3e0ad;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
            cursor: pointer;
            transition: all 0.25s ease;
            z-index: 2;
        }

        .discount-modal-close:hover {
            background: #cca43b;
            border-color: #cca43b;
            color: #1f1a16;
            transform: rotate(90deg);
        }

        .discount-modal-badge {
            width: 82px;
            height: 82px;
            margin: 0 auto 1.25rem;
            border-radius: 50%;
            background: linear-gradient(135deg, #cca43b 0%, #997510 100%);
            border: 2px solid #ffe699;
            box-shadow: 0 0 25px rgba(204, 164, 59, 0.55);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            animation: pulseModalBadge 2.5s infinite ease-in-out;
        }

        .discount-modal-badge .num {
            font-family: 'Playfair Display', serif;
            font-size: 1.55rem;
            font-weight: 800;
            color: #ffffff;
            line-height: 1;
        }

        .discount-modal-badge .text {
            font-size: 0.6rem;
            font-weight: 700;
            letter-spacing: 0.14em;
            color: #fff6d6;
        }

        .discount-modal-card h2 {
            font-family: 'Playfair Display', serif;
            font-size: 1.85rem;
            color: #f9f3ea;
            margin-bottom: 0.6rem;
        }

        .discount-modal-card p {
            font-size: 0.92rem;
            line-height: 1.6;
            color: rgba(249, 243, 234, 0.85);
            margin-bottom: 1.75rem;
            max-width: 380px;
            margin-left: auto;
            margin-right: auto;
        }

        .discount-modal-btn {
            display: inline-block;
            width: 100%;
            padding: 0.95rem 1.5rem;
            background: linear-gradient(135deg, #cca43b 0%, #ad8a2e 100%);
            color: #1f1a16;
            font-weight: 700;
            font-size: 0.85rem;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            border-radius: 999px;
            border: none;
            cursor: pointer;
            box-shadow: 0 8px 24px rgba(204, 164, 59, 0.35);
            transition: all 0.3s ease;
            text-decoration: none;
        }

        .discount-modal-btn:hover {
            background: linear-gradient(135deg, #e6b83b 0%, #c49d33 100%);
            box-shadow: 0 12px 30px rgba(204, 164, 59, 0.55);
            transform: translateY(-2px);
            color: #1f1a16;
        }

        @keyframes pulseModalBadge {
            0%, 100% { transform: scale(1); box-shadow: 0 0 20px rgba(204, 164, 59, 0.4); }
            50% { transform: scale(1.08); box-shadow: 0 0 32px rgba(255, 215, 0, 0.75); }
        }
    </style>

    <div class="discount-modal-overlay" id="discountModalOverlay" role="dialog" aria-modal="true" aria-label="10% Discount Special Offer">
        <div class="discount-modal-card">
            <button class="discount-modal-close" id="discountModalClose" type="button" aria-label="Close modal">&times;</button>
            <div class="discount-modal-badge">
                <span class="num">10%</span>
                <span class="text">OFF</span>
            </div>
            <h2>Special 10% Celebration Discount</h2>
            <p>Welcome to Jaffna Gold! Enjoy an <strong>instant 10% discount</strong> automatically applied across our entire handcrafted 22K gold & sterling silver collection.</p>
            <button class="discount-modal-btn" id="discountModalAction" type="button">Claim 10% Discount & Shop Now</button>
        </div>
    </div>

    <script>
        (() => {
            const overlay = document.getElementById('discountModalOverlay');
            const closeBtn = document.getElementById('discountModalClose');
            const actionBtn = document.getElementById('discountModalAction');

            if (!overlay || !closeBtn) return;

            const modalSeen = sessionStorage.getItem('jg_discount_modal_seen');

            if (!modalSeen) {
                setTimeout(() => {
                    overlay.classList.add('is-active');
                }, 800);
            }

            const closeModal = () => {
                overlay.classList.remove('is-active');
                sessionStorage.setItem('jg_discount_modal_seen', 'true');
            };

            closeBtn.addEventListener('click', closeModal);

            if (actionBtn) {
                actionBtn.addEventListener('click', () => {
                    closeModal();
                    if (!window.location.pathname.includes('/shop')) {
                        window.location.href = "{{ route('shop') }}";
                    }
                });
            }

            overlay.addEventListener('click', (e) => {
                if (e.target === overlay) {
                    closeModal();
                }
            });

            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape' && overlay.classList.contains('is-active')) {
                    closeModal();
                }
            });
        })();
    </script>

    @livewireScripts
</body>
</html>
