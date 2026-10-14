<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_closures', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->unique()->constrained('projects')->cascadeOnDelete();
            $table->decimal('final_ae_engaged_m', 15, 2);
            $table->decimal('final_cp_consumed_m', 15, 2);
            $table->decimal('final_rar_m', 15, 2);
            $table->string('closure_decision_ref', 100)->nullable();
            $table->foreignId('approved_by')->constrained('users');
            $table->timestamp('approved_at');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_closures');
    }
};
