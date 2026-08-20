<?php

namespace Database\Seeders;

use App\Models\Book;
use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Seeder;

class ReviewSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::query()
            ->orderBy('id')
            ->take(5)
            ->get();

        $books = Book::query()
            ->orderBy('id')
            ->take(11)
            ->get();

        if ($users->isEmpty() || $books->isEmpty()) {
            return;
        }

        $commentTemplates = [
            1 => '期待していた内容とは少し違いました。',
            2 => '参考になる部分はありましたが、やや物足りなさも感じました。',
            3 => '基本的な内容が整理されていて、読みやすい一冊でした。',
            4 => '学びが多く、実務や日常でも活かせそうです。',
            5 => '非常に満足度が高く、何度も読み返したい一冊です。',
        ];

        $reviewCounts = [
            3, 2, 4, 3, 3, 2,
            4, 3, 2, 4, 2,
        ];

        foreach ($books as $bookIndex => $book) {
            $count = $reviewCounts[$bookIndex];

            for ($i = 0; $i < $count; $i++) {
                $user = $users[($bookIndex + $i) % $users->count()];
                $rating = (($bookIndex + $i) % 5) + 1;

                Review::updateOrCreate(
                    [
                        'user_id' => $user->id,
                        'book_id' => $book->id,
                    ],
                    [
                        'rating' => $rating,
                        'comment' => $commentTemplates[$rating],
                    ]
                );
            }
        }
    }
}
