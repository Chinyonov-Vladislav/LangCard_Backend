<?php

namespace App\Models;

use App\Helpers\ColumnLabel;
use App\Models\Interfaces\ColumnLabelsableInterface;
use App\Traits\HasTableColumns;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $path
 * @property string $destination
 * @property int $card_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Card $card
 * @method static Builder<static>|Audiofile newModelQuery()
 * @method static Builder<static>|Audiofile newQuery()
 * @method static Builder<static>|Audiofile query()
 * @method static Builder<static>|Audiofile whereCardId($value)
 * @method static Builder<static>|Audiofile whereCreatedAt($value)
 * @method static Builder<static>|Audiofile whereDestination($value)
 * @method static Builder<static>|Audiofile whereId($value)
 * @method static Builder<static>|Audiofile wherePath($value)
 * @method static Builder<static>|Audiofile whereUpdatedAt($value)
 * @mixin Eloquent
 */
class Audiofile extends Model implements ColumnLabelsableInterface
{
    use HasTableColumns;
    protected $table = 'audiofiles';
    protected $guarded = [];

    public function card(): BelongsTo
    {
        return $this->belongsTo(Card::class, 'card_id');
    }

    public function voice(): BelongsTo
    {
        return $this->belongsTo(Voice::class, 'voice_id');
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
