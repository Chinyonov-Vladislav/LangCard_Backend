<?php

namespace App\Http\Resources\V1\MessageResources;

use App\Enums\TypesMessage;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MessageResource extends JsonResource
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
            "message"=>$this->message,
            "type"=>$this->type,
            "created_at"=>$this->created_at->toDateTimeString(),
            "user"=>$this->whenLoaded('user', function (){
                $user = $this->user;
                return [
                    "id"=>$user->id,
                    "name"=>$user->name,
                    "avatar_url"=>$user->avatar_url,
                ];
            }),
            "emotions"=>$this->when($this->type === TypesMessage::MessageFromUser->value && $this->relationLoaded("messageEmotions"), function () {
                return $this->messageEmotions->map(function ($messageEmotion) {
                    $emotion = $messageEmotion->relationLoaded('emotion') ? $messageEmotion->emotion : null;
                    if($emotion === null) {
                        return null;
                    }
                    return [
                        "id"=>$emotion->id,
                        "name"=>$emotion->name,
                        'icon'=>$emotion->icon,
                        'count'=>$messageEmotion->users_count,
                        "reacted_by_me"=>(bool)$messageEmotion->reacted_by_me
                    ];
                })->values();
            }),
        ];
    }
}
