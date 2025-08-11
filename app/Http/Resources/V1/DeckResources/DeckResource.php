<?php

namespace App\Http\Resources\V1\DeckResources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *     schema="DeckResource",
 *     title="Deck Resource (Ресурс колоды)",
 *     type="object",
 *     required={"id", "name", "is_premium", "creation_time", "visitors_count", "tests_count", "cards_count"},
 *     @OA\Property(property="id", type="integer", example=10, description="ID колоды"),
 *     @OA\Property(property="name", type="string", example="Основная колода", description="Название колоды"),
 *     @OA\Property(property="is_premium", type="boolean", example=false, description="Является ли колода премиум"),
 *     @OA\Property(property="creation_time", type="string", format="date-time", example="2025-08-11T12:34:56", description="Дата и время создания"),
 *     @OA\Property(property="visitors_count", type="integer", example=100, description="Количество посетителей"),
 *     @OA\Property(property="tests_count", type="integer", example=5, description="Количество тестов в колоде"),
 *     @OA\Property(property="cards_count", type="integer", example=50, description="Количество карточек в колоде"),
 *
 *     @OA\Property(
 *         property="original_language",
 *         type="object",
 *         description="Исходный язык колоды",
 *         required={"id", "name", "code", "flag_url"},
 *         @OA\Property(property="id", type="integer", example=1),
 *         @OA\Property(property="name", type="string", example="English"),
 *         @OA\Property(property="code", type="string", example="en"),
 *         @OA\Property(property="flag_url", type="string", format="uri", example="https://example.com/flags/en.png"),
 *     ),
 *
 *     @OA\Property(
 *         property="target_language",
 *         type="object",
 *         description="Целевой язык колоды",
 *         required={"id", "name", "code", "flag_url"},
 *         @OA\Property(property="id", type="integer", example=2),
 *         @OA\Property(property="name", type="string", example="Russian"),
 *         @OA\Property(property="code", type="string", example="ru"),
 *         @OA\Property(property="flag_url", type="string", format="uri", example="https://example.com/flags/ru.png"),
 *     ),
 *
 *     @OA\Property(
 *         property="user",
 *         type="object",
 *         description="Пользователь - владелец колоды",
 *         required={"id", "avatar_url", "name"},
 *         @OA\Property(property="id", type="integer", example=42),
 *         @OA\Property(property="avatar_url", type="string", format="uri", example="https://example.com/avatar.jpg"),
 *         @OA\Property(property="name", type="string", example="Владислав"),
 *     ),
 *
 *     @OA\Property(
 *         property="topics",
 *         type="array",
 *         description="Список тем, связанных с колодой",
 *         @OA\Items(
 *             type="object",
 *             required={"id", "name"},
 *             @OA\Property(property="id", type="integer", example=3),
 *             @OA\Property(property="name", type="string", example="Грамматика"),
 *         ),
 *     ),
 *
 *     @OA\Property(
 *         property="tests",
 *         type="array",
 *         description="Список тестов колоды",
 *         @OA\Items(
 *             type="object",
 *             required={"id", "name", "time_seconds", "count_attempts", "creation_time"},
 *             @OA\Property(property="id", type="integer", example=101),
 *             @OA\Property(property="name", type="string", example="Тест №1"),
 *             @OA\Property(property="time_seconds", type="integer", example=900, description="Время на прохождение теста в секундах"),
 *             @OA\Property(property="count_attempts", type="integer", example=3, description="Максимальное количество попыток"),
 *             @OA\Property(property="creation_time", type="string", format="date-time", example="2025-08-01T10:00:00"),
 *             @OA\Property(property="authorized_user_attempts", type="integer", nullable=true, example=1, description="Количество попыток пользователя (опционально)"),
 *         ),
 *     ),
 *
 *     @OA\Property(
 *         property="cards",
 *         type="array",
 *         description="Список карточек колоды",
 *         @OA\Items(
 *             type="object",
 *             required={"id", "word", "translate", "creation_time"},
 *             @OA\Property(property="id", type="integer", example=555),
 *             @OA\Property(property="word", type="string", example="Hello"),
 *             @OA\Property(property="translate", type="string", example="Привет"),
 *             @OA\Property(property="image_url", type="string", format="uri", nullable=true, example="https://example.com/images/hello.png"),
 *             @OA\Property(property="creation_time", type="string", format="date-time", example="2025-08-10T15:45:00"),
 *
 *             @OA\Property(
 *                 property="original_word_audiofiles",
 *                 type="array",
 *                 description="Аудиофайлы для исходного слова",
 *                 @OA\Items(
 *                     type="object",
 *                     required={"id", "path"},
 *                     @OA\Property(property="id", type="integer", example=1001),
 *                     @OA\Property(property="path", type="string", example="/audio/original/hello.mp3"),
 *                 ),
 *             ),
 *
 *             @OA\Property(
 *                 property="target_word_audiofiles",
 *                 type="array",
 *                 description="Аудиофайлы для целевого слова",
 *                 @OA\Items(
 *                     type="object",
 *                     required={"id", "path"},
 *                     @OA\Property(property="id", type="integer", example=1002),
 *                     @OA\Property(property="path", type="string", example="/audio/target/hello.mp3"),
 *                 ),
 *             ),
 *         ),
 *     ),
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
            'is_premium' => (bool) $this->is_premium,
            'creation_time' => $this->created_at->toDateTimeString(),
            'visitors_count' => $this->visitors_count,
            'tests_count' => $this->tests_count,
            'cards_count' => $this->cards_count,

            'original_language' => $this->whenLoaded('originalLanguage', function () {
                $originalLanguage = $this->originalLanguage;
                return [
                    'id' => $originalLanguage->id,
                    'name' => $originalLanguage->name,
                    'code' => $originalLanguage->code,
                    'flag_url' => $originalLanguage->flag_url,
                ];
            }),

            'target_language' => $this->whenLoaded('targetLanguage', function () {
                $targetLanguage = $this->targetLanguage;
                return [
                    'id' => $targetLanguage->id,
                    'name' => $targetLanguage->name,
                    'code' => $targetLanguage->code,
                    'flag_url' => $targetLanguage->flag_url,
                ];
            }),

            'user' => $this->whenLoaded('user', function () {
                $user = $this->user;
                return [
                    'id' => $user->id,
                    'avatar_url' => $user->avatar_url,
                    'name' => $user->name,
                ];
            }),

            'topics' => $this->whenLoaded('topics', function () {
                return $this->topics->map(fn($topic) => [
                    'id' => $topic->id,
                    'name' => $topic->name,
                ]);
            }),

            'tests' => $this->whenLoaded('tests', function () {
                return $this->tests->map(fn($test) => [
                    'id' => $test->id,
                    'name' => $test->name,
                    'time_seconds' => $test->time_seconds,
                    'count_attempts' => $test->count_attempts,
                    'creation_time' => $test->created_at->toDateTimeString(),
                    'authorized_user_attempts' => $test->authorized_user_attempts ?? null,
                ]);
            }),

            'cards' => $this->whenLoaded('cards', function () {
                return $this->cards->map(fn($card) => [
                    'id' => $card->id,
                    'word' => $card->word,
                    'translate' => $card->translate,
                    'image_url' => $card->image_url,
                    'creation_time' => $card->created_at->toDateTimeString(),

                    'original_word_audiofiles' => $card->whenLoaded('audiofilesForOriginalWord', function () use ($card) {
                        return $card->audiofilesForOriginalWord->map(fn($audiofile) => [
                            'id' => $audiofile->id,
                            'path' => $audiofile->path,
                        ]);
                    }),

                    'target_word_audiofiles' => $card->whenLoaded('audiofilesForTargetWord', function () use ($card) {
                        return $card->audiofilesForTargetWord->map(fn($audiofile) => [
                            'id' => $audiofile->id,
                            'path' => $audiofile->path,
                        ]);
                    }),
                ]);
            }),
        ];
    }
}
