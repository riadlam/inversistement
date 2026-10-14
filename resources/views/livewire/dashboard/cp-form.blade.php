<div class="ds-page-enter max-w-4xl">
    <x-dashboard.page-header :title="__('ui.cp.title')" :subtitle="__('ui.cp.subtitle')">
        <x-slot:actions>
            <a href="{{ route('dashbored.projects') }}" class="btn btn-outline-secondary btn-sm">{{ __('ui.cp.back') }}</a>
        </x-slot:actions>
    </x-dashboard.page-header>

    <div class="space-y-5">
        @if (session('success'))
            <div class="mb-6 flex items-center gap-3 rounded-xl border border-emerald-200 bg-emerald-50 px-5 py-4" style="animation: ds-fade-up 0.35s var(--ds-ease) both">
                <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-emerald-100 text-emerald-600">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" /></svg>
                </span>
                <div>
                    <p class="text-[13px] font-semibold text-emerald-800">{{ session('success') }}</p>
                </div>
            </div>
        @endif

        @if (! empty($this->payments))
        <div class="ds-card" x-data="{ open: true }">
            <button type="button" @click="open = !open" class="ds-card-header w-full cursor-pointer items-center gap-2 hover:bg-[var(--ds-surface-sunken)]/50 transition">
                <span class="flex h-6 w-6 items-center justify-center rounded-lg bg-slate-100 text-slate-600">
                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 12h16.5m-16.5 0l5.25-5.25M3.75 12l5.25 5.25" /></svg>
                </span>
                <div class="flex-1 text-left">
                    <h2 class="text-[13px] font-semibold text-[var(--ds-text)]">{{ __('ui.cp.history') }}</h2>
                    <p class="text-[11px] text-[var(--ds-text-faint)]">{{ __('ui.cp.history_hint', ['count' => count($this->payments)]) }}</p>
                </div>
                <svg class="h-4 w-4 text-[var(--ds-text-muted)] transition" :class="open ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" /></svg>
            </button>
            <div x-show="open" x-collapse x-cloak>
                <div class="ds-card-body">
                    <div class="space-y-3">
                        @foreach ($this->payments as $pmt)
                            <div class="rounded-lg border border-[var(--ds-border-subtle)] bg-[var(--ds-surface-sunken)] p-4">
                                <div class="flex flex-wrap items-start justify-between gap-2">
                                    <div class="flex items-center gap-3">
                                        <span class="rounded-md bg-white px-2 py-0.5 font-mono text-[11px] font-semibold text-[var(--ds-text-secondary)] shadow-sm">{{ $pmt['reference'] }}</span>
                                        <span class="text-[13px] font-semibold">{{ \App\Support\Money::da($pmt['amount_da'], 2) }} <span class="text-[11px] font-normal text-[var(--ds-text-muted)]">DA</span></span>
                                    </div>
                                    @if ($pmt['status'] === 'pending')
                                        <span class="inline-flex items-center gap-1 rounded-full bg-amber-50 px-2.5 py-0.5 text-[11px] font-semibold text-amber-700 ring-1 ring-amber-200">
                                            <span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span> Pending
                                        </span>
                                    @elseif ($pmt['status'] === 'validated')
                                        <span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-2.5 py-0.5 text-[11px] font-semibold text-emerald-700 ring-1 ring-emerald-200">
                                            <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span> Validated
                                        </span>
                                    @elseif ($pmt['status'] === 'rejected')
                                        <span class="inline-flex items-center gap-1 rounded-full bg-red-50 px-2.5 py-0.5 text-[11px] font-semibold text-red-700 ring-1 ring-red-200">
                                            <span class="h-1.5 w-1.5 rounded-full bg-red-500"></span> Rejected
                                        </span>
                                    @endif
                                </div>
                                <div class="mt-2 flex flex-wrap gap-x-6 gap-y-1 text-[11px] text-[var(--ds-text-muted)]">
                                    <span>Submitted: {{ $pmt['created_at'] }}</span>
                                    <span>By: {{ $pmt['submitted_by'] }}</span>
                                    @if ($pmt['payment_type_label'])
                                        <span>Type: {{ $pmt['payment_type_label'] }}</span>
                                    @endif
                                    @if ($pmt['paid_at'])
                                        <span>Paid: {{ $pmt['paid_at'] }}</span>
                                    @endif
                                    @if ($pmt['mandate_number'])
                                        <span>Mandate: {{ $pmt['mandate_number'] }}</span>
                                    @endif
                                    @if ($pmt['description'])
                                        <span class="w-full mt-1 text-[var(--ds-text-secondary)]">{{ $pmt['description'] }}</span>
                                    @endif
                                    @if ($pmt['rejection_cause'])
                                        <span class="w-full mt-1 text-red-600">Reason: {{ $pmt['rejection_cause'] }}</span>
                                    @endif
                                    @if ($pmt['invoice'])
                                        @php $invoices = json_decode($pmt['invoice'], true) ?? [$pmt['invoice']]; @endphp
                                        <div class="w-full mt-1 flex flex-wrap gap-2">
                                            @foreach ($invoices as $inv)
                                                <a href="{{ asset('storage/' . $inv) }}" target="_blank" class="inline-flex items-center gap-1 rounded-md border border-[var(--ds-border)] bg-white px-2 py-1 text-[10px] font-medium text-[var(--ds-primary)] hover:underline">
                                                    <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" /></svg>
                                                    {{ basename($inv) }}
                                                </a>
                                            @endforeach
                                        </div>
                                    @endif
                                    @if ($pmt['invoice_by_ministry'])
                                        <div class="w-full mt-1">
                                            <a href="{{ asset('storage/' . $pmt['invoice_by_ministry']) }}" target="_blank" class="inline-flex items-center gap-1 rounded-md border border-[var(--ds-border)] bg-white px-2 py-1 text-[10px] font-medium text-[var(--ds-primary)] hover:underline">
                                                <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" /></svg>
                                                Ministry invoice
                                            </a>
                                        </div>
                                    @endif
                                </div>

                                @if ($this->canValidate && $pmt['status'] === 'pending')
                                    <div class="mt-3 flex items-center gap-2 border-t border-[var(--ds-border-subtle)] pt-3">
                                        <button type="button" wire:click="showAcceptForm({{ $pmt['id'] }})"
                                            class="btn btn-success btn-sm" style="display: inline-flex; align-items: center; gap: 0.25rem;">
                                            <svg class="h-3.5 w-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                            Validate
                                        </button>
                                        <button type="button" wire:click="showRejectForm({{ $pmt['id'] }})"
                                            class="btn btn-outline-danger btn-sm" style="display: inline-flex; align-items: center; gap: 0.25rem;">
                                            <svg class="h-3.5 w-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                                            Reject
                                        </button>
                                    </div>

                                    {{-- Accept form --}}
                                    <div x-show="$wire.acceptingId === {{ $pmt['id'] }}" x-cloak x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" class="relative mt-3 overflow-hidden rounded-xl border border-emerald-200 bg-white shadow-sm">
                                        <div class="absolute left-0 top-0 bottom-0 w-1 bg-emerald-500"></div>
                                        <div class="px-5 py-4">
                                            <div class="mb-4 flex items-center gap-2">
                                                <span class="flex h-6 w-6 items-center justify-center rounded-lg bg-emerald-50 text-emerald-600">
                                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                                </span>
                                                <span class="text-[13px] font-semibold text-emerald-800">Validate CP request</span>
                                            </div>
                                            <div class="space-y-4">
                                                <div class="grid gap-4 sm:grid-cols-2">
                                                    <div>
                                                        <label class="ds-form-label">Payment type <span class="text-red-500">*</span></label>
                                                        <select wire:model="paymentType" class="form-select">
                                                            <option value="">Select type...</option>
                                                            <option value="regular">Regular</option>
                                                            <option value="credit_request">Credit Request</option>
                                                            <option value="revaluation">Revaluation</option>
                                                        </select>
                                                        @error('paymentType') <p class="ds-form-error">{{ $message }}</p> @enderror
                                                    </div>
                                                    <div>
                                                        <label class="ds-form-label">Mandate number</label>
                                                        <input wire:model="mandateNumber" type="text" class="form-control" placeholder="e.g. M-2026-123" />
                                                        @error('mandateNumber') <p class="ds-form-error">{{ $message }}</p> @enderror
                                                    </div>
                                                </div>
                                                <div>
                                                    <label class="ds-form-label">Invoice by ministry</label>
                                                    <div class="flex items-center gap-3 rounded-lg border-2 border-dashed border-[var(--ds-border)] bg-[var(--ds-surface-sunken)] px-4 py-3">
                                                        <svg class="h-5 w-5 shrink-0 text-[var(--ds-text-muted)]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5" /></svg>
                                                        <input wire:model="invoiceByMinistry" type="file" accept=".pdf,.jpg,.jpeg,.png" class="form-control border-0 bg-transparent p-0 text-[13px]" />
                                                    </div>
                                                    @error('invoiceByMinistry') <p class="ds-form-error">{{ $message }}</p> @enderror
                                                </div>
                                                <div class="flex justify-end gap-2 border-t border-[var(--ds-border-subtle)] pt-3">
                                                    <button type="button" wire:click="cancelAction" class="btn btn-outline-secondary btn-sm">Cancel</button>
                                                    <button type="button" wire:click="confirmAccept" wire:loading.attr="disabled" wire:target="confirmAccept" class="btn btn-primary btn-sm inline-flex items-center gap-1">
                                                        <span wire:loading wire:target="confirmAccept" class="spinner-border spinner-border-sm" role="status"></span>
                                                        Confirm validation
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Reject form --}}
                                    <div x-show="$wire.rejectingId === {{ $pmt['id'] }}" x-cloak x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" class="relative mt-3 overflow-hidden rounded-xl border border-red-200 bg-white shadow-sm">
                                        <div class="absolute left-0 top-0 bottom-0 w-1 bg-red-500"></div>
                                        <div class="px-5 py-4">
                                            <div class="mb-4 flex items-center gap-2">
                                                <span class="flex h-6 w-6 items-center justify-center rounded-lg bg-red-50 text-red-600">
                                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                                                </span>
                                                <span class="text-[13px] font-semibold text-red-800">Reject CP request</span>
                                            </div>
                                            <div class="space-y-4">
                                                <div>
                                                    <label class="ds-form-label">Rejection cause <span class="text-red-500">*</span></label>
                                                    <textarea wire:model="rejectionCause" rows="3" class="form-control" placeholder="Specify the reason for rejection..."></textarea>
                                                    @error('rejectionCause') <p class="ds-form-error">{{ $message }}</p> @enderror
                                                </div>
                                                <div class="flex justify-end gap-2">
                                                    <button type="button" wire:click="cancelAction" class="btn btn-outline-secondary btn-sm">Cancel</button>
                                                    <button type="button" wire:click="confirmReject" wire:loading.attr="disabled" wire:target="confirmReject" class="btn btn-outline-secondary btn-sm text-red-600 border-red-300 hover:bg-red-50 hover:text-red-700 hover:border-red-400 inline-flex items-center gap-1">
                                                        <span wire:loading wire:target="confirmReject" class="spinner-border spinner-border-sm" role="status"></span>
                                                        Confirm rejection
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
        @endif

        @if ($this->canRequest)
        <div class="ds-card">
            <div class="ds-card-header flex items-center gap-2">
                <span class="flex h-6 w-6 items-center justify-center rounded-lg bg-emerald-50 text-emerald-600">
                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75" /></svg>
                </span>
                <div>
                    <h2 class="text-[13px] font-semibold text-[var(--ds-text)]">{{ __('ui.cp.details') }}</h2>
                    <p class="text-[11px] text-[var(--ds-text-faint)]">Project: <strong>{{ $project->fip_id }}</strong> — {{ $project->title_fr }}</p>
                </div>
            </div>
            <div class="ds-card-body space-y-4">
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="ds-form-label">Reference ID</label>
                        <input type="text" class="form-control bg-[var(--ds-surface-sunken)] text-[var(--ds-text-muted)]" value="{{ $this->referenceId }}" disabled />
                    </div>
                    <div>
                        <label for="amount_da" class="ds-form-label">{{ __('ui.cp.amount') }} <span class="text-red-500">*</span></label>
                        <input wire:model="amount_da" id="amount_da" type="number" step="0.01" min="0.01" class="form-control" placeholder="0,00" />
                        <p class="mt-1 text-[11px] text-[var(--ds-text-faint)]">
                            {{ __('ui.cp.rar_hint', ['available' => number_format($this->rarAvailable, 2, ',', ' ')]) }}
                        </p>
                        <p class="mt-0.5 text-[11px] text-[var(--ds-text-faint)]">{{ __('ui.cp.who_validates') }}</p>
                        @error('amount_da') <p class="ds-form-error">{{ $message }}</p> @enderror
                    </div>
                </div>
                <div>
                    <label for="description" class="ds-form-label">Notes</label>
                    <textarea wire:model="description" id="description" rows="3" class="form-control" placeholder="Context, payment details…"></textarea>
                    @error('description') <p class="ds-form-error">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>

        <div class="ds-card">
            <div class="ds-card-header flex items-center gap-2">
                <span class="flex h-6 w-6 items-center justify-center rounded-lg bg-amber-50 text-amber-700">
                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5" /></svg>
                </span>
                <div class="flex-1">
                    <h2 class="text-[13px] font-semibold text-[var(--ds-text)]">{{ __('ui.cp.docs') }}</h2>
                    <p class="text-[11px] text-[var(--ds-text-faint)]">{{ __('ui.cp.docs_hint') }}</p>
                </div>
            </div>
            <div class="ds-card-body space-y-4">
                <div class="flex items-center gap-3">
                    <span class="text-[14px] font-semibold text-[var(--ds-text)]">{{ __('ui.cp.files') }}</span>
                    <button type="button" wire:click="addDocument" class="btn btn-outline-secondary btn-sm">{{ __('ui.cp.add_file') }}</button>
                </div>
                <div class="space-y-4">
                    @foreach ($documentNotes as $i => $note)
                        <div wire:key="cp-doc-{{ $i }}" class="rounded-xl border border-[var(--ds-border)] bg-white shadow-sm">
                            <div class="flex items-center justify-between border-b border-[var(--ds-border-subtle)] bg-[var(--ds-surface-sunken)] px-4 py-2">
                                <span class="text-[12px] font-semibold text-[var(--ds-text-secondary)]">{{ __('ui.cp.document_n', ['n' => $i + 1]) }}</span>
                                @if (count($documentNotes) > 1)
                                    <button type="button" wire:click="removeDocument({{ $i }})" class="rounded-md p-1 text-[var(--ds-text-faint)] hover:bg-red-50 hover:text-red-600" title="{{ __('ui.common.remove') }}">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                                    </button>
                                @endif
                            </div>
                            <div class="space-y-3 p-4">
                                <input wire:model="documentNotes.{{ $i }}" type="text" class="form-control" placeholder="{{ __('ui.cp.notes') }}" />
                                <div class="flex items-center gap-3 rounded-lg border-2 border-dashed border-[var(--ds-border)] bg-[var(--ds-surface-sunken)] px-4 py-3">
                                    <svg class="h-5 w-5 shrink-0 text-[var(--ds-text-muted)]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5" /></svg>
                                    <input wire:model="documentFiles.{{ $i }}" type="file" accept=".pdf,.jpg,.jpeg,.png,application/pdf,image/*" class="form-control border-0 bg-transparent p-0 text-[13px]" />
                                </div>
                                <div wire:loading wire:target="documentFiles.{{ $i }}" class="text-[11px] text-[var(--ds-text-muted)]">
                                    {{ __('ui.cp.uploading') }}
                                </div>
                                @error('documentFiles.'.$i) <p class="ds-form-error">{{ $message }}</p> @enderror
                                @if (! empty($documentFiles[$i] ?? null))
                                    @php $tmp = $documentFiles[$i]; @endphp
                                    <div class="flex items-center gap-3 rounded-lg border border-[var(--ds-border)] bg-[var(--ds-surface-sunken)] px-4 py-3">
                                        <svg class="h-5 w-5 shrink-0 text-[var(--ds-primary)]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" /></svg>
                                        <span class="text-[13px] text-[var(--ds-text-muted)]">
                                            {{ is_object($tmp) && method_exists($tmp, 'getClientOriginalName') ? $tmp->getClientOriginalName() : __('ui.cp.file_selected') }}
                                        </span>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="flex flex-col gap-4 rounded-xl border border-[var(--ds-border)] bg-white px-5 py-4 shadow-sm sm:flex-row sm:flex-wrap sm:items-center sm:justify-between">
            <p class="text-[12px] text-[var(--ds-text-muted)] max-w-md">
                <strong>Submit</strong> — the CP request will be recorded for validation.
            </p>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('dashbored.projects') }}" class="btn btn-outline-secondary btn-sm">{{ __('ui.common.cancel') }}</a>
                <button type="button" wire:click="submit" wire:loading.attr="disabled" wire:target="submit" class="btn btn-primary btn-sm">
                    <span wire:loading wire:target="submit" class="spinner-border spinner-border-sm me-1" role="status"></span>
                    {{ __('ui.cp.submit_btn') }}
                </button>
            </div>
        </div>
        @endif
    </div>
</div>
