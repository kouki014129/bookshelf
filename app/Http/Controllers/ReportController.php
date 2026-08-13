<?php

namespace App\Http\Controllers;

use App\Models\Genre;
use App\Models\Review;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function index(): View
    {
        $reviews = Review::query()
            ->where('user_id', Auth::id())
            ->with('book.genres')
            ->get();

        // 基本統計
        $totalReviews = $reviews->count();

        $completedBooks = $reviews
            ->pluck('book_id')
            ->unique()
            ->count();

        $averageRating = $reviews->isNotEmpty()
            ? round($reviews->avg('rating'), 1)
            : 0;

        // 評価分布
        $ratingDistribution = [];

        for ($i = 1; $i <= 5; $i++) {
            $ratingDistribution[$i] = $reviews
                ->where('rating', $i)
                ->count();
        }

        // 高評価書籍
        // 要件UIでは上位3冊を表示
        $topRatedBooks = $reviews
            ->sortByDesc('rating')
            ->take(3)
            ->values();

        // ジャンル別評価傾向 TOP5
        $genreStatistics = Genre::with('books.reviews')
            ->get()
            ->map(function ($genre) {
                $genreReviews = collect();

                foreach ($genre->books as $book) {
                    $genreReviews = $genreReviews->merge(
                        $book->reviews->where('user_id', Auth::id())
                    );
                }

                return (object) [
                    'name' => $genre->name,
                    'reviews_count' => $genreReviews->count(),
                    'average_rating' => $genreReviews->isNotEmpty()
                        ? round($genreReviews->avg('rating'), 1)
                        : 0,
                ];
            })
            ->filter(
                fn ($genre) => $genre->reviews_count > 0
            )
            ->sortByDesc('average_rating')
            ->take(5)
            ->values();

        return view('reports.index', compact(
            'totalReviews',
            'completedBooks',
            'averageRating',
            'ratingDistribution',
            'topRatedBooks',
            'genreStatistics',
        ));
    }
}
