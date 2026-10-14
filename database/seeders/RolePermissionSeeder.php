<?php

namespace Database\Seeders;

use App\Enums\Permission as PermissionEnum;
use App\Models\Permission;
use App\Models\RolePermission;
use App\Support\Rbac\PermissionRegistry;
use Illuminate\Database\Seeder;

/**
 * Seeds the TDR RACI matrix. Idempotent: re-running restores the default grid
 * without duplicating rows, which also makes it a reset button if an
 * administrator edits the matrix into a broken state.
 */
class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissionIds = Permission::pluck('id', 'key');

        foreach (PermissionEnum::matrix() as $role => $keys) {
            foreach ($keys as $key) {
                $permissionId = $permissionIds[$key] ?? null;

                if ($permissionId === null) {
                    continue;
                }

                RolePermission::updateOrCreate([
                    'role' => $role,
                    'permission_id' => $permissionId,
                ]);
            }
        }

        app(PermissionRegistry::class)->flush();
    }
}
