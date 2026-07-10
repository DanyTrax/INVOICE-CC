<!DOCTYPE html>
<html lang="es" class="accioncol-login-html">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar contraseña - Invoices</title>
    <script src="https://cdn.tailwindcss.com"></script>
    @include('partials.tailwind-brand-config')
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/accioncol-brand.css') }}">
</head>
<body class="accioncol-login-page">
    <div class="accioncol-login-bg" aria-hidden="true"></div>

    <main class="accioncol-login-main">
        <div class="w-full max-w-md">
            <div class="accioncol-login-card p-8">
                <h1 class="text-2xl font-bold text-gray-900 mb-2">Recuperar contraseña</h1>
                <p class="text-sm text-gray-600 mb-6">Indica tu correo y te enviaremos un enlace para restablecerla.</p>

                @if (session('status'))
                    <div class="mb-4 p-4 text-sm text-teal-800 bg-teal-50 border border-teal-200 rounded-lg">
                        {{ session('status') }}
                    </div>
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

                <form action="{{ route('password.email') }}" method="post" class="space-y-4">
                    @csrf
                    <div>
                        <label for="email" class="block mb-2 text-sm font-medium text-gray-900">Correo electrónico</label>
                        <input type="email" name="email" id="email" value="{{ old('email') }}" required autofocus
                               class="accioncol-login-input text-gray-900 text-sm block w-full p-2.5">
                    </div>
                    <button type="submit" class="accioncol-btn-primary w-full py-2.5 text-sm">
                        Enviar enlace
                    </button>
                </form>

                <p class="mt-6 text-center text-sm text-gray-600">
                    <a href="{{ route('login') }}" class="accioncol-link hover:underline">Volver al inicio de sesión</a>
                </p>
            </div>
        </div>
    </main>
</body>
</html>
