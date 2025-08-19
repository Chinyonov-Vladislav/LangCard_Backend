<?php

namespace App\Repositories\RoomUserRepositories;

use App\Models\RoomUser;

class RoomUserRepository implements RoomUserRepositoryInterface
{
    protected RoomUser $model;
    public function __construct(RoomUser $model)
    {
        $this->model = $model;
    }

    public function addUserToRoom(int $roomId, int $userId, string $type): RoomUser
    {
        $newRoomUser = new RoomUser();
        $newRoomUser->room_id = $roomId;
        $newRoomUser->user_id = $userId;
        $newRoomUser->type = $type;
        $newRoomUser->save();
        return $newRoomUser;
    }

    public function getUserInRoom(int $roomId, int $userId)
    {
        return $this->model->where("room_id", "=", $roomId)->where("user_id", "=", $userId)->first();
    }

    public function changeBlockedStatusForUser(RoomUser $roomUser)
    {
        $roomUser->is_blocked = !$roomUser->is_blocked;
        $roomUser->save();
        return $roomUser;
    }

}
