<?php

namespace App\Models;

use App\Helpers\ColumnLabel;
use App\Models\Interfaces\ColumnLabelsableInterface;
use App\Traits\HasTableColumns;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $name
 * @property int $days
 * @property int $is_active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Cost> $costs
 * @property-read int|null $costs_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Tariff> $promocodes
 * @property-read int|null $promocodes_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tariff newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tariff newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tariff query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tariff whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tariff whereDays($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tariff whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tariff whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tariff whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tariff whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Tariff extends Model implements ColumnLabelsableInterface
{
    use HasTableColumns;
    protected $table = 'tariffs';
    protected $guarded = [];

    public function costs(): HasMany
    {
        return $this->hasMany(Cost::class, 'tariff_id');
    }
    public function promocodes(): HasMany
    {
        return $this->hasMany(Tariff::class, 'tariff_id');
    }

    protected function casts(): array
    {
        return [

        ];
    }

    public static function columnLabels(): array
    {
        return [
            new ColumnLabel('id', __('model_attributes/tariff.id')),
            new ColumnLabel('name',__('model_attributes/tariff.name')),
            new ColumnLabel('days',__('model_attributes/tariff.days')),
            new ColumnLabel('is_active',__('model_attributes/tariff.is_active')),
            new ColumnLabel('created_at',__('model_attributes/tariff.created_at')),
            new ColumnLabel('updated_at',__('model_attributes/tariff.updated_at'))
        ];
    }
}
