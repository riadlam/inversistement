<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One procurement event on a lot. Kept as rows rather than columns because the
 * same event recurs (the fiche has an explicit "2ème avis d'appel d'offres").
 */
class LotMilestone extends Model
{
    use HasFactory;

    protected $fillable = [
        'lot_id',
        'type',
        'date',
        'reference',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
        ];
    }

    public function lot(): BelongsTo
    {
        return $this->belongsTo(ProjectLot::class, 'lot_id');
    }

    /** Ordered as the fiche prints them — loaded from admin reference data. */
    public static function typeLabels(): array
    {
        $options = LotMilestoneType::options();

        if ($options !== []) {
            return $options;
        }

        // Fallback if the types table is empty / not migrated yet.
        return [
            'cdc_en_elaboration' => "Cahier des charges en cours d'élaboration",
            'cdc_approuve_commission' => 'Cahier des charges approuvé par la commission',
            'appel_offres' => "Avis d'appel d'offres",
            'infructuosite' => 'Infructuosité',
            'attribution_provisoire' => 'Attribution provisoire',
            'annulation_attribution' => "Annulation de l'attribution",
            'appel_offres_2' => "2ème avis d'appel d'offres",
            'approbation_marche_commission' => 'Approbation du marché par la commission',
            'engagement_controleur_financier' => 'Engagement chez le contrôleur financier',
            'ods_notification' => "Notification de l'ODS",
        ];
    }

    public function typeLabel(): string
    {
        $type = LotMilestoneType::query()->where('code', $this->type)->first();

        return $type?->label() ?? (self::typeLabels()[$this->type] ?? $this->type);
    }
}
