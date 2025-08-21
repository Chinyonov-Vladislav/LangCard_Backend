<?php

namespace App\Services\ApiServices;

use App\Enums\JobStatuses;
use App\Enums\TypeRequestApi;
use App\Enums\TypeStatus;
use App\Jobs\ProcessDelayedApiRequest;
use App\Repositories\ApiLimitRepositories\ApiLimitRepositoryInterface;
use App\Repositories\CurrencyRepositories\CurrencyRepositoryInterface;
use App\Repositories\JobStatusRepositories\JobStatusRepositoryInterface;
use App\Repositories\LanguageRepositories\LanguageRepositoryInterface;
use App\Repositories\TimezoneRepositories\TimezoneRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Support\Str;

class ApiService
{
    protected CurrencyRepositoryInterface $currencyRepository;
    protected TimezoneRepositoryInterface $timezoneRepository;

    protected LanguageRepositoryInterface $languageRepository;

    private ApiLimitRepositoryInterface $apiLimitRepository;

    private JobStatusRepositoryInterface $jobStatusRepository;
    private IpAddressService  $ipAddressService;
    private int $maxRequestsPerDay = 1000;

    public function __construct(ApiLimitRepositoryInterface $apiLimitRepository,
                                CurrencyRepositoryInterface $currencyRepository,
                                TimezoneRepositoryInterface $timezoneRepository,
                                JobStatusRepositoryInterface $jobStatusRepository,
                                LanguageRepositoryInterface $languageRepository,)
    {
        $this->apiLimitRepository = $apiLimitRepository;
        $this->currencyRepository = $currencyRepository;
        $this->timezoneRepository = $timezoneRepository;
        $this->jobStatusRepository = $jobStatusRepository;
        $this->languageRepository = $languageRepository;
        $this->ipAddressService = new IpAddressService();
    }

    public function makeRequest(string $ipAddress,int $userId, TypeRequestApi $type): string
    {
        $jobId = (string)Str::uuid();
        $ipAddress = $this->ipAddressService->getIpAddress($ipAddress);
        $today = Carbon::today();
        $limit = $this->apiLimitRepository->findOrCreateByDate($today->toDateString());
        if ($limit->request_count >= $this->maxRequestsPerDay)
        {
            $countNewDays = 1;
            // Лимит исчерпан – ставим в очередь на подходящий день
            while(true)
            {
                $futureDate = Carbon::today()->addDays($countNewDays);
                $limitInFutureDate = $this->apiLimitRepository->findOrCreateByDate($futureDate->toDateString());
                if($limitInFutureDate->request_count >= $this->maxRequestsPerDay)
                {
                    $countNewDays++;
                    continue;
                }
                $this->jobStatusRepository->saveNewJobStatus($jobId, "ProcessDelayedApiRequest", JobStatuses::queued->value, $userId, ['type'=>$type, 'execution_date'=>$futureDate]);
                $this->apiLimitRepository->incrementRequestCount($limitInFutureDate);
                ProcessDelayedApiRequest::dispatch($jobId, $ipAddress,$userId, $type)
                    ->delay($futureDate->startOfDay());
                return $jobId;
            }
        }
        $this->jobStatusRepository->saveNewJobStatus($jobId, "ProcessDelayedApiRequest", JobStatuses::queued->value, $userId, ['type'=>$type, 'execution_date'=>$today]);
        ProcessDelayedApiRequest::dispatch($jobId, $ipAddress,$userId, $type);
        $this->apiLimitRepository->incrementRequestCount($limit);
        return $jobId;
    }
}
