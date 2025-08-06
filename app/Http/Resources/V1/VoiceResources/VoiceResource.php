<?php

namespace App\Http\Resources\V1\VoiceResources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *     schema="VoiceResource",
 *     title="Voice Resource (ресурс для информации о голосе с помощью которого можно озвучить текст)",
 *     type="object",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="voice_id", type="string", example="voice_123"),
 *     @OA\Property(property="voice_name", type="string", example="John"),
 *     @OA\Property(property="sex", type="string", enum={"male", "female", "other"}, example="male"),
 *     @OA\Property(property="is_active", type="boolean", example=true),
 *     @OA\Property(
 *         property="language",
 *         type="object",
 *         nullable=true,
 *         @OA\Property(property="id", type="integer", example=10),
 *         @OA\Property(property="name", type="string", example="English"),
 *         @OA\Property(property="native_name", type="string", example="English"),
 *         @OA\Property(property="code", type="string", example="en"),
 *         @OA\Property(property="flag_url", type="string", format="url", example="https://example.com/flags/en.png"),
 *         @OA\Property(property="locale", type="string", example="en_US"),
 *     )
 * )
 */
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
            'language'=>$this->relationLoaded('language') && $this->language ?
                [
                    'id'=> $this->language->id,
                    'name'=>$this->language->name,
                    'native_name'=>$this->language->native_name,
                    'code'=>$this->language->code,
                    'flag_url'=>$this->language->flag_url,
                    'locale'=>$this->language->locale,
                ] : null
        ];
    }
}
