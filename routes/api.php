<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\LocationController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\CameraKitController;
use App\Http\Controllers\Api\WorkCityController;
use App\Http\Controllers\Api\HireRequestController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\DeviceController;

/*
|--------------------------------------------------------------------------
| API Routes — /api/v1/
|--------------------------------------------------------------------------
*/

Route::prefix('v1')->group(function () {

    // ── Public (No Auth) ────────────────────────────────────
    Route::post('/auth/register', [AuthController::class, 'register']);
    Route::post('/auth/login', [AuthController::class, 'login']);
    Route::post('/auth/forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('/auth/reset-password', [AuthController::class, 'resetPassword']);

    // Locations — cascading dropdowns (public, no auth needed)
    Route::prefix('locations')->group(function () {
        Route::get('/countries', [LocationController::class, 'countries']);
        Route::get('/states', [LocationController::class, 'states']);
        Route::get('/cities', [LocationController::class, 'cities']);
    });

    // Categories list (public)
    Route::get('/categories', [CategoryController::class, 'index']);

    // ── Protected (Auth Required) ───────────────────────────
    Route::middleware('auth:sanctum')->group(function () {

        // Auth
        Route::post('/auth/logout', [AuthController::class, 'logout']);
        Route::put('/auth/change-password', [AuthController::class, 'changePassword']);

        // FCM Token
        Route::post('/fcm-token', [AuthController::class, 'updateFcmToken']);

        // Device Management
        Route::get('/devices', [DeviceController::class, 'index']);
        Route::delete('/devices/{id}', [DeviceController::class, 'destroy']);

        // Profile
        Route::get('/profile', [ProfileController::class, 'show']);
        Route::put('/profile', [ProfileController::class, 'update']);
        Route::post('/profile/photo', [ProfileController::class, 'updatePhoto']);

        // Profile — Categories (sync with charge_per_day)
        Route::post('/profile/categories', [CategoryController::class, 'syncMyCategories']);

        // Profile — Camera Kit
        Route::get('/profile/camera-kit', [CameraKitController::class, 'index']);
        Route::post('/profile/camera-kit', [CameraKitController::class, 'store']);
        Route::delete('/profile/camera-kit/{id}', [CameraKitController::class, 'destroy']);

        // Profile — Work Cities
        Route::get('/profile/work-cities', [WorkCityController::class, 'index']);
        Route::post('/profile/work-cities', [WorkCityController::class, 'store']);
        Route::delete('/profile/work-cities/{id}', [WorkCityController::class, 'destroy']);

        // Hire Requests (Photographer side)
        Route::get('/hire-requests', [HireRequestController::class, 'index']);
        Route::get('/hire-requests/{id}', [HireRequestController::class, 'show']);
        Route::put('/hire-requests/{id}/respond', [HireRequestController::class, 'respond']);

        // Notifications
        Route::get('/notifications', [NotificationController::class, 'index']);
        Route::put('/notifications/{id}/read', [NotificationController::class, 'markRead']);
    });
});
