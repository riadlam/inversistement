<div class="ds-auth-card">
    {{-- Dialog --}}
    @if ($dialogMessage)
        <div class="ds-auth-dialog {{ $dialogType === 'error' ? 'ds-auth-dialog-error' : 'ds-auth-dialog-success' }}">
            @if ($dialogType === 'error')
                <svg class="ds-auth-dialog-icon mt-0.5 h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" /></svg>
            @else
                <svg class="ds-auth-dialog-icon mt-0.5 h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
            @endif
            <p>{{ $dialogMessage }}</p>
            <button type="button" wire:click="dismissDialog" class="ds-auth-dialog-dismiss">&times;</button>
        </div>
    @endif

    <div class="ds-auth-switch mb-4">
        <button type="button" class="ds-auth-switch-btn {{ $mode === 'signup' ? 'ds-auth-switch-btn-active' : '' }}" wire:click="switchMode('signup')">{{ __('ui.auth.signup') }}</button>
        <button type="button" class="ds-auth-switch-btn {{ $mode === 'signin' ? 'ds-auth-switch-btn-active' : '' }}" wire:click="switchMode('signin')">{{ __('ui.auth.signin') }}</button>
    </div>

@if ($stage === 'form')
    @if ($mode === 'signup')
    {{-- Progress indicator --}}
    <div class="ds-auth-steps">
        <div class="ds-auth-step {{ $step >= 1 ? 'ds-auth-step-active' : '' }}">
            <span class="ds-auth-step-dot">1</span>
            <span class="ds-auth-step-label">{{ __('ui.auth.identity') }}</span>
        </div>
        <div class="ds-auth-step-line {{ $step >= 2 ? 'ds-auth-step-line-active' : '' }}"></div>
        <div class="ds-auth-step {{ $step >= 2 ? 'ds-auth-step-active' : '' }}">
            <span class="ds-auth-step-dot">2</span>
            <span class="ds-auth-step-label">{{ __('ui.auth.security') }}</span>
        </div>
    </div>
    @endif

    {{-- Header --}}
    <div class="ds-auth-header">
        @if ($mode === 'signup' && $step === 1)
            <h2 class="ds-auth-title">{{ __('ui.auth.create_title') }}</h2>
            <p class="ds-auth-subtitle">{{ __('ui.auth.create_sub') }}</p>
        @elseif ($mode === 'signup' && $step === 2)
            <h2 class="ds-auth-title">{{ __('ui.auth.secure_title') }}</h2>
            <p class="ds-auth-subtitle">{{ __('ui.auth.secure_sub') }}</p>
        @else
            <h2 class="ds-auth-title">{{ __('ui.auth.welcome') }}</h2>
            <p class="ds-auth-subtitle">{{ __('ui.auth.welcome_sub') }}</p>
        @endif
    </div>

    {{-- Step 1: Identity --}}
    @if ($mode === 'signup' && $step === 1)
        <div class="ds-auth-form" wire:key="step-1">
            <div class="ds-auth-field">
                <label for="username" class="ds-auth-label">{{ __('ui.auth.username') }}</label>
                <div class="ds-auth-input-wrap">
                    <span class="ds-auth-input-icon">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" /></svg>
                    </span>
                    <input
                        id="username"
                        type="text"
                        class="ds-auth-input"
                        wire:model="username"
                        placeholder="{{ __('ui.auth.username_ph') }}"
                        autocomplete="username"
                        autofocus
                    >
                </div>
                @error('username') <p class="ds-auth-error">{{ $message }}</p> @enderror
            </div>

            <div class="ds-auth-field">
                <label for="email" class="ds-auth-label">{{ __('ui.auth.email') }}</label>
                <div class="ds-auth-input-wrap">
                    <span class="ds-auth-input-icon">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75" /></svg>
                    </span>
                    <input
                        id="email"
                        type="email"
                        class="ds-auth-input"
                        wire:model="email"
                        placeholder="{{ __('ui.auth.email_ph') }}"
                        autocomplete="email"
                    >
                </div>
                @error('email') <p class="ds-auth-error">{{ $message }}</p> @enderror
            </div>

            <button type="button" class="ds-auth-btn-primary" wire:click="nextStep">
                {{ __('ui.auth.continue') }}
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" /></svg>
            </button>
        </div>
    @endif

    {{-- Step 2: Password --}}
    @if ($mode === 'signup' && $step === 2)
        <div class="ds-auth-form" wire:key="step-2">
            <div class="ds-auth-field">
                <label for="password" class="ds-auth-label">{{ __('ui.auth.password') }}</label>
                <div class="ds-auth-input-wrap">
                    <span class="ds-auth-input-icon">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" /></svg>
                    </span>
                    <input
                        id="password"
                        type="password"
                        class="ds-auth-input"
                        wire:model="password"
                        placeholder="{{ __('ui.auth.password_ph') }}"
                        autocomplete="new-password"
                        autofocus
                    >
                </div>
                @error('password') <p class="ds-auth-error">{{ $message }}</p> @enderror
            </div>

            <div class="ds-auth-field">
                <label for="password_confirmation" class="ds-auth-label">{{ __('ui.auth.confirm') }}</label>
                <div class="ds-auth-input-wrap">
                    <span class="ds-auth-input-icon">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.57-.598-3.75h-.152c-3.196 0-6.1-1.248-8.25-3.285z" /></svg>
                    </span>
                    <input
                        id="password_confirmation"
                        type="password"
                        class="ds-auth-input"
                        wire:model="password_confirmation"
                        placeholder="{{ __('ui.auth.repeat_ph') }}"
                        autocomplete="new-password"
                    >
                </div>
                @error('password_confirmation') <p class="ds-auth-error">{{ $message }}</p> @enderror
            </div>

            <div class="ds-auth-btn-group">
                <button type="button" class="ds-auth-btn-ghost" wire:click="prevStep">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" /></svg>
                    {{ __('ui.auth.back') }}
                </button>
                <button type="button" class="ds-auth-btn-primary" wire:click="submitSignup">
                    {{ __('ui.auth.create') }}
                </button>
            </div>
        </div>
    @endif

    {{-- Sign in form --}}
    @if ($mode === 'signin')
        <div class="ds-auth-form" wire:key="signin">
            <div class="ds-auth-field">
                <label for="signin_email" class="ds-auth-label">{{ __('ui.auth.email') }}</label>
                <div class="ds-auth-input-wrap">
                    <span class="ds-auth-input-icon">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75" /></svg>
                    </span>
                    <input id="signin_email" type="email" class="ds-auth-input" wire:model="email" placeholder="{{ __('ui.auth.email_ph') }}" autocomplete="email" autofocus>
                </div>
                @error('email') <p class="ds-auth-error">{{ $message }}</p> @enderror
            </div>

            <div class="ds-auth-field">
                <label for="signin_password" class="ds-auth-label">{{ __('ui.auth.password') }}</label>
                <div class="ds-auth-input-wrap">
                    <span class="ds-auth-input-icon">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" /></svg>
                    </span>
                    <input id="signin_password" type="password" class="ds-auth-input" wire:model="password" placeholder="{{ __('ui.auth.enter_password') }}" autocomplete="current-password">
                </div>
                @error('password') <p class="ds-auth-error">{{ $message }}</p> @enderror
            </div>

            <button type="button" class="ds-auth-btn-primary" wire:click="submitSignin">{{ __('ui.auth.signin') }}</button>
        </div>
    @endif
