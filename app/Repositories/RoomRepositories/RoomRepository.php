<?php

namespace App\Repositories\RoomRepositories;

use App\Models\Room;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class RoomRepository implements RoomRepositoryInterface
{
    protected Room $model;

    public function __construct(Room $model)
    {
        $this->model = $model;
    }

    public function createGroupRoom(string $name, bool $isPrivate): Room
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
            $query->where('user_id', "=", $userId)->where("deleted_at", "=", null);
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
        return $this->model->where("id", "=", $id)->first();
    }

    public function getRoomByIdWithAdmin(int $id): ?Room
    {
        return $this->model->with(["admin"])->where("id", "=", $id)->first();
    }

    public function deleteRoomById(int $id): void
    {
        $this->model->where("id", "=", $id)->delete();
    }

    public function getStatisticForRoomByMonth(int $id): Collection
    {
        // 1. Определяем диапазон месяцев
        $firstDate = DB::table('room_users')->min('created_at');
        $lastDateUsers = DB::table('room_users')->max('deleted_at');
        $lastDateMessages = DB::table('messages')->max('created_at');
        $lastDate = collect([$lastDateUsers, $lastDateMessages, now()])->filter()->max();

        $months = [];
        $start = Carbon::parse($firstDate)->startOfMonth();
        $end = Carbon::parse($lastDate)->startOfMonth();

        while ($start <= $end) {
            $months[] = [
                'date' => $start->format('Y-m'),
                'joined_users' => 0,
                'left_users' => 0,
                'total_messages' => 0
            ];
            $start->addMonth();
        }
        // 2. Получаем данные из таблиц
        $joined = DB::table('room_users')
            ->select(DB::raw("DATE_FORMAT(created_at, '%Y-%m') as month"), DB::raw("COUNT(*) as count"))
            ->groupBy('month')
            ->pluck('count', 'month');
        $left = DB::table('room_users')
            ->whereNotNull('deleted_at')
            ->select(DB::raw("DATE_FORMAT(deleted_at, '%Y-%m') as month"), DB::raw("COUNT(*) as count"))
            ->groupBy('month')
            ->pluck('count', 'month');
        $messages = DB::table('messages')
            ->where('type', 'messageFromUser')
            ->select(DB::raw("DATE_FORMAT(created_at, '%Y-%m') as month"), DB::raw("COUNT(*) as count"))
            ->groupBy('month')
            ->pluck('count', 'month');
        // 3. Объединяем данные
        foreach ($months as $month => &$data) {
            $data['joined_users'] = $joined->get($month, 0);
            $data['left_users'] = $left->get($month, 0);
            $data['total_messages'] = $messages->get($month, 0);
        }
        return collect($months);
    }

    public function getCountMessageFromUsersForRoom(int $id)
    {
         return $this->model->with(['users' => function ($query) {
            $query
                ->select(["users.id", "users.name", "users.avatar_url"])
                ->withCount(['messages as messages_count' => function ($query) {
                    $query->where('type','=', 'messageFromUser');
                }])
                ->orderByDesc('messages_count');
        }])->where("id", "=", $id)->first()->users;
    }
}
