<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Company extends Model
{
    protected $fillable = [
        'name',
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
        'allows_loans',
    ];

    protected function casts(): array
    {
        return [
            'allows_loans' => 'boolean',
        ];
    }

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
}
