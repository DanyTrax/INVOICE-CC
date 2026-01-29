<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Establecer contraseña - RAMS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-md bg-white rounded-lg shadow-lg p-6">
        <h1 class="text-xl font-semibold text-gray-900 mb-2">
            <span class="text-teal-600">R</span> Establecer contraseña
        </h1>
        <p class="text-sm text-gray-600 mb-4">Ingresa tu nueva contraseña para acceder al sistema.</p>

        @if (session('status'))
            <div class="mb-4 p-3 text-sm text-green-800 bg-green-50 rounded-lg">{{ session('status') }}</div>
        @endif
        @if ($errors->any())
            <div class="mb-4 p-3 text-sm text-red-800 bg-red-50 rounded-lg">
                <ul class="list-disc list-inside">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('password.reset.store') }}" class="space-y-4">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">
            <input type="hidden" name="email" value="{{ $email }}">
            <div>
                <label for="email_display" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                <input type="text" id="email_display" value="{{ $email }}" readonly
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-50 text-gray-600">
            </div>
            <div>
                <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Nueva contraseña <span class="text-red-500">*</span></label>
                <input type="password" name="password" id="password" required minlength="8"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-teal-500 focus:border-teal-500"
                       autocomplete="new-password">
            </div>
            <div>
                <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">Confirmar contraseña <span class="text-red-500">*</span></label>
                <input type="password" name="password_confirmation" id="password_confirmation" required minlength="8"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-teal-500 focus:border-teal-500"
                       autocomplete="new-password">
            </div>
            <button type="submit" class="w-full px-4 py-2 bg-teal-600 text-white rounded-lg hover:bg-teal-700 font-medium">
                <i class="fas fa-key mr-2"></i> Establecer contraseña
            </button>
        </form>
        <p class="mt-4 text-center text-sm text-gray-500">
            <a href="{{ route('login') }}" class="text-teal-600 hover:underline">Volver al inicio de sesión</a>
        </p>
    </div>
</body>
</html>
