<?php

namespace App\Http\Controllers;

use App\Enums\ReadingPlanStatus;
use App\Http\Requests\StoreReadingPlanRequest;
use App\Http\Requests\UpdateReadingPlanRequest;
use App\Models\Book;
use App\Models\ReadingPlan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReadingPlanController extends Controller
{
    /**
     * ログインユーザーの読書計画一覧を表示する。
     *
     * @param  Request  $request  ステータス絞り込み条件
     * @return View 読書計画一覧画面
     */
    public function index(Request $request): View
    {
        $status = $request->query('status');

        $allowedStatuses = [
            ReadingPlanStatus::Planning->value,
            ReadingPlanStatus::Completed->value,
            ReadingPlanStatus::Expired->value,
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

    /**
     * 読書計画の新規登録画面を表示する。
     *
     * @return View 読書計画登録画面
     */
    public function create(): View
    {
        $books = Book::query()
            ->orderBy('title')
            ->get();

        return view('reading-plans.create', compact('books'));
    }

    /**
     * ログインユーザーの読書計画を登録する。
     *
     * @param  StoreReadingPlanRequest  $request  読書計画登録フォームの入力値
     * @return RedirectResponse 登録後のリダイレクトレスポンス
     */
    public function store(
        StoreReadingPlanRequest $request
    ): RedirectResponse {
        ReadingPlan::create([
            'user_id' => auth()->id(),
            'book_id' => $request->validated('book_id'),
            'deadline' => $request->validated('deadline'),
            'status' => ReadingPlanStatus::Planning,
        ]);

        return redirect()
            ->route('reading-plans.index')
            ->with('success', '読書計画を登録しました。');
    }

    /**
     * 読書計画の編集画面を表示する。
     *
     * @param  ReadingPlan  $readingPlan  編集対象の読書計画
     * @return View|RedirectResponse 読書計画編集画面または一覧へのリダイレクト
     */
    public function edit(ReadingPlan $readingPlan): View|RedirectResponse
    {
        $this->authorize('update', $readingPlan);

        if ($readingPlan->status !== ReadingPlanStatus::Planning) {
            return redirect()
                ->route('reading-plans.index')
                ->with('error', '読了済みまたは期限切れの読書計画は編集できません。');
        }

        $readingPlan->load('book');

        return view(
            'reading-plans.edit',
            compact('readingPlan')
        );
    }

    /**
     * 読書計画の期限を更新する。
     *
     * @param  UpdateReadingPlanRequest  $request  読書計画更新フォームの入力値
     * @param  ReadingPlan  $readingPlan  更新対象の読書計画
     * @return RedirectResponse 更新後のリダイレクトレスポンス
     */
    public function update(
        UpdateReadingPlanRequest $request,
        ReadingPlan $readingPlan
    ): RedirectResponse {
        $this->authorize('update', $readingPlan);

        if ($readingPlan->status !== ReadingPlanStatus::Planning) {
            return redirect()
                ->route('reading-plans.index')
                ->with('error', '読了済みまたは期限切れの読書計画は更新できません。');
        }

        $readingPlan->update([
            'deadline' => $request->validated('deadline'),
        ]);

        return redirect()
            ->route('reading-plans.index')
            ->with('success', '読書計画を更新しました。');
    }

    /**
     * 読書計画を削除する。
     *
     * @param  ReadingPlan  $readingPlan  削除対象の読書計画
     * @return RedirectResponse 削除後のリダイレクトレスポンス
     */
    public function destroy(
        ReadingPlan $readingPlan
    ): RedirectResponse {
        $this->authorize('delete', $readingPlan);

        $readingPlan->delete();

        return redirect()
            ->route('reading-plans.index')
            ->with('success', '読書計画を削除しました。');
    }

    /**
     * 読書計画を読了状態に更新する。
     *
     * @param  ReadingPlan  $readingPlan  読了対象の読書計画
     * @return RedirectResponse 読了後のリダイレクトレスポンス
     */
    public function complete(
        ReadingPlan $readingPlan
    ): RedirectResponse {
        $this->authorize('update', $readingPlan);

        if ($readingPlan->status !== ReadingPlanStatus::Planning) {
            return redirect()
                ->route('reading-plans.index')
                ->with('error', '計画中の読書計画のみ読了できます。');
        }

        $readingPlan->update([
            'status' => ReadingPlanStatus::Completed,
        ]);

        return redirect()
            ->route('reading-plans.index')
            ->with('success', '読了しました。');
    }
}
