<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class BookCollection extends ResourceCollection
{
    /**
     * 書籍一覧をAPIレスポンス用の配列に変換する。
     *
     * @param  Request  $request  APIリクエスト
     * @return array<string, mixed> 書籍一覧
     */
    public function toArray(Request $request): array
    {
        return [
            'data' => BookResource::collection($this->collection),
        ];
    }

    /**
     * ページネーション情報をAPI仕様に合わせて整形する。
     *
     * @param  Request  $request  APIリクエスト
     * @param  array<string, mixed>  $paginated  Laravelのページネーション情報
     * @param  array<string, mixed>  $default  デフォルトのページネーション情報
     * @return array<string, mixed> 整形済みページネーション情報
     */
    public function paginationInformation(
        $request,
        $paginated,
        $default
    ): array {
        $currentPage = (int) $paginated['current_page'];
        $lastPage = (int) $paginated['last_page'];

        return [
            'meta' => [
                'current_page' => $currentPage,
                'last_page' => $lastPage,
                'prev_page' => $currentPage > 1 ? $currentPage - 1 : null,
                'next_page' => $currentPage < $lastPage ? $currentPage + 1 : null,
                'per_page' => (int) $paginated['per_page'],
                'from' => $paginated['from'],
                'to' => $paginated['to'],
                'total' => $paginated['total'],
            ],
        ];
    }
}
