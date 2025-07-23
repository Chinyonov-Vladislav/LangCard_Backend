<?php

namespace App\Models;

use App\Helpers\ColumnLabel;
use App\Models\Interfaces\ColumnLabelsableInterface;
use App\Traits\HasTableColumns;
use Illuminate\Database\Eloquent\Model;

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
