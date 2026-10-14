<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Models\Project;
use App\Policies\ProjectPolicy;
use Laravel\Sanctum\Sanctum;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\SeedsPlatform;
use Tests\TestCase;

class RbacPolicyTest extends TestCase
{
    use RefreshDatabase, SeedsPlatform;

    public function test_dass_is_confined_to_its_wilaya(): void
    {
        $this->seedAccessControl();

        $biskra = $this->makeWilaya('07', 'Biskra');
        $alger = $this->makeWilaya('16', 'Alger');
        $programme = $this->makeProgramme();

        $dass = $this->makeUser(Role::Dass, $biskra);
        $admin = $this->makeUser(Role::Admin);

        $own = $this->makeProject($admin, $biskra, $programme, ['fip_id' => 'FIP-OWN']);
        $other = $this->makeProject($admin, $alger, $programme, ['fip_id' => 'FIP-OTHER']);

        $policy = new ProjectPolicy;

        $this->assertTrue($policy->view($dass, $own));
        $this->assertFalse($policy->view($dass, $other));
        $this->assertTrue($policy->view($admin, $other));
    }

    public function test_gestionnaire_cannot_arbitrate_or_close(): void
    {
        $this->seedAccessControl();

        $wilaya = $this->makeWilaya();
        $programme = $this->makeProgramme();
        $dass = $this->makeUser(Role::Dass, $wilaya);
        $manager = $this->makeUser(Role::ProgrammeManager);
        $admin = $this->makeUser(Role::Admin);

        $project = $this->makeProject($admin, $wilaya, $programme, ['status' => 'in_arbitration']);

        $policy = new ProjectPolicy;

        $this->assertFalse($policy->arbitrate($dass, $project));
        $this->assertTrue($policy->arbitrate($manager, $project));
        $this->assertFalse($policy->close($dass, $project));
        $this->assertTrue($policy->close($manager, $project));
    }

    public function test_auth_me_returns_role_and_permissions(): void
    {
        $this->seedAccessControl();

        $user = $this->makeUser(Role::Admin);

        Sanctum::actingAs($user);

        $this->getJson('/api/auth/me')
            ->assertOk()
            ->assertJsonPath('user.role', 'admin')
            ->assertJsonStructure(['user' => ['role', 'role_label', 'permissions']]);
    }
}
