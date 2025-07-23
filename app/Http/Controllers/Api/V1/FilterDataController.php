<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\Tables;
use App\Enums\TypeShowingDeck;
use App\Http\Controllers\Controller;
use App\Http\Resources\v1\FilterParametersResources\ApiLimitFilterParametersResource;
use App\Http\Resources\v1\FilterParametersResources\DeckFilterParameterResource;
use App\Http\Responses\ApiResponse;
use App\Repositories\ApiLimitRepositories\ApiLimitRepositoryInterface;
use App\Repositories\LanguageRepositories\LanguageRepositoryInterface;

class FilterDataController extends Controller
{
    protected ApiLimitRepositoryInterface $apiLimitRepository;
    protected LanguageRepositoryInterface $languageRepository;
    public function __construct(ApiLimitRepositoryInterface $apiLimitRepository, LanguageRepositoryInterface $languageRepository)
    {
        $this->apiLimitRepository = $apiLimitRepository;
        $this->languageRepository = $languageRepository;
    }

    public function getFilterData(string $nameTable)
    {
        switch ($nameTable) {
            case Tables::ApiLimit->value:
                $data = [
                    'min_day' => $this->apiLimitRepository->getMinDay(),
                    'max_day' => $this->apiLimitRepository->getMaxDay(),
                    'min_request_count' => $this->apiLimitRepository->getMinRequestCount(),
                    'max_request_count' => $this->apiLimitRepository->getMaxRequestCount(),
                ];
                return ApiResponse::success(__('api.filter_data_for_model',['nameTable'=>$nameTable]),
                    (object)['filterParameters' =>new ApiLimitFilterParametersResource((object)$data)]);

            case Tables::Card->value:
                return ApiResponse::success(__('api.filter_data_for_model',['nameTable'=>$nameTable]));

            case Tables::Cost->value:
                return ApiResponse::success(__('api.filter_data_for_model',['nameTable'=>$nameTable]));

            case Tables::Currency->value:
                return ApiResponse::success(__('api.filter_data_for_model',['nameTable'=>$nameTable]));

            case Tables::Deck->value:
                $data = [
                    'originalLanguages'=>$this->languageRepository->getAllLanguagesName(),
                    'targetLanguages'=>$this->languageRepository->getAllLanguagesName(),
                    'typesShowDeck'=>[
                        (object)['name'=>TypeShowingDeck::all->value, 'displayValue'=>'Все'],
                        (object)['name'=>TypeShowingDeck::onlyNotPremium->value, 'displayValue'=>'Все, кроме премиум'],
                        (object)['name'=>TypeShowingDeck::onlyPremium->value, 'displayValue'=>'Только премиум'],
                    ]
                ];
                return ApiResponse::success(__('api.filter_data_for_model',['nameTable'=>$nameTable]),
                    (object)['filterParameters' => new DeckFilterParameterResource((object)$data)] );

            case Tables::DeckTopic->value:
                return ApiResponse::success(__('api.filter_data_for_model',['nameTable'=>$nameTable]));

            case Tables::Example->value:
                return ApiResponse::success(__('api.filter_data_for_model',['nameTable'=>$nameTable]));

            case Tables::HistoryPurchase->value:
                return ApiResponse::success(__('api.filter_data_for_model',['nameTable'=>$nameTable]));

            case Tables::Language->value:
                return ApiResponse::success(__('api.filter_data_for_model',['nameTable'=>$nameTable]));

            case Tables::Question->value:
                return ApiResponse::success(__('api.filter_data_for_model',['nameTable'=>$nameTable]));

            case Tables::QuestionAnswer->value:
                return ApiResponse::success(__('api.filter_data_for_model',['nameTable'=>$nameTable]));

            case Tables::Tariff->value:
                return ApiResponse::success(__('api.filter_data_for_model',['nameTable'=>$nameTable]));

            case Tables::Test->value:
                return ApiResponse::success(__('api.filter_data_for_model',['nameTable'=>$nameTable]));

            case Tables::Timezone->value:
                return ApiResponse::success(__('api.filter_data_for_model',['nameTable'=>$nameTable]));

            case Tables::Topic->value:
                return ApiResponse::success(__('api.filter_data_for_model',['nameTable'=>$nameTable]));

            case Tables::User->value:
                return ApiResponse::success(__('api.filter_data_for_model',['nameTable'=>$nameTable]));

            case Tables::UserTestAnswer->value:
                return ApiResponse::success(__('api.filter_data_for_model',['nameTable'=>$nameTable]));

            case Tables::UserTestResult->value:
                return ApiResponse::success(__('api.filter_data_for_model',['nameTable'=>$nameTable]));

            case Tables::VisitedDeck->value:
                return ApiResponse::success(__('api.filter_data_for_model',['nameTable'=>$nameTable]));

            default:
                return ApiResponse::error(__('api.table_not_found', ['nameTable'=>$nameTable]));
        }
    }
}
