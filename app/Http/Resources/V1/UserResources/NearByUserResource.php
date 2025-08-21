<?php

namespace App\Http\Resources\V1\UserResources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NearByUserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            "id"=>$this->id,
            "name"=>$this->name,
            "avatar"=>$this->avatar_url
        ];
    }
}
