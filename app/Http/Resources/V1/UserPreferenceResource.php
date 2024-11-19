<?php

namespace App\Http\Resources\V1;

use App\Models\UserPreference;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserPreferenceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @see UserPreference
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'preferred_sources' => $this->preferred_sources,
            'preferred_categories' => $this->preferred_categories,
            'preferred_authors' => $this->preferred_authors,
        ];
    }
}
