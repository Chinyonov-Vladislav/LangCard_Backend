<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\TariffRequests\AddingNewTariffRequest;
use App\Http\Resources\v1\TariffResources\TariffResource;
use App\Http\Responses\ApiResponse;
use App\Repositories\TariffRepositories\TariffRepositoryInterface;
use App\Repositories\UserRepositories\UserRepositoryInterface;

class TariffController extends Controller
{
    protected TariffRepositoryInterface $tariffRepository;
    protected UserRepositoryInterface $userRepository;

    public function __construct(TariffRepositoryInterface $tariffRepository, UserRepositoryInterface $userRepository)
    {
        $this->tariffRepository = $tariffRepository;
        $this->userRepository = $userRepository;
    }

    public function getTariffs()
    {
        $authUserInfo = $this->userRepository->getInfoUserById(auth()->id());
        if($authUserInfo->currency_id === null)
        {
            $data = $this->tariffRepository->getAllActiveTariffs();
            return ApiResponse::success(__('api.all_tariff_data'), (object)['items'=> $data]);
        }
        $data = $this->tariffRepository->getActiveTariffsForUserCurrency($authUserInfo->currency_id);
        return ApiResponse::success(__('api.all_tariff_data_for_user_currency'), (object)['items'=> TariffResource::collection($data)]);
    }
    public function addTariff(AddingNewTariffRequest $request)
    {
        $newTariff = $this->tariffRepository->saveNewTariff($request->name, $request->days);
        if($newTariff === null)
        {
            return ApiResponse::error(__('api.tariff_creation_failed'), null, 500);
        }
        return ApiResponse::success(__('api.tariff_created_successfully'));
    }
    public function changeTariffStatus($id)
    {
        if(!$this->tariffRepository->isExistTariffById($id))
        {
            return ApiResponse::error(__('api.tariff_not_found', ['id'=>$id]), null, 404);
        }
        $this->tariffRepository->changeStatus($id);
        return ApiResponse::success(__('api.tariff_status_changed', ['id'=>$id]));
    }
}
