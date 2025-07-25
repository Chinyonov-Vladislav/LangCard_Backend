<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\TypeFolderForFiles;
use App\Enums\TypeInfoAboutDeck;
use App\Enums\TypeStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\CardRequests\CreatingCardForDeckRequest;
use App\Http\Responses\ApiResponse;
use App\Models\Deck;
use App\Repositories\AudiofileRepositories\AudiofileRepositoryInterface;
use App\Repositories\CardRepositories\CardRepositoryInterface;
use App\Repositories\DeckRepositories\DeckRepositoryInterface;
use App\Repositories\ExampleRepositories\ExampleRepositoryInterface;
use App\Repositories\VoiceRepositories\VoiceRepositoryInterface;
use App\Services\FileServices\AudioProcessingService;
use App\Services\FileServices\DownloadFileService;
use App\Services\FileServices\SaveFileService;
use App\Services\TextToSpeechService;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Throwable;

class CardController extends Controller
{
    protected DeckRepositoryInterface $deckRepository;

    protected CardRepositoryInterface $cardRepository;

    protected VoiceRepositoryInterface $voiceRepository;

    protected AudiofileRepositoryInterface $audiofileRepository;

    protected ExampleRepositoryInterface $exampleRepository;

    protected DownloadFileService $downloadFileService;
    protected SaveFileService $saveFileService;

    protected AudioProcessingService  $audioProcessingService;

    public function __construct(DeckRepositoryInterface $deckRepository,
                                CardRepositoryInterface $cardRepository,
                                VoiceRepositoryInterface $voiceRepository,
                                AudiofileRepositoryInterface $audiofileRepository,
                                ExampleRepositoryInterface $exampleRepository)
    {
        $this->deckRepository = $deckRepository;
        $this->cardRepository = $cardRepository;
        $this->voiceRepository = $voiceRepository;
        $this->audiofileRepository = $audiofileRepository;
        $this->exampleRepository = $exampleRepository;
        $this->downloadFileService = new DownloadFileService();
        $this->saveFileService = new SaveFileService();
        $this->audioProcessingService = new AudioProcessingService();
    }
    public function createCardForDeck(CreatingCardForDeckRequest $request)
    {
        try {
            $originalVoices = array_unique($request->originalVoices);
            $targetVoices = array_unique($request->targetVoices);
            $deckInfo = $this->deckRepository->getDeckById($request->deck_id, TypeInfoAboutDeck::maximum);
            if ($deckInfo === null) {
                return ApiResponse::error('Колода не найдена', null, 404);
            }
            if ($deckInfo->user_id !== auth()->id()) {
                return ApiResponse::error('Авторизованный пользователь не является создателем колоды', null, 403);
            }
            DB::beginTransaction();
            $newCard = $this->cardRepository->saveNewCard($request->word, $request->translate, $request->imagePath, $request->deck_id);
            $originalVoices = $this->voiceRepository->getVoicesByVoiceId($originalVoices);
            $targetVoices = $this->voiceRepository->getVoicesByVoiceId($targetVoices);
            $resultGenerationFilesPronunciationForOriginalLanguages = $this->generatePronunciationFiles($originalVoices, $deckInfo, $request->word, 'original');
            $resultGenerationFilesPronunciationForTargetLanguages = $this->generatePronunciationFiles($targetVoices, $deckInfo, $request->translate, 'target');
            foreach ($resultGenerationFilesPronunciationForOriginalLanguages as $path) {
                $this->audiofileRepository->saveNewAudiofile($path, 'original', $newCard->id);
            }
            foreach ($resultGenerationFilesPronunciationForTargetLanguages as $path) {
                $this->audiofileRepository->saveNewAudiofile($path, 'target', $newCard->id);
            }
            $examples = array_unique($request->examples);
            foreach ($examples as $example) {
                $this->exampleRepository->saveNewExample($example, $newCard->id);
            }
            DB::commit();
            return ApiResponse::success("Карточки для колоды с id = $request->deck_id были успешно созданы");
        } catch (Throwable $e) {
            DB::rollBack();
            return ApiResponse::error($e->getMessage(), null, 500);
        }
    }

    private function generatePronunciationFiles(Collection $voices, Deck $deckInfo, string $wordForInsonation, string $type, bool $isConvertTo2Channel = false)
    {
        $pathsToFiles = [];
        $textToSpeechService = new TextToSpeechService();
        foreach ($voices as $voice) {
            if($type === 'original' and $voice->language->code !== $deckInfo->originalLanguage->code){
                continue;
            }
            if($type === 'target' and $voice->language->code !== $deckInfo->targetLanguage->code){
                continue;
            }
            $result = $textToSpeechService->getUrlForGeneratedAudio($wordForInsonation, $voice->language->code,$voice->voice_id);
            if($result->status === TypeStatus::success->value)
            {
                try {
                    $file = $this->downloadFileService->downloadFile($result->url_download);
                    if($isConvertTo2Channel) {
                        $temporaryFilePath = $this->saveFileService->saveFile($file);
                        $convertedAudio = $this->audioProcessingService->convertToStereo($temporaryFilePath);
                        if ($convertedAudio !== false) {
                            $pathsToFiles[] = $this->saveFileService->saveFile($convertedAudio);
                        }
                    }
                    else
                    {
                        $pathsToFiles[] = $this->saveFileService->saveFile($file);
                    }
                } catch (Exception $e) {
                    logger("Произошла ошибка при скачивании файла по ссылке $result->url_download. Текст ошибки: {$e->getMessage()}");
                }
            }
        }
        return $pathsToFiles;
    }
}
