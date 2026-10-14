<?php

namespace App\Models;

use App\Models\Concerns\ScopedToWilaya;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Project extends Model
{
    use HasFactory, ScopedToWilaya, SoftDeletes;

    /** Official fiche « Situation du projet » boxes (PDF header row). */
    public const PHASE_ETUDES = 'etudes';

    public const PHASE_LANCEMENT = 'lancement';

    public const PHASE_REALISATION = 'realisation';

    public const PHASE_ARRET = 'arret';

    public const PHASE_ACHEVE = 'acheve';

    public const PHASE_EN_EQUIPEMENT = 'en_equipement';

    public const PHASE_ACHEVE_EQUIPE = 'acheve_equipe';

    /** @return array<string, string> */
    public static function phaseLabels(): array
    {
        return [
            self::PHASE_ETUDES => 'En études',
            self::PHASE_LANCEMENT => 'En cours de lancement',
            self::PHASE_REALISATION => 'En cours de réalisation',
            self::PHASE_ARRET => "A l'arrêt",
            self::PHASE_ACHEVE => 'Achevé',
            self::PHASE_EN_EQUIPEMENT => "En cours d'équipement",
            self::PHASE_ACHEVE_EQUIPE => 'Achevé et équipé',
        ];
    }

    protected $fillable = [
        'fip_id',
        'operation_prefix',
        'operation_number',
        'title_fr',
        'title_ar',
        'short_label',
        'description',
        'programme_id',
        'wilaya_id',
        'daira_id',
        'commune_code',
        'establishment_id',
        'project_owner_id',
        'nature',
        'status',
        'phase',
        'equipment_progress_pct',
        'capacity',
        'nbr_operations',
        'secteur_id',
        'sous_secteur_id',
        'chapitre_id',
        'programme_type_id',
        'programme_structure_id',
        'sous_programme_id',
        'decision_programme_ref',
        'inscription_date',
        'individualisation_number',
        'individualisation_date',
        'notification_year',
        'titre',
        'managed_by',
        'ae_da',
        'reevaluation_count',
        'cp_da',
        'cp_notification_current_year_da',
        'cp_reliquat_da',
        'cp_besoins_da',
        'amount_committed_da',
        'ods_date',
        'initial_launch_date',
        'initial_launch_scope',
        'works_reception_quarter',
        'works_reception_year',
        'equipment_reception_quarter',
        'equipment_reception_year',
        'inauguration_quarter',
        'inauguration_year',
        'fiche_updated_at',
        'reception_provisoire_date',
        'reception_definitive_date',
        'project_duration_months',
        'deadline',
        'company_name',
        'nif',
        'business_register',
        'nc13_code_id',
        'budget_type',
        'observations',
        'is_closed',
        'created_by',
        'validated_by',
        'validated_at',
        'rejection_reason',
        'returned_from',
    ];

    protected function casts(): array
    {
        return [
            'ae_da' => 'decimal:2',
            'cp_da' => 'decimal:2',
            'cp_notification_current_year_da' => 'decimal:2',
            'cp_reliquat_da' => 'decimal:2',
            'cp_besoins_da' => 'decimal:2',
            'amount_committed_da' => 'decimal:2',
            'equipment_progress_pct' => 'decimal:2',
            'capacity' => 'integer',
            'nbr_operations' => 'integer',
            'reevaluation_count' => 'integer',
            'notification_year' => 'integer',
            'works_reception_quarter' => 'integer',
            'works_reception_year' => 'integer',
            'equipment_reception_quarter' => 'integer',
            'equipment_reception_year' => 'integer',
            'inauguration_quarter' => 'integer',
            'inauguration_year' => 'integer',
            'is_closed' => 'boolean',
            'ods_date' => 'date',
            'initial_launch_date' => 'date',
            'inscription_date' => 'date',
            'individualisation_date' => 'date',
            'reception_provisoire_date' => 'date',
            'reception_definitive_date' => 'date',
            'fiche_updated_at' => 'date',
            'deadline' => 'date',
            'validated_at' => 'datetime',
        ];
    }

    /**
     * The fiche prints the operation code in front of the intitulé
     * ("Nk.5.764.8.262.107.10.01 - Réalisation d'une ferme pédagogique").
     */
    public function operationCode(): ?string
    {
        return $this->operation_number ?: $this->fip_id;
    }

    public function programme(): BelongsTo
    {
        return $this->belongsTo(Programme::class);
    }

    public function establishment(): BelongsTo
    {
        return $this->belongsTo(TabEtablissement::class);
    }

    public function projectOwner(): BelongsTo
    {
        return $this->belongsTo(ProjectOwner::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function validator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'validated_by');
    }

    public function maturationItems(): BelongsToMany
    {
        return $this->belongsToMany(MaturationChecklistDefinition::class, 'maturation_project')
            ->withPivot('checked')
            ->withTimestamps();
    }

    public function riskFiles(): HasMany
    {
        return $this->hasMany(RisksFile::class);
    }

    public function cpProjections(): HasMany
    {
        return $this->hasMany(CpProjection::class);
    }

    public function nc13Code(): BelongsTo
    {
        return $this->belongsTo(Nc13Code::class);
    }

    public function physicalProgress(): HasMany
    {
        return $this->hasMany(PhysicalProgress::class, 'project_id');
    }

    public function cpPayments(): HasMany
    {
        return $this->hasMany(CpPayment::class);
    }

    public function aeEngagements(): HasMany
    {
        return $this->hasMany(AeEngagement::class);
    }

    public function aeRevaluations(): HasMany
    {
        return $this->hasMany(AeRevaluation::class)->orderBy('sequence');
    }

    public function aeCpEnvelopes(): HasMany
    {
        return $this->hasMany(AeCpEnvelope::class)->orderBy('year');
    }

    public function alerts(): HasMany
    {
        return $this->hasMany(Alert::class);
    }

    public function spuScores(): HasMany
    {
        return $this->hasMany(SpuScore::class)->orderByDesc('computed_at');
    }

    public function latestSpuScore(): HasOne
    {
        return $this->hasOne(SpuScore::class)->latestOfMany('computed_at');
    }

    public function closure(): HasOne
    {
        return $this->hasOne(ProjectClosure::class);
    }

    public function outgoingMovements(): HasMany
    {
        return $this->hasMany(Movement::class, 'source_project_id');
    }

    public function incomingMovements(): HasMany
    {
        return $this->hasMany(Movement::class, 'target_project_id');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(Document::class);
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class)->latest();
    }

    public function lots(): HasMany
    {
        return $this->hasMany(ProjectLot::class)->orderBy('tranche')->orderBy('lot_number');
    }

    public function pendingExpenses(): HasMany
    {
        return $this->hasMany(ProjectPendingExpense::class)->orderBy('lot_number');
    }

    public function land(): HasOne
    {
        return $this->hasOne(ProjectLand::class);
    }

    public function designMissions(): HasMany
    {
        return $this->hasMany(ProjectDesignMission::class);
    }

    public function networkLinks(): HasMany
    {
        return $this->hasMany(ProjectNetworkLink::class);
    }

    public function daira(): BelongsTo
    {
        return $this->belongsTo(Daira::class);
    }

    public function secteur(): BelongsTo
    {
        return $this->belongsTo(Secteur::class);
    }

    public function sousSecteur(): BelongsTo
    {
        return $this->belongsTo(SousSecteur::class);
    }

    public function chapitre(): BelongsTo
    {
        return $this->belongsTo(Chapitre::class);
    }

    public function programmeType(): BelongsTo
    {
        return $this->belongsTo(ProgrammeType::class);
    }

    public function programmeStructure(): BelongsTo
    {
        return $this->belongsTo(ProgrammeStructure::class);
    }

    public function sousProgramme(): BelongsTo
    {
        return $this->belongsTo(SousProgramme::class);
    }

    public function wilaya(): BelongsTo
    {
        return $this->belongsTo(Wilaya::class);
    }

    /**
     * Declined by programme manager and sent back to DASS for correction.
     * Stored as status=draft with a non-empty rejection_reason.
     */
    public function isReturned(): bool
    {
        return filled($this->rejection_reason)
            && in_array($this->status, ['draft', 'maturation'], true);
    }

    /**
     * Status key used for badges/filters (maps returned drafts to "returned").
     */
    public function displayStatus(): string
    {
        return $this->isReturned() ? 'returned' : (string) $this->status;
    }

    /**
     * A closed operation is verrouillée en "lecture seule" and becomes
     * "inactive" in the system (TDR Fiche 06).
     */
    public function isLocked(): bool
    {
        return $this->status === 'closed';
    }
}
