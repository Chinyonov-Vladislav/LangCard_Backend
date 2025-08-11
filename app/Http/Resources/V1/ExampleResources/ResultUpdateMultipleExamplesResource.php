<?php

namespace App\Http\Resources\V1\ExampleResources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ResultUpdateMultipleExamplesResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'number'=>$this['number'],
            'text'=>$this['text'],
            'success'=>$this['success'],
            'message'=>$this['message'],
        ];
    }
}
