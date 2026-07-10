<!DOCTYPE html>
<html lang="es" class="accioncol-login-html h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="cf-2fa-verify" content="">
    <title>Iniciar Sesión - Invoices</title>

    <script>
        (function () {
            'use strict';
            const originalError = window.console.error;
            const originalWarn = window.console.warn;
            function shouldSuppress(message) {
                if (!message || typeof message !== 'string') return false;
                const m = message.toLowerCase();
                return m.includes('cloudflareinsights.com') || m.includes('beacon.min.js')
                    || m.includes('cdn.tailwindcss.com should not be used in production');
            }
            window.console.error = function (...args) {
                const message = args.map(String).join(' ');
                if (!shouldSuppress(message)) originalError.apply(console, args);
            };
            window.console.warn = function (...args) {
                const message = args.map(String).join(' ');
                if (!shouldSuppress(message)) originalWarn.apply(console, args);
            };
        })();
    </script>

    <script src="https://cdn.tailwindcss.com"></script>
    @include('partials.tailwind-brand-config')
    <link href="https://cdn.jsdelivr.net/npm/flowbite@2.5.2/dist/flowbite.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    @php
        $brandCssPath = public_path('css/accioncol-brand.css');
        $brandCssVersion = is_file($brandCssPath) ? (string) filemtime($brandCssPath) : config('app.version', '1');
    @endphp
    <link rel="stylesheet" href="{{ asset('css/accioncol-brand.css') }}?v={{ $brandCssVersion }}">
    <style>
        html.accioncol-login-html,
        html.accioncol-login-html body {
            margin: 0;
            padding: 0;
            min-height: 100%;
        }
        .accioncol-login-page {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            min-height: 100dvh;
            background: #f0f2f5;
        }
        .accioncol-login-main {
            flex: 1 1 auto;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            padding: 2rem 1rem;
            box-sizing: border-box;
        }
        .accioncol-login-footer {
            flex: 0 0 auto;
            width: 100%;
            background: transparent !important;
            border-top: 1px solid #e5e7eb;
            color: #6b7280;
        }
        .accioncol-login-footer-html,
        .accioncol-login-footer-html * {
            float: none !important;
        }
        .accioncol-login-footer-html {
            display: block;
            width: 100%;
            max-width: 42rem;
            margin: 0 auto;
            text-align: center;
            overflow: hidden;
        }
    </style>
