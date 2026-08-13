<?php

namespace App\Policies;

use App\Models\Genre;
use App\Models\User;

class GenrePolicy
{
    public function update(User $user, Genre $genre): bool
    {
        return true;
    }

    public function delete(User $user, Genre $genre): bool
    {
        return true;
    }
}
