<?php

namespace App\Repositories\TariffRepositories;

use App\Models\Tariff;
use Illuminate\Database\Eloquent\Collection;

class TariffRepository implements TariffRepositoryInterface
{
    protected Tariff $model;

    public function __construct(Tariff $model)
    {
        $this->model = $model;
    }


    public function saveNewTariff(string $name, int $days, bool $statusActive = true): ?Tariff
    {
        $newTariff = new Tariff();
        $newTariff->name = $name;
        $newTariff->days = $days;
        $newTariff->is_active = $statusActive;
        if ($newTariff->save()) {
            return $newTariff; // Успешно создана модель
        }
        return null; // Ошибка при сохранении
    }

    public function isExistTariff(string $name, int $days)
    {
        return $this->model->where('name', '=', $name)->where('days', '=', $days)->exists();
    }

    public function getAllIdTariffs()
    {
        return $this->model->pluck('id')->toArray();
    }

    public function getActiveTariffsForUserCurrency(int $userCurrencyId): Collection
    {
        return $this->model->where('is_active', true)
            ->whereHas('costs', function ($query) use ($userCurrencyId) {
                $query->where('is_active', true)
                    ->whereHas('currency', function ($q) use ($userCurrencyId) {
                        $q->where('id', $userCurrencyId);
                    });
            })
            ->with(['costs' => function ($query) use ($userCurrencyId) {
                $query->where('is_active', true)
                    ->whereHas('currency', function ($q) use ($userCurrencyId) {
                        $q->where('id', $userCurrencyId);
                    })
                    ->with(['currency' => function ($q) use ($userCurrencyId) {
                        $q->where('id', $userCurrencyId);
                    }]);
            }])
            ->get();
    }

    public function getAllActiveTariffs()
    {
        return $this->model
            ->where('is_active', '=', true)
            ->whereHas('costs', function ($query) {
                $query->where('is_active', true);
            })
            ->with(['costs' => function ($query)
            {
                $query->where('is_active', true)->with(['currency']);
            }])
            ->get();
    }

    public function isExistTariffById(int $tariffId)
    {
        return $this->model->where('id', '=', $tariffId)->exists();
    }

    public function changeStatus($tariffId): void
    {
        $currentTariff = $this->model->where('id', '=', $tariffId)->first();
        $currentTariff->is_active = !$currentTariff->is_active;
        $currentTariff->save();
    }

    public function getAllIdActiveTariffs()
    {
        return $this->model->where('is_active','=', true)->pluck('id')->toArray();
    }
}
