<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * APIログインを行い、Sanctumのアクセストークンを発行する。
     *
     * @param  Request  $request  ログイン情報
     * @return JsonResponse ログイン結果とアクセストークン
     *
     * @throws ValidationException 認証情報が正しくない場合
     */
    public function login(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'email' => [
                'required',
                'email',
            ],
            'password' => [
                'required',
                'string',
            ],
        ]);

        $user = User::where('email', $credentials['email'])
            ->first();

        if (
            ! $user ||
            ! Hash::check($credentials['password'], $user->password)
        ) {
            throw ValidationException::withMessages([
                'email' => [
                    '認証情報が正しくありません。',
                ],
            ]);
        }

        $token = $user
            ->createToken('api-token')
            ->plainTextToken;

        return response()->json([
            'message' => 'ログインしました。',
            'token' => $token,
        ]);
    }

    /**
     * 現在利用中のSanctumアクセストークンを削除する。
     *
     * @param  Request  $request  認証済みリクエスト
     * @return JsonResponse ログアウト結果
     */
    public function logout(Request $request): JsonResponse
    {
        $request
            ->user()
            ->currentAccessToken()
            ->delete();

        return response()->json([
            'message' => 'ログアウトしました。',
        ]);
    }
}
