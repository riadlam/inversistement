<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Fields the FICHE DE SUIVI DU PROJET prints that had no column yet.
 *
 * Reception targets are recorded as quarters ("1er trim 2017") on the paper
 * fiche, so they are stored as quarter + year rather than as dates.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->string('short_label', 100)->nullable()->after('title_ar');
            $table->string('operation_prefix', 10)->nullable()->after('fip_id');
            $table->string('operation_number', 100)->nullable()->after('operation_prefix');

            $table->enum('phase', [
                'etudes', 'lancement', 'realisation', 'arret',
                'acheve', 'en_equipement', 'acheve_equipe',
            ])->nullable()->after('status');

            $table->decimal('equipment_progress_pct', 5, 2)->nullable()->after('phase');
            $table->unsignedInteger('capacity')->nullable()->after('equipment_progress_pct');

            $table->foreignId('daira_id')->nullable()->after('wilaya_id')->constrained('dairas')->nullOnDelete();
            $table->foreignId('secteur_id')->nullable()->after('daira_id')->constrained('secteurs')->nullOnDelete();
            $table->foreignId('sous_secteur_id')->nullable()->after('secteur_id')->constrained('sous_secteurs')->nullOnDelete();
            $table->foreignId('chapitre_id')->nullable()->after('sous_secteur_id')->constrained('chapitres')->nullOnDelete();
            $table->foreignId('programme_type_id')->nullable()->after('chapitre_id')->constrained('programme_types')->nullOnDelete();
            $table->foreignId('programme_structure_id')->nullable()->after('programme_type_id')->constrained('programme_structures')->nullOnDelete();
            $table->foreignId('sous_programme_id')->nullable()->after('programme_structure_id')->constrained('sous_programmes')->nullOnDelete();

            $table->string('decision_programme_ref', 100)->nullable()->after('sous_programme_id');
            $table->date('inscription_date')->nullable()->after('decision_programme_ref');

            $table->date('initial_launch_date')->nullable()->after('ods_date');
            $table->enum('initial_launch_scope', ['partiel', 'global'])->nullable()->after('initial_launch_date');

            $table->unsignedTinyInteger('works_reception_quarter')->nullable()->after('initial_launch_scope');
            $table->unsignedSmallInteger('works_reception_year')->nullable()->after('works_reception_quarter');
            $table->unsignedTinyInteger('equipment_reception_quarter')->nullable()->after('works_reception_year');
            $table->unsignedSmallInteger('equipment_reception_year')->nullable()->after('equipment_reception_quarter');
            $table->unsignedTinyInteger('inauguration_quarter')->nullable()->after('equipment_reception_year');
            $table->unsignedSmallInteger('inauguration_year')->nullable()->after('inauguration_quarter');

            $table->date('fiche_updated_at')->nullable()->after('inauguration_year');
        });

        Schema::create('project_land', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->unique()->constrained('projects')->cascadeOnDelete();
            $table->string('affectation_decision_ref', 100)->nullable();
            $table->date('affectation_decision_date')->nullable();
            $table->unsignedInteger('superficie_ha')->default(0);
            $table->unsignedInteger('superficie_a')->default(0);
            $table->unsignedInteger('superficie_ca')->default(0);
            $table->string('location', 255)->nullable();
            $table->timestamps();
        });

        Schema::create('project_design_missions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->string('bet_name', 255)->nullable();
            $table->date('esquisse_approved_at')->nullable();
            $table->date('avant_projet_approved_at')->nullable();
            $table->date('projet_execution_approved_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('project_network_links', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->enum('network', ['gaz', 'electricite', 'aep', 'assainissement']);
            $table->boolean('connected')->default(false);
            $table->text('motif')->nullable();
            $table->timestamps();

            $table->unique(['project_id', 'network']);
        });

        Schema::create('project_pending_expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->unsignedSmallInteger('lot_number')->nullable();
            $table->string('designation', 500);
            $table->decimal('amount_da', 18, 2)->default(0);
            $table->enum('kind', ['avenant', 'travaux_supplementaires', 'complementaires', 'autre'])->default('autre');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_pending_expenses');
        Schema::dropIfExists('project_network_links');
        Schema::dropIfExists('project_design_missions');
        Schema::dropIfExists('project_land');

        Schema::table('projects', function (Blueprint $table) {
            foreach ([
                'daira_id', 'secteur_id', 'sous_secteur_id', 'chapitre_id',
                'programme_type_id', 'programme_structure_id', 'sous_programme_id',
            ] as $fk) {
                $table->dropConstrainedForeignId($fk);
            }

            $table->dropColumn([
                'short_label', 'operation_prefix', 'operation_number', 'phase',
                'equipment_progress_pct', 'capacity', 'decision_programme_ref',
                'inscription_date', 'initial_launch_date', 'initial_launch_scope',
                'works_reception_quarter', 'works_reception_year',
                'equipment_reception_quarter', 'equipment_reception_year',
                'inauguration_quarter', 'inauguration_year', 'fiche_updated_at',
            ]);
        });
    }
};
