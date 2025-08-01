<?php

namespace App\Http\Resources\v1\DailyRewardResources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DailyRewardResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'number_date' => $this->number_date,
            'reward' => $this->reward,
            'is_take' => (bool)$this->is_take,
        ];
    }
}
