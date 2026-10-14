<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE projects MODIFY COLUMN status ENUM(
            'draft',
            'maturation',
            'submitted',
            'in_arbitration',
            'pending_visa',
            'approved',
            'in_execution',
            'completed',
            'closed'
        ) NOT NULL DEFAULT 'draft'");
    }

    public function down(): void
    {
        DB::table('projects')
            ->where('status', 'pending_visa')
            ->update(['status' => 'approved']);

        DB::statement("ALTER TABLE projects MODIFY COLUMN status ENUM(
            'draft',
            'maturation',
            'submitted',
            'in_arbitration',
            'approved',
            'in_execution',
            'completed',
            'closed'
        ) NOT NULL DEFAULT 'draft'");
    }
};
