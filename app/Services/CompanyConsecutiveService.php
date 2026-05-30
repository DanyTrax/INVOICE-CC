<?php

namespace App\Services;

use App\Models\Company;
use App\Models\Proposal;
use App\Models\Quote;
use Illuminate\Validation\ValidationException;

class CompanyConsecutiveService
{
    /**
     * Siglas normalizadas (2–10 letras A-Z), igual que solicitudes.
     */
    public static function normalizePrefix(Company $company): string
    {
        $raw = (string) $company->code_abbreviation;
        $prefix = strtoupper(preg_replace('/[^A-Za-z]/', '', $raw) ?? '');

        return mb_substr($prefix, 0, 10);
    }

    public static function prefixOrFail(Company $company): string
    {
        $prefix = self::normalizePrefix($company);
        if ($prefix === '' || mb_strlen($prefix) < 2) {
            throw ValidationException::withMessages([
                'client_id' => 'La empresa debe tener siglas (2–10 letras) en el directorio para numerar cotizaciones y propuestas.',
            ]);
        }

        return $prefix;
    }

    public static function yearSuffix(?int $year = null): string
    {
        $y = $year ?? (int) now()->format('Y');

        return str_pad((string) ($y % 100), 2, '0', STR_PAD_LEFT);
    }

    /**
     * Siguiente consecutivo de cotización por cliente y año: SIGLAS-001-26
     */
    public static function suggestQuoteConsecutive(int $clientId, ?int $year = null): string
    {
        $company = Company::query()->findOrFail($clientId);
        $prefix = self::prefixOrFail($company);
        $yearSuffix = self::yearSuffix($year);
        $next = self::nextSequenceForClient(
            Quote::query()->where('client_id', $clientId)->pluck('consecutive'),
            $prefix,
            $yearSuffix,
            null
        );

        return sprintf('%s-%03d-%s', $prefix, $next, $yearSuffix);
    }

    /**
     * Siguiente consecutivo de propuesta por cliente y año: SIGLAS-P-001-26
     */
    public static function suggestProposalConsecutive(int $clientId, ?int $year = null): string
    {
        $company = Company::query()->findOrFail($clientId);
        $prefix = self::prefixOrFail($company);
        $yearSuffix = self::yearSuffix($year);
        $next = self::nextSequenceForClient(
            Proposal::query()->where('client_id', $clientId)->pluck('consecutive'),
            $prefix,
            $yearSuffix,
            'P'
        );

        return sprintf('%s-P-%03d-%s', $prefix, $next, $yearSuffix);
    }

    /**
     * @param  iterable<string|null>  $consecutives
     */
    protected static function nextSequenceForClient(iterable $consecutives, string $prefix, string $yearSuffix, ?string $middleToken): int
    {
        $max = 0;
        $quotedPrefix = preg_quote($prefix, '/');
        $quotedYear = preg_quote($yearSuffix, '/');

        if ($middleToken) {
            $quotedMiddle = preg_quote($middleToken, '/');
            $patternWithPrefix = '/^'.$quotedPrefix.'-'.$quotedMiddle.'-(\d+)-'.$quotedYear.'$/i';
            $patternLegacy = '/^'.$quotedMiddle.'-(\d+)-'.$quotedYear.'$/i';
        } else {
            $patternWithPrefix = '/^'.$quotedPrefix.'-(\d+)-'.$quotedYear.'$/i';
            $patternLegacy = '/^(\d+)-'.$quotedYear.'$/';
        }

        foreach ($consecutives as $consecutive) {
            $consecutive = trim((string) $consecutive);
            if ($consecutive === '') {
                continue;
            }
            if (preg_match($patternWithPrefix, $consecutive, $m)) {
                $max = max($max, (int) $m[1]);
            } elseif (preg_match($patternLegacy, $consecutive, $m)) {
                $max = max($max, (int) $m[1]);
            }
        }

        return $max + 1;
    }
}
