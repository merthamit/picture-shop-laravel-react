<?php

use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\FavoriteController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\PictureController;
use App\Http\Controllers\Api\ReviewController;
use App\Http\Controllers\Api\UserController;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return [
            'user' => UserResource::make($request->user()),
            'currentToken' => $request->bearerToken(),
        ];
    });
    Route::post('user/logout', [UserController::class, 'logout']);
    Route::put('user/locale', [UserController::class, 'updateLocale']);
    Route::post('email/verification-notification', [UserController::class, 'resendVerification'])
        ->middleware('throttle:3,1');

    // Selling and buying require a verified e-mail address
    Route::middleware('verified')->group(function () {
        Route::post('store/picture', [PictureController::class, 'uploadFile']);
        Route::post('store/order', [OrderController::class, 'store']);
        Route::post('order/pay', [OrderController::class, 'payByStripe']);
    });

    Route::put('pictures/{picture}', [PictureController::class, 'update']);
    Route::delete('pictures/{picture}', [PictureController::class, 'destroy']);
    Route::get('download/picture/{picture}', [PictureController::class, 'downloadPicture'])->withTrashed();

    Route::post('store/review', [ReviewController::class, 'store']);
    Route::put('update/review', [ReviewController::class, 'updateReview']);
    Route::post('delete/review', [ReviewController::class, 'deleteReview']);

    // Wishlist
    Route::get('favorites', [FavoriteController::class, 'index']);
    Route::post('favorites/{picture}', [FavoriteController::class, 'toggle']);

    // Cart sessions (Redis)
    Route::get('cart', [CartController::class, 'index']);
    Route::post('cart', [CartController::class, 'store']);
    Route::delete('cart', [CartController::class, 'clear']);
    Route::delete('cart/{pictureId}', [CartController::class, 'destroy']);
});

Route::post('/user/register', [UserController::class, 'store']);
Route::post('/user/login', [UserController::class, 'auth'])->middleware('throttle:10,1');
Route::post('/user/forgot-password', [UserController::class, 'forgotPassword'])->middleware('throttle:5,1');
Route::post('/user/reset-password', [UserController::class, 'resetPassword']);
Route::get('/email/verify/{id}/{hash}', [UserController::class, 'verifyEmail'])->name('verification.verify');

Route::get('categories/{hasPictures}', [CategoryController::class, 'index']);
Route::get('pictures', [PictureController::class, 'index']);
Route::get('pictures/{picture}', [PictureController::class, 'fetchById']);
Route::get('extensions', [PictureController::class, 'fetchExtentions']);
