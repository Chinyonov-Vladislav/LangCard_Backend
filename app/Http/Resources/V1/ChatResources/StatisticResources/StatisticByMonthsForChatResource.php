<?php

namespace App\Http\Resources\V1\ChatResources\StatisticResources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StatisticByMonthsForChatResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            "date"=>$this['date'],
            "joined_users"=>$this['joined_users'],
            "left_users"=>$this['left_users'],
            "total_messages"=>$this['total_messages'],
        ];
    }
}
