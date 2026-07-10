<?php

namespace App\Support;

use Illuminate\Support\Facades\Route;

class AdminBreadcrumb
{
    /**
     * @return array<int, array{label: string, url: string|null}>
     */
    public static function resolve(): array
    {
        $name = Route::currentRouteName();
        if (! $name || ! str_starts_with($name, 'admin.') || $name === 'admin.dashboard') {
            return [];
        }

        $crumbs = match (true) {
            str_starts_with($name, 'admin.associates.') => self::merge(self::groupRecaudos(), self::resourceCrumbs(
                $name,
                'admin.associates.',
                'Asociados',
                'admin.associates.index',
                'Nuevo asociado',
                'Editar asociado'
            )),
            str_starts_with($name, 'admin.concepts.') => self::merge(self::groupRecaudos(), self::resourceCrumbs(
                $name,
                'admin.concepts.',
                'Conceptos de cobro',
                'admin.concepts.index',
                'Nuevo concepto',
                'Editar concepto'
            )),
            str_starts_with($name, 'admin.invoices.') => self::merge(self::groupRecaudos(), self::invoiceCrumbs($name)),
            str_starts_with($name, 'admin.agents.') => self::merge(self::groupDirectorio(), [
                ['label' => 'Usuarios', 'url' => null],
            ]),
            str_starts_with($name, 'admin.users.') => self::merge(self::groupDirectorio(), self::resourceCrumbs(
                $name,
                'admin.users.',
                'Usuarios',
                'admin.users.index',
                'Nuevo usuario',
                'Editar usuario',
                'Ver usuario'
            )),
            str_starts_with($name, 'admin.brand-settings.') => self::merge(self::groupSistema(), [
                ['label' => 'Marca Blanca', 'url' => null],
            ]),
            str_starts_with($name, 'admin.two-factor-settings.') => self::merge(self::groupSistema(), [
                ['label' => 'Verificación 2FA', 'url' => null],
            ]),
            str_starts_with($name, 'admin.settings.') => self::merge(self::groupSistema(), self::settingsCrumbs($name)),
            str_starts_with($name, 'admin.backups.') => self::merge(self::groupSistema(), [
                ['label' => 'Backups', 'url' => route('admin.backups.index')],
            ]),
            str_starts_with($name, 'admin.permissions.') || str_starts_with($name, 'admin.roles.') => self::merge(self::groupSistema(), [
                ['label' => 'Permisos', 'url' => null],
            ]),
            str_starts_with($name, 'admin.activity-logs.') => self::merge(self::groupSistema(), self::activityLogsCrumbs($name)),
            $name === 'admin.profile' || str_starts_with($name, 'admin.profile.') => [
                ['label' => 'Mi perfil', 'url' => null],
            ],
            default => [],
        };

        return self::finalizeTrail($crumbs, $name);
    }

    /**
     * @param  array<int, array{label: string, url: string|null}>  $parts
     * @return array<int, array{label: string, url: string|null}>
     */
    private static function merge(array $parts): array
    {
        return array_values(array_filter($parts));
    }

    /** @return array<int, array{label: string, url: string|null}> */
    private static function groupRecaudos(): array
    {
        return [['label' => 'Recaudos', 'url' => null]];
    }

    /** @return array<int, array{label: string, url: string|null}> */
    private static function groupDirectorio(): array
    {
        return [['label' => 'Directorio', 'url' => null]];
    }

    /** @return array<int, array{label: string, url: string|null}> */
    private static function groupSistema(): array
    {
        return [['label' => 'Sistema', 'url' => null]];
    }

    /**
     * @return array<int, array{label: string, url: string|null}>
     */
    private static function resourceCrumbs(
        string $routeName,
        string $prefix,
        string $indexLabel,
        string $indexRoute,
        string $createLabel,
        string $editLabel,
        string $showLabel = 'Detalle'
    ): array {
        $action = str_replace($prefix, '', $routeName);

        return match ($action) {
            'index' => [['label' => $indexLabel, 'url' => null]],
            'create' => [
                ['label' => $indexLabel, 'url' => route($indexRoute)],
                ['label' => $createLabel, 'url' => null],
            ],
            'edit' => [
                ['label' => $indexLabel, 'url' => route($indexRoute)],
                ['label' => $editLabel, 'url' => null],
            ],
            'show' => [
                ['label' => $indexLabel, 'url' => route($indexRoute)],
                ['label' => $showLabel, 'url' => null],
            ],
            default => [['label' => $indexLabel, 'url' => null]],
        };
    }

