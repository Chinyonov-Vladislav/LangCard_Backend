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
    public function test()
    {
        return ApiResponse::success('Успешный успех');
    }
}
