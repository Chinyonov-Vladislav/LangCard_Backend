<?php

namespace App\Repositories\InviteToChatRepositories;

use App\Models\GroupChatInvite;
use App\Services\PaginatorService;
use Illuminate\Database\Eloquent\Collection;

class InviteToChatRepository implements InviteToChatRepositoryInterface
{
    protected GroupChatInvite $model;

    public function __construct(GroupChatInvite $model)
    {
        $this->model = $model;
    }

    public function saveInviteToGroupChat(int $room_id, int $sender_user_id, int $recipient_user_id, string $type): GroupChatInvite
    {
        $newGroupChatInvite = new GroupChatInvite();
        $newGroupChatInvite->room_id = $room_id;
        $newGroupChatInvite->sender_user_id = $sender_user_id;
        $newGroupChatInvite->recipient_user_id = $recipient_user_id;
        $newGroupChatInvite->type = $type;
        $newGroupChatInvite->save();
        return $newGroupChatInvite;
    }

    public function isExistRequestOrInvitationToChat(int $room_id, int $sender_user_id, int $recipient_user_id, string $type): bool
    {
        return $this->model->where("room_id","=",$room_id)
            ->where("sender_user_id","=",$sender_user_id)
            ->where("recipient_user_id","=",$recipient_user_id)
            ->where("type","=",$type)->exists();
    }

    public function getRequestOrInvitationToChatById(int $id): ?GroupChatInvite
    {
        return $this->model->where("id", "=", $id)->first();
    }

    public function deleteRequestInviteToGroupChat(int $id)
    {
        return $this->model->where("id", "=", $id)->delete();
    }

    public function setResponseUserToRequestOrInvitationToChat(int $requestOrInvitationId, bool $answerUser): void
    {
        $this->model->where("id", "=", $requestOrInvitationId)->update(["accepted" => $answerUser]);
    }

    public function getRequestOrInvitationForUserWithPaginationAndSortDirection(PaginatorService $paginator, int $userId, int $countOnPage, int $numberCurrentPage, string $sortDirection): array
    {
        $query = $this->model->with(["room","sender", "recipient"])->where("sender_user_id", "=", $userId)->orWhere("recipient_user_id", "=", $userId)->orderBy("created_at", $sortDirection);
        $data = $paginator->paginate($query, $countOnPage, $numberCurrentPage);
        $metadataPagination = $paginator->getMetadataForPagination($data);
        return ['items' => collect($data->items()), "pagination" => $metadataPagination];
    }
}
