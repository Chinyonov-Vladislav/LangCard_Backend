<?php

namespace App\Http\Resources\V1\NotificationResources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StatsNotificationForUserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'total_count'=>$this['total_count'],
            'read_count'=>$this['read_count'],
            'unread_count'=>$this['unread_count'],
        ];
    }
}
