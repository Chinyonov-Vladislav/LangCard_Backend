<?php

namespace App\Models;

use App\Helpers\ColumnLabel;
use App\Models\Interfaces\ColumnLabelsableInterface;
use App\Traits\HasTableColumns;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * @property int $id
 * @property string $word
 * @property string $translate
 * @property string|null $image_url
 * @property string|null $pronunciation_url
 * @property int $deck_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Audiofile> $audiofilesForOriginalWord
 * @property-read int|null $audiofiles_for_original_word_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Audiofile> $audiofilesForTargetWord
 * @property-read int|null $audiofiles_for_target_word_count
 * @property-read \App\Models\Deck $deck
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Example> $examples
 * @property-read int|null $examples_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Question> $questions
 * @property-read int|null $questions_count
 * @method static Builder<static>|Card newModelQuery()
 * @method static Builder<static>|Card newQuery()
 * @method static Builder<static>|Card query()
 * @method static Builder<static>|Card whereCreatedAt($value)
 * @method static Builder<static>|Card whereDeckId($value)
 * @method static Builder<static>|Card whereId($value)
 * @method static Builder<static>|Card whereImageUrl($value)
 * @method static Builder<static>|Card wherePronunciationUrl($value)
 * @method static Builder<static>|Card whereTranslate($value)
 * @method static Builder<static>|Card whereUpdatedAt($value)
 * @method static Builder<static>|Card whereWord($value)
 * @mixin \Eloquent
 */
class Card extends Model implements  ColumnLabelsableInterface
{
    use HasTableColumns;
    protected $table = 'cards';
    protected $guarded = [];

    public function deck(): BelongsTo
    {
        return $this->belongsTo(Deck::class, 'deck_id');
    }
    public function examples(): HasMany
    {
        return $this->hasMany(Example::class, 'card_id');
    }
    public function examplesOriginal(): HasMany
    {
        return $this->hasMany(Example::class, 'card_id')->where('source', '=', 'original');
    }
    public function examplesTarget(): HasMany
    {
        return $this->hasMany(Example::class, 'card_id')->where('source', '=', 'target');
    }
    public function questions(): HasMany
    {
        return $this->hasMany(Question::class, 'card_id');
    }

    public function audiofilesForOriginalWord(): HasMany
    {
        return $this->hasMany(Audiofile::class, 'card_id')->where('destination','=','original');
    }

    public function audiofilesForTargetWord(): HasMany
    {
        return $this->hasMany(Audiofile::class, 'card_id')->where('destination','=','target');
    }


    protected static function boot()
    {
        parent::boot();

        static::deleting(function (Card $card) {
            if ($card->image_url) {
                $relativePath = Str::replaceFirst('storage/', '', $card->image_url);
                if (Storage::disk('public')->exists($relativePath)) {
                    Storage::disk('public')->delete($relativePath);
                }
            }
        });
    }

    protected function casts(): array
    {
        return [

        ];
    }

    public static function columnLabels(): array
    {
        return [
            new ColumnLabel('id', __('model_attributes/card.id')),
            new ColumnLabel('word', __('model_attributes/card.word')),
            new ColumnLabel('translate', __('model_attributes/card.translate')),
            new ColumnLabel('image_url', __('model_attributes/card.image_url')),
            new ColumnLabel('pronunciation_url', __('model_attributes/card.pronunciation_url')),
            new ColumnLabel('deck_id', __('model_attributes/card.deck_id')),
            new ColumnLabel('created_at', __('model_attributes/card.created_at')),
            new ColumnLabel('updated_at', __('model_attributes/card.updated_at')),
        ];
    }
}
