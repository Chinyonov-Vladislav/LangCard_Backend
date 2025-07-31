<?php

namespace App\Http\Resources\v1\InviteResources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InviterResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'=>$this->id,
            'name'=>$this->name,
            'avatar'=>$this->avatar_url
        ];
    }
}
