<?php

namespace App\Repositories\InviteToChatRepositories;

use App\Models\GroupChatInvite;
use App\Services\PaginatorService;

interface InviteToChatRepositoryInterface
{
    public function getRequestOrInvitationForUserWithPaginationAndSortDirection(PaginatorService $paginator, int $userId, int $countOnPage, int $numberCurrentPage, string $sortDirection): array;
    public function getRequestOrInvitationToChatById(int $id): ?GroupChatInvite;
    public function isExistRequestOrInvitationToChat(int $room_id, int $sender_user_id, int $recipient_user_id, string $type): bool;

    public function setResponseUserToRequestOrInvitationToChat(int $requestOrInvitationId, bool $answerUser): void;

    public function saveInviteToGroupChat(int $room_id, int $sender_user_id, int $recipient_user_id, string $type): GroupChatInvite;

    public function deleteRequestInviteToGroupChat(int $id);
}
