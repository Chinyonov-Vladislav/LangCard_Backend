<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\InviteRequests\SetInviterRequest;
use App\Http\Resources\v1\InviteResources\InviterResource;
use App\Http\Responses\ApiResponse;
use App\Models\User;
use App\Repositories\InviteCodeRepositories\InviteCodeRepositoryInterface;
use App\Repositories\UserRepositories\UserRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Container\CircularDependencyException;
use function PHPUnit\Framework\objectEquals;

class InviteController extends Controller
{
    protected InviteCodeRepositoryInterface $inviteCodeRepository;
    protected UserRepositoryInterface $userRepository;
    public function __construct(InviteCodeRepositoryInterface $inviteCodeRepository, UserRepositoryInterface $userRepository)
    {
        $this->inviteCodeRepository = $inviteCodeRepository;
        $this->userRepository = $userRepository;
    }


    public function setInviter(SetInviterRequest $request)
    {
        $userWithInviteCode = $this->inviteCodeRepository->getUserWithInviteCode($request->invite_code);
        if($userWithInviteCode === null){
            return ApiResponse::error('Не найден пользователь, владеющий данным invite-кодом',null, 404);
        }
        if(auth()->user()->inviter !== null)
        {
            return ApiResponse::error('Текущий авторизованный пользователь уже указал пользователя, который его пригласил',null, 409);
        }
        $currentAuthUserId = auth()->user()->id;
        if($currentAuthUserId === $userWithInviteCode->id)
        {
            return ApiResponse::error('Вы не можете указать свой собственный код в качестве пригласительного',null, 409);
        }
        $this->userRepository->setInviter($currentAuthUserId, $userWithInviteCode->id);
        $inviter = $this->userRepository->getInfoUserById($userWithInviteCode->id);
        $ancestorsOfUser = $this->userRepository->getAncestorsInviterOfUser($currentAuthUserId);
        foreach ($ancestorsOfUser as $ancestor) {
            // #TODO добавить уведомление о продлении vip - статуса
            $currentEndDate = $ancestor->vip_status_time_end ? Carbon::parse($ancestor->vip_status_time_end) : Carbon::now();
            $dateEndOfVipStatus = $currentEndDate->max(Carbon::now())->addDays($ancestor->depth * -1);
            $this->userRepository->updateEndDateOfVipStatusByIdUser($ancestor->id, $dateEndOfVipStatus);
        }
        return ApiResponse::success('Благодарим! Вы указали пригласившего пользователя.', (object)['inviter' => new InviterResource($inviter)]);
    }
}
