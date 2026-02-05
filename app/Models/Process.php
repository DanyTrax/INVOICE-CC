<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Process extends Model
{
    protected $fillable = [
        'quote_item_id',
        'client_id',
        'status',
        'expediente_invima',
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
