<?php

require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Livewire\Dashboard\ProjectFiche;
use App\Models\Project;
use App\Models\User;
use Illuminate\Support\Facades\Event;
use Livewire\Livewire;

// Reverb is not running during this harness; keep the broadcasts in memory.
config(['broadcasting.default' => 'null']);
Event::fake([
    App\Events\ProjectMetricsRecalculated::class,
    App\Events\AlertRaised::class,
]);

$project = Project::where('fip_id', 'FIP-2010-BISKRA-FP')->firstOrFail();
$admin = User::where('role', 'admin')->firstOrFail();
$observer = User::where('role', 'police')->firstOrFail();

$check = function (string $label, callable $fn) {
    try {
        $fn();
        echo "  ok    {$label}\n";
    } catch (Throwable $e) {
        echo '  FAIL  '.$label.' :: '.mb_substr($e->getMessage(), 0, 200).' ('.basename($e->getFile()).':'.$e->getLine().")\n";
    }
};

echo "--- admin writes ---\n";
auth()->login($admin);

$lotsBefore = $project->lots()->count();

$component = Livewire::test(ProjectFiche::class, ['projectId' => $project->fip_id]);

$check('create lot', function () use ($component, $project, $lotsBefore) {
    $component->call('newLot')
        ->set('lotForm.tranche', 9)
        ->set('lotForm.lot_number', 91)
        ->set('lotForm.designation', 'Lot de test — clôture')
        ->set('lotForm.designation_ar', 'حصة تجريبية')
        ->set('lotForm.kind', 'travaux')
        ->set('lotForm.state', 'en_cours')
        ->set('lotForm.amount_da', 4_500_000)
        ->set('lotForm.progress_pct', 35)
        ->call('saveLot')
        ->assertHasNoErrors();

    $after = $project->lots()->count();
    if ($after !== $lotsBefore + 1) {
        throw new RuntimeException("lot count {$lotsBefore} -> {$after}");
    }
});

$lot = $project->lots()->where('tranche', 9)->where('lot_number', 91)->firstOrFail();

$check('duplicate lot rejected', function () use ($component) {
    $component->call('newLot')
        ->set('lotForm.tranche', 9)
        ->set('lotForm.lot_number', 91)
        ->set('lotForm.designation', 'Doublon')
        ->call('saveLot')
        ->assertHasErrors('lotForm.lot_number');
});

$check('add milestone', function () use ($component, $lot) {
    $component->call('cancelLot')
        ->call('openMilestones', $lot->id)
        ->set('milestoneForm.type', 'ordre_service')
        ->set('milestoneForm.date', '2026-03-14')
        ->set('milestoneForm.reference', 'ODS/2026/91')
        ->call('addMilestone')
        ->assertHasNoErrors();

    if ($lot->milestones()->count() !== 1) {
        throw new RuntimeException('milestone not stored');
    }
});

$check('save foncier', function () use ($component, $project) {
    $component->set('land.affectation_decision_ref', 'ARR/07/2026/118')
        ->set('land.affectation_decision_date', '2026-02-01')
        ->set('land.superficie_ha', 12)
        ->set('land.superficie_a', 45)
        ->set('land.superficie_ca', 60)
        ->set('land.location', 'El Hadjeb')
        ->call('saveLand')
        ->assertHasNoErrors();

    $land = $project->fresh()->land;
    if ((int) $land->superficie_ha !== 12 || (int) $land->superficie_ca !== 60) {
        throw new RuntimeException('superficie not stored');
    }
});

$check('save maîtrise d\'œuvre', function () use ($component, $project) {
    $component->set('mission.bet_name', 'BET SAHARA')
        ->set('mission.esquisse_approved_at', '2025-05-10')
        ->call('saveMission')
        ->assertHasNoErrors();

    if ($project->fresh()->designMissions->first()?->bet_name !== 'BET SAHARA') {
        throw new RuntimeException('BET not stored');
    }
});

$check('save réseaux', function () use ($component, $project) {
    $component->set('networks.eau.connected', true)
        ->set('networks.gaz.connected', false)
        ->set('networks.gaz.motif', 'Réseau distant de 1,8 km')
        ->call('saveNetworks')
        ->assertHasNoErrors();

    $links = $project->fresh()->networkLinks->keyBy('network');
    if (! $links['eau']->connected || $links['gaz']->connected) {
        throw new RuntimeException('network flags not stored');
    }
});

$check('create contractor from lot form', function () use ($component) {
    $component->call('newLot')
        ->set('contractorForm.name', 'ETB TEST BISKRA')
        ->set('contractorForm.legal_form', 'sarl')
        ->set('contractorForm.nif', '000712345678901')
        ->call('saveContractor')
        ->assertHasNoErrors()
        ->assertSet('showContractorForm', false);

    if ($component->get('lotForm.contractor_id') === null) {
        throw new RuntimeException('contractor not attached to the open lot form');
    }
});

$check('réévaluation request then approve', function () use ($component, $project) {
    $next = ($project->aeRevaluations()->max('sequence') ?? 0) + 1;

    $component->call('cancelLot')
        ->call('newRevaluation')
        ->set('revaluationForm.sequence', min(5, $next))
        ->set('revaluationForm.year', 2026)
        ->set('revaluationForm.requested_amount_da', 8_000_000)
        ->set('revaluationForm.decision_ref', 'MF/2026/RE/44')
        ->set('revaluationForm.justification', 'Hausse des prix des matériaux.')
        ->call('saveRevaluation')
        ->assertHasNoErrors();

    $pending = $project->aeRevaluations()->where('status', 'pending')->latest('id')->firstOrFail();

    $component->call('approveRevaluation', $pending->id);

    if ($pending->fresh()->status !== 'approved') {
        throw new RuntimeException('approval did not stick');
    }

    if ((int) $project->fresh()->reevaluation_count !== $project->aeRevaluations()->where('status', 'approved')->count()) {
        throw new RuntimeException('reevaluation_count out of sync');
    }
});

$check('delete lot cascades milestones', function () use ($component, $lot) {
    $component->call('deleteLot', $lot->id);

    if (App\Models\ProjectLot::whereKey($lot->id)->exists()) {
        throw new RuntimeException('lot still present');
    }

    if (App\Models\LotMilestone::where('lot_id', $lot->id)->exists()) {
        throw new RuntimeException('orphan milestones left behind');
    }
});

echo "\n--- observer is read-only ---\n";
auth()->logout();
auth()->login($observer);

$observerProject = Project::where('wilaya_id', $observer->wilaya_id)->firstOrFail();
$observerComponent = Livewire::test(ProjectFiche::class, ['projectId' => $observerProject->fip_id]);

$check('cannot manage lots', function () use ($observerComponent) {
    if ($observerComponent->instance()->canManageLots()) {
        throw new RuntimeException('observer reports write access');
    }
});

foreach (['newLot', 'saveLand', 'saveNetworks', 'saveMission'] as $action) {
    $check("{$action} forbidden", function () use ($observerComponent, $action) {
        $observerComponent->call($action)->assertForbidden();
    });
}

$check('newRevaluation forbidden', function () use ($observerComponent) {
    $observerComponent->call('newRevaluation')->assertForbidden();
});

echo "\n--- events ---\n";
$recalcs = count(Event::dispatched(App\Events\ProjectMetricsRecalculated::class));
echo "  ProjectMetricsRecalculated dispatched: {$recalcs}\n";
