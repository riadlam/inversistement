<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Store money in raw dinars, not millions.
 *
 * The TDR quotes AE/CP in millions of DA, but every official document the
 * ministry actually produces — the FICHE DE SUIVI and the DGB "ETAT PHYSIQUE ET
 * FINANCIER GLOBAL" canevas — is written in dinars (e.g. "160 000 000,00 DA",
 * and lot amounts like "9 704 617,20 DA" that lose all their significant digits
 * once expressed in millions. Millions are a presentation choice, so they now
 * belong in the view layer only.
 *
 * Existing values were entered as millions and are multiplied accordingly.
 */
return new class extends Migration
{
    private const FACTOR = 1_000_000;

    /** table => [old column => new column] */
    private const COLUMNS = [
        'projects' => [
            'ae' => 'ae_da',
            'cp' => 'cp_da',
            'amount_committed_m' => 'amount_committed_da',
        ],
        'ae_cp_envelopes' => [
            'ae_notified_m' => 'ae_notified_da',
            'cp_notified_m' => 'cp_notified_da',
        ],
        'ae_engagements' => [
            'amount_m' => 'amount_da',
        ],
        'ae_revaluations' => [
            'requested_amount_m' => 'requested_amount_da',
        ],
        'cp_payments' => [
            'amount_m' => 'amount_da',
        ],
        'cp_projections' => [
            'amount' => 'amount_da',
        ],
        'movements' => [
            'amount_m' => 'amount_da',
        ],
        'project_closures' => [
            'final_ae_engaged_m' => 'final_ae_engaged_da',
            'final_cp_consumed_m' => 'final_cp_consumed_da',
            'final_rar_m' => 'final_rar_da',
        ],
    ];

    public function up(): void
    {
        foreach (self::COLUMNS as $table => $map) {
            foreach ($map as $old => $new) {
                if (! Schema::hasColumn($table, $old)) {
                    continue;
                }

                // decimal(18,2) keeps national aggregates in range: the whole
                // sector programme is a few hundred billion dinars.
                Schema::table($table, function (Blueprint $t) use ($new, $old) {
                    $t->decimal($new, 18, 2)->nullable()->after($old);
                });

                DB::table($table)->update([
                    $new => DB::raw("`{$old}` * ".self::FACTOR),
                ]);

                Schema::table($table, function (Blueprint $t) use ($old) {
                    $t->dropColumn($old);
                });
            }
        }

        // These were dropped by an earlier migration on some installs but are
        // still listed as fillable; make sure they are gone for good.
        foreach (['cp_year_n', 'cp_year_n1', 'cp_year_n2'] as $column) {
            if (Schema::hasColumn('projects', $column)) {
                Schema::table('projects', fn (Blueprint $t) => $t->dropColumn($column));
            }
        }
    }

    public function down(): void
    {
        foreach (self::COLUMNS as $table => $map) {
            foreach ($map as $old => $new) {
                if (! Schema::hasColumn($table, $new)) {
                    continue;
                }

                Schema::table($table, function (Blueprint $t) use ($new, $old) {
                    $t->decimal($old, 15, 2)->nullable()->after($new);
                });

                DB::table($table)->update([
                    $old => DB::raw("`{$new}` / ".self::FACTOR),
                ]);

                Schema::table($table, function (Blueprint $t) use ($new) {
                    $t->dropColumn($new);
                });
            }
        }
    }
};
