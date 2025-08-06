<?php

namespace App\Http\Resources\V1\TariffResources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *     schema="TariffResource",
 *     title="Tariff Resource (Ресурс тарифа)",
 *     type="object",
 *     required={"id", "name", "days"},
 *     @OA\Property(
 *         property="id",
 *         type="integer",
 *         example=1,
 *         description="Уникальный идентификатор тарифа"
 *     ),
 *     @OA\Property(
 *         property="name",
 *         type="string",
 *         example="Premium Plan",
 *         description="Название тарифа"
 *     ),
 *     @OA\Property(
 *         property="days",
 *         type="integer",
 *         example=30,
 *         description="Количество дней действия тарифа"
 *     ),
 *     @OA\Property(
 *         property="costs",
 *         type="array",
 *         nullable=true,
 *         description="Список цен тарифа в разных валютах",
 *         @OA\Items(
 *             type="object",
 *             required={"id", "cost"},
 *             @OA\Property(
 *                 property="id",
 *                 type="integer",
 *                 example=10,
 *                 description="Идентификатор стоимости"
 *             ),
 *             @OA\Property(
 *                 property="cost",
 *                 type="number",
 *                 format="float",
 *                 example=99.99,
 *                 description="Стоимость тарифа"
 *             ),
 *             @OA\Property(
 *                 property="currency",
 *                 type="object",
 *                 nullable=true,
 *                 description="Валюта стоимости",
 *                 required={"id", "name", "code", "symbol"},
 *                 @OA\Property(
 *                     property="id",
 *                     type="integer",
 *                     example=1,
 *                     description="Идентификатор валюты"
 *                 ),
 *                 @OA\Property(
 *                     property="name",
 *                     type="string",
 *                     example="US Dollar",
 *                     description="Название валюты"
 *                 ),
 *                 @OA\Property(
 *                     property="code",
 *                     type="string",
 *                     example="USD",
 *                     description="Код валюты"
 *                 ),
 *                 @OA\Property(
 *                     property="symbol",
 *                     type="string",
 *                     example="$",
 *                     description="Символ валюты"
 *                 ),
 *             ),
 *         ),
 *     ),
 * )
 */
class TariffResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'days' => $this->days,
            'costs' => $this->relationLoaded('costs') && $this->costs ?
                $this->costs->map(fn($cost) => [
                    'id' => $cost->id,
                    'cost' => $cost->cost,
                    'currency' => $cost->relationLoaded('currency') && $cost->currency ?
                        [
                            'id' => $cost->currency->id,
                            'name' => $cost->currency->name,
                            'code' => $cost->currency->code,
                            'symbol' => $cost->currency->symbol,
                        ] : null,
                ]) : null,
        ];
    }
}
