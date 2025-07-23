<?php

namespace App\Console\Commands;

use App\Repositories\ApiLimitRepositories\ApiLimitRepositoryInterface;
use Illuminate\Console\Command;

class ResetApiLimits extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'limits:reset';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reset API limits at the start of a new day';

    protected ApiLimitRepositoryInterface $apiLimitRepository;

    public function __construct(ApiLimitRepositoryInterface $apiLimitRepository)
    {
        parent::__construct();
        $this->apiLimitRepository = $apiLimitRepository;
    }
    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->apiLimitRepository->deleteInfoBeforeCurrentDay();
        logger("Все данные по использованию Api до теперешнего дня были удалены");
    }
}
