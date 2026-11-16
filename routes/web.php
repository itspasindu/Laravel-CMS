<?php

use App\Http\Controllers\Admin\NewsController;
use App\Http\Controllers\Admin\NoticeController;
use App\Http\Controllers\Auth\OidcAuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HealthController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\NewsPageController;
use App\Http\Controllers\NoticePageController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');
Route::get('/health', HealthController::class)->name('health');
Route::get('/news', [NewsPageController::class, 'index'])->name('news.index');
Route::get('/news/{news:slug}', [NewsPageController::class, 'show'])->name('news.show');
Route::get('/notices', [NoticePageController::class, 'index'])->name('notices.index');
Route::get('/notices/{notice:slug}', [NoticePageController::class, 'show'])->name('notices.show');

Route::get('/auth/oidc/redirect', [OidcAuthController::class, 'redirect'])
    ->name('oidc.redirect');
Route::get('/auth/oidc/callback', [OidcAuthController::class, 'callback'])
    ->name('oidc.callback');

Route::get('/dashboard', DashboardController::class)
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::prefix('admin')->name('admin.')->group(function () {
        Route::resource('news', NewsController::class)->except(['show']);
        Route::resource('notices', NoticeController::class)->except(['show']);
    });
});

require __DIR__.'/auth.php';
