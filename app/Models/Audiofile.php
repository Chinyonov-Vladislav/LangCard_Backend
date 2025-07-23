<?php

namespace App\Models;

use App\Helpers\ColumnLabel;
use App\Models\Interfaces\ColumnLabelsableInterface;
use App\Traits\HasTableColumns;
use Illuminate\Database\Eloquent\Model;

class Audiofile extends Model implements ColumnLabelsableInterface
{
    use HasTableColumns;
    protected $table = 'audiofiles';
    protected $guarded = [];

    public function card()
    {
        return $this->belongsTo(Card::class, 'card_id');
    }

    protected function casts(): array
    {
        return [
            'created_at'=>'datetime',
            'updated_at'=>'datetime'
        ];
    }

    public static function columnLabels(): array
    {
        return [
            new ColumnLabel('id', "Идентификатор"),
            new ColumnLabel('path', 'Путь к файлу'),
            new ColumnLabel('destination', 'Предназначение'),
            new ColumnLabel('created_at', "Дата создания"),
            new ColumnLabel('updated_at', "Дата обновления"),
        ];
    }
}
