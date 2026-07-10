<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Associate extends Model
{
    public const CATEGORIES = [
        'Titular',
        'Adherente',
        'Honorario',
        'Estudiante',
        'Corporativo',
    ];

    protected $fillable = [
        'full_name',
        'document_id',
        'phone',
        'email',
        'category',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public static function categoryOptions(): array
    {
        return self::CATEGORIES;
    }
}
