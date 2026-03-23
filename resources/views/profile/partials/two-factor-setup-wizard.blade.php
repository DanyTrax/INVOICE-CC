{{--
    Asistente modal para activar 2FA (Alpine).
    variant: full = botón estándar (perfil); banner = tarjeta sugerencia en inicio.
    Requiere: $routePrefix ('admin' | 'portal')
--}}
@php
    $rp = $routePrefix ?? 'admin';
    $variant = $variant ?? 'full';
    $urls = [
        'start' => route($rp . '.profile.two-factor.start'),
        'confirm' => route($rp . '.profile.two-factor.confirm'),
        'cancel' => route($rp . '.profile.two-factor.cancel'),
    ];
@endphp

<div x-data="twoFactorWizardModal(@js($urls))"
     @keydown.escape.window="open && closeModal()"
     class="{{ $variant === 'banner' ? 'mb-6' : '' }}">

    @if (session('two_factor_qr'))
        <div class="mb-6 p-4 border border-amber-200 rounded-lg bg-amber-50 text-sm text-amber-900">
            Tienes una configuración de 2FA pendiente desde otra pestaña. Usa el asistente o
            <form action="{{ route($rp . '.profile.two-factor.cancel') }}" method="POST" class="inline">
                @csrf
                <button type="submit" class="underline font-medium">cancela la configuración</button>
            </form>.
        </div>
    @endif

    @if ($variant === 'banner')
        <div class="flex flex-col gap-4 rounded-xl border border-teal-200 bg-gradient-to-br from-teal-50 via-white to-gray-50/80 p-4 shadow-sm ring-1 ring-teal-100 sm:flex-row sm:items-center sm:justify-between sm:gap-6">
            <div class="flex gap-3 min-w-0">
                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-teal-100 text-teal-700">
                    <i class="fas fa-shield-halved text-xl"></i>
                </div>
                <div class="min-w-0">
                    <h3 class="text-base font-bold text-gray-900">Activa la verificación en dos pasos (2FA)</h3>
                    <p class="mt-1 text-sm text-gray-600">
                        Añade una capa extra de seguridad a tu cuenta con una app de autenticación (Google Authenticator, Microsoft Authenticator, etc.).
                    </p>
                </div>
            </div>
            <div class="flex shrink-0 flex-wrap gap-2 sm:flex-col sm:items-stretch">
                <button type="button" @click="openModal()"
                        class="inline-flex items-center justify-center gap-2 rounded-lg bg-teal-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-teal-700">
                    <i class="fas fa-mobile-screen-button"></i>
                    Configurar 2FA
                </button>
            </div>
        </div>
    @else
        <button type="button" @click="openModal()"
                class="px-4 py-2 bg-teal-600 text-white rounded-lg hover:bg-teal-700 text-sm inline-flex items-center gap-2">
            <i class="fas fa-mobile-screen-button"></i>
            Iniciar configuración de 2FA
        </button>
    @endif

    {{-- Modal asistente --}}
    <div x-show="open"
         x-cloak
         class="fixed inset-0 z-[100] overflow-y-auto"
         aria-modal="true" role="dialog">
        <div class="flex min-h-full items-center justify-center p-4">
            <div class="fixed inset-0 bg-gray-900/60 transition-opacity" @click="closeModal()"></div>
            <div class="relative w-full max-w-lg rounded-xl bg-white shadow-2xl border border-gray-200"
                 @click.stop>
                <div class="flex items-center justify-between border-b border-gray-100 px-5 py-4">
                    <div>
                        <p class="text-xs font-medium uppercase tracking-wide text-teal-600" x-text="'Paso ' + step + ' de 5'"></p>
                        <h3 class="text-lg font-bold text-gray-900" x-text="stepHeading()"></h3>
                    </div>
                    <button type="button" @click="closeModal()" class="rounded-lg p-2 text-gray-400 hover:bg-gray-100 hover:text-gray-600">
                        <i class="fas fa-times text-lg"></i>
                    </button>
                </div>

                <div class="px-5 py-4 max-h-[70vh] overflow-y-auto">
                    <div x-show="errorMsg" x-transition
                         class="mb-4 rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-800"
                         x-text="errorMsg"></div>

                    {{-- Paso 1: Intro --}}
                    <div x-show="step === 1" x-transition>
                        <p class="text-sm text-gray-600 mb-4">Te guiaremos para vincular una app de autenticación a tu cuenta.</p>
                        <ul class="list-disc list-inside text-sm text-gray-700 space-y-2 mb-6">
                            <li>Instala una app como <strong>Google Authenticator</strong>, <strong>Microsoft Authenticator</strong> o similar en tu móvil.</li>
                            <li>Generaremos un código QR y un secreto por si no puedes escanear.</li>
                            <li>Introducirás un código de 6 dígitos para confirmar.</li>
                            <li>Al final te mostraremos <strong>códigos de respaldo</strong> (guárdalos en un lugar seguro).</li>
                        </ul>
                        <button type="button" @click="beginSetup()"
                                class="w-full rounded-lg bg-teal-600 px-4 py-2.5 text-sm font-medium text-white hover:bg-teal-700">
                            Comenzar
                        </button>
                    </div>

                    {{-- Paso 2: Cargando --}}
                    <div x-show="step === 2" x-transition class="flex flex-col items-center justify-center py-10">
                        <i class="fas fa-circle-notch fa-spin text-3xl text-teal-600 mb-4"></i>
                        <p class="text-sm text-gray-600">Generando código QR…</p>
                    </div>

                    {{-- Paso 3: QR --}}
                    <div x-show="step === 3" x-transition>
                        <p class="text-sm text-gray-600 mb-4">Abre la app de autenticación y escanea este código. Si no puedes, copia el secreto manualmente.</p>
                        <div class="flex flex-col sm:flex-row gap-4 items-start mb-4">
                            <div class="rounded-lg border border-gray-200 bg-white p-2 mx-auto sm:mx-0">
                                <img :src="qrDataUri" alt="QR 2FA" class="h-44 w-44 object-contain" x-show="qrDataUri">
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-xs font-medium text-gray-500 mb-1">Secreto (texto)</p>
                                <code class="block break-all rounded border border-gray-200 bg-gray-50 p-2 text-xs font-mono text-gray-800" x-text="secretText"></code>
                                <button type="button" @click="copySecret()"
                                        class="mt-2 text-sm font-medium text-teal-700 hover:underline">
                                    <i class="fas fa-copy mr-1"></i> Copiar secreto
                                </button>
                            </div>
                        </div>
                        <p class="text-sm text-gray-600 mb-4">Cuando veas el código de 6 dígitos que cambia en la app, pulsa continuar.</p>
                        <button type="button" @click="step = 4; errorMsg = ''"
                                class="w-full rounded-lg bg-teal-600 px-4 py-2.5 text-sm font-medium text-white hover:bg-teal-700">
                            Continuar
                        </button>
                    </div>

                    {{-- Paso 4: Código TOTP --}}
                    <div x-show="step === 4" x-transition>
                        <p class="text-sm text-gray-600 mb-4">Escribe el código de <strong>6 dígitos</strong> que muestra la app para tu cuenta.</p>
                        <input type="text" x-model="codeInput" inputmode="numeric" autocomplete="one-time-code" maxlength="8"
                               placeholder="000000"
                               class="mb-4 w-full rounded-lg border border-gray-300 px-3 py-2.5 text-center text-2xl font-mono tracking-[0.3em] focus:border-teal-500 focus:ring-teal-500"
                               @keyup.enter="submitCode()">
                        <button type="button" @click="submitCode()" :disabled="loading"
                                class="w-full rounded-lg bg-teal-600 px-4 py-2.5 text-sm font-medium text-white hover:bg-teal-700 disabled:opacity-50">
                            <span x-show="!loading">Verificar y activar</span>
                            <span x-show="loading"><i class="fas fa-circle-notch fa-spin mr-2"></i> Verificando…</span>
                        </button>
                        <button type="button" @click="step = 3; errorMsg = ''"
                                class="mt-3 w-full text-sm text-gray-500 hover:text-gray-700">
                            ← Volver al código QR
                        </button>
                    </div>

                    {{-- Paso 5: Códigos de respaldo --}}
                    <div x-show="step === 5" x-transition>
                        <p class="text-sm text-gray-600 mb-4">El segundo factor ya está activo. Estos <strong>códigos de respaldo</strong> solo se muestran una vez; guárdalos en un lugar seguro (si pierdes el teléfono, podrás usarlos para entrar).</p>
                        <ul class="mb-6 space-y-1.5 rounded-lg border border-amber-200 bg-amber-50 p-4 font-mono text-sm text-amber-950">
                            <template x-for="(c, i) in recoveryCodes" :key="i">
                                <li x-text="c"></li>
                            </template>
                        </ul>
                        <button type="button" @click="finishModal()"
                                class="w-full rounded-lg bg-teal-600 px-4 py-2.5 text-sm font-medium text-white hover:bg-teal-700">
                            Entendido, cerrar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@once
    @push('scripts')
        <script>
            function twoFactorWizardModal(urls) {
                return {
                    urls,
                    open: false,
                    step: 1,
                    loading: false,
                    errorMsg: '',
                    qrDataUri: '',
                    secretText: '',
                    codeInput: '',
                    recoveryCodes: [],
                    sessionPending: false,
                    finished: false,
                    stepHeading() {
                        const t = {
                            1: 'Configurar 2FA',
                            2: 'Preparando…',
                            3: 'Escanear código QR',
                            4: 'Confirmar código',
                            5: 'Códigos de respaldo',
                        };
                        return t[this.step] || '';
                    },
                    csrf() {
                        const m = document.querySelector('meta[name="csrf-token"]');
                        return m ? m.getAttribute('content') : '';
                    },
                    async jsonFetch(url, body = {}) {
                        const r = await fetch(url, {
                            method: 'POST',
                            credentials: 'same-origin',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': this.csrf(),
                                'X-Requested-With': 'XMLHttpRequest',
                            },
                            body: Object.keys(body).length ? JSON.stringify(body) : '{}',
                        });
                        const data = await r.json().catch(() => ({}));
                        if (!r.ok) {
                            let msg = data.message || 'Ha ocurrido un error.';
                            if (data.errors) {
                                const first = Object.values(data.errors).flat()[0];
                                if (first) msg = first;
                            }
                            throw new Error(msg);
                        }
                        return data;
                    },
                    openModal() {
                        this.errorMsg = '';
                        this.step = 1;
                        this.qrDataUri = '';
                        this.secretText = '';
                        this.codeInput = '';
                        this.recoveryCodes = [];
                        this.sessionPending = false;
                        this.finished = false;
                        this.open = true;
                    },
                    async closeModal() {
                        if (this.sessionPending && !this.finished) {
                            try {
                                await this.jsonFetch(this.urls.cancel, {});
                            } catch (e) { /* ignore */ }
                        }
                        this.sessionPending = false;
                        this.open = false;
                        this.step = 1;
                    },
                    async beginSetup() {
                        this.errorMsg = '';
                        this.step = 2;
                        try {
                            const data = await this.jsonFetch(this.urls.start, {});
                            this.qrDataUri = data.qr_data_uri;
                            this.secretText = data.secret;
                            this.sessionPending = true;
                            this.step = 3;
                        } catch (e) {
                            this.errorMsg = e.message || 'No se pudo generar el código.';
                            this.step = 1;
                        }
                    },
                    copySecret() {
                        if (!this.secretText) return;
                        navigator.clipboard.writeText(this.secretText).then(() => {
                            alert('Secreto copiado al portapapeles');
                        }).catch(() => {});
                    },
                    async submitCode() {
                        this.errorMsg = '';
                        const clean = String(this.codeInput || '').replace(/\D/g, '');
                        if (clean.length !== 6) {
                            this.errorMsg = 'Introduce exactamente 6 dígitos.';
                            return;
                        }
                        this.loading = true;
                        try {
                            const data = await this.jsonFetch(this.urls.confirm, { code: clean });
                            this.recoveryCodes = data.recovery_codes || [];
                            this.sessionPending = false;
                            this.finished = true;
                            this.step = 5;
                        } catch (e) {
                            this.errorMsg = e.message || 'Código no válido.';
                        } finally {
                            this.loading = false;
                        }
                    },
                    finishModal() {
                        this.open = false;
                        window.location.reload();
                    },
                };
            }
        </script>
    @endpush
@endonce
