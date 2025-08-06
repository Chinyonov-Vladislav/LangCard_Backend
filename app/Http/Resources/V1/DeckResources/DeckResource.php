<?php

namespace App\Http\Resources\V1\DeckResources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *     schema="DeckResource",
 *     title="Deck Resource (Ресурс колоды)",
 *     description="Ресурс колоды с карточками, языками, тестами и пользователем",
 *     type="object",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="name", type="string", example="Английский для начинающих"),
 *     @OA\Property(property="is_premium", type="boolean", example=true),
 *     @OA\Property(property="creation_time", type="string", format="date-time", example="2025-08-06 15:23:12"),
 *     @OA\Property(property="visitors_count", type="integer", example=120),
 *     @OA\Property(property="tests_count", type="integer", example=4),
 *     @OA\Property(property="cards_count", type="integer", example=150),
 *     @OA\Property(
 *         property="original_language",
 *         type="object",
 *         nullable=true,
 *         @OA\Property(property="id", type="integer", example=1),
 *         @OA\Property(property="name", type="string", example="Английский"),
 *         @OA\Property(property="code", type="string", example="en"),
 *         @OA\Property(property="flag_url", type="string", format="url", example="https://example.com/flags/en.svg")
 *     ),
 *     @OA\Property(
 *         property="target_language",
 *         type="object",
 *         nullable=true,
 *         @OA\Property(property="id", type="integer", example=2),
 *         @OA\Property(property="name", type="string", example="Русский"),
 *         @OA\Property(property="code", type="string", example="ru"),
 *         @OA\Property(property="flag_url", type="string", format="url", example="https://example.com/flags/ru.svg")
 *     ),
 *     @OA\Property(
 *         property="user",
 *         type="object",
 *         nullable=true,
 *         @OA\Property(property="id", type="integer", example=10),
 *         @OA\Property(property="avatar_url", type="string", format="url", example="https://example.com/avatars/user.png"),
 *         @OA\Property(property="name", type="string", example="Иван Иванов")
 *     ),
 *     @OA\Property(
 *         property="topics",
 *         type="array",
 *         nullable=true,
 *         @OA\Items(
 *             type="object",
 *             @OA\Property(property="id", type="integer", example=1),
 *             @OA\Property(property="name", type="string", example="Путешествия")
 *         )
 *     ),
 *     @OA\Property(
 *         property="tests",
 *         type="array",
 *         nullable=true,
 *         @OA\Items(
 *             type="object",
 *             @OA\Property(property="id", type="integer", example=1),
 *             @OA\Property(property="name", type="string", example="Тест 1"),
 *             @OA\Property(property="time_seconds", type="integer", example=300),
 *             @OA\Property(property="count_attempts", type="integer", example=3),
 *             @OA\Property(property="creation_time", type="string", format="date-time", example="2025-08-06 15:00:00"),
 *             @OA\Property(property="authorized_user_attempts", type="integer", nullable=true, example=2)
 *         )
 *     ),
 *     @OA\Property(
 *         property="cards",
 *         type="array",
 *         nullable=true,
 *         @OA\Items(
 *             type="object",
 *             @OA\Property(property="id", type="integer", example=100),
 *             @OA\Property(property="word", type="string", example="Apple"),
 *             @OA\Property(property="translate", type="string", example="Яблоко"),
 *             @OA\Property(property="image_url", type="string", format="url", example="https://example.com/images/apple.png"),
 *             @OA\Property(property="creation_time", type="string", format="date-time", example="2025-08-06 15:20:00"),
 *             @OA\Property(
 *                 property="original_word_audiofiles",
 *                 type="array",
 *                 nullable=true,
 *                 @OA\Items(
 *                     type="object",
 *                     @OA\Property(property="id", type="integer", example=1),
 *                     @OA\Property(property="path", type="string", example="/audio/en/apple.mp3")
 *                 )
 *             ),
 *             @OA\Property(
 *                 property="target_word_audiofiles",
 *                 type="array",
 *                 nullable=true,
 *                 @OA\Items(
 *                     type="object",
 *                     @OA\Property(property="id", type="integer", example=2),
 *                     @OA\Property(property="path", type="string", example="/audio/ru/apple.mp3")
 *                 )
 *             )
 *         )
 *     )
 * )
 */
class DeckResource extends JsonResource
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
            'name' => $this->name,
            'is_premium' => (bool)$this->is_premium,
            'creation_time' => $this->created_at->toDateTimeString(),
            "visitors_count" => $this->visitors_count,
            "tests_count" => $this->tests_count,
            'cards_count' => $this->cards_count,
            'original_language' => $this->relationLoaded('originalLanguage') && $this->originalLanguage ?
                [
                    'id' => $this->originalLanguage->id,
                    'name' => $this->originalLanguage->name,
                    'code' => $this->originalLanguage->code,
                    'flag_url' => $this->originalLanguage->flag_url,
                ] : null,
            'target_language' => $this->relationLoaded('targetLanguage') && $this->targetLanguage ?
                [
                    'id' => $this->targetLanguage->id,
                    'name' => $this->targetLanguage->name,
                    'code' => $this->targetLanguage->code,
                    'flag_url' => $this->targetLanguage->flag_url
                ] : null,
            'user' => $this->relationLoaded('user') && $this->user ?
                [
                    'id' => $this->user->id,
                    'avatar_url' => $this->user->avatar_url,
                    'name' => $this->user->name,
                ] : null,
            'topics' => $this->relationLoaded('topics') && $this->topics  ?
                $this->topics->map(fn($topic) => [
                'id' => $topic->id,
                'name' => $topic->name,
            ]) : null,
            'tests' => $this->relationLoaded('tests') && $this->tests ?
                $this->tests->map(fn($test) => [
                    // Структура данных для каждого теста
                    'id' => $test->id,
                    'name' => $test->name,
                    "time_seconds" => $test->time_seconds,
                    "count_attempts" => $test->count_attempts,
                    "creation_time" => $test->created_at->toDateTimeString(),
                    'authorized_user_attempts' => $test->authorized_user_attempts ?? null]) : null,
            'cards' => $this->relationLoaded('cards') && $this->cards ?
                $this->cards->map(fn($card) => [
                    'id' => $card->id,
                    'word' => $card->word,
                    'translate' => $card->translate,
                    'image_url' => $card->image_url,
                    'creation_time' => $card->created_at->toDateTimeString(),
                    'original_word_audiofiles' => $card->relationLoaded('audiofilesForOriginalWord') && $card->audiofilesForOriginalWord ?
                        $card->audiofilesForOriginalWord->map(fn($audiofile) => [
                            'id' => $audiofile->id,
                            'path' => $audiofile->path,
                        ])
                        : null,
                    'target_word_audiofiles' => $card->relationLoaded('audiofilesForTargetWord') && $card->audiofilesForTargetWord ?
                        $card->audiofilesForTargetWord->map(fn($audiofile) => [
                            'id' => $audiofile->id,
                            'path' => $audiofile->path,
                        ])
                        : null,
                ])
                : null,
        ];
    }
}
