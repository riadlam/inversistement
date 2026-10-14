<?php

namespace Database\Seeders;

use App\Enums\Permission as PermissionEnum;
use App\Models\Permission;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        foreach (PermissionEnum::cases() as $permission) {
            Permission::updateOrCreate(
                ['key' => $permission->value],
                [
                    'label_fr' => $permission->label(),
                    'category' => $permission->category(),
                ],
            );
        }
    }
}
