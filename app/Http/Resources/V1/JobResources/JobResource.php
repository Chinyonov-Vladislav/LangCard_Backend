<?php

namespace App\Http\Resources\V1\JobResources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *     schema="JobResource",
 *     title="Job Resource (ресурс для информации статусе Job, инициируемой пользователем",
 *     description="Ресурс задания с основной информацией о задаче и её результате",
 *     @OA\Property(
 *         property="id",
 *         type="integer",
 *         description="Внутренний идентификатор записи в базе",
 *         example=1
 *     ),
 *     @OA\Property(
 *         property="job_id",
 *         type="string",
 *         description="Идентификатор задачи в очереди или системе выполнения",
 *         example="job_123456"
 *     ),
 *     @OA\Property(
 *         property="initial_data",
 *         type="object",
 *         nullable = true,
 *         description="Начальные данные задачи",
 *         example={"param1":"value1","param2":2}
 *     ),
 *     @OA\Property(
 *         property="name_job",
 *         type="string",
 *         description="Название или тип задачи",
 *         example="ProcessUserData"
 *     ),
 *     @OA\Property(
 *         property="status",
 *         type="string",
 *         description="Текущий статус задачи (например, queued, processing, finished, failed)",
 *         enum = {"queued", "processing", "finished", "failed"},
 *         example="queued"
 *     ),
 *     @OA\Property(
 *         property="result",
 *         type="object",
 *         nullable = true,
 *         description="Результат выполнения задачи",
 *         example={"success":true,"processed_records":100}
 *     )
 * )
 */
class JobResource extends JsonResource
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
            'job_id'=>$this->job_id,
            'initial_data'=>$this->initial_data,
            'name_job'=>$this->name_job,
            'status'=>$this->status,
            'result'=>$this->result
        ];
    }
}
