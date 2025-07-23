<?php

namespace App\Models;

use App\Helpers\ColumnLabel;
use App\Http\Filters\Filterable;
use App\Models\Interfaces\ColumnLabelsableInterface;
use App\Traits\HasTableColumns;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Voice extends Model implements ColumnLabelsableInterface
{
    use HasTableColumns, Filterable;
    protected $table = 'voices';
    protected $guarded = [];
    protected function casts(): array
    {
        return [
            'is_active'=>'boolean'
        ];
    }
    public function language(): BelongsTo
    {
        return $this->belongsTo(Language::class, 'language_id');
    }

    public static function columnLabels(): array
    {
        return [
            new ColumnLabel('id', "Идентификатор"),
            new ColumnLabel('voice_id', 'ID номер голоса'),
            new ColumnLabel('voice_name', 'Наименование голоса'),
            new ColumnLabel('sex', "Пол"),
            new ColumnLabel('language_id', "Id языка"),
            new ColumnLabel('is_active', "Активность"),
            new ColumnLabel('created_at', "Дата создания"),
            new ColumnLabel('updated_at', "Дата обновления"),
        ];
    }
}
