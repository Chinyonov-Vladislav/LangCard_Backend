<?php

namespace App\Repositories\RoomRepositories;

use App\Models\Room;

interface RoomRepositoryInterface
{
    public function getRoomById(int $id): ?Room;

    public function getRoomByIdWithAdmin(int $id): ?Room;

    public function getRoomsOfUser(int $userId);

    public function createGroupRoom(string $name, bool $isPrivate);

    public function createDirectRoom();

    public function isExistDirectRoomForUsers(int $firstUserId, int $secondUserId): bool;
}
