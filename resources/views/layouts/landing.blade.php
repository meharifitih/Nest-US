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
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            padding: 1rem 0;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .navbar-brand img {
            height: 40px;
        }

        .nav-link {
            color: var(--text-color);
            font-weight: 500;
            padding: 0.5rem 1rem;
            transition: color 0.3s ease;
        }

        .nav-link:hover {
            color: var(--primary-color);
        }

        .btn-primary {
            background: var(--primary-color);
            border: none;
            padding: 0.75rem 1.5rem;
            font-weight: 600;
            border-radius: 8px;
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            background: var(--secondary-color);
            transform: translateY(-2px);
        }

        .hero-section {
            padding: 120px 0 80px;
            background: linear-gradient(135deg, #fff 0%, var(--light-bg) 100%);
            position: relative;
            overflow: hidden;
        }

        .hero-section::before {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 50%;
            height: 100%;
            background: url('assets/images/landing/img-header-bg.png') no-repeat right center;
            background-size: contain;
            opacity: 0.1;
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
            background: white;
            border-radius: 16px;
            padding: 2rem;
            height: 100%;
            transition: all 0.3s ease;
            border: 1px solid rgba(0, 0, 0, 0.1);
        }

        .feature-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--card-shadow);
        }

        .feature-icon {
            width: 64px;
            height: 64px;
            background: var(--light-bg);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1.5rem;
        }

        .feature-icon i {
            font-size: 32px;
            color: var(--primary-color);
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
            background: linear-gradient(120deg, #fff 0%, #e0f7fa 100%);
            border-radius: 2rem;
            margin-bottom: 2rem;
        }
        .modern-offers-section h2 {
            font-size: 2rem;
        }
        .modern-offers-section .fw-semibold {
            font-size: 1.15rem;
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
        .spacer-navbar { height: 100px; }
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
        .price-card .card-body {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
        }
        .price-card .card-body ul {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            width: 100%;
            text-align: center !important;
            margin: 0 auto !important;
            padding: 0;
        }
        .price-card .card-body ul li {
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            width: 100%;
            margin: 0 auto;
        }
        .price-card .card-body ul li i {
            margin-right: 8px;
            font-size: 1.1em;
        }
        .price-card .card-body .price-price,
        .price-card .card-body h2,
        .price-card .card-body span,
        .price-card .card-body p {
            width: 100%;
            text-align: center;
            margin-left: auto;
            margin-right: auto;
        }
    </style>
</head>

<body class="landing-page" data-pc-preset="{{ !empty($settings['color_type']) && $settings['color_type'] == 'custom' ? 'custom' : $settings['accent_color'] }}" data-pc-sidebar-theme="light"
    data-pc-sidebar-caption="{{ $settings['sidebar_caption'] }}" data-pc-direction="{{ $settings['theme_layout'] }}"
    data-pc-theme="{{ $settings['theme_mode'] }}">


    <nav class="navbar navbar-expand-lg fixed-top">
        <div class="container">
            <a class="navbar-brand" href="#">
                <img src="{{ asset(Storage::url('upload/logo/landing_logo.png')) }}" alt="logo"
                    class="img-fluid" />
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
                data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false"
                aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-center">
                    <li class="nav-item">
                        <a class="nav-link" href="#features">Features</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#pricing">Pricing</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#privacy">Privacy Policy</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#terms">Terms & Conditions</a>
                    </li>
                    <li class="nav-item ms-lg-3">
                        <a class="nav-link" href="{{ route('login') }}">Login</a>
                    </li>
                    <li class="nav-item ms-lg-3">
                        <a class="btn btn-primary" href="{{ route('register') }}">Get Started</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    <div class="spacer-navbar"></div>
    <!-- [ Nav ] start -->
    <!-- [ Header ] start -->
    @php
        $Section_1 = App\Models\HomePage::where('section', 'Section 1')->first();
        $Section_1_content_value = !empty($Section_1->content_value)
            ? json_decode($Section_1->content_value, true)
            : [];
    @endphp
    @if (empty($Section_1_content_value['section_enabled']) || $Section_1_content_value['section_enabled'] == 'active')
        <header id="home" class="simple-hero-section py-5">
            <div class="container">
                <div class="row justify-content-center text-center">
                    <div class="col-lg-8 mx-auto">
                        <h1 class="fw-bold mb-3" style="font-size:2.5rem;">
                            @if (!empty($Section_1_content_value['title']))
                                {{ $Section_1_content_value['title'] }}
                            @else
                                Nest – Property Management System
                            @endif
                        </h1>
                        <p class="lead text-muted mb-4">
                            @if (!empty($Section_1_content_value['sub_title']))
                                {{ $Section_1_content_value['sub_title'] }}
                            @else
                                Property management made easy: streamline operations, manage tenants, and grow your business with confidence.
                            @endif
                        </p>
                        @php
                            $Section_1_btn_link = !empty($Section_1_content_value['btn_link'])
                                ? $Section_1_content_value['btn_link']
                                : '#';
                            $sec1_url = $Section_1_btn_link;
                            if (in_array($Section_1_btn_link, ['#', ''])) {
                                $sec1_url = route('register');
                            }
                        @endphp
                        <a href="{{ $sec1_url }}" class="btn btn-primary btn-lg px-5 shadow-sm mb-3">
                            @if (!empty($Section_1_content_value['btn_name']))
                                {{ $Section_1_content_value['btn_name'] }}
                            @else
                                {{ __('Get Started') }}
                            @endif
                        </a>
                    </div>
                </div>
                <div class="row justify-content-center mt-5">
                    <div class="col-lg-10 text-center">
                        <img src="@if (!empty($Section_1_content_value['section_main_image_path'])){{ asset(Storage::url($Section_1_content_value['section_main_image_path'])) }}@else assets/images/landing/img-header-main.svg @endif" alt="dashboard preview" class="img-fluid rounded-4 shadow-sm" style="max-width: 80%;">
                    </div>
                </div>
            </div>
        </header>
    @endif
    <!-- [ Header ] End -->
    <!-- [ section ] start -->
    @php
        $Section_2 = App\Models\HomePage::where('section', 'Section 2')->first();
        $Section_2_content_value = !empty($Section_2->content_value)
            ? json_decode($Section_2->content_value, true)
            : [];
    @endphp
    @if (empty($Section_2_content_value['section_enabled']) || $Section_2_content_value['section_enabled'] == 'active')
        <section>
            <div class="container">
                <div class="row g-4">
                    <div class="col-md-6 col-lg-4">
                        <div class="card feature-card mb-0 bg-secondary">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="flex-shrink-0">
                                        <div class="avtar avtar-l">
                                            <img src="{{ !empty($Section_2_content_value['box_image_1_path']) ? asset(Storage::url($Section_2_content_value['box_image_1_path'])) : 'assets/images/landing/img-feature-1.svg' }}"
                                                alt="img" class="img-fluid" />
                                        </div>
                                    </div>
                                    <div class="flex-grow-1 ms-3 text-end">
                                        <span
                                            class="h1 mb-0 d-block fw-semibold">{{ !empty($Section_2_content_value['Box1_number']) ? $Section_2_content_value['Box1_number'] : '500+' }}</span>
                                        <span
                                            class="h5 mb-0 d-block">{{ !empty($Section_2_content_value['Box1_title']) ? $Section_2_content_value['Box1_title'] : 'Customers' }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-4">
                        <div class="card feature-card mb-0 bg-blue-200">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="flex-shrink-0">
                                        <div class="avtar avtar-l">
                                            <img src="{{ !empty($Section_2_content_value['box_image_2_path']) ? asset(Storage::url($Section_2_content_value['box_image_2_path'])) : 'assets/images/landing/img-feature-2.svg' }}"
                                                alt="img" class="img-fluid" />
                                        </div>
                                    </div>
                                    <div class="flex-grow-1 ms-3 text-end">
                                        <span
                                            class="h1 mb-0 d-block fw-semibold">{{ !empty($Section_2_content_value['Box2_number']) ? $Section_2_content_value['Box2_number'] : '4+' }}</span>
                                        <span
                                            class="h5 mb-0 d-block">{{ !empty($Section_2_content_value['Box2_title']) ? $Section_2_content_value['Box2_title'] : 'Subscription Plan' }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-12 col-lg-4">
                        <div class="card feature-card mb-0 bg-purple-200">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="flex-shrink-0">
                                        <div class="avtar avtar-l">
                                            <img src="{{ !empty($Section_2_content_value['box_image_3_path']) ? asset(Storage::url($Section_2_content_value['box_image_3_path'])) : 'assets/images/landing/img-feature-3.svg' }}"
                                                alt="img" class="img-fluid" />
                                        </div>
                                    </div>
                                    <div class="flex-grow-1 ms-3 text-end">
                                        <span
                                            class="h1 mb-0 d-block fw-semibold">{{ !empty($Section_2_content_value['Box3_number']) ? $Section_2_content_value['Box3_number'] : '11+' }}</span>
                                        <span
                                            class="h5 mb-0 d-block">{{ !empty($Section_2_content_value['Box3_title']) ? $Section_2_content_value['Box3_title'] : 'Language' }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    @endif
    <!-- [ section ] End -->
    <!-- [ section ] start -->
    @php
        $Section_3 = App\Models\HomePage::where('section', 'Section 3')->first();
        $Section_3_content_value = !empty($Section_3->content_value)
            ? json_decode($Section_3->content_value, true)
            : [];
    @endphp
    @if (empty($Section_3_content_value['section_enabled']) || $Section_3_content_value['section_enabled'] == 'active')
        <section class="minimal-feature-section py-5">
            <div class="container">
                @for ($is3 = 1; $is3 <= 2; $is3++)
                    <div class="row align-items-center justify-content-center mb-5 flex-lg-row flex-column-reverse">
                        <div class="col-lg-6 mb-4 mb-lg-0">
                            <div class="minimal-feature-content pe-lg-5">
                                <h2 class="fw-bold mb-3" style="font-size:2rem; color:#155263;">
                                    {{ !empty($Section_3_content_value['Box' . $is3 . '_title']) ? $Section_3_content_value['Box' . $is3 . '_title'] : ($is3 == 1 ? 'Empower Your Business to Thrive with Us' : 'Eliminate Paperwork, Elevate Productivity') }}
                                </h2>
                                <p class="mb-4 text-muted" style="font-size:1.1rem;">
                                    {{ !empty($Section_3_content_value['Box' . $is3 . '_info']) ? $Section_3_content_value['Box' . $is3 . '_info'] : ($is3 == 1 ? 'Unlock growth, streamline operations, and achieve success with our innovative solutions.' : 'Digitize your workflow and boost productivity with seamless automation and smart tools.') }}
                                </p>
                                <ul class="list-unstyled minimal-checklist">
                                    @if (!empty($Section_3_content_value['Box' . $is3 . '_list']))
                                        @foreach ($Section_3_content_value['Box' . $is3 . '_list'] as $box_item)
                                            <li class="mb-3"><span class="minimal-icon">&#9679;</span> {{ $box_item }}</li>
                                        @endforeach
                                    @else
                                        @if ($is3 == 1)
                                            <li class="mb-3"><span class="minimal-icon">&#9679;</span> Simplify and automate your business processes for maximum efficiency.</li>
                                            <li class="mb-3"><span class="minimal-icon">&#9679;</span> Receive tailored strategies to meet business needs and unlock potential.</li>
                                            <li class="mb-3"><span class="minimal-icon">&#9679;</span> Grow confidently with flexible solutions that adapt to your business needs.</li>
                                            <li class="mb-3"><span class="minimal-icon">&#9679;</span> Make smarter decisions with real-time analytics and performance tracking.</li>
                                            <li class="mb-3"><span class="minimal-icon">&#9679;</span> Rely on 24/7 expert assistance to keep your business running smoothly.</li>
                                        @else
                                            <li class="mb-3"><span class="minimal-icon">&#9679;</span> Eliminate manual paperwork with digital forms and e-signatures.</li>
                                            <li class="mb-3"><span class="minimal-icon">&#9679;</span> Centralize documents for easy access and sharing.</li>
                                            <li class="mb-3"><span class="minimal-icon">&#9679;</span> Automate repetitive tasks to save time and reduce errors.</li>
                                            <li class="mb-3"><span class="minimal-icon">&#9679;</span> Collaborate in real-time with your team and clients.</li>
                                            <li class="mb-3"><span class="minimal-icon">&#9679;</span> Track progress and productivity with smart dashboards.</li>
                                        @endif
                                    @endif
                                </ul>
                            </div>
                        </div>
                        <div class="col-lg-6 text-center mb-4 mb-lg-0">
                            @if (!empty($Section_1_content_value['Box' . $is3 . '_image_path']))
                                <img src="{{ asset(Storage::url($Section_1_content_value['Box' . $is3 . '_image_path'])) }}" alt="img" class="img-fluid minimal-feature-img" style="max-height:320px;object-fit:contain;" />
                            @else
                                <img src="assets/images/landing/img-customize-{{ $is3 }}.svg" alt="img" class="img-fluid minimal-feature-img" style="max-height:320px;object-fit:contain;" />
                            @endif
                        </div>
                    </div>
                @endfor
            </div>
        </section>
        <style>
            .minimal-feature-section {
                background: #fafdff;
                border-radius: 2rem;
            }
            .minimal-feature-content {
                background: none;
                box-shadow: none;
                padding: 0;
            }
            .minimal-feature-content h2 {
                color: #155263;
            }
            .minimal-checklist {
                margin-top: 2rem;
            }
            .minimal-checklist li {
                font-size: 1.08rem;
                display: flex;
                align-items: flex-start;
                color: #222;
                margin-bottom: 1rem;
            }
            .minimal-icon {
                color: #00bcd4;
                font-size: 1.1rem;
                margin-right: 12px;
                margin-top: 4px;
                display: inline-block;
            }
            .minimal-feature-img {
                border-radius: 1.5rem;
                background: #f3fafd;
                padding: 1.5rem;
            }
            @media (max-width: 991.98px) {
                .minimal-feature-section { border-radius: 1rem; }
                .minimal-feature-img { padding: 1rem; }
            }
        </style>
    @endif
    <!-- [ section ] End -->
    <!-- [ section ] start -->
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
                        <h2 class="display-5 fw-bold mb-3 text-gradient">
                            {{ !empty($Section_4_content_value['Sec4_title']) ? $Section_4_content_value['Sec4_title'] : 'What does Smartweb offer?' }}
                        </h2>
                        <p class="lead text-muted">
                            {{ !empty($Section_4_content_value['Sec4_info']) ? $Section_4_content_value['Sec4_info'] : 'Smartweb is a reliable choice for your admin panel needs, offering a wide range of features to easily manage your backend panel' }}
                        </p>
                    </div>
                </div>
                <div class="row g-4 text-center">
                    @php $is4_check = 0; @endphp
                    @for ($is4 = 1; $is4 <= 6; $is4++)
                        @if (!empty($Section_4_content_value['Sec4_box' . $is4 . '_enabled']) && $Section_4_content_value['Sec4_box' . $is4 . '_enabled'] == 'active')
                            @php $is4_check++; @endphp
                            <div class="col-md-6 col-xl-4">
                                @if (!empty($Section_4_content_value['Sec4_box' . $is4 . '_image_path']))
                                    <img src="{{ asset(Storage::url($Section_4_content_value['Sec4_box' . $is4 . '_image_path'])) }}" alt="img" class="img-fluid mb-3 rounded-3 shadow-sm" />
                                @else
                                    <img src="assets/images/landing/img-design-{{ $is4 }}.svg" alt="img" class="img-fluid mb-3 rounded-3 shadow-sm" />
                                @endif
                                <h3 class="fw-semibold mb-2">{{ !empty($Section_4_content_value['Sec4_box' . $is4 . '_title']) ? $Section_4_content_value['Sec4_box' . $is4 . '_title'] : 'What Our Software Offers' }}</h3>
                                <p class="text-muted">{{ !empty($Section_4_content_value['Sec4_box' . $is4 . '_info']) ? $Section_4_content_value['Sec4_box' . $is4 . '_info'] : 'Our software provides powerful, scalable solutions designed to streamline your business operations.' }}</p>
                            </div>
                        @endif
                    @endfor
                    @if ($is4_check == 0)
                        <div class="col-md-6 col-xl-4">
                            <img src="assets/images/landing/img-design-1.svg" alt="img" class="img-fluid mb-3 rounded-3 shadow-sm" />
                            <h3 class="fw-semibold mb-2">User-Friendly Interface</h3>
                            <p class="text-muted">Simplify operations with an intuitive and easy-to-use platform.</p>
                        </div>
                        <div class="col-md-6 col-xl-4">
                            <img src="assets/images/landing/img-design-2.svg" alt="img" class="img-fluid mb-3 rounded-3 shadow-sm" />
                            <h3 class="fw-semibold mb-2">End-to-End Automation</h3>
                            <p class="text-muted">Automate repetitive tasks to save time and increase efficiency.</p>
                        </div>
                        <div class="col-md-6 col-xl-4">
                            <img src="assets/images/landing/img-design-3.svg" alt="img" class="img-fluid mb-3 rounded-3 shadow-sm" />
                            <h3 class="fw-semibold mb-2">Customizable Solutions</h3>
                            <p class="text-muted">Tailor features to fit your unique business needs and workflows.</p>
                        </div>
                        <div class="col-md-6 col-xl-4">
                            <img src="assets/images/landing/img-design-4.svg" alt="img" class="img-fluid mb-3 rounded-3 shadow-sm" />
                            <h3 class="fw-semibold mb-2">Scalable Features</h3>
                            <p class="text-muted">Grow your business with flexible solutions that scale with you.</p>
                        </div>
                        <div class="col-md-6 col-xl-4">
                            <img src="assets/images/landing/img-design-5.svg" alt="img" class="img-fluid mb-3 rounded-3 shadow-sm" />
                            <h3 class="fw-semibold mb-2">Enhanced Security</h3>
                            <p class="text-muted">Protect your data with advanced encryption and security protocols.</p>
                        </div>
                        <div class="col-md-6 col-xl-4">
                            <img src="assets/images/landing/img-design-6.svg" alt="img" class="img-fluid mb-3 rounded-3 shadow-sm" />
                            <h3 class="fw-semibold mb-2">Real-Time Analytics</h3>
                            <p class="text-muted">Gain actionable insights with live data tracking and reporting.</p>
                        </div>
                    @endif
                </div>
            </div>
        </section>
    @endif

    <!-- [ section ] End -->
    @php
        $Section_5 = App\Models\HomePage::where('section', 'Section 5')->first();
        $Section_5_content_value = !empty($Section_5->content_value)
            ? json_decode($Section_5->content_value, true)
            : [];
    @endphp
    @if ($settings['pricing_feature'] == 'on')
        @if (empty($Section_5_content_value['section_enabled']) || $Section_5_content_value['section_enabled'] == 'active')
            <section class="bg-body pricingpricing" id="pricing">
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
    <!-- [ section ] start -->

    @php
        $Section_6 = App\Models\HomePage::where('section', 'Section 6')->first();
        $Section_6_content_value = !empty($Section_6->content_value)
            ? json_decode($Section_6->content_value, true)
            : [];
    @endphp
    @if (empty($Section_6_content_value['section_enabled']) || $Section_6_content_value['section_enabled'] == 'active')
        <section class="application-slider" id="features">
            <div class="container">
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
                                        <h3>
                                            Social Profile
                                            <i class="ti ti-link"></i>
                                        </h3>
                                        <p>Complete Social profile with all possible option</p>
                                    </div>
                                    <div class="swiper-slide">
                                        <img src="assets/images/landing/slider-light-2.png" alt="images"
                                            class="img-fluid" />
                                        <h3>
                                            Mail/Message App
                                            <i class="ti ti-link"></i>
                                        </h3>
                                        <p>Complete Mail/Message App with all possible option</p>
                                    </div>
                                    <div class="swiper-slide">
                                        <img src="assets/images/landing/slider-light-3.png" alt="images"
                                            class="img-fluid" />
                                        <h3>
                                            Mail/Message App
                                            <i class="ti ti-link"></i>
                                        </h3>
                                        <p>Complete Chat App with all possible option</p>
                                    </div>
                                    <div class="swiper-slide">
                                        <img src="assets/images/landing/slider-light-4.png" alt="images"
                                            class="img-fluid" />
                                        <h3>
                                            Kanban App
                                            <i class="ti ti-link"></i>
                                        </h3>
                                        <p>Complete Kanban App with all possible option</p>
                                    </div>
                                    <div class="swiper-slide">
                                        <img src="assets/images/landing/slider-light-5.png" alt="images"
                                            class="img-fluid" />
                                        <h3>
                                            Calendar App
                                            <i class="ti ti-link"></i>
                                        </h3>
                                        <p>Complete Calendar App with all possible option</p>
                                    </div>
                                    <div class="swiper-slide">
                                        <img src="assets/images/landing/slider-light-6.png" alt="images"
                                            class="img-fluid" />
                                        <h3>
                                            Ecommerce App
                                            <i class="ti ti-link"></i>
                                        </h3>
                                        <p>Complete Ecommerce App with all possible option</p>
                                    </div>
                                @endif
                            </div>
                            <div class="swiper-button-next avtar">
                                <i class="ti ti-chevron-right"></i>
                            </div>
                            <div class="swiper-button-prev avtar">
                                <i class="ti ti-chevron-left"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    @endif
    <!-- [ section ] End -->
    <!-- Testimonials Section -->
    <section class="testimonials-section py-5 bg-light">
        <div class="container">
            <div class="row justify-content-center text-center mb-5">
                <div class="col-lg-6">
                    <h2 class="display-5 fw-bold mb-3">What Our Customers Say</h2>
                    <p class="lead text-muted">Trusted by property managers worldwide</p>
                </div>
            </div>
            <div class="row g-4">
                @foreach($testimonials as $testimonial)
                <div class="col-md-4">
                    <div class="testimonial-card">
                        <div class="testimonial-content">
                            <div class="quote-icon mb-3">
                                <i class="ti ti-quote-left text-primary"></i>
                            </div>
                            <p class="testimonial-text">{{ $testimonial->content }}</p>
                            <div class="testimonial-author d-flex align-items-center mt-4">
                                <img src="{{ $testimonial->avatar }}" alt="{{ $testimonial->name }}" class="rounded-circle" width="50" height="50">
                                <div class="ms-3">
                                    <h5 class="mb-0">{{ $testimonial->name }}</h5>
                                    <p class="text-muted mb-0">{{ $testimonial->position }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- [ section ] start -->
    @php
        $Section_8 = App\Models\HomePage::where('section', 'Section 8')->first();
        $Section_8_content_value = !empty($Section_8->content_value)
            ? json_decode($Section_8->content_value, true)
            : [];
    @endphp
    @if (empty($Section_8_content_value['section_enabled']) || $Section_8_content_value['section_enabled'] == 'active')
        <section class="why-choose-section py-5">
            <div class="container">
                <div class="row justify-content-center text-center mb-5">
                    <div class="col-lg-6">
                        <h2 class="display-5 fw-bold mb-3">Why Choose Us</h2>
                        <p class="lead text-muted">Experience the difference with our comprehensive property management solution</p>
                    </div>
                </div>
                <div class="row g-4">
                    <div class="col-md-6 col-lg-3">
                        <div class="choose-card">
                            <div class="choose-icon">
                                <i class="ti ti-shield-check"></i>
                            </div>
                            <h3 class="choose-title">Secure & Reliable</h3>
                            <p class="choose-text">Bank-level security with regular backups and data protection</p>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-3">
                        <div class="choose-card">
                            <div class="choose-icon">
                                <i class="ti ti-devices"></i>
                            </div>
                            <h3 class="choose-title">Cross-Platform</h3>
                            <p class="choose-text">Access your dashboard from any device, anywhere</p>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-3">
                        <div class="choose-card">
                            <div class="choose-icon">
                                <i class="ti ti-headset"></i>
                            </div>
                            <h3 class="choose-title">24/7 Support</h3>
                            <p class="choose-text">Round-the-clock customer support for all your needs</p>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-3">
                        <div class="choose-card">
                            <div class="choose-icon">
                                <i class="ti ti-chart-line"></i>
                            </div>
                            <h3 class="choose-title">Analytics</h3>
                            <p class="choose-text">Detailed insights and reports for better decision making</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    @endif

    <!-- [ section ] End -->
    <!-- [ section ] start -->
    @php
        $Section_9 = App\Models\HomePage::where('section', 'Section 9')->first();
        $Section_9_content_value = !empty($Section_9->content_value)
            ? json_decode($Section_9->content_value, true)
            : [];
    @endphp
    @if (empty($Section_9_content_value['section_enabled']) || $Section_9_content_value['section_enabled'] == 'active')
        <section class="frameworks-section" id="faqs">
            <div class="container">
                <div class="row justify-content-center title">
                    <div class="col-md-9 col-lg-6 text-center">
                        <h2 class="h1">
                            {{ !empty($Section_9_content_value['Sec9_title']) ? $Section_9_content_value['Sec9_title'] : 'Frequently Asked Questions (FAQ)' }}
                        </h2>
                        <p class="text-lg">
                            {{ !empty($Section_9_content_value['Sec9_info']) ? $Section_9_content_value['Sec9_info'] : 'Please refer the Frequently ask question for your quick help' }}
                        </p>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="accordion accordion-flush" id="accordionFlushExample">
                            @if (!empty($FAQs->toArray()))
                                @foreach ($FAQs as $FAQ_key => $FAQ)
                                    <div class="accordion-item">
                                        <h2 class="accordion-header" id="flush-{{ $FAQ->id }}">
                                            <button
                                                class="accordion-button {{ $FAQ_key == 0 ? '' : 'collapsed' }} text-muted"
                                                type="button" data-bs-toggle="collapse"
                                                data-bs-target="#flush-collapse-{{ $FAQ->id }}"
                                                aria-expanded="false" aria-controls="flush-collapseThree">
                                                <b>{{ $FAQ->question }}</b>
                                            </button>
                                        </h2>
                                        <div id="flush-collapse-{{ $FAQ->id }}"
                                            class="accordion-collapse collapse {{ $FAQ_key == 0 ? 'collapse show' : '' }}"
                                            aria-labelledby="flush-{{ $FAQ->id }}"
                                            data-bs-parent="#accordionFlushExample">
                                            <div class="accordion-body text-muted">{!! $FAQ->description !!}</div>
                                        </div>
                                    </div>
                                @endforeach
                            @else
                                <div class="accordion-item">
                                    <h2 class="accordion-header" id="flush-headingOne">
                                        <button class="accordion-button text-muted" type="button"
                                            data-bs-toggle="collapse" data-bs-target="#flush-collapseOne"
                                            aria-expanded="false">
                                            <b>What features does your software offer?</b>
                                        </button>
                                    </h2>
                                    <div id="flush-collapseOne" class="accordion-collapse collapse show"
                                        aria-labelledby="flush-headingOne" data-bs-parent="#accordionFlushExample">
                                        <div class="accordion-body text-muted">
                                            Our software provides a range of features including automation tools,
                                            real-time analytics, cloud-based access, secure data storage, seamless
                                            integrations, and customizable solutions tailored to your business needs.
                                        </div>
                                    </div>
                                </div>
                                <div class="accordion-item">
                                    <h2 class="accordion-header" id="flush-headingTwo">
                                        <button class="accordion-button collapsed text-muted" type="button"
                                            data-bs-toggle="collapse" data-bs-target="#flush-collapseTwo"
                                            aria-expanded="false" aria-controls="flush-collapseTwo">
                                            <b>Is your software easy to use?</b>
                                        </button>
                                    </h2>
                                    <div id="flush-collapseTwo" class="accordion-collapse collapse"
                                        aria-labelledby="flush-headingTwo" data-bs-parent="#accordionFlushExample">
                                        <div class="accordion-body text-muted">
                                            Yes! Our platform is designed to be user-friendly and intuitive, so your
                                            team can get started quickly without a steep learning curve.
                                        </div>
                                    </div>
                                </div>
                                <div class="accordion-item">
                                    <h2 class="accordion-header" id="flush-headingThree">
                                        <button class="accordion-button collapsed text-muted" type="button"
                                            data-bs-toggle="collapse" data-bs-target="#flush-collapseThree"
                                            aria-expanded="false" aria-controls="flush-collapseThree">
                                            <b>Can I integrate your software with my existing systems?</b>
                                        </button>
                                    </h2>
                                    <div id="flush-collapseThree" class="accordion-collapse collapse"
                                        aria-labelledby="flush-headingThree" data-bs-parent="#accordionFlushExample">
                                        <div class="accordion-body text-muted">
                                            Absolutely! Our software is built to easily integrate with your current
                                            tools and systems, making the transition seamless and efficient.
                                        </div>
                                    </div>
                                </div>
                                <div class="accordion-item">
                                    <h2 class="accordion-header" id="flush-headingfour">
                                        <button class="accordion-button collapsed text-muted" type="button"
                                            data-bs-toggle="collapse" data-bs-target="#flush-collapse-four"
                                            aria-expanded="false" aria-controls="flush-collapseThree">
                                            <b>Is customer support available?</b>
                                        </button>
                                    </h2>
                                    <div id="flush-collapse-four" class="accordion-collapse collapse"
                                        aria-labelledby="flush-headingfour" data-bs-parent="#accordionFlushExample">
                                        <div class="accordion-body text-muted">
                                            Yes! We offer 24/7 customer support. Our dedicated team is ready to assist
                                            you with any questions or issues you may have.
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </section>
    @endif
    <!-- [ section ] End -->
    <!-- [ footer ] start -->
    <footer class="footer bg-dark text-light py-5">
        <div class="container">
            <div class="row g-4">
                <div class="col-lg-4">
                    <div class="footer-brand mb-4">
                        <img src="{{ asset(Storage::url('upload/logo/landing_logo.png')) }}" alt="logo" class="img-fluid mb-3" style="max-height: 40px;">
                        <p class="text-light-50">Streamline your property management with our comprehensive solution. Built for modern property managers.</p>
                    </div>
                    <div class="social-links">
                        <a href="#" class="text-light me-3"><i class="ti ti-brand-facebook"></i></a>
                        <a href="#" class="text-light me-3"><i class="ti ti-brand-twitter"></i></a>
                        <a href="#" class="text-light me-3"><i class="ti ti-brand-linkedin"></i></a>
                        <a href="#" class="text-light"><i class="ti ti-brand-instagram"></i></a>
                    </div>
                </div>
                <div class="col-6 col-lg-2">
                    <h5 class="text-light mb-3">Product</h5>
                    <ul class="list-unstyled footer-links">
                        <li><a href="#features">Features</a></li>
                        <li><a href="#pricing">Pricing</a></li>
                        <li><a href="#testimonials">Testimonials</a></li>
                        <li><a href="#faq">FAQ</a></li>
                    </ul>
                </div>
                <div class="col-6 col-lg-2">
                    <h5 class="text-light mb-3">Company</h5>
                    <ul class="list-unstyled footer-links">
                        <li><a href="#about">About Us</a></li>
                        <li><a href="#contact">Contact</a></li>
                        <li><a href="#careers">Careers</a></li>
                        <li><a href="#blog">Blog</a></li>
                    </ul>
                </div>
                <div class="col-6 col-lg-2">
                    <h5 class="text-light mb-3">Legal</h5>
                    <ul class="list-unstyled footer-links">
                        <li><a href="#privacy">Privacy Policy</a></li>
                        <li><a href="#terms">Terms of Service</a></li>
                        <li><a href="#cookies">Cookie Policy</a></li>
                        <li><a href="#gdpr">GDPR</a></li>
                    </ul>
                </div>
                <div class="col-6 col-lg-2">
                    <h5 class="text-light mb-3">Support</h5>
                    <ul class="list-unstyled footer-links">
                        <li><a href="#help">Help Center</a></li>
                        <li><a href="#documentation">Documentation</a></li>
                        <li><a href="#api">API</a></li>
                        <li><a href="#status">Status</a></li>
                    </ul>
                </div>
            </div>
            <hr class="my-4 border-light-50">
            <div class="row align-items-center">
                <div class="col-md-6 text-center text-md-start">
                    <p class="mb-0 text-light-50">&copy; {{ date('Y') }} {{ !empty($settings['app_name']) ? $settings['app_name'] : env('APP_NAME') }}. All rights reserved.</p>
                </div>
                <div class="col-md-6 text-center text-md-end mt-3 mt-md-0">
                    <div class="footer-bottom-links">
                        <a href="#privacy" class="text-light-50 me-3">Privacy</a>
                        <a href="#terms" class="text-light-50 me-3">Terms</a>
                        <a href="#cookies" class="text-light-50">Cookies</a>
                    </div>
                </div>
            </div>
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
