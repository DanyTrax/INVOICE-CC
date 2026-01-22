<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmailLog extends Model
{
    protected $fillable = [
        'to',
        'from_email',
        'from_name',
        'subject',
        'body',
        'provider',
        'status',
        'error_message',
        'user_id',
        'related_type',
        'related_id',
        'is_test',
    ];

    protected function casts(): array
    {
        return [
            'is_test' => 'boolean',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * Usuario que envió el correo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope para correos de prueba
     */
    public function scopeTest($query)
    {
        return $query->where('is_test', true);
    }

    /**
     * Scope para correos enviados exitosamente
     */
    public function scopeSent($query)
    {
        return $query->where('status', 'sent');
    }

    /**
     * Scope para correos fallidos
     */
    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }
}
