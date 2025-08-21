<?php

namespace App\Http\Resources\V1\NewsResources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FullNewsResource extends JsonResource
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
            "content"=>$this->content,
            "published_at"=>$this->published_at,
            "can_redact"=>$this->whenLoaded("user", function () {
                $user = $this->user;
                return auth()->id() === $user->id;
            }, false),
            "can_delete"=>$this->whenLoaded("user", function () {
                $user = $this->user;
                return auth()->id() === $user->id;
            }, false),
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
