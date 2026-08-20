<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class NotificationController extends Controller
{
    /**
     * ログインユーザーの通知一覧画面を表示する。
     *
     * @return View 通知一覧画面
     */
    public function index(): View
    {
        /** @var User $user */
        $user = Auth::user();

        $notifications = $user
            ->notifications()
            ->latest()
            ->get();

        return view('notifications.index', compact('notifications'));
    }

    /**
     * ログインユーザーの通知を既読にする。
     *
     * @param  DatabaseNotification  $notification  既読化対象の通知
     * @return RedirectResponse 既読化後のリダイレクトレスポンス
     */
    public function markAsRead(
        DatabaseNotification $notification
    ): RedirectResponse {
        /** @var User $user */
        $user = Auth::user();

        abort_unless(
            $notification->notifiable_type === User::class
            && (int) $notification->notifiable_id === $user->id,
            403
        );

        $notification->markAsRead();

        return redirect()
            ->route('notifications.index')
            ->with('success', '通知を既読にしました。');
    }
}
