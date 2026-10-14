<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Columns the DGB "ETAT PHYSIQUE ET FINANCIER GLOBAL" canevas prints
 * (letters below refer to the spreadsheet columns).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->unsignedInteger('nbr_operations')->default(1)->after('capacity');          // E
            $table->string('individualisation_number', 100)->nullable()->after('nbr_operations'); // F
            $table->date('individualisation_date')->nullable()->after('individualisation_number');
            $table->unsignedSmallInteger('notification_year')->nullable()->after('individualisation_date'); // I
            $table->enum('titre', ['t3', 't4'])->nullable()->after('notification_year');       // J
            $table->enum('managed_by', ['centrale', 'dass', 'dep'])->nullable()->after('titre'); // Z

            $table->date('reception_provisoire_date')->nullable()->after('managed_by');        // X
            $table->date('reception_definitive_date')->nullable()->after('reception_provisoire_date'); // Y

            $table->decimal('cp_notification_current_year_da', 18, 2)->nullable()->after('cp_da'); // AA
            $table->decimal('cp_reliquat_da', 18, 2)->nullable()->after('cp_notification_current_year_da'); // AB
            $table->decimal('cp_besoins_da', 18, 2)->nullable()->after('cp_reliquat_da');      // AC

            $table->text('observations')->nullable()->after('cp_besoins_da');                  // AD
            $table->boolean('is_closed')->default(false)->after('observations');               // AE (CLOTUREE)
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn([
                'nbr_operations', 'individualisation_number', 'individualisation_date',
                'notification_year', 'titre', 'managed_by',
                'reception_provisoire_date', 'reception_definitive_date',
                'cp_notification_current_year_da', 'cp_reliquat_da', 'cp_besoins_da',
                'observations', 'is_closed',
            ]);
        });
    }
};
