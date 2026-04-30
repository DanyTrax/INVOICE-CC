<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @php
        try {
            $gs = app(\App\Settings\GeneralSettings::class);
            $agencyLabel = trim($gs->agency_name ?? '') ?: 'RAMS';
        } catch (\Throwable $e) {
            $agencyLabel = 'RAMS';
        }
    @endphp
    <title>Propósito — RAMS · {{ $agencyLabel }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 text-gray-800 antialiased">
    <div class="min-h-screen py-10 px-4">
        <div class="max-w-2xl mx-auto">
            <header class="text-center mb-10">
                <h1 class="text-3xl font-bold text-gray-900">RAMS</h1>
                <p class="text-lg text-gray-600 mt-2">Regulatory Affairs Management System</p>
                <p class="text-sm text-gray-500 mt-1">{{ $agencyLabel }}</p>
            </header>

            <main class="space-y-6 text-gray-700">
                <section class="bg-white rounded-lg border border-gray-200 p-6 shadow-sm">
                    <h2 class="text-xl font-semibold text-gray-900 mb-3">Propósito de la aplicación</h2>
                    <p class="mb-3">RAMS es el sistema de gestión regulatoria de <strong>{{ $agencyLabel }}</strong>. Permite a la empresa y a sus clientes:</p>
                    <ul class="list-disc pl-6 space-y-1 text-sm">
                        <li>Gestionar empresas (clientes), cotizaciones y solicitudes o procesos regulatorios.</li>
                        <li>Mantener checklists documentales y el seguimiento de trámites ante autoridades.</li>
                        <li>Organizar y subir documentos en Google Drive, integrado con la estructura de carpetas del servicio.</li>
                        <li>Consultar estados, líneas de tiempo y documentación asociada a cada solicitud.</li>
                    </ul>
                    <p class="mt-4 text-sm">El acceso a la aplicación está restringido a usuarios autorizados (administradores, especialistas y clientes según corresponda).</p>
                </section>

                <div class="flex flex-col sm:flex-row gap-3 justify-center items-center pt-2">
                    <a href="{{ route('login') }}" class="inline-flex items-center px-5 py-2.5 bg-teal-600 text-white font-medium rounded-lg hover:bg-teal-700">
                        Iniciar sesión
                    </a>
                    <a href="{{ route('legal.privacy') }}" class="text-sm text-teal-600 hover:text-teal-800 font-medium">Política de Privacidad</a>
                    <span class="hidden sm:inline text-gray-300">·</span>
                    <a href="{{ route('legal.terms') }}" class="text-sm text-teal-600 hover:text-teal-800 font-medium">Términos y Condiciones</a>
                </div>
            </main>

            <footer class="mt-12 text-center text-sm text-gray-500">
                <p>© {{ date('Y') }} {{ $agencyLabel }}. Todos los derechos reservados.</p>
            </footer>
        </div>
    </div>
</body>
</html>
