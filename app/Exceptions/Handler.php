<?php

namespace App\Exceptions;

use App\Models\Book;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * 入力値として表示しない項目
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * 例外処理を登録する
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $exception) {
            //
        });

        $this->renderable(function (
            NotFoundHttpException $exception,
            Request $request
        ) {
            $previousException = $exception->getPrevious();

            if (
                $request->is('api/v1/books/*')
                && $previousException instanceof ModelNotFoundException
                && $previousException->getModel() === Book::class
            ) {
                return response()->json([
                    'message' => '指定された書籍が見つかりません。',
                ], 404);
            }

            return null;
        });
    }
}