<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::getConnection()->getDriverName() !== 'mysql') {
            return;
        }

        DB::statement("ALTER TABLE cp_payments MODIFY COLUMN status ENUM('pending', 'validated', 'rejected') DEFAULT 'pending'");
        DB::statement('ALTER TABLE cp_payments MODIFY COLUMN payment_type ENUM("regular", "credit_request", "revaluation") NULL');
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE cp_payments MODIFY COLUMN status ENUM('validated', 'rejected') NULL");
        DB::statement("ALTER TABLE cp_payments MODIFY COLUMN payment_type ENUM('regular', 'credit_request', 'revaluation') NOT NULL DEFAULT 'regular'");
    }
};
