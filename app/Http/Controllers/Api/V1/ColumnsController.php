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
        return match ($nameTable) {
            Tables::ApiLimit->value => ApiResponse::success(__('api.table_column_data', ['nameTable' => $nameTable]), (object)['columns' => ApiLimit::columnLabels()]),
            Tables::Card->value => ApiResponse::success(__('api.table_column_data', ['nameTable' => $nameTable]), (object)['columns' => Card::columnLabels()]),
            Tables::Cost->value => ApiResponse::success(__('api.table_column_data', ['nameTable' => $nameTable]), (object)['columns' => Cost::columnLabels()]),
            Tables::Currency->value => ApiResponse::success(__('api.table_column_data', ['nameTable' => $nameTable]), (object)['columns' => Currency::columnLabels()]),
            Tables::Deck->value => ApiResponse::success(__('api.table_column_data', ['nameTable' => $nameTable]), (object)['columns' => Deck::columnLabels()]),
            Tables::DeckTopic->value => ApiResponse::success(__('api.table_column_data', ['nameTable' => $nameTable]), (object)['columns' => DeckTopic::columnLabels()]),
            Tables::Example->value => ApiResponse::success(__('api.table_column_data', ['nameTable' => $nameTable]), (object)['columns' => Example::columnLabels()]),
            Tables::HistoryPurchase->value => ApiResponse::success(__('api.table_column_data', ['nameTable' => $nameTable]), (object)['columns' => HistoryPurchase::columnLabels()]),
            Tables::Language->value => ApiResponse::success(__('api.table_column_data', ['nameTable' => $nameTable]), (object)['columns' => Language::columnLabels()]),
            Tables::Question->value => ApiResponse::success(__('api.table_column_data', ['nameTable' => $nameTable]), (object)['columns' => Question::columnLabels()]),
            Tables::QuestionAnswer->value => ApiResponse::success(__('api.table_column_data', ['nameTable' => $nameTable]), (object)['columns' => QuestionAnswer::columnLabels()]),
            Tables::Tariff->value => ApiResponse::success(__('api.table_column_data', ['nameTable' => $nameTable]), (object)['columns' => Tariff::columnLabels()]),
            Tables::Test->value => ApiResponse::success(__('api.table_column_data', ['nameTable' => $nameTable]), (object)['columns' => Test::columnLabels()]),
            Tables::Timezone->value => ApiResponse::success(__('api.table_column_data', ['nameTable' => $nameTable]), (object)['columns' => Timezone::columnLabels()]),
            Tables::Topic->value => ApiResponse::success(__('api.table_column_data', ['nameTable' => $nameTable]), (object)['columns' => Topic::columnLabels()]),
            Tables::User->value => ApiResponse::success(__('api.table_column_data', ['nameTable' => $nameTable]), (object)['columns' => User::columnLabels()]),
            Tables::UserTestAnswer->value => ApiResponse::success(__('api.table_column_data', ['nameTable' => $nameTable]), (object)['columns' => UserTestAnswer::columnLabels()]),
            Tables::UserTestResult->value => ApiResponse::success(__('api.table_column_data', ['nameTable' => $nameTable]), (object)['columns' => UserTestResult::columnLabels()]),
            Tables::VisitedDeck->value => ApiResponse::success(__('api.table_column_data', ['nameTable' => $nameTable]), (object)['columns' => VisitedDeck::columnLabels()]),
            default => ApiResponse::error(__('api.table_not_found', ['nameTable' => $nameTable])),
        };
    }
}
