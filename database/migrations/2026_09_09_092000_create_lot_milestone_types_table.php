<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lot_milestone_types', function (Blueprint $table) {
            $table->id();
            $table->string('code', 80)->unique();
            $table->string('label_fr', 255);
            $table->string('label_en', 255);
            $table->unsignedSmallInteger('sort_order')->default(0)->index();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });

        $now = now();
        $defaults = [
            ['code' => 'cdc_en_elaboration', 'label_fr' => "Cahier des charges en cours d'élaboration", 'label_en' => 'Specifications currently being developed', 'sort_order' => 10],
            ['code' => 'cdc_approuve_commission', 'label_fr' => 'Cahier des charges approuvé par la commission', 'label_en' => 'Specifications approved by the commission', 'sort_order' => 20],
            ['code' => 'appel_offres', 'label_fr' => "Avis d'appel d'offres", 'label_en' => 'Call for tenders notice', 'sort_order' => 30],
            ['code' => 'infructuosite', 'label_fr' => 'Infructuosité', 'label_en' => 'Unsuccessful tender', 'sort_order' => 40],
            ['code' => 'attribution_provisoire', 'label_fr' => 'Attribution provisoire', 'label_en' => 'Provisional award', 'sort_order' => 50],
            ['code' => 'annulation_attribution', 'label_fr' => "Annulation de l'attribution", 'label_en' => 'Award cancellation', 'sort_order' => 60],
            ['code' => 'appel_offres_2', 'label_fr' => "2ème avis d'appel d'offres", 'label_en' => '2nd call for tenders notice', 'sort_order' => 70],
            ['code' => 'approbation_marche_commission', 'label_fr' => 'Approbation du marché par la commission', 'label_en' => 'Contract approval by the commission', 'sort_order' => 80],
            ['code' => 'engagement_controleur_financier', 'label_fr' => 'Engagement chez le contrôleur financier', 'label_en' => 'Engagement with the financial controller', 'sort_order' => 90],
            ['code' => 'ods_notification', 'label_fr' => "Notification de l'ODS", 'label_en' => 'ODS notification', 'sort_order' => 100],
        ];

        foreach ($defaults as $row) {
            DB::table('lot_milestone_types')->insert($row + [
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        // Drop MySQL enum so admin can add new step codes freely.
        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE lot_milestones MODIFY type VARCHAR(80) NOT NULL');
        } else {
            Schema::table('lot_milestones', function (Blueprint $table) {
                $table->string('type', 80)->change();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('lot_milestone_types');
    }
};
