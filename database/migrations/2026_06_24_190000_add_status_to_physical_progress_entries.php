<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('physical_progress_entries', function (Blueprint $table) {
            $table->enum('status', ['pending', 'accepted', 'declined'])->default('pending')->after('notes');
            $table->foreignId('accepted_by')->nullable()->constrained('users')->nullOnDelete()->after('status');
            $table->foreignId('declined_by')->nullable()->constrained('users')->nullOnDelete()->after('accepted_by');
        });
    }

    public function down(): void
    {
        Schema::table('physical_progress_entries', function (Blueprint $table) {
            $table->dropForeign(['accepted_by']);
            $table->dropForeign(['declined_by']);
            $table->dropColumn(['status', 'accepted_by', 'declined_by']);
        });
    }
};
