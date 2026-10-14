<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Cache;

class DesignOffice extends Model
{
    protected $fillable = [
        'name',
        'city',
        'phone',
        'email',
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

    public function designMissions(): HasMany
    {
        return $this->hasMany(ProjectDesignMission::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /** @return list<array{id:int,name:string,city:?string}> */
    public static function options(): array
    {
        return Cache::remember('design_offices.options', 60, function () {
            return static::query()
                ->active()
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get(['id', 'name', 'city'])
                ->map(fn (self $o) => [
                    'id' => $o->id,
                    'name' => $o->name,
                    'city' => $o->city,
                ])
                ->all();
        });
    }

    public static function clearOptionsCache(): void
    {
        Cache::forget('design_offices.options');
    }

    protected static function booted(): void
    {
        static::saved(fn () => static::clearOptionsCache());
        static::deleted(fn () => static::clearOptionsCache());
    }
}
