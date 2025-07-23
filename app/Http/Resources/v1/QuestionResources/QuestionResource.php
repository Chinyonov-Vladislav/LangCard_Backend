<?php

namespace App\Http\Resources\v1\QuestionResources;

use App\Enums\TypeQuestionInTest;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

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
            'card'=>$this->when($this->relationLoaded('card'), function() {
                return [
                    'word'=>$this->when($this->type === TypeQuestionInTest::translate->value, function (){
                        return $this->card->word;
                    }),
                    'image_url'=>$this->card->image_url,
                ];
            }),
            'answers'=>$this->when($this->relationLoaded('answers'), function(){
                return $this->answers->map(fn($answer) => [
                    'id'=>$answer->id,
                    'text'=>$answer->text_answer,

                ]);
            })
        ];
    }
}
