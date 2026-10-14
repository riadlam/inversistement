<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->string('budget_type', 10)->nullable()->after('status');
            $table->foreignId('nc13_code_id')->nullable()->after('project_owner_id')
                ->constrained('nc13_codes')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropForeign(['nc13_code_id']);
            $table->dropColumn('nc13_code_id');
            $table->dropColumn('budget_type');
        });
    }
};
