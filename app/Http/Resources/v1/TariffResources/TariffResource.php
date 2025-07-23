<?php

namespace App\Http\Resources\v1\TariffResources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

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
            'id'=>$this->id,
            'name'=>$this->name,
            'days'=>$this->days,
            'costs'=>$this->when($this->relationLoaded('costs'), function () {
                return $this->costs->map(fn($cost) => [
                    'id'=>$cost->id,
                    'cost'=>$cost->cost,
                    'currency'=>$cost->when($this->whenLoaded('currency'), function () use ($cost) {
                        return [
                            'id'=>$cost->currency->id,
                            'name'=>$cost->currency->name,
                            'code'=>$cost->currency->code,
                            'symbol'=>$cost->currency->symbol,
                        ];
                    })
                ]);
            })
        ];
    }
}
