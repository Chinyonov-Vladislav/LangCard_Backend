<?php

namespace Database\Seeders;

use App\Repositories\CurrencyRepositories\CurrencyRepositoryInterface;
use App\Repositories\LanguageRepositories\LanguageRepositoryInterface;
use App\Repositories\RegistrationRepositories\RegistrationRepositoryInterface;
use App\Repositories\TimezoneRepositories\TimezoneRepositoryInterface;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    protected RegistrationRepositoryInterface $registrationRepository;

    protected CurrencyRepositoryInterface $currencyRepository;

    protected LanguageRepositoryInterface $languageRepository;
    protected TimezoneRepositoryInterface $timezoneRepository;

    public function __construct(RegistrationRepositoryInterface $registrationRepository,
                                CurrencyRepositoryInterface $currencyRepository,
                                LanguageRepositoryInterface $languageRepository,
                                TimezoneRepositoryInterface $timezoneRepository)
    {
        $this->registrationRepository = $registrationRepository;
        $this->currencyRepository = $currencyRepository;
        $this->languageRepository = $languageRepository;
        $this->timezoneRepository = $timezoneRepository;
    }

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            [
                'name' => 'chinyonov_vlad',
                'email' => 'vlad2000100600@gmail.com',
                'avatar_url' => null,
                'password' => '78Aeq4883$',
                'type_user' => 'user',
                'currency_id' => 3,
                'timezone_id' => null,
                'language_id' => null,
                'vip_status_time_end' => null
            ],
            [
                'name' => 'vlad_zolotarev_59',
                'email' => 'zolotarev.vladik59@gmail.com',
                'avatar_url' => null,
                'password' => 'Qwerty12asdf!',
                'type_user' => 'user',
                'language_id' => null,
                'currency_id' => 3,
                'timezone_id' => null,
                'vip_status_time_end' => null
            ],
        ];
        foreach ($data as $user) {
            if($this->registrationRepository->isExistUserByEmail($user['email'])) {
                continue;
            }
            if($user['currency_id']!== null && !$this->currencyRepository->isExistCurrencyById($user['currency_id'])) {
                continue;
            }
            if($user["language_id"] !== null && !$this->languageRepository->isExistLanguageById($user["language_id"]))
            {
                continue;
            }
            if($user["timezone_id"]!== null && $this->timezoneRepository->getTimezoneById($user["timezone_id"]) === null) {
                continue;
            }
            $this->registrationRepository->registerUser(
                $user['name'],
                $user['email'],
                $user['password'],
                $user['type_user'],
                $user['avatar_url'],
                $user['vip_status_time_end'],
                $user['timezone_id'],
                $user['currency_id'],
                $user['language_id'],
            );
        }

    }
}
