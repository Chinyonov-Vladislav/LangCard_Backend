<?php

namespace App\Http\Controllers;

use App\Http\Resources\V1\EmotionResources\EmotionResource;
use App\Http\Responses\ApiResponse;
use App\Repositories\EmotionRepositories\EmotionRepositoryInterface;
use Illuminate\Http\Request;

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
