<?php

namespace App\Services\ApiServices;

use App\Enums\JobStatuses;
use App\Enums\TypeRequestApi;
use App\Enums\TypeStatus;
use App\Jobs\ProcessDelayedApiRequest;
use App\Repositories\ApiLimitRepositories\ApiLimitRepositoryInterface;
use App\Repositories\CurrencyRepositories\CurrencyRepositoryInterface;
use App\Repositories\JobStatusRepositories\JobStatusRepositoryInterface;
use App\Repositories\TimezoneRepositories\TimezoneRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class ApiService
{
    protected CurrencyRepositoryInterface $currencyRepository;
    protected TimezoneRepositoryInterface $timezoneRepository;
    private ApiLimitRepositoryInterface $apiLimitRepository;

    private JobStatusRepositoryInterface $jobStatusRepository;
    private IpAddressService  $ipAddressService;
    private int $maxRequestsPerDay = 1000;

    public function __construct(ApiLimitRepositoryInterface $apiLimitRepository,
                                CurrencyRepositoryInterface $currencyRepository,
                                TimezoneRepositoryInterface $timezoneRepository,
                                JobStatusRepositoryInterface $jobStatusRepository)
    {
        $this->apiLimitRepository = $apiLimitRepository;
        $this->currencyRepository = $currencyRepository;
        $this->timezoneRepository = $timezoneRepository;
        $this->jobStatusRepository = $jobStatusRepository;
        $this->ipAddressService = new IpAddressService();
    }

    public function makeRequest(string $ipAddress,int $userId, TypeRequestApi $type): array
    {
        $ipAddress = $this->ipAddressService->getIpAddress($ipAddress);
        $today = Carbon::today()->toDateString();
        $limit = $this->apiLimitRepository->findOrCreateByDate($today);
        if ($limit->request_count >= $this->maxRequestsPerDay) {
            // Лимит исчерпан – ставим в очередь на завтра
            $jobId = (string)Str::uuid();
            $this->jobStatusRepository->saveNewJobStatus($jobId, "ProcessDelayedApiRequest", JobStatuses::queued->value, $userId);
            ProcessDelayedApiRequest::dispatch($jobId, $ipAddress,$userId, $type)
                ->delay(now()->addDay()->startOfDay());
            return ['status'=>TypeStatus::error->value, "job_id" => $jobId];
        }
        if($type === TypeRequestApi::currencyRequest)
        {
            $currencyId = $this->getCurrencyByIpAddress($ipAddress);
            if($currencyId !== null)
            {
                $this->apiLimitRepository->incrementRequestCount($limit);
            }
            return ['status'=>TypeStatus::success->value, "id" => $currencyId];
        }
        $timezoneId = $this->getTimezoneByIpAddress($ipAddress);
        if($timezoneId !== null)
        {
            $this->apiLimitRepository->incrementRequestCount($limit);
        }
        return ['status'=>TypeStatus::success->value, "id" => $timezoneId];
    }
    private function getCurrencyByIpAddress(string $ipAddress)
    {
        $apiKey = config('services.ipgeolocation.key');
        $response = Http::get("https://api.ipgeolocation.io/v2/ipgeo", [
            'apiKey' => $apiKey,
            'ip' => $ipAddress,
            'fields'=>'currency'
        ]);
        $data = $response->json();
        $currencyId = null;
        if (isset($data['currency'])) {
            if(!$this->currencyRepository->isExistByCode($data['currency']['code'])) {
                $this->currencyRepository->saveNewCurrency($data['currency']['name'], $data['currency']['code'], $data['currency']['symbol']);
            }
            $currencyInfoFromDatabase = $this->currencyRepository->getByCode($data['currency']['code']);
            $currencyId = $currencyInfoFromDatabase->id;
        }
        return $currencyId;
    }
    private function getTimezoneByIpAddress(string $ipAddress)
    {
        $apiKey = config('services.ipgeolocation.key');
        $response = Http::get("https://api.ipgeolocation.io/v2/timezone", [
            'apiKey' => $apiKey,
            'ip' => $ipAddress,
        ]);
        $data = $response->json();
        $timezoneId = null;
        $nameRegion = data_get($data, 'time_zone.name');
        if (isset($nameRegion)) {
            if ($this->timezoneRepository->isExistTimezoneByNameRegion($nameRegion)) {
                $timezoneDB = $this->timezoneRepository->getTimezoneByNameRegion($nameRegion);
                $timezoneId = $timezoneDB->id;
            }
        }
        return $timezoneId;
    }
}
