<?php

namespace App\Models;

use App\Helpers\ColumnLabel;
use App\Models\Interfaces\ColumnLabelsableInterface;
use App\Traits\HasTableColumns;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property string $code
 * @property bool $active
 * @property int $tariff_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Tariff $tariff
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Promocode newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Promocode newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Promocode query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Promocode whereActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Promocode whereCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Promocode whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Promocode whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Promocode whereTariffId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Promocode whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Promocode extends Model implements ColumnLabelsableInterface
{
    use HasTableColumns;
    protected $table = 'promocodes';
    protected $guarded = [];

    public function tariff(): BelongsTo
    {
        return $this->belongsTo(Tariff::class, 'tariff_id');
    }

    public static function columnLabels(): array
    {
        return [
            new ColumnLabel('id', __('model_attributes/promocode.id')),
            new ColumnLabel('code', __('model_attributes/promocode.code')),
            new ColumnLabel('active', __('model_attributes/promocode.active')),
            new ColumnLabel('tariff_id', __('model_attributes/promocode.tariff_id')),
            new ColumnLabel('created_at', __('model_attributes/promocode.created_at')),
            new ColumnLabel('updated_at', __('model_attributes/promocode.updated_at')),
        ];
    }
    protected function casts(): array
    {
        return [
            'active' => 'bool',
        ];
    }
}
