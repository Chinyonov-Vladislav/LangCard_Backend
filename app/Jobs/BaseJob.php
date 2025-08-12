<?php

namespace App\Jobs;

use App\Repositories\JobStatusRepositories\JobStatusRepository;
use App\Repositories\JobStatusRepositories\JobStatusRepositoryInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

abstract class BaseJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected ?string $jobId;

    public function __construct(?string $jobId = null)
    {
        $this->jobId = $jobId;
    }

    /**
     * Установить статус задачи.
     */
    protected function updateJobStatus(string $status, ?array $result = null): void
    {
        if($this->jobId !== null) {
            $jobStatusRepository = app(JobStatusRepositoryInterface::class);
            $jobStatusRepository->updateStatus($this->jobId, $status, $result);
        }
    }
}
