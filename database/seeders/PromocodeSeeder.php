<?php

namespace Database\Seeders;

use App\Repositories\PromocodeRepositories\PromocodeRepositoryInterface;
use App\Repositories\TariffRepositories\TariffRepositoryInterface;
use App\Services\PromocodeGeneratorService;
use Exception;
use Illuminate\Database\Seeder;

class PromocodeSeeder extends Seeder
{
    protected TariffRepositoryInterface $tariffRepository;
    protected PromocodeRepositoryInterface $promocodeRepository;
    protected PromocodeGeneratorService $promocodeGeneratorService;

    public function __construct(PromocodeGeneratorService $promocodeGeneratorService,
                                PromocodeRepositoryInterface $promocodeRepository,
                                TariffRepositoryInterface $tariffRepository)
    {
        $this->tariffRepository = $tariffRepository;
        $this->promocodeRepository = $promocodeRepository;
        $this->promocodeGeneratorService = $promocodeGeneratorService;
    }
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        try {
            $count = 100;
            $allIdActiveTariffs = $this->tariffRepository->getAllIdActiveTariffs();
            if(count($allIdActiveTariffs) === 0){
                return;
            }
            $codes = $this->promocodeGeneratorService->generateCertainCountCode($count);
            foreach ($codes as $code) {
                $this->promocodeRepository->saveNewPromocode($code, $allIdActiveTariffs[array_rand($allIdActiveTariffs)]);
            }
        } catch (Exception $e) {
            logger("Произошла ошибка при генерации промокодов в seeder: {$e->getMessage()}");
        }
    }
}
