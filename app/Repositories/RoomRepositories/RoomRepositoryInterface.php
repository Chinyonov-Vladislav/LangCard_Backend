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

    public function deleteRoomById(int $id);

    public function getStatisticForRoomByMonth(int $id);

    public function getCountMessageFromUsersForRoom(int $id);
}
