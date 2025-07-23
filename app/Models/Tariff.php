<?php

namespace App\Models;

use App\Helpers\ColumnLabel;
use App\Models\Interfaces\ColumnLabelsableInterface;
use App\Traits\HasTableColumns;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
