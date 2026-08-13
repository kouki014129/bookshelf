<?php

namespace Database\Seeders;

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
            'planned_start' => Carbon::today()->addDay(),
            'planned_end' => Carbon::today()->addDay(),
            'status' => 'planning',
        ]);

        ReadingPlan::create([
            'user_id' => $mainUser->id,
            'book_id' => $books->get(1)->id,
            'planned_start' => Carbon::today()->addDays(3),
            'planned_end' => Carbon::today()->addDays(3),
            'status' => 'planning',
        ]);

        ReadingPlan::create([
            'user_id' => $mainUser->id,
            'book_id' => $books->get(2)->id,
            'planned_start' => Carbon::today()->subDay(),
            'planned_end' => Carbon::today()->subDay(),
            'status' => 'reading',
        ]);

        ReadingPlan::create([
            'user_id' => $mainUser->id,
            'book_id' => $books->get(3)->id,
            'planned_start' => Carbon::today()->subDays(7),
            'planned_end' => Carbon::today()->subDays(7),
            'status' => 'completed',
        ]);

        if ($users->count() >= 2 && $books->count() >= 5) {
            ReadingPlan::create([
                'user_id' => $users->get(1)->id,
                'book_id' => $books->get(4)->id,
                'planned_start' => Carbon::today()->addDay(),
                'planned_end' => Carbon::today()->addDay(),
                'status' => 'planning',
            ]);
        }
    }
}
