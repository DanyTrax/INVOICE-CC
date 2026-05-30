<?php

use App\Http\Controllers\Admin\ActivityLogController;
use App\Http\Controllers\Admin\BackupController;
use App\Http\Controllers\Admin\CapacitacionController;
use App\Http\Controllers\Admin\CompanyController;
use App\Http\Controllers\Admin\ConceptCatalogController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\PermissionController;
use App\Http\Controllers\Admin\ProcessAssignmentController;
use App\Http\Controllers\Admin\ProcessController;
use App\Http\Controllers\Admin\ProposalController;
use App\Http\Controllers\Admin\ProposalPdfTemplateController;
use App\Http\Controllers\Admin\QuoteController;
use App\Http\Controllers\Admin\QuotePdfTemplateController;
use App\Http\Controllers\Admin\RegistrationController;
use App\Http\Controllers\Admin\RegulatoryEventController;
use App\Http\Controllers\Admin\ServiceController;
use App\Http\Controllers\Admin\ServiceTypeController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\UserPreferenceController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\Auth\TwoFactorChallengeController;
use App\Http\Controllers\Auth\TwoFactorProfileController;
use App\Http\Controllers\ClientPortalController;
use App\Http\Controllers\ClientRegisterController;
use App\Http\Controllers\LegalPageController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// Raíz: autenticado → panel; invitado → login (información de la app en /acerca y pie en pantalla de login)
Route::get('/', function () {
    if (Auth::check()) {
        return Auth::user()->hasRole('client') ? redirect()->route('portal.dashboard') : redirect()->route('admin.dashboard');
    }

    return redirect()->route('login');
})->name('home');

Route::get('/acerca', function () {
    return view('public.app-purpose');
})->name('public.app-purpose');

// Autenticación
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

Route::get('/forgot-password', [ForgotPasswordController::class, 'show'])
    ->middleware('guest')
    ->name('password.request');
Route::post('/forgot-password', [ForgotPasswordController::class, 'send'])
    ->middleware(['guest', 'throttle:10,1'])
    ->name('password.email');

Route::middleware(['guest', 'two_factor.pending', 'throttle:20,1'])->group(function () {
    Route::get('/two-factor/challenge', [TwoFactorChallengeController::class, 'show'])->name('two-factor.challenge');
    Route::post('/two-factor/challenge', [TwoFactorChallengeController::class, 'verify'])->name('two-factor.verify');
    Route::post('/two-factor/recovery-email', [TwoFactorChallengeController::class, 'sendRecoveryEmail'])->name('two-factor.recovery.email');
});

Route::get('/two-factor/recovery/{token}', [TwoFactorChallengeController::class, 'confirmRecovery'])
    ->name('two-factor.recovery.confirm');

// Páginas legales (públicas; contenido editable en Configuración → Empresa → Políticas)
Route::get('/politica-privacidad', [LegalPageController::class, 'privacy'])->name('legal.privacy');
Route::get('/terminos-condiciones', [LegalPageController::class, 'terms'])->name('legal.terms');

// Establecer/restablecer contraseña (link enviado por admin a especialistas)
Route::get('/establecer-contrasena', [ResetPasswordController::class, 'show'])->name('password.reset');
Route::post('/establecer-contrasena', [ResetPasswordController::class, 'store'])->name('password.reset.store');

// Registro por invitación (link de único uso, guest)
Route::get('/registrarse', [ClientRegisterController::class, 'show'])->name('client.register');
Route::post('/registrarse', [ClientRegisterController::class, 'store']);

