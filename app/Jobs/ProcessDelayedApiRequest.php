<?php

namespace App\Jobs;

use App\Enums\JobStatuses;
use App\Enums\TypeRequestApi;
use App\Enums\TypeStatus;
use App\Repositories\UserRepositories\UserRepositoryInterface;
use App\Services\ApiServices\ApiService;;

class ProcessDelayedApiRequest extends BaseJob
{
    protected string $ipAddress;
    protected TypeRequestApi $typeRequest;
    protected int $userId;
    protected UserRepositoryInterface $userRepository;
    /**
     * Create a new job instance.
     */
    public function __construct(?string $job_id, string $ipAddress,int $userId, TypeRequestApi $typeRequest, UserRepositoryInterface $userRepository)
    {
        parent::__construct($job_id);
        $this->ipAddress = $ipAddress;
        $this->userId = $userId;
        $this->typeRequest = $typeRequest;
        $this->userRepository = $userRepository;
    }

    /**
     * Execute the job.
     */
    public function handle(ApiService $apiService): void
    {
        $this->updateJobStatus(JobStatuses::processing->value);
        $info = $apiService->makeRequest($this->ipAddress, $this->userId, $this->typeRequest);
        if($info['status'] === TypeStatus::error->value)
        {
            if($this->typeRequest === TypeRequestApi::currencyRequest) {
                $this->updateJobStatus(JobStatuses::failed->value, ["message" =>"Не удалось установить валюту пользователя. Будет выполнена повторная попытка","job_id"=>$info['job_id']]);
            }
            else if($this->typeRequest === TypeRequestApi::timezoneRequest) {
                $this->updateJobStatus(JobStatuses::failed->value, ["message" =>"Не удалось установить временной пояс пользователя. Будет выполнена повторная попытка", "job_id"=>$info['job_id']]);
            }
            else
            {
                $this->updateJobStatus(JobStatuses::failed->value);
            }
            return;
        }
        if($this->typeRequest === TypeRequestApi::currencyRequest)
        {
            $this->userRepository->updateCurrencyIdByIdUser($this->userId, $info['id']);
        }
        if($this->typeRequest == TypeRequestApi::timezoneRequest)
        {
            $this->userRepository->updateTimezoneIdByIdUser($this->userId, $info['id']);
        }
        $this->updateJobStatus(JobStatuses::finished->value);
    }
}
