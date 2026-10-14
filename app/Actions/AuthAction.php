<?php

namespace App\Actions;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthAction
{
    public function check(string $username, string $email): array
    {
        $user = User::where('username', $username)
            ->where('email', $email)
            ->first();

        if (! $user) {
            return ['found' => false, 'has_password' => false, 'is_active' => false];
        }

        return [
            'found' => true,
            'has_password' => $user->password !== null && $user->password !== '',
            'is_active' => (bool) $user->is_active,
            'user' => $user,
        ];
    }

    public function register(string $username, string $email, string $password): User
    {
        $user = User::where('username', $username)
            ->where('email', $email)
            ->first();

        if (! $user) {
            throw ValidationException::withMessages(['username' => 'Account not found.']);
        }

        if (! $user->is_active) {
            throw ValidationException::withMessages(['username' => 'Account is deactivated.']);
        }

        if ($user->password !== null && $user->password !== '') {
            throw ValidationException::withMessages(['email' => 'User already has a password.']);
        }

        $user->password = Hash::make($password);
        $user->save();

        return $user;
    }

    public function login(string $email, string $password): User
    {
        $user = User::where('email', $email)->first();

        if (! $user || ! Hash::check($password, (string) $user->password)) {
            throw ValidationException::withMessages(['email' => 'The provided credentials are incorrect.']);
        }

        if (! $user->is_active) {
            throw ValidationException::withMessages(['email' => 'Account is deactivated.']);
        }

        return $user;
    }

    /**
     * Mint an API token whose abilities mirror the role's permission grid, so
     * a token can never do more than its owner's role allows.
     */
    public function issueToken(User $user, string $name = 'auth-token'): string
    {
        $abilities = $user->permissions();

        // Sanctum treats an empty ability list as "no abilities"; give observers
        // at least their read grant rather than a token that can do nothing.
        if ($abilities === []) {
            $abilities = ['*'];
        }

        return $user->createToken($name, $abilities)->plainTextToken;
    }

    /**
     * Payload shared by register, login and me so clients always see the same
     * shape.
     *
     * @return array<string, mixed>
     */
    public function identityPayload(User $user): array
    {
        return [
            'id' => $user->id,
            'username' => $user->username,
            'name' => $user->name,
            'email' => $user->email,
            'wilaya_id' => $user->wilaya_id,
            'role' => $user->role,
            'role_label' => $user->roleLabel(),
            'is_national' => $user->isNational(),
            'permissions' => $user->permissions(),
        ];
    }
}
