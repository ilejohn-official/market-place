<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\CallController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\SellerProfileController;
use App\Http\Controllers\ServiceController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Public auth routes
Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/login', [AuthController::class, 'login']);

// Public service endpoints
Route::get('/services', [ServiceController::class, 'index']);
Route::get('/services/{id}', [ServiceController::class, 'show']);

// Public seller profile endpoint
Route::get('/sellers/{sellerId}/profile', [SellerProfileController::class, 'showPublic']);

// Protected routes (require authentication)
Route::middleware('auth:sanctum')->group(function () {
    // Auth routes
    Route::get('/auth/profile', [AuthController::class, 'profile']);
    Route::put('/auth/profile', [AuthController::class, 'updateProfile']);
    Route::post('/auth/logout', [AuthController::class, 'logout']);

    // Seller profile routes
    Route::post('/profile/seller', [SellerProfileController::class, 'store']);
    Route::get('/profile/seller', [SellerProfileController::class, 'show']);
    Route::put('/profile/seller', [SellerProfileController::class, 'update']);

    // Service routes (seller only for create/update/delete)
    Route::post('/services', [ServiceController::class, 'store']);
    Route::put('/services/{id}', [ServiceController::class, 'update']);
    Route::delete('/services/{id}', [ServiceController::class, 'destroy']);
    Route::get('/my-services', [ServiceController::class, 'myServices']);

    // Booking routes (buyer for create, buyer/seller for read)
    Route::post('/bookings', [BookingController::class, 'store']);
    Route::get('/bookings', [BookingController::class, 'index']);
    Route::get('/bookings/{id}', [BookingController::class, 'show']);

    // Message routes
    Route::post('/bookings/{id}/messages', [MessageController::class, 'store']);
    Route::get('/bookings/{id}/messages', [MessageController::class, 'index']);
    Route::patch('/messages/{id}/read', [MessageController::class, 'markRead']);

    // Call routes
    Route::post('/bookings/{id}/calls', [CallController::class, 'store']);
    Route::patch('/calls/{id}', [CallController::class, 'update']);
});
