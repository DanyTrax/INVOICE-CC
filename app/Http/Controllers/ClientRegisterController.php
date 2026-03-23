<?php

namespace App\Http\Controllers;

use App\Models\CompanyInvite;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class ClientRegisterController extends Controller
{
    /**
     * Mostrar formulario de registro por invitación (link de unico uso).
     */
    public function show(Request $request)
    {
        $token = $request->query('token');
        if (! $token) {
            return redirect()->route('login')->with('error', 'Enlace de invitación no válido.');
        }

        $invite = CompanyInvite::where('token', $token)->with('company')->first();
        if (! $invite || ! $invite->isValid()) {
            return redirect()->route('login')->with('error', 'Este enlace ha expirado o ya fue utilizado.');
        }

        return view('client.register', [
            'invite' => $invite,
            'email' => $invite->email,
            'companyName' => $invite->company->name,
        ]);
    }

    /**
     * Procesar registro: crear usuario, vincular empresa, marcar invitación usada.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'token' => 'required|string|size:64',
            'name' => 'required|string|max:255',
            'email' => 'required|email',
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $invite = CompanyInvite::where('token', $validated['token'])->with('company')->first();
        if (! $invite || ! $invite->isValid()) {
            return redirect()->route('login')->with('error', 'Este enlace ha expirado o ya fue utilizado.');
        }
        if (strtolower($validated['email']) !== strtolower($invite->email)) {
            return back()->withErrors(['email' => 'El correo debe coincidir con la invitación.'])->withInput();
        }

        // Evitar duplicados: si ya existe user con ese email y esa company, redirigir a login
        $existing = User::where('email', $invite->email)
            ->whereHas('companies', function ($q) use ($invite) {
                $q->where('companies.id', $invite->company_id);
            })
            ->first();
        if ($existing) {
            $invite->markAsUsed();

            return redirect()->route('login')->with('success', 'Ya tienes cuenta. Inicia sesión.');
        }

        $user = User::create([
            'name' => $validated['name'],
            'email' => $invite->email,
            'password' => Hash::make($validated['password']),
            // Puede iniciar sesión; el portal muestra "pendiente de activación" hasta que un admin active.
            'is_active' => true,
            'client_status' => User::CLIENT_STATUS_PENDIENTE,
        ]);

        $user->companies()->attach($invite->company_id);
        $user->assignRole('client');
        $invite->markAsUsed();

        return redirect()->route('login')->with('success', 'Cuenta creada. Inicia sesión con tu correo y contraseña.');
    }
}
