<?php

namespace App\Enums;

enum GroupChatInviteTypes: string
{
    case Request = "request";
    case Invitation = "invitation";
}
