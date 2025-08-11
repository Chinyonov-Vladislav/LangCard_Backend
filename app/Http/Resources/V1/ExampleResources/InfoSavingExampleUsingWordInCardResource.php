<?php

namespace App\Http\Resources\V1\ExampleResources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InfoSavingExampleUsingWordInCardResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        logger($this->resource);
        return [
            'number' => $this->when(array_key_exists('number', $this->resource), function () {
                return $this['number'];
            }),
            'text_example' => $this['text_example'],
            "message" => $this['message']
        ];
    }
}
