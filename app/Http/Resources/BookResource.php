<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BookResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $reviewsCount = (int) ($this->reviews_count ?? 0);

        return [
            'id' => $this->id,
            'title' => $this->title,
            'author' => $this->author,
            'isbn' => (string) $this->isbn,
            'published_date' => $this->published_date,
            'description' => $this->description,
            'image_url' => $this->image_url,

            'genres' => GenreResource::collection(
                $this->whenLoaded('genres')
            ),

            'average_rating' => $reviewsCount === 0
                ? null
                : round((float) $this->reviews_avg_rating, 1),

            'reviews_count' => $reviewsCount,

            'reviews' => $this->whenLoaded('reviews', function () {
                return $this->reviews->map(function ($review) {
                    return [
                        'id' => $review->id,

                        'user' => [
                            'id' => $review->user->id,
                            'name' => $review->user->name,
                        ],

                        'rating' => $review->rating,
                        'comment' => $review->comment,
                        'created_at' => $review->created_at?->toIso8601String(),
                    ];
                });
            }),

            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
