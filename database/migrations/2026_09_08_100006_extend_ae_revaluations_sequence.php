<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * The "AP initiale -> AP actuelle" block of the fiche numbers each
 * reevaluation (up to five) and stamps it with a year and a decision reference.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ae_revaluations', function (Blueprint $table) {
            $table->unsignedTinyInteger('sequence')->default(1)->after('project_id');
            $table->unsignedSmallInteger('year')->nullable()->after('sequence');
            $table->string('decision_ref', 100)->nullable()->after('year');
        });

        Schema::table('projects', function (Blueprint $table) {
            $table->unsignedTinyInteger('reevaluation_count')->default(0)->after('ae_da');
        });

        // Give pre-existing rows a stable sequence per project.
        $grouped = DB::table('ae_revaluations')->orderBy('id')->get()->groupBy('project_id');

        foreach ($grouped as $rows) {
            foreach ($rows->values() as $index => $row) {
                DB::table('ae_revaluations')->where('id', $row->id)->update([
                    'sequence' => $index + 1,
                    'year' => $row->created_at ? (int) date('Y', strtotime($row->created_at)) : null,
                ]);
            }
        }

        foreach (DB::table('projects')->pluck('id') as $projectId) {
            $count = DB::table('ae_revaluations')
                ->where('project_id', $projectId)
                ->where('status', 'approved')
                ->count();

            DB::table('projects')->where('id', $projectId)->update([
                'reevaluation_count' => $count,
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('ae_revaluations', function (Blueprint $table) {
            $table->dropColumn(['sequence', 'year', 'decision_ref']);
        });

        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn('reevaluation_count');
        });
    }
};
