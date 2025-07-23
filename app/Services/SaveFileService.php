<?php

namespace App\Services;

use App\Enums\TypeFiles;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

class SaveFileService
{
    protected $imageDirectory = 'cards/images';
    protected $audioDirectory = 'cards/audio';

    public function storeFile(UploadedFile $file, $deckId, TypeFiles $typeFiles): string
    {
        $filename = $this->generateFilename($file);
        $directory = match($typeFiles) {
            TypeFiles::image => $this->imageDirectory . '/' . $deckId,
            TypeFiles::audio => $this->audioDirectory . '/' . $deckId,
        };
        return $file->storeAs($directory, $filename, 'public');

    }

    protected function generateFilename(UploadedFile $file): string
    {
        $timestamp = now()->timestamp;
        $random = Str::random(8);
        $extension = $file->getClientOriginalExtension();
        return "{$timestamp}_{$random}.{$extension}";
    }
}
