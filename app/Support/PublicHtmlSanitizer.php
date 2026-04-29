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
}
