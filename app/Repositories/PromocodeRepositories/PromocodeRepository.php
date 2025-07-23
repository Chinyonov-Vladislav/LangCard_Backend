<?php

namespace App\Repositories\PromocodeRepositories;

use App\Models\Promocode;
use Illuminate\Database\Eloquent\Collection;

class PromocodeRepository implements PromocodeRepositoryInterface
{
    protected Promocode $model;

    public function __construct(Promocode $model)
    {
        $this->model = $model;
    }

    public function isExistPromocode(string $code): bool
    {
        return $this->model->where('code','=', $code)->exists();
    }

    public function saveNewPromocode(string $code, int $tariff_id): void
    {
        $newPromocode = new Promocode();
        $newPromocode->code = $code;
        $newPromocode->tariff_id = $tariff_id;
        $newPromocode->save();
    }

    public function getPromocodeByCode(string $code): ?Promocode
    {
        return $this->model->with(['tariff'])->where('code','=', $code)->first();
    }

    public function deactivatePromocodeByCode(string $code)
    {
        $this->model->where('code', $code)->update([
            'active' => false
        ]);
    }

    public function deactivatePromocodeByPromocode(Promocode $promocode)
    {
        $promocode->active = false;
        $promocode->save();
    }

    public function getActivePromocodesById(?int $tariff_id): Collection
    {
        $query = $this->model->with(['tariff' => function ($query) {
            $query->select(['id', 'name', 'days']);
        }])
            ->where('active', true)
            ->select(['code', 'tariff_id']);

        if ($tariff_id !== null) {
            $query->whereHas('tariff', function ($query) use ($tariff_id) {
                $query->where('id', $tariff_id);
            });
        }

        return $query->get();
    }
}