@endif

@if ($stage === 'verify')
    <div class="ds-auth-header mb-4">
        <h2 class="ds-auth-title">{{ __('ui.auth.verify_title') }}</h2>
        <p class="ds-auth-subtitle">{{ __('ui.auth.verify_sub', ['email' => $email]) }}</p>
    </div>

    <div class="ds-auth-success">
        <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 8.25v8.25a2.25 2.25 0 01-2.25 2.25H4.5a2.25 2.25 0 01-2.25-2.25V8.25m19.5 0L12 13.5 2.25 8.25m19.5 0A2.25 2.25 0 0019.5 6H4.5a2.25 2.25 0 00-2.25 2.25" /></svg>
        <div>
            <p class="font-semibold">{{ __('ui.auth.inbox') }}</p>
        </div>
    </div>

    <div class="ds-auth-form">
        <div class="ds-auth-field">
            <label for="verification_code" class="ds-auth-label">{{ __('ui.auth.code') }}</label>
            <input id="verification_code" type="text" class="ds-auth-input" wire:model="verification_code" placeholder="{{ __('ui.auth.code_ph') }}">
        </div>
        <div class="ds-auth-btn-group">
            <button type="button" class="ds-auth-btn-ghost" wire:click="backToForm">{{ __('ui.auth.back') }}</button>
            <button type="button" class="ds-auth-btn-primary">{{ __('ui.auth.verify') }}</button>
        </div>
    </div>
@endif

    {{-- Footer --}}
    @if ($stage === 'form')
        <p class="ds-auth-footer">
            @if ($mode === 'signup')
                {{ __('ui.auth.have_account') }} <button type="button" class="ds-auth-link-btn" wire:click="switchMode('signin')">{{ __('ui.auth.signin') }}</button>
            @else
                {{ __('ui.auth.need_account') }} <button type="button" class="ds-auth-link-btn" wire:click="switchMode('signup')">{{ __('ui.auth.signup') }}</button>
            @endif
        </p>
    @endif
</div>
