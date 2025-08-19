<?php

namespace App\Http\Resources\V1\ChatResources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RequestOrInvitationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $authUserId = auth()->id();
        return [
            "id" => $this->id,
            "type" => $this->type,
            "accepted" => $this->accepted,
            "can_answer" => $this->whenLoaded('recipient', function () use ($authUserId) {
                return $this->accepted === null && $this->recipient->id === $authUserId;
            }, false),
            "can_delete"=>$this->whenLoaded("sender", function () use ($authUserId) {
                return $this->accepted === null && $this->sender->id === $authUserId;
            }),
            "created_at" => $this->created_at->toDateTimeString(),
            'room' => $this->whenLoaded('room', function () {
                $room = $this->room;
                return [
                    'id' => $room->id,
                    'name' => $room->name
                ];
            }),
            'sender' => $this->whenLoaded('sender', function () {
                $sender = $this->sender;
                return [
                    "id" => $sender->id,
                    'name' => $sender->name
                ];
            }),
            "recipient"=>$this->whenLoaded('recipient', function () {
                $recipient = $this->recipient;
                return [
                    "id" => $recipient->id,
                    'name' => $recipient->name
                ];
            })

        ];
    }
}
