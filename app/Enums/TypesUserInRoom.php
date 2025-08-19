<?php

namespace App\Enums;

enum TypesUserInRoom: string
{
    case Admin = 'admin';
    case Member = 'member';
}
