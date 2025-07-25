<?php

namespace App\Enums;

enum TypeFolderForFiles: string
{
    case audio = "audio";
    case video = "video";
    case image = "image";
    case document = "document";
    case temporary = "temp";
}
