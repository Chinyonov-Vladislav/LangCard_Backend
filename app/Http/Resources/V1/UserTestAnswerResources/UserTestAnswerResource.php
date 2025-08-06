<?php

namespace App\Http\Resources\V1\UserTestAnswerResources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *     schema="UserTestAnswerResource",
 *     title="User Test Answer Resource (ресурс пользовательских ответов на вопросы к тесту)",
 *     type="object",
 *     nullable=true,
 *     @OA\Property(
 *         property="question",
 *         type="object",
 *         nullable=true,
 *         @OA\Property(property="id", type="integer", example=123),
 *         @OA\Property(property="text", type="string", example="What is the capital of France?"),
 *         @OA\Property(property="type", type="string", example="multiple_choice"),
 *         @OA\Property(
 *             property="card",
 *             type="object",
 *             nullable=true,
 *             @OA\Property(property="id", type="integer", example=456),
 *             @OA\Property(property="image_url", type="string", format="uri", example="https://example.com/images/card1.png"),
 *         ),
 *         @OA\Property(
 *             property="correct_answer",
 *             type="object",
 *             nullable=true,
 *             @OA\Property(property="id", type="integer", example=789),
 *             @OA\Property(property="text_answer", type="string", example="Paris"),
 *         ),
 *         @OA\Property(
 *             property="user_answer",
 *             type="object",
 *             nullable=true,
 *             @OA\Property(property="id", type="integer", example=1011),
 *             @OA\Property(property="text_answer", type="string", example="Paris"),
 *             @OA\Property(property="is_correct", type="boolean", example=true),
 *         )
 *     )
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
            'question' => $this->relationLoaded('question') && $this->question ? [
                'id' => $this->question->id,
                'text' => $this->question->text,
                'type' => $this->question->type,

                'card' => $this->question->relationLoaded('card') && $this->question->card ? [
                    'id' => $this->question->card->id,
                    'image_url' => $this->question->card->image_url,
                ] : null,

                'correct_answer' => $this->question->relationLoaded('correctAnswer') && $this->question->correctAnswer ? [
                    'id' => $this->question->correctAnswer->id,
                    'text_answer' => $this->question->correctAnswer->text_answer,
                ] : null,

                'user_answer' => $this->relationLoaded('questionAnswer') && $this->questionAnswer ? [
                    'id' => $this->questionAnswer->id,
                    'text_answer' => $this->questionAnswer->text_answer,
                    'is_correct' => $this->questionAnswer->is_correct,
                ] : null,

            ] : null,
        ];
    }
}
