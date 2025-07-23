<?php

namespace App\Models;

use App\Helpers\ColumnLabel;
use App\Models\Interfaces\ColumnLabelsableInterface;
use App\Traits\HasTableColumns;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
