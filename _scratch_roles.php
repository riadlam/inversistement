<?php

require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Enums\Role;
use App\Models\User;
use Illuminate\Support\Facades\DB;

foreach (Role::cases() as $role) {
    $users = User::where('role', $role->value)->pluck('username')->implode(', ');
    $perms = DB::table('role_permissions')
        ->join('permissions', 'permissions.id', '=', 'role_permissions.permission_id')
        ->where('role_permissions.role', $role->value)
        ->count();

    printf("%-14s %-46s perms=%-3d users=[%s]\n", $role->value, $role->label(), $perms, $users);
}

echo "\nreports.export granted to: ";
echo DB::table('role_permissions')
    ->join('permissions', 'permissions.id', '=', 'role_permissions.permission_id')
    ->where('permissions.key', 'reports.export')
    ->pluck('role_permissions.role')
    ->implode(', ')."\n";
