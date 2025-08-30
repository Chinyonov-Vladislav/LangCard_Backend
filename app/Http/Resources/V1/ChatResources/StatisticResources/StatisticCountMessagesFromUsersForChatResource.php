<?php

namespace App\Http\Resources\V1\ChatResources\StatisticResources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StatisticCountMessagesFromUsersForChatResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            "id" => $this->id,
            "name" => $this->name,
            "avatar_url" => $this->avatar_url,
            "messages_count" => $this->messages_count
        ];
    }
}
