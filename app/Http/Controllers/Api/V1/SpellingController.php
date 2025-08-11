<?php

namespace App\Http\Controllers\Api\V1;

use App\Exceptions\HunspellNotInstallException;
use App\Exceptions\ProcessHunspellCheckException;
use App\Exceptions\UnsupportedDictionaryLanguageException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\CheckCorrectSentenceRequests\CheckCorrectSentenceRequest;
use App\Http\Resources\V1\SpellCheckingResources\SpellingCheckItemResource;
use App\Http\Responses\ApiResponse;
use App\Services\ApiServices\SpellCheckerService;

class SpellingController extends Controller
{
    /**
     * @OA\Post(
     *     path="/checkSpelling",
     *     summary="Проверка правописания",
     *     description="Проверяет грамматическую корректность предложений с помощью Hunspell. Возвращает список слов с ошибками и варианты исправлений.",
     *     operationId="checkSpelling",
     *     tags={"Проверка правописания"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(ref="#/components/parameters/AcceptLanguageHeader"),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/CheckCorrectSentenceRequest")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Результат проверки правописания",
     *         @OA\JsonContent(
     *             type="object",
     *             required = {"status","message","data"},
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Результат проверки правописания"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="items",
     *                     type="array",
     *                     @OA\Items(ref="#/components/schemas/SpellingCheckItemResource")
     *                 )
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=500,
     *         description="Ошибка при проверке текста",
     *         @OA\JsonContent(
     *             type="object",
     *             required = {"status","message","errors"},
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Ошибка при запуске Hunspell"),
     *             @OA\Property(property="errors", type="object", nullable=true, example=null)
     *         )
     *     ),
     *     @OA\Response(response=401, ref="#/components/responses/Unauthorized"),
     *     @OA\Response(response=420, ref="#/components/responses/NotVerifiedEmail")
     * )
     */
    public function checkSpelling(CheckCorrectSentenceRequest $request)
    {
        try {
            $spellCheckerService = new SpellCheckerService();
            $data = $spellCheckerService->checkSentence($request->language, $request->text);
            return ApiResponse::success(__('api.hunspell_spelling_check_result'),(object)['items'=>SpellingCheckItemResource::collection($data)]);
        }
        catch (HunspellNotInstallException|UnsupportedDictionaryLanguageException|ProcessHunspellCheckException $exception)
        {
            return ApiResponse::error($exception->getMessage(), null, 500);
        }
    }
}
