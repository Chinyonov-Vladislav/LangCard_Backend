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
 * @property string $offset_utc
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $users
 * @property-read int|null $users_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Timezone newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Timezone newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Timezone query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Timezone whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Timezone whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Timezone whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Timezone whereOffsetUtc($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Timezone whereUpdatedAt($value)
 * @mixin \Eloquent
 */
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
