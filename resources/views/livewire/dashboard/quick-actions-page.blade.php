<div class="ds-page-enter-stagger max-w-6xl">
    <x-dashboard.page-header
        :title="__('ui.actions.title')"
        :subtitle="__('ui.actions.subtitle')"
    />

    @if (session('actions-saved'))
        <div class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-[13px] text-emerald-800">{{ session('actions-saved') }}</div>
    @endif
    @if (session('actions-error'))
        <div class="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-[13px] text-red-800">{{ session('actions-error') }}</div>
    @endif

    <div class="mb-6 grid gap-4 md:grid-cols-3">
        @can('create', App\Models\Project::class)
            <div class="ds-card group">
                <div class="ds-ribbon bg-gradient-to-r from-emerald-500 to-teal-600 -mx-px -mt-px rounded-t-[var(--ds-radius)]"></div>
                <div class="p-6">
                    <h3 class="text-base font-bold text-[var(--ds-text)]">{{ __('ui.actions.new_fip') }}</h3>
                    <p class="mt-1.5 text-[12px] leading-relaxed text-[var(--ds-text-muted)]">{{ __('ui.actions.new_fip_desc') }}</p>
                    <a href="{{ route('dashbored.projects.create') }}" class="btn btn-primary btn-sm mt-5 w-100">{{ __('ui.actions.create') }}</a>
                </div>
            </div>
        @endcan

        @if ($this->canRequestRevaluation() || $this->canApproveRevaluation())
            <div class="ds-card group">
                <div class="ds-ribbon bg-gradient-to-r from-amber-500 to-orange-600 -mx-px -mt-px rounded-t-[var(--ds-radius)]"></div>
                <div class="p-6">
                    <h3 class="text-base font-bold text-[var(--ds-text)]">{{ __('ui.actions.reeval_ae') }}</h3>
                    <p class="mt-1.5 text-[12px] leading-relaxed text-[var(--ds-text-muted)]">{{ __('ui.actions.reeval_desc') }}</p>
                    <button type="button" wire:click="open('revaluation')" class="btn btn-primary btn-sm mt-5 w-100">{{ __('ui.actions.open') }}</button>
                </div>
            </div>
        @endif

        @if ($this->canRequestMovement() || $this->canApproveMovement())
            <div class="ds-card group">
                <div class="ds-ribbon bg-gradient-to-r from-indigo-500 to-violet-600 -mx-px -mt-px rounded-t-[var(--ds-radius)]"></div>
                <div class="p-6">
                    <h3 class="text-base font-bold text-[var(--ds-text)]">{{ __('ui.actions.movement_credits') }}</h3>
                    <p class="mt-1.5 text-[12px] leading-relaxed text-[var(--ds-text-muted)]">{{ __('ui.actions.movement_desc') }}</p>
                    <button type="button" wire:click="open('movement')" class="btn btn-primary btn-sm mt-5 w-100">{{ __('ui.actions.open') }}</button>
                </div>
            </div>
        @endif

        @if ($this->canClose())
            <div class="ds-card group">
                <div class="ds-ribbon bg-gradient-to-r from-slate-500 to-slate-700 -mx-px -mt-px rounded-t-[var(--ds-radius)]"></div>
                <div class="p-6">
                    <h3 class="text-base font-bold text-[var(--ds-text)]">{{ __('ui.actions.close') }}</h3>
                    <p class="mt-1.5 text-[12px] leading-relaxed text-[var(--ds-text-muted)]">{{ __('ui.actions.close_desc') }}</p>
                    <button type="button" wire:click="open('closure')" class="btn btn-primary btn-sm mt-5 w-100">{{ __('ui.actions.open') }}</button>
                </div>
            </div>
        @endif
    </div>

    @if ($panel === 'revaluation')
        <div class="ds-card mb-6">
            <div class="ds-card-header">
                <h3 class="text-[13px] font-semibold">{{ __('ui.actions.reeval_ae') }}</h3>
            </div>
            <div class="ds-card-body space-y-4">
                @if ($this->canRequestRevaluation())
                    <form wire:submit="submitRevaluation" class="grid gap-3 md:grid-cols-2">
                        <label class="text-[12px] font-medium">{{ __('ui.actions.operation') }}
                            <select wire:model="revaluationProjectId" class="form-select mt-1 text-[13px]">
                                <option value="">—</option>
                                @foreach ($projects as $p)
                                    <option value="{{ $p['id'] }}">{{ $p['label'] }}</option>
                                @endforeach
                            </select>
                        </label>
                        <label class="text-[12px] font-medium">{{ __('ui.actions.amount_da') }}
                            <input type="number" step="0.01" min="0" wire:model="revaluationAmount" class="form-control mt-1 text-[13px]">
                        </label>
                        <label class="text-[12px] font-medium">{{ __('ui.actions.year') }}
                            <input type="number" wire:model="revaluationYear" class="form-control mt-1 text-[13px]">
                        </label>
                        <label class="text-[12px] font-medium md:col-span-2">{{ __('ui.actions.justification') }}
                            <textarea wire:model="revaluationJustification" rows="3" class="form-control mt-1 text-[13px]"></textarea>
                        </label>
                        @error('revaluationJustification') <p class="text-[12px] text-red-600 md:col-span-2">{{ $message }}</p> @enderror
                        <div class="md:col-span-2">
                            <button type="submit" class="btn btn-primary btn-sm">{{ __('ui.actions.submit') }}</button>
                        </div>
                    </form>
                @endif

                @if ($pendingRevaluations->isNotEmpty())
                    <table class="w-full text-[12px]">
                        <thead><tr class="text-left text-[var(--ds-text-faint)]"><th class="py-2">Opération</th><th>Demandeur</th><th>Montant</th><th>Statut</th><th></th></tr></thead>
                        <tbody>
                            @foreach ($pendingRevaluations as $row)
                                <tr class="border-t border-[var(--ds-border-subtle)]">
                                    <td class="py-2">{{ $row->project?->fip_id }} — {{ $row->project?->title_fr }}</td>
                                    <td>{{ \App\Models\User::requesterLabelFor($row->submitter, $row->project?->wilaya?->name_fr) }}</td>
                                    <td>{{ number_format((float) $row->requested_amount_da, 2, ',', ' ') }} DA</td>
                                    <td>{{ $row->status }}</td>
                                    <td>
                                        @if ($this->canApproveRevaluation())
                                            <button type="button" wire:click="approveRevaluation({{ $row->id }})" class="btn btn-sm btn-outline-success">{{ __('ui.actions.approve') }}</button>
                                            <button type="button" wire:click="rejectRevaluation({{ $row->id }})" class="btn btn-sm btn-outline-danger">{{ __('ui.actions.reject') }}</button>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>
        </div>
    @endif

    @if ($panel === 'movement')
        <div class="ds-card mb-6">
            <div class="ds-card-header">
                <h3 class="text-[13px] font-semibold">{{ __('ui.actions.movement_cancel_transfer') }}</h3>
            </div>
            <div class="ds-card-body space-y-4">
                @if ($this->canRequestMovement())
                    <form wire:submit="submitMovement" class="grid gap-3 md:grid-cols-2">
                        <label class="text-[12px] font-medium">{{ __('ui.actions.type') }}
                            <select wire:model.live="movementType" class="form-select mt-1 text-[13px]">
                                <option value="transfer">{{ __('ui.actions.transfer') }}</option>
                                <option value="cancellation">{{ __('ui.actions.cancellation') }}</option>
                            </select>
                        </label>
                        <label class="text-[12px] font-medium">{{ __('ui.actions.source') }}
                            <select wire:model="movementSourceId" class="form-select mt-1 text-[13px]">
                                <option value="">—</option>
                                @foreach ($projects as $p)
                                    <option value="{{ $p['id'] }}">{{ $p['label'] }}</option>
                                @endforeach
                            </select>
                            @error('movementSourceId') <span class="text-red-600">{{ $message }}</span> @enderror
                        </label>
                        @if ($movementType === 'transfer')
                            <label class="text-[12px] font-medium">{{ __('ui.actions.target') }}
                                <select wire:model="movementTargetId" class="form-select mt-1 text-[13px]">
                                    <option value="">—</option>
                                    @foreach ($projects as $p)
                                        <option value="{{ $p['id'] }}">{{ $p['label'] }}</option>
                                    @endforeach
                                </select>
                            </label>
                        @endif
                        <label class="text-[12px] font-medium">{{ __('ui.actions.amount_da') }}
                            <input type="number" step="0.01" min="0" wire:model="movementAmount" class="form-control mt-1 text-[13px]">
                        </label>
                        <label class="text-[12px] font-medium md:col-span-2">{{ __('ui.actions.motif') }}
                            <textarea wire:model="movementJustification" rows="3" class="form-control mt-1 text-[13px]"></textarea>
                        </label>
                        <div class="md:col-span-2">
                            <button type="submit" class="btn btn-primary btn-sm">{{ __('ui.actions.submit') }}</button>
                        </div>
                    </form>
                @endif

                @if ($pendingMovements->isNotEmpty())
                    <table class="w-full text-[12px]">
                        <thead><tr class="text-left text-[var(--ds-text-faint)]"><th class="py-2">Source</th><th>Demandeur</th><th>Cible</th><th>Montant</th><th></th></tr></thead>
                        <tbody>
                            @foreach ($pendingMovements as $row)
                                <tr class="border-t border-[var(--ds-border-subtle)]">
                                    <td class="py-2">{{ $row->sourceProject?->fip_id }}</td>
                                    <td>{{ \App\Models\User::requesterLabelFor($row->submitter, $row->sourceProject?->wilaya?->name_fr) }}</td>
                                    <td>{{ $row->targetProject?->fip_id ?? '—' }}</td>
                                    <td>{{ number_format((float) $row->amount_da, 2, ',', ' ') }} DA</td>
                                    <td>
                                        @if ($this->canApproveMovement())
                                            <button type="button" wire:click="approveMovement({{ $row->id }})" class="btn btn-sm btn-outline-success">{{ __('ui.actions.approve') }}</button>
                                            <button type="button" wire:click="rejectMovement({{ $row->id }})" class="btn btn-sm btn-outline-danger">{{ __('ui.actions.reject') }}</button>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>
        </div>
    @endif

    @if ($panel === 'closure' && $this->canClose())
        <div class="ds-card mb-6">
            <div class="ds-card-header">
                <h3 class="text-[13px] font-semibold">{{ __('ui.actions.close') }}</h3>
            </div>
            <div class="ds-card-body">
                <form wire:submit="submitClosure" class="grid gap-3 md:grid-cols-2">
                    <label class="text-[12px] font-medium md:col-span-2">{{ __('ui.actions.operation') }}
                        <select wire:model="closureProjectId" class="form-select mt-1 text-[13px]">
                            <option value="">—</option>
                            @foreach ($projects as $p)
                                @if ($p['status'] !== 'closed')
                                    <option value="{{ $p['id'] }}">{{ $p['label'] }}</option>
                                @endif
                            @endforeach
                        </select>
                    </label>
                    <label class="text-[12px] font-medium">{{ __('ui.actions.decision_ref') }}
                        <input type="text" wire:model="closureDecisionRef" class="form-control mt-1 text-[13px]">
                    </label>
                    <label class="text-[12px] font-medium md:col-span-2">{{ __('ui.actions.notes') }}
                        <textarea wire:model="closureNotes" rows="2" class="form-control mt-1 text-[13px]"></textarea>
                    </label>
                    <div class="md:col-span-2">
                        <button type="submit" class="btn btn-primary btn-sm">{{ __('ui.actions.close') }}</button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <div class="ds-card">
        <div class="ds-card-header">
            <h3 class="text-[13px] font-semibold">{{ __('ui.actions.journal') }}</h3>
            <p class="text-[11px] text-[var(--ds-text-faint)]">{{ __('ui.actions.journal_hint') }}</p>
        </div>
        <div class="divide-y divide-[var(--ds-border-subtle)]">
            @forelse ($recentActivity as $row)
                <div class="flex items-center gap-3 px-5 py-3">
                    <span class="h-2 w-2 shrink-0 rounded-full bg-emerald-500"></span>
                    <div class="min-w-0 flex-1">
                        <p class="text-[13px] font-medium text-[var(--ds-text)]">{{ $row['action'] }}</p>
                        <p class="text-[11px] text-[var(--ds-text-faint)]">{{ $row['project'] }} · {{ $row['user'] }}</p>
                    </div>
                    <span class="shrink-0 text-[10px] font-medium text-[var(--ds-text-faint)]">{{ $row['at'] }}</span>
                </div>
            @empty
                <p class="px-5 py-6 text-[12px] text-[var(--ds-text-faint)]">{{ __('ui.actions.journal_empty') }}</p>
            @endforelse
        </div>
    </div>
</div>
