<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\TypeStatus;
use App\Http\Controllers\Controller;
use App\Http\Filters\FiltersForModels\VoiceFilter;
use App\Http\Requests\Api\V1\VoiceRequests\GetVoicesRequest;
use App\Http\Resources\v1\VoiceResources\VoiceResource;
use App\Http\Responses\ApiResponse;
use App\Repositories\LanguageRepositories\LanguageRepositoryInterface;
use App\Repositories\VoiceRepositories\VoiceRepositoryInterface;
use App\Services\PaginatorService;
use Dedoc\Scramble\Attributes\QueryParameter;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

class VoiceController extends Controller
{
    protected VoiceRepositoryInterface $voiceRepository;
    protected LanguageRepositoryInterface $languageRepository;

    public function __construct(VoiceRepositoryInterface $voiceRepository, LanguageRepositoryInterface $languageRepository)
    {
        $this->voiceRepository = $voiceRepository;
        $this->languageRepository = $languageRepository;
    }

    #[QueryParameter('page', 'Номер страницы', type: 'int',default:10, example: 1)]
    #[QueryParameter('countOnPage', 'Количество элементов на странице', type: 'int',default:10, example: 10)]
    #[QueryParameter('languages', description: 'Параметр для фильтрации по языкам', type: 'string', infer: true, example: 'en_US,ru_RU,de_DE')]
    public function getVoices(PaginatorService $paginator, VoiceFilter $voiceFilter, GetVoicesRequest $request)
    {
        $useZeroDefaults = !$request->has('countOnPage') && !$request->has('page');
        $countOnPage = (int)$request->input('countOnPage',
            $useZeroDefaults ? config('app.zero_count_on_page') : config('app.default_count_on_page')
        );
        $numberCurrentPage = (int)$request->input('page',
            $useZeroDefaults ? config('app.zero_page') : config('app.default_page')
        );
        $data = $this->voiceRepository->getVoicesWithPaginationAndFilters($paginator, $voiceFilter, $countOnPage, $numberCurrentPage);
        if ($useZeroDefaults) {
            return ApiResponse::success('Данные о голосах для озвучивания текста', (object)['items' => VoiceResource::collection($data)]);
        }
        return ApiResponse::success("Данные о поддерживаемых голосах для языков на странице $numberCurrentPage", (object)['items' => VoiceResource::collection($data['items']),
            'pagination' => $data['pagination']]);
    }

    public function createVoice()
    {
        try {
            $countNewVoices = 0;
            $request = Http::get('https://freetts.ru/api/list');
            $response = $request->json();
            if ($response['status'] === TypeStatus::success->value) {
                $voicesInfo = $response['data']['voices'];
                foreach ($voicesInfo as $voice) {
                    if(!$this->voiceRepository->isExistVoice($voice['id']))
                    {
                        $convertedLang = str_replace('-', '_', $voice['lang']);
                        $languageByLocale = $this->languageRepository->getLanguageByLocale($convertedLang);
                        if ($languageByLocale === null) {
                            continue;
                        }
                        $convertedSex = $voice['sex'] === 'm' ? 'male' : 'female';
                        $this->voiceRepository->saveNewVoice($voice['id'], $voice['name'], $convertedSex,true, $languageByLocale->id);
                        $countNewVoices++;
                    }
                }
                return ApiResponse::success("Количество новых голосов = $countNewVoices");
            }
            return ApiResponse::error('Произошла ошибка при получении языков с сайта freetts.ru', null, 500);
        }
        catch (ConnectionException $e) {
            return ApiResponse::error('Произошла ошибка при получении языков с сайта freetts.ru: ' . $e->getMessage(), null, 500);
        }
    }

    public function updateStatusOfVoices()
    {
        try {
            $voiceIdNewNonActiveLanguages = [];
            $dataVoices = $this->voiceRepository->getVoicesWithPaginationAndFilters();
            $request = Http::get('https://freetts.ru/api/list');
            $response = $request->json();
            if ($response['status'] === TypeStatus::success->value) {
                $voicesInfo = $response['data']['voices'];
                //unset($voicesInfo[3]);
                foreach ($dataVoices as $voiceFromDb) {
                    $isFound = false;
                    foreach ($voicesInfo as $voiceFromSite) {
                        if($voiceFromSite['id'] === $voiceFromDb->voice_id) {
                            $isFound = true;
                            break;
                        }
                    }
                    if(!$isFound) {
                        $this->voiceRepository->updateStatusActive($voiceFromDb->voice_id, false);
                        $voiceIdNewNonActiveLanguages[] = $voiceFromDb->voice_id;
                    }
                    else
                    {
                        if(!$voiceFromDb->is_active)
                        {
                            $this->voiceRepository->updateStatusActive($voiceFromDb->voice_id, true);
                            $voiceIdNewNonActiveLanguages[] = $voiceFromDb->voice_id;
                        }
                    }
                }
                $items = $this->voiceRepository->getVoicesByVoiceId($voiceIdNewNonActiveLanguages);
                return ApiResponse::success('Изменения статусов языков', (object)['items'=>VoiceResource::collection($items)]);
            }
            return ApiResponse::error('Произошла ошибка при получении языков с сайта freetts.ru', null, 500);
        } catch (ConnectionException $e) {
            return ApiResponse::error('Произошла ошибка при получении языков с сайта freetts.ru: ' . $e->getMessage(), null, 500);
        }
    }
}
