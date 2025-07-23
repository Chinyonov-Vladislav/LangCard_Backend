<?php

namespace Database\Seeders;

use App\Models\Card;
use App\Models\HistoryPurchase;
use App\Repositories\CostRepositories\CostRepositoryInterface;
use App\Repositories\HistoryPurchasesRepository\HistoryPurchaseRepository;
use App\Repositories\UserRepositories\UserRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class HistoryPurchaseSeeder extends Seeder
{
    protected UserRepositoryInterface $userRepository;
    protected HistoryPurchaseRepository $historyPurchaseRepository;
    protected CostRepositoryInterface $costRepository;

    public function __construct(HistoryPurchaseRepository $historyPurchaseRepository,
                                UserRepositoryInterface $userRepository,
                                CostRepositoryInterface $costRepository)
    {
        $this->historyPurchaseRepository = $historyPurchaseRepository;
        $this->userRepository = $userRepository;
        $this->costRepository = $costRepository;
    }

    public function run(): void
    {
        $dataUser = $this->userRepository->getInfoAboutUsersForHistoryPurchaseSeeder();
        foreach ($dataUser as $user) {
            if($user->currency_id === null) {
                continue;
            }
            $infoCost = $this->costRepository->getAllCostsWithActiveTariffByCurrencyId($user->currency_id);
            $randomIndexCost = random_int(0, count($infoCost)-1);
            $tariff = $infoCost[$randomIndexCost]->tariff;
            $countDaysOfTariff = $tariff->days;
            if($user->vip_status_time_end == null)
            {
                $dateEndOfVipStatusAfterBuying = Carbon::now()->addDays($countDaysOfTariff);
            }
            else
            {
                $dateEndOfVipStatusAfterBuying = Carbon::parse($user->vip_status_time_end)->addDays($countDaysOfTariff);
            }
            $this->historyPurchaseRepository->saveNewHistoryPurchase(Carbon::now()->format('Y-m-d H:i:s'),
                $dateEndOfVipStatusAfterBuying->format('Y-m-d H:i:s'),$user->id, $infoCost[$randomIndexCost]->id);
            $this->userRepository->updateEndDateOfVipStatusByIdUser($user->id, $dateEndOfVipStatusAfterBuying);
        }
    }
}
