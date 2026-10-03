<?php

declare(strict_types=1);

use {{namespace}}Http\Controllers\Settings\AppearanceController;
use {{namespace}}Http\Controllers\Settings\PasswordController;
use {{namespace}}Http\Controllers\Settings\ProfileController;
use {{namespace}}Http\Controllers\Settings\SecurityController;
use {{namespace}}Http\Controllers\Settings\TwoFactorAuthenticationController;
use Illuminate\Support\Facades\Route;

Route::prefix('settings')->group(function (): void {
    Route::get('profile', [ProfileController::class, 'edit'])->middleware('auth')->name('settings.profile.edit');
    Route::patch('profile', [ProfileController::class, 'update'])->middleware('auth')->name('settings.profile.update');
    Route::delete('profile', [ProfileController::class, 'destroy'])->middleware('auth')->name('settings.profile.destroy');

    Route::get('security', [SecurityController::class, 'edit'])->middleware(['auth', 'verified'])->name('settings.security.edit');
    Route::put('security', [SecurityController::class, 'update'])->middleware('auth')->name('settings.security.update');

    Route::get('password', [PasswordController::class, 'edit'])->middleware('auth')->name('settings.password.edit');
    Route::put('password', [PasswordController::class, 'update'])->middleware('auth')->name('settings.password.update');

    Route::get('appearance', [AppearanceController::class, 'edit'])->middleware('auth')->name('settings.appearance.edit');

    Route::get('two-factor', [TwoFactorAuthenticationController::class, 'show'])->middleware(['auth', 'verified'])->name('two-factor.show');
});
