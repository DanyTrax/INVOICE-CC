<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Company extends Model
{
    protected $fillable = [
        'name',
        'code_abbreviation',
        'nit_rut',
        'address',
        'country',
        'phone',
        'logo_path',
        'logo_base64',
        'logo_mime',
        'contact_person_name',
        'contact_person_email',
        'drive_folder_id',
    ];

    /**
     * Usuarios (clientes) asignados a la empresa, con descripción opcional en el vínculo (ej. contabilidad, gestor).
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)
            ->withPivot(['description', 'sees_all_processes'])
            ->withTimestamps();
    }

    /**
     * Registros de la empresa
     */
    public function registrations(): HasMany
    {
        return $this->hasMany(Registration::class);
    }

    /**
     * Cotizaciones (pre-venta) donde la empresa es cliente
     */
    public function quotes(): HasMany
    {
        return $this->hasMany(Quote::class, 'client_id');
    }

    /**
     * Solicitudes (processes) donde la empresa es cliente
     */
    public function processes(): HasMany
    {
        return $this->hasMany(Process::class, 'client_id');
    }

    /**
     * Usuario registrado con el email de contacto (activo) para este cliente, si existe.
     */
    public function contactRegisteredUser(): ?User
    {
        $email = $this->contact_person_email;
        if (! $email) {
            $first = $this->users()
                ->whereHas('roles', fn ($r) => $r->where('name', 'client'))
                ->orderBy('users.name')
                ->first();

            return $first && $first->is_active ? $first : null;
        }

        return $this->users()
            ->where('email', $email)
            ->where('is_active', true)
            ->first();
    }

    /**
     * Sincroniza clientes asignados y actualiza campos legacy de contacto (primer cliente).
     * Conserva en el pivot a usuarios que no son clientes (p. ej. especialistas vinculados desde Directorio).
     *
     * @param  array<int, array{user_id: int, description?: string|null}>  $assignments
     */
    public function syncClientAssignments(array $assignments): void
    {
        $clientRoleUserIds = User::role('client')->pluck('id')->all();

        // Mantener vínculos de no-clientes (agentes/especialistas) sin tocar su fila pivot
        $preserveUserIds = $this->users()
            ->whereNotIn('users.id', $clientRoleUserIds)
            ->pluck('users.id')
            ->all();

        $sync = [];
        foreach ($preserveUserIds as $uid) {
            $sync[(int) $uid] = ['description' => null];
        }

        foreach ($assignments as $row) {
            if (empty($row['user_id'])) {
                continue;
            }
            $uid = (int) $row['user_id'];
            $desc = isset($row['description']) ? trim((string) $row['description']) : null;
            $sync[$uid] = ['description' => $desc === '' ? null : $desc];
        }

        $this->users()->sync($sync);

        $first = $this->users()
            ->whereHas('roles', fn ($r) => $r->where('name', 'client'))
            ->orderBy('users.name')
            ->first();
        if ($first) {
            $this->forceFill([
                'contact_person_name' => $first->name,
                'contact_person_email' => $first->email,
            ])->saveQuietly();
        } else {
            $this->forceFill([
                'contact_person_name' => null,
                'contact_person_email' => null,
            ])->saveQuietly();
        }
    }

    /**
     * URL o data URI para el src de la imagen del logo (BD o ruta legada en public/).
     */
    public function logoSrcForImg(): ?string
    {
        if (! empty($this->logo_base64) && ! empty($this->logo_mime)) {
            return 'data:'.$this->logo_mime.';base64,'.$this->logo_base64;
        }

        $path = $this->logo_path;
        if ($path && file_exists(public_path($path))) {
            return asset($path);
        }

        return null;
    }

    public function hasLogo(): bool
    {
        return $this->logoSrcForImg() !== null;
    }

    /**
     * Sugerencia de siglas (2–3 letras) a partir del nombre comercial, para mostrar en formularios.
     * Ej. «Peperon Gord» → «PG». El usuario puede editar a PEG, PGO, etc.
     */
    public static function suggestCodeAbbreviationFromName(string $name): string
    {
        $name = trim(preg_replace('/\s+/u', ' ', $name));
        if ($name === '') {
            return '';
        }

        $parts = preg_split('/\s+/u', $name) ?: [];
        $parts = array_values(array_filter($parts, fn ($p) => $p !== ''));

        if (count($parts) === 0) {
            return '';
        }

        if (count($parts) === 1) {
            $only = $parts[0];
            $letters = preg_replace('/[^[:alpha:]]/u', '', $only) ?? '';

            return mb_strtoupper(mb_substr($letters !== '' ? $letters : $only, 0, 3));
        }

        $out = '';
        foreach (array_slice($parts, 0, 4) as $p) {
            $letters = preg_replace('/[^[:alpha:]]/u', '', $p) ?? '';
            $out .= $letters !== '' ? mb_substr($letters, 0, 1) : mb_substr($p, 0, 1);
        }

        return mb_strtoupper(mb_substr($out, 0, 3));
    }
}
