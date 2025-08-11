<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\TypeInfoAboutDeck;
use App\Http\Controllers\Controller;
use App\Http\Filters\FiltersForModels\DeckFilter;
use App\Http\Requests\Api\V1\DeckRequests\AddingTopicsToDeckRequest;
use App\Http\Requests\Api\V1\DeckRequests\CreateDeckRequest;
use App\Http\Requests\Api\V1\DeckRequests\DeckFilterRequest;
use App\Http\Resources\v1\DeckResources\DeckResource;
use App\Http\Resources\V1\PaginationResources\PaginationResource;
use App\Http\Responses\ApiResponse;
use App\Repositories\DeckRepositories\DeckRepositoryInterface;
use App\Repositories\DeckTopicRepositories\DeckTopicRepositoryInterface;
use App\Repositories\TopicRepositories\TopicRepositoryInterface;
use App\Repositories\UserRepositories\UserRepositoryInterface;
use App\Repositories\UserTestResultRepositories\UserTestResultRepositoryInterface;
use App\Services\PaginatorService;
use Dedoc\Scramble\Attributes\HeaderParameter;
use Dedoc\Scramble\Attributes\QueryParameter;
use Exception;
use Illuminate\Support\Facades\DB;
use Throwable;

class DeckController extends Controller
{
    protected UserRepositoryInterface $userRepository;
    protected DeckRepositoryInterface $deckRepository;

    protected UserTestResultRepositoryInterface $userTestResultRepository;

    protected DeckTopicRepositoryInterface $deckTopicRepository;

    protected TopicRepositoryInterface $topicRepository;

    public function __construct(DeckRepositoryInterface           $deckRepository,
                                UserTestResultRepositoryInterface $userTestResultRepository,
                                UserRepositoryInterface           $userRepository,
                                DeckTopicRepositoryInterface      $deckTopicRepository,
                                TopicRepositoryInterface          $topicRepository)
    {
        $this->deckRepository = $deckRepository;
        $this->userTestResultRepository = $userTestResultRepository;
        $this->userRepository = $userRepository;
        $this->deckTopicRepository = $deckTopicRepository;
        $this->topicRepository = $topicRepository;
    }


    /**
     * @OA\Get(
     *     path="/decks",
     *     summary="Получение списка колод",
     *     description="Возвращает список колод с фильтрацией и пагинацией.",
     *     operationId="getDecks",
     *     tags={"Колоды"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Номер страницы",
     *         required=false,
     *         @OA\Schema(type="integer", minimum=1, example=1)
     *     ),
     *     @OA\Parameter(
     *         name="countOnPage",
     *         in="query",
     *         description="Количество элементов на странице",
     *         required=false,
     *         @OA\Schema(type="integer", minimum=1, example=10)
     *     ),
     *     @OA\Parameter(
     *         name="originalLanguages",
     *         in="query",
     *         description="Коды оригинальных языков через запятую (en,fr,de)",
     *         required=false,
     *         @OA\Schema(type="string", example="en,fr")
     *     ),
     *     @OA\Parameter(
     *         name="targetLanguages",
     *         in="query",
     *         description="Коды целевых языков через запятую (es,it)",
     *         required=false,
     *         @OA\Schema(type="string", example="es,it")
     *     ),
     *     @OA\Parameter(
     *         name="showPremium",
     *         in="query",
     *         description="Фильтр премиум-колод: only — только премиум, exclude — исключить, all — все",
     *         required=false,
     *         @OA\Schema(type="string", enum={"only","exclude","all"}, example="all")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Успешный ответ с пагинированным списком колод",
     *         @OA\JsonContent(
     *             type="object",
     *             required = {"status","message","data"},
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Данные колод на странице 1"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="items",
     *                     type="array",
     *                     @OA\Items(ref="#/components/schemas/DeckResource")
     *                 ),
     *                 @OA\Property(
     *                      property="pagination",
     *                      ref="#/components/schemas/PaginationResource"
     *                  )
     *             )
     *         )
     *     ),
     *      @OA\Response(response=401, ref="#/components/responses/Unauthorized"),
     *      @OA\Response(response=420, ref="#/components/responses/NotVerifiedEmail")
     * )
     */
    #[QueryParameter('page', 'Номер страницы', type: 'int', default: 10, example: 1)]
    #[QueryParameter('countOnPage', 'Количество элементов на странице', type: 'int', default: 10, example: 10)]
    #[QueryParameter('originalLanguages', description: 'Оригинальные языки (через запятую)', type: 'string', infer: true, example: 'en_US,ru_RU,de_DE')]
    #[QueryParameter('targetLanguages', description: 'Целевые языки (через запятую)', type: 'string', infer: true, example: 'en_US,ru_RU,de_DE')]
    #[QueryParameter('showPremium', description: 'Тип показа премиум контента', type: 'string', infer: true, example: 'onlyPremium')]
    public function getDecks(DeckFilterRequest $request, PaginatorService $paginator, DeckFilter $deckFilter)
    {
        $countOnPage = (int)$request->input('countOnPage', config('app.default_count_on_page'));
        $numberCurrentPage = (int)$request->input('page', config('app.default_page'));
        $data = $this->deckRepository->getDecksWithPaginationAndFilters($paginator, $deckFilter, $countOnPage, $numberCurrentPage);
        return ApiResponse::success(__('api.deck_data_on_page', ['numberCurrentPage' => $numberCurrentPage]), (object)['items' => DeckResource::collection($data['items']),
            'pagination' => new PaginationResource($data['pagination'])]);
    }


