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
        'phone',
        'logo_path',
        'contact_person_name',
        'contact_person_email',
        'drive_folder_id',
    ];

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
}
