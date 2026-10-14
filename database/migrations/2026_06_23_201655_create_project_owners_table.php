<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_owners', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->nullable();
            $table->string('name', 255);
            $table->string('company_name', 255)->nullable();
            $table->string('nif', 50)->nullable();
            $table->string('business_register', 50)->nullable();
            $table->decimal('amount_committed_m', 15, 2)->nullable();
            $table->date('ods_date')->nullable();
            $table->unsignedInteger('project_duration_months')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_owners');
    }
};
