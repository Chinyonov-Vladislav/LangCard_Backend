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
 *     ),
 *     @OA\Property(
 *         property="created_at",
 *         type="string",
 *         format="date-time",
 *         example="2025-08-28T12:34:56Z",
 *         description="Дата создания записи"
 *     ),
 *     @OA\Property(
 *          property="updated_at",
 *          type="string",
 *          format="date-time",
 *          example="2025-08-28T12:34:56Z",
 *          description="Дата изменения записи"
 *      ),
 *
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
            'id' => $this->when(array_key_exists('id', $this->resource->getAttributes()), function () {
                return $this->id;
            }),
            'name'=>$this->when(array_key_exists('name', $this->resource->getAttributes()), function () {
                return $this->name;
            }),
            'native_name'=>$this->when(array_key_exists('native_name', $this->resource->getAttributes()), function (){
                return $this->native_name;
            }),
            'code'=>$this->when(array_key_exists('code', $this->resource->getAttributes()), function () {
                return $this->code;
            }),
            'flag_url'=>$this->when(array_key_exists('flag_url', $this->resource->getAttributes()), function (){
                return $this->flag_url;
            }),
            'locale'=>$this->when(array_key_exists('locale', $this->resource->getAttributes()), function (){
                return $this->locale;
            }),
            'created_at'=>$this->when(array_key_exists('created_at', $this->resource->getAttributes()), function (){
                return $this->created_at;
            }),
            'updated_at'=>$this->when(array_key_exists('updated_at', $this->resource->getAttributes()), function (){
                return $this->updated_at;
            }),
        ];
    }
}
