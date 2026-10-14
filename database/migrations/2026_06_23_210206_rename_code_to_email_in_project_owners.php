<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('project_owners', function (Blueprint $table) {
            $table->dropColumn('code');
        });

        Schema::table('project_owners', function (Blueprint $table) {
            $table->string('email', 255)->nullable()->unique()->after('name');
        });
    }

    public function down(): void
    {
        Schema::table('project_owners', function (Blueprint $table) {
            $table->dropColumn('email');
        });

        Schema::table('project_owners', function (Blueprint $table) {
            $table->string('code', 50)->nullable()->after('id');
        });
    }
};
