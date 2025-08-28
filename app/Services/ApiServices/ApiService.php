<?php

namespace App\Services\ApiServices;

use App\Enums\JobStatuses;
use App\Enums\NameJobsEnum;
use App\Enums\TypeRequestApi;
use App\Enums\TypeStatus;
use App\Enums\TypeStatusRequestApi;
use App\Jobs\ProcessDelayedApiRequest;
use App\Repositories\ApiLimitRepositories\ApiLimitRepositoryInterface;
use App\Repositories\CurrencyRepositories\CurrencyRepositoryInterface;
use App\Repositories\JobStatusRepositories\JobStatusRepositoryInterface;
use App\Repositories\LanguageRepositories\LanguageRepositoryInterface;
use App\Repositories\TimezoneRepositories\TimezoneRepositoryInterface;
use App\Repositories\UserRepositories\UserRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Support\Str;

class ApiService
{
    protected CurrencyRepositoryInterface $currencyRepository;
    protected TimezoneRepositoryInterface $timezoneRepository;

    protected LanguageRepositoryInterface $languageRepository;

    private ApiLimitRepositoryInterface $apiLimitRepository;

    private JobStatusRepositoryInterface $jobStatusRepository;
    private IpAddressService $ipAddressService;

    private UserRepositoryInterface $userRepository;
    private int $maxRequestsPerDay;

    public function __construct(ApiLimitRepositoryInterface  $apiLimitRepository,
                                CurrencyRepositoryInterface  $currencyRepository,
                                TimezoneRepositoryInterface  $timezoneRepository,
                                JobStatusRepositoryInterface $jobStatusRepository,
                                LanguageRepositoryInterface  $languageRepository,
                                UserRepositoryInterface      $userRepository)
    {
        $this->apiLimitRepository = $apiLimitRepository;
        $this->currencyRepository = $currencyRepository;
        $this->timezoneRepository = $timezoneRepository;
        $this->jobStatusRepository = $jobStatusRepository;
        $this->languageRepository = $languageRepository;
        $this->userRepository = $userRepository;
        $this->ipAddressService = new IpAddressService();
        $this->maxRequestsPerDay = config('app.limit_count_request_on_day_to_ip_geolocation');
    }

    public function makeRequest(string $ipAddress, int $userId, TypeRequestApi $type): array
    {
        $ipGeolocationApiService = new IpGeolocationApiService();
        $jobId = (string) Str::uuid();
        $ipAddress = $this->ipAddressService->getIpAddress($ipAddress);
        $today = Carbon::today();
        $limit = $this->apiLimitRepository->findOrCreateByDate($today->toDateString());
        if ($type === TypeRequestApi::allRequests) {
            if ($this->maxRequestsPerDay - $limit->request_count < 2) {
                return $this->queueDelayedJob($jobId, $ipAddress, $userId, $type, 2);
            }
            return $this->processAllRequests($ipGeolocationApiService, $ipAddress, $userId);
        }
        if ($limit->request_count >= $this->maxRequestsPerDay) {
            return $this->queueDelayedJob($jobId, $ipAddress, $userId, $type);
        }

        return $this->processSingleRequest($ipGeolocationApiService, $ipAddress, $userId, $type);
    }

    private function queueDelayedJob(string $jobId, string $ipAddress, int $userId, TypeRequestApi $type, int $minFreeSlots = 1): array
    {
        $countNewDays = 1;
        while (true) {
            $futureDate = Carbon::today()->addDays($countNewDays);
            $limitInFutureDate = $this->apiLimitRepository->findOrCreateByDate($futureDate->toDateString());

            if ($this->maxRequestsPerDay - $limitInFutureDate->request_count < $minFreeSlots) {
                $countNewDays++;
                continue;
            }

            $this->jobStatusRepository->saveNewJobStatus(
                $jobId,
                NameJobsEnum::ProcessDelayedApiRequest->value,
                JobStatuses::queued->value,
                $userId,
                ['type' => $type->value, 'execution_date' => $futureDate]
            );

            // увеличиваем счётчик на нужное количество
            for ($i = 0; $i < $minFreeSlots; $i++) {
                $this->apiLimitRepository->incrementRequestCount($limitInFutureDate);
            }

            ProcessDelayedApiRequest::dispatch($jobId, $ipAddress, $userId, $type)
                ->delay($futureDate->startOfDay());

            return ["status" => TypeStatusRequestApi::delayed->value, "job_id" => $jobId];
        }
    }

