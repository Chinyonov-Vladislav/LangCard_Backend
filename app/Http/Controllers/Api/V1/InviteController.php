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


    /**
     * @OA\Post(
     *     path="/setInviter",
     *     summary="Установить пригласившего пользователя",
     *     description="Позволяет авторизованному пользователю указать другого пользователя, который его пригласил, по коду приглашения.",
     *     operationId="setInviter",
     *     tags={"Привязка к пользователю, который пригласил в систему"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Parameter(ref="#/components/parameters/AcceptLanguageHeader"),
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/SetInviterRequest")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Успешная установка пригласившего пользователя",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Благодарим! Вы указали пригласившего пользователя."),
     *             @OA\Property(property="data", type="object",
     *                  required={"inviter"},
     *                  @OA\Property(property="inviter", ref="#/components/schemas/InviterResource")
     *              )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=401,
     *         description="Пользователь не авторизован",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Пользовать не авторизован и не имеет доступа к данным"),
     *             @OA\Property(property="errors", type="object", nullable=true)
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=404,
     *         description="Не найден пользователь с таким кодом",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Не найден пользователь, владеющий данным invite-кодом"),
     *             @OA\Property(property="errors", type="object", nullable=true)
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=409,
     *         description="Конфликт — код уже установлен, код невалиден или нарушает бизнес-логику",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Конфликт — код уже установлен, код невалиден или нарушает бизнес-логику"),
     *             @OA\Property(property="errors", type="object", nullable=true)
     *         )
     *     ),
     *      @OA\Response(
     *          response=422,
     *          description="Ошибка валидации",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="The given data was invalid."),
     *              @OA\Property(
     *                  property="errors",
     *                  type="object",
     *                  @OA\Property(
     *                      property="invite_code",
     *                      type="array",
     *                      @OA\Items(type="string", example="Invite Code is required")
     *                  ),
     *              )
     *          )
     *      )
     * )
     */
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
        if($currentAuthUserId < $userWithInviteCode->id)
        {
            return ApiResponse::error('Невозможно указать пользователя, который был зарегистрирован позже вас, в качестве пригласившего',null, 409);
        }
        $this->userRepository->setInviter($currentAuthUserId, $userWithInviteCode->id);
        $inviter = $this->userRepository->getInfoUserById($userWithInviteCode->id);
        $ancestorsOfUser = $this->userRepository->getAncestorsInviterOfUser($currentAuthUserId);
        foreach ($ancestorsOfUser as $ancestor) {
            // TODO добавить уведомление о продлении vip - статуса
            $currentEndDate = $ancestor->vip_status_time_end ? Carbon::parse($ancestor->vip_status_time_end) : Carbon::now();
            $dateEndOfVipStatus = $currentEndDate->max(Carbon::now())->addDays($ancestor->depth * -1);
            $this->userRepository->updateEndDateOfVipStatusByIdUser($ancestor->id, $dateEndOfVipStatus);
        }
        return ApiResponse::success('Благодарим! Вы указали пригласившего пользователя.', (object)['inviter' => new InviterResource($inviter)]);
    }
}
