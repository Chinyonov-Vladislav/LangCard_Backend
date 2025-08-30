<?php

namespace App\Models;

use App\Helpers\ColumnLabel;
use App\Models\Interfaces\ColumnLabelsableInterface;
use App\Traits\HasTableColumns;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $cost
 * @property int $currency_id
 * @property int $tariff_id
 * @property int $is_active
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Currency $currency
 * @property-read Collection<int, HistoryPurchase> $historyPurchases
 * @property-read int|null $history_purchases_count
 * @property-read Tariff $tariff
 * @method static Builder<static>|Cost newModelQuery()
 * @method static Builder<static>|Cost newQuery()
 * @method static Builder<static>|Cost query()
 * @method static Builder<static>|Cost whereCost($value)
 * @method static Builder<static>|Cost whereCreatedAt($value)
 * @method static Builder<static>|Cost whereCurrencyId($value)
 * @method static Builder<static>|Cost whereId($value)
 * @method static Builder<static>|Cost whereIsActive($value)
 * @method static Builder<static>|Cost whereTariffId($value)
 * @method static Builder<static>|Cost whereUpdatedAt($value)
 * @mixin Eloquent
 */
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
    public function historyPurchases(): Builder|HasMany
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
