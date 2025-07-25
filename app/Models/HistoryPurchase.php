<?php

namespace App\Models;

use App\Helpers\ColumnLabel;
use App\Models\Interfaces\ColumnLabelsableInterface;
use App\Traits\HasTableColumns;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property string $date_purchase
 * @property string $date_end
 * @property int $user_id
 * @property int $cost_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Cost $cost
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HistoryPurchase newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HistoryPurchase newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HistoryPurchase query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HistoryPurchase whereCostId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HistoryPurchase whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HistoryPurchase whereDateEnd($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HistoryPurchase whereDatePurchase($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HistoryPurchase whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HistoryPurchase whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HistoryPurchase whereUserId($value)
 * @mixin \Eloquent
 */
class HistoryPurchase extends Model implements ColumnLabelsableInterface
{
    use HasTableColumns;
    protected $table = 'history_purchases';
    protected $guarded = [];
    public function cost(): BelongsTo
    {
        return $this->belongsTo(Cost::class, 'cost_id');
    }
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    protected function casts(): array
    {
        return [

        ];
    }

    public static function columnLabels(): array
    {
        return [
            new ColumnLabel('id', __('model_attributes/history_purchase.id')),
            new ColumnLabel('date_purchase', __('model_attributes/history_purchase.date_purchase')),
            new ColumnLabel('date_end', __('model_attributes/history_purchase.date_end')),
            new ColumnLabel('user_id', __('model_attributes/history_purchase.user_id')),
            new ColumnLabel('cost_id', __('model_attributes/history_purchase.cost_id')),
            new ColumnLabel('created_at', __('model_attributes/history_purchase.created_at')),
            new ColumnLabel('updated_at', __('model_attributes/history_purchase.updated_at')),
        ];
    }
}
