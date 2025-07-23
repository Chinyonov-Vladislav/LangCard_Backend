<?php

namespace App\Enums;

enum Tables: string
{
    case ApiLimit = 'api_limits';
    case Card = 'cards';
    case Cost = 'costs';

    case Currency = 'currencies';

    case Deck = 'decks';
    case DeckTopic = 'deck_topics';

    case Example = 'examples';

    case HistoryPurchase = 'history_purchases';

    case Language = 'languages';
    case Question = 'questions';
    case QuestionAnswer = 'question_answers';
    case Tariff = 'tariffs';
    case Test = 'tests';
    case Timezone = 'timezones';
    case Topic = 'topics';
    case User = 'users';
    case UserTestAnswer = 'user_test_answers';
    case UserTestResult = 'user_test_results';
    case VisitedDeck = 'visited_decks';

}
