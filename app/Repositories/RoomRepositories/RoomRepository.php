<?php

namespace App\Repositories\RoomRepositories;

use App\Models\Room;

class RoomRepository implements RoomRepositoryInterface
{
    protected Room $model;

    public function __construct(Room $model)
    {
        $this->model = $model;
    }

    public function createGroupRoom(string $name, bool $isPrivate)
    {
        $newModel = new Room();
        $newModel->name = $name;
        $newModel->is_private = $isPrivate;
        $newModel->room_type = "group";
        $newModel->save();
        return $newModel;
    }

    public function getRoomsOfUser(int $userId)
    {
        return $this->model->select(["id", "name", "room_type", "is_private", "created_at"])->whereHas('users', function ($query) use ($userId) {
            $query->where('user_id', "=", $userId);
        })->with([
            'users' => function ($query) {
                $query->select(["users.id", "name", "avatar_url"]);
            },
            'latestMessage' => function ($query) {
                $query->select(["messages.id", "messages.room_id", "messages.user_id", "messages.message", "messages.created_at"])->with(['user' => function ($query) {
                    $query->select(["id", "name", "avatar_url"]);
                }]);
            }])
            ->withMax('messages', 'created_at') // добавит поле messages_max_created_at
            ->orderByDesc('messages_max_created_at')
            ->get();
    }

    public function createDirectRoom(): Room
    {
        $newDirectRoom = new Room();
        $newDirectRoom->name = null;
        $newDirectRoom->is_private = true;
        $newDirectRoom->room_type = "direct";
        $newDirectRoom->save();
        return $newDirectRoom;
    }

    public function isExistDirectRoomForUsers(int $firstUserId, int $secondUserId): bool
    {
        return $this->model->where('room_type', 'direct')
            ->whereHas('users', function ($query) use ($firstUserId) {
                $query->where('user_id', $firstUserId);
            })
            ->whereHas('users', function ($query) use ($secondUserId) {
                $query->where('user_id', $secondUserId);
            })
            ->withCount('users')
            ->having('users_count', 2) // гарантируем, что только эти два пользователя
            ->exists();
    }

    public function getRoomById(int $id): ?Room
    {
        return $this->model->where("id","=",$id)->first();
    }

    public function getRoomByIdWithAdmin(int $id): ?Room
    {
        return $this->model->with(["admin"])->where("id","=",$id)->first();
    }
}
