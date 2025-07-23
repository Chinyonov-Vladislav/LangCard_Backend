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
