<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\CapacitacionVideo;
use App\Models\ChecklistItem;
use App\Models\Company;
use App\Models\ConceptCatalog;
use App\Models\Process;
use App\Models\ProcessDocument;
use App\Models\Proposal;
use App\Models\ProposalPdfTemplate;
use App\Models\Quote;
use App\Models\QuotePdfTemplate;
use App\Models\RegulatoryEvent;
use App\Models\Service;
use App\Models\ServiceType;
use App\Models\Submission;
use App\Models\SystemBackup;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Request as RequestFacade;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;
use Symfony\Component\HttpFoundation\Response;

class ActivityLogService
{
    /**
     * Texto corto en español por ruta (clave: nombre completo admin.*).
     *
     * @var array<string, string>
     */
    protected static array $routeLabels = [
        'admin.dashboard' => 'Panel principal (acción)',
        'admin.backups.store' => 'Creó backup',
        'admin.backups.destroy' => 'Eliminó backup',
        'admin.backups.wipe' => 'Limpió backups',
        'admin.permissions.update' => 'Actualizó matriz de permisos',
        'admin.permissions.hierarchy' => 'Actualizó jerarquía de roles',
        'admin.roles.store' => 'Creó rol',
        'admin.roles.destroy' => 'Eliminó rol',
        'admin.companies.store' => 'Creó empresa / cliente',
        'admin.companies.update' => 'Actualizó empresa / cliente',
        'admin.companies.destroy' => 'Eliminó empresa / cliente',
        'admin.companies.send-invite' => 'Envió invitación a empresa',
        'admin.company-invites.resend' => 'Reenvió invitación de registro',
        'admin.company-invites.destroy' => 'Eliminó invitación de registro pendiente',
        'admin.clients.store' => 'Creó usuario cliente',
        'admin.clients.update' => 'Actualizó usuario cliente',
        'admin.users.client-status.update' => 'Cambió estado de cliente',
        'admin.users.store' => 'Creó usuario (agente)',
        'admin.users.update' => 'Actualizó usuario (agente)',
        'admin.users.destroy' => 'Eliminó usuario',
        'admin.users.send-access-email' => 'Envió correo de acceso',
        'admin.profile.update' => 'Actualizó su perfil',
        'admin.users.disable-two-factor' => 'Desactivó 2FA de un usuario (admin)',
        'admin.profile.two-factor.confirm' => 'Activó verificación en dos pasos (2FA)',
        'admin.profile.two-factor.disable' => 'Desactivó verificación en dos pasos (2FA)',
        'admin.quotes.store' => 'Creó cotización',
        'admin.quotes.update' => 'Actualizó cotización',
        'admin.quotes.destroy' => 'Eliminó cotización',
        'admin.quotes.approve' => 'Aprobó cotización',
        'admin.quotes.anular' => 'Canceló cotización',
        'admin.quotes.pdf-footer.update' => 'Actualizó pie de PDF de cotización',
        'admin.proposals.store' => 'Creó propuesta',
        'admin.proposals.update' => 'Actualizó propuesta',
        'admin.proposals.destroy' => 'Eliminó propuesta',
        'admin.proposals.approve' => 'Aprobó propuesta',
        'admin.proposals.pdf-footer.update' => 'Actualizó pie de PDF de propuesta',
        'admin.concept-catalogs.store' => 'Creó concepto de catálogo',
        'admin.concept-catalogs.update' => 'Actualizó concepto de catálogo',
        'admin.concept-catalogs.destroy' => 'Eliminó concepto de catálogo',
        'admin.service-types.store' => 'Creó tipo de trámite',
        'admin.service-types.update' => 'Actualizó tipo de trámite',
        'admin.services.store' => 'Creó servicio (catálogo)',
        'admin.services.update' => 'Actualizó servicio (catálogo)',
        'admin.services.destroy' => 'Eliminó servicio (catálogo)',
        'admin.processes.store' => 'Creó expediente',
        'admin.processes.destroy' => 'Eliminó expediente',
        'admin.processes.link-to-quote' => 'Vinculó expediente a cotización',
        'admin.processes.submissions.store' => 'Registró sometimiento',
        'admin.processes.checklist-items.store' => 'Agregó ítem a checklist',
        'admin.processes.documents.upload' => 'Subió documento a expediente (Drive)',
        'admin.processes.documents.destroy' => 'Eliminó documento de expediente',
        'admin.submissions.register-response' => 'Registró respuesta INVIMA',
        'admin.submissions.update' => 'Actualizó sometimiento',
        'admin.submissions.update-radicado' => 'Actualizó radicado',
        'admin.submissions.destroy-radicado' => 'Eliminó radicado',
        'admin.submissions.link-quote' => 'Vinculó sometimiento a cotización',
        'admin.submissions.destroy' => 'Eliminó sometimiento / ciclo',
        'admin.regulatory-events.update' => 'Actualizó evento regulatorio',
        'admin.regulatory-events.destroy' => 'Eliminó evento regulatorio',
        'admin.submissions.events.store-auto' => 'Registró evento AUTO',
        'admin.submissions.events.store-resolution' => 'Registró resolución',
        'admin.checklist-items.update' => 'Actualizó ítem de checklist',
        'admin.capacitaciones.store' => 'Creó capacitación',
        'admin.capacitaciones.update' => 'Actualizó capacitación',
        'admin.capacitaciones.destroy' => 'Eliminó capacitación',
        'admin.capacitaciones.completar' => 'Marcó capacitación como vista',
        'admin.settings.update' => 'Actualizó configuración',
        'admin.settings.git-pull' => 'Ejecutó git pull (servidor)',
        'admin.settings.artisan' => 'Ejecutó comando Artisan',
        'admin.settings.test-drive-connection' => 'Probó conexión Google Drive',
        'admin.settings.delete-user-by-email' => 'Eliminó usuario por correo (sistema)',
        'admin.settings.email-logs.destroy' => 'Eliminó log de correo',
        'admin.settings.drive-operations-log.delete' => 'Eliminó historial operaciones Drive',
        'admin.settings.quote-pdf-templates.store' => 'Creó plantilla PDF cotización',
        'admin.settings.quote-pdf-templates.update' => 'Actualizó plantilla PDF cotización',
        'admin.settings.quote-pdf-templates.destroy' => 'Eliminó plantilla PDF cotización',
        'admin.settings.proposal-pdf-templates.store' => 'Creó plantilla PDF propuesta',
        'admin.settings.proposal-pdf-templates.update' => 'Actualizó plantilla PDF propuesta',
        'admin.settings.proposal-pdf-templates.destroy' => 'Eliminó plantilla PDF propuesta',
    ];

