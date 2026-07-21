<?php

namespace App\Providers;

// use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use App\Models\Book;
use App\Models\Genre;
use App\Models\Review;
use App\Policies\BookPolicy;
use App\Policies\GenrePolicy;
use App\Policies\ReviewPolicy;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        Book::class   => BookPolicy::class,
        Genre::class  => GenrePolicy::class,
        Review::class => ReviewPolicy::class,
    ];

    public function boot(): void
    {
        //
    }
}