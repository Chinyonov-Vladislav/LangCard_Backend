<?php

namespace App\Models;

use App\Helpers\ColumnLabel;
use App\Models\Interfaces\ColumnLabelsableInterface;
use App\Traits\HasTableColumns;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property string $name
 * @property int $card_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Card $card
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Example newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Example newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Example query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Example whereCardId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Example whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Example whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Example whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Example whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Example extends Model implements  ColumnLabelsableInterface
{
    use HasTableColumns;
    protected $table = 'examples';
    protected $guarded = [];

    public function card(): BelongsTo
    {
        return $this->belongsTo(Card::class, 'card_id');
    }
    protected function casts(): array
    {
        return [

        ];
    }

    public static function columnLabels(): array
    {
        return [
            new ColumnLabel('id', __('model_attributes/example.id')),
            new ColumnLabel('name', __('model_attributes/example.name')),
            new ColumnLabel('card_id', __('model_attributes/example.card_id')),
            new ColumnLabel('created_at', __('model_attributes/example.created_at')),
            new ColumnLabel('updated_at', __('model_attributes/example.updated_at')),
        ];
    }
}
