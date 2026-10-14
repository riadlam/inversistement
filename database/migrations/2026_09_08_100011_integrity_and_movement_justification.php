<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Integrity and leftover unit conversions:
 *  - one sequence number per operation for the AP block
 *  - leftover `cp_requests` millions columns renamed to dinars
 *  - a justification field on movements (TDR Fiche 05)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ae_revaluations', function (Blueprint $table) {
            $table->unique(['project_id', 'sequence'], 'ae_revaluations_project_sequence_unique');
        });

        if (Schema::hasColumn('cp_requests', 'amount_m')) {
            Schema::table('cp_requests', function (Blueprint $table) {
                $table->decimal('amount_da', 18, 2)->nullable()->after('amount_m');
                $table->decimal('rar_at_request_da', 18, 2)->nullable()->after('rar_at_request_m');
            });

            foreach (\Illuminate\Support\Facades\DB::table('cp_requests')->get() as $row) {
                \Illuminate\Support\Facades\DB::table('cp_requests')->where('id', $row->id)->update([
                    'amount_da' => ((float) $row->amount_m) * 1_000_000,
                    'rar_at_request_da' => $row->rar_at_request_m === null
                        ? null
                        : ((float) $row->rar_at_request_m) * 1_000_000,
                ]);
            }

            Schema::table('cp_requests', function (Blueprint $table) {
                $table->dropColumn(['amount_m', 'rar_at_request_m']);
            });
        }

        if (! Schema::hasColumn('movements', 'justification')) {
            Schema::table('movements', function (Blueprint $table) {
                $table->text('justification')->nullable()->after('type');
            });
        }
    }

    public function down(): void
    {
        Schema::table('ae_revaluations', function (Blueprint $table) {
            $table->dropUnique('ae_revaluations_project_sequence_unique');
        });

        if (Schema::hasColumn('cp_requests', 'amount_da')) {
            Schema::table('cp_requests', function (Blueprint $table) {
                $table->decimal('amount_m', 15, 2)->nullable()->after('amount_da');
                $table->decimal('rar_at_request_m', 15, 2)->nullable()->after('rar_at_request_da');
            });

            Schema::table('cp_requests', function (Blueprint $table) {
                $table->dropColumn(['amount_da', 'rar_at_request_da']);
            });
        }

        if (Schema::hasColumn('movements', 'justification')) {
            Schema::table('movements', function (Blueprint $table) {
                $table->dropColumn('justification');
            });
        }
    }
};
