<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * `commune` and `tab_etablissement` arrived as ad-hoc phpMyAdmin imports, so a
 * `migrate:fresh` used to wipe them and leave the fiche without a commune or
 * an établissement to point at. They are declared here so the schema is
 * reproducible from migrations alone.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('commune')) {
            Schema::create('commune', function (Blueprint $table) {
                $table->string('code_comm', 10)->primary();
                $table->string('lib_comm_ar', 255)->nullable();
                $table->string('lib_comm_fr', 255)->nullable();
                $table->string('code_wilaya', 10)->nullable()->index();
            });
        }

        if (! Schema::hasTable('tab_etablissement')) {
            Schema::create('tab_etablissement', function (Blueprint $table) {
                $table->id();
                $table->double('wilaya')->nullable()->index();
                $table->string('Code établissement', 255)->nullable();
                $table->string('libelle', 255)->nullable();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('tab_etablissement');
        Schema::dropIfExists('commune');
    }
};