    /**
     * @OA\Delete(
     *     path="/decks/{id}",
     *     operationId="deleteDeck",
     *     tags={"Колоды"},
     *     summary="Удаление колоды",
     *     description="Удаляет колоду по её ID. Если для колоды нет запущенных тестов — удаление будет физическим, иначе — мягким (soft delete).",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID колоды для удаления",
     *         required=true,
     *         @OA\Schema(type="integer", example=5)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Колода успешно удалена",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Колода 5 была удалена (мягко или физически)."),
     *             @OA\Property(property="data", type="object", nullable=true, example=null)
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Колода не найдена",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Колода с ID 5 не найдена"),
     *             @OA\Property(property="data", type="object", nullable=true, example=null)
     *         )
     *     ),
     *     @OA\Response(
     *         response=409,
     *         description="Пользователь не является владельцем колоды",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Пользователь не является владельцем колоды."),
     *             @OA\Property(property="data", type="object", nullable=true, example=null)
     *         )
     *     ),
     *     @OA\Response(response=401, ref="#/components/responses/Unauthorized"),
     *     @OA\Response(response=420, ref="#/components/responses/NotVerifiedEmail")
     * )
     */
    public function deleteDeck(int $id)
    {
        $userId = auth()->id();
        $currentDeck = $this->deckRepository->getDeckById($id, TypeInfoAboutDeck::minimum);
        if ($currentDeck === null) {
            return ApiResponse::error(__('api.deck_not_found', ['id' => $id]), null, 404);
        }
        if ($currentDeck->user_id !== $userId) {
            return ApiResponse::error(__('api.user_not_deck_owner'), null, 409);
        }
        $hasAnyStartedTests = $this->userTestResultRepository->existStartedTestForDeck($id);
        if ($hasAnyStartedTests === false) {
            $this->deckRepository->deleteDeckById($id);
            return ApiResponse::success(__('api.deck_deleted_permanently', ['id' => $id]));
        }
        $this->deckRepository->softDeleteDeckById($id);
        return ApiResponse::success(__('api.deck_soft_deleted', ['id' => $id]));
    }

    /**
     * @OA\Get(
     *     path="/decks/{id}",
     *     summary="Получить колоду по ID",
     *     description="Возвращает полную информацию о колоде по её ID.",
     *     operationId="getDeck",
     *     tags={"Колоды"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Parameter(ref="#/components/parameters/AcceptLanguageHeader"),
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID колоды",
     *         @OA\Schema(type="integer", example=5, minimum=1)
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Колода найдена",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Колода с ID 5 найдена"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="item", ref="#/components/schemas/DeckResource")
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=404,
     *         description="Колода не найдена",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Колода с ID 99 не найдена"),
     *             @OA\Property(property="errors", type="array", @OA\Items(type="string"))
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=409,
     *         description="Доступ к премиум-колоде запрещён",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Доступ к премиум-колоде с ID 5 запрещён"),
     *             @OA\Property(property="errors", type="array", @OA\Items(type="string"))
     *         )
     *     ),
     *     @OA\Response(response=401, ref="#/components/responses/Unauthorized"),
     *     @OA\Response(response=420, ref="#/components/responses/NotVerifiedEmail")
     * )
     */
    public function getDeck(int $id)
    {
        $deck = $this->deckRepository->getDeckById($id, TypeInfoAboutDeck::maximum);
        if ($deck === null) {
            return ApiResponse::error(__('api.deck_not_found', ['id' => $id]), null, 404);
        }
        if ($deck->is_premium && !$this->userRepository->hasUserActivePremiumStatusByIdUser(auth()->id())) {
            return ApiResponse::error(__('api.deck_is_premium_access_denied', ['id' => $id]), null, 409);
        }
        return ApiResponse::success(__('api.deck_found', ['id' => $id]), (object)['item' => new DeckResource($deck)]);
    }


