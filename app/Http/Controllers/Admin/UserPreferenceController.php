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
            'theme' => 'required|string|in:light,dark,system',
        ]);

        $user = $request->user();
        $user->admin_theme = $validated['theme'];
        $user->save();

        return response()->json(['ok' => true, 'theme' => $user->admin_theme]);
    }

    /**
     * Guarda el tamaño de texto del panel admin (porcentaje sobre el tamaño base).
     */
    public function updateFontScale(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'scale' => 'required|integer|in:90,100,110,125',
        ]);

        $user = $request->user();
        $user->admin_ui_font_scale = (int) $validated['scale'];
        $user->save();

        return response()->json(['ok' => true, 'scale' => $user->admin_ui_font_scale]);
    }
}
