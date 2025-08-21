<?php

namespace App\Services;

use App\Enums\TypesNotification;
use App\Events\NotificationCreated;
use App\Http\Filters\FiltersForModels\NotificationFilter;
use App\Models\Notification;
use App\Models\User;
use App\Repositories\NotificationRepositories\NotificationRepositoryInterface;

class NotificationService
{
    protected NotificationRepositoryInterface $notificationRepository;

    public function __construct()
    {
        $this->notificationRepository = app(NotificationRepositoryInterface::class);
    }

    public function getNotificationById(string $notificationId)
    {
        return $this->notificationRepository->getNotificationById($notificationId);
    }

    public function getStatsNotificationForUser(User $user): array
    {
        return $this->notificationRepository->getStatsNotificationForUser($user);
    }

    public function getNotifications(PaginatorService $paginator, NotificationFilter $notificationFilter,  User $user, ?int $countOnPage, ?int $numberCurrentPage)
    {
        return $this->notificationRepository->getNotificationsOfUser($paginator,$notificationFilter, $user, $countOnPage, $numberCurrentPage);
    }

    public function markNotificationAsRead(Notification $notification): void
    {
        $this->notificationRepository->markNotificationAsRead($notification);
    }

    /**
     * Отправка уведомления через broadcast конкретному пользователю
     */
    public function broadcast(User $user, array $data, TypesNotification $typeNotification): void
    {
        $this->notificationRepository->saveNotification($user, $typeNotification, User::class, $user->id, $data);
        broadcast(new NotificationCreated($user, $data, $typeNotification))->toOthers();
    }
}
