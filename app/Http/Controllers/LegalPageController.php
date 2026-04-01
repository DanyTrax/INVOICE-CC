<?php

namespace App\Http\Controllers;

use App\Settings\GeneralSettings;
use App\Support\LegalPageDefaults;

class LegalPageController extends Controller
{
    public function privacy(GeneralSettings $settings)
    {
        $body = trim($settings->legal_privacy_html ?? '');
        if ($body === '') {
            $body = LegalPageDefaults::privacyHtml();
        }

        return view('legal.show', [
            'title' => 'Política de Privacidad',
            'heading' => 'Política de Privacidad',
            'bodyHtml' => $body,
        ]);
    }

    public function terms(GeneralSettings $settings)
    {
        $body = trim($settings->legal_terms_html ?? '');
        if ($body === '') {
            $body = LegalPageDefaults::termsHtml();
        }

        return view('legal.show', [
            'title' => 'Términos y Condiciones',
            'heading' => 'Términos y Condiciones del Servicio',
            'bodyHtml' => $body,
        ]);
    }
}
