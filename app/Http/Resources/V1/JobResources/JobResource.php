<?php

namespace App\Http\Resources\V1\JobResources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

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
