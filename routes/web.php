<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OAuthController;

Route::get('/', [OAuthController::class, 'index']);
Route::get('/user', [OAuthController::class, 'user']);
Route::get('/user/photo', [OAuthController::class, 'userPhoto']);
Route::get('/login', [OAuthController::class, 'login'])->name('sso.login');
Route::get('/openid', [OAuthController::class, 'callback'])->name('sso.callback');
Route::get('/logout', [OAuthController::class, 'logout'])->name('sso.logout');
Route::get('/logout/govbr', [OAuthController::class, 'logoutGovBrCallback']);

