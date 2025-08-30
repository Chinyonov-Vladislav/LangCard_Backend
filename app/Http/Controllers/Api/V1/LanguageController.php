<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\LanguageRequests\AddingLanguageRequest;
use App\Http\Resources\v1\LanguageResources\LanguageResource;
use App\Http\Responses\ApiResponse;
use App\Repositories\LanguageRepositories\LanguageRepositoryInterface;

class LanguageController extends Controller
{
    protected LanguageRepositoryInterface $languageRepository;
    public function __construct(LanguageRepositoryInterface $languageRepository)
    {
        $this->languageRepository = $languageRepository;
    }

    /**
     * @OA\Get(
     *     path="/languages",
     *     summary="Получить список всех языков",
     *     tags={"Языки"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(ref="#/components/parameters/AcceptLanguageHeader"),
     *     @OA\Response(
     *         response=200,
     *         description="Список языков успешно получен",
     *         @OA\JsonContent(
     *             type="object",
     *             required={"status", "message", "data"},
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Все данные о языках"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 required={"items"},
     *                 @OA\Property(
     *                     property="items",
     *                     type="array",
     *                     @OA\Items(ref="#/components/schemas/LanguageResource")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(response=401, ref="#/components/responses/Unauthorized"),
     *     @OA\Response(response=420, ref="#/components/responses/NotVerifiedEmail"),
     * )
     */
    public function getLanguages()
    {
        return ApiResponse::success(__('api.all_language_data'), (object)['items' => LanguageResource::collection($this->languageRepository->getAllLanguages())]);
    }


    /**
     * @OA\Post(
     *     path="/languages",
     *     summary="Добавить новый язык",
     *     tags={"Языки"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(ref="#/components/parameters/AcceptLanguageHeader"),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/AddingLanguageRequest")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Язык успешно добавлен",
     *         @OA\JsonContent(
     *             type="object",
     *             required={"status", "message", "data"},
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Данные о новом языке успешно сохранены"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 required={"language"},
     *                 @OA\Property(property="language", ref="#/components/schemas/LanguageResource")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *           response=422,
     *           description="Ошибка валидации",
     *           @OA\JsonContent(
     *               @OA\Property(property="message", type="string", example="The given data was invalid."),
     *               @OA\Property(property="errors", type="object",
     *                   @OA\Property(property="name", type="array",
     *                       @OA\Items(type="string", example="Поле 'Название' обязательно для заполнения")
     *                   ),
     *                   @OA\Property(property="native_name", type="array",
     *                        @OA\Items(type="string", example="Поле 'Родное название' обязательно для заполнения")
     *                    ),
     *                  @OA\Property(property="code", type="array",
     *                         @OA\Items(type="string", example="Поле 'Код языка' обязательно для заполнения")
     *                     ),
     *                  @OA\Property(property="flag", type="array",
     *                         @OA\Items(type="string", example="Поле 'Флаг' обязательно для заполнения")
     *                     ),
     *                  @OA\Property(property="locale_lang", type="array",
     *                         @OA\Items(type="string", example="Поле 'Локаль' обязательно для заполнения.")
     *                     ),
     *               )
     *           )
     *       ),
     *      @OA\Response(response=401, ref="#/components/responses/Unauthorized"),
     *      @OA\Response(response=420, ref="#/components/responses/NotVerifiedEmail"),
     *      @OA\Response(response=403, ref="#/components/responses/NotAdmin")
     * )
     */
    public function addLanguage(AddingLanguageRequest $request)
    {
        $newLang = $this->languageRepository->saveLanguage($request->name, $request->native_name, $request->code, $request->locale_lang, $request->flag);
        return ApiResponse::success("Данные о новом языке успешно сохранены", (object)['language' => new LanguageResource($newLang)]);
    }
}
