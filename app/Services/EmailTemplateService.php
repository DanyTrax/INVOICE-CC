<?php

namespace App\Services;

use App\Models\EmailTemplate;
use App\Settings\GeneralSettings;
use Illuminate\Support\Facades\Log;

class EmailTemplateService
{
    protected $settings;

    public function __construct()
    {
        $this->settings = app(GeneralSettings::class);
    }

    /**
     * Obtener plantilla por tipo
     */
    public function getTemplate(string $type): ?EmailTemplate
    {
        return EmailTemplate::where('type', $type)->first();
    }

    /**
     * Procesar plantilla reemplazando variables
     */
    public function processTemplate(string $type, array $variables = []): ?array
    {
        $template = $this->getTemplate($type);
        
        if (!$template) {
            Log::warning("Plantilla de tipo '{$type}' no encontrada");
            return null;
        }

        // Agregar variables globales del sistema
        $allVariables = array_merge($this->getGlobalVariables(), $variables);

        // Procesar asunto y cuerpo
        $subject = $this->replaceVariables($template->subject, $allVariables);
        $body = $this->replaceVariables($template->body, $allVariables);

        return [
            'subject' => $subject,
            'body' => $body,
        ];
    }

    /**
     * Obtener variables globales del sistema
     */
    protected function getGlobalVariables(): array
    {
        return [
            'agency_name' => $this->settings->agency_name ?? 'Invoices',
            'agency_email' => $this->settings->agency_email ?? '',
            'agency_phone' => $this->settings->agency_phone ?? '',
            'agency_address' => $this->settings->agency_address ?? '',
            'agency_website' => $this->settings->agency_website ?? '',
            'system_name' => $this->settings->system_name ?? 'Sistema de Gestión Regulatoria',
        ];
    }

    /**
     * Reemplazar variables en el texto
     */
    protected function replaceVariables(string $text, array $variables): string
    {
        foreach ($variables as $key => $value) {
            // Escapar valores para HTML si es necesario
            $safeValue = is_string($value) ? htmlspecialchars($value, ENT_QUOTES, 'UTF-8') : $value;
            
            // Reemplazar {variable} y {variable|default}
            $text = preg_replace_callback(
                '/\{' . preg_quote($key, '/') . '(?:\|([^}]+))?\}/',
                function ($matches) use ($safeValue) {
                    // Si hay valor, usarlo; si no, usar default si existe
                    return $safeValue ?: ($matches[1] ?? '');
                },
                $text
            );
        }

        // Limpiar variables no reemplazadas (opcional - comentar si quieres mantenerlas)
        // $text = preg_replace('/\{[^}]+\}/', '', $text);

        return $text;
    }

    /**
     * Procesar secciones condicionales en plantillas
     * Permite usar {if:variable}...{/if} para mostrar contenido solo si existe la variable
     */
    protected function processConditionalSections(string $text, array $variables): string
    {
        // Procesar bloques {if:variable}...{/if}
        $text = preg_replace_callback(
            '/\{if:([^}]+)\}(.*?)\{\/if\}/s',
            function ($matches) use ($variables) {
                $condition = $matches[1];
                $content = $matches[2];
                
                // Si la variable existe y no está vacía, mostrar contenido
                if (isset($variables[$condition]) && !empty($variables[$condition])) {
                    return $content;
                }
                
                return '';
            },
            $text
        );

        return $text;
    }

    /**
     * Formatear fecha para mostrar en emails
     */
    public function formatDate($date, string $format = 'd/m/Y'): string
    {
        if (!$date) {
            return '';
        }

        if (is_string($date)) {
            $date = \Carbon\Carbon::parse($date);
        }

        return $date->format($format);
    }

    /**
     * Formatear lista de documentos pendientes
     */
    public function formatPendingDocuments(array $documents): string
    {
        if (empty($documents)) {
            return '<p style="color: #666; font-style: italic;">No hay documentos pendientes.</p>';
        }

        $html = '<ul style="margin: 10px 0; padding-left: 20px; color: #333;">';
        foreach ($documents as $doc) {
            $html .= '<li style="margin: 5px 0;">' . htmlspecialchars($doc, ENT_QUOTES, 'UTF-8') . '</li>';
        }
        $html .= '</ul>';

        return $html;
    }

    /**
     * Generar secciones HTML condicionales para plantillas
     */
    public function generateConditionalSection(string $label, ?string $value, string $color = '#0f766e'): string
    {
        if (empty($value)) {
            return '';
        }

        return sprintf(
            '<tr>
                <td style="padding-top: 15px; padding-bottom: 10px; border-top: 1px solid #%s;">
                    <p style="color: %s; font-size: 14px; margin: 0; font-weight: bold; text-transform: uppercase;">%s</p>
                    <p style="color: #333333; font-size: 16px; margin: 5px 0 0 0;">%s</p>
                </td>
            </tr>',
            substr($color, 1) . '80', // Color con transparencia para el borde
            $color,
            htmlspecialchars($label, ENT_QUOTES, 'UTF-8'),
            htmlspecialchars($value, ENT_QUOTES, 'UTF-8')
        );
    }
}
