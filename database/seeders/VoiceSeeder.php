<?php

namespace Database\Seeders;

use App\Enums\TypeStatus;
use App\Repositories\LanguageRepositories\LanguageRepositoryInterface;
use App\Repositories\VoiceRepositories\VoiceRepositoryInterface;
use Illuminate\Database\Seeder;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

class VoiceSeeder extends Seeder
{
    protected VoiceRepositoryInterface $voiceRepository;
    protected LanguageRepositoryInterface $languageRepository;

    public function __construct(VoiceRepositoryInterface $voiceRepository, LanguageRepositoryInterface $languageRepository)
    {
        $this->voiceRepository = $voiceRepository;
        $this->languageRepository = $languageRepository;
    }

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        try {
            $request = Http::get('https://freetts.ru/api/list');
            $response = $request->json();
            if ($response['status'] === TypeStatus::success->value) {
                $voicesInfo = $response['data']['voices'];
                foreach ($voicesInfo as $voice) {
                    $convertedLang = str_replace('-', '_', $voice['lang']);
                    $languageByLocale = $this->languageRepository->getLanguageByLocale($convertedLang);
                    if ($languageByLocale === null) {
                        continue;
                    }
                    $convertedSex = $voice['sex'] === 'm' ? 'male' : 'female';
                    $this->voiceRepository->saveNewVoice($voice['id'], $voice['name'], $convertedSex,true, $languageByLocale->id);
                }
            }
        } catch (ConnectionException $e) {
            logger("Произошла ошибка соединения в VoiceSeeder: {$e->getMessage()}");
        }

    }
}
