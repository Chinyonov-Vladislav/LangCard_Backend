<?php

namespace App\Repositories\AttachmentRepositories;

use App\DTO\FileInfo;
use App\Models\Attachment;

class AttachmentRepository implements AttachmentRepositoryInterface
{

    protected Attachment $model;
    public function __construct(Attachment $model)
    {
        $this->model = $model;
    }

    public function addNewAttachmentToMessage(int $messageId, FileInfo $fileInfo): Attachment
    {
        $newAttachment = new Attachment();
        $newAttachment->message_id = $messageId;
        $newAttachment->path = $fileInfo->getPath();
        $newAttachment->type = $fileInfo->getType();
        $newAttachment->extension = $fileInfo->getExtension();
        $newAttachment->size = $fileInfo->getSize();
        $newAttachment->save();
        return $newAttachment;
    }


    public function getAttachmentByPathForMessage(string $path, int $messageId): ?Attachment
    {
        return $this->model->where("path","=", $path)->where("message_id","=",$messageId)-first();
    }

    public function deleteAttachmentOfMessage(string $path, int $messageId): void
    {
        $this->model->where("path","=", $path)->where("message_id","=",$messageId)-delete();
    }

    public function deleteAttachment(Attachment $attachment): void
    {
        $attachment->delete();
    }
}
