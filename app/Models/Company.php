<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\User;

class Company extends Model
{
    protected $fillable = [
        'name',
        'nit_rut',
        'address',
        'phone',
        'logo_path',
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
     * Relación Many-to-Many con Users
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
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
     * Expedientes (processes) donde la empresa es cliente
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
        if (!$this->contact_person_email) {
            return null;
        }
        return $this->users()
            ->where('email', $this->contact_person_email)
            ->where('is_active', true)
            ->first();
    }
}
