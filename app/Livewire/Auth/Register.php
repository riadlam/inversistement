<?php

namespace App\Livewire\Auth;

use App\Actions\AuthAction;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.auth')]
class Register extends Component
{
    public string $mode = 'signup';
    public string $stage = 'form';
    public int $step = 1;

    public string $username = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';
    public string $verification_code = '';

    public ?string $dialogMessage = null;
    public string $dialogType = 'error';

    public function switchMode(string $mode): void
    {
        if (! in_array($mode, ['signup', 'signin'], true)) return;

        $this->resetErrorBag();
        $this->dialogMessage = null;
        $this->mode = $mode;
        $this->stage = 'form';
        $this->step = 1;
        $this->password = '';
        $this->password_confirmation = '';
        $this->verification_code = '';
    }

    public function dismissDialog(): void
    {
        $this->dialogMessage = null;
    }

    public function nextStep(): void
    {
        $this->resetErrorBag();
        $this->dialogMessage = null;

        if (trim($this->username) === '') {
            $this->addError('username', __('ui.auth.username_required'));
            return;
        }
        if (trim($this->email) === '' || ! filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
            $this->addError('email', __('ui.auth.email_required'));
            return;
        }

        $result = app(AuthAction::class)->check($this->username, $this->email);

        if (! $result['found']) {
            $this->dialogMessage = __('ui.auth.not_found');
            $this->dialogType = 'error';
            return;
        }

        if (! $result['is_active']) {
            $this->dialogMessage = __('ui.auth.deactivated');
            $this->dialogType = 'error';
            return;
        }

        if ($result['has_password']) {
            $this->dialogMessage = __('ui.auth.has_password');
            $this->dialogType = 'error';
            $this->mode = 'signin';
            return;
        }

        $this->step = 2;
    }

    public function prevStep(): void
    {
        $this->step = 1;
    }

    public function submitSignup(): void
    {
        $this->resetErrorBag();
        $this->dialogMessage = null;

        if (strlen($this->password) < 8) {
            $this->addError('password', __('ui.auth.password_min'));
            return;
        }
        if ($this->password !== $this->password_confirmation) {
            $this->addError('password_confirmation', __('ui.auth.password_mismatch'));
            return;
        }

        try {
            $user = app(AuthAction::class)->register(
                $this->username,
                $this->email,
                $this->password,
            );
        } catch (\Illuminate\Validation\ValidationException $e) {
            $msg = $e->errors()[array_key_first($e->errors())][0] ?? __('ui.auth.register_failed');

            if (str_contains($msg, 'not found')) {
                $this->dialogMessage = __('ui.auth.not_found');
                $this->dialogType = 'error';
            } elseif (str_contains($msg, 'deactivated')) {
                $this->dialogMessage = __('ui.auth.deactivated');
                $this->dialogType = 'error';
            } elseif (str_contains($msg, 'already has a password')) {
                $this->dialogMessage = __('ui.auth.has_password');
                $this->dialogType = 'error';
                $this->mode = 'signin';
            } else {
                $this->dialogMessage = $msg;
                $this->dialogType = 'error';
            }
            return;
        }

        Auth::login($user);
        $this->redirect(route('dashbored.overview'), navigate: false);
    }

    public function submitSignin(): void
    {
        $this->resetErrorBag();
        $this->dialogMessage = null;

        if (trim($this->email) === '' || ! filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
            $this->addError('email', __('ui.auth.email_required'));
            return;
        }
        if (trim($this->password) === '') {
            $this->addError('password', __('ui.auth.password_required'));
            return;
        }

        try {
            $user = app(AuthAction::class)->login($this->email, $this->password);
        } catch (\Illuminate\Validation\ValidationException $e) {
            $msg = $e->errors()['email'][0] ?? __('ui.auth.bad_credentials');

            if (str_contains($msg, 'deactivated')) {
                $this->dialogMessage = __('ui.auth.deactivated');
                $this->dialogType = 'error';
            } else {
                $this->dialogMessage = __('ui.auth.bad_credentials');
                $this->dialogType = 'error';
            }
            return;
        }

        Auth::login($user);
        $this->redirect(route('dashbored.overview'), navigate: false);
    }

    public function backToForm(): void
    {
        $this->stage = 'form';
        $this->verification_code = '';
    }

    public function render()
    {
        return view('livewire.auth.register')
            ->title(__('ui.auth.page_title'));
    }
}
