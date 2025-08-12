<?php

namespace App\Jobs;

use App\Enums\JobStatuses;
use App\Enums\TypeStatus;
use App\Repositories\AudiofileRepositories\AudiofileRepositoryInterface;
use App\Repositories\CardRepositories\CardRepositoryInterface;
use App\Repositories\VoiceRepositories\VoiceRepositoryInterface;
use App\Services\FileServices\AudioProcessingService;
use App\Services\FileServices\DownloadFileService;
use App\Services\FileServices\SaveFileService;
use App\Services\TextToSpeechService;
use Exception;

class GeneratingVoiceJob extends BaseJob
{

    protected DownloadFileService $downloadFileService;
    protected SaveFileService $saveFileService;

    protected AudioProcessingService $audioProcessingService;

    protected array $originalVoices;
    protected array $targetVoices;

    protected int $cardId;


    /**
     * Create a new job instance.
     */
    public function __construct(?string $jobId, array $originalVoices, array $targetVoices, int $cardId)
    {
        parent::__construct($jobId);
        $this->originalVoices = array_unique($originalVoices);
        $this->targetVoices = array_unique($targetVoices);
        $this->cardId = $cardId;
        $this->downloadFileService = new DownloadFileService();
        $this->saveFileService = new SaveFileService();
        $this->audioProcessingService = new AudioProcessingService();
    }

    /**
     * Execute the job.
     */
    public function handle(AudiofileRepositoryInterface $audiofileRepository, CardRepositoryInterface $cardRepository, VoiceRepositoryInterface $voiceRepository): void
    {
        try {
            $this->updateJobStatus(JobStatuses::processing->value);
            $card = $cardRepository->getCardById($this->cardId, ['deck'=>function ($query) {
                $query->with(['originalLanguage', 'targetLanguage']);
            }]);
            if($card === null)
            {
                $this->updateJobStatus(JobStatuses::failed->value, ["message"=>"Карточка с id = $this->cardId не найдена"]);
                return;
            }
            $originalVoices = $voiceRepository->getVoicesByVoiceId($this->originalVoices);
            $targetVoices = $voiceRepository->getVoicesByVoiceId($this->targetVoices);
            $voicesInfo = [];
            foreach ($originalVoices as $voice)
            {
                if ($voice->language->code !== $card->deck->originalLanguage->code || $voice->is_active === false) {
                    continue;
                }
                $voicesInfo[] = (object)['text'=>$card->word,
                    'lang'=>$voice->language->code,
                    'voiceId'=>$voice->voice_id,
                    'destination'=>"original",
                    'voice_name'=>$voice->voice_name];
            }
            foreach ($targetVoices as $voice)
            {
                if ($voice->language->code !== $card->deck->targetLanguage->code || $voice->is_active === false) {
                    continue;
                }
                $voicesInfo[] = (object)['text'=>$card->translate,
                    'lang'=>$voice->language->code,
                    'voiceId'=>$voice->voice_id,
                    'destination'=>"target",
                    'voice_name'=>$voice->voice_name];
            }
            $textToSpeechService = new TextToSpeechService();
            $result = $textToSpeechService->getUrlsForGeneratedAudio($voicesInfo);
            $isConvertTo2Channel = false;
            $errorsGeneratingFiles = [];
            $successGeneratingFiles = [];
            foreach ($result as $item) {
                if($item->status === TypeStatus::success->value)
                {
                    $file = $this->downloadFileService->downloadFile($item->url_download);
                    if ($isConvertTo2Channel) {
                        $temporaryFilePath = $this->saveFileService->saveFile($file);
                        $convertedAudio = $this->audioProcessingService->convertToStereo($temporaryFilePath);
                        if ($convertedAudio !== false) {
                            $path = $this->saveFileService->saveFile($convertedAudio);
                        }
                        else
                        {
                            $path = $this->saveFileService->saveFile($file);
                        }
                    } else {
                        $path = $this->saveFileService->saveFile($file);
                    }
                    $audiofileRepository->saveNewAudiofile($path, $item->destination, $this->cardId);
                    $successGeneratingFiles[] = "Генерация озвучки для текста \"$item->text\" с использованием голоса $item->voice_name прошла успешно";
                }
                else
                {
                    $errorsGeneratingFiles[] = "Произошла ошибка при генерации озвучки для текста \"$item->text\" с использованием голоса $item->voice_name";
                }
            }
            $resultArrayMessagesGeneratingFiles = array_merge($errorsGeneratingFiles, $successGeneratingFiles);
            if(count($errorsGeneratingFiles) === 0)
            {
                $this->updateJobStatus(JobStatuses::finished->value, $resultArrayMessagesGeneratingFiles);
            }
            else
            {
                $this->updateJobStatus(JobStatuses::failed->value, $resultArrayMessagesGeneratingFiles);
            }
        }
        catch (Exception $exception) {
            $this->updateJobStatus(JobStatuses::failed->value, ["message"=>"Ошибка при генерации озвучки: {$exception->getMessage()}"]);
        }
    }
}