    /**
     * Registrar una actividad.
     */
    public function log(
        string $action,
        string $description,
        ?Model $subject = null,
        ?array $properties = null
    ): ActivityLog {
        return ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => $action,
            'description' => $description,
            'subject_type' => $subject ? $subject->getMorphClass() : null,
            'subject_id' => $subject?->getKey(),
            'properties' => $properties,
            'ip_address' => RequestFacade::ip(),
            'user_agent' => RequestFacade::userAgent(),
        ]);
    }

    /**
     * Registro automático desde middleware tras mutación HTTP exitosa en admin.
     */
    public function logAdminMutation(Request $request, Response $response): void
    {
        $route = $request->route();
        if (! $route) {
            return;
        }

        $routeName = $route->getName();
        $method = $request->method();
        $action = $this->inferActionKey($method);

        $description = $this->buildMutationDescription($request, $routeName);
        $subject = $this->guessPrimarySubject($request);
        $properties = [
            'route' => $routeName,
            'http_method' => $method,
            'http_status' => $response->getStatusCode(),
        ];

        $this->log($action, $description, $subject, $properties);
    }

    protected function inferActionKey(string $method): string
    {
        return match ($method) {
            'POST' => 'mutation',
            'PUT', 'PATCH' => 'updated',
            'DELETE' => 'deleted',
            default => 'mutation',
        };
    }

    protected function buildMutationDescription(Request $request, string $routeName): string
    {
        $label = self::$routeLabels[$routeName] ?? $this->fallbackRouteLabel($routeName);
        $suffix = $this->summarizeRouteParameters($request);

        return $suffix ? "{$label} · {$suffix}" : $label;
    }

    protected function fallbackRouteLabel(string $routeName): string
    {
        $short = Str::after($routeName, 'admin.');
        $short = str_replace(['.', '-'], ' ', $short);

        return Str::ucfirst($short);
    }

    protected function summarizeRouteParameters(Request $request): string
    {
        $route = $request->route();
        if (! $route) {
            return '';
        }

        $parts = [];
        foreach ($route->parameters() as $value) {
            if ($value instanceof Model) {
                $s = $this->summarizeModel($value);
                if ($s !== '') {
                    $parts[] = $s;
                }
            }
        }

        return implode(' · ', array_unique($parts));
    }

    protected function guessPrimarySubject(Request $request): ?Model
    {
        $route = $request->route();
        if (! $route) {
            return null;
        }

        foreach ($route->parameters() as $value) {
            if ($value instanceof Model) {
                return $value;
            }
        }

        return null;
    }

    protected function summarizeModel(Model $model): string
    {
        return match (true) {
            $model instanceof Company => $model->name,
            $model instanceof User => trim($model->name.' <'.$model->email.'>'),
            $model instanceof Quote => 'Cotización '.($model->consecutive ?? '#'.$model->getKey()),
            $model instanceof Proposal => 'Propuesta '.($model->consecutive ?? '#'.$model->getKey()),
            $model instanceof Process => 'Expediente #'.$model->getKey().($model->expediente_invima ? ' (INVIMA '.$model->expediente_invima.')' : ''),
            $model instanceof Submission => 'Sometimiento #'.$model->getKey(),
            $model instanceof RegulatoryEvent => 'Evento regulatorio #'.$model->getKey(),
            $model instanceof ChecklistItem => 'Checklist: '.Str::limit($model->document_name ?? '#'.$model->getKey(), 80),
            $model instanceof ProcessDocument => 'Documento: '.($model->file_name ?? '#'.$model->getKey()),
            $model instanceof ServiceType => $model->name ?? '#'.$model->getKey(),
            $model instanceof Service => $model->name ?? '#'.$model->getKey(),
            $model instanceof ConceptCatalog => $model->name ?? '#'.$model->getKey(),
            $model instanceof CapacitacionVideo => $model->titulo ?? '#'.$model->getKey(),
            $model instanceof Role => 'Rol: '.($model->name ?? '#'.$model->getKey()),
            $model instanceof SystemBackup => $model->name ?? 'Backup #'.$model->getKey(),
            $model instanceof QuotePdfTemplate => 'Plantilla cotización: '.($model->name ?? '#'.$model->getKey()),
            $model instanceof ProposalPdfTemplate => 'Plantilla propuesta: '.($model->name ?? '#'.$model->getKey()),
            default => class_basename($model).' #'.$model->getKey(),
        };
    }

    /**
     * Acciones estándar para etiquetas.
     */
    public static function actionLabels(): array
    {
        return [
            'login' => 'Inicio de sesión',
            'logout' => 'Cierre de sesión',
            'created' => 'Creó',
            'updated' => 'Actualizó',
            'deleted' => 'Eliminó',
            'mutation' => 'Envió / registró (POST)',
            'viewed' => 'Consultó',
            'sent_email' => 'Envió correo',
        ];
    }
}
