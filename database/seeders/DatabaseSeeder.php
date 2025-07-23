<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $seeders = [
            TariffSeeder::class,
            TimezoneSeeder::class,
            CurrencySeeder::class,
            UserSeeder::class,
            CostSeeder::class,
            HistoryPurchaseSeeder::class,
            LanguageSeeder::class,
            TopicSeeder::class,
            DeckSeeder::class,
            DeckTopicSeeder::class,
            VisitedDeckSeeder::class,
            CardSeeder::class,
            ExampleSeeder::class,
            TestSeeder::class,
            QuestionSeeder::class,
            QuestionAnswerSeeder::class,
            UserTestResultSeeder::class,
            UserTestAnswerSeeder::class,
            ApiLimitSeeder::class,
            PromocodeSeeder::class,
            VoiceSeeder::class,
            ];
        foreach ($seeders as $seeder) {
            $this->call($seeder);
        }
    }
}
