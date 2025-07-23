<?php

namespace Database\Seeders;

use App\Repositories\TestRepositories\TestRepositoryInterface;
use App\Repositories\UserRepositories\UserRepositoryInterface;
use App\Repositories\UserTestResultRepositories\UserTestResultRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class UserTestResultSeeder extends Seeder
{
    protected UserTestResultRepositoryInterface $userTestResultRepository;
    protected UserRepositoryInterface $userRepository;
    protected TestRepositoryInterface $testRepository;

    public function __construct(UserTestResultRepositoryInterface $userTestResultRepository,
                                UserRepositoryInterface           $userRepository,
                                TestRepositoryInterface           $testRepository)
    {
        $this->userTestResultRepository = $userTestResultRepository;
        $this->userRepository = $userRepository;
        $this->testRepository = $testRepository;
    }

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            [
                'score' => null,
                'start_time' => Carbon::now(),
                'end_time' => null,
                'user_id' => 1,
                'test_id' => 1
            ]
        ];
        foreach ($data as $item) {
            if (!$this->userRepository->isExistUserById($item['user_id']) || !$this->testRepository->isExistTestById($item['test_id'])) {
                continue;
            }
            $testInfo = $this->testRepository->getTestById($item['test_id']);
            $countOfAttemptsTestByUser = $this->userTestResultRepository->getCountAttemptsOfTestByUserId($item['test_id'], $item['user_id']);
            if ($testInfo->count_attempts !== null && $countOfAttemptsTestByUser >= $testInfo->count_attempts) {
                continue;
            }

            $this->userTestResultRepository->saveNewUserTestResult($item['start_time'], $item['user_id'], $item['test_id'], $countOfAttemptsTestByUser+1);
        }
    }
}
