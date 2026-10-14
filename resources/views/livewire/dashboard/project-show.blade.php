@php
    $p = $this->detail;
    $statusBadge = match ($p['status']) {
        'completed' => ['variant' => 'success', 'label' => __('ui.status.completed')],
        'in_execution' => ['variant' => 'info', 'label' => __('ui.status.in_execution')],
        'submitted' => ['variant' => 'warning', 'label' => __('ui.status.submitted')],
        'in_arbitration' => ['variant' => 'warning', 'label' => __('ui.status.in_arbitration')],
        'pending_visa' => ['variant' => 'warning', 'label' => __('ui.status.pending_visa')],
        'returned' => ['variant' => 'danger', 'label' => __('ui.status.returned')],
        'approved' => ['variant' => 'success', 'label' => __('ui.status.approved')],
        'draft' => ['variant' => 'secondary', 'label' => __('ui.status.draft')],
        'maturation' => ['variant' => 'secondary', 'label' => __('ui.status.maturation')],
        'closed' => ['variant' => 'secondary', 'label' => __('ui.status.closed')],
        default => ['variant' => 'danger', 'label' => ucfirst($p['status'])],
    };
@endphp

<div class="ds-page-enter max-w-5xl">
    <x-dashboard.page-header
        :title="$p['title_fr']"
        :subtitle="__('ui.show.subtitle')"
    >
        <x-slot:actions>
            @if (auth()->user()->hasPermission('reports.export'))
                <a href="{{ route('dashbored.projects.fiche-suivi', $p['id']) }}" class="btn btn-primary btn-sm">
                    {{ __('ui.show.fiche_pdf') }}
                </a>
            @endif
            @if (! empty($p['can_view_cp']))
                <a href="{{ route('dashbored.projects.cp', $p['id']) }}" class="btn btn-outline-primary btn-sm" wire:navigate>
                    {{ __('ui.show.cp') }}
                </a>
            @endif
            @if (auth()->user()->hasPermission('lots.view'))
                <a href="{{ route('dashbored.projects.fiche', $p['id']) }}" class="btn btn-outline-primary btn-sm" wire:navigate>
                    {{ __('ui.show.lots_fiche') }}
                </a>
            @endif
            @if (! empty($p['can_edit']))
                <a href="{{ route('dashbored.projects.edit', $p['id']) }}" class="btn btn-outline-primary btn-sm">{{ __('ui.common.edit') }}</a>
            @endif
            <a href="{{ route('dashbored.projects') }}" class="btn btn-outline-secondary btn-sm">{{ __('ui.show.register') }}</a>
        </x-slot:actions>
    </x-dashboard.page-header>

    @if (session('success'))
        <div class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-[13px] font-medium text-emerald-800">
            {{ session('success') }}
        </div>
    @endif

    @if ($p['status'] === 'returned' && ! empty($p['rejection_reason']))
        <div class="mb-6 rounded-xl border border-red-200 bg-red-50 px-5 py-4 shadow-sm">
            <div class="flex flex-wrap items-start gap-3">
                <x-dashboard.ui-badge variant="danger">{{ __('ui.status.returned') }}</x-dashboard.ui-badge>
                <div class="min-w-0 flex-1">
                    <p class="text-[13px] font-semibold text-red-900">{{ __('ui.show.returned_banner') }}</p>
                    <p class="mt-0.5 text-[12px] text-red-800/80">{{ __('ui.show.returned_banner_hint') }}</p>
                    <p class="mt-2 rounded-lg border border-red-200/80 bg-white/80 px-3 py-2 text-[12px] text-red-950">{{ $p['rejection_reason'] }}</p>
                </div>
            </div>
        </div>
    @endif

    @if (! empty($p['pending_situations']) && ! empty($p['can_validate_situation']))
        <div class="mb-6 rounded-xl border border-amber-300 bg-amber-50 px-5 py-4 shadow-sm">
            <p class="text-[13px] font-semibold text-amber-950">{{ __('ui.show.situation_pending_title') }}</p>
            <p class="mt-0.5 mb-3 text-[12px] text-amber-900/80">{{ __('ui.show.situation_pending_hint') }}</p>
            <div class="space-y-3">
                @foreach ($p['pending_situations'] as $pp)
                    <div class="flex flex-wrap items-start justify-between gap-3 rounded-lg border border-amber-200 bg-white/90 px-4 py-3">
                        <div class="min-w-0">
                            <p class="text-[12px] font-semibold text-amber-950">
                                {{ __('ui.show.situation_pending_item', [
                                    'title' => $pp['title'],
                                    'pct' => $pp['progress_pct'],
                                    'by' => $pp['submitted_by'],
                                    'date' => $pp['created_at'],
                                ]) }}
                            </p>
                        </div>
                        <div class="flex flex-wrap gap-2 shrink-0">
                            <button type="button" wire:click="acceptProgress({{ $pp['id'] }})" class="btn btn-success btn-sm">
                                {{ __('ui.show.situation_accept') }}
                            </button>
                            <button type="button" wire:click="declineProgress({{ $pp['id'] }})" class="btn btn-outline-danger btn-sm">
                                {{ __('ui.show.situation_decline') }}
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    @if (! empty($p['pending_cp_requests']))
        <div class="mb-6 rounded-xl border border-teal-200 bg-teal-50/80 px-5 py-4 shadow-sm">
            <div class="mb-3 flex flex-wrap items-start justify-between gap-2">
                <div>
                    <p class="text-[13px] font-semibold text-teal-900">{{ __('ui.show.cp_pending_title') }}</p>
                    <p class="mt-0.5 text-[12px] text-teal-800/80">{{ __('ui.show.cp_pending_hint') }}</p>
                </div>
                @if (! empty($p['can_view_cp']))
                    <a href="{{ route('dashbored.projects.cp', $p['id']) }}" class="btn btn-outline-secondary btn-sm" wire:navigate>
                        {{ __('ui.show.cp_open_desk') }}
                    </a>
                @endif
            </div>
            <div class="space-y-3">
                @foreach ($p['pending_cp_requests'] as $cp)
                    <div class="rounded-lg border border-teal-200/80 bg-white/80 px-4 py-3">
                        <div class="flex flex-wrap items-start justify-between gap-3">
                            <div class="min-w-0">
                                <p class="text-[12px] font-semibold text-teal-950">
                                    {{ __('ui.show.cp_pending_item', [
                                        'amount' => number_format($cp['amount_da'], 2, ',', ' '),
                                        'by' => $cp['submitted_by'],
                                        'date' => $cp['created_at'],
                                    ]) }}
                                </p>
                                @if ($cp['description'] !== '')
                                    <p class="mt-1 text-[12px] text-teal-900/90">{{ $cp['description'] }}</p>
                                @endif
                            </div>
                            @if (! empty($cp['can_decide']))
                                <div class="flex flex-wrap gap-2 shrink-0">
                                    <button type="button" wire:click="showCpAcceptForm({{ $cp['id'] }})" class="btn btn-success btn-sm">
                                        {{ __('ui.cp.validate') }}
                                    </button>
                                    <button type="button" wire:click="showCpRejectForm({{ $cp['id'] }})" class="btn btn-outline-danger btn-sm">
                                        {{ __('ui.cp.reject') }}
                                    </button>
                                </div>
                            @endif
                        </div>

                        @if ($cpAcceptingId === $cp['id'])
                            <div class="mt-3 space-y-3 border-t border-teal-100 pt-3">
                                <div class="grid gap-3 sm:grid-cols-2">
                                    <div>
                                        <label class="mb-1 block text-[10px] font-semibold uppercase tracking-wider text-teal-900/70">{{ __('ui.cp.payment_type') }}</label>
                                        <select wire:model="cpPaymentType" class="form-select form-select-sm">
                                            <option value="regular">{{ __('ui.cp.regular') }}</option>
                                            <option value="credit_request">{{ __('ui.cp.credit_request') }}</option>
                                            <option value="revaluation">{{ __('ui.cp.revaluation') }}</option>
                                        </select>
                                        @error('cpPaymentType') <p class="mt-1 text-[11px] text-red-600">{{ $message }}</p> @enderror
                                    </div>
                                    <div>
                                        <label class="mb-1 block text-[10px] font-semibold uppercase tracking-wider text-teal-900/70">{{ __('ui.cp.mandate_number') }}</label>
                                        <input type="text" wire:model="cpMandateNumber" class="form-control form-control-sm">
                                    </div>
                                </div>
                                <div>
                                    <label class="mb-1 block text-[10px] font-semibold uppercase tracking-wider text-teal-900/70">{{ __('ui.cp.receipt_upload') }}</label>
                                    <p class="mb-1.5 text-[11px] text-teal-800/70">{{ __('ui.cp.receipt_upload_hint') }}</p>
                                    <div class="flex items-center gap-3 rounded-lg border-2 border-dashed border-teal-200 bg-white/70 px-3 py-2.5">
                                        <input wire:model="cpReceipt" type="file" accept=".pdf,.jpg,.jpeg,.png,application/pdf,image/*" class="form-control form-control-sm border-0 bg-transparent p-0 text-[12px]" />
                                    </div>
                                    <div wire:loading wire:target="cpReceipt" class="mt-1 text-[11px] text-teal-800/70">{{ __('ui.cp.uploading') }}</div>
                                    @error('cpReceipt') <p class="mt-1 text-[11px] text-red-600">{{ $message }}</p> @enderror
                                    @if ($cpReceipt)
                                        <p class="mt-1.5 text-[12px] font-medium text-teal-900">
                                            {{ is_object($cpReceipt) && method_exists($cpReceipt, 'getClientOriginalName') ? $cpReceipt->getClientOriginalName() : __('ui.cp.file_selected') }}
                                        </p>
                                    @endif
                                </div>
                                <div class="flex justify-end gap-2">
                                    <button type="button" wire:click="cancelCpAction" class="btn btn-outline-secondary btn-sm">{{ __('ui.common.cancel') }}</button>
                                    <button type="button" wire:click="confirmCpAccept" wire:loading.attr="disabled" wire:target="confirmCpAccept" class="btn btn-success btn-sm">
                                        <span wire:loading wire:target="confirmCpAccept" class="spinner-border spinner-border-sm" role="status"></span>
                                        {{ __('ui.cp.confirm_validation') }}
                                    </button>
                                </div>
                            </div>
                        @endif

                        @if ($cpRejectingId === $cp['id'])
                            <div class="mt-3 space-y-3 border-t border-teal-100 pt-3">
                                <div>
                                    <label class="mb-1 block text-[10px] font-semibold uppercase tracking-wider text-teal-900/70">{{ __('ui.cp.rejection_cause') }}</label>
                                    <textarea wire:model="cpRejectionCause" rows="2" class="form-control form-control-sm" placeholder="{{ __('ui.cp.rejection_ph') }}"></textarea>
                                    @error('cpRejectionCause') <p class="mt-1 text-[11px] text-red-600">{{ $message }}</p> @enderror
                                </div>
                                <div class="flex justify-end gap-2">
                                    <button type="button" wire:click="cancelCpAction" class="btn btn-outline-secondary btn-sm">{{ __('ui.common.cancel') }}</button>
                                    <button type="button" wire:click="confirmCpReject" wire:loading.attr="disabled" wire:target="confirmCpReject" class="btn btn-outline-danger btn-sm">
                                        <span wire:loading wire:target="confirmCpReject" class="spinner-border spinner-border-sm" role="status"></span>
                                        {{ __('ui.cp.confirm_rejection') }}
                                    </button>
                                </div>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    @if (! empty($p['pending_reevaluations']))
        <div class="mb-6 rounded-xl border border-violet-200 bg-violet-50/80 px-5 py-4 shadow-sm">
            <p class="text-[13px] font-semibold text-violet-900">{{ __('ui.show.reeval_pending_title') }}</p>
            <p class="mt-0.5 mb-3 text-[12px] text-violet-800/80">{{ __('ui.show.reeval_pending_hint') }}</p>
            <div class="space-y-3">
                @foreach ($p['pending_reevaluations'] as $reeval)
                    <div class="rounded-lg border border-violet-200/80 bg-white/80 px-4 py-3">
                        <div class="flex flex-wrap items-start justify-between gap-3">
                            <div class="min-w-0">
                                <p class="text-[12px] font-semibold text-violet-950">
                                    {{ __('ui.show.reeval_pending_item', [
                                        'n' => $reeval['sequence'],
                                        'year' => $reeval['year'],
                                        'amount' => number_format($reeval['amount_da'], 2, ',', ' '),
                                        'by' => $reeval['submitted_by'] ?? '—',
                                    ]) }}
                                </p>
                                @if ($reeval['decision_ref'] !== '')
                                    <p class="mt-0.5 text-[11px] text-violet-800/70">{{ __('ui.show.reeval_ref') }}: {{ $reeval['decision_ref'] }}</p>
                                @endif
                                @if ($reeval['justification'] !== '')
                                    <p class="mt-1 text-[12px] text-violet-900/90">{{ $reeval['justification'] }}</p>
                                @endif
                            </div>
                            @if (! empty($reeval['can_decide']))
                                <div class="flex flex-wrap gap-2 shrink-0">
                                    <button type="button"
                                            wire:click="approveRevaluation({{ $reeval['id'] }})"
                                            wire:loading.attr="disabled"
                                            wire:target="approveRevaluation({{ $reeval['id'] }})"
                                            class="btn btn-success btn-sm">
                                        <span wire:loading wire:target="approveRevaluation({{ $reeval['id'] }})" class="spinner-border spinner-border-sm" role="status"></span>
                                        {{ __('ui.show.reeval_approve') }}
                                    </button>
                                    <button type="button"
                                            wire:click="rejectRevaluation({{ $reeval['id'] }})"
                                            wire:loading.attr="disabled"
                                            wire:target="rejectRevaluation({{ $reeval['id'] }})"
                                            wire:confirm="{{ __('ui.show.reeval_reject_confirm') }}"
                                            class="btn btn-outline-danger btn-sm">
                                        {{ __('ui.show.reeval_reject') }}
                                    </button>
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    @if (! empty($p['can_arbitrate']))
        <div class="mb-6 rounded-xl border border-amber-200 bg-amber-50/80 px-5 py-4 shadow-sm">
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div>
                    <p class="text-[13px] font-semibold text-amber-900">{{ __('ui.show.arbitration_pending') }}</p>
                    <p class="mt-0.5 text-[12px] text-amber-800/80">{{ __('ui.show.arbitration_pending_hint', ['by' => $p['requester_scope'] ?? 'DASS']) }}</p>
                </div>
                <div class="flex flex-wrap gap-2">
                    <button type="button" wire:click="acceptArbitration" wire:loading.attr="disabled" wire:target="acceptArbitration"
                            class="btn btn-success btn-sm" style="display: inline-flex; align-items: center; gap: 0.25rem;">
                        <span wire:loading wire:target="acceptArbitration" class="spinner-border spinner-border-sm" role="status"></span>
                        {{ __('ui.show.arbitration_accept') }}
                    </button>
                    <button type="button" wire:click="openRejectModal"
                            class="btn btn-outline-danger btn-sm" style="display: inline-flex; align-items: center; gap: 0.25rem;">
                        {{ __('ui.show.arbitration_decline') }}
                    </button>
                </div>
            </div>

            @if ($showRejectModal)
                @include('livewire.dashboard.partials.workflow-reject-form')
            @endif
        </div>
    @endif

    @if (! empty($p['can_visa']))
        <div class="mb-6 rounded-xl border border-sky-200 bg-sky-50/80 px-5 py-4 shadow-sm">
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div>
                    <p class="text-[13px] font-semibold text-sky-900">{{ __('ui.show.visa_pending') }}</p>
                    <p class="mt-0.5 text-[12px] text-sky-800/80">{{ __('ui.show.visa_pending_hint', ['by' => $p['requester_scope'] ?? 'DASS']) }}</p>
                </div>
                <div class="flex flex-wrap gap-2">
                    <button type="button" wire:click="acceptVisa" wire:loading.attr="disabled" wire:target="acceptVisa"
                            class="btn btn-success btn-sm" style="display: inline-flex; align-items: center; gap: 0.25rem;">
                        <span wire:loading wire:target="acceptVisa" class="spinner-border spinner-border-sm" role="status"></span>
                        {{ __('ui.show.visa_accept') }}
                    </button>
                    <button type="button" wire:click="openRejectModal"
                            class="btn btn-outline-danger btn-sm" style="display: inline-flex; align-items: center; gap: 0.25rem;">
                        {{ __('ui.show.visa_decline') }}
                    </button>
                </div>
            </div>

            @if ($showRejectModal)
                @include('livewire.dashboard.partials.workflow-reject-form')
            @endif
        </div>
    @endif

    @if (! empty($p['can_engage_ae']))
        <div class="mb-6 rounded-xl border border-emerald-200 bg-emerald-50/80 px-5 py-4 shadow-sm">
            <div class="mb-4">
                <p class="text-[13px] font-semibold text-emerald-900">{{ __('ui.show.ae_engage_title') }}</p>
                <p class="mt-0.5 text-[12px] text-emerald-800/80">
                    {{ ($p['status'] ?? '') === 'approved'
                        ? __('ui.show.ae_engage_hint_awaiting')
                        : __('ui.show.ae_engage_hint') }}
                </p>
                <p class="mt-2 text-[11px] text-emerald-900/70">
                    {{ __('ui.show.ae_engage_summary', [
                        'recorded' => number_format($p['recorded_engagements_da'] ?? 0, 2, ',', ' '),
                        'ceiling' => number_format($p['ap_actuelle_da'] ?? 0, 2, ',', ' '),
                        'reliquat' => number_format($p['reliquat_ae_da'] ?? 0, 2, ',', ' '),
                    ]) }}
                </p>
            </div>

            @if (! empty($p['ae_engagements']))
                <div class="mb-4 overflow-hidden rounded-lg border border-emerald-200/80 bg-white/70">
                    <table class="w-full text-left text-[12px]">
                        <thead class="bg-emerald-100/50 text-[10px] uppercase tracking-wider text-emerald-900/70">
                            <tr>
                                <th class="px-3 py-2 font-semibold">{{ __('ui.show.ae_engage_date') }}</th>
                                <th class="px-3 py-2 font-semibold">{{ __('ui.show.ae_engage_amount') }}</th>
                                <th class="px-3 py-2 font-semibold">{{ __('ui.show.ae_engage_description') }}</th>
                                <th class="px-3 py-2 font-semibold">{{ __('ui.show.ae_engage_by') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($p['ae_engagements'] as $eng)
                                <tr class="border-t border-emerald-100">
                                    <td class="px-3 py-2 whitespace-nowrap">{{ $eng['engaged_at'] }}</td>
                                    <td class="px-3 py-2 font-mono font-semibold">{{ number_format($eng['amount_da'], 2, ',', ' ') }}</td>
                                    <td class="px-3 py-2">{{ $eng['description'] !== '' ? $eng['description'] : '—' }}</td>
                                    <td class="px-3 py-2">{{ $eng['created_by'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="mb-4 text-[12px] text-emerald-800/70">{{ __('ui.show.ae_engage_empty') }}</p>
            @endif

            <div class="grid gap-3 sm:grid-cols-3">
                <div>
                    <label class="mb-1 block text-[10px] font-semibold uppercase tracking-wider text-emerald-900/70">{{ __('ui.show.ae_engage_amount') }}</label>
                    <input type="number" step="0.01" min="1" wire:model="engageAmount" class="form-control form-control-sm" placeholder="0.00">
                    @error('engageAmount') <p class="mt-1 text-[11px] text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="mb-1 block text-[10px] font-semibold uppercase tracking-wider text-emerald-900/70">{{ __('ui.show.ae_engage_date') }}</label>
                    <input type="date" wire:model="engageDate" class="form-control form-control-sm">
                    @error('engageDate') <p class="mt-1 text-[11px] text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="mb-1 block text-[10px] font-semibold uppercase tracking-wider text-emerald-900/70">{{ __('ui.show.ae_engage_description') }}</label>
                    <input type="text" wire:model="engageDescription" class="form-control form-control-sm" placeholder="{{ __('ui.show.ae_engage_description_ph') }}">
                    @error('engageDescription') <p class="mt-1 text-[11px] text-red-600">{{ $message }}</p> @enderror
                </div>
            </div>
            <div class="mt-3 flex justify-end">
                <button type="button" wire:click="engageAe" wire:loading.attr="disabled" wire:target="engageAe"
                        class="btn btn-success btn-sm" style="display: inline-flex; align-items: center; gap: 0.25rem;">
                    <span wire:loading wire:target="engageAe" class="spinner-border spinner-border-sm" role="status"></span>
                    {{ __('ui.show.ae_engage_submit') }}
                </button>
            </div>
        </div>
    @elseif (! empty($p['ae_engagements']))
        <div class="mb-6 rounded-xl border border-[var(--ds-border)] bg-white px-5 py-4 shadow-sm">
            <p class="mb-3 text-[13px] font-semibold text-[var(--ds-text)]">{{ __('ui.show.ae_engage_list_title') }}</p>
            <div class="overflow-hidden rounded-lg border border-[var(--ds-border)]">
                <table class="w-full text-left text-[12px]">
                    <thead class="bg-[var(--ds-surface-sunken)] text-[10px] uppercase tracking-wider text-[var(--ds-text-faint)]">
                        <tr>
                            <th class="px-3 py-2 font-semibold">{{ __('ui.show.ae_engage_date') }}</th>
                            <th class="px-3 py-2 font-semibold">{{ __('ui.show.ae_engage_amount') }}</th>
                            <th class="px-3 py-2 font-semibold">{{ __('ui.show.ae_engage_description') }}</th>
                            <th class="px-3 py-2 font-semibold">{{ __('ui.show.ae_engage_by') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($p['ae_engagements'] as $eng)
                            <tr class="border-t border-[var(--ds-border)]">
                                <td class="px-3 py-2 whitespace-nowrap">{{ $eng['engaged_at'] }}</td>
                                <td class="px-3 py-2 font-mono font-semibold">{{ number_format($eng['amount_da'], 2, ',', ' ') }}</td>
                                <td class="px-3 py-2">{{ $eng['description'] !== '' ? $eng['description'] : '—' }}</td>
                                <td class="px-3 py-2">{{ $eng['created_by'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    {{-- Summary strip — Hero banner --}}
    <div class="mb-8 overflow-hidden rounded-2xl border border-[var(--ds-border-subtle)] bg-gradient-to-r from-[var(--ds-surface)] via-[var(--ds-surface)] to-[var(--ds-surface-sunken)] shadow-sm">
        <div class="flex flex-wrap items-center gap-x-4 gap-y-2.5 px-6 py-4">
            <div class="flex items-center gap-2.5">
                <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-[var(--ds-primary)]/10 text-[var(--ds-primary)]">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25z" /></svg>
                </div>
                <div>
                    <p class="text-[10px] font-semibold uppercase tracking-wider text-[var(--ds-text-faint)]">FIP</p>
                    <p class="font-mono text-[14px] font-bold text-[var(--ds-text)]">{{ $p['id'] }}</p>
                </div>
            </div>
            <div class="hidden h-8 w-px bg-[var(--ds-border-subtle)] lg:block"></div>
            <div class="flex items-center gap-2.5">
                <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-violet-50 text-violet-600">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z" /></svg>
                </div>
                <div>
                    <p class="text-[10px] font-semibold uppercase tracking-wider text-[var(--ds-text-faint)]">{{ __('ui.projects.programme') }}</p>
                    <p class="text-[13px] font-semibold text-[var(--ds-text)]">{{ $p['program'] }}</p>
                </div>
            </div>
            <div class="hidden h-8 w-px bg-[var(--ds-border-subtle)] lg:block"></div>
            <div class="flex items-center gap-2.5">
                <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-amber-50 text-amber-600">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" /></svg>
                </div>
                <div>
                    <p class="text-[10px] font-semibold uppercase tracking-wider text-[var(--ds-text-faint)]">{{ __('ui.show.location') }}</p>
                    <p class="text-[13px] font-semibold text-[var(--ds-text)]">{{ $p['wilaya'] }}@if ($p['commune']) <span class="font-normal text-[var(--ds-text-muted)]">· {{ $p['commune'] }}</span>@endif</p>
                </div>
            </div>
            <div class="ml-auto flex flex-wrap items-center gap-2">
                <x-dashboard.ui-badge :variant="$statusBadge['variant']">{{ $statusBadge['label'] }}</x-dashboard.ui-badge>
                @if ($p['budget_type'])
                    <x-dashboard.ui-badge>{{ $p['budget_type'] }}</x-dashboard.ui-badge>
                @endif
            </div>
        </div>
    </div>

    <div class="space-y-6">
        {{-- FIP identity --}}
        <div class="ds-card overflow-hidden">
            <div class="relative">
                <div class="absolute left-0 top-0 bottom-0 w-1 bg-[var(--ds-primary)]/30"></div>
                <div class="ds-card-header pl-5">
                    <h2 class="text-[13px] font-semibold text-[var(--ds-text)]">{{ __('ui.show.identity') }}</h2>
                    <p class="text-[11px] text-[var(--ds-text-faint)]">{{ __('ui.show.identity_hint') }}</p>
                </div>
            </div>
            <div class="ds-card-body">
                <div class="grid gap-x-6 gap-y-4 sm:grid-cols-2">
                    <div class="rounded-lg bg-[var(--ds-surface-sunken)] px-4 py-3">
                        <p class="text-[10px] font-semibold uppercase tracking-wider text-[var(--ds-text-faint)]">Title (French)</p>
                        <p class="mt-1 text-[13px] font-medium text-[var(--ds-text)]">{{ $p['title_fr'] }}</p>
                    </div>
                    <div class="rounded-lg bg-[var(--ds-surface-sunken)] px-4 py-3">
                        <p class="text-[10px] font-semibold uppercase tracking-wider text-[var(--ds-text-faint)]">Title (Arabic)</p>
                        <p class="mt-1 text-right text-[13px] font-medium text-[var(--ds-text)]" dir="rtl" lang="ar">{{ $p['title_ar'] ?: '—' }}</p>
                    </div>
                    <div class="rounded-lg bg-[var(--ds-surface-sunken)] px-4 py-3">
                        <p class="text-[10px] font-semibold uppercase tracking-wider text-[var(--ds-text-faint)]">Project owner</p>
                        <p class="mt-1 text-[13px] font-medium text-[var(--ds-text)]">{{ $p['project_owner'] }}</p>
                    </div>
                    <div class="rounded-lg bg-[var(--ds-surface-sunken)] px-4 py-3">
                        <p class="text-[10px] font-semibold uppercase tracking-wider text-[var(--ds-text-faint)]">Target deadline</p>
                        <p class="mt-1 text-[13px] font-medium text-[var(--ds-text)]">
                            {{ $p['deadline'] ? \Illuminate\Support\Carbon::parse($p['deadline'])->locale('en')->translatedFormat('d M Y') : '—' }}
                        </p>
                    </div>
                    @if ($p['description'])
                        <div class="sm:col-span-2">
                            <p class="text-[10px] font-semibold uppercase tracking-wider text-[var(--ds-text-faint)]">Description</p>
                            <p class="mt-1 text-[13px] leading-relaxed text-[var(--ds-text-muted)]">{{ $p['description'] }}</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Budget Classification --}}
        <div class="ds-card overflow-hidden">
            <div class="relative">
                <div class="absolute left-0 top-0 bottom-0 w-1 bg-amber-400"></div>
                <div class="ds-card-header pl-5">
                    <h2 class="text-[13px] font-semibold text-[var(--ds-text)]">{{ __('ui.show.budget_class') }}</h2>
                    <p class="text-[11px] text-[var(--ds-text-faint)]">{{ __('ui.show.budget_hint') }}</p>
                </div>
            </div>
            <div class="ds-card-body">
                <div class="grid gap-x-6 gap-y-4 sm:grid-cols-2 lg:grid-cols-4">
                    <div class="rounded-lg border border-amber-100 bg-amber-50/40 px-4 py-3">
                        <p class="text-[10px] font-semibold uppercase tracking-wider text-amber-800">Category</p>
                        <p class="mt-1 text-[13px] font-bold text-amber-900">{{ $p['nc13_category'] ?: '—' }}</p>
                    </div>
                    <div class="rounded-lg border border-amber-100 bg-amber-50/40 px-4 py-3">
                        <p class="text-[10px] font-semibold uppercase tracking-wider text-amber-800">NC13 Code</p>
                        <p class="mt-1 text-[13px] font-bold text-amber-900">
                            @if ($p['nc13_code'])
                                {{ $p['nc13_code'] }} — {{ $p['nc13_label'] }}
                            @else
                                <span class="font-normal text-amber-700/60">—</span>
                            @endif
                        </p>
                    </div>
                    <div class="rounded-lg border border-amber-100 bg-amber-50/40 px-4 py-3">
                        <p class="text-[10px] font-semibold uppercase tracking-wider text-amber-800">Class</p>
                        <p class="mt-1 flex items-center gap-1.5 text-[13px] font-bold text-amber-900">
                            @if ($p['nc13_class_label'])
                                <span>{{ $p['nc13_class_label'] }}</span>
                                <svg class="h-4 w-4 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                            @else
                                <span class="font-normal text-amber-700/60">—</span>
                            @endif
                        </p>
                    </div>
                    <div class="rounded-lg border border-amber-100 bg-amber-50/40 px-4 py-3">
                        <p class="text-[10px] font-semibold uppercase tracking-wider text-amber-800">Budget Type</p>
                        <p class="mt-1 text-[13px] font-bold text-amber-900">{{ $p['budget_type'] ?: '—' }}</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Maturation checklist --}}
        <div class="ds-card overflow-hidden">
            <div class="relative">
                <div class="absolute left-0 top-0 bottom-0 w-1 bg-emerald-400"></div>
                <div class="ds-card-header pl-5">
                    <h2 class="text-[13px] font-semibold text-[var(--ds-text)]">{{ __('ui.show.checklist') }}</h2>
                    <p class="text-[11px] text-[var(--ds-text-faint)]">{{ __('ui.show.checklist_hint') }}</p>
                </div>
            </div>
            <div class="ds-card-body">
                <div class="grid gap-3 sm:grid-cols-2">
                    @foreach ([
                        ['key' => 'land', 'label' => 'Land / footprint secured'],
                        ['key' => 'studies', 'label' => 'Studies (APS / APD)'],
                        ['key' => 'quote', 'label' => 'Estimates / budget'],
                        ['key' => 'technical_opinion', 'label' => 'Technical opinion'],
                    ] as $item)
                        @php $checked = $p['maturation_' . $item['key']] ?? false; @endphp
                        <div class="flex items-center justify-between rounded-xl border px-4 py-3.5 @if($checked) border-emerald-200 bg-emerald-50/60 @else border-[var(--ds-border-subtle)] bg-[var(--ds-surface-sunken)] @endif">
                            <span class="text-[13px] font-medium text-[var(--ds-text-secondary)]">{{ $item['label'] }}</span>
                            @if ($checked)
                                <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-100 px-2.5 py-1 text-[11px] font-semibold text-emerald-800">
                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                    Confirmed
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 rounded-full bg-slate-100 px-2.5 py-1 text-[11px] font-medium text-slate-500">
                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                    Pending
                                </span>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Financial envelope --}}
        <div class="ds-card overflow-hidden">
            <div class="relative">
                <div class="absolute left-0 top-0 bottom-0 w-1 bg-indigo-400"></div>
                <div class="ds-card-header pl-5">
                    <h2 class="text-[13px] font-semibold text-[var(--ds-text)]">{{ __('ui.show.envelope') }}</h2>
                    <p class="text-[11px] text-[var(--ds-text-faint)]">{{ __('ui.show.envelope_hint') }}</p>
                </div>
            </div>
            <div class="ds-card-body space-y-5">
                <div class="grid gap-4 sm:grid-cols-3">
                    <div class="rounded-xl border border-emerald-200 bg-gradient-to-br from-emerald-50 to-white px-5 py-4 shadow-sm">
                        <p class="text-[10px] font-bold uppercase tracking-wider text-emerald-700">AP actuelle</p>
                        <p class="mt-1.5 text-2xl font-bold text-emerald-800">{{ \App\Support\Money::da($p['ap_actuelle_da'], 0) }} <span class="text-sm font-semibold">DA</span></p>
                        <p class="mt-0.5 text-[11px] text-emerald-600/70">AP initiale {{ \App\Support\Money::da($p['ap_initiale_da'], 0) }} + réévaluations {{ \App\Support\Money::da($p['total_reevaluations_da'], 0) }}</p>
                    </div>
                    <div class="rounded-xl border border-indigo-200 bg-gradient-to-br from-indigo-50 to-white px-5 py-4 shadow-sm">
                        <p class="text-[10px] font-bold uppercase tracking-wider text-indigo-700">CP notifiés</p>
                        <p class="mt-1.5 text-2xl font-bold text-indigo-800">{{ \App\Support\Money::da($p['cp_da'], 0) }} <span class="text-sm font-semibold">DA</span></p>
                        <p class="mt-0.5 text-[11px] text-indigo-600/70">Plafond de paiement</p>
                    </div>
                    <div class="rounded-xl border border-amber-200 bg-gradient-to-br from-amber-50 to-white px-5 py-4 shadow-sm">
                        <p class="text-[10px] font-bold uppercase tracking-wider text-amber-700">Reste à réaliser</p>
                        <p class="mt-1.5 text-2xl font-bold text-amber-800">{{ \App\Support\Money::da($p['rar_da'], 0) }} <span class="text-sm font-semibold">DA</span></p>
                        <p class="mt-0.5 text-[11px] text-amber-600/70">Programme en cours {{ \App\Support\Money::da($p['pec_da'], 0) }}</p>
                    </div>
                </div>

                <div>
                    <p class="mb-3 text-[11px] font-semibold uppercase tracking-wider text-[var(--ds-text-muted)]">CP projection by year</p>
                    <div class="grid gap-3 sm:grid-cols-3">
                        @foreach ([['Année N', 'cp_year_n'], ['N+1', 'cp_year_n1'], ['N+2', 'cp_year_n2']] as [$label, $key])
                            <div class="rounded-xl border border-[var(--ds-border-subtle)] bg-[var(--ds-surface)] px-5 py-3.5 shadow-sm">
                                <p class="text-[11px] font-medium text-[var(--ds-text-muted)]">{{ $label }}</p>
                                <p class="mt-1 text-xl font-bold text-[var(--ds-text)]">{{ \App\Support\Money::da($p[$key], 0) }} <span class="text-xs font-semibold text-[var(--ds-text-muted)]">DA</span></p>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        {{-- Physical Progress / Situation physique --}}
        @php
            $accepted = array_filter($p['physical_progress'], fn ($pp) => ($pp['status'] ?? 'pending') === 'accepted');
            $maxPct = count($accepted) > 0 ? max(array_column($accepted, 'progress_pct')) : 0;
            $ppEntries = $p['physical_progress'];
        @endphp
        <div class="ds-card" style="overflow: visible;">
            <div class="relative">
                <div class="absolute left-0 top-0 bottom-0 w-1 bg-emerald-400"></div>
                <div class="ds-card-header pl-5">
                    <h2 class="text-[13px] font-semibold text-[var(--ds-text)]">{{ __('ui.show.physical') }}</h2>
                    <p class="text-[11px] text-[var(--ds-text-faint)]">{{ __('ui.show.physical_hint') }}</p>
                </div>
            </div>
            <div class="ds-card-body space-y-4">
                @if (count($ppEntries) === 0)
                    <p class="text-[12px] text-[var(--ds-text-faint)]">{{ __('ui.show.situation_empty') }}</p>
                    @if (! empty($p['can_report_situation']))
                        <p class="text-[12px] text-[var(--ds-text-secondary)]">
                            <a href="{{ route('dashbored.projects.fiche', $p['id']) }}?tab=physique" class="font-semibold text-[var(--ds-primary)] hover:underline" wire:navigate>
                                {{ __('ui.show.situation_go_fiche') }}
                            </a>
                        </p>
                    @endif
                @else
                    <div class="flex flex-wrap items-center gap-6"
                         x-data="{
                             pct: {{ $maxPct }},
                             circumference: 2 * Math.PI * 36,
                             get offset() { return this.circumference - (this.pct / 100) * this.circumference; }
                         }">
                        <div class="relative flex-shrink-0">
                            <svg width="100" height="100" viewBox="0 0 100 100" class="-rotate-90">
                                <defs>
                                    <linearGradient id="ring-grad" x1="0" y1="0" x2="1" y2="1">
                                        <stop offset="0%" stop-color="#34d399" />
                                        <stop offset="100%" stop-color="#059669" />
                                    </linearGradient>
                                </defs>
                                <circle cx="50" cy="50" r="36" fill="none" stroke="var(--ds-border-subtle)" stroke-width="8" />
                                <circle cx="50" cy="50" r="36" fill="none" stroke="url(#ring-grad)" stroke-width="8"
                                        stroke-linecap="round"
                                        :stroke-dasharray="circumference"
                                        :stroke-dashoffset="offset"
                                        class="transition-all duration-700 ease-out" />
                            </svg>
                            <div class="absolute inset-0 flex items-center justify-center">
                                <span class="text-lg font-bold text-emerald-700" x-text="pct + '%'"></span>
                            </div>
                        </div>
                        <div class="flex-1 min-w-[200px]">
                            <p class="text-[11px] text-[var(--ds-text-secondary)]">{{ __('ui.show.situation_accepted_max', ['pct' => $maxPct]) }}</p>
                        </div>
                    </div>

                    <div class="overflow-hidden rounded-lg border border-[var(--ds-border)]">
                        <table class="w-full text-left text-[12px]">
                            <thead class="bg-[var(--ds-surface-sunken)] text-[10px] uppercase tracking-wider text-[var(--ds-text-faint)]">
                                <tr>
                                    <th class="px-3 py-2">{{ __('ui.show.situation_title') }}</th>
                                    <th class="px-3 py-2">%</th>
                                    <th class="px-3 py-2">{{ __('ui.show.situation_by') }}</th>
                                    <th class="px-3 py-2">{{ __('ui.projects.status') }}</th>
                                    <th class="px-3 py-2"></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($ppEntries as $pp)
                                    <tr class="border-t border-[var(--ds-border)]">
                                        <td class="px-3 py-2 font-medium">{{ $pp['title'] }}</td>
                                        <td class="px-3 py-2 font-mono">{{ $pp['progress_pct'] }}%</td>
                                        <td class="px-3 py-2 text-[var(--ds-text-muted)]">{{ $pp['added_by'] }} · {{ $pp['added_at'] }}</td>
                                        <td class="px-3 py-2">
                                            <span class="inline-block rounded-full px-2 py-0.5 text-[10px] font-semibold uppercase
                                                {{ $pp['status'] === 'accepted' ? 'bg-emerald-100 text-emerald-700' : ($pp['status'] === 'declined' ? 'bg-red-100 text-red-600' : 'bg-amber-100 text-amber-700') }}">
                                                {{ $pp['status'] }}
                                            </span>
                                        </td>
                                        <td class="px-3 py-2 text-right">
                                            @if (! empty($p['can_validate_situation']) && $pp['status'] === 'pending')
                                                <button type="button" wire:click="acceptProgress({{ $pp['id'] }})" class="btn btn-success btn-sm">{{ __('ui.show.situation_accept') }}</button>
                                                <button type="button" wire:click="declineProgress({{ $pp['id'] }})" class="btn btn-outline-danger btn-sm">{{ __('ui.show.situation_decline') }}</button>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @if (! empty($p['can_report_situation']))
                        <p class="text-[12px] text-[var(--ds-text-secondary)]">
                            <a href="{{ route('dashbored.projects.fiche', $p['id']) }}?tab=physique" class="font-semibold text-[var(--ds-primary)] hover:underline" wire:navigate>
                                {{ __('ui.show.situation_go_fiche') }}
                            </a>
                        </p>
                    @endif
                @endif
            </div>
        </div>

        {{-- Financial Progress (validated CP vs notified CP) --}}
        @php $fin = $p; @endphp
        @if ($fin['cp_notified_da'] > 0 && count($fin['cp_validated_payments']) > 0)
        <div class="ds-card" style="overflow: visible;">
            <div class="relative">
                <div class="absolute left-0 top-0 bottom-0 w-1 bg-indigo-400"></div>
                <div class="ds-card-header pl-5">
                    <h2 class="text-[13px] font-semibold text-[var(--ds-text)]">{{ __('ui.show.financial_progress') }}</h2>
                    <p class="text-[11px] text-[var(--ds-text-faint)]">{{ count($fin['cp_validated_payments']) }} mandatement(s) validé(s) — {{ \App\Support\Money::daWithUnit($fin['cp_validated_da'], 0) }} sur {{ \App\Support\Money::daWithUnit($fin['cp_notified_da'], 0) }}</p>
                </div>
            </div>
            <div class="ds-card-body">
                <div class="flex flex-wrap items-center gap-6"
                     x-data="{
                         pct: {{ $fin['cp_financial_pct'] }},
                         circumference: 2 * Math.PI * 36,
                         get offset() { return this.circumference - (this.pct / 100) * this.circumference; }
                     }">
                    {{-- Donut ring --}}
                    <div class="relative flex-shrink-0">
                        <svg width="100" height="100" viewBox="0 0 100 100" class="-rotate-90">
                            <defs>
                                <linearGradient id="cp-ring-grad" x1="0" y1="0" x2="1" y2="1">
                                    <stop offset="0%" stop-color="#34d399" />
                                    <stop offset="100%" stop-color="#059669" />
                                </linearGradient>
                            </defs>
                            <circle cx="50" cy="50" r="36" fill="none" stroke="var(--ds-border-subtle)" stroke-width="8" />
                            <circle cx="50" cy="50" r="36" fill="none" stroke="url(#cp-ring-grad)" stroke-width="8"
                                    stroke-linecap="round"
                                    :stroke-dasharray="circumference"
                                    :stroke-dashoffset="offset"
                                    class="transition-all duration-700 ease-out" />
                        </svg>
                        <div class="absolute inset-0 flex items-center justify-center">
                            <span class="text-lg font-bold text-emerald-700" x-text="pct + '%'"></span>
                        </div>
                    </div>

                    {{-- Bar with payment markers --}}
                    <div class="flex-1 min-w-[200px] space-y-3">
                        <div class="flex items-center justify-between text-[11px] text-[var(--ds-text-secondary)]">
                            <span>CP consumption</span>
                            <span class="font-bold text-emerald-700">{{ $fin['cp_financial_pct'] }}%</span>
                        </div>
                        <div class="relative h-6 flex items-center">
                            <div class="absolute inset-x-0 h-3 rounded-full bg-[var(--ds-border-subtle)]"></div>
                            <div class="absolute left-0 h-3 rounded-full bg-gradient-to-r from-emerald-400 to-emerald-600 transition-all duration-700" style="width: {{ min(100, $fin['cp_financial_pct']) }}%"></div>
                            @foreach ($fin['cp_validated_payments'] as $pm)
                                @php $left = min(100, max(0, $pm['pct'])); @endphp
                                <div class="absolute top-1/2 -translate-y-1/2 -translate-x-1/2 z-10"
                                     style="left: {{ $left }}%;"
                                     onmouseover="this.querySelector('.cp-fin-tip').style.display='block'"
                                     onmouseout="this.querySelector('.cp-fin-tip').style.display='none'">
                                    <div class="h-4 w-4 rounded-full border-2 border-white bg-indigo-500 shadow-md cursor-pointer transition-transform hover:scale-125"></div>
                                    <div class="cp-fin-tip" style="display:none; position:absolute; bottom:100%; left:50%; transform:translateX(-50%); margin-bottom:0.5rem; width:max-content; max-width:220px; z-index:20; border:1px solid var(--ds-border-subtle); border-radius:0.5rem; background:var(--ds-surface-elevated); padding:0.5rem 0.75rem; box-shadow:0 4px 16px rgba(15,23,42,0.15); white-space:normal;">
                                        <p style="font-weight:600; font-size:0.8125rem; color:var(--ds-text); margin:0 0 0.125rem;">{{ \App\Support\Money::daWithUnit($pm['amount_da'], 0) }}</p>
                                        <p style="font-size:0.6875rem; color:var(--ds-text-secondary); margin:0 0 0.125rem;">{{ $pm['pct'] }}% of notified CP</p>
                                        @if ($pm['payment_type'])
                                            <p style="font-size:0.6875rem; color:var(--ds-text-muted); margin:0 0 0.125rem;">Type: {{ ucfirst(str_replace('_', ' ', $pm['payment_type'])) }}</p>
                                        @endif
                                        @if ($pm['mandate_number'])
                                            <p style="font-size:0.6875rem; color:var(--ds-text-muted); margin:0 0 0.125rem;">Mandate: {{ $pm['mandate_number'] }}</p>
                                        @endif
                                        @if ($pm['paid_at'])
                                            <p style="font-size:0.6875rem; color:var(--ds-text-faint); margin:0 0 0.125rem;">Paid: {{ $pm['paid_at'] }}</p>
                                        @endif
                                        <p style="font-size:0.6875rem; color:var(--ds-text-faint); margin:0;">Created: {{ $pm['created_at'] }}</p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <div class="flex justify-between text-[9px] text-[var(--ds-text-faint)]">
                            <span>0%</span>
                            <span>50%</span>
                            <span>100%</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif

        {{-- Contract & contractor --}}
        @if ($p['company_name'] || $p['amount_committed_da'] > 0)
        <div class="ds-card overflow-hidden">
            <div class="relative">
                <div class="absolute left-0 top-0 bottom-0 w-1 bg-slate-400"></div>
                <div class="ds-card-header pl-5">
                    <h2 class="text-[13px] font-semibold text-[var(--ds-text)]">{{ __('ui.show.contract') }}</h2>
                    <p class="text-[11px] text-[var(--ds-text-faint)]">{{ __('ui.show.contract_hint') }}</p>
                </div>
            </div>
            <div class="ds-card-body">
                <div class="grid gap-x-6 gap-y-4 sm:grid-cols-2">
                    <div class="rounded-lg bg-[var(--ds-surface-sunken)] px-4 py-3">
                        <p class="text-[10px] font-semibold uppercase tracking-wider text-[var(--ds-text-faint)]">Company</p>
                        <p class="mt-1 text-[13px] font-medium text-[var(--ds-text)]">{{ $p['company_name'] ?: '—' }}</p>
                    </div>
                    <div class="rounded-lg bg-[var(--ds-surface-sunken)] px-4 py-3">
                        <p class="text-[10px] font-semibold uppercase tracking-wider text-[var(--ds-text-faint)]">NIF</p>
                        <p class="mt-1 font-mono text-[13px] text-[var(--ds-text-secondary)]">{{ $p['nif'] ?: '—' }}</p>
                    </div>
                    <div class="rounded-lg bg-[var(--ds-surface-sunken)] px-4 py-3">
                        <p class="text-[10px] font-semibold uppercase tracking-wider text-[var(--ds-text-faint)]">Business register</p>
                        <p class="mt-1 font-mono text-[13px] text-[var(--ds-text-secondary)]">{{ $p['business_register'] ?: '—' }}</p>
                    </div>
                    <div class="rounded-lg bg-[var(--ds-surface-sunken)] px-4 py-3">
                        <p class="text-[10px] font-semibold uppercase tracking-wider text-[var(--ds-text-faint)]">Committed amount</p>
                        <p class="mt-1 text-[13px] font-semibold text-[var(--ds-text)]">{{ $p['amount_committed_da'] > 0 ? \App\Support\Money::daWithUnit($p['amount_committed_da'], 0) : '—' }}</p>
                    </div>
                    <div class="rounded-lg bg-[var(--ds-surface-sunken)] px-4 py-3">
                        <p class="text-[10px] font-semibold uppercase tracking-wider text-[var(--ds-text-faint)]">Commencement order (ODS)</p>
                        <p class="mt-1 text-[13px] text-[var(--ds-text-secondary)]">
                            {{ $p['ods_date'] ? \Illuminate\Support\Carbon::parse($p['ods_date'])->locale('en')->translatedFormat('d M Y') : '—' }}
                        </p>
                    </div>
                    <div class="rounded-lg bg-[var(--ds-surface-sunken)] px-4 py-3">
                        <p class="text-[10px] font-semibold uppercase tracking-wider text-[var(--ds-text-faint)]">Contract duration</p>
                        <p class="mt-1 text-[13px] text-[var(--ds-text-secondary)]">{{ $p['project_duration_months'] ? $p['project_duration_months'] . ' months' : '—' }}</p>
                    </div>
                </div>
            </div>
        </div>
        @endif

        {{-- Risk files --}}
        @if (count($p['risk_files']) > 0)
        <div class="ds-card overflow-hidden">
            <div class="relative">
                <div class="absolute left-0 top-0 bottom-0 w-1 bg-red-400"></div>
                <div class="ds-card-header pl-5">
                    <h2 class="text-[13px] font-semibold text-[var(--ds-text)]">{{ __('ui.show.risks') }}</h2>
                    <p class="text-[11px] text-[var(--ds-text-faint)]">{{ count($p['risk_files']) }} file(s) attached</p>
                </div>
            </div>
            <div class="ds-card-body space-y-3">
                @foreach ($p['risk_files'] as $rf)
                    <div class="flex items-start gap-4 rounded-xl border border-[var(--ds-border-subtle)] bg-[var(--ds-surface-sunken)] p-4 transition hover:border-[var(--ds-primary)]/20 hover:shadow-sm">
                        @if ($rf['url'])
                            @php $isImage = preg_match('/\.(jpg|jpeg|png|gif|webp|svg)$/i', $rf['file']); @endphp
                            @if ($isImage)
                                <a href="{{ $rf['url'] }}" target="_blank" class="shrink-0">
                                    <img src="{{ $rf['url'] }}" class="h-16 w-16 rounded-xl border border-[var(--ds-border-subtle)] object-cover shadow-sm" alt="Risk file">
                                </a>
                            @else
                                <a href="{{ $rf['url'] }}" target="_blank" class="flex h-16 w-16 shrink-0 items-center justify-center rounded-xl border border-[var(--ds-border-subtle)] bg-white shadow-sm">
                                    <svg class="h-7 w-7 text-[var(--ds-text-faint)]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" /></svg>
                                </a>
                            @endif
                        @endif
                        <div class="min-w-0 flex-1">
                            @if ($rf['url'])
                                <a href="{{ $rf['url'] }}" target="_blank" class="text-[13px] font-semibold text-[var(--ds-primary)] hover:underline">{{ basename($rf['file']) }}</a>
                            @else
                                <p class="text-[13px] font-medium text-[var(--ds-text)]">{{ basename($rf['file']) }}</p>
                            @endif
                            @if ($rf['notes'])
                                <p class="mt-1 text-[11px] leading-relaxed text-[var(--ds-text-muted)]">{{ $rf['notes'] }}</p>
                            @endif
                            @if ($rf['url'])
                                <a href="{{ $rf['url'] }}" target="_blank" class="mt-1.5 inline-flex items-center gap-1 text-[11px] font-medium text-[var(--ds-primary)]/70 hover:text-[var(--ds-primary)]">
                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" /></svg>
                                    Download
                                </a>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
        @endif

        @if (count($p['audit_trail']) > 0)
        <div class="ds-card overflow-hidden">
            <div class="ds-card-header">
                <h2 class="text-[13px] font-semibold text-[var(--ds-text)]">{{ __('ui.show.journal') }}</h2>
                <p class="text-[11px] text-[var(--ds-text-faint)]">{{ __('ui.show.journal_hint') }}</p>
            </div>
            <div class="divide-y divide-[var(--ds-border-subtle)]">
                @foreach ($p['audit_trail'] as $row)
                    <div class="flex items-start gap-3 px-5 py-3">
                        <div class="min-w-0 flex-1">
                            <p class="text-[13px] font-medium text-[var(--ds-text)]">{{ $row['action'] }}</p>
                            <p class="text-[11px] text-[var(--ds-text-faint)]">{{ $row['user'] }}</p>
                        </div>
                        <span class="shrink-0 text-[10px] text-[var(--ds-text-faint)]">{{ $row['at'] }}</span>
                    </div>
                @endforeach
            </div>
        </div>
        @endif

    </div>
</div>
