<?php

namespace App\Http\Resources\v1\FilterParametersResources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DeckFilterParameterResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'originalLanguages'=>$this->originalLanguages,
            'targetLanguages'=>$this->targetLanguages,
            'typesShowDeck'=>$this->typesShowDeck,
        ];
    }
}
