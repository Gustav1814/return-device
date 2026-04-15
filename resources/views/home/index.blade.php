@extends('layouts.home')

@php
    $isTenantPortal = isset($_SERVER['SERVER_NAME']) && $_SERVER['SERVER_NAME'] != env('MAIN_DOMAIN');
    $accent = isset($companySettings) && $companySettings->btn_bg_color ? $companySettings->btn_bg_color : '#6366f1';
    $hex = ltrim(trim((string) $accent), '#');
    if (strlen($hex) === 3) {
        $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
    }
    $accentRgb = preg_match('/^[a-fA-F0-9]{6}$/', $hex) ? hexdec(substr($hex, 0, 2)) . ',' . hexdec(substr($hex, 2, 2)) . ',' . hexdec(substr($hex, 4, 2)) : '99,102,241';
    $hasLogo = isset($companySettings) && !empty($companySettings->logo);
    $logoUrl = $hasLogo ? asset('storage/logoImage/' . $companySettings->logo) : '';
    $brandName = isset($companySettings) ? optional($companySettings->company)->company_name : null;
    $brandName = $brandName ?: 'Return Device';
    $elStyle = '--accent-color:' . e($accent) . ';--accent-rgb:' . e($accentRgb) . ';';
@endphp

@section('body_class', 'employee-landing-body')

