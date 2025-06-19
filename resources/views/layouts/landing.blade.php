@php
    $profile = asset(Storage::url('upload/profile'));
    $settings = settings();
    $user = \App\Models\User::find(1);
    \App::setLocale($user->lang);
    $intervals = $subscriptions->pluck('interval')->unique()->values()->toArray();
    // Move 'Unlimited' to the end if it exists
    if (($idx = array_search('Unlimited', $intervals)) !== false) {
        unset($intervals[$idx]);
        $intervals[] = 'Unlimited';
    }
@endphp
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ env('APP_NAME') }}</title>

    <meta http-equiv="X-UA-Compatible" content="IE=edge" />

    <meta name="author" content="{{ !empty($settings['app_name']) ? $settings['app_name'] : env('APP_NAME') }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ !empty($settings['app_name']) ? $settings['app_name'] : env('APP_NAME') }} - @yield('page-title') </title>

    <meta name="title" content="{{ $settings['meta_seo_title'] }}">
    <meta name="keywords" content="{{ $settings['meta_seo_keyword'] }}">
    <meta name="description" content="{{ $settings['meta_seo_description'] }}">


    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ env('APP_URL') }}">
    <meta property="og:title" content="{{ $settings['meta_seo_title'] }}">
    <meta property="og:description" content="{{ $settings['meta_seo_description'] }}">
    <meta property="og:image" content="{{ asset(Storage::url('upload/seo')) . '/' . $settings['meta_seo_image'] }}">

    <meta property="twitter:card" content="summary_large_image">
    <meta property="twitter:url" content="{{ env('APP_URL') }}">
    <meta property="twitter:title" content="{{ $settings['meta_seo_title'] }}">
    <meta property="twitter:description" content="{{ $settings['meta_seo_description'] }}">
    <meta property="twitter:image"
        content="{{ asset(Storage::url('upload/seo')) . '/' . $settings['meta_seo_image'] }}">


    <link rel="icon" href="{{ asset(Storage::url('upload/logo')) . '/' . $settings['company_favicon'] }}"
        type="image/x-icon" />
    <link href="{{ asset('assets/css/plugins/animate.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('assets/css/plugins/swiper-bundle.css') }}" rel="stylesheet" />
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" />
    <link rel="stylesheet" href="{{ asset('assets/fonts/phosphor/duotone/style.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/fonts/tabler-icons.min.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/fonts/feather.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/fonts/fontawesome.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/fonts/material.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/css/owl.carousel.min.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}" id="main-style-link" />


    @if (!empty($settings['custom_color']) && $settings['color_type'] == 'custom')
        <link rel="stylesheet" id="Pstylesheet" href="{{ asset('assets/css/custom-color.css') }}" />
        <script src="{{ asset('js/theme-pre-color.js') }}"></script>
    @else
        <link rel="stylesheet" id="Pstylesheet" href="{{ asset('assets/css/style-preset.css') }}" />
    @endif


    <link rel="stylesheet" href="{{ asset('assets/css/landing.css') }}" />
    <link href="{{ asset('css/custom.css') }}" rel="stylesheet">
    <style>
        :root {
            --primary-color: #155263;
            --secondary-color: #0D47A1;
            --accent-color: #00BCD4;
            --text-color: #2C3E50;
            --light-bg: #F8FAFC;
            --card-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }

        body {
            font-family: 'Inter', sans-serif;
            color: var(--text-color);
            background: var(--light-bg);
        }

        .navbar {
            background: #fff !important;
            box-shadow: none !important;
            padding: 0.5rem 0 0.5rem 0 !important;
            border-bottom: none !important;
        }

        .navbar .container {
            align-items: center;
        }

        .navbar .navbar-brand {
            font-size:2.7rem;
            font-weight:700;
            letter-spacing:-1px;
            margin-right: 2rem;
        }

        .navbar .d-flex.align-items-center.gap-4 {
            gap: 2.2rem !important;
        }

        .navbar .nav-link {
            color: #222;
            font-weight: 500;
            font-size: 0.98rem;
            padding: 0.5rem 0.8rem;
            transition: color 0.2s;
        }

        .navbar .nav-link:hover {
            color: #1a7f5a;
        }

        .navbar .btn.btn-primary {
            background: #16263a !important;
            border: none;
            border-radius: 12px;
            font-weight: 700;
            padding: 0.45rem 1.1rem;
            font-size: 0.98rem;
            box-shadow: none;
            margin-left: 1.2rem;
        }

        .hero-section {
            padding: 120px 0 80px;
            background: #fff;
            position: relative;
            overflow: hidden;
        }

        .hero-content h1 {
            font-size: 3.5rem;
            font-weight: 700;
            color: var(--primary-color);
            margin-bottom: 1.5rem;
            line-height: 1.2;
        }

        .hero-content p {
            font-size: 1.25rem;
            color: var(--text-color);
            margin-bottom: 2rem;
            line-height: 1.6;
        }

        .feature-card {
            background: #ffffff;
            border-radius: 16px;
            padding: 2rem;
            transition: all 0.3s ease;
            height: 100%;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            border: 1px solid rgba(0, 0, 0, 0.05);
        }

        .feature-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }

        .feature-icon {
            background: rgba(26, 127, 90, 0.1);
            border-radius: 12px;
            width: 80px;
            height: 80px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            transition: all 0.3s ease;
        }

        .feature-card:hover .feature-icon {
            background: rgba(26, 127, 90, 0.15);
            transform: scale(1.1);
        }

        .feature-icon svg {
            transition: all 0.3s ease;
        }

        .feature-card:hover .feature-icon svg {
            transform: scale(1.1);
        }

        .feature-title {
            font-size: 1.25rem;
            font-weight: 600;
            margin-bottom: 1rem;
            color: var(--text-color);
        }

        .feature-text {
            color: #64748B;
            line-height: 1.6;
        }

        .stats-section {
            background: white;
            padding: 4rem 0;
            border-radius: 24px;
            margin: 4rem 0;
            box-shadow: var(--card-shadow);
        }

        .stat-card {
            text-align: center;
            padding: 2rem;
        }

        .stat-number {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--primary-color);
            margin-bottom: 0.5rem;
        }

        .stat-label {
            color: #64748B;
            font-weight: 500;
        }

        @media (max-width: 991.98px) {
            .hero-content h1 {
                font-size: 2.5rem;
            }
            
            .hero-section {
                padding: 80px 0 60px;
            }
        }

        @media (max-width: 767.98px) {
            .hero-content h1 {
                font-size: 2rem;
            }
            
            .feature-card {
                margin-bottom: 1.5rem;
            }
        }

        /* Testimonials Section */
        .testimonial-card {
            background: white;
            border-radius: 16px;
            padding: 2rem;
            height: 100%;
            transition: all 0.3s ease;
            border: 1px solid rgba(0, 0, 0, 0.1);
        }

        .testimonial-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--card-shadow);
        }

        .quote-icon {
            font-size: 2rem;
            color: var(--primary-color);
            opacity: 0.2;
        }

        .testimonial-text {
            font-size: 1.1rem;
            line-height: 1.6;
            color: var(--text-color);
            margin-bottom: 1.5rem;
        }

        .testimonial-author img {
            border: 2px solid var(--primary-color);
        }

        /* Why Choose Us Section */
        .choose-card {
            background: white;
            border-radius: 16px;
            padding: 2rem;
            height: 100%;
            transition: all 0.3s ease;
            border: 1px solid rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        .choose-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--card-shadow);
        }

        .choose-icon {
            width: 80px;
            height: 80px;
            background: var(--light-bg);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
        }

        .choose-icon i {
            font-size: 2rem;
            color: var(--primary-color);
        }

        .choose-title {
            font-size: 1.25rem;
            font-weight: 600;
            margin-bottom: 1rem;
            color: var(--text-color);
        }

        .choose-text {
            color: #64748B;
            line-height: 1.6;
            margin-bottom: 0;
        }

        /* Footer Styles */
        .footer {
            background: #1a1f36;
        }

        .footer-brand img {
            filter: brightness(0) invert(1);
        }

        .footer-links li {
            margin-bottom: 0.75rem;
        }

        .footer-links a {
            color: rgba(255, 255, 255, 0.7);
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .footer-links a:hover {
            color: white;
        }

        .social-links a {
            font-size: 1.25rem;
            transition: opacity 0.3s ease;
        }

        .social-links a:hover {
            opacity: 0.8;
        }

        .text-light-50 {
            color: rgba(255, 255, 255, 0.5);
        }

        .border-light-50 {
            border-color: rgba(255, 255, 255, 0.1);
        }

        @media (max-width: 767.98px) {
            .testimonial-card,
            .choose-card {
                margin-bottom: 1.5rem;
            }
        }

        .modern-hero-section {
            background: linear-gradient(135deg, #f8fafc 60%, #e0f7fa 100%);
            border-radius: 0 0 2rem 2rem;
        }
        .text-gradient {
            background: linear-gradient(90deg, #155263, #00bcd4);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            text-fill-color: transparent;
        }
        .modern-hero-img img {
            max-width: 90%;
            border-radius: 2rem;
            box-shadow: 0 8px 32px rgba(21,82,99,0.08);
        }
        .empower-section {
            background: linear-gradient(120deg, #e0f7fa 0%, #fff 100%);
            border-radius: 2rem;
            margin-bottom: 2rem;
        }
        .empower-section h2 {
            font-size: 2.2rem;
        }
        .empower-section ul li {
            font-size: 1.1rem;
            font-weight: 500;
        }
        .modern-offers-section {
            padding: 6rem 0;
            background: linear-gradient(180deg, #fff 0%, var(--light-bg) 100%);
        }
        .modern-offers-section h2 {
            font-size: 2.5rem;
            margin-bottom: 1rem;
        }
        .modern-offers-section .lead {
            font-size: 1.2rem;
            line-height: 1.8;
            color: #64748b;
            max-width: 800px;
            margin: 0 auto;
        }
        .modern-offers-section h3 {
            color: #1e293b;
            font-size: 1.5rem;
            margin: 1rem 0;
        }
        .modern-offers-section .text-muted {
            font-size: 1rem;
            line-height: 1.6;
            color: #64748b !important;
        }
        .modern-offers-section img {
            max-height: 90px;
            object-fit: contain;
        }
        .simple-hero-section {
            padding-top: 180px !important;
            background: linear-gradient(120deg, #f8fafc 60%, #e0f7fa 100%);
            border-radius: 0 0 2rem 2rem;
        }
        .simple-hero-section h1 {
            letter-spacing: -1px;
        }
        .simple-hero-section .btn-primary {
            font-size: 1.1rem;
            border-radius: 8px;
        }
        .simple-hero-section img[alt='dashboard preview'] {
            margin-top: 2rem;
            box-shadow: 0 4px 24px rgba(21,82,99,0.08);
        }
        .spacer-navbar { display: none !important; }
        .interval-tab-outer {
            width: 100%;
            margin-bottom: 2rem;
        }
        .interval-tab-container.card {
            border-radius: 18px;
            box-shadow: 0 2px 16px rgba(25, 118, 210, 0.07);
            background: #fff;
            border: 1.5px solid #e3eafc;
        }
        .price-card {
            background: #fff;
            border-radius: 1.5rem;
            box-shadow: 0 4px 24px rgba(21,82,99,0.10);
            border: 1.5px solid #e3eafc;
            transition: box-shadow 0.2s, transform 0.2s;
            margin-bottom: 2rem;
        }
        .price-card:hover {
            box-shadow: 0 8px 32px rgba(21,82,99,0.13);
            transform: translateY(-6px) scale(1.03);
        }
        .price-card .card-body {
            padding: 2.5rem 1.5rem;
        }
        .price-card h2 {
            font-size: 2rem;
            font-weight: 700;
            color: #16263a;
            margin-bottom: 0.5rem;
        }
        .price-price {
            font-size: 2.5rem;
            font-weight: 700;
            color: #155263;
            margin-bottom: 1.2rem;
        }
        .price-card ul {
            margin-bottom: 1.5rem;
        }
        .price-card ul li {
            font-size: 1.08rem;
            color: #475569;
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .price-card ul li i {
            margin-right: 8px;
            font-size: 1.1em;
        }
        .price-card .btn {
            border-radius: 0.7rem;
            font-weight: 600;
            font-size: 1.1rem;
            padding: 0.7rem 0;
        }
        @media (max-width: 991.98px) {
            .price-card .card-body {
                padding: 1.5rem 0.7rem;
            }
            .price-card {
                margin-bottom: 1.2rem;
            }
        }
        .hero-section-clean {
            min-height: calc(100vh - 72px);
            display: flex;
            align-items: center;
            padding: 24px 0 0 0;
            background: #fff;
            position: relative;
        }
        .hero-row-clean {
            min-height: 70vh;
            display: flex;
            align-items: stretch;
        }
        .hero-text-col {
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding-left: 2.5rem;
            padding-right: 2.5rem;
        }
        .hero-text-col h1 {
            font-size: 3.5rem;
            font-weight: 800;
            color: #16263a;
            margin-bottom: 1.5rem;
            letter-spacing: -1px;
            line-height: 1.1;
        }
        .hero-text-col p {
            font-size: 1.25rem;
            color: #222;
            margin-bottom: 2.2rem;
            line-height: 1.6;
        }
        .hero-img-col {
            display: flex;
            align-items: flex-end;
            justify-content: flex-end;
            padding: 0;
        }
        .hero-img-clean {
            width: 100%;
            max-width: none;
            height: 80vh;
            min-height: 400px;
            object-fit: cover;
            border-radius: 0;
            box-shadow: none;
            background: #fff;
            margin-right: 0;
            margin-top: 32px;
            position: relative;
            z-index: 2;
            clip-path: polygon(0 0, 100% 0, 100% 90%, 0 100%);
            -webkit-mask-image: linear-gradient(to right, transparent 0%, #000 10%, #000 90%, transparent 100%), linear-gradient(to top, transparent 0%, #000 15%, #000 100%), linear-gradient(to bottom, transparent 0%, #000 15%, #000 100%);
            mask-image: linear-gradient(to right, transparent 0%, #000 10%, #000 90%, transparent 100%), linear-gradient(to top, transparent 0%, #000 15%, #000 100%), linear-gradient(to bottom, transparent 0%, #000 15%, #000 100%);
            -webkit-mask-composite: intersect;
            mask-composite: intersect;
        }
        .hero-section-clean::after {
            display: none;
        }
        @media (max-width: 991.98px) {
            .hero-section-clean {
                min-height: unset;
                padding: 40px 0 20px;
            }
            .hero-row-clean {
                min-height: unset;
                display: block;
            }
            .hero-text-col {
                padding: 1.2rem;
            }
            .hero-img-col {
                justify-content: center;
                align-items: center;
                padding: 0;
            }
            .hero-img-clean {
                height: 220px;
                min-height: 120px;
                max-width: 100%;
                margin: 0 auto;
                display: block;
                border-radius: 0;
            }
        }
        .footer-logo-white {
            filter: brightness(0) invert(1) !important;
        }
        .footer-social {
            display: flex;
            justify-content: center;
            gap: 1.2rem;
            margin-bottom: 1rem;
        }
        .footer-social-link {
            color: #fff;
            font-size: 1.35rem;
            transition: color 0.2s, transform 0.2s;
            opacity: 0.85;
        }
        .footer-social-link:hover {
            color: #1a7f5a;
            opacity: 1;
            transform: translateY(-2px) scale(1.1);
        }
        .footer-flex {
            gap: 2.5rem !important;
            flex-wrap: wrap;
        }
        .footer-logo-white {
            filter: brightness(0) invert(1) !important;
        }
        .footer-social-link {
            color: #fff;
            font-size: 1.35rem;
            transition: color 0.2s, transform 0.2s;
            opacity: 0.85;
        }
        .footer-social-link:hover {
            color: #1a7f5a;
            opacity: 1;
            transform: translateY(-2px) scale(1.1);
        }
        @media (max-width: 767.98px) {
            .footer-flex {
                flex-direction: column !important;
                gap: 1rem !important;
                text-align: center;
            }
        }
        .footer-modern {
            background: #f8fafc;
            border-top: 2px solid #00bcd4;
            padding: 2.2rem 0 1.2rem 0;
            text-align: center;
        }
        .footer-modern .footer-brand {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.7rem;
            margin-bottom: 1.2rem;
        }
        .footer-modern .footer-brand img {
            height: 36px;
            width: auto;
            filter: none;
        }
        .footer-modern .footer-links {
            display: flex;
            justify-content: center;
            gap: 2rem;
            margin-bottom: 1.2rem;
            list-style: none;
            padding: 0;
        }
        .footer-modern .footer-links a {
            color: #155263;
            font-weight: 600;
            text-decoration: none;
            transition: color 0.2s;
        }
        .footer-modern .footer-links a:hover {
            color: #00bcd4;
        }
        .footer-modern .footer-social {
            display: flex;
            justify-content: center;
            gap: 1.2rem;
            margin-bottom: 1.2rem;
        }
        .footer-modern .footer-social-link {
            color: #155263;
            font-size: 1.35rem;
            transition: color 0.2s, transform 0.2s;
            opacity: 0.85;
        }
        .footer-modern .footer-social-link:hover {
            color: #00bcd4;
            opacity: 1;
            transform: translateY(-2px) scale(1.1);
        }
        .footer-modern .footer-copyright {
            color: #64748b;
            font-size: 0.98rem;
            margin-top: 0.5rem;
        }
        .custom-swiper-nav {
            position: absolute;
            top: 50%;
            z-index: 10;
            width: 48px;
            height: 48px;
            background: #16263a;
            color: #fff;
            border-radius: 50%;
            box-shadow: 0 4px 16px rgba(0,0,0,0.12);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            cursor: pointer;
            opacity: 0.2;
            transition: opacity 0.2s, background 0.2s, color 0.2s, transform 0.2s;
        }
        .swiper-button-prev.custom-swiper-nav {
            left: -70px;
        }
        .swiper-button-next.custom-swiper-nav {
            right: -70px;
        }
        .custom-swiper-nav:after {
            font-family: 'Font Awesome 5 Free', 'FontAwesome';
            font-weight: 900;
            font-size: 2rem;
        }
        .swiper-button-prev.custom-swiper-nav:after {
            content: '\f053'; /* FontAwesome left arrow */
        }
        .swiper-button-next.custom-swiper-nav:after {
            content: '\f054'; /* FontAwesome right arrow */
        }
        .custom-swiper-nav:hover,
        .custom-swiper-nav:focus,
        .custom-swiper-nav:active {
            opacity: 1;
        }
        @media (max-width: 991.98px) {
            .swiper-button-prev.custom-swiper-nav {
                left: -20px;
            }
            .swiper-button-next.custom-swiper-nav {
                right: -20px;
            }
            .custom-swiper-nav {
                width: 38px;
                height: 38px;
                font-size: 1.3rem;
            }
        }
        header:after {
            display: none !important;
            background: none !important;
            content: none !important;
        }
        .footer-oneline {
            background: #f8fafc;
            padding: 1.1rem 2.5rem 1.1rem 2.5rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1.2rem;
            font-size: 1rem;
            color: #155263;
            box-shadow: 0 -4px 24px rgba(21,82,99,0.08);
        }
        .footer-oneline .footer-brand {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-weight: 700;
            font-size: 1.1rem;
            color: #155263;
        }
        .footer-oneline .footer-brand img {
            height: 28px;
            width: auto;
        }
        .footer-oneline .footer-social {
            display: flex;
            gap: 1rem;
        }
        .footer-oneline .footer-social-link {
            color: #155263;
            font-size: 1.15rem;
            transition: color 0.2s, transform 0.2s;
            opacity: 0.85;
        }
        .footer-oneline .footer-social-link:hover {
            color: #00bcd4;
            opacity: 1;
            transform: translateY(-2px) scale(1.1);
        }
        .footer-oneline .footer-copyright {
            color: #64748b;
            font-size: 0.97rem;
            margin-left: 1.2rem;
            white-space: nowrap;
        }
        @media (max-width: 991.98px) {
            .footer-oneline {
                flex-direction: column;
                gap: 0.7rem;
                font-size: 0.98rem;
                text-align: center;
                padding: 1.1rem 1rem 1.1rem 1rem;
            }
            .footer-oneline .footer-copyright {
                margin-left: 0;
            }
        }
    </style>
</head>

<body class="landing-page" data-pc-preset="{{ !empty($settings['color_type']) && $settings['color_type'] == 'custom' ? 'custom' : $settings['accent_color'] }}" data-pc-sidebar-theme="light"
    data-pc-sidebar-caption="{{ $settings['sidebar_caption'] }}" data-pc-direction="{{ $settings['theme_layout'] }}"
    data-pc-theme="{{ $settings['theme_mode'] }}">

<!-- New Nav/Header from screenshot -->
<nav class="navbar navbar-expand-lg fixed-top" style="background: #fff; box-shadow: 0 1px 3px rgba(0,0,0,0.05); height: 80px; display: flex; align-items: center;">
  <div class="container" style="max-width: 1320px; padding-left: 40px; padding-right: 40px;">
    <a class="navbar-brand" href="#" style="padding: 0;"><img src="{{ asset(Storage::url('upload/logo/landing_logo.png')) }}" alt="logo" class="img-fluid" style="height:120px; margin-top: -20px; margin-bottom: -20px;" /></a>
    <div class="d-flex align-items-center gap-4">
      <a class="nav-link" href="#features">Features</a>
      <a class="nav-link" href="#pricing">Pricing</a>
      <a class="nav-link" href="{{ route('login') }}">Login</a>
      <a class="btn btn-primary" href="{{ route('register') }}" style="background:#16263a;border:none;">Get Started</a>
    </div>
  </div>
</nav>
<div class="spacer-navbar"></div>
<!-- Hero Section -->
<header id="home" class="hero-section hero-section-clean">
  <div class="container" style="max-width: 1320px; padding-left: 40px;">
    <div class="row hero-row-clean">
      <div class="col-lg-6 hero-text-col" style="padding-top: 40px;">
        <h1 class="display-4 fw-bold mb-4">Welcome to <span style="color:#1a7f5a;">Nest</span></h1>
        <p class="lead" style="font-size: 1.25rem; line-height: 1.8; color: #4a5568; max-width: 600px; margin-bottom: 2rem;">Nest is the all-in-one property management software designed to simplify your workflow and boost your profits. Easily manage tenants, track maintenance requests, view profit & loss reports, and much more — all in one powerful platform.</p>
        <div class="d-flex gap-3">
          <a href="{{ route('login') }}" class="btn btn-primary" style="background:#16263a;border:none;min-width:120px;">Log in</a>
          <a href="{{ route('register') }}" class="btn btn-outline-secondary" style="min-width:120px;">Get Started</a>
        </div>
      </div>
      <div class="col-lg-6 hero-img-col p-0">
        <img src="assets/images/landing/building.png" alt="Modern apartment building" class="hero-img-clean">
      </div>
    </div>
  </div>
</header>
<!-- End Hero -->

<!-- What Our Software Offers (Section 4) -->
    @php
        $Section_4 = App\Models\HomePage::where('section', 'Section 4')->first();
        $Section_4_content_value = !empty($Section_4->content_value)
            ? json_decode($Section_4->content_value, true)
            : [];
    @endphp
    @if (empty($Section_4_content_value['section_enabled']) || $Section_4_content_value['section_enabled'] == 'active')
        <section class="modern-offers-section py-5">
            <div class="container">
                <div class="row justify-content-center mb-5">
                    <div class="col-md-9 col-lg-7 text-center">
                        <h2 class="display-4 fw-bold mb-3" style="background: linear-gradient(120deg, #155263, #1a7f5a); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">What Our Software Offers</h2>
                        <p class="lead text-muted">Discover the powerful features that make Nest the perfect solution for your property management needs.</p>
                    </div>
                </div>
                <div class="row g-4 text-center">
                    @php $is4_check = 0; @endphp
                    @if ($is4_check == 0)
                        <div class="col-md-6 col-xl-4">
                            <div class="feature-card">
                                <div class="feature-icon">
                                    <svg width="64" height="64" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M12 4.5V7.5M12 7.5L9 5.5M12 7.5L15 5.5M3 19H21M5 16H19C19.5523 16 20 15.5523 20 15V9C20 8.44772 19.5523 8 19 8H5C4.44772 8 4 8.44772 4 9V15C4 15.5523 4.44772 16 5 16Z" stroke="#1a7f5a" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                </div>
                                <h3 class="fw-semibold mb-2">Dual Language Support</h3>
                                <p class="text-muted">Use Nest in both English and Amharic, making it simple and accessible for all users.</p>
                            </div>
                        </div>
                        <div class="col-md-6 col-xl-4">
                            <div class="feature-card">
                                <div class="feature-icon">
                                    <svg width="64" height="64" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M17 21V19C17 17.9391 16.5786 16.9217 15.8284 16.1716C15.0783 15.4214 14.0609 15 13 15H5C3.93913 15 2.92172 15.4214 2.17157 16.1716C1.42143 16.9217 1 17.9391 1 19V21M23 21V19C22.9993 18.1137 22.7044 17.2528 22.1614 16.5523C21.6184 15.8519 20.8581 15.3516 20 15.13M16 3.13C16.8604 3.3503 17.623 3.8507 18.1676 4.55231C18.7122 5.25392 19.0078 6.11683 19.0078 7.005C19.0078 7.89317 18.7122 8.75608 18.1676 9.45769C17.623 10.1593 16.8604 10.6597 16 10.88M13 7C13 9.20914 11.2091 11 9 11C6.79086 11 5 9.20914 5 7C5 4.79086 6.79086 3 9 3C11.2091 3 13 4.79086 13 7Z" stroke="#1a7f5a" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                </div>
                                <h3 class="fw-semibold mb-2">Tenant Management</h3>
                                <p class="text-muted">Easily create or remove tenants at any time for better communication and streamlined operations.</p>
                            </div>
                        </div>
                        <div class="col-md-6 col-xl-4">
                            <div class="feature-card">
                                <div class="feature-icon">
                                    <svg width="64" height="64" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z" stroke="#1a7f5a" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                </div>
                                <h3 class="fw-semibold mb-2">Maintenance Requests</h3>
                                <p class="text-muted">Tenants can conveniently submit maintenance requests from home and connect directly with building managers.</p>
                            </div>
                        </div>
                        <div class="col-md-6 col-xl-4">
                            <div class="feature-card">
                                <div class="feature-icon">
                                    <svg width="64" height="64" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M12 1V23M17 5H9.5C8.57174 5 7.6815 5.36875 7.02513 6.02513C6.36875 6.6815 6 7.57174 6 8.5C6 9.42826 6.36875 10.3185 7.02513 10.9749C7.6815 11.6313 8.57174 12 9.5 12H14.5C15.4283 12 16.3185 12.3687 16.9749 13.0251C17.6313 13.6815 18 14.5717 18 15.5C18 16.4283 17.6313 17.3185 16.9749 17.9749C16.3185 18.6313 15.4283 19 14.5 19H6" stroke="#1a7f5a" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                </div>
                                <h3 class="fw-semibold mb-2">Financial Management</h3>
                                <p class="text-muted">A hassle-free finance system that helps track expenses, profits, and losses — all in one place.</p>
                            </div>
                        </div>
                        <div class="col-md-6 col-xl-4">
                            <div class="feature-card">
                                <div class="feature-icon">
                                    <svg width="64" height="64" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M4 3H20C20.5523 3 21 3.44772 21 4V20C21 20.5523 20.5523 21 20 21H4C3.44772 21 3 20.5523 3 20V4C3 3.44772 3.44772 3 4 3ZM7.5 7V17M12 7V17M16.5 7V17" stroke="#1a7f5a" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                </div>
                                <h3 class="fw-semibold mb-2">In-App Payments</h3>
                                <p class="text-muted">Tenants can make payments directly through the app, ensuring fast, easy, and trackable transactions.</p>
                            </div>
                        </div>
                        <div class="col-md-6 col-xl-4">
                            <div class="feature-card">
                                <div class="feature-icon">
                                    <svg width="64" height="64" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M18 20V10M12 20V4M6 20V14" stroke="#1a7f5a" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                </div>
                                <h3 class="fw-semibold mb-2">Real-Time Data Insights</h3>
                                <p class="text-muted">Access live data on tenant activity, finances, and building operations to make smarter decisions.</p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </section>
    @endif

<!-- Pricing (Section 5) -->
    @php
        $Section_5 = App\Models\HomePage::where('section', 'Section 5')->first();
        $Section_5_content_value = !empty($Section_5->content_value)
            ? json_decode($Section_5->content_value, true)
            : [];
    @endphp
    @if ($settings['pricing_feature'] == 'on')
        @if (empty($Section_5_content_value['section_enabled']) || $Section_5_content_value['section_enabled'] == 'active')
            <section class="modern-offers-section" id="pricing">
                <div class="container">
                    <div class="row justify-content-center title">
                        <div class="col-md-9 col-lg-6 text-center">
                            <h2 class="h1">
                                {{ !empty($Section_5_content_value['Sec5_title']) ? $Section_5_content_value['Sec5_title'] : 'Flexible Pricing' }}
                            </h2>
                            <p class="text-lg">
                                {{ !empty($Section_5_content_value['Sec5_info']) ? $Section_5_content_value['Sec5_info'] : 'Get started for free, upgrade later in our application.' }}
                            </p>
                        </div>
                    </div>
                    <div class="interval-tab-outer mb-4 d-flex justify-content-center">
                        <div class="interval-tab-container card shadow-sm px-4 py-3 bg-white rounded-4" style="max-width: 420px;">
                            <ul class="nav nav-tabs justify-content-center" id="intervalTabs" role="tablist">
                                @foreach ($intervals as $idx => $interval)
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link @if($idx === 0) active @endif" id="tab-{{ $interval }}" data-bs-toggle="tab" data-bs-target="#interval-{{ $interval }}" type="button" role="tab" aria-controls="interval-{{ $interval }}" aria-selected="{{ $idx === 0 ? 'true' : 'false' }}">
                                            {{ __($interval) }}
                                        </button>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                    <div class="tab-content" id="intervalTabsContent">
                        @foreach ($intervals as $idx => $interval)
                            <div class="tab-pane fade @if($idx === 0) show active @endif" id="interval-{{ $interval }}" role="tabpanel" aria-labelledby="tab-{{ $interval }}">
                                <div class="row justify-content-center">
                                    @foreach ($subscriptions->where('interval', $interval) as $subscription)
                                        <div class="col-md-6 col-lg-4">
                                            <div class="card price-card text-center">
                                                <div class="card-body">
                                                    <h2 class="">{{ $subscription->title }}</h2>
                                                    <div class="price-price mt-4">
                                                        <sup>{{ subscriptionPaymentSettings()['CURRENCY_SYMBOL'] }}</sup>
                                                        {{ $subscription->package_amount }}
                                                        <span>/{{ $subscription->interval }}</span>
                                                    </div>
                                                    <ul class="list-unstyled text-center mb-4" style="margin: 0 auto;">
                                                        <li class="mb-2"><i class="ti ti-circle-check text-success me-2"></i>{{ __('User Limit') }}: {{ $subscription->user_limit }}</li>
                                                        <li class="mb-2"><i class="ti ti-circle-check text-success me-2"></i>{{ __('Property Limit') }}: {{ $subscription->property_limit }}</li>
                                                        <li class="mb-2"><i class="ti ti-circle-check text-success me-2"></i>{{ __('Tenant Limit') }}: {{ $subscription->tenant_limit }}</li>
                                                        <li class="mb-2"><i class="ti ti-circle-check text-success me-2"></i>{{ __('Unit Range') }}: {{ $subscription->min_units }} - {{ $subscription->max_units == 0 ? 'Unlimited' : $subscription->max_units }}</li>
                                                    </ul>
                                                    <div class="mt-4">
                                                        <a href="{{ route('register') }}" class="btn btn-primary w-100">{{ __('Get Started') }}</a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                    <!-- Enterprise Card for this interval -->
                                    <div class="col-md-6 col-lg-4">
                                        <div class="card price-card text-center">
                                            <div class="card-body">
                                                <h2 class="">{{ __('Enterprise') }}</h2>
                                                <div class="price-price mt-4">
                                                    <span>{{ __('Contact us for pricing') }}</span>
                                                </div>
                                                <ul class="list-unstyled text-center mb-4" style="margin: 0 auto;">
                                                    <li><i class="ti ti-circle-check text-success me-2"></i>{{ __('Custom User Limit') }}</li>
                                                    <li><i class="ti ti-circle-check text-success me-2"></i>{{ __('Custom Property Limit') }}</li>
                                                    <li><i class="ti ti-circle-check text-success me-2"></i>{{ __('Custom Tenant Limit') }}</li>
                                                    <li><i class="ti ti-circle-check text-success me-2"></i>{{ __('Custom Unit Range') }}</li>
                                                    <li><i class="ti ti-circle-check text-success me-2"></i>{{ __('Priority Support') }}</li>
                                                    <li><i class="ti ti-circle-check text-success me-2"></i>{{ __('Custom Features') }}</li>
                                                </ul>
                                                <div class="mt-4">
                                                    <button type="button" class="btn btn-primary w-100" data-bs-toggle="modal" data-bs-target="#enterpriseContactModal">
                                                        {{ __('Contact Us') }}
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </section>
        @endif
    @endif

<!-- Core Features (Section 6) -->
@php
    $Section_6 = App\Models\HomePage::where('section', 'Section 6')->first();
    $Section_6_content_value = !empty($Section_6->content_value)
        ? json_decode($Section_6->content_value, true)
        : [];
@endphp
@if (empty($Section_6_content_value['section_enabled']) || $Section_6_content_value['section_enabled'] == 'active')
    <section class="application-slider" id="features">
        <div class="container position-relative">
            <div class="row justify-content-center title">
                <div class="col-md-9 col-lg-6 text-center">
                    <h2 class="h1">
                        {{ !empty($Section_6_content_value['Sec6_title']) ? $Section_6_content_value['Sec6_title'] : 'Explore Concenputal Apps' }}
                    </h2>
                    <p class="text-lg">
                        {{ !empty($Section_6_content_value['Sec6_info']) ? $Section_6_content_value['Sec6_info'] : 'Smartweb has conceptul working apps like Chat, Inbox, E-commerce, Invoice, Kanban, and Calendar' }}
                    </p>
                </div>
            </div>
            <div class="row text-center justify-content-center">
                <div class="col-11 col-md-9 col-lg-7 position-relative">
                    <div class="swiper app-slider">
                        <div class="swiper-wrapper">
                            @if (!empty($Section_6_content_value['Sec6_Box_title']))
                                @foreach ($Section_6_content_value['Sec6_Box_title'] as $s6_key => $s6_item)
                                    <div class="swiper-slide">
                                        @if (!empty($Section_6_content_value['Sec6_box' . $s6_key . '_image_path']))
                                            <img src="{{ asset(Storage::url($Section_6_content_value['Sec6_box' . $s6_key . '_image_path'])) }}"
                                                alt="img" class="img-fluid" />
                                        @else
                                            <img src="assets/images/landing/slider-light-1.png" alt="images"
                                                class="img-fluid" />
                                        @endif
                                        <h3> {{ $s6_item }} <i class="ti ti-link"></i> </h3>
                                        <p>{{ $Section_6_content_value['Sec6_Box_subtitle'][$s6_key] }}</p>
                                    </div>
                                @endforeach
                            @else
                                <div class="swiper-slide">
                                    <img src="assets/images/landing/slider-light-1.png" alt="images"
                                        class="img-fluid" />
                                    <h3>Social Profile <i class="ti ti-link"></i></h3>
                                    <p>Complete Social profile with all possible option</p>
                                </div>
                            @endif
                        </div>
                    </div>
                    <!-- Swiper navigation buttons, custom styled and at page edge -->
                    <div class="custom-swiper-nav swiper-button-prev"></div>
                    <div class="custom-swiper-nav swiper-button-next"></div>
                </div>
            </div>
        </div>
    </section>
@endif

    <!-- Testimonials Section -->
    <!-- [ section ] start -->
    <!-- [ footer ] start -->
    <footer class="footer-oneline">
        <div class="footer-brand">
            <img src="{{ asset(Storage::url('upload/logo/landing_logo.png')) }}" alt="Nest Logo" />
            Yene Bet
        </div>
        <div class="footer-social">
            <a href="#" class="footer-social-link"><i class="ti ti-brand-facebook"></i></a>
            <a href="#" class="footer-social-link"><i class="ti ti-brand-twitter"></i></a>
            <a href="#" class="footer-social-link"><i class="ti ti-brand-linkedin"></i></a>
            <a href="#" class="footer-social-link"><i class="ti ti-brand-instagram"></i></a>
        </div>
        <div class="footer-copyright">
            &copy; {{ date('Y') }} Yene Bet. All rights reserved.
        </div>
    </footer>
    <!-- [ footer ] End -->
    <!-- Required Js -->
    <script src="{{ asset('js/jquery.js') }}"></script>
    <script src="{{ asset('assets/js/plugins/popper.min.js') }}"></script>
    <script src="{{ asset('assets/js/plugins/simplebar.min.js') }}"></script>
    <script src="{{ asset('assets/js/fonts/custom-font.js') }}"></script>
    <script src="{{ asset('assets/js/pcoded.js') }}"></script>
    <script src="{{ asset('assets/js/plugins/feather.min.js') }}"></script>

    <script>
        font_change('Roboto');
    </script>

    <!-- [Page Specific JS] start -->
    <script src="{{ asset('assets/js/plugins/wow.min.js') }}"></script>
    <script src="{{ asset('assets/js/plugins/swiper-bundle.js') }}"></script>
    <script>
        // Start [ Menu hide/show on scroll ]
        let ost = 0;
        document.addEventListener('scroll', function() {
            let cOst = document.documentElement.scrollTop;
            if (cOst == 0) {
                document.querySelector('.navbar').classList.add('top-nav-collapse');
            } else if (cOst > ost) {
                document.querySelector('.navbar').classList.add('top-nav-collapse');
                document.querySelector('.navbar').classList.remove('default');
            } else {
                document.querySelector('.navbar').classList.add('default');
                document.querySelector('.navbar').classList.remove('top-nav-collapse');
            }
            ost = cOst;
        });
        // End [ Menu hide/show on scroll ]
        var wow = new WOW({
            animateClass: 'animated'
        });
        wow.init();
        const app_Swiper = new Swiper('.app-slider', {
            loop: true,
            slidesPerView: '1.2',
            centeredSlides: true,
            spaceBetween: 20,
            navigation: {
                nextEl: '.swiper-button-next',
                prevEl: '.swiper-button-prev'
            }
        });
        const choose_Swiper = new Swiper('.choose-slider', {
            direction: 'vertical',
            loop: true,
            centeredSlides: true,
            slidesPerView: '4',
            autoplay: {
                delay: 2500,
                disableOnInteraction: false
            }
        });
        const frameworks_Swiper = new Swiper('.frameworks-slider', {
            loop: true,
            centeredSlides: true,
            spaceBetween: 24,
            slidesPerView: 2,
            pagination: {
                el: '.swiper-pagination',
                dynamicBullets: true,
                clickable: true
            },
            breakpoints: {
                640: {
                    slidesPerView: 2
                },
                768: {
                    slidesPerView: 4
                },
                1024: {
                    slidesPerView: 5
                }
            }
        });
    </script>
    <!-- [Page Specific JS] end -->

    <!-- Enterprise Contact Modal -->
    <div class="modal fade" id="enterpriseContactModal" tabindex="-1" aria-labelledby="enterpriseContactModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="enterpriseContactModalLabel">{{ __('Enterprise Package Inquiry') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="enterpriseContactForm">
                        <div class="mb-3">
                            <label for="name" class="form-label">{{ __('Name') }}</label>
                            <input type="text" class="form-control" id="name" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">{{ __('Email') }}</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                        <div class="mb-3">
                            <label for="phone" class="form-label">{{ __('Phone (Optional)') }}</label>
                            <input type="text" class="form-control" id="phone" name="phone" placeholder="Enter phone number (optional)">
                        </div>
                        <div class="mb-3">
                            <label for="property_limit" class="form-label">{{ __('Desired Property Limit') }}</label>
                            <input type="text" class="form-control" id="property_limit" name="property_limit" required>
                        </div>
                        <div class="mb-3">
                            <label for="unit_limit" class="form-label">{{ __('Desired Unit Limit') }}</label>
                            <input type="text" class="form-control" id="unit_limit" name="unit_limit" required>
                        </div>
                        <div class="mb-3">
                            <label for="interval" class="form-label">{{ __('Preferred Interval') }}</label>
                            <select class="form-control" id="interval" name="interval" required>
                                <option value="Monthly">{{ __('Monthly') }}</option>
                                <option value="Quarterly">{{ __('Quarterly') }}</option>
                                <option value="Yearly">{{ __('Yearly') }}</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="message" class="form-label">{{ __('Additional Message (Optional)') }}</label>
                            <textarea class="form-control" id="message" name="message" rows="3"></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Close') }}</button>
                    <button type="button" class="btn btn-primary" id="submitEnterpriseContact">{{ __('Submit') }}</button>
                </div>
            </div>
        </div>
    </div>

    <script>
    if (typeof $ === 'undefined') { alert('jQuery not loaded!'); }

    $(document).ready(function() {
        $('#submitEnterpriseContact').click(function() {
            var form = $('#enterpriseContactForm');
            var formData = form.serialize();
            
            $.ajax({
                url: '{{ route("enterprise.contact") }}',
                type: 'POST',
                data: formData,
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                success: function(response) {
                    alert(response.message);
                    $('#enterpriseContactModal').modal('hide');
                    form[0].reset();
                },
                error: function(xhr) {
                    if (xhr.status === 422) {
                        var errors = xhr.responseJSON.errors;
                        var errorMessage = '';
                        for (var key in errors) {
                            errorMessage += errors[key][0] + '\n';
                        }
                        alert(errorMessage);
                    } else {
                        alert('An error occurred. Please try again.');
                    }
                }
            });
        });
    });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // If no tab is active, activate the first one
        var firstTab = document.querySelector('.nav-tabs .nav-link');
        var firstPane = document.querySelector('.tab-pane');
        if (firstTab && !firstTab.classList.contains('active')) {
            firstTab.classList.add('active');
        }
        if (firstPane && !firstPane.classList.contains('show')) {
            firstPane.classList.add('show', 'active');
        }
    });
    </script>
</body>

</html>