// Portal Cliente (rol client: solo informativo y descargas)
Route::middleware(['auth', 'client', 'client.portal.access'])->prefix('portal')->name('portal.')->group(function () {
    Route::get('/cuenta-deshabilitada', [ClientPortalController::class, 'accountDisabled'])->name('account-disabled');
    Route::get('/perfil', [ClientPortalController::class, 'profile'])->name('profile');
    Route::put('/perfil', [ClientPortalController::class, 'updateProfile'])->name('profile.update');
    Route::post('/perfil/two-factor/start', [TwoFactorProfileController::class, 'start'])->name('profile.two-factor.start');
    Route::post('/perfil/two-factor/confirm', [TwoFactorProfileController::class, 'confirm'])->name('profile.two-factor.confirm');
    Route::post('/perfil/two-factor/disable', [TwoFactorProfileController::class, 'disable'])->name('profile.two-factor.disable');
    Route::post('/perfil/two-factor/regenerate-recovery', [TwoFactorProfileController::class, 'regenerateRecovery'])->name('profile.two-factor.regenerate-recovery');
    Route::post('/perfil/two-factor/cancel', [TwoFactorProfileController::class, 'cancelSetup'])->name('profile.two-factor.cancel');
    Route::get('/', [ClientPortalController::class, 'dashboard'])->name('dashboard');
    Route::get('/registrations', [ClientPortalController::class, 'index'])->name('registrations.index');
    Route::get('/registrations/{registration}', [ClientPortalController::class, 'show'])->name('registrations.show');
    Route::get('/registrations/{registration}/documents/{document}/view', [ClientPortalController::class, 'viewDocument'])->name('documents.view');
    Route::get('/registrations/{registration}/documents/{document}/download', [ClientPortalController::class, 'downloadDocument'])->name('documents.download');
});

