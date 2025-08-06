<?php

namespace App\Http\Resources\V1\ProfileUserResources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *     schema="ProfileUserResource",
 *     type="object",
 *     title="Profile User Resource",
 *     description="Ресурс профиля пользователя с основной информацией",
 *     required={"id", "name", "email", "created_at"},
 *     @OA\Property(
 *         property="id",
 *         type="integer",
 *         example=123,
 *         description="Уникальный идентификатор пользователя"
 *     ),
 *     @OA\Property(
 *         property="name",
 *         type="string",
 *         example="Иван Иванов",
 *         description="Имя пользователя"
 *     ),
 *     @OA\Property(
 *         property="email",
 *         type="string",
 *         format="email",
 *         example="ivan@example.com",
 *         description="Email пользователя"
 *     ),
 *     @OA\Property(
 *         property="vip_status_time_end",
 *         type="string",
 *         format="date-time",
 *         nullable=true,
 *         example="2025-12-31 23:59:59",
 *         description="Дата и время окончания VIP-статуса или null"
 *     ),
 *     @OA\Property(
 *         property="invite_code",
 *         type="string",
 *         nullable=true,
 *         example="INVITE123",
 *         description="Код приглашения, доступен только для авторизованного пользователя"
 *     ),
 *     @OA\Property(
 *         property="created_at",
 *         type="string",
 *         format="date-time",
 *         example="2023-08-06 12:34:56",
 *         description="Дата и время создания профиля"
 *     ),
 *     @OA\Property(
 *         property="currency",
 *         type="object",
 *         nullable=true,
 *         description="Валюта пользователя",
 *         @OA\Property(property="id", type="integer", example=1),
 *         @OA\Property(property="name", type="string", example="US Dollar"),
 *         @OA\Property(property="code", type="string", example="USD"),
 *         @OA\Property(property="symbol", type="string", example="$")
 *     ),
 *     @OA\Property(
 *         property="timezone",
 *         type="object",
 *         nullable=true,
 *         description="Часовой пояс пользователя",
 *         @OA\Property(property="id", type="integer", example=5),
 *         @OA\Property(property="name", type="string", example="Europe/Moscow"),
 *         @OA\Property(property="offset_utc", type="string", example="+03:00")
 *     ),
 *     @OA\Property(
 *         property="inviter",
 *         type="object",
 *         nullable=true,
 *         description="Пригласивший пользователь",
 *         @OA\Property(property="id", type="integer", example=10),
 *         @OA\Property(property="name", type="string", example="Пётр Петров"),
 *         @OA\Property(property="avatar", type="string", format="uri", example="https://example.com/avatar.jpg")
 *     )
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
            'currency' => $this->relationLoaded('currency') && $this->currency ?
                [
                    'id' => $this->currency->id,
                    'name' => $this->currency->name,
                    'code' => $this->currency->code,
                    'symbol' => $this->currency->symbol
                ] : null,
            'timezone' => $this->relationLoaded('timezone') && $this->timezone ?
                [
                    'id' => $this->timezone->id,
                    'name' => $this->timezone->name,
                    'offset_utc' => $this->timezone->offset_utc,
                ]: null,
            'inviter'=>$this->relationLoaded('inviter') && $this->inviter ?
                [
                    'id'=>$this->inviter->id,
                    'name'=>$this->inviter->name,
                    'avatar'=>$this->inviter->avatar_url
                ] :null
        ];
    }
}
