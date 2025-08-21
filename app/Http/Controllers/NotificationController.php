<?php

namespace App\Http\Controllers;

use App\Enums\TypesNotification;
use App\Http\Filters\FiltersForModels\NotificationFilter;
use App\Http\Requests\Api\V1\NotificationRequests\NotificationFilterRequest;
use App\Http\Resources\V1\NotificationResources\NotificationResource;
use App\Http\Resources\V1\NotificationResources\StatsNotificationForUserResource;
use App\Http\Resources\V1\PaginationResources\PaginationResource;
use App\Http\Responses\ApiResponse;
use App\Services\NotificationService;
use App\Services\PaginatorService;

class NotificationController extends Controller
{
    private NotificationService $notificationService;

    public function __construct()
    {
        $this->notificationService = new NotificationService();
    }
    public function getNotifications(NotificationFilterRequest $request, PaginatorService $paginator, NotificationFilter $notificationFilter)
    {
        $user = auth()->user();
        if($request->has("countOnPage") && $request->has("page"))
        {
            $countOnPage = (int)$request->input('countOnPage', config('app.default_count_on_page'));
            $numberCurrentPage = (int)$request->input('page', config('app.default_page'));
        }
        else
        {
            $countOnPage = null;
            $numberCurrentPage = null;
        }
        $statsCountNotificationForAuthUser = $this->notificationService->getStatsNotificationForUser($user);
        $data = $this->notificationService->getNotifications($paginator, $notificationFilter, $user, $countOnPage, $numberCurrentPage);
        if(is_array($data)) {
            return ApiResponse::success("Уведомления для пользователя постранично", (object)[
                'stats'=> new StatsNotificationForUserResource($statsCountNotificationForAuthUser),
                'items' => NotificationResource::collection($data['items']),
                'pagination' => new PaginationResource($data['pagination'])]);
        }
        return ApiResponse::success("Все уведомления пользователя", (object)[
            'stats'=> new StatsNotificationForUserResource($statsCountNotificationForAuthUser),
            'items' => NotificationResource::collection($data)]);
    }

    public function createNotification()
    {
        $data = [
            'title'=>"Обновление VIP - статуса",
            'message'=>"Ваш VIP-статус продлён"
        ];
        $user = auth()->user();
        $this->notificationService->broadcast($user, $data, TypesNotification::DefaultNotification);
    }
    public function markingNotificationAsRead(string $notificationId)
    {
        $notification = $this->notificationService->getNotificationById($notificationId);
        if($notification === null)
        {
            return ApiResponse::error("Уведомление с id = $notificationId не найдено", null, 404);
        }
        $first = $notification->notifiable_type === get_class(auth()->user());
        $second = $notification->notifiable_id === auth()->id();
        logger("Первая проверка: {$first}");
        logger("Вторая проверка: {$second}");
        if(!($notification->notifiable_type === get_class(auth()->user()) &&
            $notification->notifiable_id === auth()->id()))
        {
            return ApiResponse::error("Уведомление не принадлежит авторизованному пользователю", null, 409);
        }
        if($notification->read_at !== null)
        {
            return ApiResponse::error("Уведомление уже отмечено как прочитанное", null, 409);
        }
        $this->notificationService->markNotificationAsRead($notification);
        return ApiResponse::success("Уведомление отмечено как прочитанное", (object)["item"=>new NotificationResource($notification)]);
    }
}
