<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Storage for the configurable calculation engine. Every ratio, weight and
 * total the platform reports is an editable expression here rather than PHP,
 * so the ministry can change the arithmetic the way it used to change a
 * spreadsheet formula.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('calculation_rules', function (Blueprint $table) {
            $table->id();
            $table->string('key', 100)->unique();
            $table->string('label_fr', 255);
            $table->string('label_ar', 255)->nullable();
            $table->string('category', 50)->index();

            // The expression itself, evaluated by symfony/expression-language.
            $table->text('expression');

            // Declared inputs: [{key, label, unit}] — drives the admin UI hints.
            $table->json('variables')->nullable();

            $table->string('unit', 20)->default('number');   // da | pct | days | points | number
            $table->unsignedTinyInteger('precision')->default(2);
            $table->decimal('clamp_min', 20, 4)->nullable();
            $table->decimal('clamp_max', 20, 4)->nullable();

            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('version')->default(1);
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('calculation_parameters', function (Blueprint $table) {
            $table->id();
            $table->string('key', 100)->unique();
            $table->string('label_fr', 255);
            $table->string('category', 50)->index();
            $table->decimal('value', 20, 4);
            $table->string('type', 20)->default('decimal');  // int | decimal | bool
            $table->decimal('min', 20, 4)->nullable();
            $table->decimal('max', 20, 4)->nullable();
            $table->string('unit', 20)->nullable();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        // Version history, so a bad edit can be traced and rolled back.
        Schema::create('calculation_rule_revisions', function (Blueprint $table) {
            $table->id();
            $table->string('target_type', 20);   // rule | parameter
            $table->string('target_key', 100)->index();
            $table->text('old_value')->nullable();
            $table->text('new_value')->nullable();
            $table->unsignedInteger('version')->default(1);
            $table->foreignId('changed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('created_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('calculation_rule_revisions');
        Schema::dropIfExists('calculation_parameters');
        Schema::dropIfExists('calculation_rules');
    }
};
