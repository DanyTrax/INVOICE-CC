<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmailTemplate extends Model
{
    protected $fillable = [
        'name',
        'subject',
        'body',
        'type',
    ];

    /**
     * Obtener plantilla por tipo
     */
    public static function getByType(string $type): ?self
    {
        return static::where('type', $type)->first();
    }

    /**
     * Obtener lista de variables disponibles para esta plantilla
     */
    public function getAvailableVariables(): array
    {
        $variables = [
            // Variables globales
            'agency_name' => 'Nombre de la agencia',
            'agency_email' => 'Email de la agencia',
            'agency_phone' => 'Teléfono de la agencia',
            'agency_address' => 'Dirección de la agencia',
            'agency_website' => 'Sitio web de la agencia',
        ];

        // Variables específicas por tipo
        switch ($this->type) {
            case 'client_invitation':
                $variables = array_merge($variables, [
                    'name' => 'Nombre del cliente',
                    'email' => 'Email del cliente',
                    'link' => 'Link de registro/acceso',
                    'company_name' => 'Nombre de la empresa',
                ]);
                break;

            case 'expiration_reminder':
                $variables = array_merge($variables, [
                    'name' => 'Nombre del destinatario',
                    'product_name' => 'Nombre del producto/registro',
                    'expiration_date' => 'Fecha de vencimiento',
                    'company_name' => 'Nombre de la empresa',
                    'registration_number' => 'Número de registro',
                ]);
                break;

            case 'new_registration':
                $variables = array_merge($variables, [
                    'name' => 'Nombre del destinatario',
                    'product_name' => 'Nombre del producto/registro',
                    'company_name' => 'Nombre de la empresa',
                    'registration_number' => 'Número de registro',
                    'status' => 'Estado del registro',
                    'expiration_date' => 'Fecha de vencimiento',
                    'assigned_specialist' => 'Especialista asignado',
                ]);
                break;

            case 'status_change':
                $variables = array_merge($variables, [
                    'name' => 'Nombre del destinatario',
                    'product_name' => 'Nombre del producto/registro',
                    'status' => 'Nuevo estado',
                    'previous_status' => 'Estado anterior',
                    'company_name' => 'Nombre de la empresa',
                    'registration_number' => 'Número de registro',
                    'observations' => 'Observaciones',
                ]);
                break;

            case 'pending_documents':
                $variables = array_merge($variables, [
                    'name' => 'Nombre del destinatario',
                    'product_name' => 'Nombre del producto/registro',
                    'pending_documents' => 'Lista de documentos pendientes',
                    'company_name' => 'Nombre de la empresa',
                    'registration_number' => 'Número de registro',
                ]);
                break;

            case 'specialist_assignment':
                $variables = array_merge($variables, [
                    'name' => 'Nombre del destinatario',
                    'product_name' => 'Nombre del producto/registro',
                    'specialist_name' => 'Nombre del especialista',
                    'specialist_email' => 'Email del especialista',
                    'company_name' => 'Nombre de la empresa',
                    'registration_number' => 'Número de registro',
                ]);
                break;

            case 'important_date_reminder':
                $variables = array_merge($variables, [
                    'name' => 'Nombre del destinatario',
                    'event_name' => 'Nombre del evento',
                    'event_date' => 'Fecha del evento',
                    'product_name' => 'Nombre del producto/registro',
                    'company_name' => 'Nombre de la empresa',
                    'registration_number' => 'Número de registro',
                ]);
                break;

            case 'requirement_notification':
                $variables = array_merge($variables, [
                    'name' => 'Nombre del destinatario',
                    'product_name' => 'Nombre del producto/registro',
                    'requirement_type' => 'Tipo de requerimiento',
                    'requirement_description' => 'Descripción del requerimiento',
                    'company_name' => 'Nombre de la empresa',
                    'registration_number' => 'Número de registro',
                ]);
                break;
        }

        return $variables;
    }
}
