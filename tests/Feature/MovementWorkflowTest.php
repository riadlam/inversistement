<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Livewire\Dashboard\QuickActionsPage;
use App\Models\AeEngagement;
use App\Models\Movement;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\Concerns\SeedsPlatform;
use Tests\TestCase;

class MovementWorkflowTest extends TestCase
{
    use RefreshDatabase, SeedsPlatform;

    public function test_cancellation_is_refused_when_engagements_exist(): void
    {
        $this->seedAccessControl();

        $wilaya = $this->makeWilaya();
        $programme = $this->makeProgramme();
        $dass = $this->makeUser(Role::Dass, $wilaya);
        $project = $this->makeProject($dass, $wilaya, $programme);

        AeEngagement::create([
            'project_id' => $project->id,
            'amount_da' => 1_000_000,
            'description' => 'Marché',
            'engaged_at' => now(),
            'created_by' => $dass->id,
        ]);

        Livewire::actingAs($dass)
            ->test(QuickActionsPage::class)
            ->set('panel', 'movement')
            ->set('movementType', 'cancellation')
            ->set('movementSourceId', $project->id)
            ->set('movementAmount', 500_000)
            ->set('movementJustification', 'Annulation de reliquat après arrêt du chantier.')
            ->call('submitMovement')
            ->assertHasErrors('movementSourceId');

        $this->assertDatabaseCount('movements', 0);
    }

    public function test_programme_manager_approves_a_transfer(): void
    {
        $this->seedAccessControl();

        $wilaya = $this->makeWilaya();
        $programme = $this->makeProgramme();
        $dass = $this->makeUser(Role::Dass, $wilaya);
        $manager = $this->makeUser(Role::ProgrammeManager);
        $source = $this->makeProject($dass, $wilaya, $programme, ['fip_id' => 'FIP-SRC']);
        $target = $this->makeProject($dass, $wilaya, $programme, ['fip_id' => 'FIP-DST']);

        $movement = Movement::create([
            'source_project_id' => $source->id,
            'target_project_id' => $target->id,
            'amount_da' => 2_000_000,
            'type' => 'transfer',
            'justification' => 'Redéploiement vers un projet prioritaire.',
            'status' => 'submitted',
            'submitted_by' => $dass->id,
        ]);

        Livewire::actingAs($manager)
            ->test(QuickActionsPage::class)
            ->call('approveMovement', $movement->id)
            ->assertHasNoErrors();

        $this->assertSame('approved', $movement->fresh()->status);
    }
}