@push('styles')
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style id="employee-landing-styles">
        .employee-landing-body {
            margin: 0;
            min-height: 100vh;
            background: #f8fafc;
            font-family: Inter, system-ui, -apple-system, sans-serif;
        }

        .el-root {
            --el-text: #0f172a;
            --el-muted: #64748b;
            --el-page-bg: #f8fafc;
            --el-card: #ffffff;
            color: var(--el-text);
        }

        .el-nav {
            position: sticky;
            top: 0;
            z-index: 1040;
            width: 100%;
            background: rgba(255, 255, 255, 0.72);
            backdrop-filter: blur(14px);
            -webkit-backdrop-filter: blur(14px);
            border-bottom: 1px solid rgba(15, 23, 42, 0.06);
            box-shadow: 0 4px 24px -12px rgba(15, 23, 42, 0.12);
        }

        .el-nav-inner {
            max-width: 72rem;
            margin: 0 auto;
            padding: 0.875rem 1.25rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .el-brand {
            display: flex;
            align-items: center;
            min-height: 2.5rem;
            min-width: 0;
            text-decoration: none;
            color: inherit;
        }

        .el-logo-skel {
            width: 140px;
            height: 40px;
            border-radius: 10px;
            background: linear-gradient(90deg, rgba(var(--accent-rgb), 0.08) 0%, rgba(var(--accent-rgb), 0.18) 50%, rgba(var(--accent-rgb), 0.08) 100%);
            background-size: 200% 100%;
            animation: el-shimmer 1.4s ease-in-out infinite;
        }

        .el-brand--ready .el-logo-skel {
            display: none;
        }

        .el-logo-img {
            max-height: 44px;
            width: auto;
            max-width: 200px;
            object-fit: contain;
            object-position: left center;
            opacity: 0;
            transition: opacity 0.35s ease;
        }

        .el-brand--ready .el-logo-img {
            opacity: 1;
        }

        .el-brand-text {
            font-weight: 700;
            font-size: 1.125rem;
            letter-spacing: -0.02em;
            color: var(--el-text);
        }

        .el-nav-actions {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            flex-wrap: wrap;
        }

        .el-nav-actions a {
            color: var(--accent-color);
            text-decoration: none;
            font-weight: 500;
            font-size: 0.875rem;
            padding: 0.35rem 0.65rem;
            border-radius: 0.5rem;
            transition: background-color 0.25s ease, color 0.25s ease;
        }

        .el-nav-actions a:hover {
            background: rgba(var(--accent-rgb), 0.08);
            color: var(--accent-color);
        }

        .el-nav-actions .el-btn-outline {
            border: 1px solid rgba(var(--accent-rgb), 0.35);
        }

        .el-dropdown .btn {
            border-radius: 0.75rem;
            font-weight: 600;
            background: var(--accent-color) !important;
            border-color: var(--accent-color) !important;
            color: #fff !important;
        }

        .el-hero {
            position: relative;
            min-height: min(100vh, 920px);
            display: flex;
            align-items: center;
            overflow: hidden;
            background: linear-gradient(
                165deg,
                rgba(var(--accent-rgb), 0.08) 0%,
                rgba(var(--accent-rgb), 0.04) 38%,
                var(--el-page-bg) 100%
            );
        }

        .el-hero-blob {
            position: absolute;
            border-radius: 50%;
            filter: blur(0.5px);
            pointer-events: none;
        }

        .el-hero-inner {
            position: relative;
            z-index: 1;
            max-width: 72rem;
            margin: 0 auto;
            padding: 3rem 1.25rem 4rem;
            display: grid;
            grid-template-columns: 1fr;
            gap: 2.5rem;
            align-items: center;
        }

        @media (min-width: 992px) {
            .el-hero-inner {
                grid-template-columns: 1fr 1fr;
                gap: 3rem;
            }
        }

        .el-hero-copy {
            animation: el-hero-in 0.75s ease forwards;
        }

        @keyframes el-hero-in {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .el-hero h1 {
            font-size: clamp(2rem, 4vw, 3rem);
            font-weight: 800;
            letter-spacing: -0.03em;
            line-height: 1.12;
            margin: 0 0 1rem;
            color: var(--el-text);
        }

        .el-hero .el-sub {
            font-size: 1.0625rem;
            line-height: 1.55;
            color: var(--el-muted);
            margin: 0 0 1.75rem;
            max-width: 36rem;
        }

        @media (min-width: 992px) {
            .el-hero .el-sub {
                margin-left: 0;
            }
            .el-hero-copy {
                text-align: left;
            }
        }

        @media (max-width: 991.98px) {
            .el-hero-copy {
                text-align: center;
            }
            .el-hero .el-sub {
                margin-left: auto;
                margin-right: auto;
            }
        }

        .el-cta {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.85rem 1.75rem;
            font-weight: 600;
            font-size: 1rem;
            color: #fff !important;
            background: var(--accent-color);
            border: none;
            border-radius: 9999px;
            text-decoration: none;
            cursor: pointer;
            box-shadow: 0 8px 24px -8px rgba(var(--accent-rgb), 0.55);
            transition: transform 0.25s ease, box-shadow 0.25s ease;
        }

        .el-cta:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 32px -8px rgba(var(--accent-rgb), 0.65), 0 0 0 6px rgba(var(--accent-rgb), 0.12);
            color: #fff !important;
        }

        .el-cta:focus-visible {
            outline: 2px solid var(--accent-color);
            outline-offset: 3px;
        }

        .el-cta-arrow {
            font-size: 1.15rem;
            line-height: 1;
        }

        .el-illus-wrap {
            display: flex;
            justify-content: center;
            align-items: center;
            animation: el-float 5s ease-in-out infinite;
        }

        @keyframes el-float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }

        .el-illus {
            width: min(100%, 300px);
            height: auto;
            color: var(--accent-color);
        }

        .el-hiw {
            padding: 4rem 1.25rem 5rem;
            background: var(--el-page-bg);
        }

        .el-hiw-inner {
            max-width: 72rem;
            margin: 0 auto;
        }

        .el-hiw h2 {
            text-align: center;
            font-size: clamp(1.75rem, 3vw, 2.25rem);
            font-weight: 800;
            letter-spacing: -0.02em;
            margin: 0 0 0.75rem;
            color: var(--el-text);
        }

        .el-hiw .el-hiw-sub {
            text-align: center;
            max-width: 34rem;
            margin: 0 auto 3rem;
            font-size: 1rem;
            line-height: 1.6;
            color: var(--el-muted);
        }

        .el-steps {
            position: relative;
            display: grid;
            grid-template-columns: 1fr;
            gap: 1.5rem;
        }

        @media (min-width: 768px) and (max-width: 991.98px) {
            .el-steps {
                grid-template-columns: repeat(2, 1fr);
            }
            .el-steps .el-step-card:last-child:nth-child(odd) {
                grid-column: 1 / -1;
                max-width: 50%;
                margin: 0 auto;
            }
        }

        @media (min-width: 992px) {
            .el-steps {
                grid-template-columns: repeat(3, 1fr);
                gap: 1.75rem;
            }
            .el-steps::before {
                content: '';
                position: absolute;
                left: 12%;
                right: 12%;
                top: 3.25rem;
                height: 0;
                border-top: 2px dotted rgba(var(--accent-rgb), 0.35);
                z-index: 0;
            }
        }

        .el-step-card {
            position: relative;
            z-index: 1;
            background: var(--el-card);
            border-radius: 16px;
            padding: 1.75rem 1.5rem 1.75rem;
            box-shadow: 0 4px 6px -1px rgba(15, 23, 42, 0.06), 0 12px 24px -10px rgba(15, 23, 42, 0.1);
            border: 1px solid rgba(15, 23, 42, 0.05);
            border-top: 3px solid var(--accent-color);
            opacity: 0;
            transform: translateY(16px);
            transition: opacity 0.5s ease, transform 0.5s ease, box-shadow 0.25s ease;
            transition-delay: var(--stagger, 0ms);
        }

        .el-step-card.el-step-card--in {
            opacity: 1;
            transform: translateY(0);
        }

        .el-step-card.el-step-card--in:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 28px -8px rgba(15, 23, 42, 0.14), 0 4px 12px -4px rgba(15, 23, 42, 0.08);
        }

        .el-step-icon {
            width: 28px;
            height: 28px;
            color: var(--accent-color);
            margin-bottom: 0.75rem;
        }

        .el-step-badge {
            width: 2.75rem;
            height: 2.75rem;
            border-radius: 9999px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            font-size: 1.1rem;
            color: #fff;
            background: linear-gradient(145deg, var(--accent-color), rgba(var(--accent-rgb), 0.75));
            margin-bottom: 1rem;
            box-shadow: 0 4px 14px -4px rgba(var(--accent-rgb), 0.6);
        }

        .el-step-card h3 {
            font-size: 1.125rem;
            font-weight: 700;
            margin: 0 0 0.5rem;
            color: var(--el-text);
        }

        .el-step-card p {
            margin: 0;
            font-size: 0.9375rem;
            line-height: 1.55;
            color: var(--el-muted);
        }

        .el-footer {
            background: #f1f5f9;
            border-top: 1px solid rgba(15, 23, 42, 0.06);
            padding: 2.5rem 1.25rem;
        }

        .el-footer-inner {
            max-width: 72rem;
            margin: 0 auto;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 1rem;
            text-align: center;
        }

        @media (min-width: 640px) {
            .el-footer-inner {
                flex-direction: row;
                justify-content: space-between;
                text-align: left;
                align-items: center;
            }
        }

        .el-footer-brand {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0.75rem;
        }

        @media (min-width: 640px) {
            .el-footer-brand {
                align-items: flex-start;
            }
        }

        .el-footer .el-footer-copy {
            margin: 0;
            font-size: 0.8125rem;
            color: var(--el-muted);
        }

        .el-footer-nav {
            display: flex;
            flex-wrap: wrap;
            gap: 1.25rem;
            justify-content: center;
        }

        .el-footer-nav a {
            font-size: 0.875rem;
            font-weight: 500;
            color: var(--accent-color);
            text-decoration: none;
            transition: opacity 0.2s ease;
        }

        .el-footer-nav a:hover {
            opacity: 0.85;
            text-decoration: underline;
        }

        @keyframes el-shimmer {
            0% { background-position: 200% 0; }
            100% { background-position: -200% 0; }
        }
    </style>
