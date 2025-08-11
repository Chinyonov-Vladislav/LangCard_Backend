<?php

namespace App\Http\Resources\V1\FilterParametersResources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;


/**
 * @OA\Schema(
 *     schema="DeckFilterParameterResource",
 *     title="API Limit Filter Parameters Resource (Ресурс для фильтров модели Deck)",
 *     description="Параметры фильтрации колод: исходные и целевые языки, типы отображения колод.",
 *     type="object",
 *     required = {"original_languages", "target_languages", "types_show_deck"},
 *     @OA\Property(
 *         property="original_languages",
 *         type="array",
 *         description="Список исходных языков (только имя языка).",
 *         @OA\Items(
 *             type="object",
 *             required = {"id", "name"},
 *             @OA\Property(property="id", type="integer", example="1"),
 *             @OA\Property(property="name", type="string", example="English")
 *         )
 *     ),
 *     @OA\Property(
 *         property="target_languages",
 *         type="array",
 *         description="Список целевых языков (только имя языка).",
 *         @OA\Items(
 *             type="object",
 *             required = {"id", "name"},
 *             @OA\Property(property="id", type="integer", example="1"),
 *             @OA\Property(property="name", type="string", example="Russian")
 *         )
 *     ),
 *     @OA\Property(
 *         property="types_show_deck",
 *         type="array",
 *         description="Типы отображения колод",
 *         @OA\Items(
 *             type="object",
 *             required = {"name", "display_value"},
 *             @OA\Property(property="name", type="string", example="all"),
 *             @OA\Property(property="display_value", type="string", example="Все")
 *         )
 *     )
 * )
 */
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
            'original_languages'=>$this->originalLanguages->map(function ($language){
                return ['id'=>$language->id,'name'=>$language->name];
            }),
            'target_languages'=>$this->targetLanguages->map(function ($language){
                return ['id'=>$language->id,'name'=>$language->name];
            }),
            'types_show_deck'=>$this->typesShowDeck->map(function ($typeShowDeck){
                return ['name'=>$typeShowDeck->name, 'display_value'=>$typeShowDeck->displayValue];
            }),
        ];
    }
}
