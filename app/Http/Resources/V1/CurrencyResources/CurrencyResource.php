<?php

namespace App\Http\Resources\V1\CurrencyResources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CurrencyResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->when(array_key_exists('id', $this->resource->getAttributes()), function () {
                return $this->id;
            }),
            'name'=>$this->when(array_key_exists('name', $this->resource->getAttributes()), function () {
                return $this->name;
            }),
            'code'=>$this->when(array_key_exists('code', $this->resource->getAttributes()), function (){
                return $this->code;
            }),
            'symbol'=>$this->when(array_key_exists('symbol', $this->resource->getAttributes()), function () {
                return $this->symbol;
            }),
            'created_at'=>$this->when(array_key_exists('created_at', $this->resource->getAttributes()), function (){
                return $this->created_at;
            }),
            'updated_at'=>$this->when(array_key_exists('updated_at', $this->resource->getAttributes()), function (){
                return $this->updated_at;
            }),
        ];
    }
}
