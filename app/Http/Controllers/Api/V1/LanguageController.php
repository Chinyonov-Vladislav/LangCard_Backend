<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\v1\LanguageResources\LanguageResource;
use App\Http\Responses\ApiResponse;
use App\Repositories\LanguageRepositories\LanguageRepositoryInterface;

class LanguageController extends Controller
{
    protected LanguageRepositoryInterface $languageRepository;

    public function __construct(LanguageRepositoryInterface $languageRepository)
    {
        $this->languageRepository = $languageRepository;
    }

    public function getLanguages()
    {
        return ApiResponse::success(__('api.all_language_data'), (object)['items'=>LanguageResource::collection($this->languageRepository->getAllLanguages())]);
    }
}
