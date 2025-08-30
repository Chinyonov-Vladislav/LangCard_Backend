<?php

namespace App\Repositories\AttachmentRepositories;

use App\DTO\FileInfo;
use App\Models\Attachment;

interface AttachmentRepositoryInterface
{
    public function getAttachmentByPathForMessage(string $path, int $messageId): ?Attachment;

    public function addNewAttachmentToMessage(int $messageId, FileInfo $fileInfo): Attachment;

    public function deleteAttachmentOfMessage(string $path, int $messageId): void;

    public function deleteAttachment(Attachment $attachment): void;
}
