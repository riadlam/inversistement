<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cp_payments', function (Blueprint $table) {
            $table->string('invoice_by_ministry')->nullable()->after('invoice');
            $table->date('paid_at')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('cp_payments', function (Blueprint $table) {
            $table->dropColumn('invoice_by_ministry');
            $table->date('paid_at')->nullable(false)->change();
        });
    }
};
