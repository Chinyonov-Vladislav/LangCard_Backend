<?php

namespace App\Http\Resources\V1\TimezoneResources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;


/**
 * @OA\Schema(
 *     schema="TimezoneResource",
 *     title="Timezone Resource (Ресурс для получения информации о временных зонах, поддерживаемых в системе)",
 *     type="object",
 *     description="Ресурс часового пояса",
 *     @OA\Property(
 *         property="id",
 *         type="integer",
 *         format="int64",
 *         description="Уникальный идентификатор"
 *     ),
 *     @OA\Property(
 *         property="name",
 *         type="string",
 *         description="Название часового пояса"
 *     ),
 *     @OA\Property(
 *         property="offset_utc",
 *         type="integer",
 *         description="Смещение от UTC в минутах"
 *     ),
 *     @OA\Property(
 *         property="created_at",
 *         type="string",
 *         format="date-time",
 *         description="Дата и время создания"
 *     ),
 *     @OA\Property(
 *         property="updated_at",
 *         type="string",
 *         format="date-time",
 *         description="Дата и время последнего обновления"
 *     )
 * )
 */
class TimezoneResource extends JsonResource
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
            'offset_utc'=>$this->when(array_key_exists('offset_utc', $this->resource->getAttributes()), function (){
                return $this->offset_utc;
            }),
            'created_at'=>$this->when(array_key_exists('created_at', $this->resource->getAttributes()), function () {
                return $this->created_at;
            }),
            'updated_at'=>$this->when(array_key_exists('updated_at', $this->resource->getAttributes()), function (){
                return $this->updated_at;
            }),
        ];
    }
}
