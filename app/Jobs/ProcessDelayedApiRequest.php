<?php

namespace App\Jobs;

use App\Enums\TypeRequestApi;
use App\Repositories\UserRepositories\UserRepositoryInterface;
use App\Services\ApiServices\ApiService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessDelayedApiRequest implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected string $ipAddress;
    protected TypeRequestApi $typeRequest;
    protected int $userId;
    protected UserRepositoryInterface $userRepository;
    /**
     * Create a new job instance.
     */
    public function __construct(string $ipAddress,int $userId, TypeRequestApi $typeRequest, UserRepositoryInterface $userRepository)
    {
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
        $id = $apiService->makeRequest($this->ipAddress, $this->userId, $this->typeRequest);
        if($id === null)
        {
            return;
        }
        if($this->typeRequest === TypeRequestApi::currencyRequest)
        {
            $this->userRepository->updateCurrencyIdByIdUser($this->userId, $id);
        }
        if($this->typeRequest == TypeRequestApi::timezoneRequest)
        {
            $this->userRepository->updateTimezoneIdByIdUser($this->userId, $id);
        }
    }
}
