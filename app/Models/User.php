<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements CanResetPasswordContract
{
    /** @use HasFactory<UserFactory> */
    use CanResetPassword, HasFactory, HasRoles, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'avatar_url',
        'is_active',
        'client_status',
        'manage_capacitaciones',
        'phone',
        'admin_theme',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_secret',
        'two_factor_recovery_codes',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'manage_capacitaciones' => 'boolean',
            'two_factor_secret' => 'encrypted',
            'two_factor_confirmed_at' => 'datetime',
            'two_factor_recovery_codes' => 'array',
        ];
    }

    /**
     * 2FA TOTP activo y confirmado.
     */
    public function hasTwoFactorEnabled(): bool
    {
        return $this->two_factor_confirmed_at !== null && filled($this->two_factor_secret);
    }

    /** Valores válidos para client_status (solo usuarios con rol client). */
    public const CLIENT_STATUS_ACTIVO = 'activo';

    public const CLIENT_STATUS_PENDIENTE = 'pendiente';

    public const CLIENT_STATUS_DESHABILITADO = 'deshabilitado';

    /**
     * Indica si el cliente puede acceder al portal (estado activo).
     */
    public function canAccessPortal(): bool
    {
        if (! $this->hasRole('client')) {
            return true;
        }

        // null o 'activo' = puede acceder (retrocompatibilidad con usuarios sin client_status)
        return in_array($this->client_status, [null, self::CLIENT_STATUS_ACTIVO], true);
    }

    /**
     * Relación Many-to-Many con Companies
     */
    public function companies(): BelongsToMany
    {
        return $this->belongsToMany(Company::class)
            ->withPivot(['description', 'sees_all_processes'])
            ->withTimestamps();
    }

    /**
     * Registros asignados como especialista
     */
    public function assignedRegistrations(): HasMany
    {
        return $this->hasMany(Registration::class, 'assigned_specialist_id');
    }

    /**
     * Expedientes INVIMA (processes) asignados con permisos por expediente.
     */
    public function assignedProcesses(): BelongsToMany
    {
        return $this->belongsToMany(Process::class, 'process_user')
            ->withPivot(['can_feed_timeline', 'can_manage_documents'])
            ->withTimestamps();
    }

    /**
     * Documentos subidos por el usuario
     */
    public function uploadedDocuments(): HasMany
    {
        return $this->hasMany(Document::class, 'uploaded_by_id');
    }

    /**
     * Completaciones de videos de capacitación (vistos al 100%).
     */
    public function capacitacionCompletions(): HasMany
    {
        return $this->hasMany(CapacitacionCompletion::class);
    }
}
