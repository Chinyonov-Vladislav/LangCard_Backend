<?php

namespace App\Http\Resources\V1\VoiceResources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VoiceResource extends JsonResource
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
            'voice_id'=>$this->voice_id,
            'voice_name'=>$this->voice_name,
            'sex'=>$this->sex,
            'is_active'=>$this->is_active,
            'language'=>$this->when($this->relationLoaded('language'), function () {
                return [
                    'id'=> $this->language->id,
                    'name'=>$this->language->name,
                    'native_name'=>$this->language->native_name,
                    'code'=>$this->language->code,
                    'flag_url'=>$this->language->flag_url,
                    'locale'=>$this->language->locale,
                ];
            })
        ];
    }
}
