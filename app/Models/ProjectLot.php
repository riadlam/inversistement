<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * One lot of one tranche. Lot numbers restart on every tranche, so a lot is
 * only identified by the (project, tranche, number) triplet.
 */
class ProjectLot extends Model
{
    use HasFactory;

    public const STATE_RECEPTIONNE = 'receptionne';
    public const STATE_EN_COURS_REALISATION = 'en_cours_realisation';
    public const STATE_EN_COURS_LANCEMENT = 'en_cours_lancement';
    public const STATE_NON_LANCE = 'non_lance';

    protected $fillable = [
        'project_id',
        'tranche',
        'lot_number',
        'designation',
        'designation_ar',
        'kind',
        'state',
        'amount_da',
        'estimation_da',
        'estimated_by',
        'estimation_basis',
        'blocking_reason',
        'blocking_note_ar',
        'delai_months',
        'launch_date',
        'progress_pct',
        'reception_date',
        'contractor_id',
        'observations',
    ];

    protected function casts(): array
    {
        return [
            'tranche' => 'integer',
            'lot_number' => 'integer',
            'amount_da' => 'decimal:2',
            'estimation_da' => 'decimal:2',
            'delai_months' => 'integer',
            'launch_date' => 'date',
            'progress_pct' => 'decimal:2',
            'reception_date' => 'date',
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function contractor(): BelongsTo
    {
        return $this->belongsTo(Contractor::class);
    }

    public function milestones(): HasMany
    {
        return $this->hasMany(LotMilestone::class, 'lot_id');
    }

    public function scopeInState(Builder $query, string $state): Builder
    {
        return $query->where('state', $state);
    }

    /**
     * Contracted amount when a marché exists, otherwise the estimation the
     * fiche prints for lots that are not yet awarded.
     */
    public function effectiveAmountDa(): float
    {
        $amount = (float) $this->amount_da;

        return $amount > 0 ? $amount : (float) ($this->estimation_da ?? 0);
    }

    public static function stateLabels(): array
    {
        return [
            self::STATE_RECEPTIONNE => 'Réceptionné',
            self::STATE_EN_COURS_REALISATION => 'En cours de réalisation',
            self::STATE_EN_COURS_LANCEMENT => 'En cours de lancement',
            self::STATE_NON_LANCE => 'Non lancé',
        ];
    }

    public static function blockingReasonLabels(): array
    {
        return [
            'visa_refuse_plafond' => 'Visa refusé par le contrôle financier (dépassement du plafond)',
            'attente_visa_controle_financier' => 'En attente du visa du contrôle financier',
            'infructueux_offres_excessives' => 'Déclaré infructueux (offres excessives)',
            'attribution_provisoire' => 'Attribution provisoire prononcée',
            'autre' => 'Autre',
        ];
    }

    public function stateLabel(): string
    {
        return self::stateLabels()[$this->state] ?? $this->state;
    }
}
