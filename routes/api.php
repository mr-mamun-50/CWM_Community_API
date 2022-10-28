<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\ChatMessageController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\LikeController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/


//__Public routes

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);


//__Protected routes
Route::group(['middleware' => ['auth:sanctum']], function () {
    //__User
    Route::get('/user', [AuthController::class, 'user']);
    Route::put('/user', [AuthController::class, 'update']);
    Route::post('/logout', [AuthController::class, 'logout']);

    //__Posts
    Route::resource('posts', PostController::class);
    //__Comments
    Route::resource('posts.comment', CommentController::class);
    Route::get('/posts/{id}/comments', [CommentController::class, 'home']);
    //__Likes
    Route::post('/posts/{id}/like', [LikeController::class, 'likeOrUnlike']);


    //__Chats
    Route::apiResource('chats', ChatController::class)->only(['index', 'store', 'show']);
    Route::apiResource('chat_message', ChatMessageController::class)->only('index', 'store');

    Route::apiResource('users', UserController::class)->only('index');
});


// php artisan serve --host 192.168.42.3 --port 8000
// php artisan serve --host 192.168.43.196 --port 8000
