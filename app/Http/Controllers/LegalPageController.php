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

        $pageTitle = trim($settings->legal_privacy_title ?? '') ?: 'Política de Privacidad';

        return view('legal.show', [
            'title' => $pageTitle,
            'pageTitle' => $pageTitle,
            'bodyHtml' => $body,
            'footerHtml' => $settings->footer_text ?? '',
        ]);
    }

    public function terms(GeneralSettings $settings)
    {
        $body = trim($settings->legal_terms_html ?? '');
        if ($body === '') {
            $body = LegalPageDefaults::termsHtml();
        }

        $pageTitle = trim($settings->legal_terms_title ?? '') ?: 'Términos y Condiciones del Servicio';

        return view('legal.show', [
            'title' => $pageTitle,
            'pageTitle' => $pageTitle,
            'bodyHtml' => $body,
            'footerHtml' => $settings->footer_text ?? '',
        ]);
    }
}
