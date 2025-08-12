<?php

namespace App\Repositories\JobStatusRepositories;

use App\Models\JobStatus;
use App\Services\PaginatorService;

interface JobStatusRepositoryInterface
{
    public function getJobsOfUserWithPagination(PaginatorService $paginator, int $userId, int $countOnPage, int $numberCurrentPage);

    public function getJobStatusById(int $id): ?JobStatus;

    public function getJobStatusByJobId(int $jobId): ?JobStatus;

    public function updateStatus(string $job_id, string $status, ?array $result = null);
    public function saveNewJobStatus(string $job_id, string $name_job, string $status,int $userId, ?array $initial_data = null, ?array $result = null);
}
