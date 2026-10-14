@php $user = auth()->user(); @endphp

<div class="ds-page-enter max-w-3xl">
    <x-dashboard.page-header
        :title="__('ui.profile.title')"
        :subtitle="__('ui.profile.subtitle')"
    />

    <div class="ds-card mb-6 overflow-hidden">
        {{-- Profile banner --}}
        <div class="relative bg-gradient-to-r from-slate-800 via-emerald-900 to-slate-800 px-6 py-8">
            <div class="pointer-events-none absolute inset-0 bg-[url('data:image/svg+xml;charset=utf-8,%3Csvg%20width%3D%2240%22%20height%3D%2240%22%20viewBox%3D%220%200%2040%2040%22%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%3E%3Cg%20fill%3D%22%23fff%22%20fill-opacity%3D%220.03%22%3E%3Cpath%20d%3D%22M0%2040L40%200H20L0%2020zm40%200V20L20%2040z%22%2F%3E%3C%2Fg%3E%3C%2Fsvg%3E')]"></div>
            <div class="relative flex items-center gap-4">
                <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-white/10 text-2xl font-extrabold text-white ring-2 ring-white/20 backdrop-blur-sm">
                    {{ $this->initials }}
                </div>
                <div>
                    <p class="text-lg font-bold text-white">{{ $user?->name ?? '—' }}</p>
                    <p class="text-[13px] text-white/60">{{ $user?->email ?? '' }}</p>
                    <span class="mt-1.5 inline-block rounded-full bg-emerald-500/20 px-2.5 py-0.5 text-[10px] font-bold text-emerald-300">
                        {{ $user?->roleLabel() }}
                    </span>
                </div>
            </div>
        </div>

        <div class="ds-card-body space-y-5">
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="mb-1 block text-[11px] font-semibold uppercase tracking-wider text-[var(--ds-text-faint)]">{{ __('ui.profile.username') }}</label>
                    <input type="text" class="form-control" value="{{ $user?->username }}" disabled />
                </div>
                <div>
                    <label class="mb-1 block text-[11px] font-semibold uppercase tracking-wider text-[var(--ds-text-faint)]">{{ __('ui.profile.role_raci') }}</label>
                    <input type="text" class="form-control" value="{{ $user?->roleLabel() }}" disabled />
                </div>
                <div>
                    <label class="mb-1 block text-[11px] font-semibold uppercase tracking-wider text-[var(--ds-text-faint)]">{{ __('ui.profile.function') }}</label>
                    <input type="text" class="form-control" value="{{ $user?->roleEnum()?->function() ?? '—' }}" disabled />
                </div>
                <div>
                    <label class="mb-1 block text-[11px] font-semibold uppercase tracking-wider text-[var(--ds-text-faint)]">{{ __('ui.profile.scope') }}</label>
                    <input type="text" class="form-control"
                           value="{{ $user?->isNational() ? __('ui.profile.scope_national') : __('ui.profile.scope_wilaya', ['name' => $user?->wilaya?->name_fr ?? '—']) }}"
                           disabled />
                </div>
            </div>
        </div>
    </div>

    {{-- Effective permissions --}}
    <div class="ds-card">
        <div class="ds-card-header flex items-center justify-between">
            <div>
                <h3 class="text-[13px] font-semibold">{{ __('ui.profile.rights') }}</h3>
                <p class="text-[11px] text-[var(--ds-text-faint)]">
                    {{ __('ui.profile.granted', ['count' => $this->grantedCount]) }}
                </p>
            </div>
            @can('users.manage')
                <x-dashboard.ui-badge variant="success">{{ __('ui.profile.admin') }}</x-dashboard.ui-badge>
            @endcan
        </div>
        <div class="ds-card-body space-y-5">
            @foreach ($this->permissionMatrix as $category => $items)
                <div>
                    <p class="mb-2 text-[10px] font-bold uppercase tracking-[0.12em] text-[var(--ds-text-faint)]">{{ $category }}</p>
                    <div class="grid gap-1.5 sm:grid-cols-2">
                        @foreach ($items as $item)
                            <div @class([
                                'flex items-center gap-2 rounded-lg border px-3 py-2 text-[12px]',
                                'border-emerald-200 bg-emerald-50/60 text-emerald-900' => $item['granted'],
                                'border-[var(--ds-border-subtle)] bg-[var(--ds-surface-sunken)] text-[var(--ds-text-faint)]' => ! $item['granted'],
                            ])>
                                @if ($item['granted'])
                                    <svg class="h-3.5 w-3.5 shrink-0 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                                    </svg>
                                @else
                                    <svg class="h-3.5 w-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                @endif
                                <span class="truncate">{{ $item['label'] }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>
