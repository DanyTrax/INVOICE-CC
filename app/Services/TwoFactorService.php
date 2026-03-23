<?php

namespace App\Services;

use App\Models\User;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use PragmaRX\Google2FA\Google2FA;

class TwoFactorService
{
    public const RECOVERY_CODE_COUNT = 8;

    public function __construct(
        protected Google2FA $google2fa
    ) {}

    public function generateSecretKey(): string
    {
        return $this->google2fa->generateSecretKey();
    }

    /**
     * URL otpauth:// para apps autenticadoras.
     */
    public function getOtpAuthUrl(string $secret, string $email, string $issuer): string
    {
        return $this->google2fa->getQRCodeUrl($issuer, $email, $secret);
    }

    /**
     * Imagen QR como data URI (SVG vía BaconQrCode; sin dependencia de endroid/qr-code).
     */
    public function getQrCodeDataUri(string $otpAuthUrl): string
    {
        $renderer = new ImageRenderer(
            new RendererStyle(220, 8),
            new SvgImageBackEnd
        );
        $writer = new Writer($renderer);
        $svg = $writer->writeString($otpAuthUrl);

        return 'data:image/svg+xml;base64,'.base64_encode($svg);
    }

    public function verifyTotp(string $secret, string $code): bool
    {
        $code = preg_replace('/\s+/', '', $code) ?? '';

        return (bool) $this->google2fa->verifyKey($secret, $code);
    }

    /**
     * @return list<string> Códigos en texto plano (mostrar una sola vez).
     */
    public function generateRecoveryCodesPlain(): array
    {
        $codes = [];
        for ($i = 0; $i < self::RECOVERY_CODE_COUNT; $i++) {
            $codes[] = strtoupper(Str::random(5).'-'.Str::random(5));
        }

        return $codes;
    }

    /**
     * @param  list<string>  $plainCodes
     * @return list<string> Hashes para guardar en JSON
     */
    public function hashRecoveryCodes(array $plainCodes): array
    {
        return array_map(fn (string $c) => Hash::make(strtoupper(trim($c))), $plainCodes);
    }

    /**
     * Verifica un código de respaldo y lo elimina del usuario (one-time).
     */
    public function verifyAndConsumeRecoveryCode(User $user, string $input): bool
    {
        $raw = strtoupper(preg_replace('/\s+/', '', $input) ?? '');
        $hashes = $user->two_factor_recovery_codes ?? [];
        if (! is_array($hashes) || $hashes === []) {
            return false;
        }

        $remaining = [];
        $matched = false;
        foreach ($hashes as $storedHash) {
            if (! $matched && Hash::check($raw, $storedHash)) {
                $matched = true;

                continue;
            }
            $remaining[] = $storedHash;
        }

        if (! $matched) {
            return false;
        }

        $user->forceFill([
            'two_factor_recovery_codes' => $remaining === [] ? null : $remaining,
        ])->save();

        return true;
    }

    public function userHasConfirmedTwoFactor(User $user): bool
    {
        return $user->two_factor_confirmed_at !== null
            && filled($user->two_factor_secret);
    }

    /**
     * Quita 2FA y códigos de respaldo (BD).
     */
    public function disableTwoFactor(User $user): void
    {
        $user->forceFill([
            'two_factor_secret' => null,
            'two_factor_confirmed_at' => null,
            'two_factor_recovery_codes' => null,
        ])->save();
    }
}
