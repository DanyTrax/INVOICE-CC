<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\BackupController;
use App\Http\Controllers\Admin\CompanyController;
use App\Http\Controllers\Admin\RegistrationController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\ClientRegisterController;
use App\Http\Controllers\ClientPortalController;

// Redirigir raíz según rol
Route::get('/', function () {
    if (Auth::check()) {
        return Auth::user()->hasRole('client') ? redirect()->route('portal.dashboard') : redirect()->route('admin.dashboard');
    }
    return redirect()->route('login');
});

// Autenticación
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

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

// Rutas Admin (auth + no clientes)
Route::middleware(['auth', 'not.client'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

            // Backups de sistema (solo super_admin dentro del controlador)
            Route::get('/backups', [BackupController::class, 'index'])->name('backups.index');
            Route::post('/backups', [BackupController::class, 'store'])->name('backups.store');
            Route::get('/backups/{backup}/download', [BackupController::class, 'download'])->name('backups.download');
            Route::delete('/backups/{backup}', [BackupController::class, 'destroy'])->name('backups.destroy');
            Route::post('/backups/wipe', [BackupController::class, 'wipe'])->name('backups.wipe');
    
    // Companies (Empresas)
    Route::post('companies/{company}/send-invite', [CompanyController::class, 'sendInvite'])->name('companies.send-invite');
    Route::resource('companies', CompanyController::class);
    
    // Listas separadas: Clientes (rol client) y Agentes (no client)
    Route::get('clients', [UserController::class, 'clients'])->name('clients.index');
    Route::get('agents', [UserController::class, 'agents'])->name('agents.index');
    
    // Registrations
    Route::get('/registrations/{registration}/documents/{document}/view', [RegistrationController::class, 'viewDocument'])
        ->name('registrations.documents.view');
    Route::get('/registrations/{registration}/documents/{document}/download', [RegistrationController::class, 'downloadDocument'])
        ->name('registrations.documents.download');
    Route::delete('/registrations/{registration}/documents/{document}', [RegistrationController::class, 'destroyDocument'])
        ->name('registrations.documents.destroy');
    Route::resource('registrations', RegistrationController::class);
    
    // Users
    Route::resource('users', UserController::class);
    
    // Profile (perfil del usuario autenticado)
    Route::get('/profile', [UserController::class, 'profile'])->name('profile');
    Route::put('/profile', [UserController::class, 'updateProfile'])->name('profile.update');
    
            // Settings - Rutas independientes para cada sección
            Route::get('/settings', function() {
                return redirect()->route('admin.settings.section', 'agency');
            })->name('settings.index');
            Route::get('/settings/{section}', [SettingsController::class, 'index'])->name('settings.section')->where('section', 'agency|drive|mail|templates|history|system');
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
