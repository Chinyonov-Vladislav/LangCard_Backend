<?php

namespace App\Http\Resources\V1\QuestionResources;

use App\Enums\TypeQuestionInTest;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *     schema="QuestionResource",
 *     title="Question Resource (ресурс вопроса)",
 *     type="object",
 *     required={"id", "type", "text"},
 *     @OA\Property(property="id", type="integer", example=123, description="ID вопроса"),
 *     @OA\Property(property="type", type="string", example="translate", description="Тип вопроса"),
 *     @OA\Property(property="text", type="string", example="Переведите слово", description="Текст вопроса"),
 *
 *     @OA\Property(
 *         property="card",
 *         type="object",
 *         description="Карточка, связанная с вопросом (опционально, если загружена)",
 *         @OA\Property(property="word", type="string", nullable=true, example="cat", description="Слово (только для типа 'translate', иначе null)"),
 *         @OA\Property(property="image_url", type="string", format="uri", nullable=true, example="https://example.com/image.jpg", description="URL изображения карточки")
 *     ),
 *
 *     @OA\Property(
 *         property="answers",
 *         type="array",
 *         description="Варианты ответов (опционально, если загружены)",
 *         @OA\Items(
 *             type="object",
 *             required={"id", "text"},
 *             @OA\Property(property="id", type="integer", example=10, description="ID варианта ответа"),
 *             @OA\Property(property="text", type="string", example="Кошка", description="Текст варианта ответа")
 *         )
 *     )
 * )
 */



class QuestionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'type'=>$this->type,
            'text'=>$this->text,
            'card'=>$this->whenLoaded('card', function (){
                $card = $this->card;
                return [
                    'word'=>$this->type === TypeQuestionInTest::translate->value ? $card->word : null,
                    'image_url'=>$card->image_url,
                ];
            }),
            'answers'=>$this->whenLoaded('answers', function () {
                $answers = $this->answers;
                return $answers->map(fn ($answer) => [
                    'id'=>$answer->id,
                    'text'=>$answer->text_answer
                ]);
            }),
        ];
    }
}
