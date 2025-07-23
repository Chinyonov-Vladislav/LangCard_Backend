<?php

namespace App\Models;

use App\Helpers\ColumnLabel;
use App\Models\Interfaces\ColumnLabelsableInterface;
use App\Traits\HasTableColumns;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
