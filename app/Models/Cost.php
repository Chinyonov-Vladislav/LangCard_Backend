<?php

namespace App\Models;

use App\Helpers\ColumnLabel;
use App\Models\Interfaces\ColumnLabelsableInterface;
use App\Traits\HasTableColumns;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Cost extends Model implements  ColumnLabelsableInterface
{
    use HasTableColumns;
    protected $table = 'costs';
    protected $guarded = [];
    public function tariff(): BelongsTo
    {
        return $this->belongsTo(Tariff::class, 'tariff_id');
    }
    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class, 'currency_id');
    }
    public function historyPurchases()
    {
        return $this->hasMany(HistoryPurchase::class, 'cost_id');
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
            new ColumnLabel('cost', __('model_attributes/card.cost')),
            new ColumnLabel('currency_id', __('model_attributes/card.currency_id')),
            new ColumnLabel('tariff_id', __('model_attributes/card.tariff_id')),
            new ColumnLabel('created_at', __('model_attributes/card.created_at')),
            new ColumnLabel('updated_at', __('model_attributes/card.updated_at')),
        ];
    }
}
