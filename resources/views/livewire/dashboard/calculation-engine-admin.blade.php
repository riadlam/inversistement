@php
    use App\Services\Calculation\MetricCatalog;
    use App\Support\Money;
@endphp

<div class="ds-page-enter">
    <x-dashboard.page-header
        :title="__('ui.calc.title')"
        :subtitle="__('ui.calc.subtitle')"
    >
        <x-slot:actions>
            @unless ($canManage)
                <span class="rounded-full bg-slate-100 px-3 py-1 text-[11px] font-semibold text-slate-500">
                    {{ __('ui.calc.readonly') }}
                </span>
            @endunless
        </x-slot:actions>
    </x-dashboard.page-header>

    @if (session('calc-status'))
        <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-2.5 text-[12px] font-medium text-emerald-700">
            {{ session('calc-status') }}
        </div>
    @endif

    {{-- Live progress of the portfolio recompute a save kicks off. --}}
    @if ($recalculation && ! ($recalculation['finished'] ?? false))
        <div class="mb-4 rounded-lg border border-blue-200 bg-blue-50 px-4 py-3">
            <div class="flex items-center justify-between text-[12px] font-semibold text-blue-800">
                <span>Recalcul du portefeuille — {{ $recalculation['reason'] }}</span>
                <span>{{ $recalculation['processed'] }} / {{ $recalculation['total'] }}</span>
            </div>
            <div class="mt-2 h-1.5 w-full overflow-hidden rounded-full bg-blue-100">
                <div class="h-full rounded-full bg-blue-500 transition-all duration-300"
                     style="width: {{ $recalculation['percent'] }}%"></div>
            </div>
        </div>
    @endif

    <div class="mb-4 flex gap-1 rounded-lg border border-[var(--ds-border)] bg-white p-1 text-[12px] font-semibold">
        <button type="button" wire:click="$set('tab', 'rules')"
                @class(['flex-1 rounded-md px-3 py-1.5', 'bg-[var(--ds-text)] text-white' => $tab === 'rules', 'text-[var(--ds-text-muted)]' => $tab !== 'rules'])>
            {{ __('ui.calc.formulas', ['count' => $ruleGroups->flatten()->count()]) }}
        </button>
        <button type="button" wire:click="$set('tab', 'parameters')"
                @class(['flex-1 rounded-md px-3 py-1.5', 'bg-[var(--ds-text)] text-white' => $tab === 'parameters', 'text-[var(--ds-text-muted)]' => $tab !== 'parameters'])>
            {{ __('ui.calc.parameters', ['count' => $parameterGroups->flatten()->count()]) }}
        </button>
        <button type="button" wire:click="$set('tab', 'variables')"
                @class(['flex-1 rounded-md px-3 py-1.5', 'bg-[var(--ds-text)] text-white' => $tab === 'variables', 'text-[var(--ds-text-muted)]' => $tab !== 'variables'])>
            {{ __('ui.calc.variables') }}
        </button>
    </div>

    @if ($tab === 'rules')
        <div class="grid gap-4 lg:grid-cols-[minmax(0,1fr)_minmax(0,1.1fr)]">
            {{-- Rule list, grouped by category --}}
            <div class="space-y-4">
                @foreach ($ruleGroups as $category => $categoryRules)
                    <div class="ds-card overflow-hidden">
                        <p class="border-b border-[var(--ds-border)] bg-[var(--ds-surface-alt)] px-4 py-2 text-[11px] font-bold uppercase tracking-wide text-[var(--ds-text-muted)]">
                            {{ MetricCatalog::categoryLabel($category) }}
                        </p>
                        <div class="divide-y divide-[var(--ds-border)]">
                            @foreach ($categoryRules as $item)
                                <button type="button" wire:click="editRule({{ $item->id }})"
                                        @class([
                                            'block w-full px-4 py-2.5 text-left transition hover:bg-[var(--ds-surface-alt)]',
                                            'bg-emerald-50' => $editingRuleId === $item->id,
                                        ])>
                                    <div class="flex items-center justify-between gap-3">
                                        <span class="text-[12.5px] font-semibold text-[var(--ds-text)]">{{ $item->label_fr }}</span>
                                        <span class="shrink-0 rounded bg-slate-100 px-1.5 py-0.5 font-mono text-[9.5px] text-slate-500">
                                            v{{ $item->version }} · {{ $item->unit }}
                                        </span>
                                    </div>
                                    <code class="mt-1 block truncate font-mono text-[11px] text-[var(--ds-text-faint)]">{{ $item->expression }}</code>
                                    @unless ($item->is_active)
                                        <span class="mt-1 inline-block rounded bg-slate-200 px-1.5 py-0.5 text-[9px] font-bold uppercase text-slate-600">inactive</span>
                                    @endunless
                                </button>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Editor --}}
            <div class="lg:sticky lg:top-4 lg:self-start">
                @if (! $rule)
                    <div class="ds-card p-8 text-center text-[12.5px] text-[var(--ds-text-muted)]">
                        {{ __('ui.calc.select_formula') }}
                    </div>
                @else
                    <div class="ds-card p-4">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <code class="font-mono text-[11px] text-[var(--ds-text-faint)]">{{ $rule->key }}</code>
                                <p class="text-[13px] font-bold text-[var(--ds-text)]">{{ $rule->label_fr }}</p>
                            </div>
                            <button type="button" wire:click="cancelEdit" class="text-[11px] font-semibold text-[var(--ds-text-faint)] hover:underline">
                                Fermer
                            </button>
                        </div>

                        <div class="mt-4 space-y-3">
                            <div>
                                <label class="ds-label">Intitulé</label>
                                <input type="text" wire:model="labelFr" class="form-control form-control-sm" @disabled(! $canManage)>
                                @error('labelFr') <p class="mt-1 text-[11px] text-red-600">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label class="ds-label">Expression</label>
                                <textarea wire:model.live.debounce.500ms="expression" rows="3"
                                          class="form-control form-control-sm font-mono text-[12px]"
                                          @disabled(! $canManage)></textarea>
                                @if ($validationErrors)
                                    <ul class="mt-1.5 space-y-0.5">
                                        @foreach ($validationErrors as $error)
                                            <li class="text-[11px] font-medium text-red-600">• {{ $error }}</li>
                                        @endforeach
                                    </ul>
                                @else
                                    <p class="mt-1 text-[11px] font-medium text-emerald-600">Expression valide.</p>
                                @endif
                            </div>

                            <div class="grid grid-cols-3 gap-2">
                                <div>
                                    <label class="ds-label">Décimales</label>
                                    <input type="number" min="0" max="6" wire:model="precision" class="form-control form-control-sm" @disabled(! $canManage)>
                                </div>
                                <div>
                                    <label class="ds-label">Borne min</label>
                                    <input type="number" step="any" wire:model="clampMin" class="form-control form-control-sm" placeholder="—" @disabled(! $canManage)>
                                </div>
                                <div>
                                    <label class="ds-label">Borne max</label>
                                    <input type="number" step="any" wire:model="clampMax" class="form-control form-control-sm" placeholder="—" @disabled(! $canManage)>
                                </div>
                            </div>

                            <label class="flex items-center gap-2 text-[12px] font-medium text-[var(--ds-text-muted)]">
                                <input type="checkbox" wire:model="isActive" class="form-check-input m-0" @disabled(! $canManage)>
                                Règle active
                            </label>
                        </div>

                        {{-- Live preview against a real operation --}}
                        <div class="mt-4 rounded-lg border border-[var(--ds-border)] bg-[var(--ds-surface-alt)] p-3">
                            <div class="flex items-center justify-between gap-2">
                                <p class="text-[11px] font-bold uppercase tracking-wide text-[var(--ds-text-muted)]">Aperçu sur un projet réel</p>
                                <select wire:model.live="previewProjectId" class="form-select form-select-sm max-w-[55%] text-[11px]">
                                    @foreach ($projects as $p)
                                        <option value="{{ $p->id }}">{{ $p->fip_id }} — {{ Str::limit($p->title_fr, 40) }}</option>
                                    @endforeach
                                </select>
                            </div>

                            @if ($previewError)
                                <p class="mt-2 text-[11px] font-medium text-red-600">{{ $previewError }}</p>
                            @endif

                            <div class="mt-2 grid grid-cols-2 gap-2">
                                <div class="rounded-md bg-white p-2.5">
                                    <p class="text-[10px] font-semibold uppercase text-[var(--ds-text-faint)]">Valeur actuelle</p>
                                    <p class="mt-0.5 font-mono text-[14px] font-bold text-[var(--ds-text)]">
                                        {{ $previewCurrent === null ? '—' : Money::metric($previewCurrent, $rule->unit) }}
                                    </p>
                                </div>
                                <div @class([
                                    'rounded-md p-2.5',
                                    'bg-emerald-50' => $previewNew !== null && $previewNew === $previewCurrent,
                                    'bg-amber-50' => $previewNew !== null && $previewNew !== $previewCurrent,
                                    'bg-white' => $previewNew === null,
                                ])>
                                    <p class="text-[10px] font-semibold uppercase text-[var(--ds-text-faint)]">Avec cette expression</p>
                                    <p class="mt-0.5 font-mono text-[14px] font-bold text-[var(--ds-text)]">
                                        {{ $previewNew === null ? '—' : Money::metric($previewNew, $rule->unit) }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        @if ($canManage)
                            <div class="mt-4 flex items-center gap-2">
                                <button type="button" wire:click="saveRule" class="btn btn-sm btn-dark flex-1"
                                        @disabled($validationErrors !== [])>
                                    Enregistrer et recalculer
                                </button>
                                <button type="button" wire:click="resetRuleToDefault({{ $rule->id }})" class="btn btn-sm btn-outline-secondary">
                                    Formule d'origine
                                </button>
                            </div>
                            <p class="mt-2 text-[10.5px] text-[var(--ds-text-faint)]">
                                Enregistrer relance le calcul de tout le portefeuille et met à jour les tableaux de bord en direct.
                            </p>
                        @endif

                        {{-- Version history --}}
                        @if ($revisions->isNotEmpty())
                            <div class="mt-4 border-t border-[var(--ds-border)] pt-3">
                                <p class="mb-2 text-[11px] font-bold uppercase tracking-wide text-[var(--ds-text-muted)]">Historique</p>
                                <div class="space-y-2">
                                    @foreach ($revisions as $revision)
                                        <div class="rounded-md border border-[var(--ds-border)] p-2">
                                            <div class="flex items-center justify-between text-[10.5px] text-[var(--ds-text-faint)]">
                                                <span>v{{ $revision->version }} · {{ $revision->author?->name ?? 'système' }}</span>
                                                <span>{{ $revision->created_at?->format('d/m/Y H:i') }}</span>
                                            </div>
                                            <code class="mt-1 block font-mono text-[10.5px] text-red-600 line-through">{{ $revision->old_value }}</code>
                                            <code class="block font-mono text-[10.5px] text-emerald-700">{{ $revision->new_value }}</code>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    @elseif ($tab === 'parameters')
        <div class="space-y-4">
            @foreach ($parameterGroups as $category => $categoryParameters)
                <div class="ds-card overflow-hidden">
                    <p class="border-b border-[var(--ds-border)] bg-[var(--ds-surface-alt)] px-4 py-2 text-[11px] font-bold uppercase tracking-wide text-[var(--ds-text-muted)]">
                        {{ MetricCatalog::categoryLabel($category) }}
                    </p>
                    <div class="divide-y divide-[var(--ds-border)]">
                        @foreach ($categoryParameters as $parameter)
                            <div class="flex flex-wrap items-center gap-3 px-4 py-2.5">
                                <div class="min-w-0 flex-1">
                                    <p class="text-[12.5px] font-semibold text-[var(--ds-text)]">{{ $parameter->label_fr }}</p>
                                    <code class="font-mono text-[10.5px] text-[var(--ds-text-faint)]">
                                        {{ $parameter->key }}
                                        @if ($parameter->min !== null || $parameter->max !== null)
                                            · {{ $parameter->min ?? '−∞' }} à {{ $parameter->max ?? '+∞' }}
                                        @endif
                                    </code>
                                </div>
                                <div class="flex items-center gap-2">
                                    <input type="number" step="any"
                                           wire:model="parameterValues.{{ $parameter->key }}"
                                           class="form-control form-control-sm w-24 text-end font-mono"
                                           @disabled(! $canManage)>
                                    <span class="w-12 text-[11px] text-[var(--ds-text-faint)]">{{ $parameter->unit }}</span>
                                    @if ($canManage)
                                        <button type="button" wire:click="saveParameter('{{ $parameter->key }}')"
                                                class="btn btn-sm btn-outline-dark">
                                            Appliquer
                                        </button>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="ds-card p-4">
            <p class="mb-3 text-[12.5px] text-[var(--ds-text-muted)]">
                Identifiants utilisables dans une expression, ainsi que les fonctions
                <code class="font-mono">min max abs round floor ceil sqrt pow intdiv pct div clamp jours</code>.
            </p>
            <div class="flex flex-wrap gap-1.5">
                @foreach ($variables as $variable)
                    <code class="rounded bg-slate-100 px-2 py-1 font-mono text-[11px] text-slate-700">{{ $variable }}</code>
                @endforeach
            </div>
        </div>
    @endif
</div>
