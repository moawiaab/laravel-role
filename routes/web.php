<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\App;
use Moawiaab\Role\Http\Controllers\UserController;

Route::get('/pk', function () {
    dd(auth()->user());
});

Route::get('/greeting/{locale}', function (string $locale) {
    if (!in_array($locale, ['en-US', 'ar', 'fr'])) {
        abort(400);
    }

    App::setLocale($locale);
});

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
    ])->group(function () {
    // Route::resource('users', UserController::class);
    // Route::resource('accounts', UserController::class);
    // Route::resource('roles', UserController::class);
    // Route::resource('permissions', UserController::class);
});
