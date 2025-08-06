<?php

namespace App\Http\Resources\V1\FilterParametersResources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;


/**
 * @OA\Schema(
 *     schema="ApiLimitFilterParametersResource",
 *     title="API Limit Filter Parameters Resource (Ресурс для фильтров модели ApiLimit)",
 *     description="Доступные фильтры для модели API Limit: количество дней и запросов.",
 *     type="object",
 *     @OA\Property(
 *         property="day_range",
 *         type="object",
 *         description="Диапазон дней",
 *         @OA\Property(property="min", type="integer", example=1),
 *         @OA\Property(property="max", type="integer", example=30)
 *     ),
 *     @OA\Property(
 *         property="request_count_range",
 *         type="object",
 *         description="Диапазон количества запросов",
 *         @OA\Property(property="min", type="integer", example=10),
 *         @OA\Property(property="max", type="integer", example=1000)
 *     )
 * )
 */
class ApiLimitFilterParametersResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'day_range' => [
                'min' => $this->min_day,
                'max' => $this->max_day,
            ],
            'request_count_range' => [
                'min' => $this->min_request_count,
                'max' => $this->max_request_count,
            ],
        ];
    }
}
