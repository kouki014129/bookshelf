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
        return [
            'links' => [
                'first' => $paginated['first_page_url'],
                'last'  => $paginated['last_page_url'],
                'prev'  => $paginated['prev_page_url'],
                'next'  => $paginated['next_page_url'],
            ],

            'meta' => [
                'current_page' => $paginated['current_page'],
                'from'         => $paginated['from'],
                'last_page'    => $paginated['last_page'],
                'path'         => $paginated['path'],
                'per_page'     => $paginated['per_page'],
                'to'           => $paginated['to'],
                'total'        => $paginated['total'],
            ],
        ];
    }
}