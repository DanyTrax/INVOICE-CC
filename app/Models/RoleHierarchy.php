<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Permission\Models\Role;

class RoleHierarchy extends Model
{
    protected $fillable = [
        'role_id',
        'can_create_role',
        'can_view',
    ];

    protected function casts(): array
    {
        return [
            'can_view' => 'boolean',
        ];
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }
}
