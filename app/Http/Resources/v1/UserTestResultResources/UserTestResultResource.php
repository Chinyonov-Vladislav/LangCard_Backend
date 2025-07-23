<?php

namespace App\Http\Resources\v1\UserTestResultResources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserTestResultResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'=>$this->id,
            'score'=>$this->score,
            'start_time'=>$this->start_time,
            'finish_time'=> $this->finish_time,
            'number_attempt'=>$this->number_attempt,
            'test' => $this->when($this->relationLoaded('test'), function (){
                return [
                    'id'=>$this->test->id,
                    'name'=>$this->test->name,
                    'time_seconds'=>$this->test->time_seconds,
                    'count_attempts'=>$this->test->count_attempts,
                ];
            })
        ];
    }
}
