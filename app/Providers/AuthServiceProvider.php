<?php

namespace App\Providers;

use App\Enums\Permission as PermissionEnum;
use App\Models\User;
use App\Support\Rbac\PermissionRegistry;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(PermissionRegistry::class);
        $this->app->singleton(\App\Services\Calculation\MetricRegistry::class);
    }

    public function boot(): void
    {
        // One gate per permission key, so `@can('cp.validate')` works in Blade,
        // Livewire and policies alike.
        foreach (PermissionEnum::cases() as $permission) {
            Gate::define($permission->value, function (User $user) use ($permission): bool {
                if (! $user->is_active) {
                    return false;
                }

                return $user->hasPermission($permission->value);
            });
        }

        // An inactive account can do nothing, whatever the matrix says.
        Gate::before(function (User $user) {
            if (! $user->is_active) {
                return false;
            }

            return null;
        });
    }
}
