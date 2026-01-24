<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class CompanyInvite extends Model
{
    protected $fillable = [
        'token',
        'email',
        'company_id',
        'expires_at',
        'used_at',
    ];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'used_at' => 'datetime',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function isValid(): bool
    {
        return !$this->used_at && $this->expires_at->isFuture();
    }

    public function markAsUsed(): void
    {
        $this->update(['used_at' => now()]);
    }

    public static function createForCompany(Company $company, string $email): self
    {
        // Invalidar invitaciones previas para mismo company+email
        static::where('company_id', $company->id)
            ->where('email', $email)
            ->whereNull('used_at')
            ->update(['used_at' => now()]);

        return static::create([
            'token' => Str::random(64),
            'email' => $email,
            'company_id' => $company->id,
            'expires_at' => now()->addDays(7),
        ]);
    }
}
