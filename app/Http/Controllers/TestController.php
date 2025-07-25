<?php

namespace App\Http\Controllers;

use App\Enums\TypeStatus;
use App\Http\Responses\ApiResponse;
use App\Services\TextToSpeechService;

class TestController extends Controller
{

    public function __construct()
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
