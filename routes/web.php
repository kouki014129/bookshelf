<?php

use App\Http\Controllers\BookController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\GenreController;
use App\Http\Controllers\GoogleBooksController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\RankingController;
use App\Http\Controllers\ReadingPlanController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\ReviewLikeController;
use Illuminate\Support\Facades\Route;

Route::get('/', [BookController::class, 'index'])
    ->name('books.index');

Route::get(
    '/books/isbn/{isbn}',
    [GoogleBooksController::class, 'search']
)->name('books.isbn.search');

Route::resource('books', BookController::class);

Route::resource('genres', GenreController::class);

Route::get(
    '/ranking',
    [RankingController::class, 'index']
)->name('ranking.index');

Route::middleware('auth')->group(function () {
    Route::get(
        '/favorites',
        [FavoriteController::class, 'index']
    )->name('favorites.index');

    Route::post(
        '/books/{book}/favorites',
        [FavoriteController::class, 'toggle']
    )->name('favorites.toggle');

    Route::post(
        '/books/{book}/reviews',
        [ReviewController::class, 'store']
    )->name('reviews.store');

    Route::get(
        '/reviews/{review}/edit',
        [ReviewController::class, 'edit']
    )->name('reviews.edit');

    Route::put(
        '/reviews/{review}',
        [ReviewController::class, 'update']
    )->name('reviews.update');

    Route::delete(
        '/reviews/{review}',
        [ReviewController::class, 'destroy']
    )->name('reviews.destroy');

    Route::post(
        '/reviews/{review}/like',
        [ReviewLikeController::class, 'toggle']
    )->name('reviews.like');

    Route::get(
        '/reports',
        [ReportController::class, 'index']
    )->name('reports.index');

    Route::resource(
        'reading-plans',
        ReadingPlanController::class
    )
        ->except('show')
        ->parameters([
            'reading-plans' => 'readingPlan',
        ]);

    Route::post(
        '/reading-plans/{readingPlan}/complete',
        [ReadingPlanController::class, 'complete']
    )->name('reading-plans.complete');

    Route::get(
        '/notifications',
        [NotificationController::class, 'index']
    )->name('notifications.index');

    Route::post(
        '/notifications/{notification}/read',
        [NotificationController::class, 'markAsRead']
    )->name('notifications.read');
});
