<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('network_types', function (Blueprint $table) {
            $table->id();
            $table->string('code', 80)->unique();
            $table->string('label_fr', 255);
            $table->string('label_en', 255);
            $table->unsignedSmallInteger('sort_order')->default(0)->index();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });

        $now = now();
        foreach ([
            ['code' => 'gaz', 'label_fr' => 'Gaz', 'label_en' => 'Gas', 'sort_order' => 10],
            ['code' => 'electricite', 'label_fr' => 'Électricité', 'label_en' => 'Electricity', 'sort_order' => 20],
            ['code' => 'aep', 'label_fr' => 'AEP', 'label_en' => 'AEP', 'sort_order' => 30],
            ['code' => 'assainissement', 'label_fr' => 'Assainissement', 'label_en' => 'Sanitation', 'sort_order' => 40],
        ] as $row) {
            DB::table('network_types')->insert($row + [
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE project_network_links MODIFY network VARCHAR(80) NOT NULL');
        } else {
            Schema::table('project_network_links', function (Blueprint $table) {
                $table->string('network', 80)->change();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('network_types');
    }
};
