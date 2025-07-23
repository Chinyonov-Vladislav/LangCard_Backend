<?php

namespace Database\Seeders;

use App\Repositories\CurrencyRepositories\CurrencyRepositoryInterface;
use Illuminate\Database\Seeder;

class CurrencySeeder extends Seeder
{
    protected CurrencyRepositoryInterface $currencyRepository;

    public function __construct(CurrencyRepositoryInterface $currencyRepository)
    {
        $this->currencyRepository = $currencyRepository;
    }

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            ['name' => 'Доллар США', 'code' => 'USD', 'symbol' => '$'],
            ['name' => 'Евро', 'code' => 'EUR', 'symbol' => '€'],
            ['name' => 'Российский рубль', 'code' => 'RUB', 'symbol' => '₽'],
            ['name' => 'Британский фунт', 'code' => 'GBP', 'symbol' => '£'],
            ['name' => 'Японская иена', 'code' => 'JPY', 'symbol' => '¥'],
            ['name' => 'Китайский юань', 'code' => 'CNY', 'symbol' => '¥'],
            ['name' => 'Швейцарский франк', 'code' => 'CHF', 'symbol' => '₣'],
            ['name' => 'Индийская рупия', 'code' => 'INR', 'symbol' => '₹'],
            ['name' => 'Австралийский доллар', 'code' => 'AUD', 'symbol' => '$'],
            ['name' => 'Канадский доллар', 'code' => 'CAD', 'symbol' => '$'],
            ['name' => 'Сингапурский доллар', 'code' => 'SGD', 'symbol' => '$'],
            ['name' => 'Турецкая лира', 'code' => 'TRY', 'symbol' => '₺'],
            ['name' => 'Бразильский реал', 'code' => 'BRL', 'symbol' => 'R$'],
            ['name' => 'Южноафриканский рэнд', 'code' => 'ZAR', 'symbol' => 'R'],
            ['name' => 'Украинская гривна', 'code' => 'UAH', 'symbol' => '₴'],
        ];
        foreach ($data as $item) {
            if(!$this->currencyRepository->isExistByCode($item['code'])) {
                $this->currencyRepository->saveNewCurrency($item['name'], $item['code'], $item['symbol']);
            }
        }
    }
}
