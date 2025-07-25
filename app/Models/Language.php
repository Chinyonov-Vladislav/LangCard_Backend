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
 * @property string $native_name
 * @property string $code
 * @property string $flag_url
 * @property string $locale
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Deck> $originalLanguageDecks
 * @property-read int|null $original_language_decks_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Deck> $targetLanguageDecks
 * @property-read int|null $target_language_decks_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Voice> $voices
 * @property-read int|null $voices_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Language newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Language newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Language query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Language whereCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Language whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Language whereFlagUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Language whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Language whereLocale($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Language whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Language whereNativeName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Language whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Language extends Model implements ColumnLabelsableInterface
{
    use HasTableColumns;
    protected $table = 'languages';
    protected $guarded = [];

    public function originalLanguageDecks(): HasMany
    {
        return $this->hasMany(Deck::class, 'original_language_id');
    }
    public function targetLanguageDecks(): HasMany
    {
        return $this->hasMany(Deck::class, 'target_language_id');
    }

    public function voices(): HasMany
    {
        return $this->hasMany(Voice::class, 'language_id');
    }

    protected function casts(): array
    {
        return [

        ];
    }

    public static function columnLabels(): array
    {
        return [
            new ColumnLabel('id', __('model_attributes/language.id')),
            new ColumnLabel('name', __('model_attributes/language.name')),
            new ColumnLabel('native_name',__('model_attributes/language.native_name')),
            new ColumnLabel('code', __('model_attributes/language.code')),
            new ColumnLabel('flag_url', __('model_attributes/language.flag_url')),
            new ColumnLabel( 'locale',__('model_attributes/language.locale')),
            new ColumnLabel('created_at', __('model_attributes/language.created_at')),
            new ColumnLabel('updated_at', __('model_attributes/language.updated_at')),
        ];
    }
}
