<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\TypeFolderForFiles;
use App\Exceptions\ErrorDefiningFile;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\LanguageRequests\AddingLanguageRequest;
use App\Http\Resources\v1\LanguageResources\LanguageResource;
use App\Http\Responses\ApiResponse;
use App\Repositories\LanguageRepositories\LanguageRepositoryInterface;
use App\Services\FileServices\SaveFileService;

class LanguageController extends Controller
{
    protected LanguageRepositoryInterface $languageRepository;
    public function __construct(LanguageRepositoryInterface $languageRepository)
    {
        $this->languageRepository = $languageRepository;
    }
    public function getLanguages()
    {
        return ApiResponse::success(__('api.all_language_data'), (object)['items' => LanguageResource::collection($this->languageRepository->getAllLanguages())]);
    }
    public function addLanguage(AddingLanguageRequest $request)
    {
        $newLang = $this->languageRepository->saveLanguage($request->name, $request->native_name, $request->code, $request->locale_lang, $request->flag);
        return ApiResponse::success("Данные о новом языке успешно сохранены", (object)['language' => $newLang]);
    }
}
