<?php

namespace App\Services\FileServices;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

class DownloadFileService
{
    /**
     * @throws ConnectionException
     */
    public function downloadFile(string $url): ?string
    {
        $response = Http::get($url);
        return $response->body();
    }
}
