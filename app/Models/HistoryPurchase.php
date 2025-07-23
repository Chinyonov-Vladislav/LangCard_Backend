<?php

namespace App\Models;

use App\Helpers\ColumnLabel;
use App\Models\Interfaces\ColumnLabelsableInterface;
use App\Traits\HasTableColumns;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
