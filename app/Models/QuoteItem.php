<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class QuoteItem extends Model
{
    protected $fillable = [
        'quote_id',
        'service_id',
        'service_label',
        'item_position',
        'row_id',
        'service_type_id',
        'raa_code',
        'franquicia',
        'centro_costos',
        'contacto',
        'previous_license',
        'description',
        'scope',
        'fee_value',
        'invima_rate_code',
        'invima_rate_value',
    ];

    protected function casts(): array
    {
        return [
            'fee_value' => 'decimal:2',
            'invima_rate_value' => 'decimal:2',
        ];
    }

    public function quote(): BelongsTo
    {
        return $this->belongsTo(Quote::class);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function serviceType(): BelongsTo
    {
        return $this->belongsTo(ServiceType::class);
    }

    public function process(): HasOne
    {
        return $this->hasOne(Process::class);
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(Submission::class, 'quote_item_id');
    }
}
