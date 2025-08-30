<?php

namespace App\Services\FileServices;

use App\DTO\FileInfo;
use Illuminate\Support\Facades\Storage;

class FileInfoService
{
    public function getInfoAboutFile(string $path): FileInfo
    {
        $absolutePath = Storage::disk('public')->path(
            str_replace('storage/', '', $path) // у тебя в БД "storage/images/...", надо превратить в путь к файлу
        );
        // 1. Определяем MIME-тип
        $mimeType = mime_content_type($absolutePath);
        // 2. Категория файла (type)
        $type = match (true) {
            str_starts_with($mimeType, 'image/') => 'image',
            str_starts_with($mimeType, 'audio/') => 'audio',
            str_starts_with($mimeType, 'video/') => 'video',
            default => 'document',
        };
        // 3. Расширение
        $extension = pathinfo($absolutePath, PATHINFO_EXTENSION);
        // 4. Размер в байтах
        $size = filesize($absolutePath);
        return new FileInfo($path, $type, $extension, $size);
    }
}
