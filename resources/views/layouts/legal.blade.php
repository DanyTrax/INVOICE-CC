<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title') - Asesoría y Consultoría Doble Vía</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 text-gray-800 antialiased">
    <div class="min-h-screen py-8 px-4">
        <div class="max-w-3xl mx-auto">
            <header class="mb-8 border-b border-gray-200 pb-4">
                <h1 class="text-xl font-semibold text-gray-900">Asesoría y Consultoría Doble Vía</h1>
                <p class="text-sm text-gray-500 mt-1">RAMS - Sistema de Gestión Regulatoria</p>
            </header>
            <main class="prose prose-gray max-w-none">
                @yield('content')
            </main>
            <footer class="mt-12 pt-6 border-t border-gray-200 text-sm text-gray-500">
                <a href="{{ url('/') }}" class="text-teal-600 hover:text-teal-800">Volver al inicio</a>
            </footer>
        </div>
    </div>
</body>
</html>