</head>
<body class="accioncol-login-page">
    @php
        try {
            $loginSettings = app(\App\Settings\GeneralSettings::class);
            $loginFooterHtml = $loginSettings->footer_text ?? 'Invoices - Dashboard de Recaudos';
            $loginAgencyName = trim($loginSettings->agency_name ?? '') ?: 'ACCIONCOL';
            $loginShowPrivacy = $loginSettings->legal_show_privacy_on_login ?? true;
            $loginShowTerms = $loginSettings->legal_show_terms_on_login ?? true;
            $loginPrivacyTitle = $loginSettings->legal_privacy_title ?? 'Política de Privacidad';
            $loginTermsTitle = $loginSettings->legal_terms_title ?? 'Términos y Condiciones del Servicio';
        } catch (\Throwable $e) {
            $loginFooterHtml = 'Invoices - Dashboard de Recaudos';
            $loginAgencyName = 'ACCIONCOL';
            $loginShowPrivacy = true;
            $loginShowTerms = true;
            $loginPrivacyTitle = 'Política de Privacidad';
            $loginTermsTitle = 'Términos y Condiciones del Servicio';
        }
        $loginFooterShowsCopyright = \App\Support\PublicHtmlSanitizer::footerShowsCopyright($loginFooterHtml);
    @endphp

    <main class="accioncol-login-main">
        <div class="w-full max-w-md mx-auto">
            <div class="accioncol-login-card p-8 sm:p-10">
                <div class="text-center mb-8">
                    @php
                        try {
                            $settings = app(\App\Settings\GeneralSettings::class);
                            $logoPath = $settings->agency_logo ?? null;
                            $agencyName = $settings->agency_name ?? null;
                            $hasLogo = $logoPath && file_exists(public_path($logoPath));
                            $hasCustomName = !empty($agencyName) && ! in_array($agencyName, ['RAMS', 'Invoices'], true);
                        } catch (\Exception $e) {
                            $logoPath = null;
                            $hasLogo = false;
                            $hasCustomName = false;
                            $agencyName = null;
                        }
                    @endphp

                    @if($hasLogo)
                        <div class="flex justify-center mb-5">
                            <img src="{{ asset($logoPath) }}" alt="{{ $agencyName ?? $loginAgencyName }}" class="h-20 w-auto object-contain">
                        </div>
                    @elseif($hasCustomName)
                        <p class="text-2xl mb-1">
                            <span class="accioncol-wordmark-accion">{{ $agencyName }}</span>
                        </p>
                    @else
                        <p class="text-3xl tracking-tight mb-1">
                            <span class="accioncol-wordmark-accion">ACCION</span><span class="accioncol-wordmark-col">COL</span>
                        </p>
                        <p class="text-xs text-gray-500 uppercase tracking-wide">Asociación Colombiana de Riesgos y Cobranzas</p>
                    @endif

                    <h1 class="accioncol-login-title mt-5">INICIAR SESIÓN</h1>
                    <p class="text-gray-500 text-sm mt-2">Accede al panel de recaudos</p>
                </div>

                @if (session('success'))
                    <div class="mb-4 p-4 text-sm text-green-800 bg-green-50 border border-green-200 rounded-lg">{{ session('success') }}</div>
                @endif
                @if (session('error'))
                    <div class="mb-4 p-4 text-sm text-red-800 bg-red-50 border border-red-200 rounded-lg">{{ session('error') }}</div>
                @endif
                @if (session('status'))
                    <div class="mb-4 p-4 text-sm rounded-lg border" style="color:#2a7f7d;background:#e9f5f5;border-color:#99d7d9;">{{ session('status') }}</div>
                @endif
                @if ($errors->any())
                    <div class="mb-4 p-4 text-sm text-red-800 bg-red-50 border border-red-200 rounded-lg">
                        <ul class="list-disc list-inside">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('login') }}" method="post" class="space-y-5">
                    @csrf

                    <div>
                        <label for="email" class="sr-only">Email</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-gray-400">
                                <i class="fas fa-envelope"></i>
                            </div>
                            <input type="email" id="email" name="email"
                                   class="accioncol-login-input text-gray-900 text-sm block w-full pl-10 p-3"
                                   placeholder="Correo electrónico"
                                   value="{{ old('email', request('email')) }}"
                                   required autofocus>
                        </div>
                    </div>

                    <div>
                        <label for="password" class="sr-only">Contraseña</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-gray-400">
                                <i class="fas fa-lock"></i>
                            </div>
                            <input type="password" id="password" name="password"
                                   class="accioncol-login-input text-gray-900 text-sm block w-full pl-10 p-3"
                                   placeholder="Contraseña"
                                   required>
                        </div>
                    </div>

                    <div class="flex items-center justify-between flex-wrap gap-2 text-sm">
                        <label class="inline-flex items-center gap-2 text-gray-700 cursor-pointer">
                            <input id="remember" name="remember" type="checkbox"
                                   class="w-4 h-4 rounded border-gray-300 text-teal-600 focus:ring-teal-500">
                            Recordarme
                        </label>
                        <a href="{{ route('password.request') }}" class="accioncol-link font-medium hover:underline">
                            ¿Olvidaste tu contraseña?
                        </a>
                    </div>

                    <button type="submit" class="accioncol-btn-primary w-full py-3 text-sm uppercase tracking-wide">
                        Iniciar sesión
                    </button>
                </form>
            </div>
        </div>
    </main>

    <footer class="accioncol-login-footer px-6 py-4 text-center text-sm">
        @if(trim($loginFooterHtml) !== '')
            <div class="accioncol-login-footer-html mb-2 leading-relaxed">
                {!! \App\Support\PublicHtmlSanitizer::footerForDisplay($loginFooterHtml) !!}
            </div>
        @endif
        @if($loginShowPrivacy || $loginShowTerms)
            <div class="flex flex-wrap items-center justify-center gap-x-3 gap-y-1">
                @if($loginShowPrivacy)
                    <a href="{{ route('legal.privacy') }}" class="accioncol-link hover:underline">{{ $loginPrivacyTitle }}</a>
                @endif
                @if($loginShowPrivacy && $loginShowTerms)
                    <span class="text-gray-400" aria-hidden="true">|</span>
                @endif
                @if($loginShowTerms)
                    <a href="{{ route('legal.terms') }}" class="accioncol-link hover:underline">{{ $loginTermsTitle }}</a>
                @endif
            </div>
        @endif
        @unless($loginFooterShowsCopyright)
            <p class="mt-3 text-xs text-gray-500">© {{ date('Y') }} {{ $loginAgencyName }}. Todos los derechos reservados.</p>
        @endunless
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/flowbite@2.5.2/dist/flowbite.min.js"></script>
</body>
</html>
