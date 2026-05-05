<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\PhotographerController;
use App\Http\Controllers\Admin\HireRequestController;
use App\Http\Controllers\Admin\NotificationController;
use App\Http\Controllers\Admin\LocationController;
use App\Http\Controllers\Admin\CategoryController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Redirect root to admin
Route::get('/', fn() => redirect()->route('admin.login'));

// ── Admin Auth (Guest) ──────────────────────────────
Route::prefix('admin')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('admin.login');
    Route::post('/login', [AuthController::class, 'login'])->name('admin.login.post');
});

// ── Admin Protected ─────────────────────────────────
Route::prefix('admin')->middleware(['auth', 'admin'])->group(function () {

    Route::post('/logout', [AuthController::class, 'logout'])->name('admin.logout');

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('admin.dashboard');

    // Photographers
    Route::get('/photographers', [PhotographerController::class, 'index'])->name('admin.photographers');
    Route::get('/photographers/{id}', [PhotographerController::class, 'show'])->name('admin.photographers.show');
    Route::post('/photographers/{id}/block', [PhotographerController::class, 'block'])->name('admin.photographers.block');
    Route::post('/photographers/{id}/unblock', [PhotographerController::class, 'unblock'])->name('admin.photographers.unblock');
    Route::delete('/photographers/{id}', [PhotographerController::class, 'destroy'])->name('admin.photographers.destroy');

    // Hire Requests
    Route::get('/hire-requests', [HireRequestController::class, 'index'])->name('admin.hire-requests');
    Route::get('/hire-requests/create/{photographer_id}', [HireRequestController::class, 'create'])->name('admin.hire-requests.create');
    Route::post('/hire-requests', [HireRequestController::class, 'store'])->name('admin.hire-requests.store');
    Route::post('/hire-requests/{id}/invalidate', [HireRequestController::class, 'invalidate'])->name('admin.hire-requests.invalidate');

    // Notifications
    Route::get('/notifications', [NotificationController::class, 'index'])->name('admin.notifications');
    Route::get('/notifications/send', [NotificationController::class, 'create'])->name('admin.notifications.create');
    Route::post('/notifications/send', [NotificationController::class, 'store'])->name('admin.notifications.store');

    // Locations
    Route::get('/locations', [LocationController::class, 'index'])->name('admin.locations');
    Route::post('/locations/cities', [LocationController::class, 'storeCity'])->name('admin.locations.store-city');
    Route::post('/locations/{type}/{id}/toggle', [LocationController::class, 'toggle'])->name('admin.locations.toggle');

    // AJAX endpoints for cascading dropdowns
    Route::get('/api/states', [LocationController::class, 'getStates'])->name('admin.api.states');
    Route::get('/api/cities', [LocationController::class, 'getCities'])->name('admin.api.cities');

    // Categories
    Route::get('/categories', [CategoryController::class, 'index'])->name('admin.categories');
    Route::post('/categories', [CategoryController::class, 'store'])->name('admin.categories.store');
    Route::put('/categories/{id}', [CategoryController::class, 'update'])->name('admin.categories.update');
    Route::post('/categories/{id}/toggle', [CategoryController::class, 'toggle'])->name('admin.categories.toggle');
});