    /**
     * @return array<int, array{label: string, url: string|null}>
     */
    private static function invoiceCrumbs(string $routeName): array
    {
        $index = ['label' => 'Cuentas de cobro', 'url' => route('admin.invoices.index')];
        $invoice = Route::current()->parameter('invoice');

        return match ($routeName) {
            'admin.invoices.index' => [['label' => 'Cuentas de cobro', 'url' => null]],
            'admin.invoices.create' => [$index, ['label' => 'Nueva cuenta', 'url' => null]],
            'admin.invoices.edit' => [
                $index,
                ['label' => $invoice ? (string) $invoice->number : 'Editar', 'url' => $invoice ? route('admin.invoices.show', $invoice) : null],
                ['label' => 'Editar', 'url' => null],
            ],
            'admin.invoices.show' => [
                $index,
                ['label' => $invoice ? (string) $invoice->number : 'Detalle', 'url' => null],
            ],
            default => [$index, ['label' => 'Detalle', 'url' => null]],
        };
    }

    /**
     * @return array<int, array{label: string, url: string|null}>
     */
    private static function settingsCrumbs(string $routeName): array
    {
        $configIndex = ['label' => 'Configuración', 'url' => route('admin.settings.section', 'mail')];

        if ($routeName === 'admin.settings.index') {
            return [['label' => 'Configuración', 'url' => null]];
        }

        if ($routeName === 'admin.settings.section') {
            $section = (string) (Route::current()->parameter('section') ?? 'mail');
            $label = self::settingsSectionLabel($section);

            if ($section === 'system') {
                $sub = request()->query('system_sub', 'git');
                $subLabel = self::settingsSystemSubLabel((string) $sub);

                return [
                    $configIndex,
                    ['label' => $label, 'url' => route('admin.settings.section', 'system').'?system_sub=git'],
                    ['label' => $subLabel, 'url' => null],
                ];
            }

            return [
                $configIndex,
                ['label' => $label, 'url' => null],
            ];
        }

        return [['label' => 'Configuración', 'url' => null]];
    }

    private static function settingsSectionLabel(string $section): string
    {
        return match ($section) {
            'mail' => 'Correo & SMTP',
            'templates' => 'Plantillas de email',
            'history' => 'Correos enviados',
            'system' => 'Sistema',
            'legal-policies' => 'Políticas legales',
            'login-lockouts' => 'Bloqueos de acceso',
            default => 'Configuración',
        };
    }

    private static function settingsSystemSubLabel(string $sub): string
    {
        return match ($sub) {
            'git' => 'Actualización (Git)',
            'delete-user' => 'Eliminar usuario',
            'customization' => 'Personalización',
            'errors' => 'Errores de la aplicación',
            default => 'Sistema',
        };
    }

    /**
     * @return array<int, array{label: string, url: string|null}>
     */
    private static function activityLogsCrumbs(string $routeName): array
    {
        $index = ['label' => 'Registros de actividad', 'url' => route('admin.activity-logs.index')];

        if ($routeName === 'admin.activity-logs.index') {
            return [['label' => 'Registros de actividad', 'url' => null]];
        }

        if ($routeName === 'admin.activity-logs.show') {
            $user = Route::current()->parameter('user');

            return [
                $index,
                ['label' => $user ? (string) $user->name : 'Detalle', 'url' => null],
            ];
        }

        return [['label' => 'Registros de actividad', 'url' => null]];
    }

    /**
     * @param  array<int, array{label: string, url: string|null}>  $crumbs
     * @return array<int, array{label: string, url: string|null}>
     */
    private static function finalizeTrail(array $crumbs, string $routeName): array
    {
        if ($crumbs === []) {
            return [];
        }

        // En índices de listado, el último ítem no lleva enlace.
        if (str_ends_with($routeName, '.index') && count($crumbs) > 0) {
            $last = array_key_last($crumbs);
            $crumbs[$last]['url'] = null;
        }

        // Backups: en acciones secundarias mantener enlace al listado.
        if (str_starts_with($routeName, 'admin.backups.') && $routeName !== 'admin.backups.index' && count($crumbs) > 1) {
            $crumbs[count($crumbs) - 1]['url'] = null;
        }

        return $crumbs;
    }
}
