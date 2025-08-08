<?php

namespace App\Jobs;

use App\Enums\TypeStatus;
use App\Repositories\LanguageRepositories\LanguageRepository;
use App\Repositories\VoiceRepositories\VoiceRepository;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Log;

class FetchVoicesFromFreetts implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Кол-во попыток выполнения Job
     *
     * @var int
     */
    public $tries = 3;

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
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(VoiceRepository $voiceRepository, LanguageRepository $languageRepository): void
    {
        try {
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

                Log::info("FetchVoicesFromFreetts: добавлено новых голосов = {$countNewVoices}");
                return;
            }

            Log::error('FetchVoicesFromFreetts: ошибка при получении данных с freetts.ru');
        } catch (ConnectionException $e) {
            Log::error('FetchVoicesFromFreetts: ошибка подключения - ' . $e->getMessage());
        } catch (\Throwable $e) {
            Log::error('FetchVoicesFromFreetts: непредвиденная ошибка - ' . $e->getMessage());
        }
    }
}