// Rutas Admin (auth + no clientes + permisos granulares por módulo)
Route::middleware(['auth', 'not.client', 'module.permission', 'admin.no-cache', 'log.admin.mutations'])->prefix('admin')->name('admin.')->group(function () {
    Route::post('/preferences/theme', [UserPreferenceController::class, 'updateTheme'])->name('preferences.theme');

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Backups de sistema (solo super_admin dentro del controlador)
    Route::get('/backups', [BackupController::class, 'index'])->name('backups.index');
    Route::post('/backups', [BackupController::class, 'store'])->name('backups.store');
    Route::get('/backups/{backup}/download', [BackupController::class, 'download'])->name('backups.download');
    Route::delete('/backups/{backup}', [BackupController::class, 'destroy'])->name('backups.destroy');
    Route::post('/backups/import', [BackupController::class, 'import'])->name('backups.import');
    Route::post('/backups/{backup}/restore', [BackupController::class, 'restore'])->name('backups.restore');
    Route::post('/backups/wipe', [BackupController::class, 'wipe'])->name('backups.wipe');

    // Permisos y roles personalizados
    Route::get('/permissions', [PermissionController::class, 'index'])->name('permissions.index');
    Route::post('/permissions/update', [PermissionController::class, 'updatePermissions'])->name('permissions.update');
    Route::post('/permissions/hierarchy', [PermissionController::class, 'updateHierarchy'])->name('permissions.hierarchy');
    Route::post('/roles', [PermissionController::class, 'storeRole'])->name('roles.store');
    Route::delete('/roles/{role}', [PermissionController::class, 'destroyRole'])->name('roles.destroy');

    // Registros de actividad por usuario
    Route::get('activity-logs', [ActivityLogController::class, 'index'])->name('activity-logs.index');
    Route::delete('activity-logs', [ActivityLogController::class, 'destroyAll'])->name('activity-logs.destroy-all');
    Route::get('activity-logs/user/{user}', [ActivityLogController::class, 'show'])->name('activity-logs.show');

    // Companies (Empresas)
    Route::get('companies/{company}/invite-data', [CompanyController::class, 'inviteData'])->name('companies.invite-data');
    Route::post('companies/{company}/send-invite', [CompanyController::class, 'sendInvite'])->name('companies.send-invite');
    Route::post('company-invites/{invite}/resend', [CompanyController::class, 'resendInvite'])->name('company-invites.resend');
    Route::delete('company-invites/{invite}', [CompanyController::class, 'destroyInvite'])->name('company-invites.destroy');
    Route::resource('companies', CompanyController::class);

    // Listas separadas: Clientes (rol client) y Agentes (no client)
    Route::get('clients', [UserController::class, 'clients'])->name('clients.index');
    Route::get('clients/create', [UserController::class, 'createClient'])->name('clients.create');
    Route::post('clients', [UserController::class, 'storeClient'])->name('clients.store');
    Route::get('clients/{user}/edit', [UserController::class, 'editClient'])->name('clients.edit');
    Route::put('clients/{user}', [UserController::class, 'updateClient'])->name('clients.update');
    Route::get('agents', [UserController::class, 'agents'])->name('agents.index');

    // Registrations (módulo jubilado: se usa Solicitudes / Procesos)
    // Route::get('/registrations/{registration}/documents/{document}/view', [RegistrationController::class, 'viewDocument'])
    //     ->name('registrations.documents.view');
    // Route::get('/registrations/{registration}/documents/{document}/download', [RegistrationController::class, 'downloadDocument'])
    //     ->name('registrations.documents.download');
    // Route::delete('/registrations/{registration}/documents/{document}', [RegistrationController::class, 'destroyDocument'])
    //     ->name('registrations.documents.destroy');
    // Route::resource('registrations', RegistrationController::class);

    // Cotizaciones (pre-venta)
    Route::get('quotes/suggest-consecutive', [QuoteController::class, 'suggestConsecutive'])->name('quotes.suggest-consecutive');
    Route::get('quotes', [QuoteController::class, 'index'])->name('quotes.index');
    Route::get('quotes/create', [QuoteController::class, 'create'])->name('quotes.create');
    Route::post('quotes', [QuoteController::class, 'store'])->name('quotes.store');
    Route::get('quotes/{quote}', [QuoteController::class, 'show'])->name('quotes.show');
    Route::get('quotes/{quote}/edit', [QuoteController::class, 'edit'])->name('quotes.edit');
    Route::put('quotes/{quote}', [QuoteController::class, 'update'])->name('quotes.update');
    Route::post('quotes/{quote}/approve', [QuoteController::class, 'approve'])->name('quotes.approve');
    Route::get('quotes/{quote}/pdf', [QuoteController::class, 'pdf'])->name('quotes.pdf');
    Route::patch('quotes/{quote}/pdf-footer', [QuoteController::class, 'updatePdfFooter'])->name('quotes.pdf-footer.update');
    Route::post('quotes/{quote}/anular', [QuoteController::class, 'anular'])->name('quotes.anular');
    Route::delete('quotes/{quote}', [QuoteController::class, 'destroy'])->name('quotes.destroy');

    // Propuestas (honorarios / concepto–alcance)
    Route::get('proposals/suggest-consecutive', [ProposalController::class, 'suggestConsecutive'])->name('proposals.suggest-consecutive');
    Route::get('proposals', [ProposalController::class, 'index'])->name('proposals.index');
    Route::get('proposals/create', [ProposalController::class, 'create'])->name('proposals.create');
    Route::post('proposals', [ProposalController::class, 'store'])->name('proposals.store');
    Route::get('proposals/{proposal}', [ProposalController::class, 'show'])->name('proposals.show');
    Route::get('proposals/{proposal}/edit', [ProposalController::class, 'edit'])->name('proposals.edit');
    Route::put('proposals/{proposal}', [ProposalController::class, 'update'])->name('proposals.update');
    Route::post('proposals/{proposal}/approve', [ProposalController::class, 'approve'])->name('proposals.approve');
    Route::get('proposals/{proposal}/pdf', [ProposalController::class, 'pdf'])->name('proposals.pdf');
    Route::patch('proposals/{proposal}/pdf-footer', [ProposalController::class, 'updatePdfFooter'])->name('proposals.pdf-footer.update');
    Route::delete('proposals/{proposal}', [ProposalController::class, 'destroy'])->name('proposals.destroy');

    // Catálogo de conceptos (opcional, alimenta propuestas)
    Route::resource('concept-catalogs', ConceptCatalogController::class)->except(['show']);

    // Tipos de Trámite (ServiceTypes) — módulo "Trámite"
    Route::get('service-types', [ServiceTypeController::class, 'index'])->name('service-types.index');
    Route::get('service-types/create', [ServiceTypeController::class, 'create'])->name('service-types.create');
    Route::post('service-types', [ServiceTypeController::class, 'store'])->name('service-types.store');
    Route::get('service-types/{serviceType}/edit', [ServiceTypeController::class, 'edit'])->name('service-types.edit');
    Route::put('service-types/{serviceType}', [ServiceTypeController::class, 'update'])->name('service-types.update');
    Route::delete('service-types/{serviceType}', [ServiceTypeController::class, 'destroy'])->name('service-types.destroy');

    // Servicios (catálogo para cotizaciones: nombre + alcance por defecto)
    Route::get('services/list-for-quotes', [ServiceController::class, 'listForQuotes'])->name('services.list-for-quotes');
    Route::get('services', [ServiceController::class, 'index'])->name('services.index');
    Route::get('services/create', [ServiceController::class, 'create'])->name('services.create');
    Route::post('services', [ServiceController::class, 'store'])->name('services.store');
    Route::get('services/{service}/edit', [ServiceController::class, 'edit'])->name('services.edit');
    Route::put('services/{service}', [ServiceController::class, 'update'])->name('services.update');
    Route::delete('services/{service}', [ServiceController::class, 'destroy'])->name('services.destroy');

    // Solicitudes (processes) y eventos regulatorios
    Route::get('processes', [ProcessController::class, 'index'])->name('processes.index');
    Route::get('processes/monitor', [ProcessController::class, 'masterList'])->name('processes.monitor');
    Route::get('processes/history', [ProcessController::class, 'history'])->name('processes.history');
    Route::get('processes/export', [ProcessController::class, 'export'])->name('processes.export');
    Route::get('processes/create', [ProcessController::class, 'create'])->name('processes.create');
    Route::post('processes', [ProcessController::class, 'store'])->name('processes.store');
    Route::get('processes/{process}', [ProcessController::class, 'show'])->name('processes.show');
    Route::get('processes/{process}/assignments', [ProcessAssignmentController::class, 'show'])->name('processes.assignments.show');
    Route::put('processes/{process}/assignments', [ProcessAssignmentController::class, 'update'])->name('processes.assignments.update');
    Route::delete('processes/{process}', [ProcessController::class, 'destroy'])->name('processes.destroy');
    Route::post('processes/{process}/link-to-quote', [ProcessController::class, 'linkToQuote'])->name('processes.link-to-quote');
    Route::post('processes/{process}/submissions', [ProcessController::class, 'storeSubmission'])->name('processes.submissions.store');
    Route::post('submissions/{submission}/register-response', [ProcessController::class, 'registerResponse'])->name('submissions.register-response');
    Route::put('submissions/{submission}', [ProcessController::class, 'updateSubmission'])->name('submissions.update');
    Route::put('submissions/{submission}/radicado', [ProcessController::class, 'updateRadicado'])->name('submissions.update-radicado');
    Route::delete('submissions/{submission}/radicado', [ProcessController::class, 'destroyRadicado'])->name('submissions.destroy-radicado');
    Route::put('submissions/{submission}/link-quote', [ProcessController::class, 'linkSubmissionQuote'])->name('submissions.link-quote');
    Route::delete('submissions/{submission}', [ProcessController::class, 'destroySubmission'])->name('submissions.destroy');
    Route::put('regulatory-events/{regulatoryEvent}', [RegulatoryEventController::class, 'update'])->name('regulatory-events.update');
    Route::delete('regulatory-events/{regulatoryEvent}', [RegulatoryEventController::class, 'destroy'])->name('regulatory-events.destroy');
    Route::post('processes/{process}/checklist-items', [ProcessController::class, 'storeChecklistItem'])->name('processes.checklist-items.store');
    Route::put('checklist-items/{checklistItem}', [ProcessController::class, 'updateChecklistItem'])->name('checklist-items.update');
    Route::post('processes/{process}/documents', [ProcessController::class, 'uploadDocument'])->name('processes.documents.upload');
    Route::get('processes/{process}/documents/{processDocument}/view', [ProcessController::class, 'viewDocument'])->name('processes.documents.view');
    Route::get('processes/{process}/documents/{processDocument}/download', [ProcessController::class, 'downloadDocument'])->name('processes.documents.download');
    Route::delete('processes/{process}/documents/{processDocument}', [ProcessController::class, 'destroyDocument'])->name('processes.documents.destroy');
    Route::post('submissions/{submission}/events/auto', [RegulatoryEventController::class, 'storeAuto'])->name('submissions.events.store-auto');
    Route::post('submissions/{submission}/events/resolution', [RegulatoryEventController::class, 'storeResolution'])->name('submissions.events.store-resolution');

    // Capacitaciones (videos para especialistas; solo activos pueden ver; gestor puede subir/editar/borrar y descargar reporte)
    Route::get('capacitaciones', [CapacitacionController::class, 'index'])->name('capacitaciones.index');
    Route::get('capacitaciones/create', [CapacitacionController::class, 'create'])->name('capacitaciones.create');
    Route::post('capacitaciones', [CapacitacionController::class, 'store'])->name('capacitaciones.store');
    Route::get('capacitaciones/{capacitacionVideo}/edit', [CapacitacionController::class, 'edit'])->name('capacitaciones.edit');
    Route::put('capacitaciones/{capacitacionVideo}', [CapacitacionController::class, 'update'])->name('capacitaciones.update');
    Route::delete('capacitaciones/{capacitacionVideo}', [CapacitacionController::class, 'destroy'])->name('capacitaciones.destroy');
    Route::get('capacitaciones/{capacitacionVideo}/ver', [CapacitacionController::class, 'ver'])->name('capacitaciones.ver');
    Route::get('capacitaciones/stream/{capacitacionVideo}', [CapacitacionController::class, 'stream'])->name('capacitaciones.stream');
    Route::post('capacitaciones/{capacitacionVideo}/completar', [CapacitacionController::class, 'completar'])->name('capacitaciones.completar');
    Route::get('capacitaciones/reporte/pdf', [CapacitacionController::class, 'reportePdf'])->name('capacitaciones.reporte.pdf');
    Route::get('capacitaciones/reporte/video/{capacitacionVideo}/pdf', [CapacitacionController::class, 'reporteVideoPdf'])->name('capacitaciones.reporte.video.pdf');

    // Users
    Route::post('users/{user}/send-access-email', [UserController::class, 'sendAccessEmail'])->name('users.send-access-email');
    Route::post('users/{user}/disable-two-factor', [UserController::class, 'disableTwoFactor'])->name('users.disable-two-factor');
    Route::patch('users/{user}/client-status', [UserController::class, 'updateClientStatus'])->name('users.client-status.update');
    Route::resource('users', UserController::class);

    // Profile (perfil del usuario autenticado)
    Route::get('/profile', [UserController::class, 'profile'])->name('profile');
    Route::put('/profile', [UserController::class, 'updateProfile'])->name('profile.update');
    Route::post('/profile/two-factor/start', [TwoFactorProfileController::class, 'start'])->name('profile.two-factor.start');
    Route::post('/profile/two-factor/confirm', [TwoFactorProfileController::class, 'confirm'])->name('profile.two-factor.confirm');
    Route::post('/profile/two-factor/disable', [TwoFactorProfileController::class, 'disable'])->name('profile.two-factor.disable');
    Route::post('/profile/two-factor/regenerate-recovery', [TwoFactorProfileController::class, 'regenerateRecovery'])->name('profile.two-factor.regenerate-recovery');
    Route::post('/profile/two-factor/cancel', [TwoFactorProfileController::class, 'cancelSetup'])->name('profile.two-factor.cancel');

    // Settings - Rutas independientes para cada sección
    Route::get('/settings', [SettingsController::class, 'redirectToFirstSection'])->name('settings.index');
    Route::get('/settings/quote-pdf-templates/create', [QuotePdfTemplateController::class, 'create'])->name('settings.quote-pdf-templates.create');
    Route::post('/settings/quote-pdf-templates', [QuotePdfTemplateController::class, 'store'])->name('settings.quote-pdf-templates.store');
    Route::get('/settings/quote-pdf-templates/{quotePdfTemplate}/edit', [QuotePdfTemplateController::class, 'edit'])->name('settings.quote-pdf-templates.edit');
    Route::put('/settings/quote-pdf-templates/{quotePdfTemplate}', [QuotePdfTemplateController::class, 'update'])->name('settings.quote-pdf-templates.update');
    Route::delete('/settings/quote-pdf-templates/{quotePdfTemplate}', [QuotePdfTemplateController::class, 'destroy'])->name('settings.quote-pdf-templates.destroy');
    Route::get('/settings/proposal-pdf-templates/create', [ProposalPdfTemplateController::class, 'create'])->name('settings.proposal-pdf-templates.create');
    Route::post('/settings/proposal-pdf-templates', [ProposalPdfTemplateController::class, 'store'])->name('settings.proposal-pdf-templates.store');
    Route::get('/settings/proposal-pdf-templates/{proposalPdfTemplate}/edit', [ProposalPdfTemplateController::class, 'edit'])->name('settings.proposal-pdf-templates.edit');
    Route::put('/settings/proposal-pdf-templates/{proposalPdfTemplate}', [ProposalPdfTemplateController::class, 'update'])->name('settings.proposal-pdf-templates.update');
    Route::delete('/settings/proposal-pdf-templates/{proposalPdfTemplate}', [ProposalPdfTemplateController::class, 'destroy'])->name('settings.proposal-pdf-templates.destroy');
    Route::get('/settings/{section}', [SettingsController::class, 'index'])->name('settings.section')->where('section', 'agency|drive|mail|templates|history|system|quote-pdf|proposal-pdf|legal-policies|login-lockouts');
    Route::post('/settings', [SettingsController::class, 'update'])->name('settings.update');

    Route::post('/settings/login-lockouts/{loginIpLockout}/unlock', [SettingsController::class, 'unlockLoginLockout'])->name('settings.login-lockouts.unlock');

    // Git Pull (solo super_admin)
    Route::post('/settings/git-pull', [SettingsController::class, 'gitPull'])->name('settings.git-pull');

    // Artisan Commands (solo super_admin)
    Route::post('/settings/artisan', [SettingsController::class, 'artisanCommand'])->name('settings.artisan');

    // Consola mantenimiento: composer / php artisan restringidos (solo super_admin)
    Route::post('/settings/maintenance-cli', [SettingsController::class, 'maintenanceCli'])->name('settings.maintenance-cli');

    // API: Buscar clientes
    Route::get('/api/companies/search', [CompanyController::class, 'search'])->name('api.companies.search');

    // Zoho OAuth
    Route::get('/settings/zoho/authorize', [SettingsController::class, 'zohoAuthorize'])->name('settings.zoho.authorize');
    Route::get('/settings/zoho/callback', [SettingsController::class, 'zohoCallback'])->name('settings.zoho.callback');

    Route::get('/settings/drive-oauth/authorize', [SettingsController::class, 'driveOauthAuthorize'])->name('settings.drive-oauth.authorize');
    Route::get('/settings/drive-oauth/callback', [SettingsController::class, 'driveOauthCallback'])->name('settings.drive-oauth.callback');

    // Email Logs
    Route::get('/settings/email-logs/{log}', [SettingsController::class, 'getEmailLog'])->name('settings.email-logs.show');
    Route::delete('/settings/email-logs/{log}', [SettingsController::class, 'deleteEmailLog'])->name('settings.email-logs.destroy');

    // Email Templates
    Route::get('/settings/templates/{template}', [SettingsController::class, 'getTemplate'])->name('settings.templates.show');

    // Test Google Drive Connection
    Route::post('/settings/test-drive-connection', [SettingsController::class, 'testDriveConnection'])->name('settings.test-drive-connection');

    // Drive Operations Log
    Route::get('/settings/drive-operations-log', [SettingsController::class, 'getDriveOperationsLog'])->name('settings.drive-operations-log');
    Route::delete('/settings/drive-operations-log', [SettingsController::class, 'deleteDriveOperationsLog'])->name('settings.drive-operations-log.delete');

    // Eliminar usuario por correo (Configuración → Sistema)
    Route::post('/settings/delete-user-by-email', [SettingsController::class, 'deleteUserByEmail'])->name('settings.delete-user-by-email');
});
