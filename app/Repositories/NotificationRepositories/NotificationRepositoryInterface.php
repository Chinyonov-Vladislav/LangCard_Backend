<?php

namespace App\Repositories\NotificationRepositories;

use App\Enums\TypesNotification;
use App\Http\Filters\FiltersForModels\NotificationFilter;
use App\Models\Notification;
use App\Models\User;
use App\Services\PaginatorService;

interface NotificationRepositoryInterface
{
    public function getNotificationById(string $id);

    public function getStatsNotificationForUser(User $user);

    public function getNotificationsOfUser(PaginatorService $paginator,NotificationFilter $notificationFilter,User $user, ?int $countOnPage, ?int $numberCurrentPage);
    public function markNotificationAsRead(Notification $notification);
    public function saveNotification(User $user, TypesNotification $typeNotification, string $notifiableType, int $notifiableId, array $data);
}
