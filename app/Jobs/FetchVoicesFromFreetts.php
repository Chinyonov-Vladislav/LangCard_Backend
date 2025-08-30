<?php

namespace App\Jobs;

use App\Enums\JobStatuses;
use App\Enums\TypeStatus;
use App\Repositories\LanguageRepositories\LanguageRepositoryInterface;
use App\Repositories\VoiceRepositories\VoiceRepositoryInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Log;
use Throwable;

class FetchVoicesFromFreetts extends BaseJob
{

    /**
     * Кол-во попыток выполнения Job
     *
     * @var int
     */
    public int $tries = 3;

    /**
     * Задержка между повторными попытками
     *
     * @return int
     */
    public function retryAfter(): int
    {
        return 60; // секунд
    }

    /**
     * Create a new job instance.
     */
    public function __construct(?string $jobId)
    {
        parent::__construct($jobId);
    }

    protected function execute(...$args): void
    {
        try {
            $voiceRepository = app(VoiceRepositoryInterface::class);
            $languageRepository = app(LanguageRepositoryInterface::class);
            $this->updateJobStatus(JobStatuses::processing->value);
            $countNewVoices = 0;
            $request = Http::get('https://freetts.ru/api/list');
            $response = $request->json();
            if (($response['status'] ?? null) === TypeStatus::success->value) {
                $voicesInfo = $response['data']['voices'] ?? [];

                foreach ($voicesInfo as $voice) {
                    if (!$voiceRepository->isExistVoice($voice['id'])) {
                        $convertedLang = str_replace('-', '_', $voice['lang']);
                        $languageByLocale = $languageRepository->getLanguageByLocale($convertedLang);
                        if ($languageByLocale === null) {
                            continue;
                        }

                        $convertedSex = $voice['sex'] === 'm' ? 'male' : 'female';
                        $voiceRepository->saveNewVoice(
                            $voice['id'],
                            $voice['name'],
                            $convertedSex,
                            true,
                            $languageByLocale->id
                        );
                        $countNewVoices++;
                    }
                }
                Log::info("FetchVoicesFromFreetts: добавлено новых голосов = $countNewVoices");
                $this->updateJobStatus(JobStatuses::finished->value);
                return;
            }
            $this->updateJobStatus(JobStatuses::failed->value, ['message'=>'FetchVoicesFromFreetts: ошибка при получении данных с freetts.ru']);
        } catch (ConnectionException $e) {
            $this->updateJobStatus(JobStatuses::failed->value, [JobStatuses::failed->value, ['message'=>'FetchVoicesFromFreetts: ошибка подключения - ' . $e->getMessage()]]);
        } catch (Throwable $e) {
            $this->updateJobStatus(JobStatuses::failed->value, ['message'=>'FetchVoicesFromFreetts: непредвиденная ошибка - ' . $e->getMessage()]);
        }
    }
}
