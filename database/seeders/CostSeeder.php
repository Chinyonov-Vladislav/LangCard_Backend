<?php

namespace Database\Seeders;

use App\Repositories\CostRepositories\CostRepositoryInterface;
use App\Repositories\CurrencyRepositories\CurrencyRepositoryInterface;
use App\Repositories\TariffRepositories\TariffRepositoryInterface;
use Illuminate\Database\Seeder;

class CostSeeder extends Seeder
{
    protected CurrencyRepositoryInterface $currencyRepository;
    protected TariffRepositoryInterface $tariffRepository;
    protected CostRepositoryInterface $costRepository;
    protected const MIN_COST = 1000;
    protected const MAX_COST = 10000;

    public function __construct(CurrencyRepositoryInterface $currencyRepository,
                                TariffRepositoryInterface $tariffRepository,
                                CostRepositoryInterface $costRepository)
    {
        $this->currencyRepository = $currencyRepository;
        $this->tariffRepository = $tariffRepository;
        $this->costRepository = $costRepository;
    }

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $allIdCurrencies = $this->currencyRepository->getAllIdCurrencies();
        $allIdTariffs = $this->tariffRepository->getAllIdTariffs();
        shuffle($allIdCurrencies);
        shuffle($allIdTariffs);

        foreach ($allIdCurrencies as $currency_id) {
            foreach ($allIdTariffs as $tariff_id) {
                $price = random_int(self::MIN_COST, self::MAX_COST) / 100;
                $is_active = !$this->costRepository->isExistByTariffIdAndCurrencyId($tariff_id, $currency_id);
                $this->costRepository->saveNewCost($price, $tariff_id, $currency_id, $is_active);
            }
        }
    }
}
