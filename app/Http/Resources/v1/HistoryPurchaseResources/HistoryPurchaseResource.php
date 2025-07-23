<?php

namespace App\Http\Resources\v1\HistoryPurchaseResources;

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
            'cost' => $this->when($this->relationLoaded('cost'), function () {
                return [
                    'id' => $this->cost->id,
                    'cost'=>$this->cost->cost,
                    'tariff'=>$this->when($this->cost->relationLoaded('tariff'), function (){
                        return [
                            "id"=>$this->cost->tariff->id,
                            "name"=>$this->cost->tariff->name,
                            "days"=>$this->cost->tariff->days,
                        ];
                    }),
                    'currency'=>$this->when($this->cost->relationLoaded('currency'), function () {
                        return [
                            "id"=>$this->cost->currency->id,
                            "name"=>$this->cost->currency->name,
                            "code"=>$this->cost->currency->code,
                            "symbol"=>$this->cost->currency->symbol,
                        ];
                    })
                ];
            })
        ];
    }
}
