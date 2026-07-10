<!DOCTYPE html>
<html lang="es" class="accioncol-login-html">
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
    <link rel="stylesheet" href="{{ asset('css/accioncol-brand.css') }}">
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
    @endphp

    <div class="accioncol-login-bg" aria-hidden="true">
        <svg class="accioncol-login-waves" viewBox="0 0 1440 140" preserveAspectRatio="none">
            <path fill="currentColor" fill-opacity="0.35" d="M0,80 C240,120 480,20 720,60 C960,100 1200,40 1440,70 L1440,140 L0,140 Z"/>
            <path fill="currentColor" d="M0,100 C320,60 480,130 720,95 C960,60 1200,120 1440,90 L1440,140 L0,140 Z"/>
        </svg>
    </div>

    <main class="accioncol-login-main">
        <div class="w-full max-w-md">
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

    <footer class="accioncol-login-footer px-6 py-5 text-center text-sm">
        <div class="mb-2 leading-relaxed opacity-90">{!! nl2br(e($loginFooterHtml)) !!}</div>
        @if($loginShowPrivacy || $loginShowTerms)
            <div class="flex flex-wrap items-center justify-center gap-x-3 gap-y-1">
                @if($loginShowPrivacy)
                    <a href="{{ route('legal.privacy') }}">{{ $loginPrivacyTitle }}</a>
                @endif
                @if($loginShowPrivacy && $loginShowTerms)
                    <span class="opacity-40" aria-hidden="true">|</span>
                @endif
                @if($loginShowTerms)
                    <a href="{{ route('legal.terms') }}">{{ $loginTermsTitle }}</a>
                @endif
            </div>
        @endif
        <p class="mt-3 text-xs opacity-70">© {{ date('Y') }} {{ $loginAgencyName }}. Todos los derechos reservados.</p>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/flowbite@2.5.2/dist/flowbite.min.js"></script>
</body>
</html>
