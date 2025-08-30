<?php

use App\Models\RoomUser;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int)$user->id === (int)$id;
});

Broadcast::channel('room.{roomId}', function ($user, $roomId) {
    return RoomUser::where('room_id', $roomId)
        ->where('user_id', $user->id)
        ->exists();
});
