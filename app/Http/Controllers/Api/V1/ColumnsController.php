<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\Tables;
use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use App\Models\ApiLimit;
use App\Models\Card;
use App\Models\Cost;
use App\Models\Currency;
use App\Models\Deck;
use App\Models\DeckTopic;
use App\Models\Example;
use App\Models\HistoryPurchase;
use App\Models\Language;
use App\Models\Question;
use App\Models\QuestionAnswer;
use App\Models\Tariff;
use App\Models\Test;
use App\Models\Timezone;
use App\Models\Topic;
use App\Models\User;
use App\Models\UserTestAnswer;
use App\Models\UserTestResult;
use App\Models\VisitedDeck;

class ColumnsController extends Controller
{
    public function getColumns(string $nameTable)
    {
        switch ($nameTable) {
            case Tables::ApiLimit->value:
                return ApiResponse::success(__('api.table_column_data', ['nameTable'=>$nameTable]), (object)['columns' => ApiLimit::columnLabels()]);
            case Tables::Card->value:
                return ApiResponse::success(__('api.table_column_data', ['nameTable'=>$nameTable]), (object)['columns' => Card::columnLabels()]);
            case Tables::Cost->value:
                return ApiResponse::success(__('api.table_column_data', ['nameTable'=>$nameTable]), (object)['columns' => Cost::columnLabels()]);
            case Tables::Currency->value:
                return ApiResponse::success(__('api.table_column_data', ['nameTable'=>$nameTable]), (object)['columns' => Currency::columnLabels()]);
            case Tables::Deck->value:
                return ApiResponse::success(__('api.table_column_data', ['nameTable'=>$nameTable]), (object)['columns' => Deck::columnLabels()]);
            case Tables::DeckTopic->value:
                return ApiResponse::success(__('api.table_column_data', ['nameTable'=>$nameTable]), (object)['columns' => DeckTopic::columnLabels()]);
            case Tables::Example->value:
                return ApiResponse::success(__('api.table_column_data', ['nameTable'=>$nameTable]), (object)['columns' => Example::columnLabels()]);
            case Tables::HistoryPurchase->value:
                return ApiResponse::success(__('api.table_column_data', ['nameTable'=>$nameTable]), (object)['columns' => HistoryPurchase::columnLabels()]);
            case Tables::Language->value:
                return ApiResponse::success(__('api.table_column_data', ['nameTable'=>$nameTable]), (object)['columns' => Language::columnLabels()]);
            case Tables::Question->value:
                return ApiResponse::success(__('api.table_column_data', ['nameTable'=>$nameTable]), (object)['columns' => Question::columnLabels()]);
            case Tables::QuestionAnswer->value:
                return ApiResponse::success(__('api.table_column_data', ['nameTable'=>$nameTable]), (object)['columns' => QuestionAnswer::columnLabels()]);
            case Tables::Tariff->value:
                return ApiResponse::success(__('api.table_column_data', ['nameTable'=>$nameTable]), (object)['columns' => Tariff::columnLabels()]);
            case Tables::Test->value:
                return ApiResponse::success(__('api.table_column_data', ['nameTable'=>$nameTable]), (object)['columns' => Test::columnLabels()]);
            case Tables::Timezone->value:
                return ApiResponse::success(__('api.table_column_data', ['nameTable'=>$nameTable]), (object)['columns' => Timezone::columnLabels()]);
            case Tables::Topic->value:
                return ApiResponse::success(__('api.table_column_data', ['nameTable'=>$nameTable]), (object)['columns' => Topic::columnLabels()]);
            case Tables::User->value:
                return ApiResponse::success(__('api.table_column_data', ['nameTable'=>$nameTable]), (object)['columns' => User::columnLabels()]);
            case Tables::UserTestAnswer->value:
                return ApiResponse::success(__('api.table_column_data', ['nameTable'=>$nameTable]), (object)['columns' => UserTestAnswer::columnLabels()]);
            case Tables::UserTestResult->value:
                return ApiResponse::success(__('api.table_column_data', ['nameTable'=>$nameTable]), (object)['columns' => UserTestResult::columnLabels()]);
            case Tables::VisitedDeck->value:
                return ApiResponse::success(__('api.table_column_data', ['nameTable'=>$nameTable]), (object)['columns' => VisitedDeck::columnLabels()]);
            default:
                return ApiResponse::error(__('api.table_not_found', ['nameTable' => $nameTable]));
        }
    }
}
