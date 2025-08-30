<?php

namespace App\Models;

use App\Helpers\ColumnLabel;
use App\Models\Interfaces\ColumnLabelsableInterface;
use App\Traits\HasTableColumns;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $day
 * @property int $request_count
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @method static Builder<static>|ApiLimit newModelQuery()
 * @method static Builder<static>|ApiLimit newQuery()
 * @method static Builder<static>|ApiLimit query()
 * @method static Builder<static>|ApiLimit whereCreatedAt($value)
 * @method static Builder<static>|ApiLimit whereDay($value)
 * @method static Builder<static>|ApiLimit whereId($value)
 * @method static Builder<static>|ApiLimit whereRequestCount($value)
 * @method static Builder<static>|ApiLimit whereUpdatedAt($value)
 * @mixin Eloquent
 */
class ApiLimit extends Model implements  ColumnLabelsableInterface
{
    use HasTableColumns;
    protected $table = 'api_limits';
    protected $guarded = [];

    public static function columnLabels(): array
    {
        return [
            new ColumnLabel('id',__('model_attributes/api_limit.id')),
            new ColumnLabel('day',__('model_attributes/api_limit.day')),
            new ColumnLabel('request_count',__('model_attributes/api_limit.request_count')),
            new ColumnLabel('created_at',__('model_attributes/api_limit.created_at')),
            new ColumnLabel('updated_at',__('model_attributes/api_limit.updated_at')),
        ];
    }
}
