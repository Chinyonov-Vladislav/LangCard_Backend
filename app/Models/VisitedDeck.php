<?php

namespace App\Models;

use App\Helpers\ColumnLabel;
use App\Models\Interfaces\ColumnLabelsableInterface;
use App\Traits\HasTableColumns;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $deck_id
 * @property int $user_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VisitedDeck newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VisitedDeck newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VisitedDeck query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VisitedDeck whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VisitedDeck whereDeckId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VisitedDeck whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VisitedDeck whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VisitedDeck whereUserId($value)
 * @mixin \Eloquent
 */
class VisitedDeck extends Model implements ColumnLabelsableInterface
{
    use HasTableColumns;
    protected $table = 'visited_decks';
    protected $guarded = [];
    protected function casts(): array
    {
        return [

        ];
    }

    public static function columnLabels(): array
    {
        return [
            new ColumnLabel('id', __('model_attributes/visited_deck.id')),
            new ColumnLabel('deck_id', __('model_attributes/visited_deck.deck_id')),
            new ColumnLabel('user_id', __('model_attributes/visited_deck.user_id')),
            new ColumnLabel('created_at', __('model_attributes/visited_deck.created_at')),
            new ColumnLabel('updated_at', __('model_attributes/visited_deck.updated_at')),
        ];
    }
}
