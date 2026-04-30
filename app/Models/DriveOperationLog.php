<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DriveOperationLog extends Model
{
    protected $table = 'drive_operations_log';

    protected $fillable = [
        'operation_type',
        'resource_type',
        'resource_name',
        'drive_id',
        'drive_url',
        'status',
        'error_message',
        'details',
        'user_id',
        'registration_id',
        'company_id',
    ];

    protected $casts = [
        'details' => 'array',
    ];

    /**
     * Usuario que realizó la operación
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Solicitud (process) relacionada
     */
    public function registration(): BelongsTo
    {
        return $this->belongsTo(Registration::class);
    }

    /**
     * Cliente relacionado
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Obtener icono según tipo de operación
     */
    public function getOperationIconAttribute(): string
    {
        return match ($this->operation_type) {
            'upload' => 'fa-upload',
            'download' => 'fa-download',
            'view' => 'fa-eye',
            'create_folder' => 'fa-folder-plus',
            'move' => 'fa-arrows-alt',
            'delete' => 'fa-trash',
            'update' => 'fa-edit',
            default => 'fa-file',
        };
    }

    /**
     * Obtener color según estado
     */
    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'success' => 'green',
            'failed' => 'red',
            'pending' => 'yellow',
            default => 'gray',
        };
    }
}
