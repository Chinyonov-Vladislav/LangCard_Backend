<?php

namespace App\Http\Resources\V1\MessageResources;

use App\Enums\TypesMessage;
use Carbon\Carbon;
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
            "created_at"=> $this->created_at->toDateTimeString(),
            "is_message_from_me"=> $this->relationLoaded('user') && ($this->type === TypesMessage::MessageFromUser->value && $this->user->id === auth()->id()),
            "user"=>$this->whenLoaded('user', function (){
                $user = $this->user;
                return [
                    "id"=>$user->id,
                    "name"=>$user->name,
                    "avatar_url"=>$user->avatar_url,
                ];
            }),
            "emotions"=>$this->when($this->type === TypesMessage::MessageFromUser->value && $this->relationLoaded("emotions"), function () {
                return $this->emotions->map(function ($emotion) {
                    return [
                        "id"=>$emotion->id,
                        "name"=>$emotion->name,
                        'icon'=>$emotion->icon,
                        'count'=>$emotion->message_emotions_count,
                        "reacted_by_me"=>(bool)$emotion->reacted_by_current_user
                    ];
                });
            }),
            "attachments"=>$this->when($this->type === TypesMessage::MessageFromUser->value && $this->relationLoaded("attachments"), function () {
                return $this->attachments->map(function ($attachment) {
                    return [
                        "id"=>$attachment->id,
                        "path"=>$attachment->path,
                        'type'=>$attachment->type,
                        'extension'=>$attachment->extension,
                        "size"=>$attachment->size
                    ];
                });
            })
        ];
    }
}
