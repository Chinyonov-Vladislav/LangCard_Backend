<?php

namespace App\Models;

use App\Helpers\ColumnLabel;
use App\Models\Interfaces\ColumnLabelsableInterface;
use App\Traits\HasTableColumns;
use Database\Factories\UserFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Notifications\DatabaseNotificationCollection;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Laravel\Sanctum\HasApiTokens;
use Laravel\Sanctum\PersonalAccessToken;

/**
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string|null $avatar_url
 * @property Carbon|null $email_verified_at
 * @property string|null $password
 * @property string $type_user
 * @property int|null $currency_id
 * @property int|null $timezone_id
 * @property Carbon|null $vip_status_time_end
 * @property string|null $remember_token
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Currency|null $currency
 * @property-read Collection<int, Deck> $decks
 * @property-read int|null $decks_count
 * @property-read Collection<int, HistoryPurchase> $historyPurchases
 * @property-read int|null $history_purchases_count
 * @property-read DatabaseNotificationCollection<int, DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 * @property-read Timezone|null $timezone
 * @property-read Collection<int, PersonalAccessToken> $tokens
 * @property-read int|null $tokens_count
 * @property-read Collection<int, UserTestResult> $userTestResults
 * @property-read int|null $user_test_results_count
 * @property-read Collection<int, Deck> $visitedDecks
 * @property-read int|null $visited_decks_count
 * @method static UserFactory factory($count = null, $state = [])
 * @method static Builder<static>|User newModelQuery()
 * @method static Builder<static>|User newQuery()
 * @method static Builder<static>|User query()
 * @method static Builder<static>|User whereAvatarUrl($value)
 * @method static Builder<static>|User whereCreatedAt($value)
 * @method static Builder<static>|User whereCurrencyId($value)
 * @method static Builder<static>|User whereEmail($value)
 * @method static Builder<static>|User whereEmailVerifiedAt($value)
 * @method static Builder<static>|User whereId($value)
 * @method static Builder<static>|User whereName($value)
 * @method static Builder<static>|User wherePassword($value)
 * @method static Builder<static>|User whereRememberToken($value)
 * @method static Builder<static>|User whereTimezoneId($value)
 * @method static Builder<static>|User whereTypeUser($value)
 * @method static Builder<static>|User whereUpdatedAt($value)
 * @method static Builder<static>|User whereVipStatusTimeEnd($value)
 * @mixin \Eloquent
 */
class User extends Authenticatable implements ColumnLabelsableInterface
{
    protected $table = 'users';

    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable, HasTableColumns;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'type_user',
        'currency_id',
        'timezone_id',
        'vip_status_time_end'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class, 'currency_id');
    }
    public function historyPurchases(): HasMany
    {
        return $this->hasMany(HistoryPurchase::class, 'user_id');
    }
    public function decks(): HasMany
    {
        return $this->hasMany(Deck::class, 'user_id');
    }
    public function visitedDecks(): BelongsToMany
    {
        return $this->belongsToMany(Deck::class, 'visited_decks', 'user_id', 'deck_id');
    }

    public function userTestResults(): HasMany
    {
        return $this->hasMany(UserTestResult::class, 'user_id');
    }

    public function timezone(): BelongsTo
    {
        return $this->belongsTo(Timezone::class, 'timezone_id');
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'vip_status_time_end' => 'datetime'
        ];
    }

    public static function columnLabels(): array
    {
        return [
            new ColumnLabel('id', __('model_attributes/user.id')),
            new ColumnLabel('name', __('model_attributes/user.name')),
            new ColumnLabel('email', __('model_attributes/user.email')),
            new ColumnLabel('avatar_url', __('model_attributes/user.avatar_url')),
            new ColumnLabel('email_verified_at', __('model_attributes/user.email_verified_at')),
            new ColumnLabel('password', __('model_attributes/user.password')),
            new ColumnLabel('type_user', __('model_attributes/user.type_user')),
            new ColumnLabel('currency_id', __('model_attributes/user.currency_id')),
            new ColumnLabel('timezone_id', __('model_attributes/user.timezone_id')),
            new ColumnLabel('vip_status_time_end', __('model_attributes/user.vip_status_time_end')),
            new ColumnLabel('remember_token', __('model_attributes/user.remember_token')),
            new ColumnLabel('created_at', __('model_attributes/user.created_at')),
            new ColumnLabel('updated_at', __('model_attributes/user.updated_at')),
        ];
    }

}
