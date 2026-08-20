<?php

namespace App\Http\Controllers;

use App\Models\User;
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
}
