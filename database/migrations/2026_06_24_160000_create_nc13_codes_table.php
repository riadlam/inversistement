<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('nc13_codes', function (Blueprint $table) {
            $table->id();
            $table->string('code', 10);
            $table->string('label', 255);
            $table->string('category', 100);
            $table->string('class_label', 100);
            $table->string('budget_type', 10)->default('T3');
            $table->timestamps();
        });

        $now = now();
        DB::table('nc13_codes')->insert([
            ['code' => '213', 'label' => 'Constructions (Buildings)',        'category' => 'Construction', 'class_label' => '2 — Immobilisation', 'budget_type' => 'T3', 'created_at' => $now, 'updated_at' => $now],
            ['code' => '214', 'label' => 'Roads & Highways',                  'category' => 'Construction', 'class_label' => '2 — Immobilisation', 'budget_type' => 'T3', 'created_at' => $now, 'updated_at' => $now],
            ['code' => '215', 'label' => 'Medical Equipment',                 'category' => 'Equipment',    'class_label' => '3 — Equipment',      'budget_type' => 'T3', 'created_at' => $now, 'updated_at' => $now],
            ['code' => '216', 'label' => 'Industrial Machinery',              'category' => 'Equipment',    'class_label' => '3 — Equipment',      'budget_type' => 'T3', 'created_at' => $now, 'updated_at' => $now],
            ['code' => '217', 'label' => 'Feasibility Studies',               'category' => 'Study',        'class_label' => '1 — Studies',        'budget_type' => 'T4', 'created_at' => $now, 'updated_at' => $now],
            ['code' => '218', 'label' => 'IT Systems',                        'category' => 'IT',           'class_label' => '4 — IT Systems',     'budget_type' => 'T3', 'created_at' => $now, 'updated_at' => $now],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('nc13_codes');
    }
};
