<?php

namespace App\Support;

use Symfony\Component\HtmlSanitizer\HtmlSanitizer;
use Symfony\Component\HtmlSanitizer\HtmlSanitizerConfig;

/**
 * HTML administrado (p. ej. políticas legales desde TinyMCE) antes de inyectarlo con {!! !!}.
 */
final class PublicHtmlSanitizer
{
    private static ?HtmlSanitizer $instance = null;

    private static ?HtmlSanitizer $footerInstance = null;

    public static function sanitize(?string $html): string
    {
        if ($html === null) {
            return '';
        }
        $html = trim($html);
        if ($html === '') {
            return '';
        }

        return self::instance()->sanitize($html);
    }

    /**
     * Footer administrado (texto plano o HTML básico: enlaces, negritas, divs con class).
     */
    public static function footer(?string $html): string
    {
        if ($html === null) {
            return '';
        }
        $html = trim($html);
        if ($html === '') {
            return '';
        }

        return self::normalizeFooterHtml(self::footerInstance()->sanitize($html));
    }

    /**
     * Texto plano con saltos de línea o HTML sanitizado para mostrar en el footer.
     */
    public static function footerForDisplay(?string $html, string $fallback = ''): string
    {
        $html = trim($html ?? '');
        if ($html === '') {
            return e($fallback);
        }

        if (! str_contains($html, '<')) {
            return nl2br(e($html));
        }

        return self::footer($html);
    }

    public static function footerShowsCopyright(?string $html): bool
    {
        $text = strtolower(strip_tags($html ?? ''));

        return str_contains($text, 'copyright')
            || str_contains($text, 'derechos reservados');
    }

    private static function normalizeFooterHtml(string $html): string
    {
        $html = preg_replace(
            '/\b(pull-left|pull-right|float-left|float-right)\b/',
            '',
            $html
        ) ?? $html;

        $html = preg_replace('/\sclass="\s*"/', '', $html) ?? $html;
        $html = preg_replace("/\sclass='\s*'/", '', $html) ?? $html;

        return trim($html);
    }

    private static function instance(): HtmlSanitizer
    {
        if (self::$instance === null) {
            $config = (new HtmlSanitizerConfig())
                ->allowSafeElements()
                ->allowRelativeLinks()
                ->allowRelativeMedias()
                ->allowLinkSchemes(['http', 'https', 'mailto', 'tel'])
                ->allowMediaSchemes(['http', 'https', 'data'])
                ->forceAttribute('a', 'rel', 'noopener noreferrer');

            self::$instance = new HtmlSanitizer($config);
        }

        return self::$instance;
    }

    private static function footerInstance(): HtmlSanitizer
    {
        if (self::$footerInstance === null) {
            $config = (new HtmlSanitizerConfig())
                ->allowSafeElements()
                ->allowRelativeLinks()
                ->allowRelativeMedias()
                ->allowLinkSchemes(['http', 'https', 'mailto', 'tel'])
                ->allowMediaSchemes(['http', 'https', 'data'])
                ->allowAttribute('class', '*')
                ->allowAttribute('target', 'a')
                ->forceAttribute('a', 'rel', 'noopener noreferrer');

            self::$footerInstance = new HtmlSanitizer($config);
        }

        return self::$footerInstance;
    }
}
