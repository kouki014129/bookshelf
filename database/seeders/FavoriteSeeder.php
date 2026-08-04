<?php

namespace Database\Seeders;

use App\Models\Book;
use App\Models\User;
use Illuminate\Database\Seeder;

class FavoriteSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::orderBy('id')->take(5)->get();
        $books = Book::orderBy('id')->take(11)->get();

        $favoriteCounts = [5, 4, 5, 3, 4];

        foreach ($users as $userIndex => $user) {
            $bookIds = [];

            for ($i = 0; $i < $favoriteCounts[$userIndex]; $i++) {
                $bookIndex = ($userIndex * 2 + $i) % $books->count();
                $bookIds[] = $books[$bookIndex]->id;
            }

            $user->favoriteBooks()->syncWithoutDetaching($bookIds);
        }
    }
}