<?php

namespace App\Modules\Identity\Models;

use App\Modules\Identity\Enums\Capability;
use App\Modules\Identity\Enums\CapabilityStatus;
use App\Modules\Identity\Enums\UserStatus;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

/**
 * One account per person across web, Android and iOS (ACC01).
 */
#[Fillable(['name', 'phone', 'email', 'password', 'locale'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, HasUlids, Notifiable, SoftDeletes;

    /**
     * Match the column defaults so a freshly created model is complete without a reload.
     *
     * @var array<string, mixed>
     */
    protected $attributes = [
        'locale' => 'en',
        'status' => 'active',
        'version' => 1,
    ];

    protected static function newFactory(): UserFactory
    {
        return UserFactory::new();
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'phone_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
            'status' => UserStatus::class,
            'version' => 'integer',
        ];
    }

    /**
     * @return HasMany<UserDevice, $this>
     */
    public function devices(): HasMany
    {
        return $this->hasMany(UserDevice::class);
    }

    /**
     * @return HasMany<UserCapability, $this>
     */
    public function capabilities(): HasMany
    {
        return $this->hasMany(UserCapability::class);
    }

    public function hasCapability(Capability $capability): bool
    {
        return $this->capabilities->contains(
            fn (UserCapability $c) => $c->capability === $capability && $c->status === CapabilityStatus::Active,
        );
    }

    public function isSuspended(): bool
    {
        return $this->status === UserStatus::Suspended;
    }
}
