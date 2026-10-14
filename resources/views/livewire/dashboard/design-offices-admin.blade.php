<div class="ds-page-enter max-w-5xl">
    <x-dashboard.page-header
        :title="__('ui.design_offices.title')"
        :subtitle="__('ui.design_offices.subtitle')"
    >
        <x-slot:actions>
            <button type="button" wire:click="startCreate" class="btn btn-primary btn-sm">
                {{ __('ui.design_offices.add') }}
            </button>
        </x-slot:actions>
    </x-dashboard.page-header>

    @if (session('design-offices-status'))
        <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-2.5 text-[12px] font-medium text-emerald-700">
            {{ session('design-offices-status') }}
        </div>
    @endif

    @if (session('design-offices-error'))
        <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-2.5 text-[12px] font-medium text-red-700">
            {{ session('design-offices-error') }}
        </div>
    @endif

    @if ($showForm)
        <div class="mb-5 rounded-xl border border-[var(--ds-border)] bg-white p-5 shadow-sm">
            <p class="mb-4 text-[13px] font-semibold text-[var(--ds-text)]">
                {{ $editingId ? __('ui.design_offices.edit') : __('ui.design_offices.add') }}
            </p>
            <div class="grid gap-3 sm:grid-cols-2">
                <div class="sm:col-span-2">
                    <label class="mb-1 block text-[10px] font-semibold uppercase tracking-wider text-[var(--ds-text-faint)]">{{ __('ui.design_offices.name') }}</label>
                    <input type="text" wire:model="name" class="form-control form-control-sm" placeholder="{{ __('ui.design_offices.name_ph') }}">
                    @error('name') <p class="mt-1 text-[11px] text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="mb-1 block text-[10px] font-semibold uppercase tracking-wider text-[var(--ds-text-faint)]">{{ __('ui.design_offices.city') }}</label>
                    <input type="text" wire:model="city" class="form-control form-control-sm">
                    @error('city') <p class="mt-1 text-[11px] text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="mb-1 block text-[10px] font-semibold uppercase tracking-wider text-[var(--ds-text-faint)]">{{ __('ui.design_offices.phone') }}</label>
                    <input type="text" wire:model="phone" class="form-control form-control-sm">
                    @error('phone') <p class="mt-1 text-[11px] text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="mb-1 block text-[10px] font-semibold uppercase tracking-wider text-[var(--ds-text-faint)]">{{ __('ui.design_offices.email') }}</label>
                    <input type="email" wire:model="email" class="form-control form-control-sm">
                    @error('email') <p class="mt-1 text-[11px] text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="mb-1 block text-[10px] font-semibold uppercase tracking-wider text-[var(--ds-text-faint)]">{{ __('ui.design_offices.sort_order') }}</label>
                    <input type="number" min="0" wire:model="sort_order" class="form-control form-control-sm">
                    @error('sort_order') <p class="mt-1 text-[11px] text-red-600">{{ $message }}</p> @enderror
                </div>
                <div class="sm:col-span-2 flex items-center gap-2">
                    <input id="office_active" type="checkbox" wire:model="is_active" class="rounded border-[var(--ds-border)]">
                    <label for="office_active" class="text-[12px] text-[var(--ds-text)]">{{ __('ui.design_offices.is_active') }}</label>
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
               placeholder="{{ __('ui.design_offices.search') }}">
    </div>

    <div class="overflow-hidden rounded-xl border border-[var(--ds-border)] bg-white shadow-sm">
        <table class="w-full text-left text-[12px]">
            <thead class="bg-[var(--ds-surface-sunken)] text-[10px] uppercase tracking-wider text-[var(--ds-text-faint)]">
                <tr>
                    <th class="px-4 py-2.5 font-semibold">{{ __('ui.design_offices.name') }}</th>
                    <th class="px-4 py-2.5 font-semibold">{{ __('ui.design_offices.city') }}</th>
                    <th class="px-4 py-2.5 font-semibold">{{ __('ui.design_offices.contact') }}</th>
                    <th class="px-4 py-2.5 font-semibold">{{ __('ui.design_offices.status') }}</th>
                    <th class="px-4 py-2.5 font-semibold w-40">{{ __('ui.projects.col_actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($this->offices as $office)
                    <tr class="border-t border-[var(--ds-border)]" wire:key="office-{{ $office->id }}">
                        <td class="px-4 py-2.5 font-semibold text-[var(--ds-text)]">{{ $office->name }}</td>
                        <td class="px-4 py-2.5">{{ $office->city ?: '—' }}</td>
                        <td class="px-4 py-2.5 text-[var(--ds-text-muted)]">
                            {{ $office->phone ?: '—' }}
                            @if ($office->email)
                                <span class="block text-[11px]">{{ $office->email }}</span>
                            @endif
                        </td>
                        <td class="px-4 py-2.5">
                            @if ($office->is_active)
                                <span class="rounded bg-emerald-50 px-1.5 py-0.5 text-[10px] font-semibold text-emerald-700">{{ __('ui.design_offices.active') }}</span>
                            @else
                                <span class="rounded bg-slate-100 px-1.5 py-0.5 text-[10px] font-semibold text-slate-500">{{ __('ui.design_offices.inactive') }}</span>
                            @endif
                        </td>
                        <td class="px-4 py-2.5">
                            <div class="flex flex-wrap items-center gap-1">
                                <button type="button" wire:click="edit({{ $office->id }})" class="btn btn-outline-secondary btn-sm py-0.5 px-2 text-[11px]">
                                    {{ __('ui.common.edit') }}
                                </button>
                                @if ($office->is_active)
                                    <button type="button" wire:click="deactivate({{ $office->id }})" class="btn btn-outline-secondary btn-sm py-0.5 px-2 text-[11px]">
                                        {{ __('ui.design_offices.deactivate') }}
                                    </button>
                                @else
                                    <button type="button" wire:click="activate({{ $office->id }})" class="btn btn-outline-success btn-sm py-0.5 px-2 text-[11px]">
                                        {{ __('ui.design_offices.activate') }}
                                    </button>
                                @endif
                                <button type="button"
                                        wire:click="delete({{ $office->id }})"
                                        wire:confirm="{{ __('ui.design_offices.delete_confirm') }}"
                                        class="btn btn-outline-danger btn-sm py-0.5 px-2 text-[11px]">
                                    {{ __('ui.common.delete') }}
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-10 text-center text-[13px] text-[var(--ds-text-muted)]">
                            {{ __('ui.design_offices.empty') }}
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
