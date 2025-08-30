<?php

namespace App\Http\Resources\V1\StatsResources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *     schema="CountUsersByMonthsResource",
 *     title="Count Users By Months Resource (ресурс статистики - количество зарегистрированных пользователей по месяцам)",
 *     type="object",
 *     required={"date", "count", "text"},
 *     @OA\Property(
 *          property="date",
 *          type="string",
 *          description="Месяц и год в формате YYYY-MM",
 *          example="2023-01",
 *          pattern="^\d{4}-(0[1-9]|1[0-2])$"
 *      ),
 *      @OA\Property(
 *          property="count",
 *          type="integer",
 *          description="Количество пользователей, зарегистрированных в этом месяце",
 *          example=15
 *      )
 * )
 */
class CountUsersByMonthsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'date'=>$this['date'],
            'count'=>$this['count']
        ];
    }
}
