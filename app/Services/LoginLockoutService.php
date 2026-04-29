<?php

namespace App\Services;

use App\Models\LoginIpLockout;
use App\Settings\GeneralSettings;
use Carbon\CarbonInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class LoginLockoutService
{
    public function __construct(
        protected GeneralSettings $settings
    ) {}

    public function assertNotBlocked(Request $request): void
    {
        if (! $this->settings->login_lockout_enabled) {
            return;
        }

        $ip = $this->clientIp($request);
        if ($ip === '') {
            return;
        }

        $row = LoginIpLockout::query()->where('ip_address', $ip)->first();

        if ($row === null) {
            return;
        }

        $this->expireLockIfNeeded($row);

        if ($row->isLockedNow()) {
            throw ValidationException::withMessages([
                'email' => [$this->blockedMessage($row->locked_until)],
            ]);
        }
    }

    public function recordFailedAttempt(Request $request, string $email): void
    {
        if (! $this->settings->login_lockout_enabled) {
            return;
        }

        $ip = $this->clientIp($request);
        if ($ip === '') {
            return;
        }

        $max = max(1, min(100, (int) $this->settings->login_max_failed_attempts));
        $minutes = max(1, min(10080, (int) $this->settings->login_lockout_duration_minutes));

        $ua = $request->userAgent();
        if (is_string($ua) && strlen($ua) > 2000) {
            $ua = substr($ua, 0, 2000);
        }

        DB::transaction(function () use ($ip, $email, $ua, $max, $minutes): void {
            /** @var LoginIpLockout $row */
            $row = LoginIpLockout::query()->where('ip_address', $ip)->lockForUpdate()->first();

            $now = now();
            if ($row === null) {
                $lockedUntil = null;
                if (1 >= $max) {
                    $lockedUntil = $now->copy()->addMinutes($minutes);
                }

                LoginIpLockout::query()->create([
                    'ip_address' => $ip,
                    'email_attempted' => $email !== '' ? mb_substr($email, 0, 255) : null,
                    'user_agent' => $ua,
                    'failed_attempts' => 1,
                    'locked_until' => $lockedUntil,
                    'first_attempt_at' => $now,
                    'last_attempt_at' => $now,
                ]);

                return;
            }

            $this->expireLockIfNeeded($row);

            if ($row->isLockedNow()) {
                return;
            }

            $row->failed_attempts = (int) $row->failed_attempts + 1;
            $row->email_attempted = $email !== '' ? mb_substr($email, 0, 255) : $row->email_attempted;
            $row->user_agent = $ua ?? $row->user_agent;
            $row->last_attempt_at = $now;
            if ($row->first_attempt_at === null) {
                $row->first_attempt_at = $now;
            }

            if ($row->failed_attempts >= $max) {
                $row->locked_until = $now->copy()->addMinutes($minutes);
            }

            $row->save();
        });
    }

    public function clearForSuccessfulLogin(Request $request): void
    {
        $ip = $this->clientIp($request);
        if ($ip === '') {
            return;
        }

        LoginIpLockout::query()->where('ip_address', $ip)->delete();
    }

    public function unlock(LoginIpLockout $lockout): void
    {
        $lockout->failed_attempts = 0;
        $lockout->locked_until = null;
        $lockout->save();
    }

    protected function clientIp(Request $request): string
    {
        $ip = $request->ip();
        if (! is_string($ip) || $ip === '') {
            return '';
        }

        return mb_substr($ip, 0, 45);
    }

    protected function expireLockIfNeeded(LoginIpLockout $row): void
    {
        if ($row->locked_until === null || $row->locked_until->isFuture()) {
            return;
        }

        $row->failed_attempts = 0;
        $row->locked_until = null;
        $row->save();
    }

    protected function blockedMessage(?CarbonInterface $until): string
    {
        if ($until !== null) {
            return 'Esta dirección IP ha sido temporalmente bloqueada por demasiados intentos fallidos. Podrás volver a intentarlo después del '
                .$until->timezone(config('app.timezone'))->format('d/m/Y H:i')
                .' o contacta a un administrador para desbloquear.';
        }

        return 'Esta dirección IP está temporalmente bloqueada. Contacta a un administrador si necesitas acceso.';
    }
}
