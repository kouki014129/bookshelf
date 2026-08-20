<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class BookCollection extends ResourceCollection
{
    public function toArray(Request $request): array
    {
        return [
            'data' => BookResource::collection($this->collection),
        ];
    }

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
