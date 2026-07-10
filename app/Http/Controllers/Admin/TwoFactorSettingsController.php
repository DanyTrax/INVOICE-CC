<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\PermissionService;
use App\Settings\GeneralSettings;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TwoFactorSettingsController extends Controller
{
    public function edit(): View
    {
        if (! app(PermissionService::class)->userHasPermission('settings_system', 'view')) {
            abort(403);
        }

        $settings = app(GeneralSettings::class);

        $usersWithTwoFactor = User::query()
            ->whereNotNull('two_factor_confirmed_at')
            ->whereNotNull('two_factor_secret')
            ->count();

        return view('admin.two-factor-settings.edit', [
            'enabled' => (bool) $settings->two_factor_system_enabled,
            'usersWithTwoFactor' => $usersWithTwoFactor,
            'totalUsers' => User::query()->whereDoesntHave('roles', fn ($q) => $q->where('name', 'client'))->count(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        if (! app(PermissionService::class)->userHasPermission('settings_system', 'edit')) {
            abort(403);
        }

        $validated = $request->validate([
            'two_factor_system_enabled' => 'nullable|boolean',
        ]);

        $settings = app(GeneralSettings::class);
        $settings->two_factor_system_enabled = $request->boolean('two_factor_system_enabled');
        $settings->save();

        return redirect()
            ->route('admin.two-factor-settings.edit')
            ->with('success', $settings->two_factor_system_enabled
                ? 'Verificación en dos pasos activada en el sistema.'
                : 'Verificación en dos pasos desactivada en el sistema.');
    }
}
