<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class NetworkType extends Model
{
    protected $fillable = [
        'code',
        'label_fr',
        'label_en',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function links(): HasMany
    {
        return $this->hasMany(ProjectNetworkLink::class, 'network', 'code');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function label(?string $locale = null): string
    {
        $locale = $locale ?: app()->getLocale();

        if ($locale === 'en' && filled($this->label_en)) {
            return $this->label_en;
        }

        return $this->label_fr ?: $this->label_en ?: $this->code;
    }

    /** @return array<string, string> code => label */
    public static function options(): array
    {
        $locale = app()->getLocale();

        return Cache::remember("network_types.options.{$locale}", 60, function () {
            return static::query()
                ->active()
                ->orderBy('sort_order')
                ->orderBy('id')
                ->get()
                ->mapWithKeys(fn (self $t) => [$t->code => $t->label()])
                ->all();
        });
    }

    /** @return list<string> */
    public static function codes(): array
    {
        return array_keys(static::options());
    }

    public static function clearOptionsCache(): void
    {
        Cache::forget('network_types.options.en');
        Cache::forget('network_types.options.fr');
        Cache::forget('network_types.options.'.app()->getLocale());
    }

    public static function makeCode(string $label): string
    {
        $base = Str::slug($label, '_');
        $base = Str::limit($base !== '' ? $base : 'network', 60, '');
        $code = $base;
        $i = 2;

        while (static::where('code', $code)->exists()) {
            $code = Str::limit($base, 55, '').'_'.$i;
            $i++;
        }

        return $code;
    }

    protected static function booted(): void
    {
        static::saved(fn () => static::clearOptionsCache());
        static::deleted(fn () => static::clearOptionsCache());
    }
}
