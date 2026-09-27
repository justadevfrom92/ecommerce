<?php

namespace App\Models;

use App\Services\Emailer;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'is_active', 'phone', 'address_line1', 'address_line2', 'city', 'state', 'postal_code', 'country'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $attributes = ['is_active' => true];

    /** @var array<string, bool>|null Permission slugs, cached per request. */
    protected ?array $permissionCache = null;

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class);
    }

    /** Password reset uses the editable "password_reset" email template. */
    public function sendPasswordResetNotification(#[\SensitiveParameter] $token): void
    {
        $url = route('password.reset', ['token' => $token, 'email' => $this->email]);

        app(Emailer::class)->sendTemplate($this->email, 'password_reset', [
            'name' => $this->name,
            'reset_url' => $url,
            'expires_minutes' => config('auth.passwords.users.expire', 60),
        ], $url);
    }

    public function hasAddress(): bool
    {
        return filled($this->address_line1) && filled($this->city);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function isSuperAdmin(): bool
    {
        return $this->roles->contains('is_super', true);
    }

    public function hasPermission(string $slug): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        if ($this->permissionCache === null) {
            $this->loadMissing('roles.permissions');
            $this->permissionCache = $this->roles
                ->flatMap->permissions
                ->pluck('slug')
                ->flip()
                ->map(fn () => true)
                ->all();
        }

        return isset($this->permissionCache[$slug]);
    }
}
