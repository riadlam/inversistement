<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Canonical wilaya reference (`wilayas`), the target of every `wilaya_id`
 * foreign key.
 *
 * A legacy `wilaya` table (keyed by `code_wil`, no surrogate id) also exists in
 * the database from the imported ministry dumps. It is redundant with this one
 * apart from a "WILAYA ETRANGERE" row, and nothing in the application reads it
 * any more — communes join on {@see self::legacyCode()}.
 */
class Wilaya extends Model
{
    protected $table = 'wilayas';

    protected $fillable = [
        'code',
        'name_ar',
        'name_fr',
        'region_key',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function projects(): HasMany
    {
        return $this->hasMany(Project::class);
    }

    /**
     * `wilayas.code` is zero-padded ('07') while `commune.code_wilaya` is not
     * ('7'), so any join between the two must go through this.
     */
    public function legacyCode(): string
    {
        return ltrim((string) $this->code, '0');
    }

    /**
     * Communes of this wilaya. `wilayas.code` is padded ('07'); `commune.code_wilaya` is not ('7').
     */
    public function communeQuery()
    {
        return Commune::query()
            ->whereRaw('CAST(code_wilaya AS UNSIGNED) = CAST(? AS UNSIGNED)', [$this->code]);
    }

    public function communes(): HasMany
    {
        return $this->hasMany(Commune::class, 'code_wilaya', 'legacy_code');
    }

    /** Virtual attribute backing the {@see self::communes()} relation. */
    public function getLegacyCodeAttribute(): string
    {
        return $this->legacyCode();
    }

    /**
     * `tab_etablissement.wilaya` stores numeric codes ('7'), while the form
     * holds the French name ('Biskra'). Match every common encoding.
     *
     * @return list<string>
     */
    public function territoryKeys(): array
    {
        $legacy = $this->legacyCode();
        $padded = str_pad($legacy === '' ? '0' : $legacy, 2, '0', STR_PAD_LEFT);

        return array_values(array_unique(array_filter([
            (string) $this->code,
            $legacy,
            $padded,
            (string) (int) $legacy,
            (string) $this->name_fr,
        ], fn ($v) => $v !== '' && $v !== '0')));
    }
}
