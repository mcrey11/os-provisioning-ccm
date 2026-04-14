<?php

/**
 * Copyright (c) NMS PRIME GmbH ("NMS PRIME Community Version")
 * and others – powered by CableLabs. All rights reserved.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at:
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace App;

use App;
use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Nwidart\Modules\Facades\Module;
use Silber\Bouncer\Database\HasRolesAndAbilities;

/**
 * This is the Model, holding the User data for authentication.
 * A User belongsToMany Roles and a Role role holds CRUD
 * separated Permissions. To gain access data the
 * Middleware will check for Permissions.
 */
class User extends BaseModel implements AuthenticatableContract, AuthorizableContract
{
    use Authenticatable, Authorizable, HasFactory, HasRolesAndAbilities, Notifiable;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    public $table = 'users';

    /**
     * The authentication guard name.
     *
     * @var string
     */
    protected $guard = 'admin';

    protected $casts = [
        'last_login_at' => 'datetime',
        'password_changed_at' => 'datetime',
        'geopos_updated_at' => 'datetime',
    ];

    /**
     * Expiration duration for the user position data.
     */
    public const GEOPOS_EXPIRATION_TIME = '1 day';

    /**
     * extending the boot functionality to observe changes
     *
     * @return void
     */
    public static function boot()
    {
        parent::boot();

        self::observe(new \App\Observers\UserObserver);
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $guarded = ['roles_ids'];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
        'api_token',
    ];

    public function getAuthPasswordName()
    {
        return 'password';
    }

    public function tickets()
    {
        return $this->belongsToMany(\Modules\Ticketsystem\Entities\Ticket::class, 'ticket_user', 'user_id', 'ticket_id');
    }

    public function favNetelements()
    {
        if (! Module::collections()->has('HfcReq')) {
            return collect();
        }

        return $this->belongsToMany(\Modules\HfcReq\Entities\NetElement::class, 'favorite_netelements', 'user_id', 'netelement_id');
    }

    /**
     * Query for the new and open Tickets only.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function openTickets()
    {
        return $this->tickets()->where('ticket_type_state_id', '!=', \Modules\Ticketsystem\Entities\TicketTypeState::STATES['Closed']);
    }

    public function inWorkTickets()
    {
        return $this->tickets()
            ->where('paused', false)
            ->whereNotIn('ticket_type_state_id', [
                \Modules\Ticketsystem\Entities\TicketTypeState::STATES['New'],
                \Modules\Ticketsystem\Entities\TicketTypeState::STATES['Closed'],
            ]);
    }

    /**
     * Get the user's preferred locale.
     *
     * @return string
     */
    public function preferredLocale()
    {
        return $this->language;
    }

    /**
     * Validation
     *
     *  Add your validation rules here
     */
    public function rules()
    {
        return [
            'email' => 'nullable|email',
            'phonenumber' => 'nullable|numeric',
            'login_name' => 'required|iunique:users,login_name,'.($this->id ?: 0).',id,deleted_at,NULL',
            'password' => 'sometimes|min:8|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/|confirmed',
            'password_confirmation' => 'min:8|required_with:password|same:password',
        ];
    }

    /**
     * View related Code
     */

    /**
     * Name which is Displayed on Top and in Headline
     */
    public static function view_headline(): string
    {
        return 'Users';
    }

    /**
     *  Icon for this model
     */
    public static function view_icon(): string
    {
        return '<i class="fa fa-user-o"></i>';
    }

    /**
     * This Method returns a configuration array to generate
     * the datatables on the index Page of each module.
     *
     * For more documentation look in BaseController
     * TODO: set color dependent of user role/permission
     */
    public function view_index_label()
    {
        return [
            'table' => $this->table,
            'index_header' => [
                $this->table.'.login_name',
                $this->table.'.first_name',
                $this->table.'.last_name',
                'email',
                $this->table.'.geopos_updated_at',
                'active',
            ],
            'translateBooleanColumns' => ['active'],
            'header' => $this->first_name.' '.$this->last_name,
        ];
    }

    public function label()
    {
        return $this->first_name || $this->last_name ? $this->first_name.' '.$this->last_name : $this->login_name;
    }

    public function getHighestRank(): int
    {
        $ranks = $this->roles()->pluck('rank');
        $highestRank = 0;

        foreach ($ranks as $rank) {
            $highestRank = $rank > $highestRank ? $rank : $highestRank;
        }

        return $highestRank;
    }

    public static function getHighestRankOf(self $user): int
    {
        $ranks = $user->roles()->pluck('rank');
        $highestRank = 0;
        foreach ($ranks as $rank) {
            $highestRank = $rank > $highestRank ? $rank : $highestRank;
        }

        return $highestRank;
    }

    public function hasHigherRankThan(self $user): bool
    {
        return $this->getHighestRank() > $user->getHighestRank() ? true : false;
    }

    public function hasLowerRankThan(self $user): bool
    {
        return $this->getHighestRank() < $user->getHighestRank() ? true : false;
    }

    public function hasSameRankAs(self $user): bool
    {
        return $this->getHighestRank() == $user->getHighestRank() ? true : false;
    }

    /**
     * Checks if this is the first own Login of a User.
     *
     * @param  App\User  $user
     */
    public function isFirstLogin(): bool
    {
        return $this->last_login_at == null;
    }

    /**
     * Checks if the password of the current user is expired.
     *
     * @param  App\User  $user
     * @param  Carbon\Carbon  $now
     */
    public function isPasswordExpired(): bool
    {
        $passwordInterval = Cache::get('GlobalConfig', function () {
            return \App\GlobalConfig::first();
        })->password_reset_interval;

        if ($passwordInterval === 0) {
            return false;
        }

        if ($this->password_changed_at == null) {
            return true;
        }

        return now()
            ->subDays($passwordInterval)
            ->greaterThan($this->password_changed_at);
    }

    /**
     * Use NmsPrime Helperfunction to calculate geographical distance between
     * two coordinates.
     *
     * @param  float  $latitude
     * @param  float  $longitude
     * @return float [km]
     */
    public function getDistance($latitude, $longitude)
    {
        return distanceLatLong($this->lat, $this->lng, $latitude, $longitude) / 1000;
    }

    /**
     * Check if the Position of the current user is outdated.
     *
     * @return bool
     */
    public function isGeoposOutdated()
    {
        return $this->geopos_updated_at->lte(now()->sub(self::GEOPOS_EXPIRATION_TIME));
    }

    protected function avatarPlaceholder(): Attribute
    {
        return Attribute::get(function () {
            return str($this->label())
                ->squish()
                ->explode(' ')
                ->map(fn ($part) => preg_replace('/[^a-zA-Z0-9]/', '', $part))
                ->filter()
                ->when(
                    value: fn (Collection $parts) => $parts->containsOneItem(),
                    callback: fn (Collection $parts) => substr($parts->first(), 0, 2),
                    default: fn (Collection $parts) => substr($parts->first(), 0, 1).substr($parts->skip(1)->first(), 0, 1),
                );
        });
    }

    /**
     * Format Users for edit view select field and allow for searching.
     * This method is required for select2 functionality in forms.
     */
    public function select2Users(?string $search): \Illuminate\Database\Eloquent\Builder
    {
        return static::select('id', \DB::raw("CONCAT(login_name, ' (', first_name, ' ', last_name, ')') as text"))
            ->when($search, function ($query, $search) {
                return $query->where(\DB::raw("CONCAT(login_name, ' (', first_name, ' ', last_name, ')')"), 'ilike', "%{$search}%");
            });
    }
}
