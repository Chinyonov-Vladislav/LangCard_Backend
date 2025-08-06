<?php

namespace App\Http\Resources\V1\HistoryPurchaseResources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

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
            'cost' => $this->relationLoaded('cost') && $this->cost
                ? [
                    'id' => $this->cost->id,
                    'cost' => $this->cost->cost,
                    'tariff' => $this->cost->relationLoaded('tariff') && $this->cost->tariff
                        ? [
                            'id' => $this->cost->tariff->id,
                            'name' => $this->cost->tariff->name,
                            'days' => $this->cost->tariff->days,
                        ]
                        : null,
                    'currency' => $this->cost->relationLoaded('currency') && $this->cost->currency
                        ? [
                            'id' => $this->cost->currency->id,
                            'name' => $this->cost->currency->name,
                            'code' => $this->cost->currency->code,
                            'symbol' => $this->cost->currency->symbol,
                        ]
                        : null,
                ]
                : null,
        ];
    }
}
