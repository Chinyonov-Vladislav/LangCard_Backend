<?php

namespace App\Models;

use App\Helpers\ColumnLabel;
use App\Http\Filters\Filterable;
use App\Models\Interfaces\ColumnLabelsableInterface;
use App\Traits\HasTableColumns;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property string $name
 * @property int $original_language_id
 * @property int $target_language_id
 * @property int $user_id
 * @property bool $is_premium
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Card> $cards
 * @property-read int|null $cards_count
 * @property-read \App\Models\Language $originalLanguage
 * @property-read \App\Models\Language $targetLanguage
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Test> $tests
 * @property-read int|null $tests_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Topic> $topics
 * @property-read int|null $topics_count
 * @property-read \App\Models\User $user
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Deck> $visitors
 * @property-read int|null $visitors_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Deck filter(\App\Http\Filters\QueryFilter $filter)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Deck newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Deck newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Deck onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Deck query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Deck whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Deck whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Deck whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Deck whereIsPremium($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Deck whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Deck whereOriginalLanguageId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Deck whereTargetLanguageId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Deck whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Deck whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Deck withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Deck withoutTrashed()
 * @mixin \Eloquent
 */
class Deck extends Model implements ColumnLabelsableInterface
{
    use HasTableColumns, Filterable,SoftDeletes;
    protected $table = 'decks';
    protected $guarded = [];

    // Связь с исходным языком
    public function originalLanguage(): BelongsTo
    {
        return $this->belongsTo(Language::class, 'original_language_id');
    }

    // Связь с целевым языком
    public function targetLanguage(): BelongsTo
    {
        return $this->belongsTo(Language::class, 'target_language_id');
    }
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    public function visitors(): BelongsToMany
    {
        return $this->belongsToMany(Deck::class, 'visited_decks',  'deck_id', 'user_id');
    }

    public function topics(): BelongsToMany
    {
        return $this->belongsToMany(Topic::class, 'deck_topics', 'deck_id', 'topic_id');
    }
    public function tests(): HasMany
    {
        return $this->hasMany(Test::class, 'deck_id');
    }
    public function cards(): HasMany
    {
        return $this->hasMany(Card::class, 'deck_id');
    }
    protected function casts(): array
    {
        return [
            'is_premium'=>'boolean'
        ];
    }

    public static function columnLabels(): array
    {
        return [
            new ColumnLabel('id', __('model_attributes/decks.id')),
            new ColumnLabel('name', __('model_attributes/decks.name')),
            new ColumnLabel('original_language_id', __('model_attributes/decks.original_language_id')),
            new ColumnLabel('target_language_id', __('model_attributes/decks.target_language_id')),
            new ColumnLabel('user_id', __('model_attributes/decks.user_id')),
            new ColumnLabel('is_premium', __('model_attributes/decks.is_premium')),
            new ColumnLabel('created_at', __('model_attributes/decks.created_at')),
            new ColumnLabel('updated_at', __('model_attributes/decks.updated_at')),
        ];
    }
}
