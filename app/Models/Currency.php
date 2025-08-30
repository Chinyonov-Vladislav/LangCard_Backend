<?php

namespace App\Models;

use App\Helpers\ColumnLabel;
use App\Models\Interfaces\ColumnLabelsableInterface;
use App\Traits\HasTableColumns;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $name
 * @property string $code
 * @property string $symbol
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection<int, Cost> $costs
 * @property-read int|null $costs_count
 * @property-read Collection<int, User> $users
 * @property-read int|null $users_count
 * @method static Builder<static>|Currency newModelQuery()
 * @method static Builder<static>|Currency newQuery()
 * @method static Builder<static>|Currency query()
 * @method static Builder<static>|Currency whereCode($value)
 * @method static Builder<static>|Currency whereCreatedAt($value)
 * @method static Builder<static>|Currency whereId($value)
 * @method static Builder<static>|Currency whereName($value)
 * @method static Builder<static>|Currency whereSymbol($value)
 * @method static Builder<static>|Currency whereUpdatedAt($value)
 * @mixin Eloquent
 */
class Currency extends Model implements  ColumnLabelsableInterface
{
    use HasTableColumns;
    protected $table = 'currencies';
    protected $guarded = [];

    public function costs(): HasMany
    {
        return $this->hasMany(Cost::class, 'currency_id');
    }
    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'currency_id');
    }
    protected function casts(): array
    {
        return [

        ];
    }

    public static function columnLabels(): array
    {
        return [
            new ColumnLabel('id', __('model_attributes/currency.id')),
            new ColumnLabel('name', __('model_attributes/currency.name')),
            new ColumnLabel('code', __('model_attributes/currency.code')),
            new ColumnLabel('symbol', __('model_attributes/currency.symbol')),
            new ColumnLabel('created_at', __('model_attributes/currency.created_at')),
            new ColumnLabel('updated_at', __('model_attributes/currency.updated_at')),
        ];
    }
}
