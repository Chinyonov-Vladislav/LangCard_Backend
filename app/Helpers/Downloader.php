<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class Downloader
{
    public function downloadImage(string $url, string $languageCode)
    {
        $response = Http::get($url);
        if ($response->successful()) {
            $extension = pathinfo($url, PATHINFO_EXTENSION); // например: "png"
            $filename = "flags/{$languageCode}." . $extension;
            Storage::disk('public')->put($filename, $response->body());
            return 'storage/' . $filename;
        }
        return null;
    }
}
