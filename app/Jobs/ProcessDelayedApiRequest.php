<?php

namespace App\Jobs;

use App\Enums\JobStatuses;
use App\Enums\TypeRequestApi;
use App\Enums\TypeStatus;
use App\Repositories\CurrencyRepositories\CurrencyRepositoryInterface;
use App\Repositories\LanguageRepositories\LanguageRepositoryInterface;
use App\Repositories\TimezoneRepositories\TimezoneRepositoryInterface;
use App\Repositories\UserRepositories\UserRepositoryInterface;
use App\Services\ApiServices\IpGeolocationApiService;

class ProcessDelayedApiRequest extends BaseJob
{
    protected string $ipAddress;
    protected TypeRequestApi $typeRequest;
    protected int $userId;
    /**
     * Create a new job instance.
     */
    public function __construct(?string $job_id, string $ipAddress,int $userId, TypeRequestApi $typeRequest)
    {
        parent::__construct($job_id);
        $this->ipAddress = $ipAddress;
        $this->userId = $userId;
        $this->typeRequest = $typeRequest;
    }
    protected function execute(...$args): void
    {
        $ipGeolocationApiService = new IpGeolocationApiService();
        $currencyRepository = app(CurrencyRepositoryInterface::class);
        $timezoneRepository = app(TimezoneRepositoryInterface::class);
        $languageRepository = app(LanguageRepositoryInterface::class);
        $userRepository = app(UserRepositoryInterface::class);
        $this->updateJobStatus(JobStatuses::processing->value);
        if($this->typeRequest === TypeRequestApi::allRequests)
        {
            $data = $ipGeolocationApiService->getCurrencyTimezoneLanguageCoordinatesByOneRequest($this->ipAddress, true);
            if($data['status'] === TypeStatus::error)
            {
                $this->updateJobStatus(JobStatuses::failed->value, ["message"=>$data['message']]);
                return;
            }
            $currencyDataDTO = $data["currencyDTO"];
            $currencyId = $currencyRepository->getCurrencyIdByDataFromApi($currencyDataDTO);
            $userRepository->updateCurrencyIdByIdUser($this->userId, $currencyId);
            /*$timezoneDTO = $data["timezoneDTO"];
            $timezoneId = $timezoneRepository->getTimezoneIdByDataFromApi($timezoneDTO);
            $userRepository->updateTimezoneIdByIdUser($this->userId, $timezoneId);*/
            $languageDTO = $data["languageDTO"];
            $languageId = $languageRepository->getLanguageIdByDataFromApi($languageDTO);
            $userRepository->updateLanguageIdByIdUser($this->userId, $languageId);
            $coordinateDTO = $data["coordinatesDTO"];
            $userRepository->updateCoordinatesByIdUser($this->userId, $coordinateDTO);
        }
        else if($this->typeRequest === TypeRequestApi::currencyRequest)
        {
            $data = $ipGeolocationApiService->getCurrencyByIpAddress($this->ipAddress, true);
            if($data['status'] === TypeStatus::error)
            {
                $this->updateJobStatus(JobStatuses::failed->value, ["message"=>$data['message']]);
                return;
            }
            $currencyDataDTO = $data["currencyDTO"];
            $currencyId = $currencyRepository->getCurrencyIdByDataFromApi($currencyDataDTO);
            $userRepository->updateCurrencyIdByIdUser($this->userId, $currencyId);
        }
        else if($this->typeRequest === TypeRequestApi::timezoneRequest)
        {
            $data = $ipGeolocationApiService->getTimezoneByIpAddress($this->ipAddress, true);
            if($data['status'] === TypeStatus::error)
            {
                $this->updateJobStatus(JobStatuses::failed->value, ["message"=>$data['message']]);
                return;
            }
            $timezoneDTO = $data["timezoneDTO"];
            $timezoneId = $timezoneRepository->getTimezoneIdByDataFromApi($timezoneDTO);
            $userRepository->updateTimezoneIdByIdUser($this->userId, $timezoneId);
        }
        else if($this->typeRequest === TypeRequestApi::languageRequest)
        {
            $data = $ipGeolocationApiService->getLanguageByIpAddress($this->ipAddress, true);
            if($data['status'] === TypeStatus::error)
            {
                $this->updateJobStatus(JobStatuses::failed->value, ["message"=>$data['message']]);
                return;
            }
            $languageDTO = $data["languageDTO"];
            $languageId = $languageRepository->getLanguageIdByDataFromApi($languageDTO);
            $userRepository->updateLanguageIdByIdUser($this->userId, $languageId);
        }
        else if($this->typeRequest === TypeRequestApi::coordinatesRequest)
        {
            $data = $ipGeolocationApiService->getCoordinatesByIpAddress($this->ipAddress, true);
            if($data['status'] === TypeStatus::error)
            {
                $this->updateJobStatus(JobStatuses::failed->value, ["message"=>$data['message']]);
                return;
            }
            $coordinateDTO = $data["coordinatesDTO"];
            $userRepository->updateCoordinatesByIdUser($this->userId, $coordinateDTO);
        }
        $this->updateJobStatus(JobStatuses::finished->value);
    }
}
