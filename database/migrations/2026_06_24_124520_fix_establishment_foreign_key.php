<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::getConnection()->getDriverName() !== 'mysql') {
            return;
        }

        try {
            DB::statement('ALTER TABLE projects DROP FOREIGN KEY projects_establishment_id_foreign');
        } catch (\Exception $e) {
            // FK may already have been dropped
        }
    }

    public function down(): void
    {
        // Cannot restore the FK with mismatched types automatically
    }
};
