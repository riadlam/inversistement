@php
    use App\Services\Fiche\LotBuckets;
    use App\Support\Money;

    $editable = $this->canManageLots();
    $tabs = [
        'lots' => __('ui.fiche.tab_lots'),
        'ap' => __('ui.fiche.tab_ap'),
        'foncier' => __('ui.fiche.tab_land'),
        'reseaux' => __('ui.fiche.tab_networks'),
        'situation' => __('ui.fiche.tab_situation'),
        'identification' => __('ui.fiche.tab_identification'),
        'physique' => __('ui.fiche.tab_physical'),
    ];
@endphp

<div class="ds-page-enter max-w-6xl">
    <x-dashboard.page-header
        :title="__('ui.fiche.heading', ['id' => $project->operation_number ?: $project->fip_id])"
        :subtitle="$project->title_fr"
    >
        <x-slot:actions>
            <a href="{{ route('dashbored.projects.show', $project->fip_id) }}" class="btn btn-outline-secondary btn-sm" wire:navigate>
                {{ __('ui.fiche.dossier') }}
            </a>
            @can('export', $project)
                <a href="{{ route('dashbored.projects.fiche-suivi', $project->fip_id) }}" class="btn btn-primary btn-sm">
                    {{ __('ui.fiche.export_pdf') }}
                </a>
            @endcan
        </x-slot:actions>
    </x-dashboard.page-header>

    @if (session('fiche-saved'))
        <div class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-[13px] font-medium text-emerald-900">
            {{ session('fiche-saved') }}
        </div>
    @endif

    @unless ($editable)
        @if ($tab !== 'physique')
            <div class="mb-4 rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-[13px] text-slate-700">
                {{ __('ui.fiche.readonly') }}
            </div>
        @endif
    @endunless

    {{-- Totals, straight from the engine, so the operator sees the effect of each save. --}}
    <div class="mb-5 grid gap-3 sm:grid-cols-3 lg:grid-cols-5">
        @foreach ([
            [__('ui.fiche.ap_actuelle'), $budget->apActuelle()],
            [__('ui.fiche.cumul_eng'), $budget->cumulEngagements()],
            [__('ui.fiche.cumul_pay'), $budget->cumulPaiements()],
            [__('ui.fiche.rar'), $budget->rar()],
            [__('ui.fiche.reste_engager'), $budget->resteAEngager()],
        ] as [$label, $value])
            <div class="rounded-xl border border-[var(--ds-border)] bg-white px-3 py-2">
                <p class="text-[10px] uppercase tracking-wide text-[var(--ds-text-faint)]">{{ $label }}</p>
                <p class="text-[13px] font-semibold text-[var(--ds-text)]">{{ Money::da($value) }}</p>
            </div>
        @endforeach
    </div>

    <div class="mb-4 flex flex-wrap gap-1 border-b border-[var(--ds-border)]">
        @foreach ($tabs as $key => $label)
            <button type="button" wire:click="$set('tab', '{{ $key }}')"
                    class="rounded-t-lg px-3 py-2 text-[12px] font-semibold {{ $tab === $key ? 'bg-white text-[var(--ds-text)] shadow-[inset_0_-2px_0_var(--ds-accent)]' : 'text-[var(--ds-text-muted)] hover:text-[var(--ds-text)]' }}">
                {{ $label }}
            </button>
        @endforeach
    </div>

    {{-- ============================ LOTS ============================ --}}
    @if ($tab === 'lots')
        @foreach (LotBuckets::BUCKETS as $bucket => $state)
            @php $rows = $buckets->lots($bucket); @endphp
            <div class="ds-card mb-4">
                <div class="ds-card-header flex items-center justify-between">
                    <div>
                        <h3 class="text-[13px] font-semibold">{{ LotBuckets::TABLES[$bucket]['title'] }}</h3>
                        <p class="text-[11px] text-[var(--ds-text-faint)]">{{ $rows->count() }} lot(s)</p>
                    </div>
                    <span class="text-[13px] font-semibold">{{ LotBuckets::TABLES[$bucket]['total'] }} : {{ Money::da($buckets->total($bucket)) }}</span>
                </div>
                <x-dashboard.ui-table>
                    <thead>
                    <tr>
                        <th>Tr.</th>
                        <th>Lot</th>
                        <th>Désignation</th>
                        <th class="text-right">Montant</th>
                        <th>Entreprise</th>
                        <th>Avanc.</th>
                        <th></th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse ($rows as $lot)
                        <tr>
                            <td>{{ $lot->tranche }}</td>
                            <td>{{ $lot->lot_number }}</td>
                            <td class="font-medium">
                                {{ $lot->designation }}
                                @if ($lot->blocking_reason)
                                    <span class="block text-[11px] text-amber-700">{{ $blockingReasons[$lot->blocking_reason] }}</span>
                                @endif
                                @if ($lot->blocking_note_ar)
                                    <span class="block text-[12px] text-[var(--ds-text-muted)]" dir="rtl">{{ $lot->blocking_note_ar }}</span>
                                @endif
                            </td>
                            <td class="text-right whitespace-nowrap">{{ Money::da($lot->effectiveAmountDa()) }}</td>
                            <td>{{ $lot->contractor?->name ?? '—' }}</td>
                            <td>{{ Money::pct($lot->progress_pct) }}</td>
                            <td class="text-right whitespace-nowrap">
                                <button type="button" wire:click="openMilestones({{ $lot->id }})" class="btn btn-sm btn-outline-secondary">
                                    Étapes ({{ $lot->milestones->count() }})
                                </button>
                                @if ($editable)
                                    <button type="button" wire:click="editLot({{ $lot->id }})" class="btn btn-sm btn-outline-primary">Modifier</button>
                                    <button type="button" wire:click="deleteLot({{ $lot->id }})"
                                            wire:confirm="Supprimer le lot {{ $lot->lot_number }} ?" class="btn btn-sm btn-outline-danger">Suppr.</button>
                                @endif
                            </td>
                        </tr>

                        @if ($milestoneLotId === $lot->id)
                            <tr>
                                <td colspan="7" class="bg-[var(--ds-surface-sunken)]">
                                    <p class="mb-2 text-[12px] font-semibold">Étapes de passation — lot {{ $lot->lot_number }}</p>
                                    <ul class="mb-3 space-y-1">
                                        @forelse ($lot->milestones as $milestone)
                                            <li class="flex items-center gap-2 text-[12px]">
                                                <span class="font-medium">{{ $milestoneTypes[$milestone->type] ?? $milestone->type }}</span>
                                                <span class="text-[var(--ds-text-muted)]">{{ $milestone->date?->format('d/m/Y') ?? '—' }}</span>
                                                @if ($milestone->reference)
                                                    <span class="text-[var(--ds-text-faint)]">{{ $milestone->reference }}</span>
                                                @endif
                                                @if ($editable)
                                                    <button type="button" wire:click="deleteMilestone({{ $milestone->id }})" class="text-red-600">×</button>
                                                @endif
                                            </li>
                                        @empty
                                            <li class="text-[12px] text-[var(--ds-text-faint)]">Aucune étape enregistrée.</li>
                                        @endforelse
                                    </ul>
                                    @if ($editable)
                                        <div class="grid gap-2 sm:grid-cols-4">
                                            <select wire:model="milestoneForm.type" class="form-select">
                                                @foreach ($milestoneTypes as $value => $label)
                                                    <option value="{{ $value }}">{{ $label }}</option>
                                                @endforeach
                                            </select>
                                            <input wire:model="milestoneForm.date" type="date" class="form-control" />
                                            <input wire:model="milestoneForm.reference" type="text" class="form-control" placeholder="Référence" />
                                            <button type="button" wire:click="addMilestone" class="btn btn-primary btn-sm">Ajouter l'étape</button>
                                        </div>
                                    @endif
                                </td>
                            </tr>
                        @endif
                    @empty
                        <tr><td colspan="7" class="text-center text-[var(--ds-text-faint)]">Aucun lot.</td></tr>
                    @endforelse
                    </tbody>
                </x-dashboard.ui-table>
            </div>
        @endforeach

        @if ($editable && $lotForm === null)
            <button type="button" wire:click="newLot" class="btn btn-primary btn-sm">+ Ajouter un lot</button>
        @endif

        @if ($lotForm !== null)
            <div class="ds-card mt-4">
                <div class="ds-card-header">
                    <h3 class="text-[13px] font-semibold">{{ $lotForm['id'] ? 'Modifier le lot' : 'Nouveau lot' }}</h3>
                </div>
                <div class="ds-card-body grid gap-3 sm:grid-cols-3">
                    <div>
                        <label class="ds-form-label">Tranche</label>
                        <input wire:model="lotForm.tranche" type="number" min="1" class="form-control" />
                        @error('lotForm.tranche') <p class="ds-form-error">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="ds-form-label">N° de lot</label>
                        <input wire:model="lotForm.lot_number" type="number" min="1" class="form-control" />
                        @error('lotForm.lot_number') <p class="ds-form-error">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="ds-form-label">Nature</label>
                        <select wire:model="lotForm.kind" class="form-select">
                            <option value="travaux">Travaux</option>
                            <option value="equipement">Équipement</option>
                            <option value="etude">Étude</option>
                            <option value="autre">Autre</option>
                        </select>
                    </div>
                    <div class="sm:col-span-2">
                        <label class="ds-form-label">Désignation</label>
                        <input wire:model="lotForm.designation" type="text" class="form-control" />
                        @error('lotForm.designation') <p class="ds-form-error">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="ds-form-label">Désignation (ar)</label>
                        <input wire:model="lotForm.designation_ar" type="text" class="form-control" dir="rtl" />
                    </div>
                    <div>
                        <label class="ds-form-label">État</label>
                        <select wire:model.live="lotForm.state" class="form-select">
                            @foreach ($lotStates as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="ds-form-label">Montant du marché (DA)</label>
                        <input wire:model="lotForm.amount_da" type="number" step="0.01" class="form-control" />
                    </div>
                    <div>
                        <label class="ds-form-label">Estimation (DA)</label>
                        <input wire:model="lotForm.estimation_da" type="number" step="0.01" class="form-control" />
                    </div>

                    @if ($lotForm['state'] === 'non_lance')
                        <div>
                            <label class="ds-form-label">Estimation établie par</label>
                            <input wire:model="lotForm.estimated_by" type="text" class="form-control" placeholder="BET, service technique…" />
                        </div>
                        <div>
                            <label class="ds-form-label">Base d'estimation</label>
                            <select wire:model="lotForm.estimation_basis" class="form-select">
                                <option value="">—</option>
                                <option value="bet">BET</option>
                                <option value="consultation">Consultation</option>
                                <option value="autre">Autre</option>
                            </select>
                        </div>
                    @endif

                    @if ($lotForm['state'] === 'en_cours_lancement')
                        <div>
                            <label class="ds-form-label">Motif de blocage</label>
                            <select wire:model="lotForm.blocking_reason" class="form-select">
                                <option value="">—</option>
                                @foreach ($blockingReasons as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="sm:col-span-2">
                            <label class="ds-form-label">Annotation (arabe)</label>
                            <input wire:model="lotForm.blocking_note_ar" type="text" class="form-control" dir="rtl" />
                        </div>
                    @endif

                    <div>
                        <label class="ds-form-label">Délai (mois)</label>
                        <input wire:model="lotForm.delai_months" type="number" min="1" class="form-control" />
                    </div>
                    <div>
                        <label class="ds-form-label">Date de lancement / ODS</label>
                        <input wire:model="lotForm.launch_date" type="date" class="form-control" />
                    </div>
                    <div>
                        <label class="ds-form-label">Avancement (%)</label>
                        <input wire:model="lotForm.progress_pct" type="number" step="0.01" min="0" max="100" class="form-control" />
                    </div>
                    <div>
                        <label class="ds-form-label">Date de réception</label>
                        <input wire:model="lotForm.reception_date" type="date" class="form-control" />
                    </div>
                    <div class="sm:col-span-2">
                        <label class="ds-form-label">Entreprise titulaire</label>
                        <div class="flex gap-2">
                            <select wire:model="lotForm.contractor_id" class="form-select">
                                <option value="">—</option>
                                @foreach ($contractors as $contractor)
                                    <option value="{{ $contractor->id }}">{{ $contractor->name }}</option>
                                @endforeach
                            </select>
                            <button type="button" wire:click="$toggle('showContractorForm')" class="btn btn-outline-secondary btn-sm whitespace-nowrap">+ Entreprise</button>
                        </div>
                    </div>
                    <div class="sm:col-span-3">
                        <label class="ds-form-label">Observations</label>
                        <textarea wire:model="lotForm.observations" rows="2" class="form-control"></textarea>
                    </div>

                    @if ($showContractorForm)
                        <div class="sm:col-span-3 rounded-xl border border-[var(--ds-border)] bg-[var(--ds-surface-sunken)] p-3">
                            <p class="mb-2 text-[12px] font-semibold">Nouvelle entreprise</p>
                            <div class="grid gap-2 sm:grid-cols-5">
                                <input wire:model="contractorForm.name" type="text" class="form-control" placeholder="Raison sociale" />
                                <select wire:model="contractorForm.legal_form" class="form-select">
                                    <option value="etb">ETB</option>
                                    <option value="sarl">SARL</option>
                                    <option value="eeg">EEG</option>
                                    <option value="eurl">EURL</option>
                                    <option value="spa">SPA</option>
                                    <option value="autre">Autre</option>
                                </select>
                                <input wire:model="contractorForm.nif" type="text" class="form-control" placeholder="NIF" />
                                <input wire:model="contractorForm.business_register" type="text" class="form-control" placeholder="Registre de commerce" />
                                <button type="button" wire:click="saveContractor" class="btn btn-primary btn-sm">Créer</button>
                            </div>
                            @error('contractorForm.name') <p class="ds-form-error">{{ $message }}</p> @enderror
                        </div>
                    @endif

                    <div class="sm:col-span-3 flex gap-2">
                        <button type="button" wire:click="saveLot" class="btn btn-primary btn-sm">Enregistrer le lot</button>
                        <button type="button" wire:click="cancelLot" class="btn btn-outline-secondary btn-sm">Annuler</button>
                    </div>
                </div>
            </div>
        @endif
    @endif

    {{-- ============================ AP ============================ --}}
    @if ($tab === 'ap')
        <div class="ds-card">
            <div class="ds-card-header flex items-center justify-between">
                <div>
                    <h3 class="text-[13px] font-semibold">Réévaluations</h3>
                    <p class="text-[11px] text-[var(--ds-text-faint)]">
                        AP initiale {{ Money::da($budget->apInitiale()) }} → AP actuelle {{ Money::da($budget->apActuelle()) }}
                    </p>
                </div>
                @if ($this->canRequestRevaluation() && $revaluationForm === null)
                    <button type="button" wire:click="newRevaluation" class="btn btn-primary btn-sm">+ Réévaluation</button>
                @endif
            </div>
            <x-dashboard.ui-table>
                <thead>
                <tr>
                    <th>N°</th>
                    <th>Année</th>
                    <th class="text-right">Montant</th>
                    <th>Décision</th>
                    <th>Demandeur</th>
                    <th>Statut</th>
                    <th></th>
                </tr>
                </thead>
                <tbody>
                @forelse ($revaluations as $revaluation)
                    <tr>
                        <td>{{ $revaluation->sequence }}</td>
                        <td>{{ $revaluation->year }}</td>
                        <td class="text-right">{{ Money::da($revaluation->requested_amount_da) }}</td>
                        <td>{{ $revaluation->decision_ref ?? '—' }}</td>
                        <td class="text-[12px]">{{ \App\Models\User::requesterLabelFor($revaluation->submitter, $project->wilaya?->name_fr) }}</td>
                        <td>
                            @if ($revaluation->status === 'approved')
                                <x-dashboard.ui-badge variant="success">Approuvée</x-dashboard.ui-badge>
                            @elseif ($revaluation->status === 'rejected')
                                <x-dashboard.ui-badge variant="danger">Rejetée</x-dashboard.ui-badge>
                            @else
                                <x-dashboard.ui-badge variant="warning">En attente</x-dashboard.ui-badge>
                            @endif
                        </td>
                        <td class="text-right whitespace-nowrap">
                            @if ($this->canApproveRevaluation() && $revaluation->status === 'pending')
                                <button type="button" wire:click="approveRevaluation({{ $revaluation->id }})" class="btn btn-sm btn-primary">Approuver</button>
                                <button type="button" wire:click="rejectRevaluation({{ $revaluation->id }})" class="btn btn-sm btn-outline-danger">Rejeter</button>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center text-[var(--ds-text-faint)]">Aucune réévaluation.</td></tr>
                @endforelse
                </tbody>
            </x-dashboard.ui-table>

            @if ($revaluationForm !== null)
                <div class="ds-card-body grid gap-3 sm:grid-cols-4 border-t border-[var(--ds-border)]">
                    <div>
                        <label class="ds-form-label">Rang (1–5)</label>
                        <input wire:model="revaluationForm.sequence" type="number" min="1" max="5" class="form-control" />
                        @error('revaluationForm.sequence') <p class="ds-form-error">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="ds-form-label">Année</label>
                        <input wire:model="revaluationForm.year" type="number" class="form-control" />
                    </div>
                    <div>
                        <label class="ds-form-label">Montant (DA)</label>
                        <input wire:model="revaluationForm.requested_amount_da" type="number" step="0.01" class="form-control" />
                        @error('revaluationForm.requested_amount_da') <p class="ds-form-error">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="ds-form-label">Référence de décision</label>
                        <input wire:model="revaluationForm.decision_ref" type="text" class="form-control" />
                    </div>
                    <div class="sm:col-span-4">
                        <label class="ds-form-label">Justification</label>
                        <textarea wire:model="revaluationForm.justification" rows="2" class="form-control"></textarea>
                        @error('revaluationForm.justification') <p class="ds-form-error">{{ $message }}</p> @enderror
                    </div>
                    <div class="sm:col-span-4 flex gap-2">
                        <button type="button" wire:click="saveRevaluation" class="btn btn-primary btn-sm">Soumettre</button>
                        <button type="button" wire:click="$set('revaluationForm', null)" class="btn btn-outline-secondary btn-sm">Annuler</button>
                    </div>
                </div>
            @endif
        </div>
    @endif

    {{-- ====================== FONCIER / MOE ====================== --}}
    @if ($tab === 'foncier')
        <div class="grid gap-4 lg:grid-cols-2">
            <div class="ds-card">
                <div class="ds-card-header"><h3 class="text-[13px] font-semibold">Assiette foncière</h3></div>
                <div class="ds-card-body grid gap-3 sm:grid-cols-2">
                    <div>
                        <label class="ds-form-label">Décision d'affectation</label>
                        <input wire:model="land.affectation_decision_ref" type="text" class="form-control" />
                    </div>
                    <div>
                        <label class="ds-form-label">Date de la décision</label>
                        <input wire:model="land.affectation_decision_date" type="date" class="form-control" />
                    </div>
                    <div class="sm:col-span-2 grid grid-cols-3 gap-2">
                        <div>
                            <label class="ds-form-label">Hectares</label>
                            <input wire:model="land.superficie_ha" type="number" min="0" class="form-control" />
                        </div>
                        <div>
                            <label class="ds-form-label">Ares</label>
                            <input wire:model="land.superficie_a" type="number" min="0" max="99" class="form-control" />
                        </div>
                        <div>
                            <label class="ds-form-label">Centiares</label>
                            <input wire:model="land.superficie_ca" type="number" min="0" max="99" class="form-control" />
                        </div>
                        @error('land.superficie_a') <p class="ds-form-error">{{ $message }}</p> @enderror
                        @error('land.superficie_ca') <p class="ds-form-error">{{ $message }}</p> @enderror
                    </div>
                    <div class="sm:col-span-2">
                        <label class="ds-form-label">Situation</label>
                        <input wire:model="land.location" type="text" class="form-control" />
                    </div>
                    <div class="sm:col-span-2">
                        <p class="mb-2 text-[11px] text-[var(--ds-text-faint)]">
                            Superficie calculée : {{ Money::da($budget->get('superficie_m2'), 0) }} m²
                        </p>
                        <button type="button" wire:click="saveLand" class="btn btn-primary btn-sm" @disabled(! $editable)>Enregistrer</button>
                    </div>
                </div>
            </div>

            <div class="ds-card">
                <div class="ds-card-header"><h3 class="text-[13px] font-semibold">Maîtrise d'œuvre</h3></div>
                <div class="ds-card-body grid gap-3">
                    <div>
                        <label class="ds-form-label">{{ __('ui.fiche.design_office') }}</label>
                        <select wire:model.live="mission.design_office_id" class="form-select" @disabled(! $editable)>
                            <option value="">{{ __('ui.fiche.design_office_placeholder') }}</option>
                            @foreach ($this->designOffices as $office)
                                <option value="{{ $office['id'] }}">
                                    {{ $office['name'] }}@if (! empty($office['city'])) — {{ $office['city'] }}@endif
                                </option>
                            @endforeach
                            @if ($mission['design_office_id'] && ! collect($this->designOffices)->contains('id', (int) $mission['design_office_id']) && $mission['bet_name'])
                                <option value="{{ $mission['design_office_id'] }}" selected>{{ $mission['bet_name'] }} ({{ __('ui.design_offices.inactive') }})</option>
                            @endif
                        </select>
                        @error('mission.design_office_id') <p class="ds-form-error">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="ds-form-label">Esquisse approuvée le</label>
                        <input wire:model="mission.esquisse_approved_at" type="date" class="form-control" />
                    </div>
                    <div>
                        <label class="ds-form-label">Avant-projet approuvé le</label>
                        <input wire:model="mission.avant_projet_approved_at" type="date" class="form-control" />
                    </div>
                    <div>
                        <label class="ds-form-label">Projet d'exécution approuvé le</label>
                        <input wire:model="mission.projet_execution_approved_at" type="date" class="form-control" />
                    </div>
                    <div>
                        <button type="button" wire:click="saveMission" class="btn btn-primary btn-sm" @disabled(! $editable)>Enregistrer</button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- ============================ RESEAUX ============================ --}}
    @if ($tab === 'reseaux')
        <div class="ds-card">
            <div class="ds-card-header"><h3 class="text-[13px] font-semibold">Raccordement aux différents réseaux</h3></div>
            <div class="ds-card-body space-y-3">
                @foreach ($networkLabels as $network => $label)
                    <div class="grid items-center gap-3 sm:grid-cols-[140px_120px_1fr]">
                        <span class="text-[13px] font-medium">{{ $label }}</span>
                        <label class="flex items-center gap-2 text-[12px]">
                            <input wire:model="networks.{{ $network }}.connected" type="checkbox" class="form-check-input" />
                            Raccordé
                        </label>
                        <input wire:model="networks.{{ $network }}.motif" type="text" class="form-control" placeholder="Motif si non raccordé" />
                    </div>
                @endforeach
                <button type="button" wire:click="saveNetworks" class="btn btn-primary btn-sm" @disabled(! $editable)>Enregistrer</button>
            </div>
        </div>
    @endif

    {{-- ======================== SITUATION DU PROJET ======================== --}}
    @if ($tab === 'situation')
        <div class="ds-card">
            <div class="ds-card-header">
                <h3 class="text-[13px] font-semibold">{{ __('ui.fiche.phase_title') }}</h3>
                <p class="text-[11px] text-[var(--ds-text-faint)]">{{ __('ui.fiche.phase_hint') }}</p>
            </div>
            <div class="ds-card-body">
                <div class="grid gap-3 sm:grid-cols-2">
                    <div>
                        <label class="ds-form-label">{{ __('ui.fiche.phase_label') }}</label>
                        <select wire:model="phase" class="form-select" @disabled(! $editable)>
                            <option value="">{{ __('ui.fiche.phase_placeholder') }}</option>
                            @foreach ($phaseLabels as $key => $label)
                                <option value="{{ $key }}">{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('phase') <p class="ds-form-error">{{ $message }}</p> @enderror
                    </div>
                </div>
                <div class="mt-3">
                    <button type="button" wire:click="savePhase" class="btn btn-primary btn-sm" @disabled(! $editable)>
                        {{ __('ui.fiche.save') }}
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- ======================== IDENTIFICATION ======================== --}}
    @if ($tab === 'identification')
        <div class="ds-card">
            <div class="ds-card-header">
                <h3 class="text-[13px] font-semibold">{{ __('ui.fiche.identity_title') }}</h3>
                <p class="text-[11px] text-[var(--ds-text-faint)]">{{ __('ui.fiche.identity_hint') }}</p>
            </div>
            <div class="ds-card-body space-y-4">
                <div class="grid gap-3 sm:grid-cols-2">
                    <div>
                        <label class="ds-form-label">{{ __('ui.fiche.individualisation_number') }}</label>
                        <input type="text" wire:model="individualisationNumber" class="form-control" @disabled(! $editable)>
                        @error('individualisationNumber') <p class="ds-form-error">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="ds-form-label">{{ __('ui.fiche.individualisation_date') }}</label>
                        <input type="date" wire:model="individualisationDate" class="form-control" @disabled(! $editable)>
                        @error('individualisationDate') <p class="ds-form-error">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="grid gap-3 sm:grid-cols-3">
                    <div>
                        <p class="mb-2 text-[11px] font-semibold uppercase tracking-wide text-[var(--ds-text-faint)]">{{ __('ui.fiche.works_reception') }}</p>
                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label class="ds-form-label">{{ __('ui.fiche.quarter') }}</label>
                                <select wire:model="worksReceptionQuarter" class="form-select" @disabled(! $editable)>
                                    <option value="">—</option>
                                    @foreach ([1, 2, 3, 4] as $q)
                                        <option value="{{ $q }}">{{ $q }}</option>
                                    @endforeach
                                </select>
                                @error('worksReceptionQuarter') <p class="ds-form-error">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="ds-form-label">{{ __('ui.fiche.year') }}</label>
                                <input type="number" wire:model="worksReceptionYear" class="form-control" min="1990" max="2100" placeholder="2026" @disabled(! $editable)>
                                @error('worksReceptionYear') <p class="ds-form-error">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    </div>
                    <div>
                        <p class="mb-2 text-[11px] font-semibold uppercase tracking-wide text-[var(--ds-text-faint)]">{{ __('ui.fiche.equipment_reception') }}</p>
                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label class="ds-form-label">{{ __('ui.fiche.quarter') }}</label>
                                <select wire:model="equipmentReceptionQuarter" class="form-select" @disabled(! $editable)>
                                    <option value="">—</option>
                                    @foreach ([1, 2, 3, 4] as $q)
                                        <option value="{{ $q }}">{{ $q }}</option>
                                    @endforeach
                                </select>
                                @error('equipmentReceptionQuarter') <p class="ds-form-error">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="ds-form-label">{{ __('ui.fiche.year') }}</label>
                                <input type="number" wire:model="equipmentReceptionYear" class="form-control" min="1990" max="2100" placeholder="2026" @disabled(! $editable)>
                                @error('equipmentReceptionYear') <p class="ds-form-error">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    </div>
                    <div>
                        <p class="mb-2 text-[11px] font-semibold uppercase tracking-wide text-[var(--ds-text-faint)]">{{ __('ui.fiche.inauguration') }}</p>
                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label class="ds-form-label">{{ __('ui.fiche.quarter') }}</label>
                                <select wire:model="inaugurationQuarter" class="form-select" @disabled(! $editable)>
                                    <option value="">—</option>
                                    @foreach ([1, 2, 3, 4] as $q)
                                        <option value="{{ $q }}">{{ $q }}</option>
                                    @endforeach
                                </select>
                                @error('inaugurationQuarter') <p class="ds-form-error">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="ds-form-label">{{ __('ui.fiche.year') }}</label>
                                <input type="number" wire:model="inaugurationYear" class="form-control" min="1990" max="2100" placeholder="2026" @disabled(! $editable)>
                                @error('inaugurationYear') <p class="ds-form-error">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <button type="button" wire:click="saveIdentity" class="btn btn-primary btn-sm" @disabled(! $editable)>
                    {{ __('ui.fiche.save') }}
                </button>
            </div>
        </div>
    @endif

    {{-- ======================== SITUATION PHYSIQUE ======================== --}}
    @if ($tab === 'physique')
        @php
            $situations = $project->physicalProgress->sortByDesc('created_at')->values();
            $canReport = $this->canReportSituation();
        @endphp
        <div class="ds-card mb-4">
            <div class="ds-card-header">
                <h3 class="text-[13px] font-semibold">{{ __('ui.show.situation_add_title') }}</h3>
                <p class="text-[11px] text-[var(--ds-text-faint)]">{{ __('ui.show.physical_hint') }}</p>
            </div>
            <div class="ds-card-body space-y-4">
                @if ($canReport)
                    <div class="grid gap-3 sm:grid-cols-3">
                        <div class="sm:col-span-2">
                            <label class="ds-form-label">{{ __('ui.show.situation_title') }}</label>
                            <input type="text" wire:model="situationTitle" class="form-control" placeholder="{{ __('ui.show.situation_title_ph') }}">
                            @error('situationTitle') <p class="ds-form-error">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="ds-form-label">{{ __('ui.show.situation_pct') }}</label>
                            <input type="number" wire:model="situationPct" min="0" max="100" step="0.1" class="form-control" placeholder="80">
                            @error('situationPct') <p class="ds-form-error">{{ $message }}</p> @enderror
                        </div>
                    </div>
                    <button type="button" wire:click="submitSituation" wire:loading.attr="disabled" wire:target="submitSituation" class="btn btn-primary btn-sm">
                        <span wire:loading wire:target="submitSituation" class="spinner-border spinner-border-sm" role="status"></span>
                        {{ __('ui.show.situation_submit') }}
                    </button>
                @else
                    <p class="text-[12px] text-[var(--ds-text-faint)]">{{ __('ui.fiche.situation_readonly') }}</p>
                @endif
            </div>
        </div>

        <div class="ds-card">
            <div class="ds-card-header">
                <h3 class="text-[13px] font-semibold">{{ __('ui.fiche.situation_history') }}</h3>
            </div>
            <x-dashboard.ui-table>
                <thead>
                <tr>
                    <th>{{ __('ui.show.situation_title') }}</th>
                    <th>%</th>
                    <th>{{ __('ui.show.situation_by') }}</th>
                    <th>{{ __('ui.projects.status') }}</th>
                </tr>
                </thead>
                <tbody>
                @forelse ($situations as $pp)
                    <tr>
                        <td class="font-medium">{{ $pp->title }}</td>
                        <td class="font-mono">{{ $pp->progress_pct }}%</td>
                        <td class="text-[var(--ds-text-muted)]">{{ $pp->reporter?->name ?? '—' }} · {{ $pp->created_at?->format('d/m/Y H:i') }}</td>
                        <td>
                            <span class="inline-block rounded-full px-2 py-0.5 text-[10px] font-semibold uppercase
                                {{ $pp->status === 'accepted' ? 'bg-emerald-100 text-emerald-700' : ($pp->status === 'declined' ? 'bg-red-100 text-red-600' : 'bg-amber-100 text-amber-700') }}">
                                {{ $pp->status }}
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="text-center text-[var(--ds-text-faint)]">{{ __('ui.show.situation_empty') }}</td>
                    </tr>
                @endforelse
                </tbody>
            </x-dashboard.ui-table>
        </div>
    @endif

</div>
