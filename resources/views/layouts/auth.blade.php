<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Login') — {{ $appName }}</title>

    {{-- Dynamic Favicon --}}
    @if($appFavicon)
        <link rel="icon" type="image/png" href="{{ $appFavicon }}">
    @endif

    <!-- Google Font -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

    @php
        $loginBg    = setting_image('login_bg_image');
        $loginLogo  = setting_image('login_logo');
        $themeClr   = $themeColor ?? '#4f46e5';
        $loginBgClr = setting('login_bg_color', $themeClr);
        $loginBtnClr = setting('login_button_color', $themeClr);
    @endphp

    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Inter', 'Segoe UI', sans-serif;
            min-height: 100vh;
            min-height: 100dvh;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow-x: hidden;
            overflow-y: auto;

            @if($loginBg)
                background: url('{{ $loginBg }}') center/cover no-repeat;
            @else
                background: linear-gradient(135deg, {{ $loginBgClr }} 0%, #1e293b 100%);
            @endif
        }

        /* Overlay for readability when bg image is set */
        body::before {
            content: '';
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,.45);
            z-index: 0;
            @if(!$loginBg) display: none; @endif
        }

        /* Floating shapes background (no bg image) */
        @if(!$loginBg)
        .auth-bg-shapes { position: fixed; inset: 0; overflow: hidden; z-index: 0; pointer-events: none; }
        .auth-bg-shapes span {
            position: absolute;
            border-radius: 50%;
            background: rgba(255,255,255,.06);
            animation: float 8s ease-in-out infinite;
        }
        .auth-bg-shapes span:nth-child(1) { width:300px;height:300px;top:-80px;left:-80px;animation-delay:0s; }
        .auth-bg-shapes span:nth-child(2) { width:200px;height:200px;bottom:60px;right:-60px;animation-delay:2s; }
        .auth-bg-shapes span:nth-child(3) { width:150px;height:150px;bottom:200px;left:10%;animation-delay:4s; }
        @keyframes float { 0%,100%{transform:translateY(0)} 50%{transform:translateY(-20px)} }
        @endif

        /* Auth container */
        .auth-wrap {
            position: relative;
            z-index: 1;
            width: 100%;
            max-width: 460px;
            padding: 20px;
        }

        /* Card */
        .auth-card {
            background: #fff;
            border-radius: 18px;
            box-shadow: 0 24px 70px rgba(15,23,42,.28);
            overflow: hidden;
            border: 1px solid rgba(255,255,255,.28);
        }

        /* Top accent bar */
        .auth-card-accent {
            height: 5px;
            background: linear-gradient(90deg, {{ $loginBtnClr }}, #818cf8);
        }

        .auth-card-body { padding: 38px 40px 34px; }

        /* Header */
        .auth-header { text-align: center; margin-bottom: 28px; }
        .auth-logo {
            width: 76px; height: 76px;
            border-radius: 16px;
            background: linear-gradient(135deg, {{ $loginBtnClr }}, #818cf8);
            display: inline-flex; align-items: center; justify-content: center;
            font-size: 32px; color: #fff;
            margin-bottom: 16px;
            box-shadow: 0 8px 24px color-mix(in srgb, {{ $loginBtnClr }} 40%, transparent);
        }
        .auth-logo.has-image {
            width: min(230px, 78vw);
            height: auto;
            min-height: 76px;
            padding: 14px 18px;
            background: #fff;
            border: 1px solid #e2e8f0;
            box-shadow: 0 12px 28px rgba(15,23,42,.08);
        }
        .auth-logo img { max-width: 190px; max-height: 58px; width: auto; height: auto; object-fit: contain; border-radius: 6px; }
        .auth-header h2 { font-size: 24px; font-weight: 800; color: #0f172a; margin-bottom: 6px; }
        .auth-header p  { color: #64748b; font-size: 14px; }

        /* Form */
        .form-label { font-size: 13px; font-weight: 600; color: #374151; margin-bottom: 6px; }
        .input-group-text {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-right: none;
            color: #94a3b8;
            border-radius: 10px 0 0 10px;
        }
        .form-control {
            border: 1px solid #e2e8f0;
            border-radius: 0 10px 10px 0;
            padding: 11px 14px;
            font-size: 14px;
            color: #1e293b;
            transition: all .2s;
        }
        .form-control:focus {
            border-color: {{ $loginBtnClr }};
            box-shadow: 0 0 0 3px color-mix(in srgb, {{ $loginBtnClr }} 15%, transparent);
            outline: none;
        }
        .input-group .btn {
            border-color: #e2e8f0;
            border-left: none;
            color: #64748b;
            border-radius: 0 10px 10px 0;
        }
        .input-group .btn:hover { background:#f8fafc;color:#0f172a; }
        .input-group .form-control.has-trailing-action { border-radius: 0; }
        .input-group:focus-within .input-group-text {
            border-color: {{ $loginBtnClr }};
        }

        /* Submit button */
        .btn-auth {
            width: 100%;
            padding: 13px;
            background: linear-gradient(135deg, {{ $loginBtnClr }}, #818cf8);
            border: none;
            border-radius: 10px;
            font-weight: 700;
            font-size: 15px;
            color: #fff;
            cursor: pointer;
            transition: all .3s;
            letter-spacing: .3px;
        }
        .btn-auth:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 24px color-mix(in srgb, {{ $loginBtnClr }} 40%, transparent);
        }
        .btn-auth:active { transform: translateY(0); }

        /* Links */
        .auth-link { color: {{ $loginBtnClr }}; text-decoration: none; font-weight: 600; }
        .auth-link:hover { text-decoration: underline; }

        /* Remember / forgot row */
        .form-check-input:checked {
            background-color: {{ $loginBtnClr }};
            border-color: {{ $loginBtnClr }};
        }

        /* Alert */
        .alert { border-radius: 10px; border: none; font-size: 13.5px; }

        /* Footer */
        .auth-footer {
            text-align: center;
            margin-top: 24px;
            color: rgba(255,255,255,.7);
            font-size: 12.5px;
        }
        .auth-footer a { color: rgba(255,255,255,.9); text-decoration: none; }
        .auth-footer a:hover { text-decoration: underline; }

        /* Company info strip */
        .auth-company-strip {
            background: #f8fafc;
            border-top: 1px solid #f1f5f9;
            padding: 12px 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-wrap: wrap;
            gap: 8px;
        }
        .auth-company-strip span { font-size: 12px; color: #94a3b8; }

        @media (max-width: 480px) {
            body {
                align-items: flex-start;
                justify-content: flex-start;
                padding: 12px 0;
            }
            .auth-bg-shapes span:nth-child(1) { width:190px;height:190px;top:-70px;left:-70px; }
            .auth-bg-shapes span:nth-child(2) { width:150px;height:150px;bottom:20px;right:-70px; }
            .auth-bg-shapes span:nth-child(3) { display:none; }
            .auth-wrap {
                max-width: none;
                width: 100%;
                padding: 10px 12px;
            }
            .auth-card {
                border-radius: 14px;
                box-shadow: 0 16px 40px rgba(15,23,42,.24);
            }
            .auth-card-body { padding: 22px 18px 20px; }
            .auth-header { margin-bottom: 20px; }
            .auth-logo {
                width: 62px;
                height: 62px;
                min-height: 62px;
                margin-bottom: 12px;
                border-radius: 14px;
            }
            .auth-logo.has-image {
                width: min(210px, 86vw);
                min-height: 62px;
                padding: 10px 12px;
            }
            .auth-logo img { max-width: 170px; max-height: 44px; }
            .auth-header h2 { font-size: 21px; margin-bottom: 4px; }
            .auth-header p { font-size: 13px; }
            .form-label { font-size: 12.5px; }
            .input-group-text { min-width: 42px; }
            .form-control { min-height: 44px; font-size: 14px; padding: 10px 12px; }
            .btn-auth { padding: 12px; }
            .auth-company-strip {
                padding: 10px 14px;
                gap: 5px 8px;
                text-align: center;
                line-height: 1.35;
            }
            .auth-company-strip img { max-width: 86px; }
            .auth-company-strip span { font-size: 11.5px; }
            .auth-footer {
                margin-top: 12px;
                padding: 0 10px 8px;
                font-size: 11.5px;
                line-height: 1.45;
            }
        }
    </style>
    @stack('styles')
</head>
<body>

    @if(!$loginBg)
    <div class="auth-bg-shapes">
        <span></span><span></span><span></span>
    </div>
    @endif

    <div class="auth-wrap">
        <div class="auth-card">
            <div class="auth-card-accent"></div>
            <div class="auth-card-body">

                <!-- Header -->
                <div class="auth-header">
                    <div class="auth-logo {{ ($loginLogo || $appLogo) ? 'has-image' : '' }}">
                        @if($loginLogo)
                            <img src="{{ $loginLogo }}" alt="{{ $appName }}">
                        @elseif($appLogo)
                            <img src="{{ $appLogo }}" alt="{{ $appName }}">
                        @else
                            <i class="bi bi-droplet-fill"></i>
                        @endif
                    </div>
                    <h2>{{ setting('login_welcome_text', 'Welcome Back!') }}</h2>
                    <p>{{ setting('login_page_title', 'Sign in to your account') }}</p>
                </div>

                @yield('content')

            </div>

            <!-- Company strip -->
            @if(setting('company_name') || setting('company_mobile') || setting('company_email'))
            <div class="auth-company-strip">
                @if($appLogo)
                    <img src="{{ $appLogo }}" alt="{{ $appName }}" style="height:20px;object-fit:contain;">
                @endif
                @if(setting('company_mobile'))
                    <span>·</span>
                    <span><i class="bi bi-telephone me-1"></i>{{ setting('company_mobile') }}</span>
                @endif
              
            </div>
            @endif
        </div>

        <!-- Footer text -->
        <div class="auth-footer">
            {{ setting('login_footer_text', '© ' . date('Y') . ' ' . ($appName ?? 'WaterProof CRM') . '. All rights reserved.') }}
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>
</html>
