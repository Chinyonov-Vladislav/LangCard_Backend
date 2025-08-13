<?php

namespace App\Models;

use App\Helpers\ColumnLabel;
use App\Http\Filters\Filterable;
use App\Models\Interfaces\ColumnLabelsableInterface;
use App\Traits\HasTableColumns;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $voice_id
 * @property string $voice_name
 * @property string $sex
 * @property bool $is_active
 * @property int $language_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Language $language
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Voice filter(\App\Http\Filters\QueryFilter $filter)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Voice newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Voice newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Voice query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Voice whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Voice whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Voice whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Voice whereLanguageId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Voice whereSex($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Voice whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Voice whereVoiceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Voice whereVoiceName($value)
 * @mixin \Eloquent
 */
class Voice extends Model implements ColumnLabelsableInterface
{
    use HasTableColumns, Filterable;
    protected $table = 'voices';
    protected $guarded = [];

    public function audiofiles(): HasMany
    {
        return $this->hasMany(Audiofile::class, 'voice_id');
    }

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
