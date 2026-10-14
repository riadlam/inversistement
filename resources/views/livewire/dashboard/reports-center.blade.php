<div class="ds-page-enter-stagger max-w-6xl">
    <x-dashboard.page-header
        :title="__('ui.reports.title')"
        :subtitle="__('ui.reports.subtitle')"
    />

    @unless ($this->canExport())
        <div class="mb-6 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-[13px] text-amber-900">
            {{ __('ui.reports.no_export') }}
        </div>
    @endunless

    <div class="mb-6 grid gap-4 lg:grid-cols-2">
        {{-- ETAT PHYSIQUE ET FINANCIER GLOBAL (canevas DGB) --}}
        <div class="ds-card group">
            <div class="ds-ribbon bg-gradient-to-r from-emerald-500 via-teal-500 to-cyan-500 -mx-px -mt-px rounded-t-[var(--ds-radius)]"></div>
            <div class="p-6">
                <div class="mb-4 flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 3v11.25A2.25 2.25 0 006 16.5h2.25M3.75 3h-1.5m1.5 0h16.5m0 0h1.5m-1.5 0v11.25A2.25 2.25 0 0118 16.5h-2.25m-7.5 0h7.5" /></svg>
                </div>
                <h3 class="text-lg font-bold text-[var(--ds-text)]">{{ __('ui.reports.etat_global_full') }}</h3>
                <p class="mt-1.5 text-[13px] text-[var(--ds-text-muted)]">
                    {{ __('ui.reports.etat_global_desc') }}
                </p>

                <div class="mt-4 grid gap-3 sm:grid-cols-2">
                    <div>
                        <label for="etat-wilaya" class="ds-form-label">{{ __('ui.projects.wilaya') }}</label>
                        <select wire:model="wilayaId" id="etat-wilaya" class="form-select" @disabled(! auth()->user()->isNational())>
                            <option value="">{{ __('ui.reports.all_wilayas') }}</option>
                            @foreach ($wilayas as $wilaya)
                                <option value="{{ $wilaya->id }}">{{ $wilaya->code }} — {{ $wilaya->name_fr }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="etat-titre" class="ds-form-label">{{ __('ui.reports.titre') }}</label>
                        <select wire:model="titre" id="etat-titre" class="form-select">
                            <option value="">{{ __('ui.reports.titres_3_4') }}</option>
                            <option value="T3">{{ __('ui.reports.titre_3') }}</option>
                            <option value="T4">{{ __('ui.reports.titre_4') }}</option>
                        </select>
                    </div>
                    <div>
                        <label for="etat-year" class="ds-form-label">{{ __('ui.reports.notif_year') }}</label>
                        <select wire:model="year" id="etat-year" class="form-select">
                            <option value="">{{ __('ui.reports.all_years') }}</option>
                            @foreach ($years as $y)
                                <option value="{{ $y }}">{{ $y }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="etat-arretee" class="ds-form-label">{{ __('ui.reports.arretee') }}</label>
                        <input wire:model="arreteeAu" id="etat-arretee" type="date" class="form-control" />
                    </div>
                </div>

                <div class="mt-5 flex flex-wrap gap-2">
                    <button type="button" wire:click="downloadEtatGlobal" wire:loading.attr="disabled"
                            wire:target="downloadEtatGlobal" class="btn btn-primary btn-sm"
                            @disabled(! $this->canExport())>
                        <span wire:loading wire:target="downloadEtatGlobal" class="spinner-border spinner-border-sm me-1" role="status"></span>
                        {{ __('ui.reports.export_xlsx') }}
                    </button>
                </div>
                @error('wilayaId') <p class="ds-form-error">{{ $message }}</p> @enderror
                @error('year') <p class="ds-form-error">{{ $message }}</p> @enderror
            </div>
        </div>

        {{-- FICHE DE SUIVI DU PROJET --}}
        <div class="ds-card group">
            <div class="ds-ribbon bg-gradient-to-r from-indigo-500 via-violet-500 to-purple-500 -mx-px -mt-px rounded-t-[var(--ds-radius)]"></div>
            <div class="p-6">
                <div class="mb-4 flex h-10 w-10 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" /></svg>
                </div>
                <h3 class="text-lg font-bold text-[var(--ds-text)]">{{ __('ui.reports.fiche_title') }}</h3>
                <p class="mt-1.5 text-[13px] text-[var(--ds-text-muted)]">
                    {{ __('ui.reports.fiche_desc') }}
                </p>

                <div class="mt-4">
                    <label for="fiche-project" class="ds-form-label">{{ __('ui.reports.operation') }}</label>
                    <select wire:model="ficheProjectId" id="fiche-project" class="form-select">
                        @foreach ($projects as $project)
                            <option value="{{ $project->fip_id }}">
                                {{ $project->operation_number ?: $project->fip_id }} — {{ Str::limit($project->title_fr, 60) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="mt-5 flex flex-wrap gap-2">
                    @if ($ficheProjectId && $this->canExport())
                        <a href="{{ route('dashbored.projects.fiche-suivi', $ficheProjectId) }}" class="btn btn-primary btn-sm">
                            {{ __('ui.reports.download_pdf') }}
                        </a>
                        <a href="{{ route('dashbored.projects.show', $ficheProjectId) }}" class="btn btn-outline-secondary btn-sm" wire:navigate>
                            {{ __('ui.reports.open_project') }}
                        </a>
                    @else
                        <button type="button" class="btn btn-primary btn-sm" disabled>{{ __('ui.reports.download_pdf') }}</button>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Deferred: NC13 and RPT reuse the same metrics once their templates are confirmed. --}}
    <div class="ds-card">
        <div class="ds-card-header">
            <h3 class="text-[13px] font-semibold">{{ __('ui.reports.nc13_notes') }}</h3>
            <p class="text-[11px] text-[var(--ds-text-faint)]">{{ __('ui.reports.nc13_pending') }}</p>
        </div>
        <div class="ds-card-body text-[13px] text-[var(--ds-text-muted)]">
            {{ __('ui.reports.nc13_body') }}
        </div>
    </div>
</div>
