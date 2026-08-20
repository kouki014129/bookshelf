<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\ReadingPlan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExpireReadingPlansTest extends TestCase
{
    use RefreshDatabase;

    public function test_expired_planning_reading_plan_is_changed_to_expired(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        $readingPlan = ReadingPlan::create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'deadline' => now()->subDay()->format('Y-m-d'),
            'status' => 'planning',
        ]);

        $this->artisan('app:expire-reading-plans')
            ->assertExitCode(0);

        $this->assertDatabaseHas(
            'reading_plans',
            [
                'id' => $readingPlan->id,
                'status' => 'expired',
            ]
        );
    }

    public function test_completed_reading_plan_is_not_changed_to_expired(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        $readingPlan = ReadingPlan::create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'deadline' => now()->subDay()->format('Y-m-d'),
            'status' => 'completed',
        ]);

        $this->artisan('app:expire-reading-plans')
            ->assertExitCode(0);

        $this->assertDatabaseHas(
            'reading_plans',
            [
                'id' => $readingPlan->id,
                'status' => 'completed',
            ]
        );
    }
}
