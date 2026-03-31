<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController as ApiAuth;
use App\Http\Controllers\Api\ArticleController as ApiArticle;
use App\Http\Controllers\Api\FriendController as ApiFriend;
use App\Http\Controllers\Api\UserController as ApiUser;

Route::post('/register', [ApiAuth::class, 'register']);
// Return a clear JSON message if someone tries to GET the API login URL
Route::get('/login', function () {
    return response()->json(['message' => 'Method Not Allowed. Use POST to authenticate.'], 405);
});

Route::post('/login', [ApiAuth::class, 'login']);

// Protected routes (uses lightweight token middleware)
Route::middleware(\App\Http\Middleware\ApiTokenAuth::class)->group(function () {
    Route::post('/logout', [ApiAuth::class, 'logout']);

    Route::get('/article', [ApiArticle::class, 'index']);
    Route::post('/article', [ApiArticle::class, 'store']);
    Route::put('/article/{article}', [ApiArticle::class, 'update']);
    Route::delete('/article/{article}', [ApiArticle::class, 'destroy']);

    Route::post('/article/{article}/comment', [ApiArticle::class, 'comment']);
    Route::put('/comment/{comment}', [ApiArticle::class, 'editComment']);
    Route::delete('/comment/{comment}', [ApiArticle::class, 'deleteComment']);

    Route::get('/search-user', [ApiFriend::class, 'search']);
    Route::post('/friend/send/{friendId}', [ApiFriend::class, 'send']);
    Route::post('/friend/accept/{id}', [ApiFriend::class, 'accept']);
    Route::post('/friend/reject/{id}', [ApiFriend::class, 'reject']);
    Route::post('/friend/remove/{friendId}', [ApiFriend::class, 'remove']);
    Route::post('/friend/block/{id}', [ApiFriend::class, 'block']);
    Route::post('/friend/unblock/{id}', [ApiFriend::class, 'unblock']);
    Route::get('/friends/blocked', [ApiFriend::class, 'blocked']);
    // User profile and friends
    Route::get('/user/{id}', [ApiUser::class, 'show']);
    Route::get('/user/{id}/friends', [ApiUser::class, 'friends']);
});
