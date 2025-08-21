<?php

namespace App\Http\Resources\V1\NewsResources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ShortNewsResource extends JsonResource
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
            'title'=>$this->title,
            "main_image"=>$this->main_image,
            "published_at"=>$this->published_at,
            "user"=>$this->whenLoaded('user', function (){
                $user = $this->user;
                return [
                    "id"=>$user->id,
                    "name"=>$user->name,
                    'avatar'=>$user->avatar_url
                ];
            }),
        ];
    }
}
