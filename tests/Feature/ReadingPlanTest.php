<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\ReadingPlan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReadingPlanTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_view_reading_plans(): void
    {
        $response = $this->get(
            route('reading-plans.index')
        );

        $response->assertRedirect(
            route('login')
        );
    }

    public function test_user_can_view_own_reading_plans(): void
    {
        $this->withoutExceptionHandling();

        $user = User::factory()->create();
        $book = Book::factory()->create();

        ReadingPlan::create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'deadline' => now()->addDay()->format('Y-m-d'),
            'status' => 'planning',
        ]);

        $response = $this
            ->actingAs($user)
            ->get(route('reading-plans.index'));

        $response->assertOk();
        $response->assertSee($book->title);
    }

    public function test_user_does_not_see_other_users_plans(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $book = Book::factory()->create();

        ReadingPlan::create([
            'user_id' => $otherUser->id,
            'book_id' => $book->id,
            'deadline' => now()->addDay()->format('Y-m-d'),
            'status' => 'planning',
        ]);

        $response = $this
            ->actingAs($user)
            ->get(route('reading-plans.index'));

        $response->assertOk();
        $response->assertDontSee($book->title);
    }

    public function test_user_can_create_reading_plan(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        $deadline = now()
            ->addDays(3)
            ->format('Y-m-d');

        $response = $this
            ->actingAs($user)
            ->post(
                route('reading-plans.store'),
                [
                    'book_id' => $book->id,
                    'deadline' => $deadline,
                ]
            );

        $response->assertRedirect(
            route('reading-plans.index')
        );

        $this->assertDatabaseHas(
            'reading_plans',
            [
                'user_id' => $user->id,
                'book_id' => $book->id,
                'deadline' => $deadline,
                'status' => 'planning',
            ]
        );
    }

    public function test_user_can_update_own_reading_plan(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        $readingPlan = ReadingPlan::create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'deadline' => now()->addDay()->format('Y-m-d'),
            'status' => 'planning',
        ]);

        $newDeadline = now()
            ->addDays(5)
            ->format('Y-m-d');

        $response = $this
            ->actingAs($user)
            ->put(
                route(
                    'reading-plans.update',
                    $readingPlan
                ),
                [
                    'deadline' => $newDeadline,
                ]
            );

        $response->assertRedirect(
            route('reading-plans.index')
        );

        $this->assertDatabaseHas(
            'reading_plans',
            [
                'id' => $readingPlan->id,
                'deadline' => $newDeadline,
            ]
        );
    }

    public function test_user_cannot_update_other_users_plan(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $book = Book::factory()->create();

        $readingPlan = ReadingPlan::create([
            'user_id' => $otherUser->id,
            'book_id' => $book->id,
            'deadline' => now()->addDay()->format('Y-m-d'),
            'status' => 'planning',
        ]);

        $response = $this
            ->actingAs($user)
            ->put(
                route(
                    'reading-plans.update',
                    $readingPlan
                ),
                [
                    'deadline' => now()
                        ->addDays(5)
                        ->format('Y-m-d'),
                ]
            );

        $response->assertForbidden();
    }

    public function test_user_can_complete_own_reading_plan(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        $readingPlan = ReadingPlan::create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'deadline' => now()->addDay()->format('Y-m-d'),
            'status' => 'planning',
        ]);

        $response = $this
            ->actingAs($user)
            ->post(
                route(
                    'reading-plans.complete',
                    $readingPlan
                )
            );

        $response->assertRedirect(
            route('reading-plans.index')
        );

        $this->assertDatabaseHas(
            'reading_plans',
            [
                'id' => $readingPlan->id,
                'status' => 'completed',
            ]
        );
    }

    public function test_completed_plan_cannot_be_updated(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        $readingPlan = ReadingPlan::create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'deadline' => now()->addDay()->format('Y-m-d'),
            'status' => 'completed',
        ]);

        $response = $this
            ->actingAs($user)
            ->put(
                route(
                    'reading-plans.update',
                    $readingPlan
                ),
                [
                    'deadline' => now()
                        ->addDays(5)
                        ->format('Y-m-d'),
                ]
            );

        $response->assertRedirect(
            route('reading-plans.index')
        );

        $this->assertDatabaseHas(
            'reading_plans',
            [
                'id' => $readingPlan->id,
                'status' => 'completed',
                'deadline' => $readingPlan->deadline->format('Y-m-d'),
            ]
        );
    }

    public function test_expired_plan_cannot_be_completed(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        $readingPlan = ReadingPlan::create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'deadline' => now()->subDay()->format('Y-m-d'),
            'status' => 'expired',
        ]);

        $response = $this
            ->actingAs($user)
            ->post(
                route(
                    'reading-plans.complete',
                    $readingPlan
                )
            );

        $response->assertRedirect(
            route('reading-plans.index')
        );

        $this->assertDatabaseHas(
            'reading_plans',
            [
                'id' => $readingPlan->id,
                'status' => 'expired',
            ]
        );
    }

    public function test_user_can_delete_own_reading_plan(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        $readingPlan = ReadingPlan::create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'deadline' => now()->addDay()->format('Y-m-d'),
            'status' => 'planning',
        ]);

        $response = $this
            ->actingAs($user)
            ->delete(
                route(
                    'reading-plans.destroy',
                    $readingPlan
                )
            );

        $response->assertRedirect(
            route('reading-plans.index')
        );

        $this->assertDatabaseMissing(
            'reading_plans',
            [
                'id' => $readingPlan->id,
            ]
        );
    }

    public function test_status_filter_only_displays_selected_status(): void
    {
        $user = User::factory()->create();

        $planningBook = Book::factory()->create();
        $completedBook = Book::factory()->create();
        $expiredBook = Book::factory()->create();

        ReadingPlan::create([
            'user_id' => $user->id,
            'book_id' => $planningBook->id,
            'deadline' => now()->addDay()->format('Y-m-d'),
            'status' => 'planning',
        ]);

        ReadingPlan::create([
            'user_id' => $user->id,
            'book_id' => $completedBook->id,
            'deadline' => now()->subDay()->format('Y-m-d'),
            'status' => 'completed',
        ]);

        ReadingPlan::create([
            'user_id' => $user->id,
            'book_id' => $expiredBook->id,
            'deadline' => now()->subDay()->format('Y-m-d'),
            'status' => 'expired',
        ]);

        $response = $this
            ->actingAs($user)
            ->get(
                route(
                    'reading-plans.index',
                    ['status' => 'expired']
                )
            );

        $response->assertOk();

        $response->assertSee(
            $expiredBook->title
        );

        $response->assertDontSee(
            $planningBook->title
        );

        $response->assertDontSee(
            $completedBook->title
        );
    }

    public function test_reading_status_is_not_shown_in_filter(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->get(route('reading-plans.index'));

        $response->assertOk();
        $response->assertSee('計画中');
        $response->assertSee('読了');
        $response->assertSee('期限切れ');
        $response->assertDontSee('進行中');
    }
}
