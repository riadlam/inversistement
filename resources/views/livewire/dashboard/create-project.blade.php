@php
    $y = $this->planningYearN();
    $showOwnerForm = $errors->hasAny(['new_owner_email', 'new_owner_name', 'new_owner_company_name', 'new_owner_nif', 'new_owner_business_register']);
@endphp

<div class="ds-page-enter max-w-4xl">
    <x-dashboard.page-header :title="$this->isEditing ? __('ui.create.edit_title') : __('ui.create.title')" :subtitle="__('ui.create.subtitle')">
        <x-slot:actions>
            <a href="{{ route('dashbored.projects') }}" class="btn btn-outline-secondary btn-sm">{{ __('ui.create.back') }}</a>
        </x-slot:actions>
    </x-dashboard.page-header>

    @if ($successAction === 'draft')
        <div class="mb-6 flex items-center gap-3 rounded-xl border border-slate-200 bg-slate-50 px-5 py-4" style="animation: ds-fade-up 0.35s var(--ds-ease) both">
            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-slate-200 text-slate-700">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" /></svg>
            </span>
            <div>
                <p class="text-[13px] font-semibold text-slate-800">{{ __('ui.create.draft_saved') }}</p>
                <p class="text-[12px] text-slate-600">{{ __('ui.create.draft_hint') }}</p>
            </div>
        </div>
    @elseif ($successAction === 'updated')
        <div class="mb-6 flex items-center gap-3 rounded-xl border border-emerald-200 bg-emerald-50 px-5 py-4" style="animation: ds-fade-up 0.35s var(--ds-ease) both">
            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-emerald-100 text-emerald-600">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
            </span>
            <div>
                <p class="text-[13px] font-semibold text-emerald-800">{{ __('ui.create.updates_saved') }}</p>
                <p class="text-[12px] text-emerald-700">{{ __('ui.create.updates_hint') }}</p>
            </div>
        </div>
    @elseif ($successAction === 'arbitration')
        <div class="mb-6 flex items-center gap-3 rounded-xl border border-emerald-200 bg-emerald-50 px-5 py-4" style="animation: ds-fade-up 0.35s var(--ds-ease) both">
            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-emerald-100 text-emerald-600">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" /></svg>
            </span>
            <div>
                <p class="text-[13px] font-semibold text-emerald-800">{{ __('ui.create.submitted') }}</p>
                <p class="text-[12px] text-emerald-600">{{ __('ui.create.submitted_hint', ['id' => $createdFipId]) }}</p>
            </div>
        </div>
    @endif

    @if ($rejection_reason !== '' && $this->canSubmitWorkflow)
        <div class="mb-6 rounded-xl border border-amber-200 bg-amber-50 px-5 py-4" style="animation: ds-fade-up 0.35s var(--ds-ease) both">
            <div class="flex items-start gap-3">
                <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-amber-100 text-amber-700">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" /></svg>
                </span>
                <div class="min-w-0">
                    <p class="text-[13px] font-semibold text-amber-900">
                        {{ $returned_from === 'visa' ? __('ui.create.returned_from_visa_title') : __('ui.create.returned_title') }}
                    </p>
                    <p class="mt-0.5 text-[12px] text-amber-800/80">
                        {{ $returned_from === 'visa' ? __('ui.create.returned_from_visa_hint') : __('ui.create.returned_hint') }}
                    </p>
                    <p class="mt-2 rounded-lg border border-amber-200/80 bg-white/70 px-3 py-2 text-[12px] text-amber-950">{{ $rejection_reason }}</p>
                </div>
            </div>
        </div>
    @endif

    <div class="space-y-5">

        {{-- 1 — Identifiers (execution sheet) --}}
        <div class="ds-card">
            <div class="ds-card-header flex items-center gap-2">
                <span class="flex h-6 w-6 items-center justify-center rounded-lg bg-emerald-50 text-emerald-600">
                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6z" /></svg>
                </span>
                <div>
                    <h2 class="text-[13px] font-semibold text-[var(--ds-text)]">{{ __('ui.create.identifiers') }}</h2>
                    <p class="text-[11px] text-[var(--ds-text-faint)]">{{ __('ui.create.identifiers_hint') }}</p>
                </div>
            </div>
            <div class="ds-card-body space-y-4">
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label for="title_fr" class="ds-form-label">Project title (French) <span class="text-red-500">*</span> arbitration</label>
                        <input wire:model="title_fr" id="title_fr" type="text" class="form-control" placeholder="Centre régional social — Batna" lang="fr" />
                        @error('title_fr') <p class="ds-form-error">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="title_ar" class="ds-form-label">Project title (Arabic) <span class="text-red-500">*</span> arbitration</label>
                        <input wire:model="title_ar" id="title_ar" type="text" class="form-control text-right" placeholder="عنوان المشروع" dir="rtl" lang="ar" />
                        @error('title_ar') <p class="ds-form-error">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label for="programme" class="ds-form-label">Programme <span class="text-red-500">*</span></label>
                        <div wire:ignore x-data="{
                            ts: null,
                            init() {
                                this.ts = new TomSelect(this.$refs.select, {
                                    dropdownParent: 'body',
                                    maxOptions: null,
                                    placeholder: 'Search programme…',
                                    sortField: { field: 'text', direction: 'asc' },
                                    onChange: (value) => {
                                        this.$wire.set('programme', value || '');
                                        setTimeout(() => { if (this.ts) this.ts.blur(); }, 50);
                                    },
                                });
                                const val = this.$wire.programme;
                                if (val) this.ts.setValue(String(val));
                            },
                        }">
                            <select x-ref="select" id="programme" class="form-select" dir="auto">
                                <option value="">Select programme...</option>
                                @foreach ($this->programmes as $p)
                                    <option value="{{ $p->code }}">{{ $p->label_fr }}@if($p->label_ar) — {{ $p->label_ar }}@endif</option>
                                @endforeach
                            </select>
                        </div>
                        @error('programme') <p class="ds-form-error">{{ $message }}</p> @enderror
                    </div>
                </div>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label for="wilaya" class="ds-form-label">Wilaya <span class="text-red-500">*</span></label>
                        <select wire:model.live="wilaya" id="wilaya" class="form-select">
                            <option value="">Select wilaya...</option>
                            @foreach ($this->wilayas() as $w)
                                <option value="{{ $w }}">{{ $w }}</option>
                            @endforeach
                        </select>
                        @error('wilaya') <p class="ds-form-error">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="commune" class="ds-form-label">Commune <span class="text-red-500">*</span></label>
                        {{-- Outer wire:key remounts; inner wire:ignore keeps Tom Select stable; options from commune table --}}
                        <div wire:key="commune-ts-{{ $wilaya !== '' ? $wilaya : 'none' }}">
                            <div wire:ignore x-data="{
                                ts: null,
                                init() {
                                    const hasWilaya = {{ $wilaya !== '' ? 'true' : 'false' }};
                                    this.ts = new TomSelect(this.$refs.select, {
                                        dropdownParent: 'body',
                                        maxOptions: null,
                                        placeholder: hasWilaya ? 'Search commune…' : 'Select a wilaya first…',
                                        sortField: { field: 'text', direction: 'asc' },
                                        onChange: (value) => {
                                            this.$wire.set('commune', value || '');
                                            setTimeout(() => { if (this.ts) this.ts.blur(); }, 50);
                                        },
                                    });
                                    if (! hasWilaya) {
                                        this.ts.disable();
                                    } else {
                                        const val = this.$wire.commune;
                                        if (val) this.ts.setValue(String(val));
                                    }
                                },
                                destroy() {
                                    if (this.ts) {
                                        this.ts.destroy();
                                        this.ts = null;
                                    }
                                },
                            }">
                                <select x-ref="select" id="commune" class="form-select" dir="auto">
                                    <option value="">{{ $wilaya !== '' ? 'Select commune...' : 'Select a wilaya first…' }}</option>
                                    @foreach ($this->communes as $c)
                                        <option value="{{ $c->lib_comm_fr }}">{{ $c->lib_comm_fr }}@if($c->lib_comm_ar) — {{ $c->lib_comm_ar }}@endif</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        @error('commune') <p class="ds-form-error">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="establishment_id" class="ds-form-label">Establishment <span class="text-red-500">*</span></label>
                        <div wire:key="est-ts-{{ $wilaya !== '' ? $wilaya : 'none' }}">
                            <div wire:ignore x-data="{
                                ts: null,
                                init() {
                                    const hasWilaya = {{ $wilaya !== '' ? 'true' : 'false' }};
                                    this.ts = new TomSelect(this.$refs.select, {
                                        dropdownParent: 'body',
                                        maxOptions: null,
                                        placeholder: hasWilaya ? 'Search establishment…' : 'Select a wilaya first…',
                                        sortField: { field: 'text', direction: 'asc' },
                                        onChange: (value) => {
                                            this.$wire.set('establishment_id', value ? parseInt(value) : null);
                                            setTimeout(() => { if (this.ts) this.ts.blur(); }, 50);
                                        },
                                    });
                                    if (! hasWilaya) {
                                        this.ts.disable();
                                    } else {
                                        const val = this.$wire.establishment_id;
                                        if (val) this.ts.setValue(String(val));
                                    }
                                },
                                destroy() {
                                    if (this.ts) {
                                        this.ts.destroy();
                                        this.ts = null;
                                    }
                                },
                            }">
                                <select x-ref="select" id="establishment_id" class="form-select">
                                    <option value="">{{ $wilaya !== '' ? 'Select establishment...' : 'Select a wilaya first…' }}</option>
                                    @foreach ($this->etablissements as $e)
                                        <option value="{{ $e->id }}">{{ $e->libelle }}@if($e->{'Code établissement'}) ({{ $e->{'Code établissement'} }})@endif</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        @error('establishment_id') <p class="ds-form-error">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="deadline" class="ds-form-label">Planned deadline / end date <span class="text-red-500">*</span></label>
                        <input wire:model="deadline" id="deadline" type="date" class="form-control" />
                        @error('deadline') <p class="ds-form-error">{{ $message }}</p> @enderror
                    </div>
                    <div class="sm:col-span-2">
                        <label for="description" class="ds-form-label">Description / objectives</label>
                        <textarea wire:model="description" id="description" rows="3" class="form-control" placeholder="Context, programme alignment, main deliverables…"></textarea>
                        @error('description') <p class="ds-form-error">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>
        </div>

        {{-- 2 — Maturation checklist (blocking for arbitration) --}}
        <div class="ds-card border-emerald-200/80 shadow-[0_0_0_1px_rgba(16,185,129,0.12)]">
            <div class="ds-card-header flex flex-wrap items-center gap-2">
                <span class="flex h-6 w-6 items-center justify-center rounded-lg bg-amber-50 text-amber-700">
                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                </span>
                <div class="flex-1">
                    <h2 class="text-[13px] font-semibold text-[var(--ds-text)]">{{ __('ui.create.checklist') }}</h2>
                    <p class="text-[11px] text-[var(--ds-text-faint)]">{{ __('ui.create.checklist_hint') }}</p>
                </div>
            </div>
            <div class="ds-card-body space-y-3">
                @error('maturation_checklist')
                    <div class="rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-[12px] font-medium text-red-800">{{ $message }}</div>
                @enderror
                @foreach ($this->maturationChecklistItems as $item)
                    <label class="flex cursor-pointer items-start gap-3 rounded-lg border border-[var(--ds-border-subtle)] bg-[var(--ds-surface-sunken)] px-4 py-3 transition hover:border-[var(--ds-primary)]/30">
                        <input wire:model.live="maturation.{{ $item->key }}" type="checkbox" class="form-check-input mt-1" />
                        <span class="text-[13px] font-medium text-[var(--ds-text-secondary)]">{{ $item->label }}</span>
                    </label>
                @endforeach
            </div>
        </div>

        {{-- 3 — Budget Classification --}}
        <div class="ds-card">
            <div class="ds-card-header flex items-center gap-2">
                <span class="flex h-6 w-6 items-center justify-center rounded-lg bg-amber-50 text-amber-600">
                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75" /></svg>
                </span>
                <h2 class="text-[13px] font-semibold text-[var(--ds-text)]">{{ __('ui.create.budget_class') }}</h2>
            </div>
            <div class="ds-card-body space-y-4">
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div>
                        <label class="mb-1 block text-[12px] font-medium text-[var(--ds-text-secondary)]">Project Category <span class="text-red-500">*</span></label>
                        <select wire:model.live="project_category" class="form-select w-full" x-on:change="$wire.set('nc13_code_id', null)">
                            <option value="">Select category</option>
                            @foreach ($this->projectCategories as $cat)
                                <option value="{{ $cat }}">{{ $cat }}</option>
                            @endforeach
                        </select>
                        @error('project_category') <div class="mt-1 text-[11px] text-red-600">{{ $message }}</div> @enderror
                    </div>
                    <div>
                        <label class="mb-1 block text-[12px] font-medium text-[var(--ds-text-secondary)]">NC13 Code <span class="text-red-500">*</span></label>
                        <select wire:model.live="nc13_code_id" class="form-select w-full">
                            <option value="">Select NC13 code</option>
                            @if ($this->project_category !== '')
                                @foreach ($this->nc13Options[$this->project_category] ?? [] as $nc)
                                    <option value="{{ $nc['value'] }}">{{ $nc['label'] }}</option>
                                @endforeach
                            @endif
                        </select>
                        @error('nc13_code_id') <div class="mt-1 text-[11px] text-red-600">{{ $message }}</div> @enderror
                    </div>
                    <div>
                        <label class="mb-1 block text-[12px] font-medium text-[var(--ds-text-secondary)]">Class (auto)</label>
                        <div class="flex h-[38px] items-center gap-2 rounded-lg border border-[var(--ds-border-subtle)] bg-[var(--ds-surface-sunken)] px-3 text-[13px] text-[var(--ds-text-secondary)]">
                            @if ($nc13 = $this->selectedNc13Code)
                                <span>{{ $nc13->class_label }}</span>
                                <svg class="h-4 w-4 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                            @else
                                <span class="text-[var(--ds-text-faint)]">—</span>
                            @endif
                        </div>
                    </div>
                    <div>
                        <label class="mb-1 block text-[12px] font-medium text-[var(--ds-text-secondary)]">Budget Type <span class="text-red-500">*</span></label>
                        <div class="flex gap-4 pt-1">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="radio" wire:model="budget_type" value="T3" class="form-check-input" />
                                <span class="text-[13px] text-[var(--ds-text-secondary)]">T3 — Investment</span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="radio" wire:model="budget_type" value="T4" class="form-check-input" />
                                <span class="text-[13px] text-[var(--ds-text-secondary)]">T4 — Transfer/Subsidy</span>
                            </label>
                        </div>
                        @error('budget_type') <div class="mt-1 text-[11px] text-red-600">{{ $message }}</div> @enderror
                    </div>
                </div>
            </div>
        </div>

        {{-- 4 — Financial: AE / CP, RAR, 3-year CP --}}
        <div class="ds-card">
            <div class="ds-card-header flex items-center gap-2">
                <span class="flex h-6 w-6 items-center justify-center rounded-lg bg-amber-50 text-amber-600">
                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75" /></svg>
                </span>
                <h2 class="text-[13px] font-semibold text-[var(--ds-text)]">{{ __('ui.create.envelope') }}</h2>
            </div>
            <div class="ds-card-body space-y-4">
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label for="ae" class="ds-form-label">AP / AE — autorisation de programme (DA) <span class="text-red-500">*</span></label>
                        <input wire:model.live="ae" id="ae" type="number" step="0.01" min="0" class="form-control" />
                        @error('ae') <p class="ds-form-error">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="cp" class="ds-form-label">CP — crédits de paiement notifiés (DA) <span class="text-red-500">*</span></label>
                        <input wire:model.live="cp" id="cp" type="number" step="0.01" min="0" class="form-control" />
                        @error('cp') <p class="ds-form-error">{{ $message }}</p> @enderror
                    </div>
                </div>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="rounded-xl border border-[var(--ds-border)] bg-gradient-to-br from-emerald-50/50 to-white p-4">
                        <p class="ds-form-label mb-1">RAR — reste à réaliser (auto)</p>
                        <p class="text-2xl font-extrabold tabular-nums text-[var(--ds-primary)]">
                            @if ($this->rarPreview() !== null)
                                {{ \App\Support\Money::da($this->rarPreview(), 0) }} <span class="text-sm font-semibold text-[var(--ds-text-muted)]">DA</span>
                            @else
                                <span class="text-[var(--ds-text-faint)]">— Saisir l'AP et les CP —</span>
                            @endif
                        </p>
                        <p class="mt-1 text-[11px] text-[var(--ds-text-faint)]">Calculé par la règle « rar » du moteur de calcul, modifiable par l'administrateur.</p>
                    </div>
                    <div class="rounded-xl border border-[var(--ds-border-subtle)] bg-[var(--ds-surface-sunken)] px-4 py-3 text-[11px] text-[var(--ds-text-muted)]">
                        <strong>Sheet 01</strong> — project the CP cash profile over the PLF window. Years shown use the current calendar year as <strong>N</strong>.
                    </div>
                </div>
                <p class="text-[12px] font-semibold text-[var(--ds-text)]">Échéancier CP (DA)</p>
                <div class="space-y-2">
                    @foreach ($cpProjections as $i => $proj)
                        <div wire:key="cp-proj-{{ $i }}" class="flex items-center gap-3">
                            <span class="w-20 text-[13px] font-medium text-[var(--ds-text-secondary)] shrink-0">
                                N{{ $proj['year_offset'] > 0 ? '+' . $proj['year_offset'] : '' }}
                                ({{ $y + $proj['year_offset'] }})
                            </span>
                            <input wire:model="cpProjections.{{ $i }}.amount" type="number" step="0.1" min="0" class="form-control flex-1" placeholder="0.0" />
                            @if (count($cpProjections) > 1)
                                <button type="button" wire:click="removeCpProjection({{ $i }})" class="rounded-md p-1 text-[var(--ds-text-faint)] hover:bg-red-50 hover:text-red-600" title="Remove">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                                </button>
                            @endif
                        </div>
                    @endforeach
                    <button type="button" wire:click="addCpProjection" class="btn btn-outline-secondary btn-sm">+ Add year</button>
                    @error('cpProjections.*.amount') <p class="ds-form-error">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>

        {{-- 5 — Physical Progress --}}
        @if ($this->canShowPhysicalProgress)
        <div class="ds-card">
            <div class="ds-card-header flex flex-wrap items-center gap-2">
                <span class="flex h-6 w-6 items-center justify-center rounded-lg bg-emerald-50 text-emerald-600">
                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 3v11.25A2.25 2.25 0 006 16.5h2.25M3.75 3h-1.5m1.5 0h16.5m0 0h1.5m-1.5 0v11.25A2.25 2.25 0 0118 16.5h-2.25m-7.5 0h7.5m-7.5 0l-1.5-1.5" /></svg>
                </span>
                <div class="flex-1">
                    <h2 class="text-[13px] font-semibold text-[var(--ds-text)]">{{ __('ui.physical.title') }}</h2>
                    <p class="text-[11px] text-[var(--ds-text-faint)]">{{ __('ui.create.progress_hint') }}</p>
                </div>
            </div>
            <div class="ds-card-body space-y-3">
                @foreach ($this->physicalProgress as $i => $pp)
                    <div class="flex flex-wrap items-start gap-3 rounded-lg border border-[var(--ds-border-subtle)] bg-[var(--ds-surface-sunken)] p-4" wire:key="pp-{{ $i }}">
                        @can('situations.create')
                            <div class="w-full mb-1">
                                <span class="inline-block rounded-full px-2 py-0.5 text-[9px] font-semibold uppercase leading-tight
                                    {{ ($pp['status'] ?? 'pending') === 'accepted' ? 'bg-emerald-100 text-emerald-700' : (($pp['status'] ?? 'pending') === 'declined' ? 'bg-red-100 text-red-600' : 'bg-amber-100 text-amber-700') }}">
                                    {{ $pp['status'] ?? 'pending' }}
                                    @if ($pp['status'] === 'accepted' && !empty($pp['accepted_by_name']))
                                        by {{ $pp['accepted_by_name'] }}
                                    @elseif ($pp['status'] === 'declined' && !empty($pp['declined_by_name']))
                                        by {{ $pp['declined_by_name'] }}
                                    @endif
                                </span>
                            </div>
                        @endcan
                        <div class="flex-1 min-w-[160px]">
                            <label class="mb-1 block text-[10px] font-semibold uppercase tracking-wider text-[var(--ds-text-faint)]">Title</label>
                            <input type="text" wire:model="physicalProgress.{{ $i }}.title" class="form-control form-control-sm" placeholder="e.g. Foundation works">
                            @error('physicalProgress.{{ $i }}.title') <p class="ds-form-error">{{ $message }}</p> @enderror
                        </div>
                        <div class="w-24">
                            <label class="mb-1 block text-[10px] font-semibold uppercase tracking-wider text-[var(--ds-text-faint)]">%</label>
                            <input type="number" wire:model="physicalProgress.{{ $i }}.progress_pct" class="form-control form-control-sm" min="0" max="100" step="0.1" placeholder="0">
                            @error('physicalProgress.{{ $i }}.progress_pct') <p class="ds-form-error">{{ $message }}</p> @enderror
                        </div>
                        <div class="flex items-end h-full pt-5">
                            <button type="button" wire:click="removePhysicalProgress({{ $i }})" class="btn btn-sm btn-outline-danger px-2" title="Remove">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                            </button>
                        </div>
                    </div>
                @endforeach
                <button type="button" wire:click="addPhysicalProgress" class="btn btn-outline-secondary btn-sm">+ Add milestone</button>
                @error('physicalProgress') <p class="ds-form-error">{{ $message }}</p> @enderror
            </div>
        </div>
        @endif

        {{-- 6 — Market / contract --}}
        <div class="ds-card">
            <div class="ds-card-header">
                <h2 class="text-[13px] font-semibold text-[var(--ds-text)]">{{ __('ui.create.market') }}</h2>
                <p class="text-[11px] text-[var(--ds-text-faint)]">{{ __('ui.create.market_hint') }}</p>
            </div>
            <div class="ds-card-body space-y-4" x-data="{ showForm: {{ $showOwnerForm ? 'true' : 'false' }} }">
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label for="company_name" class="ds-form-label">Company (entreprise) <span class="text-red-500">*</span></label>
                        <div class="flex gap-2">
                            <div wire:ignore x-data="{
                                ts: null,
                                init() {
                                    this.ts = new TomSelect(this.$refs.select, {
                                        maxOptions: null,
                                        placeholder: 'Search company...',
                                        sortField: { field: 'text', direction: 'asc' },
                                        onChange: (value) => {
                                            this.$wire.set('project_owner_id', value ? parseInt(value) : null).then(() => {
                                                this.$wire.selectProjectOwner(value ? parseInt(value) : null);
                                            });
                                            setTimeout(() => { if (this.ts) this.ts.blur(); }, 50);
                                        },
                                    });
                                    const val = this.$wire.project_owner_id;
                                    if (val) this.ts.setValue(String(val));
                                    Livewire.on('project-owner-created', (e) => {
                                        this.ts.addOption({ value: String(e.id), text: e.display });
                                        this.ts.setValue(String(e.id));
                                    });
                                },
                            }" class="flex-1">
                                <select x-ref="select" id="company_name" class="form-select">
                                    <option value="">Select a company...</option>
                                    @foreach ($this->projectOwners as $po)
                                        <option value="{{ $po->id }}">{{ $po->company_name ?: $po->name }}@if($po->email) ({{ $po->email }})@endif</option>
                                    @endforeach
                                </select>
                            </div>
                            <button type="button" @click="showForm = true" class="btn btn-outline-primary btn-sm flex items-center justify-center rounded-lg border border-dashed px-2 py-1 text-[13px]" title="Add new project owner">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                            </button>
                        </div>
                        @error('company_name') <p class="ds-form-error">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="amount_committed" class="ds-form-label">Montant du marché engagé (DA) <span class="text-red-500">*</span></label>
                        <input wire:model="amount_committed" id="amount_committed" type="number" step="0.1" min="0" class="form-control" />
                        @error('amount_committed') <p class="ds-form-error">{{ $message }}</p> @enderror
                    </div>
                </div>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label for="nif" class="ds-form-label">NIF <span class="text-red-500">*</span></label>
                        <input wire:model="nif" id="nif" type="text" class="form-control" autocomplete="off" />
                        @error('nif') <p class="ds-form-error">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="business_register" class="ds-form-label">Business register (RC / trade register) <span class="text-red-500">*</span></label>
                        <input wire:model="business_register" id="business_register" type="text" class="form-control" />
                        @error('business_register') <p class="ds-form-error">{{ $message }}</p> @enderror
                    </div>
                </div>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label for="ods_date" class="ds-form-label">ODS date (service order / start) <span class="text-red-500">*</span></label>
                        <input wire:model="ods_date" id="ods_date" type="date" class="form-control" />
                        @error('ods_date') <p class="ds-form-error">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="project_duration_months" class="ds-form-label">Project duration (months) <span class="text-red-500">*</span></label>
                        <input wire:model="project_duration_months" id="project_duration_months" type="number" min="1" max="600" class="form-control" />
                        @error('project_duration_months') <p class="ds-form-error">{{ $message }}</p> @enderror
                    </div>
                </div>
                <div x-show="showForm" x-cloak
                     x-transition:enter="transition ease-out duration-300"
                     x-transition:enter-start="opacity-0 -translate-y-2"
                     x-transition:enter-end="opacity-100 translate-y-0"
                     x-transition:leave="transition ease-in duration-200"
                     x-transition:leave-start="opacity-100 translate-y-0"
                     x-transition:leave-end="opacity-0 -translate-y-2">
                    <div class="rounded-xl border border-[var(--ds-primary)]/20 bg-[var(--ds-surface-sunken)] p-4 space-y-4">
                        <p class="text-[13px] font-semibold">New project owner</p>
                        <div>
                            <label for="new_owner_email" class="ds-form-label">Email <span class="text-red-500">*</span></label>
                            <input wire:model="new_owner_email" id="new_owner_email" type="email" class="form-control" placeholder="contact@company.dz" />
                            @error('new_owner_email') <p class="ds-form-error">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="new_owner_name" class="ds-form-label">Name <span class="text-red-500">*</span></label>
                            <input wire:model="new_owner_name" id="new_owner_name" type="text" class="form-control" placeholder="Company Spa" />
                            @error('new_owner_name') <p class="ds-form-error">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="new_owner_company_name" class="ds-form-label">Company (legal name)</label>
                            <input wire:model="new_owner_company_name" id="new_owner_company_name" type="text" class="form-control" />
                            @error('new_owner_company_name') <p class="ds-form-error">{{ $message }}</p> @enderror
                        </div>
                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <label for="new_owner_nif" class="ds-form-label">NIF</label>
                                <input wire:model="new_owner_nif" id="new_owner_nif" type="text" class="form-control" />
                                @error('new_owner_nif') <p class="ds-form-error">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label for="new_owner_business_register" class="ds-form-label">Business register</label>
                                <input wire:model="new_owner_business_register" id="new_owner_business_register" type="text" class="form-control" />
                                @error('new_owner_business_register') <p class="ds-form-error">{{ $message }}</p> @enderror
                            </div>
                        </div>
                        <div class="flex justify-end gap-2 pt-2">
                            <button type="button" @click="showForm = false" class="btn btn-outline-secondary btn-sm">Cancel</button>
                            <button type="button" wire:click="createProjectOwner" wire:loading.attr="disabled" class="btn btn-primary btn-sm">
                                <span wire:loading wire:target="createProjectOwner" class="spinner-border spinner-border-sm me-1" role="status"></span>
                                Enregistrer
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- 7 — Risks --}}
        <div class="ds-card">
            <div class="ds-card-header flex items-center gap-2">
                <span class="rounded-md bg-[var(--ds-surface-sunken)] px-1.5 py-0.5 text-[9px] font-bold uppercase text-[var(--ds-text-faint)]">{{ __('ui.create.optional') }}</span>
                <h2 class="text-[13px] font-semibold text-[var(--ds-text)]">{{ __('ui.create.risks') }}</h2>
            </div>
            <div class="ds-card-body space-y-4">
                <div>
                    <div class="flex items-center gap-3 mb-3">
                        <span class="text-[18px] font-semibold text-[var(--ds-text)]">Files</span>
                        <button type="button" wire:click="addRiskFile" class="btn btn-outline-secondary btn-sm">+ Add file</button>
                    </div>
                    <div class="space-y-4">
                        @foreach ($riskFiles as $i => $rf)
                            <div wire:key="risk-file-{{ $i }}" class="rounded-xl border border-[var(--ds-border)] bg-white shadow-sm">
                                <div class="flex items-center justify-between border-b border-[var(--ds-border-subtle)] bg-[var(--ds-surface-sunken)] px-4 py-2">
                                    <span class="text-[12px] font-semibold text-[var(--ds-text-secondary)]">File {{ $i + 1 }}</span>
                                    <button type="button" wire:click="removeRiskFile({{ $i }})" class="rounded-md p-1 text-[var(--ds-text-faint)] hover:bg-red-50 hover:text-red-600" title="Remove">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                                    </button>
                                </div>
                                <div class="space-y-3 p-4">
                                    <input wire:model="riskFiles.{{ $i }}.notes" type="text" class="form-control" placeholder="Titre / notes…" />
                                    @if (!empty($rf['existing_file']))
                                        <div class="flex items-center gap-3 rounded-lg border border-[var(--ds-border)] bg-[var(--ds-surface-sunken)] px-4 py-3">
                                            @php $ext = pathinfo($rf['existing_file'], PATHINFO_EXTENSION); @endphp
                                            @if (in_array(strtolower($ext), ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg']))
                                                <img src="{{ asset('storage/' . $rf['existing_file']) }}" class="h-12 w-12 shrink-0 rounded object-cover" />
                                            @else
                                                <svg class="h-5 w-5 shrink-0 text-[var(--ds-primary)]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" /></svg>
                                            @endif
                                            <a href="{{ asset('storage/' . $rf['existing_file']) }}" target="_blank" class="text-[13px] font-medium text-[var(--ds-primary)] hover:underline">{{ basename($rf['existing_file']) }}</a>
                                            <span class="text-[11px] text-[var(--ds-text-faint)]">(existing)</span>
                                            <button type="button" x-on:click="if (confirm('Delete this file?')) { fetch('/api/risk-files/{{ $rf['id'] }}', { method: 'DELETE', headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' } }).then(r => { if (r.ok) window.location.reload(); }); }" class="ml-auto rounded-md p-1 text-[var(--ds-text-faint)] hover:bg-red-50 hover:text-red-600" title="Delete file from server">
                                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" /></svg>
                                            </button>
                                        </div>
                                        <div class="flex items-center gap-3 rounded-lg border-2 border-dashed border-[var(--ds-border)] bg-[var(--ds-surface-sunken)] px-4 py-3">
                                            <svg class="h-5 w-5 shrink-0 text-[var(--ds-text-muted)]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5" /></svg>
                                            <input wire:model="riskFiles.{{ $i }}.file" type="file" class="form-control border-0 bg-transparent p-0 text-[13px]" placeholder="Replace file…" />
                                        </div>
                                        @if (! empty($rf['file']))
                                            @php $tmp = $rf['file']; @endphp
                                            <div class="flex items-center gap-3 rounded-lg border border-[var(--ds-border)] bg-[var(--ds-surface-sunken)] px-4 py-3">
                                                @if (is_object($tmp) && method_exists($tmp, 'isPreviewable') && $tmp->isPreviewable())
                                                    <img src="{{ $tmp->temporaryUrl() }}" class="h-12 w-12 shrink-0 rounded object-cover" alt="" />
                                                @else
                                                    <svg class="h-5 w-5 shrink-0 text-[var(--ds-primary)]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" /></svg>
                                                @endif
                                                <span class="text-[13px] text-[var(--ds-text-muted)]">{{ is_object($tmp) && method_exists($tmp, 'getClientOriginalName') ? $tmp->getClientOriginalName() : 'File selected' }}</span>
                                            </div>
                                        @endif
                                    @else
                                        <div class="flex items-center gap-3 rounded-lg border-2 border-dashed border-[var(--ds-border)] bg-[var(--ds-surface-sunken)] px-4 py-3">
                                            <svg class="h-5 w-5 shrink-0 text-[var(--ds-text-muted)]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5" /></svg>
                                            <input wire:model="riskFiles.{{ $i }}.file" type="file" class="form-control border-0 bg-transparent p-0 text-[13px]" />
                                        </div>
                                        @if (! empty($rf['file']))
                                            @php $tmp = $rf['file']; @endphp
                                            <div class="flex items-center gap-3 rounded-lg border border-[var(--ds-border)] bg-[var(--ds-surface-sunken)] px-4 py-3">
                                                @if (is_object($tmp) && method_exists($tmp, 'isPreviewable') && $tmp->isPreviewable())
                                                    <img src="{{ $tmp->temporaryUrl() }}" class="h-12 w-12 shrink-0 rounded object-cover" alt="" />
                                                @else
                                                    <svg class="h-5 w-5 shrink-0 text-[var(--ds-primary)]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" /></svg>
                                                @endif
                                                <span class="text-[13px] text-[var(--ds-text-muted)]">{{ is_object($tmp) && method_exists($tmp, 'getClientOriginalName') ? $tmp->getClientOriginalName() : 'File selected' }}</span>
                                            </div>
                                        @endif
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                    @error('riskFiles.*.file') <p class="ds-form-error">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>

        @php $footerMode = $this->workflowFooterMode; @endphp
        <div class="flex flex-col gap-4 rounded-xl border border-[var(--ds-border)] bg-white px-5 py-4 shadow-sm sm:flex-row sm:flex-wrap sm:items-center sm:justify-between">
            @if ($footerMode === 'resubmit_visa')
                <p class="text-[12px] text-[var(--ds-text-muted)] max-w-md">
                    <strong>{{ __('ui.create.resubmit_visa') }}</strong> — {{ __('ui.create.workflow_resubmit_visa') }}
                </p>
                <div class="flex flex-wrap gap-2">
                    <a href="{{ route('dashbored.projects') }}" class="btn btn-outline-secondary btn-sm">{{ __('ui.common.cancel') }}</a>
                    <button type="button" wire:click="saveDraft" wire:loading.attr="disabled" wire:target="saveDraft" class="btn btn-outline-secondary btn-sm">
                        <span wire:loading wire:target="saveDraft" class="spinner-border spinner-border-sm me-1" role="status"></span>
                        {{ __('ui.create.save_draft') }}
                    </button>
                    <button type="button" wire:click="resubmitToVisa" wire:loading.attr="disabled" wire:target="resubmitToVisa" class="btn btn-primary btn-sm">
                        <span wire:loading wire:target="resubmitToVisa" class="spinner-border spinner-border-sm me-1" role="status"></span>
                        {{ __('ui.create.resubmit_visa') }}
                    </button>
                </div>
            @elseif ($footerMode === 'resubmit_arbitration')
                <p class="text-[12px] text-[var(--ds-text-muted)] max-w-md">
                    <strong>{{ __('ui.create.resubmit_arbitration') }}</strong> — {{ __('ui.create.workflow_resubmit_arbitration') }}
                </p>
                <div class="flex flex-wrap gap-2">
                    <a href="{{ route('dashbored.projects') }}" class="btn btn-outline-secondary btn-sm">{{ __('ui.common.cancel') }}</a>
                    <button type="button" wire:click="saveDraft" wire:loading.attr="disabled" wire:target="saveDraft" class="btn btn-outline-secondary btn-sm">
                        <span wire:loading wire:target="saveDraft" class="spinner-border spinner-border-sm me-1" role="status"></span>
                        {{ __('ui.create.save_draft') }}
                    </button>
                    <button type="button" wire:click="submitToArbitration" wire:loading.attr="disabled" wire:target="submitToArbitration" class="btn btn-primary btn-sm">
                        <span wire:loading wire:target="submitToArbitration" class="spinner-border spinner-border-sm me-1" role="status"></span>
                        {{ __('ui.create.resubmit_arbitration') }}
                    </button>
                </div>
            @elseif ($footerMode === 'new')
                <p class="text-[12px] text-[var(--ds-text-muted)] max-w-md">
                    <strong>{{ __('ui.create.submit_arb') }}</strong> — {{ __('ui.create.workflow_new') }}
                </p>
                <div class="flex flex-wrap gap-2">
                    <a href="{{ route('dashbored.projects') }}" class="btn btn-outline-secondary btn-sm">{{ __('ui.common.cancel') }}</a>
                    <button type="button" wire:click="saveDraft" wire:loading.attr="disabled" wire:target="saveDraft" class="btn btn-outline-secondary btn-sm">
                        <span wire:loading wire:target="saveDraft" class="spinner-border spinner-border-sm me-1" role="status"></span>
                        {{ __('ui.create.save_draft') }}
                    </button>
                    <button type="button" wire:click="submitToArbitration" wire:loading.attr="disabled" wire:target="submitToArbitration" class="btn btn-primary btn-sm">
                        <span wire:loading wire:target="submitToArbitration" class="spinner-border spinner-border-sm me-1" role="status"></span>
                        {{ __('ui.create.submit_arb') }}
                    </button>
                </div>
            @elseif ($footerMode === 'awaiting')
                <p class="text-[12px] text-[var(--ds-text-muted)] max-w-md">
                    <strong>{{ __('ui.status.'.$project_status) }}</strong> — {{ __('ui.create.workflow_awaiting') }}
                </p>
                <div class="flex flex-wrap gap-2">
                    <a href="{{ route('dashbored.projects.show', $editingFipId) }}" class="btn btn-outline-secondary btn-sm">{{ __('ui.common.back') }}</a>
                </div>
            @elseif ($footerMode === 'execution')
                <p class="text-[12px] text-[var(--ds-text-muted)] max-w-md">
                    <strong>{{ __('ui.status.'.$project_status) }}</strong> — {{ __('ui.create.workflow_execution') }}
                </p>
                <div class="flex flex-wrap gap-2">
                    <a href="{{ route('dashbored.projects') }}" class="btn btn-outline-secondary btn-sm">{{ __('ui.common.cancel') }}</a>
                    <button type="button" wire:click="saveDraft" wire:loading.attr="disabled" wire:target="saveDraft" class="btn btn-primary btn-sm">
                        <span wire:loading wire:target="saveDraft" class="spinner-border spinner-border-sm me-1" role="status"></span>
                        {{ __('ui.create.save_updates') }}
                    </button>
                </div>
            @else
                <p class="text-[12px] text-[var(--ds-text-muted)] max-w-md">
                    <strong>{{ __('ui.status.'.$project_status) }}</strong> — {{ __('ui.create.workflow_locked') }}
                </p>
                <div class="flex flex-wrap gap-2">
                    <a href="{{ route('dashbored.projects') }}" class="btn btn-outline-secondary btn-sm">{{ __('ui.common.back') }}</a>
                </div>
            @endif
        </div>
    </div>
</div>
</div>
