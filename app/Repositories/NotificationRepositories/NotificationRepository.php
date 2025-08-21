<?php

namespace App\Repositories\NotificationRepositories;

use App\Enums\TypesNotification;
use App\Http\Filters\FiltersForModels\NotificationFilter;
use App\Models\User;
use App\Models\Notification;
use App\Services\PaginatorService;
use Illuminate\Support\Str;

class NotificationRepository implements NotificationRepositoryInterface
{
    protected Notification $model;

    public function __construct(Notification $model)
    {
        $this->model = $model;
    }

    public function saveNotification(User $user, TypesNotification $typeNotification, string $notifiableType, int $notifiableId, array $data)
    {
        $newNotification = Notification::create([
            'id' => (string)Str::uuid(),
            'type' => $typeNotification->value,
            'notifiable_type' => User::class,
            'notifiable_id' => $user->id,
            'data' => $data,
            'read_at' => null,
        ]);
        return $newNotification;
    }

    public function getNotificationsOfUser(PaginatorService $paginator, NotificationFilter $notificationFilter, User $user, ?int $countOnPage, ?int $numberCurrentPage)
    {
        $query = $user->notifications()->filter($notificationFilter);
        if ($countOnPage !== null && $numberCurrentPage !== null) {
            $data = $paginator->paginate($query, $countOnPage, $numberCurrentPage);
            $metadataPagination = $paginator->getMetadataForPagination($data);
            return ['items' => collect($data->items()), "pagination" => $metadataPagination];
        }
        return $query->get();
    }

    public function getStatsNotificationForUser(User $user): array
    {
        $totalCount = $user->notifications()->count();
        $unreadCount = $user->notifications->where('read_at', null)->count();
        $readCount = $totalCount - $unreadCount;
        return ["total_count" => $totalCount, "read_count" => $readCount, "unread_count" => $unreadCount];
    }

    public function getNotificationById(string $id)
    {
        return $this->model->where("id", "=", $id)->first();
    }

    public function markNotificationAsRead(Notification $notification): void
    {
        $notification->update(['read_at' => now()]);
    }
}
