<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Process extends Model
{
    public const STATUS_RECOLECCION = 'Recolección';
    public const STATUS_RADICADO = 'Radicado';
    public const STATUS_EN_REQUERIMIENTO = 'En Requerimiento';
    public const STATUS_FINALIZADO = 'Finalizado';

    public static function statuses(): array
    {
        return [
            self::STATUS_RECOLECCION,
            self::STATUS_RADICADO,
            self::STATUS_EN_REQUERIMIENTO,
            self::STATUS_FINALIZADO,
        ];
    }

    protected $fillable = [
        'quote_item_id',
        'client_id',
        'status',
        'expediente_invima',
    ];

    protected $attributes = [
        'status' => self::STATUS_RECOLECCION,
    ];

    public function quoteItem(): BelongsTo
    {
        return $this->belongsTo(QuoteItem::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'client_id');
    }

    public function checklistItems(): HasMany
    {
        return $this->hasMany(ChecklistItem::class, 'process_id');
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(Submission::class, 'process_id');
    }
}
