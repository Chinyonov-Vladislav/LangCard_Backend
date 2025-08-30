<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Illuminate\Database\Eloquent\SoftDeletes;

class Room extends Model
{
    use softDeletes;
    protected $table = 'rooms';
    protected $guarded= [];

    /**
     * Пользователи, которые находятся в комнате
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'room_users')
            ->withPivot('type')
            ->withTimestamps();
    }

    /**
     * Получение администратора комнаты
     * @return HasOneThrough
     */
    public function admin(): HasOneThrough
    {
        return $this->hasOneThrough(
            User::class,
            RoomUser::class,
            'room_id', // Foreign key on room_users
            'id',      // Foreign key on users
            'id',      // Local key on rooms
            'user_id'  // Local key on room_users
        )->where('room_users.type', 'admin');
    }

    /**
     * Сообщения в комнате
     */
    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }

    /**
     * Получение последнего сообщения в чате
     */
    public function latestMessage()
    {
        return $this->hasOne(Message::class)->latestOfMany();
    }

    public function invites()
    {
        return $this->hasMany(GroupChatInvite::class, "room_id");
    }

    // связи для статистики

    public function usersJoinedByMonth()
    {
        return $this->hasMany(RoomUser::class)
            ->selectRaw('room_id, DATE_FORMAT(created_at, "%Y-%m") as month, COUNT(*) as count')
            ->groupBy('room_id', 'month');
    }

    public function usersLeftByMonth()
    {
        return $this->hasMany(RoomUser::class)
            ->selectRaw('room_id, DATE_FORMAT(deleted_at, "%Y-%m") as month, COUNT(*) as count')
            ->whereNotNull('deleted_at')
            ->groupBy('room_id', 'month');
    }

    public function messagesCountByUser()
    {
        return $this->hasMany(Message::class)
            ->selectRaw('room_id, user_id, COUNT(*) as count')
            ->where('type', 'messageFromUser')
            ->groupBy('room_id', 'user_id');
    }


    protected function casts(): array
    {
        return [
            'is_private' => 'boolean',
        ];
    }
}
