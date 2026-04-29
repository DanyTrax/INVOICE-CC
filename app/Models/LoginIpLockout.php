<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $ip_address
 * @property string|null $email_attempted
 * @property string|null $user_agent
 * @property int $failed_attempts
 * @property \Illuminate\Support\Carbon|null $locked_until
 * @property \Illuminate\Support\Carbon|null $first_attempt_at
 * @property \Illuminate\Support\Carbon|null $last_attempt_at
 */
class LoginIpLockout extends Model
{
    protected $fillable = [
        'ip_address',
        'email_attempted',
        'user_agent',
        'failed_attempts',
        'locked_until',
        'first_attempt_at',
        'last_attempt_at',
    ];

    protected function casts(): array
    {
        return [
            'locked_until' => 'datetime',
            'first_attempt_at' => 'datetime',
            'last_attempt_at' => 'datetime',
        ];
    }

    public function isLockedNow(): bool
    {
        return $this->locked_until !== null && $this->locked_until->isFuture();
    }
}
