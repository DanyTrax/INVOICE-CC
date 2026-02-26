<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\BackupController;
use App\Http\Controllers\Admin\CompanyController;
use App\Http\Controllers\Admin\RegistrationController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\PermissionController;
use App\Http\Controllers\Admin\ActivityLogController;
use App\Http\Controllers\Admin\ProcessController;
use App\Http\Controllers\Admin\ServiceTypeController;
use App\Http\Controllers\Admin\QuoteController;
use App\Http\Controllers\Admin\QuotePdfTemplateController;
use App\Http\Controllers\Admin\RegulatoryEventController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\ClientRegisterController;
use App\Http\Controllers\ClientPortalController;

// Página principal: si está autenticado va al panel; si no, muestra información pública de la app (requisito verificación OAuth)
Route::get('/', function () {
    if (Auth::check()) {
        return Auth::user()->hasRole('client') ? redirect()->route('portal.dashboard') : redirect()->route('admin.dashboard');
    }
    return view('home-public');
})->name('home');

// Autenticación
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// Páginas legales (públicas, para OAuth y usuarios)
Route::get('/politica-privacidad', function () {
    return view('legal.politica-privacidad');
})->name('legal.privacy');
Route::get('/terminos-condiciones', function () {
    return view('legal.terminos-condiciones');
})->name('legal.terms');

// Establecer/restablecer contraseña (link enviado por admin a agentes)
Route::get('/establecer-contrasena', [ResetPasswordController::class, 'show'])->name('password.reset');
Route::post('/establecer-contrasena', [ResetPasswordController::class, 'store'])->name('password.reset.store');

// Registro por invitación (link de único uso, guest)
Route::get('/registrarse', [ClientRegisterController::class, 'show'])->name('client.register');
Route::post('/registrarse', [ClientRegisterController::class, 'store']);

// Portal Cliente (rol client: solo informativo y descargas)
Route::middleware(['auth', 'client'])->prefix('portal')->name('portal.')->group(function () {
    Route::get('/', [ClientPortalController::class, 'dashboard'])->name('dashboard');
    Route::get('/registrations', [ClientPortalController::class, 'index'])->name('registrations.index');
    Route::get('/registrations/{registration}', [ClientPortalController::class, 'show'])->name('registrations.show');
    Route::get('/registrations/{registration}/documents/{document}/view', [ClientPortalController::class, 'viewDocument'])->name('documents.view');
    Route::get('/registrations/{registration}/documents/{document}/download', [ClientPortalController::class, 'downloadDocument'])->name('documents.download');
});

