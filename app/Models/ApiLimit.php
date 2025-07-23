<?php

namespace App\Models;

use App\Helpers\ColumnLabel;
use App\Models\Interfaces\ColumnLabelsableInterface;
use App\Traits\HasTableColumns;
use Illuminate\Database\Eloquent\Model;

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
