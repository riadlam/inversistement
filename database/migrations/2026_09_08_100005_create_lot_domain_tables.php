<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * The lot-centric procurement domain the FICHE DE SUIVI is built around.
 *
 * Lot numbers restart at 1 on every tranche, so the natural key is
 * (project_id, tranche, lot_number) rather than lot_number alone.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contractors', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255);
            $table->enum('legal_form', ['etb', 'sarl', 'eeg', 'eurl', 'spa', 'autre'])->default('autre');
            $table->string('nif', 50)->nullable()->index();
            $table->string('business_register', 50)->nullable();
            $table->string('address', 255)->nullable();
            $table->string('phone', 50)->nullable();
            $table->timestamps();
        });

        Schema::create('project_lots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->unsignedTinyInteger('tranche')->default(1);
            $table->unsignedSmallInteger('lot_number');
            $table->string('designation', 500);
            $table->string('designation_ar', 500)->nullable();
            $table->enum('kind', ['travaux', 'equipement', 'etude', 'autre'])->default('travaux');
            $table->enum('state', [
                'receptionne',
                'en_cours_realisation',
                'en_cours_lancement',
                'non_lance',
            ])->default('non_lance')->index();

            // Contracted amount once a marché exists; estimation before that.
            $table->decimal('amount_da', 18, 2)->default(0);
            $table->decimal('estimation_da', 18, 2)->nullable();
            $table->string('estimated_by', 255)->nullable();
            $table->enum('estimation_basis', ['bet', 'consultation', 'autre'])->nullable();

            $table->enum('blocking_reason', [
                'visa_refuse_plafond',
                'attente_visa_controle_financier',
                'infructueux_offres_excessives',
                'attribution_provisoire',
                'autre',
            ])->nullable();
            $table->text('blocking_note_ar')->nullable();

            $table->unsignedSmallInteger('delai_months')->nullable();
            $table->date('launch_date')->nullable();
            $table->decimal('progress_pct', 5, 2)->default(0);
            $table->date('reception_date')->nullable();
            $table->foreignId('contractor_id')->nullable()->constrained('contractors')->nullOnDelete();
            $table->text('observations')->nullable();
            $table->timestamps();

            $table->unique(['project_id', 'tranche', 'lot_number']);
        });

        Schema::create('lot_milestones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lot_id')->constrained('project_lots')->cascadeOnDelete();
            $table->enum('type', [
                'cdc_en_elaboration',
                'cdc_approuve_commission',
                'appel_offres',
                'infructuosite',
                'attribution_provisoire',
                'annulation_attribution',
                'appel_offres_2',
                'approbation_marche_commission',
                'engagement_controleur_financier',
                'ods_notification',
            ]);
            $table->date('date')->nullable();
            $table->string('reference', 255)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['lot_id', 'type']);
        });

        // Backfill the legacy single-contractor columns on projects.
        $legacy = DB::table('projects')
            ->whereNotNull('company_name')
            ->where('company_name', '!=', '')
            ->select('company_name', 'nif', 'business_register')
            ->distinct()
            ->get();

        foreach ($legacy as $row) {
            DB::table('contractors')->insert([
                'name' => $row->company_name,
                'legal_form' => 'autre',
                'nif' => $row->nif,
                'business_register' => $row->business_register,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('lot_milestones');
        Schema::dropIfExists('project_lots');
        Schema::dropIfExists('contractors');
    }
};
