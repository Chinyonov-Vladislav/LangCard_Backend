<?php

namespace App\Repositories\JobStatusRepositories;

use App\Enums\JobStatuses;
use App\Enums\NameJobsEnum;
use App\Enums\TypeRequestApi;
use App\Models\JobStatus;
use App\Services\PaginatorService;

class JobStatusRepository implements JobStatusRepositoryInterface
{
    protected JobStatus $model;

    public function __construct(JobStatus $model)
    {
        $this->model = $model;
    }



    public function saveNewJobStatus(string $job_id, string $name_job, string $status,int $userId, ?array $initial_data = null, ?array $result = null): void
    {
        logger("initial_data");
        logger($initial_data);
        $newJobStatus = new JobStatus();
        $newJobStatus->job_id = $job_id;
        $newJobStatus->initial_data = $initial_data;
        $newJobStatus->name_job = $name_job;
        $newJobStatus->status = $status;
        $newJobStatus->result = $result;
        $newJobStatus->user_id = $userId;
        $newJobStatus->save();
    }

    public function updateStatus(string $job_id, string $status, ?array $result = null): void
    {
        if($result === null){
            $this->model->where('job_id', $job_id)->update(['status' => $status]);
            return;
        }
        $this->model->where('job_id', $job_id)->update(['status' => $status, 'result' => json_encode($result)]);
    }

    public function getJobStatusById(int $id): ?JobStatus
    {
        return $this->model->where('id', '=', $id)->first();
    }

    public function getJobStatusByJobId(int $jobId): ?JobStatus
    {
        return $this->model->where('job_id', '=', $jobId)->first();
    }

    public function getJobsOfUserWithPagination(PaginatorService $paginator, int $userId, int $countOnPage, int $numberCurrentPage): array
    {
        $query =  $this->model->where('user_id', '=', $userId)->orderBy('updated_at', 'desc');
        $data = $paginator->paginate($query, $countOnPage, $numberCurrentPage);
        $metadataPagination = $paginator->getMetadataForPagination($data);
        return ['items' => collect($data->items()), "pagination" => $metadataPagination];
    }

    public function isExistJobWithStatusQueuedOrProcessingForAutomaticDefineUserData(int $userId, TypeRequestApi $type): bool
    {
        return $this->model->where("user_id", "=", $userId)
            ->where("name_job", "=", NameJobsEnum::ProcessDelayedApiRequest->value)
            ->where('initial_data->type',"=", $type->value)
            ->whereIn("status", [JobStatuses::queued->value, JobStatuses::processing->value])
            ->exists();
    }

    public function getJobForNews(int $newsId): ?JobStatus
    {
        return $this->model->where("name_job", "=", NameJobsEnum::SendNewsMailJob->value)->where("initial_data->news_id","=",$newsId)->first();
    }

    public function getJobWithStatusQueuedOrProcessingForAutomaticDefineUserData(int $userId, TypeRequestApi $type): ?JobStatus
    {
        return $this->model->where("user_id", "=", $userId)
            ->where("name_job", "=", NameJobsEnum::ProcessDelayedApiRequest->value)
            ->where('initial_data->type',"=", $type->value)
            ->whereIn("status", [JobStatuses::queued->value, JobStatuses::processing->value])
            ->first();
    }
}
