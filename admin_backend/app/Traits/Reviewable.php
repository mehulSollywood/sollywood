<?php

namespace App\Traits;

use App\Models\Review;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait Reviewable
{
    public function addReview($params): void
    {
        /** @var Review $review */

        $review = $this->reviews()->updateOrCreate([
            'user_id'           => auth('sanctum')->id(),
            'reviewable_id'     => $this->id,
            'reviewable_type'   => self::class,
        ], [
            'rating'            => data_get($params, 'rating'),
            'comment'           => data_get($params, 'comment'),
        ]);

        if (!empty(data_get($params, 'images.0'))) {
            $review->galleries()->delete();
            $review->update(['img' => data_get($params, 'images.0')]);
            $review->uploads(data_get($params, 'images', []));
        }
    }

    public function addOrderReview($collection, $assignable): void
    {
        /** @var Review $review */

        $review = $this->reviews()->updateOrCreate([
            'user_id'           => auth('sanctum')->id(),
            'reviewable_id'     => $this->id,
            'reviewable_type'   => self::class,
            'assignable_id'     => $assignable->id,
            'assignable_type'   => get_class($assignable),
        ], [
            'rating'            => data_get($collection, 'rating'),
            'comment'           => data_get($collection, 'comment'),
        ]);

        if (!empty(data_get($collection, 'images.0'))) {
            $review->galleries()->delete();
            $review->update(['img' => data_get($collection, 'images.0')]);
            $review->uploads(data_get($collection, 'images', []));
        }
    }

    public function reviews(): MorphMany
    {
        return $this->morphMany(Review::class, 'reviewable');
    }

}
