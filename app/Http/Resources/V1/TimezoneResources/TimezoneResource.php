<?php

namespace App\Http\Resources\V1\TimezoneResources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;


/**
 * @OA\Schema(
 *     schema="Timezone",
 *     type="object",
 *     description="Ресурс часового пояса",
 *     @OA\Property(
 *         property="id",
 *         type="integer",
 *         format="int64",
 *         nullable=true,
 *         description="Уникальный идентификатор"
 *     ),
 *     @OA\Property(
 *         property="name",
 *         type="string",
 *         nullable=true,
 *         description="Название часового пояса"
 *     ),
 *     @OA\Property(
 *         property="offset_utc",
 *         type="integer",
 *         nullable=true,
 *         description="Смещение от UTC в минутах"
 *     ),
 *     @OA\Property(
 *         property="created_at",
 *         type="string",
 *         format="date-time",
 *         nullable=true,
 *         description="Дата и время создания"
 *     ),
 *     @OA\Property(
 *         property="updated_at",
 *         type="string",
 *         format="date-time",
 *         nullable=true,
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
            'id' => $this->when(array_key_exists('id', $this->resource->getAttributes()), $this->id),
            'name'=>$this->when(array_key_exists('name', $this->resource->getAttributes()), $this->name),
            'offset_utc'=>$this->when(array_key_exists('offset_utc', $this->resource->getAttributes()), $this->offset_utc),
            'created_at'=>$this->when(array_key_exists('created_at', $this->resource->getAttributes()), $this->created_at),
            'updated_at'=>$this->when(array_key_exists('updated_at', $this->resource->getAttributes()), $this->updated_at),
        ];
    }
}
