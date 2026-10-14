<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->foreignId('project_owner_id')->nullable()->constrained('project_owners')->nullOnDelete()->after('establishment_id');

            $table->dropColumn([
                'project_owner',
                'company_name',
                'nif',
                'business_register',
                'amount_committed_m',
                'ods_date',
                'project_duration_months',
            ]);
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->string('project_owner', 255)->nullable();
            $table->string('company_name', 255)->nullable();
            $table->string('nif', 50)->nullable();
            $table->string('business_register', 50)->nullable();
            $table->decimal('amount_committed_m', 15, 2)->nullable();
            $table->date('ods_date')->nullable();
            $table->unsignedInteger('project_duration_months')->nullable();

            $table->dropForeign(['project_owner_id']);
            $table->dropColumn('project_owner_id');
        });
    }
};
