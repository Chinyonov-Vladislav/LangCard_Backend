<?php

namespace App\Http\Resources\V1\HistoryPurchaseResources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *     schema="HistoryPurchaseResource",
 *     title="HistoryPurchaseResource (Ресурс истории покупок пользователя)",
 *     type="object",
 *     required={"id", "date_purchase", "date_end_vip_status_after_buying"},
 *     @OA\Property(property="id", type="integer", example=123, description="ID записи о покупке"),
 *     @OA\Property(property="date_purchase", type="string", format="date-time", example="2025-08-11T14:30:00", description="Дата и время покупки"),
 *     @OA\Property(property="date_end_vip_status_after_buying", type="string", format="date-time", example="2025-09-11T14:30:00", description="Дата окончания VIP-статуса после покупки"),
 *
 *     @OA\Property(
 *         property="cost",
 *         type="object",
 *         nullable=true,
 *         description="Стоимость покупки",
 *         required={"id", "cost"},
 *         @OA\Property(property="id", type="integer", example=55, description="ID стоимости"),
 *         @OA\Property(property="cost", type="number", format="float", example=199.99, description="Сумма стоимости"),
 *
 *         @OA\Property(
 *             property="tariff",
 *             type="object",
 *             nullable=true,
 *             description="Тариф, связанный со стоимостью",
 *             required={"id", "name", "days"},
 *             @OA\Property(property="id", type="integer", example=10, description="ID тарифа"),
 *             @OA\Property(property="name", type="string", example="Месячный", description="Название тарифа"),
 *             @OA\Property(property="days", type="integer", example=30, description="Количество дней тарифа"),
 *         ),
 *
 *         @OA\Property(
 *             property="currency",
 *             type="object",
 *             nullable=true,
 *             description="Валюта стоимости",
 *             required={"id", "name", "code", "symbol"},
 *             @OA\Property(property="id", type="integer", example=1, description="ID валюты"),
 *             @OA\Property(property="name", type="string", example="Российский рубль", description="Название валюты"),
 *             @OA\Property(property="code", type="string", example="RUB", description="Код валюты"),
 *             @OA\Property(property="symbol", type="string", example="₽", description="Символ валюты"),
 *         ),
 *     ),
 * )
 */
class HistoryPurchaseResource extends JsonResource
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
            'date_purchase' => $this->date_purchase,
            'date_end_vip_status_after_buying' => $this->date_end,

            'cost' => $this->whenLoaded('cost', function () {
                $cost = $this->cost;
                return [
                    'id' => $cost->id,
                    'cost' => $cost->cost,

                    'tariff' => $cost->whenLoaded('tariff', function () use ($cost) {
                        $tariff = $cost->tariff;
                        return [
                            'id' => $tariff->id,
                            'name' => $tariff->name,
                            'days' => $tariff->days,
                        ];
                    }),

                    'currency' => $cost->whenLoaded('currency', function () use ($cost) {
                        $currency = $cost->currency;
                        return [
                            'id' => $currency->id,
                            'name' => $currency->name,
                            'code' => $currency->code,
                            'symbol' => $currency->symbol,
                        ];
                    }),
                ];
            }),
        ];
    }
}
