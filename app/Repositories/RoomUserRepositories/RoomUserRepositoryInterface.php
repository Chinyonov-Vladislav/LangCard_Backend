<?php

namespace App\Repositories\RoomUserRepositories;

use App\Models\RoomUser;

interface RoomUserRepositoryInterface
{
    public function addUserToRoom(int $roomId, int $userId, string $type);

    public function getUserInRoom(int $roomId, int $userId): ?RoomUser;

    public function changeBlockedStatusForUser(RoomUser $roomUser);

    public function deleteUserFromRoom(int $roomId, int $userId);
}