@endpush

@section('replace_header')
    <header class="el-nav" style="{{ $elStyle }}">
        <div class="el-nav-inner el-root" style="{{ $elStyle }}">
            <a href="{{ route('home.index') }}" class="el-brand @if(!$hasLogo) el-brand--ready @endif" data-el-brand>
                @if ($hasLogo)
                    <div class="el-logo-skel" aria-hidden="true"></div>
                    <img
                        src="{{ $logoUrl }}?v={{ time() }}"
                        alt="{{ $brandName }}"
                        class="el-logo-img"
                        width="200"
                        height="48"
                        loading="eager"
                        onload="this.closest('[data-el-brand]')?.classList.add('el-brand--ready')"
                        onerror="this.closest('[data-el-brand]')?.classList.add('el-brand--ready'); this.style.display='none';"
                    >
                @else
                    <span class="el-brand-text">{{ $brandName }}</span>
                @endif
            </a>

            <div class="el-nav-actions el-dropdown">
                @guest
                    <a href="{{ route('login') }}" class="el-btn-outline">Log in</a>
                @endguest
                @auth
                    <div class="dropdown">
                        <button class="btn btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                            {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="{{ route('saas.dashboard') }}">Dashboard</a></li>
                            <li>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="dropdown-item">Sign out</button>
                                </form>
                            </li>
                        </ul>
                    </div>
                @endauth
            </div>
        </div>
    </header>
