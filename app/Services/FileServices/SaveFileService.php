<?php

namespace App\Services\FileServices;

use App\Enums\TypeFolderForFiles;
use App\Exceptions\ErrorDefiningFile;
use finfo;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class SaveFileService
{
    /**
     * @throws ErrorDefiningFile
     */
    public function saveFile(string $file): string
    {
        $dataForFile = $this->getExtensionFromContent($file);
        if($dataForFile === null) {
            throw new ErrorDefiningFile("Не удалось распознать тип файла");
        }
        $filename  = Str::uuid().'.'.$dataForFile['extension'];
        $pathWithFolder = $dataForFile['folder']."/$filename";
        Storage::disk('public')->put($pathWithFolder, $file);
        return "storage/$pathWithFolder";
    }

    private function getExtensionFromContent(string $content): ?array
    {
        $fileinfo = new finfo(FILEINFO_MIME_TYPE);
        $mimeType = $fileinfo->buffer($content);
        return $this->getExtensionFromMimeType($mimeType);
    }

    private function getExtensionFromMimeType(string $mimeType): ?array
    {
        $mimeTypes = [
            // Изображения
            'image/jpeg' => ['extension'=>'jpg', 'folder'=>TypeFolderForFiles::image->value],
            'image/png' => ['extension'=>'png', 'folder'=>TypeFolderForFiles::image->value],
            'image/gif' => ['extension'=>'gif', 'folder'=>TypeFolderForFiles::image->value],
            'image/webp' => ['extension'=>'webp', 'folder'=>TypeFolderForFiles::image->value],
            'image/svg+xml' => ['extension'=>'svg', 'folder'=>TypeFolderForFiles::image->value],
            'image/bmp' => ['extension'=>'bmp', 'folder'=>TypeFolderForFiles::image->value],
            'image/tiff' => ['extension'=>'tiff', 'folder'=>TypeFolderForFiles::image->value],
            'image/ico' => ['extension'=>'ico', 'folder'=>TypeFolderForFiles::image->value],

            // Аудиофайлы
            'audio/mpeg' => ['extension'=>'mp3', 'folder'=>TypeFolderForFiles::audio->value],
            'audio/mp3' => ['extension'=>'mp3', 'folder'=>TypeFolderForFiles::audio->value],
            'audio/wav' => ['extension'=>'wav', 'folder'=>TypeFolderForFiles::audio->value],
            'audio/wave' => ['extension'=>'wav', 'folder'=>TypeFolderForFiles::audio->value],
            'audio/x-wav' => ['extension'=>'wav', 'folder'=>TypeFolderForFiles::audio->value],
            'audio/aac' => ['extension'=>'aac', 'folder'=>TypeFolderForFiles::audio->value],
            'audio/ogg' => ['extension'=>'ogg', 'folder'=>TypeFolderForFiles::audio->value],
            'audio/flac' => ['extension'=>'flac', 'folder'=>TypeFolderForFiles::audio->value],
            'audio/x-flac' => ['extension'=>'flac', 'folder'=>TypeFolderForFiles::audio->value],
            'audio/wma' => ['extension'=>'wma', 'folder'=>TypeFolderForFiles::audio->value],
            'audio/x-ms-wma' => ['extension'=>'wma', 'folder'=>TypeFolderForFiles::audio->value],
            'audio/m4a' => ['extension'=>'m4a', 'folder'=>TypeFolderForFiles::audio->value],
            'audio/mp4' => ['extension'=>'m4a', 'folder'=>TypeFolderForFiles::audio->value],
            'audio/3gpp' => ['extension'=>'3gp', 'folder'=>TypeFolderForFiles::audio->value],
            'audio/amr' => ['extension'=>'amr', 'folder'=>TypeFolderForFiles::audio->value],
            'audio/webm' =>['extension'=>'webm', 'folder'=>TypeFolderForFiles::audio->value],

            // Видеофайлы
            'video/mp4' => ['extension'=>'mp4', 'folder'=>TypeFolderForFiles::video->value],
            'video/mpeg' => ['extension'=>'mp4', 'folder'=>TypeFolderForFiles::video->value],
            'video/quicktime' => ['extension'=>'mov', 'folder'=>TypeFolderForFiles::video->value],
            'video/x-msvideo' => ['extension'=>'avi', 'folder'=>TypeFolderForFiles::video->value],
            'video/x-ms-wmv' => ['extension'=>'wmv', 'folder'=>TypeFolderForFiles::video->value],
            'video/webm' => ['extension'=>'webm', 'folder'=>TypeFolderForFiles::video->value],
            'video/ogg' => ['extension'=>'ogv', 'folder'=>TypeFolderForFiles::video->value],
            'video/3gpp' => ['extension'=>'3gp', 'folder'=>TypeFolderForFiles::video->value],
            'video/x-flv' => ['extension'=>'flv', 'folder'=>TypeFolderForFiles::video->value],
            'video/x-matroska' => ['extension'=>'mkv', 'folder'=>TypeFolderForFiles::video->value],
            'video/mp2t' => ['extension'=>'ts', 'folder'=>TypeFolderForFiles::video->value],
            'video/x-m4v' => ['extension'=>'m4v', 'folder'=>TypeFolderForFiles::video->value],

            // Документы
            'application/pdf' =>['extension'=>'pdf', 'folder'=>TypeFolderForFiles::document->value],
            'application/msword' => ['extension'=>'doc', 'folder'=>TypeFolderForFiles::document->value],
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => ['extension'=>'docx', 'folder'=>TypeFolderForFiles::document->value],
            'application/vnd.ms-excel' => ['extension'=>'xls', 'folder'=>TypeFolderForFiles::document->value],
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => ['extension'=>'xlsx', 'folder'=>TypeFolderForFiles::document->value],
            'application/vnd.ms-powerpoint' => ['extension'=>'ppt', 'folder'=>TypeFolderForFiles::document->value],
            'application/vnd.openxmlformats-officedocument.presentationml.presentation' => ['extension'=>'pptx', 'folder'=>TypeFolderForFiles::document->value],

            // Архивы
            'application/zip' => ['extension'=>'zip', 'folder'=>TypeFolderForFiles::document->value],
            'application/x-rar-compressed' => ['extension'=>'rar', 'folder'=>TypeFolderForFiles::document->value],
            'application/x-7z-compressed' => ['extension'=>'pdf', 'folder'=>TypeFolderForFiles::document->value],
            'application/x-tar' => ['extension'=>'tar', 'folder'=>TypeFolderForFiles::document->value],
            'application/gzip' => ['extension'=>'gz', 'folder'=>TypeFolderForFiles::document->value],

            // Текстовые файлы
            'text/plain' => ['extension'=>'txt', 'folder'=>TypeFolderForFiles::document->value],
            'text/html' => ['extension'=>'html', 'folder'=>TypeFolderForFiles::document->value],
            'text/css' => ['extension'=>'css', 'folder'=>TypeFolderForFiles::document->value],
            'text/javascript' => ['extension'=>'js', 'folder'=>TypeFolderForFiles::document->value],
            'application/javascript' => ['extension'=>'js', 'folder'=>TypeFolderForFiles::document->value],
            'application/json' => ['extension'=>'json', 'folder'=>TypeFolderForFiles::document->value],
            'text/xml' => ['extension'=>'xml', 'folder'=>TypeFolderForFiles::document->value],
            'application/xml' => ['extension'=>'xml', 'folder'=>TypeFolderForFiles::document->value],
        ];

        return $mimeTypes[$mimeType] ?? null;
    }
}
