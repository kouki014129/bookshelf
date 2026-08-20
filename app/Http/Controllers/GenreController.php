<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreGenreRequest;
use App\Http\Requests\UpdateGenreRequest;
use App\Models\Genre;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class GenreController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(): View
    {
        $genres = Genre::withCount('books')->get();

        return view('genres.index', compact('genres'));
    }

    public function create(): View
    {
        return view('genres.create');
    }

    public function store(StoreGenreRequest $request): RedirectResponse
    {
        Genre::create(['name' => $request->name]);

        return redirect()->route('genres.index')
            ->with('success', 'ジャンルを登録しました');
    }

    public function show(Genre $genre): View
    {
        $books = $genre->books()
            ->with('genres')
            ->paginate(10);

        return view('genres.show', compact('genre', 'books'));
    }

    public function edit(Genre $genre): View
    {
        $this->authorize('update', $genre);

        return view('genres.edit', compact('genre'));
    }

    public function update(UpdateGenreRequest $request, Genre $genre): RedirectResponse
    {
        $this->authorize('update', $genre);

        $genre->update(['name' => $request->name]);

        return redirect()->route('genres.index')
            ->with('success', 'ジャンルを更新しました');
    }

    public function destroy(Genre $genre): RedirectResponse
    {
        $this->authorize('delete', $genre);

        if ($genre->books()->count() > 0) {
            return redirect()->route('genres.index')
                ->with('error', '書籍が登録されているジャンルは削除できません');
        }

        $genre->delete();

        return redirect()->route('genres.index')
            ->with('success', 'ジャンルを削除しました');
    }
}
