<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('risks_files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->text('notes')->nullable();
            $table->string('file', 500);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('risks_files');
    }
};
