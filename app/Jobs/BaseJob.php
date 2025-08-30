<?php

namespace App\Jobs;

use App\Enums\JobStatuses;
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

    public function handle(...$args): void
    {
        $jobStatusRepository = app(JobStatusRepositoryInterface::class);
        // Проверка отмены
        $job = $jobStatusRepository->getJobStatusByJobId($this->jobId);
        if($job->status === JobStatuses::cancelled->value)
        {
            return;
        }
        // Если всё нормально – вызвать реальную логику
        $this->execute($jobStatusRepository);
    }
    /**
     * Бизнес-логика конкретной задачи.
     * Любые аргументы передаются через ...$args
     */
    abstract protected function execute(...$args): void;
}
