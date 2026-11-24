<?php

use App\Http\Controllers\Admin;
use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');

Route::prefix('admin')->name('admin.')->group(function () {
    Route::middleware('guest')->group(function () {
        Route::get('login', [Admin\AuthController::class, 'show'])->name('login');
        Route::post('login', [Admin\AuthController::class, 'login'])->name('login.attempt');
    });

    Route::middleware('auth')->group(function () {
        Route::post('logout', [Admin\AuthController::class, 'logout'])->name('logout');
        Route::get('/', Admin\DashboardController::class)->name('dashboard');

        Route::resource('members', Admin\MemberController::class)->except('show');
        Route::post('members/{member}/purchases', [Admin\MemberController::class, 'storePurchase'])->name('members.purchases.store');
        Route::delete('members/{member}/purchases/{purchase}', [Admin\MemberController::class, 'destroyPurchase'])->name('members.purchases.destroy');

        Route::get('purchases', [Admin\PurchaseController::class, 'index'])->name('purchases.index');
        Route::post('purchases', [Admin\PurchaseController::class, 'store'])->name('purchases.store');
        Route::delete('purchases/{purchase}', [Admin\PurchaseController::class, 'destroy'])->name('purchases.destroy');

        Route::get('bonus', Admin\BonusController::class)->name('bonus');

        Route::resource('products', Admin\ProductController::class)->except('show');

        Route::get('import', [Admin\ImportController::class, 'show'])->name('import');
        Route::post('import', [Admin\ImportController::class, 'store'])->name('import.store');

        Route::get('settings', [Admin\SettingController::class, 'edit'])->name('settings');
        Route::put('settings', [Admin\SettingController::class, 'update'])->name('settings.update');

        Route::get('users', [Admin\UserController::class, 'index'])->name('users');
        Route::post('users', [Admin\UserController::class, 'store'])->name('users.store');
        Route::put('users/password', [Admin\UserController::class, 'password'])->name('users.password');
        Route::delete('users/{user}', [Admin\UserController::class, 'destroy'])->name('users.destroy');
    });
});
