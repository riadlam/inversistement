<?php

namespace Database\Seeders;

use App\Enums\Role;
use App\Models\User;
use App\Models\Wilaya;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * One signed-in-able account per RACI profile, so the permission matrix can be
 * exercised end to end without hand-crafting users.
 *
 * Wilaya-scoped roles land in Biskra, which is where the scanned fixture lives,
 * so a DASS login sees a fully populated dossier rather than an empty list.
 */
class RoleTestAccountSeeder extends Seeder
{
    /** Same password for every demo account; these are not production users. */
    public const PASSWORD = 'password';

    public function run(): void
    {
        $biskra = Wilaya::where('code', '07')->value('id');

        foreach (Role::cases() as $role) {
            $username = $role->value === 'dass' ? 'dass_demo' : $role->value;

            User::updateOrCreate(
                ['username' => $username],
                [
                    'name' => $role->label(),
                    'email' => $username.'@msnfcf.gov.dz',
                    'password' => Hash::make(self::PASSWORD),
                    'role' => $role->value,
                    'wilaya_id' => $role->isNational() ? null : $biskra,
                    'is_active' => true,
                    'email_verified_at' => now(),
                ],
            );
        }
    }
}
