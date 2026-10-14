<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cp_projections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->unsignedTinyInteger('year_offset');
            $table->decimal('amount', 15, 2);
            $table->timestamps();

            $table->unique(['project_id', 'year_offset'], 'cp_projections_unique');
        });

        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn(['cp_year_n', 'cp_year_n1', 'cp_year_n2']);
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->decimal('cp_year_n', 15, 2)->nullable()->after('cp');
            $table->decimal('cp_year_n1', 15, 2)->nullable()->after('cp_year_n');
            $table->decimal('cp_year_n2', 15, 2)->nullable()->after('cp_year_n1');
        });

        Schema::dropIfExists('cp_projections');
    }
};
