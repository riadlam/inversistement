<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Raccordement du projet aux différents réseaux. */
class ProjectNetworkLink extends Model
{
    /** @deprecated Use NetworkType::codes() — kept as fallback if table empty. */
    public const NETWORKS = ['gaz', 'electricite', 'aep', 'assainissement'];

    protected $fillable = [
        'project_id',
        'network',
        'connected',
        'motif',
    ];

    protected function casts(): array
    {
        return [
            'connected' => 'boolean',
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /** @return list<string> */
    public static function networkCodes(): array
    {
        $codes = NetworkType::codes();

        return $codes !== [] ? $codes : self::NETWORKS;
    }

    /** @return array<string, string> */
    public static function networkLabels(): array
    {
        $options = NetworkType::options();

        if ($options !== []) {
            return $options;
        }

        return [
            'gaz' => 'Gaz',
            'electricite' => 'Électricité',
            'aep' => 'AEP',
            'assainissement' => 'Assainissement',
        ];
    }

    public function networkLabel(): string
    {
        $type = NetworkType::query()->where('code', $this->network)->first();

        return $type?->label() ?? (self::networkLabels()[$this->network] ?? $this->network);
    }
}
