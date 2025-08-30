<?php

namespace App\Repositories\LanguageRepositories;

use App\DTO\DataFromIpGeolocation\LanguageFromIpGeolocationDTO;
use App\Models\Language;
use Illuminate\Database\Eloquent\Collection;

class LanguageRepository implements LanguageRepositoryInterface
{
    protected Language $model;

    public function __construct(Language $model)
    {
        $this->model = $model;
    }

    public function getAllLanguagesName(): array
    {
        return $this->model->select(['id','name'])->get()->toArray();
    }

    public function isExistLanguageByLocale(string $languageLocale): bool
    {
        return $this->model->where('locale','=', $languageLocale)->exists();
    }

    public function saveLanguage(string $languageName,string $native_name, string $languageCode,string $locale, string $urlToImage): Language
    {
        $newLanguage = new Language();
        $newLanguage->name = $languageName;
        $newLanguage->native_name = $native_name;
        $newLanguage->code = $languageCode;
        $newLanguage->locale = $locale;
        $newLanguage->flag_url = $urlToImage;
        $newLanguage->save();
        return $newLanguage;
    }

    public function getAllIdLanguages(): array
    {
        return $this->model->select(['id'])->get()->toArray();
    }

    public function isExistLanguageById(int $languageId): bool
    {
        return $this->model->where('id', '=', $languageId)->exists();
    }

    public function getAllLanguages(): Collection
    {
        return $this->model->get();
    }
    public function getExistentLocale(array $locales): array
    {
        return Language::whereIn('locale', $locales)
            ->pluck('locale')
            ->toArray();
    }

    public function getLanguageByLocale(string $locale): ?Language
    {
        return $this->model->where('locale', '=', $locale)->orWhere("code", "=", $locale)->first();
    }

    public function getLanguageIdByDataFromApi(LanguageFromIpGeolocationDTO $languageFromIpGeolocationDTO): ?int
    {
        if($languageFromIpGeolocationDTO->getLocales() === null)
        {
            return null;
        }
        $locales = $languageFromIpGeolocationDTO->getLocales();
        for($i = 0; $i < count($locales); $i++) {
            $infoLanguageByLocale = $this->getLanguageByLocale($locales[$i]);
            if($infoLanguageByLocale !== null) {
                return $infoLanguageByLocale->id;
            }
        }
        return null;
    }
}
