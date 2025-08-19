<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;

class Room extends Model
{
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

    protected function casts(): array
    {
        return [
            'is_private' => 'boolean',
        ];
    }
}
