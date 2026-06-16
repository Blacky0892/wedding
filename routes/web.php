<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Wedding\AdminWeddingMediaController;
use App\Http\Controllers\Wedding\PublicWeddingMediaController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', [PublicWeddingMediaController::class, 'index'])->name('wedding.index');

Route::get('/api/wedding/media', [PublicWeddingMediaController::class, 'list'])->name('wedding.media.list');
Route::post('/api/wedding/media', [PublicWeddingMediaController::class, 'store'])
    ->middleware('throttle:wedding-upload')
    ->name('wedding.media.store');
Route::get('/wedding/media/{media}', [PublicWeddingMediaController::class, 'show'])->name('wedding.media.show');
Route::get('/wedding/media/{media}/download', [PublicWeddingMediaController::class, 'download'])->name('wedding.media.download');

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth', 'verified'])
    ->prefix('/admin/wedding/media')
    ->name('admin.wedding.media.')
    ->group(function () {
        Route::get('/', [AdminWeddingMediaController::class, 'index'])->name('index');
        Route::patch('/{media}/hide', [AdminWeddingMediaController::class, 'hide'])->name('hide');
        Route::patch('/{media}/restore', [AdminWeddingMediaController::class, 'restore'])->name('restore');
        Route::delete('/{media}', [AdminWeddingMediaController::class, 'destroy'])->name('destroy');
    });

require __DIR__.'/auth.php';
