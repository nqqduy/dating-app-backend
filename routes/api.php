<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\FileController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\UserController;
use App\Middleware\AuthenticateWithToken;
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

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });
Route::prefix('auth')->group(function() {
    Route::post('login', [AuthController::class, 'login']);
    Route::post('register', [AuthController::class, 'register']);
});

ROUTE::prefix('file')->group(function() {
    Route::post('', [FileController::class, 'uploadFile']);
});

// ->middleware(EnsureTokenIsValid::class);
Route::middleware(AuthenticateWithToken::class)->group(function () {
    Route::prefix('posts')->group(function() {
        Route::post('', [PostController::class, 'create']);
        Route::get('', [PostController::class, 'find_many']);
    
        Route::post('/{id}/comments', [PostController::class, 'create_comment']);
    });
    
    Route::prefix('users')->group(function() {
        Route::get('', [UserController::class, 'find_many']);
        Route::get('/friends', [UserController::class, 'find_many_friends_by_user']);
        Route::patch('/{id}/friends/action', [UserController::class, 'accept_or_decline_request']);
        Route::post('/{id}/friends', [UserController::class, 'create_request_friends']);

        Route::post('/{id}/messages', [UserController::class, 'send_message']);
        Route::get('/{id}/messages', [UserController::class, 'get_list_message']);

        Route::get('/{id}', [UserController::class, 'find_one']);
    });
});

