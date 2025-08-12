<?php

namespace App\Jobs;

use App\Enums\JobStatuses;
use App\Enums\TypeStatus;
use App\Http\Resources\V1\VoiceResources\VoiceResource;
use App\Repositories\VoiceRepositories\VoiceRepository;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Log;

class SyncVoiceStatusesFromFreetts extends BaseJob
{

    /**
     * Create a new job instance.
     */
    public function __construct(?string $job_id)
    {
        parent::__construct($job_id);
    }

    /**
     * Execute the job.
     */
    public function handle(VoiceRepository $voiceRepository): void
    {
        $this->updateJobStatus(JobStatuses::processing->value);
        try {
            $voiceIdUpdated = [];

            // 1. Получаем голоса из базы
            $dataVoices = $voiceRepository->getVoicesWithPaginationAndFilters();

            // 2. Получаем голоса с freetts.ru
            $response = Http::get('https://freetts.ru/api/list')->json();

            if ($response['status'] !== TypeStatus::success->value) {
                $this->updateJobStatus(JobStatuses::failed->value, ['message'=>'Ошибка при получении данных с freetts.ru']);
                return;
            }

            $voicesInfo = $response['data']['voices'];

            // 3. Сравниваем
            foreach ($dataVoices as $voiceFromDb) {
                $isFound = false;

                foreach ($voicesInfo as $voiceFromSite) {
                    if ($voiceFromSite['id'] === $voiceFromDb->voice_id) {
                        $isFound = true;
                        break;
                    }
                }

                if (!$isFound) {
                    // Голос отсутствует на сайте → деактивируем
                    $voiceRepository->updateStatusActive($voiceFromDb->voice_id, false);
                    $voiceIdUpdated[] = $voiceFromDb->voice_id;
                } else {
                    // Голос найден, но был неактивным → активируем
                    if (!$voiceFromDb->is_active) {
                        $voiceRepository->updateStatusActive($voiceFromDb->voice_id, true);
                        $voiceIdUpdated[] = $voiceFromDb->voice_id;
                    }
                }
            }
            // 4. Логируем изменения
            if (!empty($voiceIdUpdated)) {
                $updatedVoices = $voiceRepository->getVoicesByVoiceId($voiceIdUpdated);
                Log::info('SyncVoiceStatusesFromFreetts: обновлены статусы голосов', [
                    'updated' => VoiceResource::collection($updatedVoices)->toArray(request()),
                ]);
            } else {
                Log::info('SyncVoiceStatusesFromFreetts: изменений нет');
            }
            $this->updateJobStatus(JobStatuses::finished->value);

        } catch (ConnectionException $e) {
            $this->updateJobStatus(JobStatuses::failed->value, ['message'=>'Ошибка соединения с freetts.ru: '.$e->getMessage()]);
        } catch (\Throwable $e) {
            $this->updateJobStatus(JobStatuses::failed->value, ['message'=>'Непредвиденная ошибка: '.$e->getMessage()]);
        }
    }
}
