<?php

namespace App\Http\Resources\V1\QuestionResources;

use App\Enums\TypeQuestionInTest;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *     schema="QuestionResource",
 *     type="object",
 *     title="Question Resource",
 *     description="Ресурс вопроса с опциональными вложенными данными",
 *     required={"id", "type", "text"},
 *     @OA\Property(
 *         property="id",
 *         type="integer",
 *         example=101,
 *         description="Уникальный идентификатор вопроса"
 *     ),
 *     @OA\Property(
 *         property="type",
 *         type="string",
 *         example="translate",
 *         description="Тип вопроса"
 *     ),
 *     @OA\Property(
 *         property="text",
 *         type="string",
 *         example="Переведите слово",
 *         description="Текст вопроса"
 *     ),
 *     @OA\Property(
 *         property="card",
 *         type="object",
 *         nullable=true,
 *         description="Карточка, связанная с вопросом",
 *         @OA\Property(
 *             property="word",
 *             type="string",
 *             nullable=true,
 *             example="apple",
 *             description="Слово из карточки (только если type = 'translate')"
 *         ),
 *         @OA\Property(
 *             property="image_url",
 *             type="string",
 *             format="uri",
 *             example="https://example.com/images/apple.png",
 *             description="URL изображения карточки"
 *         )
 *     ),
 *     @OA\Property(
 *         property="answers",
 *         type="array",
 *         nullable=true,
 *         description="Список вариантов ответов",
 *         @OA\Items(
 *             type="object",
 *             required={"id", "text"},
 *             @OA\Property(
 *                 property="id",
 *                 type="integer",
 *                 example=201,
 *                 description="Идентификатор ответа"
 *             ),
 *             @OA\Property(
 *                 property="text",
 *                 type="string",
 *                 example="яблоко",
 *                 description="Текст ответа"
 *             )
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
            'card'=>$this->relationLoaded('card') && $this->card ?
                [
                    'word'=>$this->type === TypeQuestionInTest::translate->value ? $this->card->word : null,
                    'image_url'=>$this->card->image_url,
                ]: null,
            'answers'=>$this->relationLoaded('answers') && $this->answers ?
                $this->answers->map(fn($answer) => [
                    'id'=>$answer->id,
                    'text'=>$answer->text_answer,
                ]) : null
        ];
    }
}
