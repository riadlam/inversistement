<?php

namespace App\Models;

use App\Enums\Role;
use App\Support\Rbac\PermissionRegistry;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'username',
        'name',
        'email',
        'password',
        'wilaya_id',
        'role',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    public function wilaya(): BelongsTo
    {
        return $this->belongsTo(Wilaya::class);
    }

    public function roleEnum(): ?Role
    {
        return Role::tryFrom((string) $this->role);
    }

    public function roleLabel(): string
    {
        return $this->roleEnum()?->label() ?? (string) $this->role;
    }

    /**
     * Permission keys granted to this user's role.
     *
     * @return list<string>
     */
    public function permissions(): array
    {
        return app(PermissionRegistry::class)->permissionsFor($this->role);
    }

    public function hasPermission(string $permission): bool
    {
        return app(PermissionRegistry::class)->roleHas($this->role, $permission);
    }

    /** True when the role sees every wilaya rather than only its own. */
    public function isNational(): bool
    {
        return $this->roleEnum()?->isNational() ?? false;
    }

    public function isAdmin(): bool
    {
        return $this->roleEnum() === Role::Admin;
    }

    /**
     * Short role + wilaya for approval dialogs (e.g. "DASS · Biskra").
     * Falls back to the project's wilaya name when the user has no wilaya_id.
     */
    public function requesterLabel(?string $fallbackWilayaName = null): string
    {
        if (! $this->relationLoaded('wilaya')) {
            $this->load('wilaya');
        }

        $role = match ((string) $this->role) {
            'dass' => 'DASS',
            'programme_manager' => 'RP',
            'budget_controller' => 'CB',
            'ordonnateur' => 'Ordonnateur',
            default => $this->roleLabel(),
        };

        $wilaya = $this->wilaya?->name_fr ?: $fallbackWilayaName;
        $scope = $wilaya ? "{$role} · {$wilaya}" : $role;
        $name = trim((string) $this->name);

        return $name !== '' ? "{$scope} ({$name})" : $scope;
    }

    public static function requesterLabelFor(?self $user, ?string $fallbackWilayaName = null): string
    {
        if ($user) {
            return $user->requesterLabel($fallbackWilayaName);
        }

        return $fallbackWilayaName ? 'DASS · '.$fallbackWilayaName : '—';
    }
}
