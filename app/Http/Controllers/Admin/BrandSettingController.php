<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateBrandSettingRequest;
use App\Models\Setting;
use App\Services\PermissionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class BrandSettingController extends Controller
{
    public function edit(): View
    {
        if (! app(PermissionService::class)->userHasPermission('settings_brand', 'view')) {
            abort(403);
        }

        return view('admin.brand-settings.edit', [
            'setting' => Setting::current(),
        ]);
    }

    public function update(UpdateBrandSettingRequest $request): RedirectResponse
    {
        $setting = Setting::current();
        $data = $request->safe()->except([
            'logo',
            'treasurer_signature',
            'remove_logo',
            'remove_treasurer_signature',
        ]);

        $setting->fill($data);

        if ($request->boolean('remove_logo') && $setting->logo_path) {
            Storage::disk('public')->delete($setting->logo_path);
            $setting->logo_path = null;
        }

        if ($request->boolean('remove_treasurer_signature') && $setting->treasurer_signature_path) {
            Storage::disk('public')->delete($setting->treasurer_signature_path);
            $setting->treasurer_signature_path = null;
        }

        if ($request->hasFile('logo')) {
            if ($setting->logo_path) {
                Storage::disk('public')->delete($setting->logo_path);
            }
            $setting->logo_path = $request->file('logo')->store('brand', 'public');
        }

        if ($request->hasFile('treasurer_signature')) {
            if ($setting->treasurer_signature_path) {
                Storage::disk('public')->delete($setting->treasurer_signature_path);
            }
            $setting->treasurer_signature_path = $request->file('treasurer_signature')->store('brand', 'public');
        }

        $setting->save();

        return redirect()
            ->route('admin.brand-settings.edit')
            ->with('success', 'Configuración de marca blanca actualizada.');
    }
}
