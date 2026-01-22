<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class Registration extends Model implements AuditableContract
{
    use Auditable;

    protected $fillable = [
        'company_id',
        'assigned_specialist_id',
        'product_name',
        'registration_number',
        'status',
        'transaction_type',
        'quotation_number',
        'client_request_date',
        'radication_date',
        'submission_date',
        'expiration_date',
        'invima_auto_date',
        'response_limit_date',
        'response_radication_date',
        'client_requirement',
        'invima_requirement',
        'pending_docs',
        'observations',
        'radication_number',
        'key_code',
        'resolution_number',
        'drive_folder_url',
    ];

    protected function casts(): array
    {
        return [
            'client_request_date' => 'date',
            'radication_date' => 'date',
            'submission_date' => 'date',
            'expiration_date' => 'date',
            'invima_auto_date' => 'date',
            'response_limit_date' => 'date',
            'response_radication_date' => 'date',
        ];
    }

    /**
     * Empresa propietaria del registro
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Especialista asignado
     */
    public function assignedSpecialist(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_specialist_id');
    }

    /**
     * Documentos del registro
     */
    public function documents(): HasMany
    {
        return $this->hasMany(Document::class);
    }
}
