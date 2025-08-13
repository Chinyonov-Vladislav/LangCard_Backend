<?php

namespace App\Http\Resources\V1\ExampleResources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *     schema="ResultUpdateMultipleExamplesResource",
 *     title="Result Update Multiple Examples Resource",
 *     description="Результат обновления одного примера в массиве примеров после массового обновления",
 *
 *     @OA\Property(
 *         property="number",
 *         type="integer",
 *         description="Порядковый номер примера в запросе",
 *         example=1
 *     ),
 *     @OA\Property(
 *         property="text",
 *         type="string",
 *         description="Текст примера, который был обновлен",
 *         example="I like to eat apples"
 *     ),
 *     @OA\Property(
 *         property="success",
 *         type="boolean",
 *         description="Флаг успешного обновления примера",
 *         example=true
 *     ),
 *     @OA\Property(
 *         property="message",
 *         type="string",
 *         description="Сообщение о результате обновления конкретного примера",
 *         example="Пример успешно обновлен"
 *     )
 * )
 */
class ResultUpdateMultipleExamplesResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'number'=>$this['number'],
            'text'=>$this['text'],
            'success'=>$this['success'],
            'message'=>$this['message'],
        ];
    }
}
