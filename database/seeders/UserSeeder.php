<?php

namespace Database\Seeders;

use App\Repositories\CurrencyRepositories\CurrencyRepositoryInterface;
use App\Repositories\RegistrationRepositories\RegistrationRepositoryInterface;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    protected RegistrationRepositoryInterface $registrationRepository;

    protected CurrencyRepositoryInterface $currencyRepository;

    public function __construct(RegistrationRepositoryInterface $registrationRepository, CurrencyRepositoryInterface $currencyRepository)
    {
        $this->registrationRepository = $registrationRepository;
        $this->currencyRepository = $currencyRepository;
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
                'vip_status_time_end' => null
            ]
        ];
        foreach ($data as $user) {
            if($this->registrationRepository->isExistUserByEmail($user['email'])) {
                continue;
            }
            if(!$this->currencyRepository->isExistCurrencyById($user['currency_id'])) {
                continue;
            }
            $this->registrationRepository->registerUser($user['name'],$user['email'], $user['password'], $user['timezone_id'], $user['currency_id'], $user['avatar_url'], $user['type_user'], $user['vip_status_time_end']);
        }

    }
}
