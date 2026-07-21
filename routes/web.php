<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BookController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\GenreController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\ReviewLikeController;

Route::get('/', [BookController::class, 'index'])->name('books.index');
Route::resource('books', BookController::class);
Route::resource('genres', GenreController::class);

Route::middleware('auth')->group(function () {
    Route::get('/favorites', function () {
        return view('favorites.index');
    })->name('favorites.index');

    Route::post('/books/{book}/favorites', [FavoriteController::class, 'toggle'])->name('favorites.toggle');

    Route::post('/books/{book}/reviews', [ReviewController::class, 'store'])->name('reviews.store');
    Route::get('/reviews/{review}/edit', [ReviewController::class, 'edit'])->name('reviews.edit');
    Route::put('/reviews/{review}', [ReviewController::class, 'update'])->name('reviews.update');
    Route::delete('/reviews/{review}', [ReviewController::class, 'destroy'])->name('reviews.destroy');

    Route::post('/reviews/{review}/like', [ReviewLikeController::class, 'toggle'])->name('reviews.like');
});

Route::get('/ranking', function () {
    return view('ranking.index');
})->name('ranking.index');