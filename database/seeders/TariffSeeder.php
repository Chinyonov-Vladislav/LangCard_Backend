<?php

namespace Database\Seeders;

use App\Repositories\TariffRepositories\TariffRepositoryInterface;
use Illuminate\Database\Seeder;

class TariffSeeder extends Seeder
{
    protected TariffRepositoryInterface $tariffRepository;

    public function __construct(TariffRepositoryInterface $tariffRepository)
    {
        $this->tariffRepository = $tariffRepository;
    }

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            ['name'=>'Тариф \'Неделя\'','days'=>7,'is_active'=>true],
            ['name'=>'Тариф \'Две недели\'','days'=>14,'is_active'=>true],
            ['name'=>'Тариф \'Месяц\'','days'=>30,'is_active'=>true],
            ['name'=>'Тариф \'Квартал\'','days'=>90,'is_active'=>true],
            ['name'=>'Тариф \'Полугодие\'','days'=>180,'is_active'=>true],
            ['name'=>'Тариф \'Год\'','days'=>365,'is_active'=>true]
        ];
        foreach ($data as $item) {
            if(!$this->tariffRepository->isExistTariff($item['name'], $item['days'])) {
                $this->tariffRepository->saveNewTariff($item['name'], $item['days'], $item['is_active']);
            }
        }
    }
}
