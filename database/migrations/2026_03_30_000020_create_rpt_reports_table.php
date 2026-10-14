<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rpt_reports', function (Blueprint $table) {
            $table->id();
            $table->year('year');
            $table->tinyInteger('quarter')->unsigned();
            $table->timestamp('generated_at');
            $table->foreignId('generated_by')->constrained('users');
            $table->json('data');
            $table->string('file_path', 500)->nullable();
            $table->timestamps();

            $table->unique(['year', 'quarter']);
            $table->index('generated_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rpt_reports');
    }
};
