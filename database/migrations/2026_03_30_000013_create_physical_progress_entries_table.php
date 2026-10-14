<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('physical_progress_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->decimal('progress_pct', 5, 1);
            $table->foreignId('reported_by')->constrained('users');
            $table->enum('source', ['inspector', 'consultant', 'manager'])->default('manager');
            $table->text('notes')->nullable();
            $table->date('reported_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('physical_progress_entries');
    }
};
