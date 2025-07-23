<?php

namespace App\Models;

use App\Helpers\ColumnLabel;
use App\Models\Interfaces\ColumnLabelsableInterface;
use App\Traits\HasTableColumns;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Topic extends Model implements ColumnLabelsableInterface
{
    use HasTableColumns;
    protected $table = 'topics';
    protected $guarded = [];
    public function decks(): BelongsToMany
    {
        return $this->belongsToMany(Deck::class, 'deck_topics', 'topic_id', 'deck_id');
    }

    protected function casts(): array
    {
        return [

        ];
    }

    public static function columnLabels(): array
    {
        return [
            new ColumnLabel('id', __('model_attributes/topic.id')),
            new ColumnLabel('name', __('model_attributes/topic.name')),
            new ColumnLabel('created_at', __('model_attributes/topic.created_at')),
            new ColumnLabel('updated_at', __('model_attributes/topic.updated_at')),
        ];
    }
}
