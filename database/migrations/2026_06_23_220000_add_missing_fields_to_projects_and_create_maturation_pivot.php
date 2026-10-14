<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->string('commune_code', 5)->nullable()->after('wilaya_id');
            $table->decimal('ae', 15, 2)->nullable()->after('project_duration_months');
            $table->decimal('cp', 15, 2)->nullable()->after('ae');
            $table->decimal('cp_year_n', 15, 2)->nullable()->after('cp');
            $table->decimal('cp_year_n1', 15, 2)->nullable()->after('cp_year_n');
            $table->decimal('cp_year_n2', 15, 2)->nullable()->after('cp_year_n1');
            $table->string('company_name', 255)->nullable()->after('project_owner_id');
            $table->string('nif', 50)->nullable()->after('company_name');
            $table->string('business_register', 50)->nullable()->after('nif');
        });

        Schema::create('maturation_project', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->foreignId('maturation_checklist_definition_id')->constrained('maturation_checklist_definitions')->cascadeOnDelete();
            $table->boolean('checked')->default(true);
            $table->timestamps();

            $table->unique(['project_id', 'maturation_checklist_definition_id'], 'maturation_project_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('maturation_project');

        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn([
                'commune_code',
                'ae',
                'cp',
                'cp_year_n',
                'cp_year_n1',
                'cp_year_n2',
                'company_name',
                'nif',
                'business_register',
            ]);
        });
    }
};
