<div class="ds-page-enter max-w-5xl">
    <x-dashboard.page-header
        :title="__('ui.holdings.title')"
        :subtitle="__('ui.holdings.subtitle')"
    >
        <x-slot:actions>
            <button type="button" wire:click="startCreate" class="btn btn-primary btn-sm">
                {{ __('ui.holdings.add') }}
            </button>
        </x-slot:actions>
    </x-dashboard.page-header>

    @if (session('holdings-status'))
        <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-2.5 text-[12px] font-medium text-emerald-700">
            {{ session('holdings-status') }}
        </div>
    @endif

    @if (session('holdings-error'))
        <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-2.5 text-[12px] font-medium text-red-700">
            {{ session('holdings-error') }}
        </div>
    @endif

    @if ($showForm)
        <div class="mb-5 rounded-xl border border-[var(--ds-border)] bg-white p-5 shadow-sm">
            <p class="mb-4 text-[13px] font-semibold text-[var(--ds-text)]">
                {{ $editingId ? __('ui.holdings.edit') : __('ui.holdings.add') }}
            </p>
            <div class="grid gap-3 sm:grid-cols-2">
                <div class="sm:col-span-2">
                    <label class="mb-1 block text-[10px] font-semibold uppercase tracking-wider text-[var(--ds-text-faint)]">{{ __('ui.holdings.company_name') }}</label>
                    <input type="text" wire:model="company_name" class="form-control form-control-sm" placeholder="{{ __('ui.holdings.company_name_ph') }}">
                    @error('company_name') <p class="mt-1 text-[11px] text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="mb-1 block text-[10px] font-semibold uppercase tracking-wider text-[var(--ds-text-faint)]">{{ __('ui.holdings.contact_name') }}</label>
                    <input type="text" wire:model="name" class="form-control form-control-sm">
                    @error('name') <p class="mt-1 text-[11px] text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="mb-1 block text-[10px] font-semibold uppercase tracking-wider text-[var(--ds-text-faint)]">{{ __('ui.holdings.email') }}</label>
                    <input type="email" wire:model="email" class="form-control form-control-sm">
                    @error('email') <p class="mt-1 text-[11px] text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="mb-1 block text-[10px] font-semibold uppercase tracking-wider text-[var(--ds-text-faint)]">{{ __('ui.holdings.nif') }}</label>
                    <input type="text" wire:model="nif" class="form-control form-control-sm">
                    @error('nif') <p class="mt-1 text-[11px] text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="mb-1 block text-[10px] font-semibold uppercase tracking-wider text-[var(--ds-text-faint)]">{{ __('ui.holdings.business_register') }}</label>
                    <input type="text" wire:model="business_register" class="form-control form-control-sm">
                    @error('business_register') <p class="mt-1 text-[11px] text-red-600">{{ $message }}</p> @enderror
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
               placeholder="{{ __('ui.holdings.search') }}">
    </div>

    <div class="overflow-hidden rounded-xl border border-[var(--ds-border)] bg-white shadow-sm">
        <table class="w-full text-left text-[12px]">
            <thead class="bg-[var(--ds-surface-sunken)] text-[10px] uppercase tracking-wider text-[var(--ds-text-faint)]">
                <tr>
                    <th class="px-4 py-2.5 font-semibold">{{ __('ui.holdings.company_name') }}</th>
                    <th class="px-4 py-2.5 font-semibold">{{ __('ui.holdings.contact_name') }}</th>
                    <th class="px-4 py-2.5 font-semibold">{{ __('ui.holdings.email') }}</th>
                    <th class="px-4 py-2.5 font-semibold">{{ __('ui.holdings.nif') }}</th>
                    <th class="px-4 py-2.5 font-semibold w-28">{{ __('ui.projects.col_actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($this->owners as $owner)
                    <tr class="border-t border-[var(--ds-border)]" wire:key="owner-{{ $owner->id }}">
                        <td class="px-4 py-2.5 font-semibold text-[var(--ds-text)]">{{ $owner->company_name ?: $owner->name }}</td>
                        <td class="px-4 py-2.5">{{ $owner->name }}</td>
                        <td class="px-4 py-2.5 text-[var(--ds-text-muted)]">{{ $owner->email ?: '—' }}</td>
                        <td class="px-4 py-2.5 font-mono text-[11px]">{{ $owner->nif ?: '—' }}</td>
                        <td class="px-4 py-2.5">
                            <div class="flex items-center gap-1">
                                <button type="button" wire:click="edit({{ $owner->id }})" class="btn btn-outline-secondary btn-sm py-0.5 px-2 text-[11px]">
                                    {{ __('ui.common.edit') }}
                                </button>
                                <button type="button"
                                        wire:click="delete({{ $owner->id }})"
                                        wire:confirm="{{ __('ui.holdings.delete_confirm') }}"
                                        class="btn btn-outline-danger btn-sm py-0.5 px-2 text-[11px]">
                                    {{ __('ui.common.delete') }}
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-10 text-center text-[13px] text-[var(--ds-text-muted)]">
                            {{ __('ui.holdings.empty') }}
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
