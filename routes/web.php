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
    
            // Settings
            Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
            Route::post('/settings', [SettingsController::class, 'update'])->name('settings.update');
            
            // API: Buscar clientes
            Route::get('/api/companies/search', [CompanyController::class, 'search'])->name('api.companies.search');
            
            // Zoho OAuth
            Route::get('/settings/zoho/authorize', [SettingsController::class, 'zohoAuthorize'])->name('settings.zoho.authorize');
            Route::get('/settings/zoho/callback', [SettingsController::class, 'zohoCallback'])->name('settings.zoho.callback');
            
            // Email Logs
            Route::get('/settings/email-logs/{log}', [SettingsController::class, 'getEmailLog'])->name('settings.email-logs.show');
        });
