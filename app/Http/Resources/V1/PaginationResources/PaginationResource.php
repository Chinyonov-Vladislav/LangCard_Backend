<?php

namespace App\Http\Resources\V1\PaginationResources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;


/**
 * @OA\Schema(
 *     schema="PaginationResource",
 *     title="Pagination Resource (ресурс пагинации)",
 *     description="Структура данных пагинации",
 *     @OA\Property(property="current_page", type="integer", example=1, description="Текущая страница"),
 *     @OA\Property(property="per_page", type="integer", example=10, description="Количество элементов на странице"),
 *     @OA\Property(property="total", type="integer", example=125, description="Общее количество элементов"),
 *     @OA\Property(property="last_page", type="integer", example=13, description="Последняя страница"),
 *     @OA\Property(property="from", type="integer", example=1, description="Номер первого элемента на странице"),
 *     @OA\Property(property="to", type="integer", example=10, description="Номер последнего элемента на странице"),
 *     @OA\Property(
 *         property="links",
 *         type="array",
 *         description="Массив ссылок для навигации",
 *         @OA\Items(
 *             @OA\Property(property="url", type="string", nullable=true, example="https://example.com?page=2"),
 *             @OA\Property(property="label", type="string", example="2"),
 *             @OA\Property(property="active", type="boolean", example=false)
 *         )
 *     )
 * )
 */
class PaginationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'current_page'=>$this['current_page'],
            'per_page'=>$this['per_page'],
            "total"=>$this['total'],
            "last_page"=>$this['last_page'],
            'from'=>$this['from'],
            "to"=>$this['to'],
            'links'=>$this['links']->map(function ($item) {
                return [
                    "url"=>$item['url'],
                    "label"=>$item['label'],
                    'active'=>$item['active']
                ];
            })
        ];
    }
}
