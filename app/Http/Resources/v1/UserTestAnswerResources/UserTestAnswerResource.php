<?php

namespace App\Http\Resources\v1\UserTestAnswerResources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

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
            'question' => $this->relationLoaded('question') ? [
                'id' => $this->question->id,
                'text' => $this->question->text,
                'type' => $this->question->type,

                'card' => $this->question->relationLoaded('card') ? [
                    'id' => $this->question->card->id,
                    'image_url' => $this->question->card->image_url,
                ] : null,

                'correct_answer' => $this->question->relationLoaded('correctAnswer') ? [
                    'id' => $this->question->correctAnswer->id,
                    'text_answer' => $this->question->correctAnswer->text_answer,
                ] : null,

                'user_answer' => $this->relationLoaded('questionAnswer') ? [
                    'id' => $this->questionAnswer->id,
                    'text_answer' => $this->questionAnswer->text_answer,
                    'is_correct' => $this->questionAnswer->is_correct,
                ] : null,

            ] : null,
        ];
    }
}
