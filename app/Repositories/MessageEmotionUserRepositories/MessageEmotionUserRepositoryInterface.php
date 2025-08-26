<?php

namespace App\Repositories\MessageEmotionUserRepositories;

interface MessageEmotionUserRepositoryInterface
{
    public function addNewEmotionToMessageFromUser(int $messageEmotionId, int $userId);

    public function changeEmotionToMessageFromUser(int $previousMessageEmotionId, int $newMessageEmotionId, int $userId);

    public function removeEmotionForMessageFromUser(int $messageEmotionId, int $userId);
}
