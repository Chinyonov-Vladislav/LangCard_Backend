<?php

namespace App\Enums;

enum NameJobsEnum: string
{
    case GeneratingVoiceJob = "GeneratingVoiceJob";
    case FetchVoicesFromFreetts = "FetchVoicesFromFreetts";
    case ProcessDelayedApiRequest = "ProcessDelayedApiRequest";
    case SyncVoiceStatusesFromFreetts = "SyncVoiceStatusesFromFreetts";
    case SendNewsMailJob = "SendNewsMailJob";
}
