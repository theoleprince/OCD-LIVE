<?php

use App\Http\Controllers\Admin;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\PublicController;
use Illuminate\Support\Facades\Route;

// ─── Site public ───
Route::get('/', [PublicController::class, 'home'])->name('home');
Route::get('/reserver', [PublicController::class, 'booking'])->name('booking');
Route::get('/conditions-generales', [PublicController::class, 'terms'])->name('legal.terms');
Route::get('/confidentialite', [PublicController::class, 'privacy'])->name('legal.privacy');
Route::get('/sitemap.xml', [PublicController::class, 'sitemap'])->name('sitemap');
Route::get('/robots.txt', [PublicController::class, 'robots'])->name('robots');

// ─── Authentification ───
Route::middleware('guest')->group(function () {
    Route::get('/connexion', [LoginController::class, 'create'])->name('login');
    Route::post('/connexion', [LoginController::class, 'store'])->middleware('throttle:10,1')->name('login.store');
});
Route::post('/deconnexion', [LoginController::class, 'destroy'])->middleware('auth')->name('logout');

// ─── Administration ───
Route::prefix('admin')->name('admin.')->middleware(['auth', 'role:admin,assistant'])->group(function () {
    Route::get('/', Admin\DashboardController::class)->name('dashboard');

    Route::get('/reservations', [Admin\BookingController::class, 'index'])->name('bookings.index');
    Route::get('/reservations/{booking}', [Admin\BookingController::class, 'show'])->name('bookings.show');
    Route::patch('/reservations/{booking}', [Admin\BookingController::class, 'update'])->name('bookings.update');
    Route::post('/reservations/{booking}/paiement', [Admin\BookingController::class, 'recordPayment'])->name('bookings.payment');
    Route::post('/reservations/{booking}/paiement-echoue', [Admin\BookingController::class, 'failPayment'])->name('bookings.payment-failed');
    Route::post('/reservations/{booking}/confirmer', [Admin\BookingController::class, 'confirm'])->name('bookings.confirm');
    Route::post('/reservations/{booking}/annuler', [Admin\BookingController::class, 'cancel'])->name('bookings.cancel');
    Route::post('/reservations/{booking}/terminer', [Admin\BookingController::class, 'complete'])->name('bookings.complete');
    Route::get('/reservations/{booking}/fichiers/{attachment}', [Admin\BookingController::class, 'attachment'])->name('bookings.attachment');

    Route::get('/clients', [Admin\CustomerController::class, 'index'])->name('customers.index');
    Route::get('/clients/{customer}', [Admin\CustomerController::class, 'show'])->name('customers.show');

    Route::get('/calendrier', [Admin\CalendarController::class, 'index'])->name('calendar');
    Route::post('/calendrier/jour', [Admin\CalendarController::class, 'updateDay'])->name('calendar.day');
    Route::post('/calendrier/blocages', [Admin\CalendarController::class, 'storeBlock'])->name('calendar.blocks.store');
    Route::delete('/calendrier/blocages/{block}', [Admin\CalendarController::class, 'destroyBlock'])->name('calendar.blocks.destroy');

    Route::get('/notifications', [Admin\NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/{id}/lue', [Admin\NotificationController::class, 'read'])->name('notifications.read');
    Route::post('/notifications/tout-lu', [Admin\NotificationController::class, 'readAll'])->name('notifications.read-all');

    // Réservé aux administrateurs
    Route::middleware('role:admin')->group(function () {
        Route::post('/reservations/{booking}/paiements/{payment}/rembourser', [Admin\BookingController::class, 'refundPayment'])->name('bookings.refund');
        Route::get('/export/clients.csv', [Admin\CustomerController::class, 'export'])->name('customers.export');

        Route::get('/packages', [Admin\PackageController::class, 'index'])->name('packages.index');
        Route::get('/packages/nouveau', [Admin\PackageController::class, 'create'])->name('packages.create');
        Route::post('/packages', [Admin\PackageController::class, 'store'])->name('packages.store');
        Route::get('/packages/{package}/modifier', [Admin\PackageController::class, 'edit'])->name('packages.edit');
        Route::put('/packages/{package}', [Admin\PackageController::class, 'update'])->name('packages.update');
        Route::post('/packages/{package}/basculer', [Admin\PackageController::class, 'toggle'])->name('packages.toggle');
        Route::delete('/packages/{package}', [Admin\PackageController::class, 'destroy'])->name('packages.destroy');
        Route::post('/packages/{id}/restaurer', [Admin\PackageController::class, 'restore'])->name('packages.restore');

        Route::get('/parametres', [Admin\SettingController::class, 'edit'])->name('settings.edit');
        Route::put('/parametres', [Admin\SettingController::class, 'update'])->name('settings.update');

        Route::get('/journal', Admin\ActivityLogController::class)->name('logs');
    });
});
