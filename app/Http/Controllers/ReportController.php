<?php

namespace App\Http\Controllers;

use App\Enums\ReadingPlanStatus;
use App\Models\Book;
use App\Models\Genre;
use App\Models\ReadingPlan;
use App\Models\Review;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ReportController extends Controller
{
    /**
     * ログインユーザーのレビュー履歴と読書計画を集計し、読書レポート画面を表示する。
     *
     * @return View 読書レポート画面
     */
    public function index(): View
    {
        $userId = Auth::id();

        $reviews = Review::query()
            ->where('user_id', $userId)
            ->with('book.genres')
            ->get();

        $totalReviews = $reviews->count();

        $completedBookIds = $reviews
            ->pluck('book_id')
            ->merge(
                ReadingPlan::query()
                    ->where('user_id', $userId)
                    ->where('status', ReadingPlanStatus::Completed->value)
                    ->pluck('book_id')
            )
            ->unique();

        $completedBooks = $completedBookIds->count();

        $averageRating = $reviews->isNotEmpty()
            ? round($reviews->avg('rating'), 1)
            : 0;

        $ratingDistribution = collect(range(1, 5))
            ->mapWithKeys(function (int $rating) use ($reviews): array {
                return [
                    $rating => $reviews
                        ->where('rating', $rating)
                        ->count(),
                ];
            })
            ->all();

        $topRatedBooks = $reviews
            ->filter(fn (Review $review): bool => $review->rating >= 4)
            ->sortByDesc('rating')
            ->take(5)
            ->values();

        $genreStatistics = Genre::query()
            ->with('books.reviews')
            ->get()
            ->map(function (Genre $genre) use ($userId): object {
                $genreReviews = $genre->books
                    ->flatMap(function (Book $book) use ($userId) {
                        return $book->reviews
                            ->where('user_id', $userId);
                    });

                return (object) [
                    'id' => $genre->id,
                    'name' => $genre->name,
                    'reviews_count' => $genreReviews->count(),
                    'average_rating' => $genreReviews->isNotEmpty()
                        ? round($genreReviews->avg('rating'), 1)
                        : 0,
                ];
            })
            ->filter(
                fn (object $genre): bool => $genre->reviews_count > 0
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
