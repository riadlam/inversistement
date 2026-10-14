<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Alerts are recomputed on every write, so each rule needs a stable identity
 * to update in place instead of piling up duplicates.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('alerts', function (Blueprint $table) {
            $table->string('signature', 100)->nullable()->after('project_id');
            $table->timestamp('last_seen_at')->nullable()->after('body');

            $table->unique(['project_id', 'signature']);
        });
    }

    public function down(): void
    {
        Schema::table('alerts', function (Blueprint $table) {
            $table->dropUnique(['project_id', 'signature']);
            $table->dropColumn(['signature', 'last_seen_at']);
        });
    }
};
