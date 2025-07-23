<?php

namespace App\Models;

use App\Helpers\ColumnLabel;
use App\Models\Interfaces\ColumnLabelsableInterface;
use App\Traits\HasTableColumns;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Timezone extends Model implements ColumnLabelsableInterface
{
    use HasTableColumns;

    protected $table = 'timezones';
    protected $guarded = [];
    protected $fillable = ['name', 'offset_utc'];


    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'timezone_id');
    }

    public static function columnLabels(): array
    {
         return [
             new ColumnLabel('id', __('model_attributes/timezone.id')),
             new ColumnLabel('name', __('model_attributes/timezone.name')),
             new ColumnLabel('offset_utc', __('model_attributes/timezone.offset_utc')),
             new ColumnLabel('created_at', __('model_attributes/timezone.created_at')),
             new ColumnLabel('updated_at', __('model_attributes/timezone.updated_at')),
        ];
    }
}
