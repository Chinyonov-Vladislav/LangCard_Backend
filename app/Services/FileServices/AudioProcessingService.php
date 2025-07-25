<?php

namespace App\Services\FileServices;

use App\Enums\TypeFolderForFiles;
use FFMpeg\FFMpeg;
use FFMpeg\Format\Audio\Mp3;
use Illuminate\Support\Str;

class AudioProcessingService
{
    public function convertToStereo($tempInputAudioPath): false|string
    {
        // Инициализируем FFMpeg
        $ffmpeg = FFMpeg::create([
            'ffmpeg.binaries'  => 'C:/ffmpeg/bin/ffmpeg.exe',
            'ffprobe.binaries' => 'C:/ffmpeg/bin/ffprobe.exe'
        ]);
        $audio = $ffmpeg->open($tempInputAudioPath);
        $format = new Mp3();
        $format->setAudioChannels(2); // Устанавливаем 2 канала (стерео)
        $format->setAudioKiloBitrate(320); // Качество звука
        $tempOutputAudioPath = $this->generatePathForOutputAudio($tempInputAudioPath);
        $audio->save($format, $tempOutputAudioPath);
        $processedAudio = file_get_contents($tempOutputAudioPath);
        if (file_exists($tempInputAudioPath)) {
            unlink($tempInputAudioPath);
        }
        if (file_exists($tempOutputAudioPath)) {
            unlink($tempOutputAudioPath);
        }
        return $processedAudio;
    }

    private function generatePathForOutputAudio(string $tempInputAudioPath): string
    {
        $extension = pathinfo($tempInputAudioPath, PATHINFO_EXTENSION);
        $filename = Str::uuid() . ".$extension";
        $folder = TypeFolderForFiles::temporary->value;
        return "$folder/$filename";
    }
}
