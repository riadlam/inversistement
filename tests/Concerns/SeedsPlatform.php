<?php

namespace Tests\Concerns;

use App\Enums\Role;
use App\Models\Programme;
use App\Models\Project;
use App\Models\User;
use App\Models\Wilaya;
use Database\Seeders\CalculationRuleSeeder;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RolePermissionSeeder;

trait SeedsPlatform
{
    protected function seedAccessControl(): void
    {
        $this->seed(PermissionSeeder::class);
        $this->seed(RolePermissionSeeder::class);
        $this->seed(CalculationRuleSeeder::class);
    }

    protected function makeWilaya(string $code = '07', string $name = 'Biskra'): Wilaya
    {
        return Wilaya::create([
            'code' => $code,
            'name_ar' => $name,
            'name_fr' => $name,
        ]);
    }

    protected function makeProgramme(): Programme
    {
        return Programme::create([
            'code' => 'PS-TEST',
            'label_fr' => 'Programme test',
            'label_ar' => 'برنامج',
            'type' => 'T3',
        ]);
    }

    protected function makeUser(Role|string $role, ?Wilaya $wilaya = null): User
    {
        $value = $role instanceof Role ? $role->value : $role;

        return User::factory()->create([
            'role' => $value,
            'wilaya_id' => $wilaya?->id,
            'is_active' => true,
        ]);
    }

    protected function makeProject(User $creator, Wilaya $wilaya, Programme $programme, array $attrs = []): Project
    {
        return Project::query()->create(array_merge([
            'fip_id' => 'FIP-'.fake()->unique()->numerify('####'),
            'title_fr' => 'Ferme pédagogique El Hadjeb',
            'title_ar' => 'مزرعة بيداغوجية',
            'programme_id' => $programme->id,
            'wilaya_id' => $wilaya->id,
            'status' => 'in_execution',
            'ae_da' => 80_000_000,
            'cp_da' => 40_000_000,
            'created_by' => $creator->id,
        ], $attrs));
    }
}
