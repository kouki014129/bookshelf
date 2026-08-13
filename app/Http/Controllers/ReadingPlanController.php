<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreReadingPlanRequest;
use App\Http\Requests\UpdateReadingPlanRequest;
use App\Models\Book;
use App\Models\ReadingPlan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReadingPlanController extends Controller
{
    public function index(Request $request): View
    {
        $status = $request->query('status');

        $allowedStatuses = [
            'planning',
            'completed',
        ];

        $readingPlans = ReadingPlan::query()
            ->where('user_id', auth()->id())
            ->with('book')
            ->when(
                in_array($status, $allowedStatuses, true),
                function ($query) use ($status) {
                    $query->where('status', $status);
                }
            )
            ->orderBy('deadline')
            ->get();

        return view('reading-plans.index', compact(
            'readingPlans',
            'status'
        ));
    }

    public function create(): View
    {
        $books = Book::query()
            ->orderBy('title')
            ->get();

        return view('reading-plans.create', compact('books'));
    }

    public function store(
        StoreReadingPlanRequest $request
    ): RedirectResponse {
        ReadingPlan::create([
            'user_id' => auth()->id(),
            'book_id' => $request->validated('book_id'),
            'deadline' => $request->validated('deadline'),
            'status' => 'planning',
        ]);

        return redirect()
            ->route('reading-plans.index')
            ->with('success', '読書計画を登録しました。');
    }

    public function edit(ReadingPlan $readingPlan): View
    {
        $this->authorize('update', $readingPlan);

        $readingPlan->load('book');

        return view(
            'reading-plans.edit',
            compact('readingPlan')
        );
    }

    public function update(
        UpdateReadingPlanRequest $request,
        ReadingPlan $readingPlan
    ): RedirectResponse {
        $this->authorize('update', $readingPlan);

        $readingPlan->update([
            'deadline' => $request->validated('deadline'),
        ]);

        return redirect()
            ->route('reading-plans.index')
            ->with('success', '読書計画を更新しました。');
    }

    public function destroy(
        ReadingPlan $readingPlan
    ): RedirectResponse {
        $this->authorize('delete', $readingPlan);

        $readingPlan->delete();

        return redirect()
            ->route('reading-plans.index')
            ->with('success', '読書計画を削除しました。');
    }

    public function complete(
        ReadingPlan $readingPlan
    ): RedirectResponse {
        $this->authorize('update', $readingPlan);

        $readingPlan->update([
            'status' => 'completed',
        ]);

        return redirect()
            ->route('reading-plans.index')
            ->with('success', '読了しました。');
    }
}
