<?php

namespace App\Livewire\Dashboard;

use App\Livewire\Concerns\ListensToPortfolioBroadcasts;
use App\Enums\Permission;
use App\Models\Programme;
use App\Models\Project;
use App\Models\TabEtablissement;
use App\Models\Wilaya;
use App\Services\Budget\ProjectBudget;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.dashboard')]
class ProjectList extends Component
{
    use ListensToPortfolioBroadcasts;

    public string $filterWilaya = '';

    public string $filterCommune = '';

    public string $filterEtablissement = '';

    public string $filterProgramme = '';

    public string $filterStatus = '';

    public function refreshLiveData(): void
    {
        unset($this->filteredProjects);
    }

    #[Computed]
    public function filterStatusOptions(): array
    {
        return [
            ['value' => 'draft', 'label' => __('ui.status.draft')],
            ['value' => 'maturation', 'label' => __('ui.status.maturation')],
            ['value' => 'in_arbitration', 'label' => __('ui.status.in_arbitration')],
            ['value' => 'pending_visa', 'label' => __('ui.status.pending_visa')],
            ['value' => 'returned', 'label' => __('ui.status.returned')],
            ['value' => 'approved', 'label' => __('ui.status.approved')],
            ['value' => 'in_execution', 'label' => __('ui.status.in_execution')],
            ['value' => 'completed', 'label' => __('ui.status.completed')],
            ['value' => 'closed', 'label' => __('ui.status.closed')],
        ];
    }

    #[Computed]
    public function hasActiveFilters(): bool
    {
        return $this->filterWilaya !== ''
            || $this->filterCommune !== ''
            || $this->filterEtablissement !== ''
            || $this->filterProgramme !== ''
            || $this->filterStatus !== '';
    }

    #[Computed]
    public function canCreate(): bool
    {
        return auth()->user()?->hasPermission(Permission::ProjectsCreate->value) ?? false;
    }

    /** National roles may filter by wilaya; everyone else is pinned to their own. */
    #[Computed]
    public function canFilterWilaya(): bool
    {
        return auth()->user()?->isNational() ?? false;
    }

    /** The commune filter only makes sense for a user attached to a wilaya. */
    #[Computed]
    public function canFilterCommune(): bool
    {
        return auth()->user()?->wilaya_id !== null;
    }

    #[Computed]
    public function filteredProjects(): array
    {
        // Relations are eager-loaded because the budget engine reads all of
        // them per row; without this the register would be an N+1.
        return Project::query()
            ->with([
                'programme:id,code,label_fr',
                'wilaya:id,name_fr',
                'maturationItems',
                'physicalProgress',
                'cpPayments',
                'aeEngagements',
                'aeRevaluations',
                'aeCpEnvelopes',
            ])
            ->visibleTo()
            ->when($this->filterStatus === 'returned', fn ($q) => $q
                ->whereIn('projects.status', ['draft', 'maturation'])
                ->whereNotNull('projects.rejection_reason')
                ->where('projects.rejection_reason', '!=', ''))
            ->when($this->filterStatus === 'in_arbitration', fn ($q) => $q
                ->whereIn('projects.status', ['submitted', 'in_arbitration']))
            ->when(
                $this->filterStatus !== ''
                && ! in_array($this->filterStatus, ['returned', 'in_arbitration'], true),
                fn ($q) => $q
                    ->where('projects.status', $this->filterStatus)
                    ->when($this->filterStatus === 'draft', fn ($dq) => $dq->where(function ($w) {
                        $w->whereNull('projects.rejection_reason')
                            ->orWhere('projects.rejection_reason', '');
                    }))
            )
            ->when($this->filterWilaya !== '', fn ($q) => $q->whereHas('wilaya', fn ($w) => $w->where('name_fr', $this->filterWilaya)))
            ->when($this->filterCommune !== '', fn ($q) => $q->where('projects.commune_code', $this->filterCommune))
            ->when($this->filterEtablissement !== '', fn ($q) => $q->where('projects.establishment_id', $this->filterEtablissement))
            ->when($this->filterProgramme !== '', fn ($q) => $q->where('projects.programme_id', (int) $this->filterProgramme))
            ->orderByDesc('projects.created_at')
            ->get()
            ->map(function (Project $p) {
                $budget = ProjectBudget::for($p);

                return [
                    'id' => $p->fip_id,
                    'title' => $p->title_fr,
                    'program' => $p->programme?->label_fr ?? $p->programme?->code ?? '—',
                    'ae_da' => $budget->apActuelle(),
                    'cp_da' => $budget->get('cp_notifies'),
                    'physical' => $budget->tauxPhysique(),
                    'financial' => $budget->tauxFinancier(),
                    'spu' => $budget->spu(),
                    'traffic' => $budget->trafficLight(),
                    'status' => $p->displayStatus(),
                    'rejection_reason' => $p->rejection_reason,
                    'can_edit' => auth()->user()?->can('update', $p) ?? false,
                    'can_delete' => auth()->user()?->can('delete', $p) ?? false,
                    'wilaya' => $p->wilaya?->name_fr ?? '—',
                ];
            })
            ->all();
    }

    #[Computed]
    public function wilayaOptions(): array
    {
        $user = auth()->user();

        if ($user && ! $user->isNational()) {
            $name = $user->wilaya?->name_fr
                ?? DB::table('wilayas')->where('id', $user->wilaya_id)->value('name_fr');

            return $name ? [$name] : [];
        }

        return DB::table('wilayas')->orderBy('name_fr')->pluck('name_fr')->all();
    }

    #[Computed]
    public function communeOptions(): array
    {
        $user = request()->user();
        if (! $user || ! $user->wilaya_id) {
            return [];
        }

        $wilaya = Wilaya::find($user->wilaya_id);
        if (! $wilaya) {
            return [];
        }

        return $wilaya->communeQuery()
            ->orderBy('lib_comm_fr')
            ->get(['code_comm', 'lib_comm_fr'])
            ->all();
    }

    #[Computed]
    public function establishmentOptions(): array
    {
        $user = auth()->user();
        $query = TabEtablissement::query()->orderBy('libelle')->orderBy('wilaya');

        if ($user && ! $user->isNational() && $user->wilaya_id) {
            $wilaya = $user->wilaya ?? Wilaya::find($user->wilaya_id);

            if ($wilaya) {
                $query->whereIn('wilaya', $wilaya->territoryKeys());
            }
        }

        return $query->get(['id', 'libelle', 'wilaya'])->all();
    }

    #[Computed]
    public function filterWilayaOptions(): array
    {
        return array_map(fn ($name) => ['value' => $name, 'label' => $name], $this->wilayaOptions);
    }

    #[Computed]
    public function filterCommuneOptions(): array
    {
        return array_map(fn ($c) => ['value' => $c->code_comm, 'label' => $c->lib_comm_fr], $this->communeOptions);
    }

    #[Computed]
    public function filterEtablissementOptions(): array
    {
        return array_map(fn ($e) => ['value' => (string) $e->id, 'label' => $e->libelle], $this->establishmentOptions);
    }

    #[Computed]
    public function filterProgrammeOptions(): array
    {
        return Programme::orderBy('label_fr')->get()->map(
            fn ($p) => ['value' => (string) $p->id, 'label' => ($p->label_fr ?: $p->code) . ($p->type ? ' (' . $p->type . ')' : '')]
        )->all();
    }

    public function render()
    {
        return view('livewire.dashboard.project-list')
            ->title(__('ui.projects.title'));
    }
}
