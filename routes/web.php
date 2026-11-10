<?php

use App\Http\Controllers\Admin;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\MarketController;
use App\Http\Controllers\PublicController;
use Illuminate\Support\Facades\Route;

// ─── Site public ───
Route::get('/', [PublicController::class, 'home'])->name('home');
Route::get('/reserver', [PublicController::class, 'booking'])->name('booking');
Route::get('/conditions-generales', [PublicController::class, 'terms'])->name('legal.terms');
Route::get('/confidentialite', [PublicController::class, 'privacy'])->name('legal.privacy');
Route::get('/sitemap.xml', [PublicController::class, 'sitemap'])->name('sitemap');
Route::get('/robots.txt', [PublicController::class, 'robots'])->name('robots');

// ─── OCD LIVE MARKET (public) ───
// Les routes littérales doivent être déclarées avant /market/{country} (slug dynamique) pour éviter toute collision.
Route::prefix('market')->name('market.')->group(function () {
    Route::get('/', [MarketController::class, 'index'])->name('index');
    Route::get('/submit', [MarketController::class, 'submitForm'])->name('submit');
    Route::get('/suivi/{token}', [MarketController::class, 'track'])->name('track');
    Route::post('/lives/{submission}/signaler', [MarketController::class, 'report'])->name('report');
    Route::get('/vendeur/{seller:public_slug}', [MarketController::class, 'seller'])->name('seller');
    Route::get('/{country:slug}', [MarketController::class, 'country'])->name('country');
});

// ─── Authentification ───
Route::middleware('guest')->group(function () {
    Route::get('/connexion', [LoginController::class, 'create'])->name('login');
    Route::post('/connexion', [LoginController::class, 'store'])->middleware('throttle:10,1')->name('login.store');
});
Route::post('/deconnexion', [LoginController::class, 'destroy'])->middleware('auth')->name('logout');

// ─── Administration ───
Route::prefix('admin')->name('admin.')->middleware(['auth'])->group(function () {
    Route::middleware('role:admin,assistant')->group(function () {
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
    });

    // Partagé avec les modérateurs Market (les nouvelles demandes y apparaissent aussi).
    Route::middleware('role:admin,assistant,moderator')->group(function () {
        Route::get('/notifications', [Admin\NotificationController::class, 'index'])->name('notifications.index');
        Route::post('/notifications/{id}/lue', [Admin\NotificationController::class, 'read'])->name('notifications.read');
        Route::post('/notifications/tout-lu', [Admin\NotificationController::class, 'readAll'])->name('notifications.read-all');
    });

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

// ─── OCD LIVE MARKET (administration) ───
Route::prefix('admin/market')->name('admin.market.')->middleware(['auth', 'role:admin,moderator'])->group(function () {
    Route::get('/', Admin\Market\DashboardController::class)->name('dashboard');

    Route::get('/submissions', [Admin\Market\SubmissionController::class, 'index'])->name('submissions.index');
    Route::get('/submissions/{submission}', [Admin\Market\SubmissionController::class, 'show'])->name('submissions.show');
    Route::post('/submissions/{submission}/hold', [Admin\Market\SubmissionController::class, 'hold'])->name('submissions.hold');
    Route::post('/submissions/{submission}/approve', [Admin\Market\SubmissionController::class, 'approve'])->name('submissions.approve');
    Route::post('/submissions/{submission}/reject', [Admin\Market\SubmissionController::class, 'reject'])->name('submissions.reject');
    Route::post('/submissions/{submission}/cancel', [Admin\Market\SubmissionController::class, 'cancel'])->name('submissions.cancel');
    Route::post('/submissions/{submission}/comment', [Admin\Market\SubmissionController::class, 'comment'])->name('submissions.comment');

    Route::get('/lives', [Admin\Market\LiveController::class, 'index'])->name('lives.index');
    Route::post('/lives/{submission}/remove', [Admin\Market\LiveController::class, 'remove'])->name('lives.remove');

    Route::get('/reports', [Admin\Market\ReportController::class, 'index'])->name('reports.index');
    Route::post('/reports/{report}/resolve', [Admin\Market\ReportController::class, 'resolve'])->name('reports.resolve');

    // Réservé aux administrateurs : gestion des référentiels et des modérateurs.
    Route::middleware('role:admin')->group(function () {
        Route::get('/countries', [Admin\Market\CountryController::class, 'index'])->name('countries.index');
        Route::post('/countries', [Admin\Market\CountryController::class, 'store'])->name('countries.store');
        Route::post('/countries/{country}/toggle', [Admin\Market\CountryController::class, 'toggle'])->name('countries.toggle');
        Route::post('/countries/{country}/cities', [Admin\Market\CountryController::class, 'storeCity'])->name('countries.cities.store');
        Route::post('/countries/{country}/cities/{city}/toggle', [Admin\Market\CountryController::class, 'toggleCity'])->name('countries.cities.toggle');

        Route::get('/categories', [Admin\Market\CategoryController::class, 'index'])->name('categories.index');
        Route::post('/categories', [Admin\Market\CategoryController::class, 'store'])->name('categories.store');
        Route::post('/categories/{category}/toggle', [Admin\Market\CategoryController::class, 'toggle'])->name('categories.toggle');

        Route::get('/schedules', [Admin\Market\ScheduleController::class, 'index'])->name('schedules.index');
        Route::post('/schedules', [Admin\Market\ScheduleController::class, 'store'])->name('schedules.store');
        Route::post('/schedules/{schedule}/toggle', [Admin\Market\ScheduleController::class, 'toggle'])->name('schedules.toggle');

        Route::get('/moderators', [Admin\Market\ModeratorController::class, 'index'])->name('moderators.index');
        Route::post('/moderators', [Admin\Market\ModeratorController::class, 'store'])->name('moderators.store');
        Route::post('/moderators/{user}/toggle', [Admin\Market\ModeratorController::class, 'toggle'])->name('moderators.toggle');
        Route::post('/moderators/{user}/reset', [Admin\Market\ModeratorController::class, 'reset'])->name('moderators.reset');
    });
});
