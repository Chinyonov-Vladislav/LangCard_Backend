<?php

namespace App\Models;

use App\Helpers\ColumnLabel;
use App\Models\Interfaces\ColumnLabelsableInterface;
use App\Traits\HasTableColumns;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $topic_id
 * @property int $deck_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DeckTopic newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DeckTopic newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DeckTopic query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DeckTopic whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DeckTopic whereDeckId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DeckTopic whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DeckTopic whereTopicId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DeckTopic whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class DeckTopic extends Model implements ColumnLabelsableInterface
{
    use HasTableColumns;
    protected $table = 'deck_topics';
    protected $guarded = [];
    protected function casts(): array
    {
        return [

        ];
    }

    public static function columnLabels(): array
    {
        return [
            new ColumnLabel('id', __('model_attributes/deck_topic.id')),
            new ColumnLabel('topic_id', __('model_attributes/deck_topic.topic_id')),
            new ColumnLabel('deck_id', __('model_attributes/deck_topic.deck_id')),
            new ColumnLabel('created_at', __('model_attributes/deck_topic.created_at')),
            new ColumnLabel('updated_at', __('model_attributes/deck_topic.updated_at')),
        ];
    }
}
