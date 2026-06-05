<?php

namespace App\Providers;

use App\Models\Process;
use App\Models\Quote;
use App\Models\RegulatoryEvent;
use App\Observers\QuoteObserver;
use App\Observers\RegulatoryEventObserver;
use App\Services\PermissionService;
use App\Services\ProcessAccessService;
use App\Settings\GeneralSettings;
use App\Support\UploadHelper;
use Illuminate\Auth\Notifications\ResetPassword as ResetPasswordNotification;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        try {
            UploadHelper::ensureProcessUploadTempDir();
        } catch (\Throwable $e) {
            // No bloquear la app en migraciones o primer despliegue; la subida validará de nuevo.
        }

        ResetPasswordNotification::toMailUsing(function (object $notifiable, string $token) {
            $url = url(route('password.reset', [
                'token' => $token,
                'email' => $notifiable->getEmailForPasswordReset(),
            ], false));

            return (new MailMessage)
                ->subject('Restablecer contraseña — '.config('app.name'))
                ->line('Recibiste este correo porque alguien solicitó restablecer la contraseña de tu cuenta.')
                ->action('Restablecer contraseña', $url)
                ->line('Este enlace caduca en '.(int) config('auth.passwords.'.config('auth.defaults.passwords').'.expire', 60).' minutos.')
                ->line('Si no solicitaste el cambio, ignora este mensaje.');
        });

        Quote::observe(QuoteObserver::class);
        RegulatoryEvent::observe(RegulatoryEventObserver::class);

        // Solicitudes: @processCan('delete'|'edit'|'feed'|'view'|...) — solo permiso global
        Blade::if('processCan', function (string $action) {
            $s = app(PermissionService::class);
            $map = [
                'feed' => PermissionService::ACTION_TIMELINE_FEED,
            ];
            $needed = $map[$action] ?? $action;

            return $s->userHasProcessAction($needed);
        });

        // Solicitudes con contexto: asignación por solicitud + permisos globales (upload = subir a Drive; feed o gestión doc.)
        Blade::if('processCanFor', function ($process, string $action) {
            if (! $process instanceof Process) {
                return false;
            }
            $user = auth()->user();
            if (! $user) {
                return false;
            }
            $svc = app(ProcessAccessService::class);

            return match ($action) {
                'view' => $svc->canViewProcess($user, $process),
                'feed' => $svc->canFeedTimelineOnProcess($user, $process),
                'upload' => $svc->canUploadDocumentsOnProcess($user, $process),
                'edit' => $svc->canManageDocumentsOnProcess($user, $process),
                'delete' => $svc->canDeleteProcess($user, $process),
                default => false,
            };
        });

        // Cotizaciones: @quoteCan('view'|'edit'|'delete'|'pdf')
        Blade::if('quoteCan', function (string $action) {
            $s = app(PermissionService::class);
            if ($action === 'pdf') {
                return $s->userCanDownloadQuotePdf();
            }

            return $s->userHasQuoteAction($action);
        });

        // Zona horaria configurable desde Configuración > Sistema
        try {
            $settings = app(GeneralSettings::class);
            $tz = $settings->timezone ?? null;
            if (is_string($tz) && $tz !== '') {
                config(['app.timezone' => $tz]);
                @date_default_timezone_set($tz);
            }
        } catch (\Throwable $e) {
            // Es posible que la tabla de settings aún no exista durante migraciones iniciales; ignorar.
        }
    }
}
