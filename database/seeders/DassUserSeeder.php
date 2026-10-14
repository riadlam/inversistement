<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DassUserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [];

        for ($i = 1; $i <= 58; $i++) {
            $id = str_pad((string) $i, 2, '0', STR_PAD_LEFT);
            $users[] = [
                'name' => "مديرية النشاط الاجتماعي لولاية {$i}",
                'username' => "dass_{$id}",
                'email' => "dass_{$id}@msnfcf.gov.dz",
                'password' => '',
                'role' => 'dass',
                'wilaya_id' => $i,
                'is_active' => true,
                'email_verified_at' => null,
                'remember_token' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        foreach ($users as $user) {
            DB::table('users')->updateOrInsert(
                ['username' => $user['username']],
                $user,
            );
        }
    }
}
