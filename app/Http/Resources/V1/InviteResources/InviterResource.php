<?php

namespace App\Http\Resources\V1\InviteResources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *     schema="InviterResource",
 *     title="Inviter Resource (Ресурс для данных о пригласившем пользователе",
 *     description="Информация о пригласившем пользователе",
 *     type="object",
 *     required = {"id", "name", "avatar"},
 *     @OA\Property(
 *         property="id",
 *         type="integer",
 *         description="Уникальный идентификатор пользователя",
 *         example=123
 *     ),
 *     @OA\Property(
 *         property="name",
 *         type="string",
 *         description="Имя пользователя",
 *         example="Иван Иванов"
 *     ),
 *     @OA\Property(
 *         property="avatar",
 *         type="string",
 *         nullable=true,
 *         description="URL аватара пользователя, может отсутствовать",
 *         example="https://example.com/avatars/123.jpg"
 *     )
 * )
 */
class InviterResource extends JsonResource
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
            'avatar'=>$this->avatar_url
        ];
    }
}
