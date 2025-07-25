<?php

namespace App\Http\Controllers;

use App\Enums\TypeStatus;
use App\Http\Responses\ApiResponse;
use App\Services\TextToSpeechService;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Facebook\WebDriver\WebDriverWait;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class TestController extends Controller
{

    public function __construct()
    {

    }

    private function requestGetHistory()
    {
        return Http::withHeaders($this->headers)->get('https://freetts.ru/api/history');
    }

    private function requestSynthesis(string $text, string $voiceid, string $ext = 'mp3')
    {
        $body = [
            'ext' => $ext,
            'text' => $text,
            'voiceid' => $voiceid
        ];
        return Http::withHeaders($this->headers)->post('https://freetts.ru/api/synthesis', $body);
    }

    public function func()
    {

    }
    /**
     * Тестовый роут.
     *
     * Endpoint для тестирования различного функционала.
     */
    public function testTextToSpeech()
    {
        $textToSpeechService = new TextToSpeechService();
        $text = 'Hello';
        $lang = 'en';
        $voiceId = 'ytvJwPBbkqFc';
        $info = $textToSpeechService->getUrlForGeneratedAudio($text, $lang, $voiceId);
        if($info->status === TypeStatus::success->value)
        {
            return ApiResponse::success('Успех', (object)['url' => $info->url_download]);
        }
        return ApiResponse::error($info->message);
    }
}
