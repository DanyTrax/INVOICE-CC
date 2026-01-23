<?php

namespace Database\Seeders;

use App\Models\EmailTemplate;
use Illuminate\Database\Seeder;

class EmailTemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $templates = [
            [
                'name' => 'Invitación de Cliente',
                'type' => 'client_invitation',
                'subject' => 'Bienvenido a {agency_name} - Invitación de Acceso',
                'body' => $this->getClientInvitationTemplate(),
            ],
            [
                'name' => 'Recordatorio de Vencimiento',
                'type' => 'expiration_reminder',
                'subject' => 'Recordatorio: {product_name} vence el {expiration_date}',
                'body' => $this->getExpirationReminderTemplate(),
            ],
            [
                'name' => 'Nuevo Registro Creado',
                'type' => 'new_registration',
                'subject' => 'Nuevo Registro: {product_name} - {company_name}',
                'body' => $this->getNewRegistrationTemplate(),
            ],
            [
                'name' => 'Cambio de Estado',
                'type' => 'status_change',
                'subject' => 'Actualización: {product_name} - Estado: {status}',
                'body' => $this->getStatusChangeTemplate(),
            ],
            [
                'name' => 'Documentos Pendientes',
                'type' => 'pending_documents',
                'subject' => 'Recordatorio: Documentos Pendientes - {product_name}',
                'body' => $this->getPendingDocumentsTemplate(),
            ],
            [
                'name' => 'Asignación de Especialista',
                'type' => 'specialist_assignment',
                'subject' => 'Especialista Asignado: {product_name}',
                'body' => $this->getSpecialistAssignmentTemplate(),
            ],
            [
                'name' => 'Recordatorio de Fecha Importante',
                'type' => 'important_date_reminder',
                'subject' => 'Recordatorio: {event_name} - {product_name}',
                'body' => $this->getImportantDateReminderTemplate(),
            ],
            [
                'name' => 'Notificación de Requerimiento',
                'type' => 'requirement_notification',
                'subject' => 'Nuevo Requerimiento: {product_name}',
                'body' => $this->getRequirementNotificationTemplate(),
            ],
        ];

        foreach ($templates as $template) {
            EmailTemplate::updateOrCreate(
                ['type' => $template['type']],
                [
                    'name' => $template['name'],
                    'subject' => $template['subject'],
                    'body' => $template['body'],
                ]
            );
        }
    }

    private function getClientInvitationTemplate(): string
    {
        return <<<'HTML'
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bienvenido a {agency_name}</title>
</head>
<body style="margin: 0; padding: 0; font-family: Arial, sans-serif; background-color: #f4f4f4;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background-color: #f4f4f4; padding: 20px;">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0" style="background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                    <!-- Header -->
                    <tr>
                        <td style="background: linear-gradient(135deg, #0d9488 0%, #14b8a6 100%); padding: 40px 30px; text-align: center;">
                            <h1 style="color: #ffffff; margin: 0; font-size: 28px; font-weight: bold;">¡Bienvenido a {agency_name}!</h1>
                        </td>
                    </tr>
                    
                    <!-- Content -->
                    <tr>
                        <td style="padding: 40px 30px;">
                            <p style="color: #333333; font-size: 16px; line-height: 1.6; margin: 0 0 20px 0;">
                                Hola <strong>{name}</strong>,
                            </p>
                            
                            <p style="color: #333333; font-size: 16px; line-height: 1.6; margin: 0 0 20px 0;">
                                Te damos la bienvenida a <strong>{agency_name}</strong>. Tu empresa <strong>{company_name}</strong> ha sido registrada exitosamente en nuestro sistema de gestión regulatoria.
                            </p>
                            
                            <div style="background-color: #f0fdfa; border-left: 4px solid #14b8a6; padding: 20px; margin: 30px 0; border-radius: 4px;">
                                <p style="color: #0f766e; font-size: 14px; margin: 0; font-weight: bold; margin-bottom: 10px;">📋 Información de tu cuenta:</p>
                                <p style="color: #333333; font-size: 14px; margin: 5px 0;"><strong>Email:</strong> {email}</p>
                                <p style="color: #333333; font-size: 14px; margin: 5px 0;"><strong>Empresa:</strong> {company_name}</p>
                            </div>
                            
                            <p style="color: #333333; font-size: 16px; line-height: 1.6; margin: 30px 0 20px 0;">
                                Para acceder al sistema y comenzar a gestionar tus expedientes, haz clic en el siguiente botón:
                            </p>
                            
                            <table width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td align="center" style="padding: 20px 0;">
                                        <a href="{link}" style="display: inline-block; background-color: #0d9488; color: #ffffff; text-decoration: none; padding: 14px 32px; border-radius: 6px; font-weight: bold; font-size: 16px;">Acceder al Sistema</a>
                                    </td>
                                </tr>
                            </table>
                            
                            <p style="color: #666666; font-size: 14px; line-height: 1.6; margin: 30px 0 0 0;">
                                O copia y pega este enlace en tu navegador:<br>
                                <a href="{link}" style="color: #0d9488; word-break: break-all;">{link}</a>
                            </p>
                            
                            <div style="background-color: #fff7ed; border-left: 4px solid #f59e0b; padding: 15px; margin: 30px 0; border-radius: 4px;">
                                <p style="color: #92400e; font-size: 13px; margin: 0;">
                                    <strong>⚠️ Importante:</strong> Si no solicitaste esta cuenta, puedes ignorar este correo.
                                </p>
                            </div>
                        </td>
                    </tr>
                    
                    <!-- Footer -->
                    <tr>
                        <td style="background-color: #f9fafb; padding: 30px; text-align: center; border-top: 1px solid #e5e7eb;">
                            <p style="color: #6b7280; font-size: 12px; margin: 0 0 10px 0;">
                                <strong>{agency_name}</strong><br>
                                Sistema de Gestión Regulatoria
                            </p>
                            <p style="color: #9ca3af; font-size: 11px; margin: 10px 0 0 0;">
                                Este es un correo automático. Por favor, no respondas a este mensaje.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
HTML;
    }

    private function getExpirationReminderTemplate(): string
    {
        return <<<'HTML'
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recordatorio de Vencimiento</title>
</head>
<body style="margin: 0; padding: 0; font-family: Arial, sans-serif; background-color: #f4f4f4;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background-color: #f4f4f4; padding: 20px;">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0" style="background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                    <!-- Header -->
                    <tr>
                        <td style="background: linear-gradient(135deg, #dc2626 0%, #ef4444 100%); padding: 40px 30px; text-align: center;">
                            <h1 style="color: #ffffff; margin: 0; font-size: 28px; font-weight: bold;">⏰ Recordatorio de Vencimiento</h1>
                        </td>
                    </tr>
                    
                    <!-- Content -->
                    <tr>
                        <td style="padding: 40px 30px;">
                            <p style="color: #333333; font-size: 16px; line-height: 1.6; margin: 0 0 20px 0;">
                                Hola <strong>{name}</strong>,
                            </p>
                            
                            <p style="color: #333333; font-size: 16px; line-height: 1.6; margin: 0 0 20px 0;">
                                Te informamos que el siguiente registro está próximo a vencer:
                            </p>
                            
                            <div style="background-color: #fef2f2; border: 2px solid #dc2626; padding: 25px; margin: 30px 0; border-radius: 6px;">
                                <table width="100%" cellpadding="0" cellspacing="0">
                                    <tr>
                                        <td style="padding-bottom: 10px;">
                                            <p style="color: #991b1b; font-size: 14px; margin: 0; font-weight: bold; text-transform: uppercase;">Producto/Registro</p>
                                            <p style="color: #333333; font-size: 18px; margin: 5px 0 0 0; font-weight: bold;">{product_name}</p>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="padding-top: 15px; padding-bottom: 10px; border-top: 1px solid #fecaca;">
                                            <p style="color: #991b1b; font-size: 14px; margin: 0; font-weight: bold; text-transform: uppercase;">Fecha de Vencimiento</p>
                                            <p style="color: #dc2626; font-size: 20px; margin: 5px 0 0 0; font-weight: bold;">{expiration_date}</p>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="padding-top: 15px; border-top: 1px solid #fecaca;">
                                            <p style="color: #991b1b; font-size: 14px; margin: 0; font-weight: bold; text-transform: uppercase;">Empresa</p>
                                            <p style="color: #333333; font-size: 16px; margin: 5px 0 0 0;">{company_name}</p>
                                        </td>
                                    </tr>
                                    {registration_number}
                                </table>
                            </div>
                            
                            <p style="color: #333333; font-size: 16px; line-height: 1.6; margin: 30px 0 20px 0;">
                                <strong>⚠️ Acción Requerida:</strong> Por favor, revisa este registro y toma las acciones necesarias antes de la fecha de vencimiento.
                            </p>
                            
                            <div style="background-color: #f0fdfa; border-left: 4px solid #14b8a6; padding: 15px; margin: 20px 0; border-radius: 4px;">
                                <p style="color: #0f766e; font-size: 13px; margin: 0;">
                                    <strong>💡 Tip:</strong> Puedes acceder al sistema para ver más detalles y gestionar este registro.
                                </p>
                            </div>
                        </td>
                    </tr>
                    
                    <!-- Footer -->
                    <tr>
                        <td style="background-color: #f9fafb; padding: 30px; text-align: center; border-top: 1px solid #e5e7eb;">
                            <p style="color: #6b7280; font-size: 12px; margin: 0 0 10px 0;">
                                <strong>{agency_name}</strong><br>
                                Sistema de Gestión Regulatoria
                            </p>
                            <p style="color: #9ca3af; font-size: 11px; margin: 10px 0 0 0;">
                                Este es un correo automático. Por favor, no respondas a este mensaje.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
HTML;
    }

    private function getNewRegistrationTemplate(): string
    {
        return <<<'HTML'
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nuevo Registro Creado</title>
</head>
<body style="margin: 0; padding: 0; font-family: Arial, sans-serif; background-color: #f4f4f4;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background-color: #f4f4f4; padding: 20px;">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0" style="background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                    <!-- Header -->
                    <tr>
                        <td style="background: linear-gradient(135deg, #0d9488 0%, #14b8a6 100%); padding: 40px 30px; text-align: center;">
                            <h1 style="color: #ffffff; margin: 0; font-size: 28px; font-weight: bold;">✅ Nuevo Registro Creado</h1>
                        </td>
                    </tr>
                    
                    <!-- Content -->
                    <tr>
                        <td style="padding: 40px 30px;">
                            <p style="color: #333333; font-size: 16px; line-height: 1.6; margin: 0 0 20px 0;">
                                Hola <strong>{name}</strong>,
                            </p>
                            
                            <p style="color: #333333; font-size: 16px; line-height: 1.6; margin: 0 0 20px 0;">
                                Se ha creado un nuevo registro en el sistema para <strong>{company_name}</strong>:
                            </p>
                            
                            <div style="background-color: #f0fdfa; border-left: 4px solid #14b8a6; padding: 25px; margin: 30px 0; border-radius: 6px;">
                                <table width="100%" cellpadding="0" cellspacing="0">
                                    <tr>
                                        <td style="padding-bottom: 10px;">
                                            <p style="color: #0f766e; font-size: 14px; margin: 0; font-weight: bold; text-transform: uppercase;">Producto/Registro</p>
                                            <p style="color: #333333; font-size: 18px; margin: 5px 0 0 0; font-weight: bold;">{product_name}</p>
                                        </td>
                                    </tr>
                                    {registration_number}
                                    {status}
                                    {expiration_date}
                                    {assigned_specialist}
                                </table>
                            </div>
                            
                            <p style="color: #333333; font-size: 16px; line-height: 1.6; margin: 30px 0 20px 0;">
                                Puedes acceder al sistema para ver más detalles y gestionar este registro.
                            </p>
                        </td>
                    </tr>
                    
                    <!-- Footer -->
                    <tr>
                        <td style="background-color: #f9fafb; padding: 30px; text-align: center; border-top: 1px solid #e5e7eb;">
                            <p style="color: #6b7280; font-size: 12px; margin: 0 0 10px 0;">
                                <strong>{agency_name}</strong><br>
                                Sistema de Gestión Regulatoria
                            </p>
                            <p style="color: #9ca3af; font-size: 11px; margin: 10px 0 0 0;">
                                Este es un correo automático. Por favor, no respondas a este mensaje.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
HTML;
    }

    private function getStatusChangeTemplate(): string
    {
        return <<<'HTML'
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cambio de Estado</title>
</head>
<body style="margin: 0; padding: 0; font-family: Arial, sans-serif; background-color: #f4f4f4;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background-color: #f4f4f4; padding: 20px;">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0" style="background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                    <!-- Header -->
                    <tr>
                        <td style="background: linear-gradient(135deg, #3b82f6 0%, #60a5fa 100%); padding: 40px 30px; text-align: center;">
                            <h1 style="color: #ffffff; margin: 0; font-size: 28px; font-weight: bold;">🔄 Actualización de Estado</h1>
                        </td>
                    </tr>
                    
                    <!-- Content -->
                    <tr>
                        <td style="padding: 40px 30px;">
                            <p style="color: #333333; font-size: 16px; line-height: 1.6; margin: 0 0 20px 0;">
                                Hola <strong>{name}</strong>,
                            </p>
                            
                            <p style="color: #333333; font-size: 16px; line-height: 1.6; margin: 0 0 20px 0;">
                                El estado del siguiente registro ha sido actualizado:
                            </p>
                            
                            <div style="background-color: #eff6ff; border-left: 4px solid #3b82f6; padding: 25px; margin: 30px 0; border-radius: 6px;">
                                <table width="100%" cellpadding="0" cellspacing="0">
                                    <tr>
                                        <td style="padding-bottom: 10px;">
                                            <p style="color: #1e40af; font-size: 14px; margin: 0; font-weight: bold; text-transform: uppercase;">Producto/Registro</p>
                                            <p style="color: #333333; font-size: 18px; margin: 5px 0 0 0; font-weight: bold;">{product_name}</p>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="padding-top: 15px; padding-bottom: 10px; border-top: 1px solid #bfdbfe;">
                                            <p style="color: #1e40af; font-size: 14px; margin: 0; font-weight: bold; text-transform: uppercase;">Nuevo Estado</p>
                                            <p style="color: #3b82f6; font-size: 20px; margin: 5px 0 0 0; font-weight: bold;">{status}</p>
                                        </td>
                                    </tr>
                                    {previous_status}
                                    {company_name}
                                    {registration_number}
                                </table>
                            </div>
                            
                            {observations}
                            
                            <p style="color: #333333; font-size: 16px; line-height: 1.6; margin: 30px 0 20px 0;">
                                Puedes acceder al sistema para ver más detalles sobre este cambio.
                            </p>
                        </td>
                    </tr>
                    
                    <!-- Footer -->
                    <tr>
                        <td style="background-color: #f9fafb; padding: 30px; text-align: center; border-top: 1px solid #e5e7eb;">
                            <p style="color: #6b7280; font-size: 12px; margin: 0 0 10px 0;">
                                <strong>{agency_name}</strong><br>
                                Sistema de Gestión Regulatoria
                            </p>
                            <p style="color: #9ca3af; font-size: 11px; margin: 10px 0 0 0;">
                                Este es un correo automático. Por favor, no respondas a este mensaje.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
HTML;
    }

    private function getPendingDocumentsTemplate(): string
    {
        return <<<'HTML'
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Documentos Pendientes</title>
</head>
<body style="margin: 0; padding: 0; font-family: Arial, sans-serif; background-color: #f4f4f4;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background-color: #f4f4f4; padding: 20px;">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0" style="background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                    <!-- Header -->
                    <tr>
                        <td style="background: linear-gradient(135deg, #f59e0b 0%, #fbbf24 100%); padding: 40px 30px; text-align: center;">
                            <h1 style="color: #ffffff; margin: 0; font-size: 28px; font-weight: bold;">📄 Documentos Pendientes</h1>
                        </td>
                    </tr>
                    
                    <!-- Content -->
                    <tr>
                        <td style="padding: 40px 30px;">
                            <p style="color: #333333; font-size: 16px; line-height: 1.6; margin: 0 0 20px 0;">
                                Hola <strong>{name}</strong>,
                            </p>
                            
                            <p style="color: #333333; font-size: 16px; line-height: 1.6; margin: 0 0 20px 0;">
                                Te recordamos que hay documentos pendientes para el siguiente registro:
                            </p>
                            
                            <div style="background-color: #fffbeb; border-left: 4px solid #f59e0b; padding: 25px; margin: 30px 0; border-radius: 6px;">
                                <table width="100%" cellpadding="0" cellspacing="0">
                                    <tr>
                                        <td style="padding-bottom: 10px;">
                                            <p style="color: #92400e; font-size: 14px; margin: 0; font-weight: bold; text-transform: uppercase;">Producto/Registro</p>
                                            <p style="color: #333333; font-size: 18px; margin: 5px 0 0 0; font-weight: bold;">{product_name}</p>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="padding-top: 15px; padding-bottom: 10px; border-top: 1px solid #fde68a;">
                                            <p style="color: #92400e; font-size: 14px; margin: 0; font-weight: bold; text-transform: uppercase;">Documentos Pendientes</p>
                                            <div style="color: #333333; font-size: 14px; margin: 10px 0 0 0; line-height: 1.8;">
                                                {pending_documents}
                                            </div>
                                        </td>
                                    </tr>
                                    {company_name}
                                    {registration_number}
                                </table>
                            </div>
                            
                            <p style="color: #333333; font-size: 16px; line-height: 1.6; margin: 30px 0 20px 0;">
                                <strong>⚠️ Acción Requerida:</strong> Por favor, sube los documentos faltantes lo antes posible para continuar con el proceso.
                            </p>
                            
                            <div style="background-color: #f0fdfa; border-left: 4px solid #14b8a6; padding: 15px; margin: 20px 0; border-radius: 4px;">
                                <p style="color: #0f766e; font-size: 13px; margin: 0;">
                                    <strong>💡 Tip:</strong> Puedes acceder al sistema para subir los documentos pendientes.
                                </p>
                            </div>
                        </td>
                    </tr>
                    
                    <!-- Footer -->
                    <tr>
                        <td style="background-color: #f9fafb; padding: 30px; text-align: center; border-top: 1px solid #e5e7eb;">
                            <p style="color: #6b7280; font-size: 12px; margin: 0 0 10px 0;">
                                <strong>{agency_name}</strong><br>
                                Sistema de Gestión Regulatoria
                            </p>
                            <p style="color: #9ca3af; font-size: 11px; margin: 10px 0 0 0;">
                                Este es un correo automático. Por favor, no respondas a este mensaje.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
HTML;
    }

    private function getSpecialistAssignmentTemplate(): string
    {
        return <<<'HTML'
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Especialista Asignado</title>
</head>
<body style="margin: 0; padding: 0; font-family: Arial, sans-serif; background-color: #f4f4f4;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background-color: #f4f4f4; padding: 20px;">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0" style="background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                    <!-- Header -->
                    <tr>
                        <td style="background: linear-gradient(135deg, #8b5cf6 0%, #a78bfa 100%); padding: 40px 30px; text-align: center;">
                            <h1 style="color: #ffffff; margin: 0; font-size: 28px; font-weight: bold;">👤 Especialista Asignado</h1>
                        </td>
                    </tr>
                    
                    <!-- Content -->
                    <tr>
                        <td style="padding: 40px 30px;">
                            <p style="color: #333333; font-size: 16px; line-height: 1.6; margin: 0 0 20px 0;">
                                Hola <strong>{name}</strong>,
                            </p>
                            
                            <p style="color: #333333; font-size: 16px; line-height: 1.6; margin: 0 0 20px 0;">
                                Se ha asignado un especialista al siguiente registro:
                            </p>
                            
                            <div style="background-color: #faf5ff; border-left: 4px solid #8b5cf6; padding: 25px; margin: 30px 0; border-radius: 6px;">
                                <table width="100%" cellpadding="0" cellspacing="0">
                                    <tr>
                                        <td style="padding-bottom: 10px;">
                                            <p style="color: #6b21a8; font-size: 14px; margin: 0; font-weight: bold; text-transform: uppercase;">Producto/Registro</p>
                                            <p style="color: #333333; font-size: 18px; margin: 5px 0 0 0; font-weight: bold;">{product_name}</p>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="padding-top: 15px; padding-bottom: 10px; border-top: 1px solid #e9d5ff;">
                                            <p style="color: #6b21a8; font-size: 14px; margin: 0; font-weight: bold; text-transform: uppercase;">Especialista Asignado</p>
                                            <p style="color: #333333; font-size: 18px; margin: 5px 0 0 0; font-weight: bold;">{specialist_name}</p>
                                            {specialist_email}
                                        </td>
                                    </tr>
                                    {company_name}
                                    {registration_number}
                                </table>
                            </div>
                            
                            <p style="color: #333333; font-size: 16px; line-height: 1.6; margin: 30px 0 20px 0;">
                                El especialista asignado se encargará de gestionar y dar seguimiento a este registro. Puedes contactarlo si tienes alguna pregunta.
                            </p>
                        </td>
                    </tr>
                    
                    <!-- Footer -->
                    <tr>
                        <td style="background-color: #f9fafb; padding: 30px; text-align: center; border-top: 1px solid #e5e7eb;">
                            <p style="color: #6b7280; font-size: 12px; margin: 0 0 10px 0;">
                                <strong>{agency_name}</strong><br>
                                Sistema de Gestión Regulatoria
                            </p>
                            <p style="color: #9ca3af; font-size: 11px; margin: 10px 0 0 0;">
                                Este es un correo automático. Por favor, no respondas a este mensaje.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
HTML;
    }

    private function getImportantDateReminderTemplate(): string
    {
        return <<<'HTML'
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recordatorio de Fecha Importante</title>
</head>
<body style="margin: 0; padding: 0; font-family: Arial, sans-serif; background-color: #f4f4f4;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background-color: #f4f4f4; padding: 20px;">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0" style="background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                    <!-- Header -->
                    <tr>
                        <td style="background: linear-gradient(135deg, #06b6d4 0%, #22d3ee 100%); padding: 40px 30px; text-align: center;">
                            <h1 style="color: #ffffff; margin: 0; font-size: 28px; font-weight: bold;">📅 Recordatorio de Fecha Importante</h1>
                        </td>
                    </tr>
                    
                    <!-- Content -->
                    <tr>
                        <td style="padding: 40px 30px;">
                            <p style="color: #333333; font-size: 16px; line-height: 1.6; margin: 0 0 20px 0;">
                                Hola <strong>{name}</strong>,
                            </p>
                            
                            <p style="color: #333333; font-size: 16px; line-height: 1.6; margin: 0 0 20px 0;">
                                Te recordamos que se acerca una fecha importante para el siguiente registro:
                            </p>
                            
                            <div style="background-color: #ecfeff; border-left: 4px solid #06b6d4; padding: 25px; margin: 30px 0; border-radius: 6px;">
                                <table width="100%" cellpadding="0" cellspacing="0">
                                    <tr>
                                        <td style="padding-bottom: 10px;">
                                            <p style="color: #164e63; font-size: 14px; margin: 0; font-weight: bold; text-transform: uppercase;">Evento</p>
                                            <p style="color: #333333; font-size: 18px; margin: 5px 0 0 0; font-weight: bold;">{event_name}</p>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="padding-top: 15px; padding-bottom: 10px; border-top: 1px solid #a5f3fc;">
                                            <p style="color: #164e63; font-size: 14px; margin: 0; font-weight: bold; text-transform: uppercase;">Fecha</p>
                                            <p style="color: #06b6d4; font-size: 20px; margin: 5px 0 0 0; font-weight: bold;">{event_date}</p>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="padding-top: 15px; padding-bottom: 10px; border-top: 1px solid #a5f3fc;">
                                            <p style="color: #164e63; font-size: 14px; margin: 0; font-weight: bold; text-transform: uppercase;">Producto/Registro</p>
                                            <p style="color: #333333; font-size: 16px; margin: 5px 0 0 0;">{product_name}</p>
                                        </td>
                                    </tr>
                                    {company_name}
                                    {registration_number}
                                </table>
                            </div>
                            
                            <p style="color: #333333; font-size: 16px; line-height: 1.6; margin: 30px 0 20px 0;">
                                <strong>⚠️ Acción Requerida:</strong> Por favor, prepara todo lo necesario para esta fecha.
                            </p>
                        </td>
                    </tr>
                    
                    <!-- Footer -->
                    <tr>
                        <td style="background-color: #f9fafb; padding: 30px; text-align: center; border-top: 1px solid #e5e7eb;">
                            <p style="color: #6b7280; font-size: 12px; margin: 0 0 10px 0;">
                                <strong>{agency_name}</strong><br>
                                Sistema de Gestión Regulatoria
                            </p>
                            <p style="color: #9ca3af; font-size: 11px; margin: 10px 0 0 0;">
                                Este es un correo automático. Por favor, no respondas a este mensaje.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
HTML;
    }

    private function getRequirementNotificationTemplate(): string
    {
        return <<<'HTML'
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nuevo Requerimiento</title>
</head>
<body style="margin: 0; padding: 0; font-family: Arial, sans-serif; background-color: #f4f4f4;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background-color: #f4f4f4; padding: 20px;">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0" style="background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                    <!-- Header -->
                    <tr>
                        <td style="background: linear-gradient(135deg, #ec4899 0%, #f472b6 100%); padding: 40px 30px; text-align: center;">
                            <h1 style="color: #ffffff; margin: 0; font-size: 28px; font-weight: bold;">📋 Nuevo Requerimiento</h1>
                        </td>
                    </tr>
                    
                    <!-- Content -->
                    <tr>
                        <td style="padding: 40px 30px;">
                            <p style="color: #333333; font-size: 16px; line-height: 1.6; margin: 0 0 20px 0;">
                                Hola <strong>{name}</strong>,
                            </p>
                            
                            <p style="color: #333333; font-size: 16px; line-height: 1.6; margin: 0 0 20px 0;">
                                Se ha registrado un nuevo requerimiento para el siguiente registro:
                            </p>
                            
                            <div style="background-color: #fdf2f8; border-left: 4px solid #ec4899; padding: 25px; margin: 30px 0; border-radius: 6px;">
                                <table width="100%" cellpadding="0" cellspacing="0">
                                    <tr>
                                        <td style="padding-bottom: 10px;">
                                            <p style="color: #9f1239; font-size: 14px; margin: 0; font-weight: bold; text-transform: uppercase;">Producto/Registro</p>
                                            <p style="color: #333333; font-size: 18px; margin: 5px 0 0 0; font-weight: bold;">{product_name}</p>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="padding-top: 15px; padding-bottom: 10px; border-top: 1px solid #fbcfe8;">
                                            <p style="color: #9f1239; font-size: 14px; margin: 0; font-weight: bold; text-transform: uppercase;">Tipo de Requerimiento</p>
                                            <p style="color: #333333; font-size: 16px; margin: 5px 0 0 0;">{requirement_type}</p>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="padding-top: 15px; padding-bottom: 10px; border-top: 1px solid #fbcfe8;">
                                            <p style="color: #9f1239; font-size: 14px; margin: 0; font-weight: bold; text-transform: uppercase;">Descripción</p>
                                            <div style="color: #333333; font-size: 14px; margin: 10px 0 0 0; line-height: 1.8;">
                                                {requirement_description}
                                            </div>
                                        </td>
                                    </tr>
                                    {company_name}
                                    {registration_number}
                                </table>
                            </div>
                            
                            <p style="color: #333333; font-size: 16px; line-height: 1.6; margin: 30px 0 20px 0;">
                                <strong>⚠️ Acción Requerida:</strong> Por favor, revisa este requerimiento y proporciona la información o documentación solicitada.
                            </p>
                            
                            <div style="background-color: #f0fdfa; border-left: 4px solid #14b8a6; padding: 15px; margin: 20px 0; border-radius: 4px;">
                                <p style="color: #0f766e; font-size: 13px; margin: 0;">
                                    <strong>💡 Tip:</strong> Puedes acceder al sistema para ver más detalles y gestionar este requerimiento.
                                </p>
                            </div>
                        </td>
                    </tr>
                    
                    <!-- Footer -->
                    <tr>
                        <td style="background-color: #f9fafb; padding: 30px; text-align: center; border-top: 1px solid #e5e7eb;">
                            <p style="color: #6b7280; font-size: 12px; margin: 0 0 10px 0;">
                                <strong>{agency_name}</strong><br>
                                Sistema de Gestión Regulatoria
                            </p>
                            <p style="color: #9ca3af; font-size: 11px; margin: 10px 0 0 0;">
                                Este es un correo automático. Por favor, no respondas a este mensaje.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
HTML;
    }
}
