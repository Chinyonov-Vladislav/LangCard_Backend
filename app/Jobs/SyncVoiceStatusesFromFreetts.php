<?php

namespace App\Jobs;

use App\Enums\TypeStatus;
use App\Http\Resources\V1\VoiceResources\VoiceResource;
use App\Repositories\VoiceRepositories\VoiceRepository;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Log;

class SyncVoiceStatusesFromFreetts implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

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
    public function handle(VoiceRepository $voiceRepository): void
    {
        try {
            $voiceIdUpdated = [];

            // 1. Получаем голоса из базы
            $dataVoices = $voiceRepository->getVoicesWithPaginationAndFilters();

            // 2. Получаем голоса с freetts.ru
            $response = Http::get('https://freetts.ru/api/list')->json();

            if ($response['status'] !== TypeStatus::success->value) {
                Log::error('SyncVoiceStatusesFromFreetts: ошибка при получении данных с freetts.ru');
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

        } catch (ConnectionException $e) {
            Log::error('SyncVoiceStatusesFromFreetts: ошибка соединения с freetts.ru', [
                'error' => $e->getMessage(),
            ]);
        } catch (\Throwable $e) {
            Log::error('SyncVoiceStatusesFromFreetts: непредвиденная ошибка', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }
}
