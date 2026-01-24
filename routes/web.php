<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\CompanyController;
use App\Http\Controllers\Admin\RegistrationController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Auth\LoginController;

// Redirigir raíz al dashboard
Route::get('/', function () {
    return redirect()->route('admin.dashboard');
});

// Autenticación
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// Rutas Admin (requieren autenticación)
Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // Companies
    Route::resource('companies', CompanyController::class);
    
    // Registrations
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
            
            // Email Logs
            Route::get('/settings/email-logs/{log}', [SettingsController::class, 'getEmailLog'])->name('settings.email-logs.show');
            Route::delete('/settings/email-logs/{log}', [SettingsController::class, 'deleteEmailLog'])->name('settings.email-logs.destroy');
            
            // Email Templates
            Route::get('/settings/templates/{template}', [SettingsController::class, 'getTemplate'])->name('settings.templates.show');
            
            // Test Google Drive Connection
            Route::post('/settings/test-drive-connection', [SettingsController::class, 'testDriveConnection'])->name('settings.test-drive-connection');
        });
