<?php

namespace App\Services\ApiServices;

use App\DTO\DataFromIpGeolocation\CoordinatesFromIpGeolocationDTO;
use App\DTO\DataFromIpGeolocation\CurrencyFromIpGeolocationDTO;
use App\DTO\DataFromIpGeolocation\LanguageFromIpGeolocationDTO;
use App\DTO\DataFromIpGeolocation\TimezoneFromIpGeolocationDTO;
use App\Enums\TypeStatus;
use App\Repositories\ApiLimitRepositories\ApiLimitRepository;
use Carbon\Carbon;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

class IpGeolocationApiService
{
    public function getCurrencyTimezoneLanguageCoordinatesByOneRequest(string $ipAddress,bool $fromJob): array
    {
        $apiKey = config('services.ipgeolocation.key');
        try {
            $response = Http::get("https://api.ipgeolocation.io/v2/ipgeo", [
                'apiKey' => $apiKey,
                'ip' => $ipAddress,
            ]);
            if(!$fromJob)
            {
                $this->increaseCountRequestByDate();
            }
        } catch (ConnectionException) {
            return ["status"=>TypeStatus::error, "message"=>"Не удалось получить данные для определения валюты, часового пояса, языка и координат пользователя по ссылке: https://api.ipgeolocation.io/v2/ipgeo"];
        }
        $data = $response->json();
        $currencyData = data_get($data, 'currency');
        //currency
        $currencyDTO = null;
        if($currencyData !== null) {
            $currencyDTO= new CurrencyFromIpGeolocationDTO($currencyData['name'], $currencyData['code'], $currencyData['symbol']);
        }
        $languageDTO = new LanguageFromIpGeolocationDTO(data_get($data, 'country_metadata.languages'));
        $coordinatesDTO = new CoordinatesFromIpGeolocationDTO(data_get($data, 'location.latitude'), data_get($data, 'location.longitude'));

        try {
            $response = Http::get("https://api.ipgeolocation.io/v2/timezone", [
                'apiKey' => $apiKey,
                'ip' => $ipAddress,
            ]);
            if(!$fromJob)
            {
                $this->increaseCountRequestByDate();
            }
        } catch (ConnectionException) {
            return ["status"=>TypeStatus::error, "message"=>"Не удалось получить данные для определения временной зоны по ссылке: https://api.ipgeolocation.io/v2/ipgeo"];
        }
        $data = $response->json();
        $nameRegion = data_get($data, 'time_zone.name');
        $timezoneDTO = new TimezoneFromIpGeolocationDTO($nameRegion);
        return ["status"=>TypeStatus::success,
            "currencyDTO"=>$currencyDTO,
            "timezoneDTO"=>$timezoneDTO,
            "languageDTO"=>$languageDTO,
            "coordinatesDTO"=>$coordinatesDTO];
    }

    public function getCurrencyByIpAddress(string $ipAddress, bool $fromJob): array
    {
        $apiKey = config('services.ipgeolocation.key');
        try {
            $response = Http::get("https://api.ipgeolocation.io/v2/ipgeo", [
                'apiKey' => $apiKey,
                'ip' => $ipAddress,
                'fields' => 'currency'
            ]);
            if(!$fromJob)
            {
                $this->increaseCountRequestByDate();
            }
        } catch (ConnectionException) {
            return ["status"=>TypeStatus::error, "message"=>"Не удалось получить данные для определения валюты по ссылке: https://api.ipgeolocation.io/v2/ipgeo"];
        }
        $data = $response->json();
        $currencyData = data_get($data, 'currency');
        $currencyDTO = null;
        if($currencyData !== null) {
            $currencyDTO= new CurrencyFromIpGeolocationDTO($currencyData['name'], $currencyData['code'], $currencyData['symbol']);
        }
        return ["status"=>TypeStatus::success, "currencyDTO"=>$currencyDTO];
    }

    public function getLanguageByIpAddress(string $ipAddress, bool $fromJob): array
    {
        $apiKey = config('services.ipgeolocation.key');
        try {
            $response = Http::get("https://api.ipgeolocation.io/v2/ipgeo", [
                'apiKey' => $apiKey,
                'ip' => $ipAddress,
            ]);
            if(!$fromJob)
            {
                $this->increaseCountRequestByDate();
            }
        } catch (ConnectionException) {
            return ["status"=>TypeStatus::error, "message"=>"Не удалось получить данные для определения языка по ссылке: https://api.ipgeolocation.io/v2/ipgeo"];
        }
        $data = $response->json();
        $locales = data_get($data, 'country_metadata.languages');
        $languageDTO = new LanguageFromIpGeolocationDTO($locales);
        return ["status"=>TypeStatus::success, "languageDTO"=>$languageDTO];
    }

    public function getTimezoneByIpAddress(string $ipAddress, bool $fromJob): array
    {
        $apiKey = config('services.ipgeolocation.key');
        try {
            $response = Http::get("https://api.ipgeolocation.io/v2/timezone", [
                'apiKey' => $apiKey,
                'ip' => $ipAddress,
            ]);
            if(!$fromJob)
            {
                $this->increaseCountRequestByDate();
            }
        } catch (ConnectionException) {
            return ["status"=>TypeStatus::error, "message"=>"Не удалось получить данные для определения временной зоны по ссылке: https://api.ipgeolocation.io/v2/ipgeo"];
        }
        $data = $response->json();
        $nameRegion = data_get($data, 'time_zone.name');
        $timezoneDTO = new TimezoneFromIpGeolocationDTO($nameRegion);
        return ["status"=>TypeStatus::success, "timezoneDTO"=>$timezoneDTO];
    }

    public function getCoordinatesByIpAddress(string $ipAddress, bool $fromJob): array
    {
        $apiKey = config('services.ipgeolocation.key');
        try {
            $response = Http::get("https://api.ipgeolocation.io/v2/ipgeo", [
                'apiKey' => $apiKey,
                'ip' => $ipAddress,
            ]);
            if(!$fromJob)
            {
                $this->increaseCountRequestByDate();
            }
        } catch (ConnectionException) {
            return ["status"=>TypeStatus::error, "message"=>"Не удалось получить данные для определения координат пользователя по ссылке: https://api.ipgeolocation.io/v2/ipgeo"];
        }
        $data = $response->json();
        $latitude = data_get($data, 'location.latitude');
        $longitude = data_get($data, 'location.longitude');
        $coordinatesDTO = new CoordinatesFromIpGeolocationDTO($latitude, $longitude);
        return ["status"=>TypeStatus::success, "coordinatesDTO"=>$coordinatesDTO];
    }

    private function increaseCountRequestByDate(): void
    {
        $apiLimitRepository = app(ApiLimitRepository::class);
        $today = Carbon::today();
        $limit = $apiLimitRepository->findOrCreateByDate($today->toDateString());
        $apiLimitRepository->incrementRequestCount($limit);
    }
}
