<?php

namespace App\Livewire\Dashboard;

use App\Exports\EtatGlobalExport;
use App\Models\Project;
use App\Models\Wilaya;
use Illuminate\Support\Collection;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

#[Layout('layouts.dashboard')]
class ReportsCenter extends Component
{
    public function title(): string
    {
        return __('ui.reports.title');
    }
    /** Filters of the DGB canevas, per TDR §11. */
    public ?int $wilayaId = null;

    public ?string $titre = null;

    public ?int $year = null;

    public ?string $arreteeAu = null;

    /** Fiche de suivi picker. */
    public ?string $ficheProjectId = null;

    public function mount(): void
    {
        $this->authorize('viewAny', Project::class);

        $user = auth()->user();

        if (! $user->isNational()) {
            $this->wilayaId = $user->wilaya_id;
        }

        $this->arreteeAu = now()->format('Y-m-d');
        $this->ficheProjectId = Project::visibleTo($user)->value('fip_id');
    }

    public function canExport(): bool
    {
        return auth()->user()?->hasPermission('reports.export') ?? false;
    }

    /** Streams the canevas straight from the Livewire action. */
    public function downloadEtatGlobal(): BinaryFileResponse
    {
        abort_unless($this->canExport(), 403);

        $this->validate([
            'wilayaId' => 'nullable|integer|exists:wilayas,id',
            'titre' => 'nullable|in:T3,T4',
            'year' => 'nullable|integer|min:1990|max:2100',
            'arreteeAu' => 'nullable|date',
        ]);

        $user = auth()->user();

        $export = new EtatGlobalExport(
            [
                'wilaya_id' => $user->isNational() ? $this->wilayaId : $user->wilaya_id,
                'titre' => $this->titre,
                'year' => $this->year,
            ],
            $this->arreteeAu ? \Illuminate\Support\Carbon::parse($this->arreteeAu) : null,
        );

        return Excel::download($export, $export->filename());
    }

    /** @return Collection<int, Wilaya> */
    public function wilayas(): Collection
    {
        $user = auth()->user();

        return $user->isNational()
            ? Wilaya::orderBy('code')->get(['id', 'code', 'name_fr'])
            : Wilaya::whereKey($user->wilaya_id)->get(['id', 'code', 'name_fr']);
    }

    public function render()
    {
        return view('livewire.dashboard.reports-center', [
            'wilayas' => $this->wilayas(),
            'projects' => Project::visibleTo()
                ->orderBy('title_fr')
                ->get(['fip_id', 'title_fr', 'operation_number']),
            'years' => Project::visibleTo()
                ->whereNotNull('notification_year')
                ->distinct()
                ->orderByDesc('notification_year')
                ->pluck('notification_year'),
        ]);
    }
}
