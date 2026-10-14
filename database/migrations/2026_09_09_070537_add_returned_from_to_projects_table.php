<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->string('returned_from', 20)->nullable()->after('rejection_reason');
        });

        // Infer stage for dossiers already returned before this column existed.
        $visaProjectIds = DB::table('alerts')
            ->where('signature', 'workflow.arbitration.pending_visa')
            ->pluck('project_id');

        if ($visaProjectIds->isNotEmpty()) {
            DB::table('projects')
                ->whereIn('id', $visaProjectIds)
                ->whereNotNull('rejection_reason')
                ->where('rejection_reason', '!=', '')
                ->whereIn('status', ['draft', 'maturation'])
                ->update(['returned_from' => 'visa']);
        }

        DB::table('projects')
            ->whereNotNull('rejection_reason')
            ->where('rejection_reason', '!=', '')
            ->whereIn('status', ['draft', 'maturation'])
            ->whereNull('returned_from')
            ->update(['returned_from' => 'arbitration']);
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn('returned_from');
        });
    }
};
