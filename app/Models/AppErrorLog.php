<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AppErrorLog extends Model
{
    protected $fillable = [
        'level',
        'exception_class',
        'message',
        'file',
        'line',
        'url',
        'method',
        'status_code',
        'user_id',
        'user_name',
        'ip',
        'trace',
        'resolved_at',
    ];

    protected $casts = [
        'resolved_at' => 'datetime',
    ];

    /**
     * Usuario autenticado cuando ocurrió el error (si lo había).
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Ubicación corta: archivo:línea sin la ruta base del proyecto.
     */
    public function getShortLocationAttribute(): string
    {
        if (! $this->file) {
            return '—';
        }

        $file = str_replace(base_path().DIRECTORY_SEPARATOR, '', $this->file);

        return $this->line ? $file.':'.$this->line : $file;
    }

    /**
     * Texto listo para copiar/pegar al reportar el error.
     */
    public function getCopyTextAttribute(): string
    {
        $lines = [
            'Fecha: '.optional($this->created_at)->format('d/m/Y H:i:s'),
            'Usuario: '.($this->user_name ?? 'Invitado / sin sesión'),
            'Excepción: '.($this->exception_class ?? '—'),
            'Mensaje: '.$this->message,
            'Ubicación: '.$this->short_location,
            'URL: '.($this->method ? $this->method.' ' : '').($this->url ?? '—'),
            'IP: '.($this->ip ?? '—'),
        ];

        if ($this->trace) {
            $lines[] = '';
            $lines[] = 'Stack trace:';
            $lines[] = $this->trace;
        }

        return implode("\n", $lines);
    }
}
