<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserPreferenceController extends Controller
{
    /**
     * Guarda el tema del panel admin (claro/oscuro) en el usuario autenticado.
     */
    public function updateTheme(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'theme' => 'required|string|in:light,dark',
        ]);

        $user = $request->user();
        $user->admin_theme = $validated['theme'];
        $user->save();

        return response()->json(['ok' => true, 'theme' => $user->admin_theme]);
    }
}
