<div class="ds-page-enter max-w-5xl">
    <x-dashboard.page-header
        :title="__('ui.networks_admin.title')"
        :subtitle="__('ui.networks_admin.subtitle')"
    >
        <x-slot:actions>
            <button type="button" wire:click="startCreate" class="btn btn-primary btn-sm">
                {{ __('ui.networks_admin.add') }}
            </button>
        </x-slot:actions>
    </x-dashboard.page-header>

    @if (session('networks-admin-status'))
        <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-2.5 text-[12px] font-medium text-emerald-700">
            {{ session('networks-admin-status') }}
        </div>
    @endif

    @if (session('networks-admin-error'))
        <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-2.5 text-[12px] font-medium text-red-700">
            {{ session('networks-admin-error') }}
        </div>
    @endif

    @if ($showForm)
        <div class="mb-5 rounded-xl border border-[var(--ds-border)] bg-white p-5 shadow-sm">
            <p class="mb-4 text-[13px] font-semibold text-[var(--ds-text)]">
                {{ $editingId ? __('ui.networks_admin.edit') : __('ui.networks_admin.add') }}
            </p>
            <div class="grid gap-3 sm:grid-cols-2">
                <div>
                    <label class="mb-1 block text-[10px] font-semibold uppercase tracking-wider text-[var(--ds-text-faint)]">{{ __('ui.networks_admin.label_fr') }}</label>
                    <input type="text" wire:model="label_fr" class="form-control form-control-sm">
                    @error('label_fr') <p class="mt-1 text-[11px] text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="mb-1 block text-[10px] font-semibold uppercase tracking-wider text-[var(--ds-text-faint)]">{{ __('ui.networks_admin.label_en') }}</label>
                    <input type="text" wire:model="label_en" class="form-control form-control-sm">
                    @error('label_en') <p class="mt-1 text-[11px] text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="mb-1 block text-[10px] font-semibold uppercase tracking-wider text-[var(--ds-text-faint)]">{{ __('ui.networks_admin.code') }}</label>
                    <input type="text" wire:model="code" class="form-control form-control-sm font-mono"
                           @if($editingId) disabled @endif placeholder="{{ __('ui.networks_admin.code_ph') }}">
                    <p class="mt-1 text-[10px] text-[var(--ds-text-faint)]">{{ $editingId ? __('ui.networks_admin.code_locked') : __('ui.networks_admin.code_hint') }}</p>
                    @error('code') <p class="mt-1 text-[11px] text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="mb-1 block text-[10px] font-semibold uppercase tracking-wider text-[var(--ds-text-faint)]">{{ __('ui.networks_admin.sort_order') }}</label>
                    <input type="number" min="0" wire:model="sort_order" class="form-control form-control-sm">
                    @error('sort_order') <p class="mt-1 text-[11px] text-red-600">{{ $message }}</p> @enderror
                </div>
                <div class="sm:col-span-2 flex items-center gap-2">
                    <input id="network_active" type="checkbox" wire:model="is_active" class="rounded border-[var(--ds-border)]">
                    <label for="network_active" class="text-[12px] text-[var(--ds-text)]">{{ __('ui.networks_admin.is_active') }}</label>
                </div>
            </div>
            <div class="mt-4 flex flex-wrap gap-2 justify-end">
                <button type="button" wire:click="cancelForm" class="btn btn-outline-secondary btn-sm">{{ __('ui.common.cancel') }}</button>
                <button type="button" wire:click="save" wire:loading.attr="disabled" wire:target="save" class="btn btn-primary btn-sm">
                    <span wire:loading wire:target="save" class="spinner-border spinner-border-sm me-1" role="status"></span>
                    {{ __('ui.common.save') }}
                </button>
            </div>
        </div>
    @endif

    <div class="mb-3">
        <input type="search" wire:model.live.debounce.300ms="search" class="form-control form-control-sm max-w-sm"
               placeholder="{{ __('ui.networks_admin.search') }}">
    </div>

    <div class="overflow-hidden rounded-xl border border-[var(--ds-border)] bg-white shadow-sm">
        <table class="w-full text-left text-[12px]">
            <thead class="bg-[var(--ds-surface-sunken)] text-[10px] uppercase tracking-wider text-[var(--ds-text-faint)]">
                <tr>
                    <th class="px-4 py-2.5 font-semibold w-16">{{ __('ui.networks_admin.sort_order') }}</th>
                    <th class="px-4 py-2.5 font-semibold">{{ __('ui.networks_admin.label_fr') }}</th>
                    <th class="px-4 py-2.5 font-semibold">{{ __('ui.networks_admin.label_en') }}</th>
                    <th class="px-4 py-2.5 font-semibold">{{ __('ui.networks_admin.status') }}</th>
                    <th class="px-4 py-2.5 font-semibold w-40">{{ __('ui.projects.col_actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($this->types as $type)
                    <tr class="border-t border-[var(--ds-border)]" wire:key="network-type-{{ $type->id }}">
                        <td class="px-4 py-2.5 font-mono text-[11px]">{{ $type->sort_order }}</td>
                        <td class="px-4 py-2.5 font-semibold text-[var(--ds-text)]">{{ $type->label_fr }}</td>
                        <td class="px-4 py-2.5">{{ $type->label_en }}</td>
                        <td class="px-4 py-2.5">
                            @if ($type->is_active)
                                <span class="rounded bg-emerald-50 px-1.5 py-0.5 text-[10px] font-semibold text-emerald-700">{{ __('ui.networks_admin.active') }}</span>
                            @else
                                <span class="rounded bg-slate-100 px-1.5 py-0.5 text-[10px] font-semibold text-slate-500">{{ __('ui.networks_admin.inactive') }}</span>
                            @endif
                        </td>
                        <td class="px-4 py-2.5">
                            <div class="flex flex-wrap items-center gap-1">
                                <button type="button" wire:click="edit({{ $type->id }})" class="btn btn-outline-secondary btn-sm py-0.5 px-2 text-[11px]">
                                    {{ __('ui.common.edit') }}
                                </button>
                                @if ($type->is_active)
                                    <button type="button" wire:click="deactivate({{ $type->id }})" class="btn btn-outline-secondary btn-sm py-0.5 px-2 text-[11px]">
                                        {{ __('ui.networks_admin.deactivate') }}
                                    </button>
                                @else
                                    <button type="button" wire:click="activate({{ $type->id }})" class="btn btn-outline-success btn-sm py-0.5 px-2 text-[11px]">
                                        {{ __('ui.networks_admin.activate') }}
                                    </button>
                                @endif
                                <button type="button"
                                        wire:click="delete({{ $type->id }})"
                                        wire:confirm="{{ __('ui.networks_admin.delete_confirm') }}"
                                        class="btn btn-outline-danger btn-sm py-0.5 px-2 text-[11px]">
                                    {{ __('ui.common.delete') }}
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-10 text-center text-[13px] text-[var(--ds-text-muted)]">
                            {{ __('ui.networks_admin.empty') }}
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