    private function processAllRequests(IpGeolocationApiService $service, string $ipAddress, int $userId): array
    {
        $data = $service->getCurrencyTimezoneLanguageCoordinatesByOneRequest($ipAddress, false);
        if ($data['status'] === TypeStatus::error) {
            return ["status" => TypeStatusRequestApi::error->value, "message" => $data['message']];
        }

        $currencyId  = $this->currencyRepository->getCurrencyIdByDataFromApi($data["currencyDTO"]);
        $timezoneId  = $this->timezoneRepository->getTimezoneIdByDataFromApi($data["timezoneDTO"]);
        $languageId  = $this->languageRepository->getLanguageIdByDataFromApi($data["languageDTO"]);
        $coordinates = $data["coordinatesDTO"];

        $this->userRepository->updateCurrencyIdByIdUser($userId, $currencyId);
        $this->userRepository->updateTimezoneIdByIdUser($userId, $timezoneId);
        $this->userRepository->updateLanguageIdByIdUser($userId, $languageId);
        $this->userRepository->updateCoordinatesByIdUser($userId, $coordinates);

        return ["status" => TypeStatusRequestApi::success->value];
    }

    private function processSingleRequest(IpGeolocationApiService $service, $ipAddress, $userId, TypeRequestApi $type)
    {
        $map = [
            TypeRequestApi::currencyRequest->value => [
                'method' => 'getCurrencyByIpAddress',
                'dto'    => 'currencyDTO',
                'repo'   => $this->currencyRepository,
                'repoMethod' => 'getCurrencyIdByDataFromApi',
                'update' => fn($id) => $this->userRepository->updateCurrencyIdByIdUser($userId, $id),
            ],
            TypeRequestApi::timezoneRequest->value => [
                'method' => 'getTimezoneByIpAddress',
                'dto'    => 'timezoneDTO',
                'repo'   => $this->timezoneRepository,
                'repoMethod' => 'getTimezoneIdByDataFromApi',
                'update' => fn($id) => $this->userRepository->updateTimezoneIdByIdUser($userId, $id),
            ],
            TypeRequestApi::languageRequest->value => [
                'method' => 'getLanguageByIpAddress',
                'dto'    => 'languageDTO',
                'repo'   => $this->languageRepository,
                'repoMethod' => 'getLanguageIdByDataFromApi',
                'update' => fn($id) => $this->userRepository->updateLanguageIdByIdUser($userId, $id),
            ],
            TypeRequestApi::coordinatesRequest->value => [
                'method' => 'getCoordinatesByIpAddress',
                'dto'    => 'coordinatesDTO',
                'repo'   => null,
                'repoMethod' => null,
                'update' => fn($dto) => $this->userRepository->updateCoordinatesByIdUser($userId, $dto),
            ],
        ];

        if (!isset($map[$type->value])) {
            return [
                "status"  => TypeStatusRequestApi::error->value,
                "message" => "Неизвестный тип запроса к API IpGeolocation"
            ];
        }

        $config = $map[$type->value];
        $data = $service->{$config['method']}($ipAddress, false);

        if ($data['status'] === TypeStatus::error) {
            return ["status" => TypeStatusRequestApi::error->value, "message" => $data['message']];
        }

        if ($config['repo']) {
            $id = $config['repo']->{$config['repoMethod']}($data[$config['dto']]);
            $config['update']($id);
        } else {
            $config['update']($data[$config['dto']]);
        }

        return ["status" => TypeStatusRequestApi::success->value];
    }
}