// Rutas Admin (auth + no clientes + permisos granulares por módulo)
Route::middleware(['auth', 'not.client', 'module.permission', 'admin.no-cache'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

            // Backups de sistema (solo super_admin dentro del controlador)
            Route::get('/backups', [BackupController::class, 'index'])->name('backups.index');
            Route::post('/backups', [BackupController::class, 'store'])->name('backups.store');
            Route::get('/backups/{backup}/download', [BackupController::class, 'download'])->name('backups.download');
            Route::delete('/backups/{backup}', [BackupController::class, 'destroy'])->name('backups.destroy');
            Route::post('/backups/wipe', [BackupController::class, 'wipe'])->name('backups.wipe');

            // Permisos (solo super_admin)
            Route::get('/permissions', [PermissionController::class, 'index'])->name('permissions.index');
            Route::post('/permissions/update', [PermissionController::class, 'updatePermissions'])->name('permissions.update');
            Route::post('/permissions/hierarchy', [PermissionController::class, 'updateHierarchy'])->name('permissions.hierarchy');

    // Registros de actividad por usuario
    Route::get('activity-logs', [ActivityLogController::class, 'index'])->name('activity-logs.index');
    Route::get('activity-logs/user/{user}', [ActivityLogController::class, 'show'])->name('activity-logs.show');
    
    // Companies (Empresas)
    Route::post('companies/{company}/send-invite', [CompanyController::class, 'sendInvite'])->name('companies.send-invite');
    Route::resource('companies', CompanyController::class);
    
    // Listas separadas: Clientes (rol client) y Agentes (no client)
    Route::get('clients', [UserController::class, 'clients'])->name('clients.index');
    Route::get('clients/create', [UserController::class, 'createClient'])->name('clients.create');
    Route::post('clients', [UserController::class, 'storeClient'])->name('clients.store');
    Route::get('agents', [UserController::class, 'agents'])->name('agents.index');
    
    // Registrations (módulo jubilado: se usa Expedientes / Procesos)
    // Route::get('/registrations/{registration}/documents/{document}/view', [RegistrationController::class, 'viewDocument'])
    //     ->name('registrations.documents.view');
    // Route::get('/registrations/{registration}/documents/{document}/download', [RegistrationController::class, 'downloadDocument'])
    //     ->name('registrations.documents.download');
    // Route::delete('/registrations/{registration}/documents/{document}', [RegistrationController::class, 'destroyDocument'])
    //     ->name('registrations.documents.destroy');
    // Route::resource('registrations', RegistrationController::class);

    // Cotizaciones (pre-venta)
    Route::get('quotes', [QuoteController::class, 'index'])->name('quotes.index');
    Route::get('quotes/create', [QuoteController::class, 'create'])->name('quotes.create');
    Route::post('quotes', [QuoteController::class, 'store'])->name('quotes.store');
    Route::get('quotes/{quote}', [QuoteController::class, 'show'])->name('quotes.show');
    Route::get('quotes/{quote}/edit', [QuoteController::class, 'edit'])->name('quotes.edit');
    Route::put('quotes/{quote}', [QuoteController::class, 'update'])->name('quotes.update');
    Route::post('quotes/{quote}/approve', [QuoteController::class, 'approve'])->name('quotes.approve');
    Route::get('quotes/{quote}/pdf', [QuoteController::class, 'pdf'])->name('quotes.pdf');
    Route::post('quotes/{quote}/anular', [QuoteController::class, 'anular'])->name('quotes.anular');

    // Tipos de Trámite (ServiceTypes)
    Route::get('service-types', [ServiceTypeController::class, 'index'])->name('service-types.index');
    Route::get('service-types/create', [ServiceTypeController::class, 'create'])->name('service-types.create');
    Route::post('service-types', [ServiceTypeController::class, 'store'])->name('service-types.store');
    Route::get('service-types/{serviceType}/edit', [ServiceTypeController::class, 'edit'])->name('service-types.edit');
    Route::put('service-types/{serviceType}', [ServiceTypeController::class, 'update'])->name('service-types.update');

    // Expedientes (processes) y eventos regulatorios
    Route::get('processes', [ProcessController::class, 'index'])->name('processes.index');
    Route::get('processes/monitor', [ProcessController::class, 'masterList'])->name('processes.monitor');
    Route::get('processes/export', [ProcessController::class, 'export'])->name('processes.export');
    Route::get('processes/create', [ProcessController::class, 'create'])->name('processes.create');
    Route::post('processes', [ProcessController::class, 'store'])->name('processes.store');
    Route::get('processes/{process}', [ProcessController::class, 'show'])->name('processes.show');
    Route::post('processes/{process}/link-to-quote', [ProcessController::class, 'linkToQuote'])->name('processes.link-to-quote');
    Route::post('processes/{process}/submissions', [ProcessController::class, 'storeSubmission'])->name('processes.submissions.store');
    Route::post('submissions/{submission}/register-response', [ProcessController::class, 'registerResponse'])->name('submissions.register-response');
    Route::delete('submissions/{submission}', [ProcessController::class, 'destroySubmission'])->name('submissions.destroy');
    Route::post('processes/{process}/checklist-items', [ProcessController::class, 'storeChecklistItem'])->name('processes.checklist-items.store');
    Route::put('checklist-items/{checklistItem}', [ProcessController::class, 'updateChecklistItem'])->name('checklist-items.update');
    Route::post('processes/{process}/documents', [ProcessController::class, 'uploadDocument'])->name('processes.documents.upload');
    Route::get('processes/{process}/documents/{processDocument}/view', [ProcessController::class, 'viewDocument'])->name('processes.documents.view');
    Route::get('processes/{process}/documents/{processDocument}/download', [ProcessController::class, 'downloadDocument'])->name('processes.documents.download');
    Route::delete('processes/{process}/documents/{processDocument}', [ProcessController::class, 'destroyDocument'])->name('processes.documents.destroy');
    Route::post('submissions/{submission}/events/auto', [RegulatoryEventController::class, 'storeAuto'])->name('submissions.events.store-auto');
    Route::post('submissions/{submission}/events/resolution', [RegulatoryEventController::class, 'storeResolution'])->name('submissions.events.store-resolution');
    
    // Users
    Route::post('users/{user}/send-access-email', [UserController::class, 'sendAccessEmail'])->name('users.send-access-email');
    Route::resource('users', UserController::class);
    
    // Profile (perfil del usuario autenticado)
    Route::get('/profile', [UserController::class, 'profile'])->name('profile');
    Route::put('/profile', [UserController::class, 'updateProfile'])->name('profile.update');
    
            // Settings - Rutas independientes para cada sección
            Route::get('/settings', [SettingsController::class, 'redirectToFirstSection'])->name('settings.index');
            Route::get('/settings/quote-pdf-templates/create', [QuotePdfTemplateController::class, 'create'])->name('settings.quote-pdf-templates.create');
            Route::post('/settings/quote-pdf-templates', [QuotePdfTemplateController::class, 'store'])->name('settings.quote-pdf-templates.store');
            Route::get('/settings/quote-pdf-templates/{quotePdfTemplate}/edit', [QuotePdfTemplateController::class, 'edit'])->name('settings.quote-pdf-templates.edit');
            Route::put('/settings/quote-pdf-templates/{quotePdfTemplate}', [QuotePdfTemplateController::class, 'update'])->name('settings.quote-pdf-templates.update');
            Route::delete('/settings/quote-pdf-templates/{quotePdfTemplate}', [QuotePdfTemplateController::class, 'destroy'])->name('settings.quote-pdf-templates.destroy');
            Route::get('/settings/{section}', [SettingsController::class, 'index'])->name('settings.section')->where('section', 'agency|drive|mail|templates|history|system|quote-pdf');
            Route::post('/settings', [SettingsController::class, 'update'])->name('settings.update');
            
            // Git Pull (solo super_admin)
            Route::post('/settings/git-pull', [SettingsController::class, 'gitPull'])->name('settings.git-pull');
            
            // Artisan Commands (solo super_admin)
            Route::post('/settings/artisan', [SettingsController::class, 'artisanCommand'])->name('settings.artisan');
            
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
        });
