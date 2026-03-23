<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles;

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
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
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
        ];
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
        if (!$this->hasRole('client')) {
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
            ->withPivot('description')
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