    /**
     * @OA\Post(
     *     path="/decks",
     *     summary="Создание новой колоды",
     *     description="Создаёт новую языковую колоду и возвращает её полную информацию.",
     *     operationId="createDeck",
     *     tags={"Колоды"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Parameter(ref="#/components/parameters/AcceptLanguageHeader"),
     *
     *     @OA\RequestBody(
     *         required=true,
     *         description="Данные для создания новой колоды",
     *         @OA\JsonContent(ref="#/components/schemas/CreateDeckRequest")
     *     ),
     *
     *     @OA\Response(
     *         response=201,
     *         description="Новая колода успешно создана",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Новая колода была успешно создана"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="newDeck", ref="#/components/schemas/DeckResource")
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=422,
     *         description="Ошибка валидации",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="В запросе содержатся ошибки"),
     *             @OA\Property(property="errors", type="object",
     *                 @OA\Property(property="name", type="array", @OA\Items(type="string", example="Название обязательно для заполнения.")),
     *                 @OA\Property(property="original_language_id", type="array", @OA\Items(type="string", example="Необходимо указать язык оригинала.")),
     *                 @OA\Property(property="target_language_id", type="array", @OA\Items(type="string", example="Язык перевода должен отличаться от языка оригинала.")),
     *                 @OA\Property(property="is_premium", type="array", @OA\Items(type="string", example="Поле 'is_premium' должно быть булевым значением (true/false)."))
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=500,
     *         description="Внутренняя ошибка сервера",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Произошла непредвиденная ошибка"),
     *             @OA\Property(property="errors", type="array", @OA\Items(type="string"))
     *         )
     *     ),
     *
     *     @OA\Response(response=401, ref="#/components/responses/Unauthorized"),
     *     @OA\Response(response=420, ref="#/components/responses/NotVerifiedEmail")
     * )
     */
    public function createDeck(CreateDeckRequest $request)
    {
        try {
            DB::beginTransaction();
            $newDeck = $this->deckRepository->saveNewDeck($request->name, $request->original_language_id, $request->target_language_id, auth()->id(), $request->is_premium);
            $newDeck = $this->deckRepository->getDeckById($newDeck->id, TypeInfoAboutDeck::maximum);
            DB::commit();
            return ApiResponse::success('Новая колода была успешно создана', (object)['newDeck' => new DeckResource($newDeck)], 201);
        } catch (Exception|Throwable $exception) {
            DB::rollBack();
            return ApiResponse::error($exception->getMessage(), null, 500);
        }
    }
    /**
     * @OA\Post(
     *     path="/decks/{id}/topics",
     *     operationId="addTopicsToDeck",
     *     tags={"Колоды"},
     *     summary="Добавление тем в колоду",
     *     description="Добавляет одну или несколько тем в существующую колоду по её ID.",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID колоды, в которую добавляются темы",
     *         required=true,
     *         @OA\Schema(type="integer", example=5)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/AddingTopicsToDeckRequest")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Темы успешно добавлены",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Темы установлены для колоды"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="newDeck", ref="#/components/schemas/DeckResource")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Колода не найдена",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Колода с ID 5 не найдена"),
     *             @OA\Property(property="data", type="object", nullable=true, example=null)
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Ошибка валидации",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="The given data was invalid."),
     *             @OA\Property(property="errors", type="object",
     *                 @OA\Property(property="topic_ids", type="array",
     *                     @OA\Items(type="string", example="Поле 'topic_ids' обязательно для заполнения.")
     *                 ),
     *                 @OA\Property(property="topic_ids.*", type="array",
     *                     @OA\Items(type="string", example="Каждый элемент 'topic_ids' должен быть существующим ID темы.")
     *                 )
     *             )
     *         )
     *     ),
     *      @OA\Response(response=401, ref="#/components/responses/Unauthorized"),
     *      @OA\Response(response=420, ref="#/components/responses/NotVerifiedEmail")
     * )
     */
    public function addTopicsToDeck(int $id, AddingTopicsToDeckRequest $request)
    {
        $deck = $this->deckRepository->getDeckById($id, TypeInfoAboutDeck::minimum);
        if ($deck === null) {
            return ApiResponse::error(__('api.deck_not_found', ['id' => $id]), null, 404);
        }
        $unique_topic_ids = array_unique($request->topic_ids);
        foreach ($unique_topic_ids as $unique_topic_id) {

            $this->deckTopicRepository->saveNewDeckTopic($request->deck_id, $unique_topic_id);
        }
        $newDeck = $this->deckRepository->getDeckById($request->deck_id, TypeInfoAboutDeck::maximum);
        return ApiResponse::success('Темы установлены для колоды', (object)['newDeck' => new DeckResource($newDeck)], 201);
    }
}
