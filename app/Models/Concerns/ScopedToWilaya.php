<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Builder;

/**
 * Restricts queries to the signed-in user's wilaya unless their role has
 * national scope, per the TDR access rules ("les utilisateurs ne voient que
 * leur périmètre"). Applied explicitly via `scopeVisibleTo` rather than as a
 * global scope so exports and jobs can opt out deliberately.
 */
trait ScopedToWilaya
{
    /**
     * Column holding the wilaya foreign key. Override per model if it differs.
     */
    protected static function wilayaScopeColumn(): string
    {
        return 'wilaya_id';
    }

    public function scopeVisibleTo(Builder $query, mixed $user = null): Builder
    {
        $user ??= auth()->user();

        if ($user === null) {
            return $query->whereRaw('1 = 0');
        }

        if ($user->isNational()) {
            return $query;
        }

        return $query->where(
            $query->getModel()->getTable().'.'.static::wilayaScopeColumn(),
            $user->wilaya_id,
        );
    }
}
