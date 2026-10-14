<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cp_payments', function (Blueprint $table) {
            $table->string('invoice')->nullable()->after('description');
            $table->enum('status', ['validated', 'rejected'])->nullable()->after('invoice');
            $table->text('rejection_cause')->nullable()->after('status');
            $table->string('mandate_number')->nullable()->after('rejection_cause');
        });
    }

    public function down(): void
    {
        Schema::table('cp_payments', function (Blueprint $table) {
            $table->dropColumn(['invoice', 'status', 'rejection_cause', 'mandate_number']);
        });
    }
};
