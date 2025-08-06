<?php

namespace App\Http\Resources\V1\UserTestResultResources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *     schema="UserTestResultResource",
 *     title="User Test Result (Ресурс для результатов пользователя для попытки прохождения теста)",
 *     type="object",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="score", type="number", format="float", example=85.5),
 *     @OA\Property(property="start_time", type="string", format="date-time", example="2025-08-06T12:00:00Z"),
 *     @OA\Property(property="finish_time", type="string", format="date-time", nullable=true, example="2025-08-06T12:30:00Z"),
 *     @OA\Property(property="number_attempt", type="integer", example=2),
 *     @OA\Property(
 *         property="test",
 *         type="object",
 *         nullable=true,
 *         @OA\Property(property="id", type="integer", example=10),
 *         @OA\Property(property="name", type="string", example="Math Test"),
 *         @OA\Property(property="time_seconds", type="integer", example=1800),
 *         @OA\Property(property="count_attempts", type="integer", example=3),
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
            'test' => $this->relationLoaded('test') && $this->test ?
                [
                    'id'=>$this->test->id,
                    'name'=>$this->test->name,
                    'time_seconds'=>$this->test->time_seconds,
                    'count_attempts'=>$this->test->count_attempts,
                ] : null
        ];
    }
}
