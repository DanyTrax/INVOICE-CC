<?php

namespace App\Providers;

use App\Models\Setting;
use App\Settings\GeneralSettings;
use Illuminate\Auth\Notifications\ResetPassword as ResetPasswordNotification;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
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

        View::composer('*', function ($view): void {
            try {
                $view->with('brandSetting', Setting::current());
            } catch (\Throwable $e) {
                //
            }
        });

        try {
            $settings = app(GeneralSettings::class);
            $tz = $settings->timezone ?? null;
            if (is_string($tz) && $tz !== '') {
                config(['app.timezone' => $tz]);
                @date_default_timezone_set($tz);
            }
        } catch (\Throwable $e) {
            //
        }
    }
}
