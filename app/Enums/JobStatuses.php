<?php

namespace App\Enums;

enum JobStatuses: string
{
    case queued = 'queued';
    case processing = 'processing';
    case finished = 'finished';
    case failed = 'failed';
}
