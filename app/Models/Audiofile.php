<?php

namespace App\Models;

use App\Helpers\ColumnLabel;
use App\Models\Interfaces\ColumnLabelsableInterface;
use App\Traits\HasTableColumns;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $path
 * @property string $destination
 * @property int $card_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Card $card
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Audiofile newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Audiofile newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Audiofile query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Audiofile whereCardId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Audiofile whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Audiofile whereDestination($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Audiofile whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Audiofile wherePath($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Audiofile whereUpdatedAt($value)
 * @mixin \Eloquent
 */
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
