<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('design_offices', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255);
            $table->string('city', 100)->nullable();
            $table->string('phone', 50)->nullable();
            $table->string('email', 255)->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0)->index();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });

        $now = now();
        $seed = [
            ['name' => 'BET Atlas Ingénierie', 'city' => 'Alger', 'sort_order' => 10],
            ['name' => 'Bureau d’études Sahara Technique', 'city' => 'Biskra', 'sort_order' => 20],
            ['name' => 'BET Numidia Architecture', 'city' => 'Constantine', 'sort_order' => 30],
            ['name' => 'Cabinet El Djazair Études', 'city' => 'Oran', 'sort_order' => 40],
            ['name' => 'BET Hydraulique & Structure Sud', 'city' => 'Ouargla', 'sort_order' => 50],
            ['name' => 'Études & Projets Aurès', 'city' => 'Batna', 'sort_order' => 60],
            ['name' => 'BET Médina Conception', 'city' => 'Tlemcen', 'sort_order' => 70],
            ['name' => 'Ingénierie Publique Setif', 'city' => 'Sétif', 'sort_order' => 80],
        ];

        foreach ($seed as $row) {
            DB::table('design_offices')->insert($row + [
                'phone' => null,
                'email' => null,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        Schema::table('project_design_missions', function (Blueprint $table) {
            $table->foreignId('design_office_id')
                ->nullable()
                ->after('project_id')
                ->constrained('design_offices')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('project_design_missions', function (Blueprint $table) {
            $table->dropConstrainedForeignId('design_office_id');
        });

        Schema::dropIfExists('design_offices');
    }
};
