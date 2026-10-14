<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ae_cp_envelopes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->year('year');
            $table->decimal('ae_notified_m', 15, 2)->default(0);
            $table->decimal('cp_notified_m', 15, 2)->default(0);
            $table->timestamps();

            $table->unique(['project_id', 'year']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ae_cp_envelopes');
    }
};
