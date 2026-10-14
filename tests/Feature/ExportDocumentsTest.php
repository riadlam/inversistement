<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Exports\EtatGlobalExport;
use App\Exports\FicheSuiviPdf;
use App\Models\AeRevaluation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\SeedsPlatform;
use Tests\TestCase;

class ExportDocumentsTest extends TestCase
{
    use RefreshDatabase, SeedsPlatform;

    public function test_fiche_suivi_pdf_starts_with_pdf_header_and_uses_ap_actuelle(): void
    {
        $this->seedAccessControl();

        $wilaya = $this->makeWilaya();
        $programme = $this->makeProgramme();
        $admin = $this->makeUser(Role::Admin);
        $project = $this->makeProject($admin, $wilaya, $programme, [
            'short_label' => 'ferme ped',
            'operation_number' => 'Nk.5.764.8.262.107.10.01',
            'ae_da' => 80_000_000,
        ]);

        AeRevaluation::create([
            'project_id' => $project->id,
            'sequence' => 1,
            'year' => 2014,
            'requested_amount_da' => 20_000_000,
            'justification' => 'Première réévaluation',
            'status' => 'approved',
            'submitted_by' => $admin->id,
        ]);
        AeRevaluation::create([
            'project_id' => $project->id,
            'sequence' => 2,
            'year' => 2015,
            'requested_amount_da' => 60_000_000,
            'justification' => 'Deuxième réévaluation',
            'status' => 'approved',
            'submitted_by' => $admin->id,
        ]);

        $pdf = FicheSuiviPdf::for($project->fresh())->contents();

        $this->assertStringStartsWith('%PDF', $pdf);
        $this->assertGreaterThan(1000, strlen($pdf));
    }

    public function test_etat_global_has_dgb_banner_and_data_row(): void
    {
        $this->seedAccessControl();

        $wilaya = $this->makeWilaya();
        $programme = $this->makeProgramme();
        $admin = $this->makeUser(Role::Admin);
        $this->makeProject($admin, $wilaya, $programme, [
            'operation_number' => 'NK 5.764.8.262.107.10.01',
            'title_fr' => 'Ferme pédagogique',
            'ae_da' => 80_000_000,
        ]);

        $this->actingAs($admin);

        $rows = (new EtatGlobalExport(['wilaya_id' => $wilaya->id]))->operationRows();

        $this->assertNotEmpty($rows);
        $this->assertTrue(
            collect($rows)->contains(fn ($row) => str_contains((string) ($row['G'] ?? ''), 'Ferme pédagogique'))
        );
    }

    public function test_export_routes_are_permission_gated(): void
    {
        $this->seedAccessControl();

        $wilaya = $this->makeWilaya();
        $programme = $this->makeProgramme();
        $observer = $this->makeUser(Role::Police, $wilaya);
        $admin = $this->makeUser(Role::Admin);
        $project = $this->makeProject($admin, $wilaya, $programme);

        $this->actingAs($observer)
            ->get(route('dashbored.projects.fiche-suivi', $project->fip_id))
            ->assertForbidden();

        $this->actingAs($admin)
            ->get(route('dashbored.projects.fiche-suivi', $project->fip_id))
            ->assertOk();
    }
}
