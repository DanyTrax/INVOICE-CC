<?php

namespace App\Support;

use Illuminate\Support\Facades\View;

class LegalPageDefaults
{
    public static function privacyHtml(): string
    {
        return trim(View::make('legal._default-privacy-body')->render());
    }

    public static function termsHtml(): string
    {
        return trim(View::make('legal._default-terms-body')->render());
    }
}
