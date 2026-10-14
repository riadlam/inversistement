<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Budget nomenclature reference tables.
 *
 * The DGB "ETAT PHYSIQUE ET FINANCIER GLOBAL" canevas classifies every
 * operation by secteur, sous-secteur, chapitre, type and structure de
 * programme, none of which existed as reference data.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('secteurs', function (Blueprint $table) {
            $table->id();
            $table->string('code', 10)->unique();
            $table->string('label_fr', 255);
            $table->string('label_ar', 255)->nullable();
            $table->timestamps();
        });

        Schema::create('sous_secteurs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('secteur_id')->constrained('secteurs')->cascadeOnDelete();
            $table->string('code', 10);
            $table->string('label_fr', 255);
            $table->string('label_ar', 255)->nullable();
            $table->timestamps();

            $table->unique(['secteur_id', 'code']);
        });

        Schema::create('chapitres', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->unique();
            $table->string('label_fr', 255);
            $table->string('label_ar', 255)->nullable();
            $table->timestamps();
        });

        // PACE, PCCE, PN, FSDRS...
        Schema::create('programme_types', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->unique();
            $table->string('label_fr', 255);
            $table->timestamps();
        });

        // P1 to P4 (structure du programme).
        Schema::create('programme_structures', function (Blueprint $table) {
            $table->id();
            $table->string('code', 10)->unique();
            $table->string('label_fr', 255);
            $table->timestamps();
        });

        Schema::create('sous_programmes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('programme_structure_id')->nullable()->constrained('programme_structures')->nullOnDelete();
            $table->string('code', 20);
            $table->string('label_fr', 255);
            $table->timestamps();

            $table->unique(['programme_structure_id', 'code']);
        });

        Schema::create('dairas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wilaya_id')->constrained('wilayas')->cascadeOnDelete();
            $table->string('code', 10);
            $table->string('name_fr', 255);
            $table->string('name_ar', 255)->nullable();
            $table->timestamps();

            $table->unique(['wilaya_id', 'code']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dairas');
        Schema::dropIfExists('sous_programmes');
        Schema::dropIfExists('programme_structures');
        Schema::dropIfExists('programme_types');
        Schema::dropIfExists('chapitres');
        Schema::dropIfExists('sous_secteurs');
        Schema::dropIfExists('secteurs');
    }
};
