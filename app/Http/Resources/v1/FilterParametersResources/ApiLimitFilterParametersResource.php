<?php

namespace App\Http\Resources\v1\FilterParametersResources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ApiLimitFilterParametersResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'day_range' => [
                'min' => $this->min_day,
                'max' => $this->max_day,
            ],
            'request_count_range' => [
                'min' => $this->min_request_count,
                'max' => $this->max_request_count,
            ],
        ];
    }
}
