<?php

namespace App\Http\Resources\V1\ChatResources;

use App\Enums\TypesRoom;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ChatResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $currentUserId = $request->user()->id;
        $nameChat = $this->name;
        if($this->room_type === TypesRoom::Direct->value && is_null($nameChat) && count($this->users) === 2)
        {
            foreach ($this->users as $user) {
                if($user->id !== $currentUserId){
                    $nameChat = $user->name;
                    break;
                }
            }
        }

        return [
            "id"=>$this->id,
            "name"=>$nameChat,
            "type"=>$this->room_type,
            "is_private"=>$this->is_private,
            "message"=>$this->message,
            "users"=>$this->users->map(function($user){
                return [
                    "id"=>$user->id,
                    "name"=>$user->name,
                    "avatar"=>$user->avatar_url,
                ];
            }),
            "latest_message"=> $this->whenLoaded("latestMessage", function () {
                $latestMessage = $this->latestMessage;
                if($latestMessage === null)
                {
                    return null;
                }
                return [
                    "id"=>$latestMessage->id,
                    "message"=>$latestMessage->message,
                    "created_at"=>$latestMessage->created_at->toDateTimeString(),
                    "user"=> $latestMessage->user ? [
                        "id" => $latestMessage->user->id,
                        "name" => $latestMessage->user->name,
                        "avatar" => $latestMessage->user->avatar_url,
                    ] : null
                ];
            }),
        ];
    }
}
