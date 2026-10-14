<div class="relative mt-4 overflow-hidden rounded-xl border border-red-200 bg-white shadow-sm">
    <div class="absolute bottom-0 left-0 top-0 w-1 bg-red-500"></div>
    <div class="px-5 py-4">
        <div class="mb-3 flex items-center gap-2">
            <span class="flex h-6 w-6 items-center justify-center rounded-lg bg-red-50 text-red-600">
                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
            </span>
            <span class="text-[13px] font-semibold text-red-800">{{ __('ui.show.arbitration_decline_title') }}</span>
        </div>
        <div class="space-y-3">
            <div>
                <label class="ds-form-label">{{ __('ui.show.arbitration_reason') }} <span class="text-red-500">*</span></label>
                <textarea wire:model="rejectionReason" rows="3" class="form-control" placeholder="{{ __('ui.show.arbitration_reason_placeholder') }}"></textarea>
                @error('rejectionReason') <p class="ds-form-error">{{ $message }}</p> @enderror
            </div>
            <div class="flex justify-end gap-2">
                <button type="button" wire:click="cancelReject" class="btn btn-outline-secondary btn-sm">{{ __('ui.common.cancel') }}</button>
                <button type="button" wire:click="confirmRejectArbitration" wire:loading.attr="disabled" wire:target="confirmRejectArbitration"
                        class="btn btn-outline-secondary btn-sm border-red-300 text-red-600 hover:border-red-400 hover:bg-red-50 hover:text-red-700 inline-flex items-center gap-1">
                    <span wire:loading wire:target="confirmRejectArbitration" class="spinner-border spinner-border-sm" role="status"></span>
                    {{ __('ui.show.arbitration_confirm_decline') }}
                </button>
            </div>
        </div>
    </div>
</div>
