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
 *     @OA\Property(
 *         property="originalLanguages",
 *         type="array",
 *         description="Список исходных языков (только имя языка).",
 *         @OA\Items(
 *             type="object",
 *             @OA\Property(property="name", type="string", example="English")
 *         )
 *     ),
 *     @OA\Property(
 *         property="targetLanguages",
 *         type="array",
 *         description="Список целевых языков (только имя языка).",
 *         @OA\Items(
 *             type="object",
 *             @OA\Property(property="name", type="string", example="Russian")
 *         )
 *     ),
 *     @OA\Property(
 *         property="typesShowDeck",
 *         type="array",
 *         description="Типы отображения колод",
 *         @OA\Items(
 *             type="object",
 *             @OA\Property(property="name", type="string", example="all"),
 *             @OA\Property(property="displayValue", type="string", example="Все")
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
            'originalLanguages'=>$this->originalLanguages,
            'targetLanguages'=>$this->targetLanguages,
            'typesShowDeck'=>$this->typesShowDeck,
        ];
    }
}
