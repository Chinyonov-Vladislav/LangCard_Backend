<?php

namespace App\Jobs;

use App\Enums\JobStatuses;
use App\Enums\TypeRequestApi;
use App\Enums\TypeStatus;
use App\Repositories\CurrencyRepositories\CurrencyRepositoryInterface;
use App\Repositories\LanguageRepositories\LanguageRepositoryInterface;
use App\Repositories\TimezoneRepositories\TimezoneRepositoryInterface;
use App\Repositories\UserRepositories\UserRepositoryInterface;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

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

    /**
     * Execute the job.
     */
    public function handle(CurrencyRepositoryInterface $currencyRepository,
                           TimezoneRepositoryInterface $timezoneRepository,
                           LanguageRepositoryInterface $languageRepository,
                           UserRepositoryInterface $userRepository): void
    {
        $this->updateJobStatus(JobStatuses::processing->value);
        if($this->typeRequest === TypeRequestApi::allRequests)
        {
            $data = $this->getCurrencyTimezoneLanguageCoordinatesByOneRequest($currencyRepository, $timezoneRepository, $languageRepository);
            if($data['status'] === TypeStatus::error)
            {
                $this->updateJobStatus(JobStatuses::failed->value, ["message"=>$data['message']]);
                return;
            }
            $userRepository->updateCurrencyIdByIdUser($this->userId, $data['currency_id']);
            $userRepository->updateTimezoneIdByIdUser($this->userId, $data['timezone_id']);
            $userRepository->updateLanguageIdByIdUser($this->userId, $data['language_id']);
            $userRepository->updateCoordinatesByIdUser($this->userId, $data['latitude'], $data['longitude']);
        }
        else if($this->typeRequest === TypeRequestApi::currencyRequest)
        {
            $data = $this->getCurrencyByIpAddress($currencyRepository);
            if($data['status'] === TypeStatus::error)
            {
                $this->updateJobStatus(JobStatuses::failed->value, ["message"=>$data['message']]);
                return;
            }
            $userRepository->updateCurrencyIdByIdUser($this->userId, $data['currency_id'] );
        }
        else if($this->typeRequest === TypeRequestApi::timezoneRequest)
        {
            $data = $this->getTimezoneByIpAddress($timezoneRepository);
            if($data['status'] === TypeStatus::error)
            {
                $this->updateJobStatus(JobStatuses::failed->value, ["message"=>$data['message']]);
                return;
            }
            $userRepository->updateTimezoneIdByIdUser($this->userId, $data['currency_id']);
        }
        else if($this->typeRequest === TypeRequestApi::languageRequest)
        {
            $data = $this->getLanguageByIpAddress($languageRepository);
            if($data['status'] === TypeStatus::error)
            {
                $this->updateJobStatus(JobStatuses::failed->value, ["message"=>$data['message']]);
                return;
            }
            $userRepository->updateLanguageIdByIdUser($this->userId, $data['language_id']);
        }
        else if($this->typeRequest === TypeRequestApi::coordinatesRequest)
        {
            $data = $this->getCoordinatesByIpAddress();
            if($data['status'] === TypeStatus::error)
            {
                $this->updateJobStatus(JobStatuses::failed->value, ["message"=>$data['message']]);
                return;
            }
            $userRepository->updateCoordinatesByIdUser($this->userId, $data['latitude'], $data['longitude']);
        }
        $this->updateJobStatus(JobStatuses::finished->value);
    }

    private function getCurrencyTimezoneLanguageCoordinatesByOneRequest(CurrencyRepositoryInterface $currencyRepository, TimezoneRepositoryInterface $timezoneRepository, LanguageRepositoryInterface $languageRepository): array
    {
        $apiKey = config('services.ipgeolocation.key');
        try {
            $response = Http::get("https://api.ipgeolocation.io/v2/ipgeo", [
                'apiKey' => $apiKey,
                'ip' => $this->ipAddress,
            ]);
        } catch (ConnectionException) {
            return ["status"=>TypeStatus::error, "message"=>"Не удалось получить данные для определения валюты, часового пояса, языка и координат пользователя по ссылке: https://api.ipgeolocation.io/v2/ipgeo"];
        }
        $data = $response->json();
        logger("Данные");
        logger($data);
        //валюта
        $currencyId = null;
        $currencyData = data_get($data, 'currency');
        if ($currencyData) {
            if(!$currencyRepository->isExistByCode($currencyData['code'])) {
                $currencyRepository->saveNewCurrency($currencyData['name'], $currencyData['code'], $currencyData['symbol']);
            }
            $currencyInfoFromDatabase = $currencyRepository->getByCode($currencyData['code']);
            $currencyId = $currencyInfoFromDatabase->id;
        }
        //часовой пояс
        $timezoneId = null;
        $nameRegion = data_get($data, 'time_zone.name');
        if ($nameRegion && $timezoneRepository->isExistTimezoneByNameRegion($nameRegion)) {
            $timezoneDB = $timezoneRepository->getTimezoneByNameRegion($nameRegion);
            $timezoneId = $timezoneDB->id;
        }
        //язык
        $languageId = null;
        $locales = data_get($data, 'country_metadata.languages');
        for($i = 0; $i < count($locales); $i++) {
            $infoLanguageByLocale = $languageRepository->getLanguageByLocale($locales[$i]);
            if($infoLanguageByLocale !== null) {
                $languageId = $infoLanguageByLocale->id;
                break;
            }
        }
        $latitude = data_get($data, 'location.latitude');
        $longitude = data_get($data, 'location.longitude');
        return ["status"=>TypeStatus::success, "currency_id"=>$currencyId,"timezone_id"=>$timezoneId,"language_id"=>$languageId, "latitude"=>$latitude,"longitude"=>$longitude];
    }

    private function getCurrencyByIpAddress(CurrencyRepositoryInterface $currencyRepository): array
    {
        $apiKey = config('services.ipgeolocation.key');
        try {
            $response = Http::get("https://api.ipgeolocation.io/v2/ipgeo", [
                'apiKey' => $apiKey,
                'ip' => $this->ipAddress,
                'fields' => 'currency'
            ]);
        } catch (ConnectionException) {
            return ["status"=>TypeStatus::error, "message"=>"Не удалось получить данные для определения валюты по ссылке: https://api.ipgeolocation.io/v2/ipgeo"];
        }
        $data = $response->json();
        $currencyId = null;
        $currencyData = data_get($data, 'currency');
        if ($currencyData) {
            if(!$currencyRepository->isExistByCode($currencyData['code'])) {
                $currencyRepository->saveNewCurrency($currencyData['name'], $currencyData['code'], $currencyData['symbol']);
            }
            $currencyInfoFromDatabase = $currencyRepository->getByCode($currencyData['code']);
            $currencyId = $currencyInfoFromDatabase->id;
        }
        return ["status"=>TypeStatus::success, "currency_id"=>$currencyId];
    }


    private function getLanguageByIpAddress(LanguageRepositoryInterface $languageRepository)
    {
        $apiKey = config('services.ipgeolocation.key');
        try {
            $response = Http::get("https://api.ipgeolocation.io/v2/ipgeo", [
                'apiKey' => $apiKey,
                'ip' => $this->ipAddress,
            ]);
        } catch (ConnectionException) {
            return ["status"=>TypeStatus::error, "message"=>"Не удалось получить данные для определения языка по ссылке: https://api.ipgeolocation.io/v2/ipgeo"];
        }
        $data = $response->json();
        $languageId = null;
        $locales = data_get($data, 'country_metadata.languages');
        for($i = 0; $i < count($locales); $i++) {
            $infoLanguageByLocale = $languageRepository->getLanguageByLocale($locales[$i]);
            if($infoLanguageByLocale !== null) {
                $languageId = $infoLanguageByLocale->id;
                break;
            }
        }
        return ["status"=>TypeStatus::success, "language_id"=>$languageId];
    }

    private function getTimezoneByIpAddress(TimezoneRepositoryInterface $timezoneRepository): array
    {
        $apiKey = config('services.ipgeolocation.key');
        try {
            $response = Http::get("https://api.ipgeolocation.io/v2/timezone", [
                'apiKey' => $apiKey,
                'ip' => $this->ipAddress,
            ]);
        } catch (ConnectionException) {
            return ["status"=>TypeStatus::error, "message"=>"Не удалось получить данные для определения временной зоны по ссылке: https://api.ipgeolocation.io/v2/ipgeo"];
        }
        $data = $response->json();
        $timezoneId = null;
        $nameRegion = data_get($data, 'time_zone.name');
        if ($nameRegion && $timezoneRepository->isExistTimezoneByNameRegion($nameRegion)) {
            $timezoneDB = $timezoneRepository->getTimezoneByNameRegion($nameRegion);
            $timezoneId = $timezoneDB->id;
        }
        return ["status"=>TypeStatus::success, "timezone_id"=>$timezoneId];
    }

    private function getCoordinatesByIpAddress(): array
    {
        $apiKey = config('services.ipgeolocation.key');
        try {
            $response = Http::get("https://api.ipgeolocation.io/v2/ipgeo", [
                'apiKey' => $apiKey,
                'ip' => $this->ipAddress,
            ]);
        } catch (ConnectionException) {
            return ["status"=>TypeStatus::error, "message"=>"Не удалось получить данные для определения координат пользователя по ссылке: https://api.ipgeolocation.io/v2/ipgeo"];
        }
        $data = $response->json();
        $latitude = data_get($data, 'location.latitude');
        $longitude = data_get($data, 'location.longitude');
        return ["status"=>TypeStatus::success, "latitude"=>$latitude,"longitude"=>$longitude];
    }
}
