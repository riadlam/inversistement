<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->string('fip_id', 50)->unique();
            $table->string('title_fr', 255);
            $table->string('title_ar', 255);
            $table->text('description')->nullable();

            $table->foreignId('programme_id')->constrained('programmes');
            $table->foreignId('wilaya_id')->constrained('wilayas');
            $table->foreignId('establishment_id')->nullable()->constrained('establishments')->nullOnDelete();

            $table->string('project_owner', 255)->nullable();
            $table->enum('nature', ['new', 'extension', 'rehabilitation'])->default('new');

            $table->enum('status', [
                'draft', 'maturation', 'submitted', 'in_arbitration',
                'approved', 'in_execution', 'completed', 'closed',
            ])->default('draft')->index();

            $table->unsignedInteger('project_duration_months')->nullable();
            $table->date('ods_date')->nullable();
            $table->date('deadline')->nullable();

            $table->decimal('amount_committed_m', 15, 2)->nullable();
            $table->string('company_name', 255)->nullable();
            $table->string('nif', 50)->nullable();
            $table->string('business_register', 50)->nullable();

            $table->foreignId('created_by')->constrained('users');
            $table->foreignId('validated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('validated_at')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
