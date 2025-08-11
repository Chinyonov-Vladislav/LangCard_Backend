<?php

namespace App\Http\Resources\V1\UserTestAnswerResources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *     schema="UserTestAnswerResource",
 *     title="User Test Answer Resource (ресурс пользовательских ответов на вопросы к тесту)",
 *     type="object",
 *     @OA\Property(
 *         property="question",
 *         type="object",
 *         description="Вопрос теста",
 *          required = {"id", "text", "type"},
 *         @OA\Property(property="id", type="integer", example=123),
 *         @OA\Property(property="text", type="string", example="Как переводится слово 'apple'?"),
 *         @OA\Property(property="type", type="string", example="translate"),
 *
 *         @OA\Property(
 *             property="card",
 *             type="object",
 *             description="Карточка с изображением и словом",
 *             required = {"id", "image_url"},
 *             @OA\Property(property="id", type="integer", example=456),
 *             @OA\Property(property="image_url", type="string", format="uri", example="https://example.com/image.jpg"),
 *         ),
 *
 *         @OA\Property(
 *             property="correct_answer",
 *             type="object",
 *             description="Правильный ответ на вопрос",
 *             required = {"id", "text_answer"},
 *             @OA\Property(property="id", type="integer", example=789),
 *             @OA\Property(property="text_answer", type="string", example="яблоко"),
 *         ),
 *
 *         @OA\Property(
 *             property="user_answer",
 *             type="object",
 *             nullable=true,
 *             description="Ответ пользователя",
 *             required = {"id", "text_answer","is_correct"},
 *             @OA\Property(property="id", type="integer", example=1011),
 *             @OA\Property(property="text_answer", type="string", example="яблоко"),
 *             @OA\Property(property="is_correct", type="boolean", example=true),
 *         ),
 *     ),
 * )
 */
class UserTestAnswerResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'question' => $this->whenLoaded('question', function () {
                $question = $this->question;
                return [
                    'id' => $question->id,
                    'text' => $question->text,
                    'type' => $question->type,

                    'card' => $this->question->whenLoaded('card', function () use ($question) {
                        $card = $question->card;
                        return [
                            'id' => $card->id,
                            'image_url' => $card->image_url,
                        ];
                    }),

                    'correct_answer' => $this->question->whenLoaded('correctAnswer', function () use ($question) {
                        $correctAnswer = $question->correctAnswer;
                        return [
                            'id' => $correctAnswer->id,
                            'text_answer' => $correctAnswer->text_answer,
                        ];
                    }),

                    'user_answer' => $this->whenLoaded('questionAnswer', function () {
                        $userAnswer = $this->questionAnswer;
                        if($userAnswer === null)
                        {
                            return null;
                        }
                        return [
                            'id' => $userAnswer->id,
                            'text_answer' => $userAnswer->text_answer,
                            'is_correct' => $userAnswer->is_correct,
                        ];
                    }),
                ];
            }),
        ];
    }
}
