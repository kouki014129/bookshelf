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
        $users = User::query()->get();
        $books = Book::query()->get();

        if ($users->isEmpty() || $books->isEmpty()) {
            return;
        }

        $mainUser = $users->first();

        ReadingPlan::create([
            'user_id' => $mainUser->id,
            'book_id' => $books->get(0)->id,
            'deadline' => Carbon::today()->addDay(),
            'status' => ReadingPlanStatus::Planning,
        ]);

        ReadingPlan::create([
            'user_id' => $mainUser->id,
            'book_id' => $books->get(1)->id,
            'deadline' => Carbon::today()->addDays(3),
            'status' => ReadingPlanStatus::Planning,
        ]);

        ReadingPlan::create([
            'user_id' => $mainUser->id,
            'book_id' => $books->get(2)->id,
            'deadline' => Carbon::today()->subDay(),
            'status' => ReadingPlanStatus::Expired,
        ]);

        ReadingPlan::create([
            'user_id' => $mainUser->id,
            'book_id' => $books->get(3)->id,
            'deadline' => Carbon::today()->subDays(7),
            'status' => ReadingPlanStatus::Completed,
        ]);

        if ($users->count() >= 2 && $books->count() >= 5) {
            ReadingPlan::create([
                'user_id' => $users->get(1)->id,
                'book_id' => $books->get(4)->id,
                'deadline' => Carbon::today()->addDay(),
                'status' => ReadingPlanStatus::Planning,
            ]);
        }
    }
}
