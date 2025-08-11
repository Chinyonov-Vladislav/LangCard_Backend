<?php

namespace App\Http\Resources\V1\ProfileUserResources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *     schema="ProfileUserResource",
 *     title="Profile User Resource (ресурс профиля пользователя)",
 *     type="object",
 *     required={"id", "name", "email", "created_at", "vip_status_time_end", "invite_code"},
 *     @OA\Property(property="id", type="integer", example=101, description="ID пользователя"),
 *     @OA\Property(property="name", type="string", example="Владислав", description="Имя пользователя"),
 *     @OA\Property(property="email", type="string", format="email",nullable=true, example="user@example.com", description="Email пользователя"),
 *     @OA\Property(property="vip_status_time_end", type="string", format="date-time", nullable=true, example="2025-09-10 12:00:00", description="Дата окончания VIP-статуса или null"),
 *     @OA\Property(property="invite_code", type="string", nullable=true, example="ABCD1234", description="Код приглашения (доступен только аутентифицированному пользователю)"),
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2023-04-01 10:30:00", description="Дата и время создания пользователя"),
 *
 *     @OA\Property(
 *         property="currency",
 *         type="object",
 *         nullable=true,
 *         description="Валюта пользователя",
 *         required={"id", "name", "code", "symbol"},
 *         @OA\Property(property="id", type="integer", example=1),
 *         @OA\Property(property="name", type="string", example="Российский рубль"),
 *         @OA\Property(property="code", type="string", example="RUB"),
 *         @OA\Property(property="symbol", type="string", example="₽")
 *     ),
 *
 *     @OA\Property(
 *         property="timezone",
 *         type="object",
 *         nullable=true,
 *         description="Часовой пояс пользователя",
 *         required={"id", "name", "offset_utc"},
 *         @OA\Property(property="id", type="integer", example=5),
 *         @OA\Property(property="name", type="string", example="Europe/Moscow"),
 *         @OA\Property(property="offset_utc", type="string", example="+03:00")
 *     ),
 *
 *     @OA\Property(
 *         property="inviter",
 *         type="object",
 *         nullable=true,
 *         description="Пользователь, который пригласил",
 *         required={"id", "name", "avatar"},
 *         @OA\Property(property="id", type="integer", example=45),
 *         @OA\Property(property="name", type="string", example="Иван Иванов"),
 *         @OA\Property(property="avatar", type="string", format="uri", example="https://example.com/avatar.jpg")
 *     ),
 * )
 */
class ProfileUserResource extends JsonResource
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
            'email' => $this->email,
            'vip_status_time_end' => $this->vip_status_time_end
                ? $this->vip_status_time_end->format('Y-m-d H:i:s')
                : null,
            'invite_code'=> $this->isAuthUser ? $this->invite_code : null,
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'currency' => $this->whenLoaded('currency', function (){
                $currency = $this->currency;
                if($currency === null)
                {
                    return null;
                }
                return [
                    'id' => $currency->id,
                    'name' => $currency->name,
                    'code' => $currency->code,
                    'symbol' => $currency->symbol
                ];
            }),
            'timezone' => $this->whenLoaded('timezone', function (){
                $timezone = $this->timezone;
                if($timezone === null)
                {
                    return null;
                }
                return [
                    'id' => $timezone->id,
                    'name' => $timezone->name,
                    'offset_utc' => $timezone->offset_utc,
                ];
            }),
            'inviter'=>$this->whenLoaded('inviter', function (){
                $inviter =$this->inviter;
                if($inviter === null)
                {
                    return null;
                }
                return [
                    'id'=>$inviter->id,
                    'name'=>$inviter->name,
                    'avatar'=>$inviter->avatar_url
                ];
            }),
        ];
    }
}