@endsection

@section('content')
    <div class="el-root" id="employee-landing" style="{{ $elStyle }}">
        <section class="el-hero" aria-label="Introduction">
            <div class="el-hero-blob" style="width:420px;height:420px;top:-120px;right:-100px;background:rgba(var(--accent-rgb),0.12)" aria-hidden="true"></div>
            <div class="el-hero-blob" style="width:280px;height:280px;bottom:-60px;left:-80px;background:rgba(var(--accent-rgb),0.08)" aria-hidden="true"></div>
            <div class="el-hero-inner">
                <div class="el-hero-copy">
                    <h1>Ready to Return Your Laptop/Monitor</h1>
                    <p class="el-sub">Just provide us with your employee location, company address, and device type.</p>
                    @if ($isTenantPortal)
                        <a href="{{ route('create.singleorder.notauth') }}" class="el-cta">
                            Get Started
                            <span class="el-cta-arrow" aria-hidden="true">→</span>
                        </a>
                    @else
                        <button type="button" class="el-cta" id="el-get-started-scroll">
                            Get Started
                            <span class="el-cta-arrow" aria-hidden="true">→</span>
                        </button>
                    @endif
                </div>
                <div class="el-illus-wrap" aria-hidden="true">
                    <svg class="el-illus" viewBox="0 0 220 170" width="280" height="216">
                        <defs>
                            <linearGradient id="el-illus-grad" x1="0%" y1="0%" x2="100%" y2="100%">
                                <stop offset="0%" stop-color="currentColor" stop-opacity="0.95" />
                                <stop offset="100%" stop-color="currentColor" stop-opacity="0.35" />
                            </linearGradient>
                        </defs>
                        <rect x="28" y="24" width="164" height="118" rx="14" fill="none" stroke="url(#el-illus-grad)" stroke-width="3" />
                        <rect x="48" y="42" width="124" height="72" rx="6" fill="currentColor" fill-opacity="0.12" />
                        <path d="M72 118h76" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-opacity="0.45" fill="none" />
                        <rect x="54" y="138" width="112" height="18" rx="6" fill="currentColor" fill-opacity="0.22" />
                    </svg>
                </div>
            </div>
        </section>

        <section class="el-hiw" id="how-it-works" aria-labelledby="el-hiw-title">
            <div class="el-hiw-inner">
                <h2 id="el-hiw-title">How It Works</h2>
                <p class="el-hiw-sub">A simple three-step process from request to secure return.</p>
                <div class="el-steps">
                    <article class="el-step-card" data-el-step>
                        <svg class="el-step-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="m7.5 4.27 9 5.15" />
                            <path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z" />
                            <path d="m3.3 7 8.7 5 8.7-5" />
                            <path d="M12 22V12" />
                        </svg>
                        <div class="el-step-badge" aria-hidden="true">1</div>
                        <h3>Start with a laptop return box</h3>
                        <p>Provide details about your company, employee address, and the type of IT asset you need back. We prepare an easy-to-pack laptop return box complete with pre-paid shipping label and packaging material.</p>
                    </article>
                    <article class="el-step-card" data-el-step>
                        <svg class="el-step-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M14 18V6a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2v11a1 1 0 0 0 1 1h2" />
                            <path d="M15 18H9" />
                            <path d="M19 18h2a1 1 0 0 0 1-1v-3.65a1 1 0 0 0-.22-.624l-3.48-4.35A1 1 0 0 0 17.52 8H14" />
                            <circle cx="17" cy="18" r="2" />
                            <circle cx="7" cy="18" r="2" />
                        </svg>
                        <div class="el-step-badge" aria-hidden="true">2</div>
                        <h3>Box ships to your employee</h3>
                        <p>In 24 hours, we ship the laptop return box to your employee’s. Your employee packs, and sends the box back to you via the nearest UPS/USPS. We automated reminder , your employee will receive reminder.</p>
                    </article>
                    <article class="el-step-card" data-el-step>
                        <svg class="el-step-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M20 13c0 5-3.5 7.5-7.66 8.95a1 1 0 0 1-.67-.01C7.5 20.5 4 18 4 13V6a1 1 0 0 1 1-1c2 0 4.5-1.2 6.24-2.72a1.17 1.17 0 0 1 1.52 0C14.51 3.81 17 5 19 5a1 1 0 0 1 1 1z" />
                            <path d="m9 12 2 2 4-4" />
                        </svg>
                        <div class="el-step-badge" aria-hidden="true">3</div>
                        <h3>Your laptop returns safely</h3>
                        <p>With our online tracking system, we send you intermittent email alerts about your laptop shipping box in transit. You’ll be able to locate the equipment until you receive it safely at your doorstep. Seamless, and easy!</p>
                    </article>
                </div>
            </div>
        </section>
    </div>
