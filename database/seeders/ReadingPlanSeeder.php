<?php

namespace Database\Seeders;

use App\Enums\ReadingPlanStatus;
use App\Models\Book;
use App\Models\ReadingPlan;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class ReadingPlanSeeder extends Seeder
{
    public function run(): void
    {
        $mainUser = User::query()
            ->where('email', 'yamada@example.com')
            ->first();

        $otherUser = User::query()
            ->where('email', 'suzuki@example.com')
            ->first();

        $books = Book::query()
            ->orderBy('id')
            ->get();

        if (! $mainUser || $books->count() < 5) {
            return;
        }

        ReadingPlan::updateOrCreate(
            [
                'user_id' => $mainUser->id,
                'book_id' => $books->get(0)->id,
            ],
            [
                'deadline' => Carbon::today()->addDay(),
                'status' => ReadingPlanStatus::Planning,
            ]
        );

        ReadingPlan::updateOrCreate(
            [
                'user_id' => $mainUser->id,
                'book_id' => $books->get(1)->id,
            ],
            [
                'deadline' => Carbon::today()->addDays(3),
                'status' => ReadingPlanStatus::Planning,
            ]
        );

        ReadingPlan::updateOrCreate(
            [
                'user_id' => $mainUser->id,
                'book_id' => $books->get(2)->id,
            ],
            [
                'deadline' => Carbon::today()->subDay(),
                'status' => ReadingPlanStatus::Expired,
            ]
        );

        ReadingPlan::updateOrCreate(
            [
                'user_id' => $mainUser->id,
                'book_id' => $books->get(3)->id,
            ],
            [
                'deadline' => Carbon::today()->subDays(7),
                'status' => ReadingPlanStatus::Completed,
            ]
        );

        if ($otherUser) {
            ReadingPlan::updateOrCreate(
                [
                    'user_id' => $otherUser->id,
                    'book_id' => $books->get(4)->id,
                ],
                [
                    'deadline' => Carbon::today()->addDays(5),
                    'status' => ReadingPlanStatus::Planning,
                ]
            );
        }
    }
}
