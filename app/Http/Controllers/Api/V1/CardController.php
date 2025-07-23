<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\TypeFiles;
use App\Enums\TypeInfoAboutDeck;
use App\Enums\TypeStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\CardRequests\CreatingCardForDeckRequest;
use App\Http\Responses\ApiResponse;
use App\Models\Deck;
use App\Repositories\AudiofileRepositories\AudiofileRepositoryInterface;
use App\Repositories\CardRepositories\CardRepositoryInterface;
use App\Repositories\DeckRepositories\DeckRepositoryInterface;
use App\Repositories\VoiceRepositories\VoiceRepositoryInterface;
use App\Services\DownloadFileService;
use App\Services\SaveFileService;
use App\Services\TextToSpeechService;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class CardController extends Controller
{
    protected SaveFileService $cardFileService;
    protected DeckRepositoryInterface $deckRepository;

    protected CardRepositoryInterface $cardRepository;

    protected VoiceRepositoryInterface $voiceRepository;

    protected AudiofileRepositoryInterface $audiofileRepository;

    public function __construct(DeckRepositoryInterface $deckRepository,
                                CardRepositoryInterface $cardRepository,
                                VoiceRepositoryInterface $voiceRepository,
                                AudiofileRepositoryInterface $audiofileRepository)
    {
        $this->deckRepository = $deckRepository;
        $this->cardRepository = $cardRepository;
        $this->voiceRepository = $voiceRepository;
        $this->audiofileRepository = $audiofileRepository;
        $this->cardFileService = new SaveFileService();
    }
    public function createCardForDeck(CreatingCardForDeckRequest $request)
    {
        try {
            $originalLanguagesArray = array_map('trim', explode(",", $request->originalVoices));
            $targetLanguagesArray = array_map('trim', explode(",", $request->targetVoices));
            $deckInfo = $this->deckRepository->getDeckById($request->deck_id, TypeInfoAboutDeck::maximum);
            if ($deckInfo === null) {
                return ApiResponse::error('Колода не найдена', null, 404);
            }
            if ($deckInfo->user_id !== auth()->id()) {
                return ApiResponse::error('Авторизованный пользователь не является создателем колоды', null, 403);
            }
            $imagePath = null;
            if (isset($request->image) && $request->image instanceof UploadedFile) {
                $imagePath = $this->cardFileService->storeFile(
                    $request->image,
                    $request->deck_id,
                    TypeFiles::image
                );
            }
            logger($originalLanguagesArray);
            logger($targetLanguagesArray);
            DB::beginTransaction();
            $newCard = $this->cardRepository->saveNewCard($request->word, $request->translate, $imagePath, $request->deck_id);
            $originalVoices = $this->voiceRepository->getVoicesByVoiceId($originalLanguagesArray);
            $targetVoices = $this->voiceRepository->getVoicesByVoiceId($targetLanguagesArray);
            $pathToFilesOriginalLanguages = $this->generatePronunciationFiles($originalVoices, $deckInfo, $request->word, 'original');
            $pathToFilesTargetLanguages = $this->generatePronunciationFiles($targetVoices, $deckInfo, $request->translate, 'target');
            logger($pathToFilesOriginalLanguages);
            logger($pathToFilesTargetLanguages);
            foreach ($pathToFilesOriginalLanguages as $path) {
                $this->audiofileRepository->saveNewAudiofile($path, 'original', $newCard->id);
            }
            foreach ($pathToFilesTargetLanguages as $path) {
                $this->audiofileRepository->saveNewAudiofile($path, 'target', $newCard->id);
            }
            DB::commit();
            return ApiResponse::success("Карточки для колоды с id = $request->deck_id были успешно созданы");
        } catch (Exception $e) {
            DB::rollBack();
            return ApiResponse::error($e->getMessage(), null, 500);
        }
    }
    private function generatePronunciationFiles(Collection $voices, Deck $deckInfo, string $wordForInsonation, string $type)
    {
        $pathsToFiles = [];
        $textToSpeechService = new TextToSpeechService();
        $downloadFileService = new DownloadFileService();
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
                    $pathOnServer = $downloadFileService->downloadAudioFile($result->url_download);
                    $pathsToFiles[] = $pathOnServer;
                } catch (Exception $e) {
                    logger("Произошла ошибка при скачивании файла: ".$e->getMessage());
                }
            }
        }
        return $pathsToFiles;
    }
}