@endsection

@section('replace_footer')
    <footer class="el-footer el-root" style="{{ $elStyle }}">
        <div class="el-footer-inner">
            <div class="el-footer-brand">
                <div class="el-brand @if(!$hasLogo) el-brand--ready @endif" data-el-brand-footer style="min-height:2.25rem">
                    @if ($hasLogo)
                        <div class="el-logo-skel" style="width:120px;height:36px" aria-hidden="true"></div>
                        <img
                            src="{{ $logoUrl }}?v={{ time() }}"
                            alt=""
                            class="el-logo-img"
                            style="max-height:36px"
                            width="160"
                            height="40"
                            loading="lazy"
                            onload="this.closest('[data-el-brand-footer]')?.classList.add('el-brand--ready')"
                            onerror="this.closest('[data-el-brand-footer]')?.classList.add('el-brand--ready'); this.style.display='none';"
                        >
                    @else
                        <span class="el-brand-text" style="font-size:1rem">{{ $brandName }}</span>
                    @endif
                </div>
                <p class="el-footer-copy">© {{ date('Y') }} {{ $brandName }}. All rights reserved.</p>
            </div>
            <nav class="el-footer-nav" aria-label="Footer">
                @guest
                    <a href="{{ route('login') }}">Log in</a>
                @endguest
                @auth
                    <a href="{{ route('saas.dashboard') }}">Dashboard</a>
                @endauth
            </nav>
        </div>
    </footer>
@endsection

@push('other-scripts')
    <script>
        (function () {
            var scrollBtn = document.getElementById('el-get-started-scroll');
            if (scrollBtn) {
                scrollBtn.addEventListener('click', function () {
                    var t = document.getElementById('how-it-works');
                    if (t) t.scrollIntoView({ behavior: 'smooth', block: 'start' });
                });
            }
            var cards = document.querySelectorAll('[data-el-step]');
            cards.forEach(function (el, i) {
                el.style.setProperty('--stagger', (i * 110) + 'ms');
            });
            if (!('IntersectionObserver' in window)) {
                cards.forEach(function (el) { el.classList.add('el-step-card--in'); });
                return;
            }
            var io = new IntersectionObserver(
                function (entries) {
                    entries.forEach(function (entry) {
                        if (entry.isIntersecting) {
                            entry.target.classList.add('el-step-card--in');
                            io.unobserve(entry.target);
                        }
                    });
                },
                { root: null, rootMargin: '0px 0px -8% 0px', threshold: 0.12 }
            );
            cards.forEach(function (el) { io.observe(el); });
        })();
    </script>
@endpush
