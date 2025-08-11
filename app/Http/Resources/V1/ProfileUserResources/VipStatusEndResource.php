<?php

namespace App\Http\Resources\V1\ProfileUserResources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;


/**
 * @OA\Schema(
 *     schema="VipStatusEndResource",
 *     title="Vip Status End Resource (ресурс окончания vip-статуса пользователя)",
 *     type="object",
 *     required={"vip_status_time_end"},
 *     @OA\Property(property="vip_status_time_end", type="string", format="date-time", nullable=true, example="2025-09-10 12:00:00", description="Дата окончания VIP-статуса или null"),
 * )
 */
class VipStatusEndResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'vip_status_time_end' => $this->vip_status_time_end
                ? $this->vip_status_time_end->format('Y-m-d H:i:s')
                : null,
        ];
    }
}
