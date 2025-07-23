<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class DownloadFileService
{
    /**
     * Скачивание аудиофайла
     * @throws Exception
     */
    public function downloadAudioFile(string $downloadUrl): string
    {
        $response = Http::get($downloadUrl);
        if ($response->failed()) {
            throw new Exception('Не удалось скачать файл. HTTP код: ' . $response->status());
        }
        $filename = 'audio_' . time() . '.mp3';
        $storagePath = 'audio/' . $filename;
        Storage::disk('public')->put($storagePath, $response->body());
        return $storagePath;
    }
}
