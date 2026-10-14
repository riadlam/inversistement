<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('spu_scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->unsignedInteger('score')->index();
            $table->unsignedInteger('cp_score')->default(0);
            $table->unsignedInteger('physical_score')->default(0);
            $table->unsignedInteger('delays_score')->default(0);
            $table->unsignedInteger('file_score')->default(0);
            $table->enum('traffic_light', ['green', 'orange', 'red'])->index();
            $table->date('computed_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('spu_scores');
    }
};
