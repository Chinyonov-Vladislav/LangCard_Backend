<?php

namespace App\Http\Resources\V1\UserTestResultResources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;


/**
 * @OA\Schema(
 *     schema="UserTestResultResource",
 *     title="User Test Result (Ресурс для результатов пользователя для попытки прохождения теста)",
 *     type="object",
 *     required={"id", "score", "start_time", "finish_time", "number_attempt"},
 *     @OA\Property(property="id", type="integer", example=101),
 *     @OA\Property(property="score", type="number", format="float", example=85.5, description="Баллы пользователя за тест"),
 *     @OA\Property(property="start_time", type="string", format="date-time", example="2025-08-11T14:00:00Z"),
 *     @OA\Property(property="finish_time", type="string", format="date-time", example="2025-08-11T14:30:00Z"),
 *     @OA\Property(property="number_attempt", type="integer", example=1, description="Номер попытки прохождения теста"),
 *
 *     @OA\Property(
 *         property="test",
 *         type="object",
 *         description="Данные теста",
 *         required = {"id", "name", "time_seconds","count_attempts"},
 *         @OA\Property(property="id", type="integer", example=10),
 *         @OA\Property(property="name", type="string", example="Тест по английскому"),
 *         @OA\Property(property="time_seconds", type="integer", example=1800, description="Время на тест в секундах"),
 *         @OA\Property(property="count_attempts", type="integer", example=3, description="Максимальное количество попыток"),
 *     ),
 * )
 */

class UserTestResultResource extends JsonResource
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
            'score'=>$this->score,
            'start_time'=>$this->start_time,
            'finish_time'=> $this->finish_time,
            'number_attempt'=>$this->number_attempt,
            'test'=>$this->whenLoaded('test', function () {
               $test = $this->test;
               return [
                   'id'=>$test->id,
                   'name'=>$test->name,
                   'time_seconds'=>$test->time_seconds,
                   'count_attempts'=>$test->count_attempts,
               ];
            }),
        ];
    }
}
