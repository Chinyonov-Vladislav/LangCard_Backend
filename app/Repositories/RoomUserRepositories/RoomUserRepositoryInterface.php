<?php

namespace App\Repositories\RoomUserRepositories;

use App\Models\RoomUser;

interface RoomUserRepositoryInterface
{
    public function addUserToRoom(int $roomId, int $userId, string $type);

    public function getUserInRoom(int $roomId, int $userId);

    public function changeBlockedStatusForUser(RoomUser $roomUser);
}
