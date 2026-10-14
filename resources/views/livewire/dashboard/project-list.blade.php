<div class="ds-page-enter max-w-[1400px]">
    <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3000)" x-cloak>
        @if (session('success'))
            <div class="mb-4 flex items-center gap-3 rounded-xl border border-emerald-200 bg-emerald-50 px-5 py-4">
                <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-emerald-100 text-emerald-600">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" /></svg>
                </span>
                <p class="text-[13px] font-semibold text-emerald-800">{{ session('success') }}</p>
            </div>
        @endif
    </div>
    <x-dashboard.page-header
        :title="__('ui.projects.title')"
        :subtitle="__('ui.projects.subtitle')"
    >
        @auth
            @can('projects.create')
                <x-slot:actions>
                    <x-dashboard.ui-button onclick="window.location='{{ route('dashbored.projects.create') }}'">
                        {{ __('ui.projects.add') }}
                    </x-dashboard.ui-button>
                </x-slot:actions>
            @endcan
        @endauth
    </x-dashboard.page-header>

    <div class="ds-card ds-card-static">
        <div class="ds-card-header border-b-0 pb-0">
            <div>
                <h2 class="text-[13px] font-semibold">{{ __('ui.projects.register') }}</h2>
                <p class="text-[11px] text-[var(--ds-text-faint)]">{{ __('ui.projects.found', ['count' => count($this->filteredProjects)]) }}</p>
            </div>
        </div>
        {{-- Filters --}}
        <div class="px-5 pb-4 pt-3">
            <div class="flex flex-wrap gap-1.5 items-end">
                    @if ($this->canFilterCommune)
                        <div wire:ignore x-data="dsTomSelectFilter({ wireProp: 'filterCommune', placeholder: @js(__('ui.projects.all_communes')) })">
                            <label class="mb-0.5 block text-[10px] font-semibold uppercase tracking-wider text-[var(--ds-text-faint)]">{{ __('ui.projects.commune') }}</label>
                            <select x-ref="select" class="form-select form-select-sm" style="min-width: 180px;">
                                <option value="">{{ __('ui.projects.all_communes') }}</option>
                                @foreach ($this->filterCommuneOptions as $c)
                                    <option value="{{ $c['value'] }}">{{ $c['label'] }}</option>
                                @endforeach
                            </select>
                        </div>
                    @else
                        <div wire:ignore x-data="dsTomSelectFilter({ wireProp: 'filterWilaya', placeholder: @js(__('ui.projects.all_wilayas')) })">
                            <label class="mb-0.5 block text-[10px] font-semibold uppercase tracking-wider text-[var(--ds-text-faint)]">{{ __('ui.projects.wilaya') }}</label>
                            <select x-ref="select" class="form-select form-select-sm" style="min-width: 180px;">
                                <option value="">{{ __('ui.projects.all') }}</option>
                                @foreach ($this->filterWilayaOptions as $w)
                                    <option value="{{ $w['value'] }}">{{ $w['label'] }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif
                    <div wire:ignore x-data="dsTomSelectFilter({ wireProp: 'filterEtablissement', placeholder: @js(__('ui.projects.all_establishments')) })">
                        <label class="mb-0.5 block text-[10px] font-semibold uppercase tracking-wider text-[var(--ds-text-faint)]">{{ __('ui.projects.est') }}</label>
                        <select x-ref="select" class="form-select form-select-sm" style="min-width: 180px;">
                            <option value="">{{ __('ui.projects.all') }}</option>
                            @foreach ($this->filterEtablissementOptions as $e)
                                <option value="{{ $e['value'] }}">{{ $e['label'] }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div wire:ignore x-data="dsTomSelectFilter({ wireProp: 'filterProgramme', placeholder: @js(__('ui.projects.all_programmes')) })">
                        <label class="mb-0.5 block text-[10px] font-semibold uppercase tracking-wider text-[var(--ds-text-faint)]">{{ __('ui.projects.programme') }}</label>
                        <select x-ref="select" class="form-select form-select-sm" style="min-width: 180px;">
                            <option value="">{{ __('ui.projects.all') }}</option>
                            @foreach ($this->filterProgrammeOptions as $p)
                                <option value="{{ $p['value'] }}">{{ $p['label'] }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div wire:ignore x-data="dsTomSelectFilter({ wireProp: 'filterStatus', placeholder: @js(__('ui.projects.all_statuses')) })">
                        <label class="mb-0.5 block text-[10px] font-semibold uppercase tracking-wider text-[var(--ds-text-faint)]">{{ __('ui.projects.status') }}</label>
                        <select x-ref="select" class="form-select form-select-sm" style="min-width: 160px;">
                                <option value="">{{ __('ui.projects.all') }}</option>
                                @foreach ($this->filterStatusOptions as $s)
                                    <option value="{{ $s['value'] }}">{{ $s['label'] }}</option>
                                @endforeach
                            </select>
                    </div>
                    @if ($this->hasActiveFilters)
                    <div>
                        <label class="mb-0.5 block text-[10px] font-semibold uppercase tracking-wider text-[var(--ds-text-faint)]">&nbsp;</label>
                        <button type="button" x-on:click="
                            $wire.set('filterWilaya', '');
                            $wire.set('filterCommune', '');
                            $wire.set('filterEtablissement', '');
                            $wire.set('filterProgramme', '');
                            $wire.set('filterStatus', '');
                            window.dispatchEvent(new CustomEvent('clear-filters'));
                        " class="btn btn-outline-secondary btn-sm">&times; {{ __('ui.projects.clear') }}</button>
                    </div>
                    @endif
                </div>
            </div>

        {{-- Table --}}
        <x-dashboard.ui-table>
            <thead>
                <tr>
                    <th>{{ __('ui.projects.col_id') }}</th>
                    <th>{{ __('ui.projects.col_title') }}</th>
                    <th>{{ __('ui.projects.programme') }}</th>
                    <th>{{ __('ui.projects.col_ae_cp') }}</th>
                    <th>{{ __('ui.projects.col_physical') }}</th>
                    <th>SPU</th>
                    <th>{{ __('ui.projects.col_signal') }}</th>
                    <th>{{ __('ui.projects.status') }}</th>
                    <th class="w-24">{{ __('ui.projects.col_actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($this->filteredProjects as $p)
                    <tr wire:key="{{ $p['id'] }}" class="transition hover:bg-[var(--ds-surface-sunken)]/60">
                        <td>
                            <a href="{{ route('dashbored.projects.show', ['projectId' => $p['id']]) }}" class="rounded bg-[var(--ds-surface-sunken)] px-1.5 py-0.5 font-mono text-[11px] font-semibold text-[var(--ds-primary)] hover:underline">{{ $p['id'] }}</a>
                        </td>
                        <td class="max-w-[260px]">
                            <a href="{{ route('dashbored.projects.show', ['projectId' => $p['id']]) }}" class="group block">
                                <p class="truncate text-[13px] font-medium text-[var(--ds-text)] group-hover:text-[var(--ds-primary)] group-hover:underline">{{ $p['title'] }}</p>
                            </a>
                        </td>
                        <td class="text-[var(--ds-text-muted)]">{{ $p['program'] }}</td>
                        <td>
                            <span class="text-[13px] font-medium">{{ \App\Support\Money::da($p['ae_da'], 0) }}</span>
                            <span class="text-[var(--ds-text-faint)]">/</span>
                            <span class="text-[13px] font-medium">{{ \App\Support\Money::da($p['cp_da'], 0) }}</span>
                        </td>
                        <td>
                            <div class="flex items-center gap-2">
                                <div class="ds-progress h-1.5 w-14">
                                    <div class="ds-progress-bar" style="width: {{ $p['physical'] }}%"></div>
                                </div>
                                <span class="text-[11px] font-semibold">{{ $p['physical'] }}%</span>
                            </div>
                        </td>
                        <td class="text-[13px] font-bold">{{ $p['spu'] }}</td>
                        <td>
                            <span class="ds-traffic ds-traffic-{{ $p['traffic'] }}" style="width: 1.75rem; height: 1.75rem; font-size: 0.5rem;">
                                {{ strtoupper(substr($p['traffic'], 0, 1)) }}
                            </span>
                        </td>
                        <td>
                            @php
                                $statusVariant = match ($p['status']) {
                                    'completed', 'approved', 'in_execution' => 'success',
                                    'draft', 'maturation', 'submitted', 'in_arbitration', 'pending_visa' => 'warning',
                                    'returned' => 'danger',
                                    'closed' => 'neutral',
                                    default => 'danger',
                                };
                                $statusLabel = __('ui.status.'.$p['status']);
                                if ($statusLabel === 'ui.status.'.$p['status']) {
                                    $statusLabel = ucfirst(str_replace('_', ' ', (string) $p['status']));
                                }
                            @endphp
                            <x-dashboard.ui-badge :variant="$statusVariant">{{ $statusLabel }}</x-dashboard.ui-badge>
                        </td>
                        <td>
                            <div class="flex items-center gap-1">
                                @if (! empty($p['can_edit']))
                                    <a href="{{ route('dashbored.projects.edit', ['fipId' => $p['id']]) }}"
                                       class="inline-flex h-7 w-7 items-center justify-center rounded-md text-[var(--ds-text-muted)] transition hover:bg-[var(--ds-surface-sunken)] hover:text-[var(--ds-primary)]"
                                       title="{{ __('ui.common.edit') }}">
                                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" /></svg>
                                    </a>
                                @endif
                                <a href="{{ route('dashbored.projects.cp', ['projectId' => $p['id']]) }}"
                                   class="inline-flex h-7 w-7 items-center justify-center rounded-md text-[var(--ds-text-muted)] transition hover:bg-[var(--ds-surface-sunken)] hover:text-[var(--ds-accent)]"
                                   title="{{ __('ui.projects.cp_title') }}">
                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                </a>
                                @if (! empty($p['can_delete']))
                                    <button
                                        type="button"
                                        x-on:click="if (confirm(@js(__('ui.projects.delete_confirm', ['id' => $p['id']])))) { fetch('/api/projects/{{ $p['id'] }}', { method: 'DELETE', headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' } }).then(r => { if (r.ok) window.location.reload(); }); }"
                                        class="inline-flex h-7 w-7 items-center justify-center rounded-md text-[var(--ds-text-muted)] transition hover:bg-red-50 hover:text-red-600"
                                    >
                                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" /></svg>
                                    </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="py-12 text-center">
                            <p class="text-[13px] font-medium text-[var(--ds-text-muted)]">{{ __('ui.projects.empty') }}</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </x-dashboard.ui-table>
    </div>

</div>
