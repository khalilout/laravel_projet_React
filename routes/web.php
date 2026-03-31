<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Controller;
use App\Http\Controllers\FriendController;
use App\Http\Controllers\ArticleController;


// Route::get('/', function () {
//     return view('welcome');
// });
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::get('/register', [AuthController::class, 'register'])->name('register');

Route::post('/login', [AuthController::class, 'login'])->name('login.submit');
Route::post('/register', [AuthController::class, 'registerSubmit'])->name('register.submit');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [AuthController::class, 'dashboard'])->name('dashboard');
});

#pour les articles
Route::middleware('auth')->group(function() {
    Route::post('/article', [ArticleController::class, 'store'])->name('article.store');
    Route::post('/article', [ArticleController::class,'store'])->name('article.store');
    Route::put('/article/{article}', [ArticleController::class,'update'])->name('article.update');
    Route::delete('/article/{article}', [ArticleController::class,'destroy'])->name('article.destroy');
});

#pour friend
Route::middleware('auth')->group(function() {
    Route::get('/search-user', [FriendController::class,'search'])->name('search.user');
    Route::post('/friend/send/{friendId}', [FriendController::class,'send'])->name('friend.send');
    Route::post('/friend/accept/{id}', [FriendController::class,'accept'])->name('friend.accept');
    Route::post('/friend/reject/{id}', [FriendController::class,'reject'])->name('friend.reject');
    Route::post('/friend/remove/{friendId}', [FriendController::class,'remove'])->name('friend.remove');
    Route::post('/friend/block/{id}', [FriendController::class, 'block'])->name('friend.block');
    Route::post('/friend/unblock/{id}', [FriendController::class,'unblock'])->name('friend.unblock');
});

#pour les commentaires
Route::middleware('auth')->group(function() {
    Route::post('/article/{article}/comment', [ArticleController::class,'comment'])->name('article.comment');
    Route::put('/comment/{comment}', [ArticleController::class,'editComment'])->name('article.comment.update');
    Route::delete('/comment/{comment}', [ArticleController::class,'deleteComment'])->name('article.comment.delete');
    });

// Include API routes under /api prefix but disable CSRF for this group
Route::prefix('api')->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class)->group(function () {
    require __DIR__ . '/api.php';
});