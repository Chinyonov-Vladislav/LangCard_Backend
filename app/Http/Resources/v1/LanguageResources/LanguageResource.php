<?php

namespace App\Http\Resources\v1\LanguageResources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LanguageResource extends JsonResource
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
            'name'=>$this->name,
            'native_name'=>$this->native_name,
            'code'=>$this->code,
            'flag_url'=>$this->flag_url,
            'locale'=>$this->locale,
        ];
    }
}
