<?php

namespace App\Http\Resources\V1\SpellCheckingResources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *     schema="SpellingCheckItemResource",
 *     title="Spelling Check Item (ресурс проверки правописания)",
 *     description="Элемент результата проверки правописания",
 *     type="object",
 *
 *     @OA\Property(
 *         property="original_word",
 *         type="string",
 *         example="mispelled",
 *         description="Исходное слово, найденное с ошибкой"
 *     ),
 *     @OA\Property(
 *         property="suggestion",
 *         description="Возможные варианты исправления слова",
 *         type="array",
 *         @OA\Items(type="string", example="misspelled"),
 *     )
 * )
 */
class SpellingCheckItemResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'original_word' => $this->original_word,
            'suggestion' => $this->suggestion,
        ];
    }
}
