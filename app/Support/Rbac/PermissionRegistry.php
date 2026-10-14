<?php

namespace App\Support\Rbac;

use App\Enums\Permission as PermissionEnum;
use App\Models\Permission;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

/**
 * Resolves the role => permissions grid from the database, falling back to the
 * seeded default matrix when the tables are not present yet (fresh install,
 * or during the migration that creates them).
 */
class PermissionRegistry
{
    private const CACHE_KEY = 'rbac.matrix';

    private const CACHE_TTL = 3600;

    /** @var array<string, list<string>>|null */
    private ?array $matrix = null;

    /** @return array<string, list<string>> */
    public function matrix(): array
    {
        if ($this->matrix !== null) {
            return $this->matrix;
        }

        $this->matrix = Cache::remember(
            self::CACHE_KEY,
            self::CACHE_TTL,
            fn () => $this->loadFromDatabase() ?? PermissionEnum::matrix(),
        );

        return $this->matrix;
    }

    /** @return list<string> */
    public function permissionsFor(?string $role): array
    {
        if ($role === null || $role === '') {
            return [];
        }

        return $this->matrix()[$role] ?? [];
    }

    public function roleHas(?string $role, string $permission): bool
    {
        return in_array($permission, $this->permissionsFor($role), true);
    }

    public function flush(): void
    {
        $this->matrix = null;
        Cache::forget(self::CACHE_KEY);
    }

    /**
     * @return array<string, list<string>>|null null when the RBAC tables are unavailable or empty
     */
    private function loadFromDatabase(): ?array
    {
        if (! Schema::hasTable('permissions') || ! Schema::hasTable('role_permissions')) {
            return null;
        }

        $rows = Permission::query()
            ->join('role_permissions', 'role_permissions.permission_id', '=', 'permissions.id')
            ->get(['role_permissions.role as role', 'permissions.key as key']);

        if ($rows->isEmpty()) {
            return null;
        }

        return $rows
            ->groupBy('role')
            ->map(fn ($group) => $group->pluck('key')->unique()->values()->all())
            ->all();
    }
}
