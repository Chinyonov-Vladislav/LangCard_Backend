<?php

namespace App\Http\Resources\V1\LanguageResources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *     schema="LanguageResource",
 *     title="Language Resource (ресурс языка)",
 *     type="object",
 *     description="Ресурс языка с основными свойствами",
 *     required={"id", "name", "native_name", "code", "flag_url", "locale"},
 *     @OA\Property(
 *         property="id",
 *         type="integer",
 *         example=1,
 *         description="Уникальный идентификатор языка"
 *     ),
 *     @OA\Property(
 *         property="name",
 *         type="string",
 *         example="English",
 *         description="Название языка на английском"
 *     ),
 *     @OA\Property(
 *         property="native_name",
 *         type="string",
 *         example="English",
 *         description="Название языка на родном языке"
 *     ),
 *     @OA\Property(
 *         property="code",
 *         type="string",
 *         example="en",
 *         description="Код языка (например, en, ru)"
 *     ),
 *     @OA\Property(
 *         property="flag_url",
 *         type="string",
 *         format="uri",
 *         example="https://example.com/flags/en.png",
 *         description="URL изображения флага языка"
 *     ),
 *     @OA\Property(
 *         property="locale",
 *         type="string",
 *         example="en_US",
 *         description="Локаль языка"
 *     )
 * )
 */
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
