<?php

namespace App\Http\Controllers\Api\V1\ChatControllers;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\EmotionResources\EmotionResource;
use App\Http\Responses\ApiResponse;
use App\Repositories\EmotionRepositories\EmotionRepositoryInterface;

class EmotionController extends Controller
{
    protected EmotionRepositoryInterface $emotionRepository;

    public function __construct(EmotionRepositoryInterface $emotionRepository)
    {
        $this->emotionRepository = $emotionRepository;
    }
    public function getEmotions()
    {
        $emotions = $this->emotionRepository->getEmotions();
        return ApiResponse::success("Эмоции", (object)['items'=>EmotionResource::collection($emotions)]);
    }
}
