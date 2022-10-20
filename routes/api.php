<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\LikeController;
use App\Http\Controllers\PostController;
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
});


// php artisan serve --host 192.168.42.3 --port 8000
