<?php

namespace App\Http\Resources\V1\ExampleResources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *     schema="InfoSavingExampleUsingWordInCardResource",
 *     title="Info Saving Example Using Word In Card Resource",
 *     description="Информация о сохранённом примере использования слова в карточке",
 *     required = {"text_example","message"},
 *     @OA\Property(
 *         property="number",
 *         type="integer",
 *         example=1,
 *         description="Порядковый номер примера (если возвращается)"
 *     ),
 *     @OA\Property(
 *         property="text_example",
 *         type="string",
 *         example="I like to eat apples",
 *         description="Текст примера"
 *     ),
 *     @OA\Property(
 *         property="message",
 *         type="string",
 *         example="Пример успешно сохранён",
 *         description="Служебное сообщение"
 *     )
 * )
 */
class InfoSavingExampleUsingWordInCardResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'number' => $this->when(array_key_exists('number', $this->resource), function () {
                return $this['number'];
            }),
            'text_example' => $this['text_example'],
            "message" => $this['message']
        ];
    }
}
