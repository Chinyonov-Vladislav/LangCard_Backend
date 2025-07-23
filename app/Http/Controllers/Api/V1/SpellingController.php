<?php

namespace App\Http\Controllers\Api\V1;

use App\Exceptions\HunspellNotInstallException;
use App\Exceptions\ProcessHunspellCheckException;
use App\Exceptions\UnsupportedDictionaryLanguageException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\CheckCorrectSentenceRequests\CheckCorrectSentenceRequest;
use App\Http\Responses\ApiResponse;
use App\Services\ApiServices\SpellCheckerService;

class SpellingController extends Controller
{
    public function checkSpelling(CheckCorrectSentenceRequest $request)
    {
        try {
            $spellCheckerService = new SpellCheckerService();
            $data = $spellCheckerService->checkSentence($request->language, $request->text);
            return ApiResponse::success(__('api.hunspell_spelling_check_result'),(object)['items'=>$data]);
        }
        catch (HunspellNotInstallException|UnsupportedDictionaryLanguageException|ProcessHunspellCheckException $exception)
        {
            return ApiResponse::error($exception->getMessage(), null, 500);
        }
    }
}
