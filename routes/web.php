<?php

use App\Http\Controllers\Admin\ActivityLogController;
use App\Http\Controllers\Admin\AssociateController;
use App\Http\Controllers\Admin\BackupController;
use App\Http\Controllers\Admin\BrandSettingController;
use App\Http\Controllers\Admin\ConceptController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\InvoiceController;
use App\Http\Controllers\Admin\PermissionController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\TwoFactorSettingsController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\UserPreferenceController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\Auth\TwoFactorChallengeController;
use App\Http\Controllers\Auth\TwoFactorProfileController;
use App\Http\Controllers\LegalPageController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (Auth::check()) {
        return redirect()->route('admin.dashboard');
    }

    return redirect()->route('login');
})->name('home');

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

Route::get('/politica-privacidad', [LegalPageController::class, 'privacy'])->name('legal.privacy');
Route::get('/terminos-condiciones', [LegalPageController::class, 'terms'])->name('legal.terms');

Route::get('/establecer-contrasena', [ResetPasswordController::class, 'show'])->name('password.reset');
Route::post('/establecer-contrasena', [ResetPasswordController::class, 'store'])->name('password.reset.store');

Route::middleware(['auth', 'not.client', 'module.permission', 'admin.no-cache', 'log.admin.mutations'])->prefix('admin')->name('admin.')->group(function () {
    Route::post('/preferences/theme', [UserPreferenceController::class, 'updateTheme'])->name('preferences.theme');
    Route::post('/preferences/font-scale', [UserPreferenceController::class, 'updateFontScale'])->name('preferences.font-scale');

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::resource('associates', AssociateController::class)->except(['show']);
    Route::resource('concepts', ConceptController::class)->except(['show']);
    Route::resource('invoices', InvoiceController::class);
    Route::get('invoices/{invoice}/pdf', [InvoiceController::class, 'pdf'])->name('invoices.pdf');
    Route::post('invoices/{invoice}/send', [InvoiceController::class, 'send'])->name('invoices.send');
    Route::post('invoices/{invoice}/mark-paid', [InvoiceController::class, 'markPaid'])->name('invoices.mark-paid');

    Route::get('/brand-settings', [BrandSettingController::class, 'edit'])->name('brand-settings.edit');
    Route::put('/brand-settings', [BrandSettingController::class, 'update'])->name('brand-settings.update');

    Route::get('/two-factor-settings', [TwoFactorSettingsController::class, 'edit'])->name('two-factor-settings.edit');
    Route::put('/two-factor-settings', [TwoFactorSettingsController::class, 'update'])->name('two-factor-settings.update');

    Route::get('/backups', [BackupController::class, 'index'])->name('backups.index');
    Route::post('/backups', [BackupController::class, 'store'])->name('backups.store');
    Route::get('/backups/{backup}/download', [BackupController::class, 'download'])->name('backups.download');
    Route::delete('/backups/{backup}', [BackupController::class, 'destroy'])->name('backups.destroy');
    Route::post('/backups/import', [BackupController::class, 'import'])->name('backups.import');
    Route::post('/backups/{backup}/restore', [BackupController::class, 'restore'])->name('backups.restore');
    Route::post('/backups/wipe', [BackupController::class, 'wipe'])->name('backups.wipe');

    Route::get('/permissions', [PermissionController::class, 'index'])->name('permissions.index');
    Route::post('/permissions/update', [PermissionController::class, 'updatePermissions'])->name('permissions.update');
    Route::post('/permissions/hierarchy', [PermissionController::class, 'updateHierarchy'])->name('permissions.hierarchy');
    Route::post('/roles', [PermissionController::class, 'storeRole'])->name('roles.store');
    Route::delete('/roles/{role}', [PermissionController::class, 'destroyRole'])->name('roles.destroy');

    Route::get('activity-logs', [ActivityLogController::class, 'index'])->name('activity-logs.index');
    Route::delete('activity-logs', [ActivityLogController::class, 'destroyAll'])->name('activity-logs.destroy-all');
    Route::get('activity-logs/user/{user}', [ActivityLogController::class, 'show'])->name('activity-logs.show');

    Route::get('agents', [UserController::class, 'agents'])->name('agents.index');

    Route::post('users/{user}/send-access-email', [UserController::class, 'sendAccessEmail'])->name('users.send-access-email');
    Route::post('users/{user}/disable-two-factor', [UserController::class, 'disableTwoFactor'])->name('users.disable-two-factor');
    Route::resource('users', UserController::class);

    Route::get('/profile', [UserController::class, 'profile'])->name('profile');
    Route::put('/profile', [UserController::class, 'updateProfile'])->name('profile.update');
    Route::post('/profile/two-factor/start', [TwoFactorProfileController::class, 'start'])->name('profile.two-factor.start');
    Route::post('/profile/two-factor/confirm', [TwoFactorProfileController::class, 'confirm'])->name('profile.two-factor.confirm');
    Route::post('/profile/two-factor/disable', [TwoFactorProfileController::class, 'disable'])->name('profile.two-factor.disable');
    Route::post('/profile/two-factor/regenerate-recovery', [TwoFactorProfileController::class, 'regenerateRecovery'])->name('profile.two-factor.regenerate-recovery');
    Route::post('/profile/two-factor/cancel', [TwoFactorProfileController::class, 'cancelSetup'])->name('profile.two-factor.cancel');

    Route::get('/settings', [SettingsController::class, 'redirectToFirstSection'])->name('settings.index');
    Route::get('/settings/{section}', [SettingsController::class, 'index'])->name('settings.section')->where('section', 'mail|templates|history|system|legal-policies|login-lockouts');
    Route::post('/settings', [SettingsController::class, 'update'])->name('settings.update');

    Route::post('/settings/login-lockouts/{loginIpLockout}/unlock', [SettingsController::class, 'unlockLoginLockout'])->name('settings.login-lockouts.unlock');
    Route::post('/settings/git-pull', [SettingsController::class, 'gitPull'])->name('settings.git-pull');
    Route::post('/settings/artisan', [SettingsController::class, 'artisanCommand'])->name('settings.artisan');
    Route::post('/settings/maintenance-cli', [SettingsController::class, 'maintenanceCli'])->name('settings.maintenance-cli');

    Route::get('/settings/zoho/authorize', [SettingsController::class, 'zohoAuthorize'])->name('settings.zoho.authorize');
    Route::get('/settings/zoho/callback', [SettingsController::class, 'zohoCallback'])->name('settings.zoho.callback');

    Route::get('/settings/email-logs/{log}', [SettingsController::class, 'getEmailLog'])->name('settings.email-logs.show');
    Route::delete('/settings/email-logs/{log}', [SettingsController::class, 'deleteEmailLog'])->name('settings.email-logs.destroy');
    Route::get('/settings/templates/{template}', [SettingsController::class, 'getTemplate'])->name('settings.templates.show');
    Route::post('/settings/delete-user-by-email', [SettingsController::class, 'deleteUserByEmail'])->name('settings.delete-user-by-email');
    Route::patch('/settings/error-logs/{errorLog}/resolve', [SettingsController::class, 'resolveErrorLog'])->name('settings.error-logs.resolve');
    Route::delete('/settings/error-logs/{errorLog}', [SettingsController::class, 'deleteErrorLog'])->name('settings.error-logs.destroy');
    Route::delete('/settings/error-logs', [SettingsController::class, 'clearErrorLogs'])->name('settings.error-logs.clear');
});
