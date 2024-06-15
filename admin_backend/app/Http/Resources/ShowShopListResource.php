<?php

// Assuming this is your UserResource class located in app/Http/Resources/UserResource.php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ShowShopListResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'slug' => $this->slug,
            // Add other fields you want to include in the response
        ];
    }
}
