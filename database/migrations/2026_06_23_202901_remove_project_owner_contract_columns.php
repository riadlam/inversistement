<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('project_owners', function (Blueprint $table) {
            $table->dropColumn(['amount_committed_m', 'ods_date', 'project_duration_months']);
        });

        Schema::table('projects', function (Blueprint $table) {
            $table->decimal('amount_committed_m', 15, 2)->nullable()->after('project_owner_id');
            $table->date('ods_date')->nullable()->after('amount_committed_m');
            $table->unsignedInteger('project_duration_months')->nullable()->after('ods_date');
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn(['amount_committed_m', 'ods_date', 'project_duration_months']);
        });

        Schema::table('project_owners', function (Blueprint $table) {
            $table->decimal('amount_committed_m', 15, 2)->nullable();
            $table->date('ods_date')->nullable();
            $table->unsignedInteger('project_duration_months')->nullable();
        });
    }
};
