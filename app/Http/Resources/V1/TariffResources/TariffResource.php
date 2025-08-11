<?php

namespace App\Http\Resources\V1\TariffResources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *     schema="TariffResource",
 *     title="Tariff Resource (ресурс тарифа)",
 *     type="object",
 *     required={"id", "name", "days"},
 *     @OA\Property(property="id", type="integer", example=1, description="ID тарифа"),
 *     @OA\Property(property="name", type="string", example="Premium", description="Название тарифа"),
 *     @OA\Property(property="days", type="integer", example=30, description="Длительность тарифа в днях"),
 *     @OA\Property(
 *         property="costs",
 *         type="array",
 *         nullable=true,
 *         description="Список цен, связанных с тарифом",
 *         @OA\Items(
 *             type="object",
 *             required={"id", "cost"},
 *             @OA\Property(property="id", type="integer", example=10, description="ID цены"),
 *             @OA\Property(property="cost", type="number", format="float", example=499.99, description="Стоимость"),
 *             @OA\Property(
 *                 property="currency",
 *                 type="object",
 *                 nullable=true,
 *                 description="Валюта цены",
 *                 required={"id", "name", "code", "symbol"},
 *                 @OA\Property(property="id", type="integer", example=1, description="ID валюты"),
 *                 @OA\Property(property="name", type="string", example="Russian Ruble", description="Название валюты"),
 *                 @OA\Property(property="code", type="string", example="RUB", description="Код валюты"),
 *                 @OA\Property(property="symbol", type="string", example="₽", description="Символ валюты")
 *             )
 *         )
 *     )
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
            'costs'=>$this->whenLoaded('costs', function () {
                $costs = $this->costs;
                $costs->map(fn ($cost) =>[
                    'id' => $cost->id,
                    'cost' => $cost->cost,
                    'currency' => $cost->whenLoaded('currency', function () use ($cost) {
                        $currency = $cost->currency;
                        return [
                            'id' => $currency->id,
                            'name' => $currency->name,
                            'code' => $currency->code,
                            'symbol' => $currency->symbol
                        ];
                    }),
                ]);
            })
        ];
    }
}
