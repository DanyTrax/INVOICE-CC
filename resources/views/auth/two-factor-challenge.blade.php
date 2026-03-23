<!DOCTYPE html>
<html lang="es" class="h-full bg-gray-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verificación en dos pasos - RAMS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background: linear-gradient(135deg, #0f766e 0%, #14b8a6 100%); }
    </style>
</head>
<body class="h-full flex items-center justify-center p-4">
    <div class="w-full max-w-md space-y-4">
        <div class="bg-white rounded-lg shadow-xl p-8">
            <h1 class="text-xl font-bold text-gray-900 mb-2">
                <i class="fas fa-shield-halved text-teal-600 mr-2"></i> Verificación en dos pasos
            </h1>
            <p class="text-sm text-gray-600 mb-6">Introduce el código de 6 dígitos de tu app de autenticación o un código de respaldo.</p>

            @if (session('status'))
                <div class="mb-4 p-3 text-sm text-teal-800 bg-teal-50 border border-teal-200 rounded-lg">
                    {{ session('status') }}
                </div>
            @endif
            @if (session('error'))
                <div class="mb-4 p-3 text-sm text-red-800 bg-red-50 border border-red-200 rounded-lg">
                    {{ session('error') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="mb-4 p-3 text-sm text-red-800 bg-red-50 border border-red-200 rounded-lg">
                    @foreach ($errors->all() as $error)
                        <p>{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            <form action="{{ route('two-factor.verify') }}" method="post" class="space-y-4">
                @csrf
                <div>
                    <label for="code" class="block mb-2 text-sm font-medium text-gray-900">Código</label>
                    <input type="text" name="code" id="code" autocomplete="one-time-code" required
                           class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-teal-500 focus:border-teal-500 block w-full p-2.5 font-mono tracking-wider"
                           placeholder="000000 o código de respaldo">
                </div>
                <button type="submit" class="w-full text-white bg-teal-700 hover:bg-teal-800 font-medium rounded-lg text-sm px-5 py-2.5">
                    Continuar
                </button>
            </form>
        </div>

        <div class="bg-white rounded-lg shadow-xl p-6">
            <p class="text-sm font-medium text-gray-900 mb-2">¿Perdiste el acceso al autenticador?</p>
            <p class="text-xs text-gray-600 mb-4">Confirma tu correo y te enviaremos un enlace para desactivar el 2FA. Luego podrás iniciar sesión con tu contraseña.</p>
            <form action="{{ route('two-factor.recovery.email') }}" method="post" class="space-y-3">
                @csrf
                <input type="email" name="email" required placeholder="Tu correo de cuenta"
                       class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg block w-full p-2.5">
                @error('email')
                    <p class="text-sm text-red-600">{{ $message }}</p>
                @enderror
                <button type="submit" class="w-full text-teal-800 bg-teal-50 border border-teal-200 hover:bg-teal-100 font-medium rounded-lg text-sm px-5 py-2.5">
                    Enviar enlace de recuperación
                </button>
            </form>
        </div>

        <p class="text-center">
            <a href="{{ route('login') }}" class="text-sm text-white hover:underline">Cancelar e ir al inicio de sesión</a>
        </p>
    </div>
</body>
</html>
