<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Permission\Models\Role;

class RoleHierarchy extends Model
{
    // La migración creó la tabla 'role_hierarchy' (sin plural),
    // así que necesitamos indicarlo explícitamente al modelo.
    protected $table = 'role_hierarchy';

    protected $fillable = [
        'role_id',
        'can_create_role',
        'can_view',
        'can_edit',
    ];

    protected function casts(): array
    {
        return [
            'can_view' => 'boolean',
            'can_edit' => 'boolean',
        ];
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }
}
