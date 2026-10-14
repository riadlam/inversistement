<?php

namespace Database\Seeders;

use App\Models\Chapitre;
use App\Models\Daira;
use App\Models\ProgrammeStructure;
use App\Models\ProgrammeType;
use App\Models\Secteur;
use App\Models\SousProgramme;
use App\Models\SousSecteur;
use App\Models\Wilaya;
use Illuminate\Database\Seeder;

/**
 * Coded vocabularies the DGB canevas requires as selections rather than free
 * text. Codes are taken verbatim from the sheet's own hint row (K15, L15, M15,
 * N15); labels reflect how the ministry's own operations use them and are
 * editable from the reference screens.
 */
class ReferenceDataSeeder extends Seeder
{
    public function run(): void
    {
        $this->secteurs();
        $this->chapitres();
        $this->programmes();
        $this->dairas();
    }

    private function secteurs(): void
    {
        // The ministry's operations all sit under sector 5.
        $secteur = Secteur::updateOrCreate(
            ['code' => '5'],
            ['label_fr' => 'Infrastructures socio-culturelles', 'label_ar' => 'الهياكل الاجتماعية والثقافية'],
        );

        // K15 of the canevas: "57-62-76".
        $sousSecteurs = [
            ['code' => '57', 'label_fr' => 'Infrastructures administratives', 'label_ar' => 'الهياكل الإدارية'],
            ['code' => '62', 'label_fr' => 'Hydraulique et assainissement', 'label_ar' => 'الموارد المائية والتصفية'],
            ['code' => '76', 'label_fr' => 'Établissements et infrastructures d\'action sociale', 'label_ar' => 'مؤسسات وهياكل العمل الاجتماعي'],
        ];

        foreach ($sousSecteurs as $row) {
            SousSecteur::updateOrCreate(
                ['secteur_id' => $secteur->id, 'code' => $row['code']],
                $row,
            );
        }
    }

    private function chapitres(): void
    {
        $chapitres = [
            ['code' => '764', 'label_fr' => 'Centres pour personnes handicapées'],
            ['code' => '765', 'label_fr' => 'Établissements de l\'enfance et de la jeunesse'],
            ['code' => '624', 'label_fr' => 'Alimentation en eau potable des établissements'],
            ['code' => '834', 'label_fr' => 'Sièges et infrastructures administratives'],
        ];

        foreach ($chapitres as $row) {
            Chapitre::updateOrCreate(['code' => $row['code']], $row);
        }
    }

    private function programmes(): void
    {
        // N15: "PACE-PCCE-PN-FSDRS-".
        $types = [
            ['code' => 'PACE', 'label_fr' => 'Programme d\'appui à la croissance économique'],
            ['code' => 'PCCE', 'label_fr' => 'Programme complémentaire de consolidation de la croissance économique'],
            ['code' => 'PN', 'label_fr' => 'Programme neuf'],
            ['code' => 'FSDRS', 'label_fr' => 'Fonds spécial de développement des régions du Sud'],
        ];

        foreach ($types as $row) {
            ProgrammeType::updateOrCreate(['code' => $row['code']], $row);
        }

        // L15: "P1-P2-P3-P4"; M15: "P1,1-P1,2;;;;;;;".
        $structures = [
            'P1' => 'Solidarité nationale et action sociale',
            'P2' => 'Protection et promotion de la famille',
            'P3' => 'Promotion de la condition de la femme',
            'P4' => 'Administration générale et soutien',
        ];

        foreach ($structures as $code => $label) {
            $structure = ProgrammeStructure::updateOrCreate(['code' => $code], ['label_fr' => $label]);

            foreach ([1, 2] as $index) {
                SousProgramme::updateOrCreate(
                    ['programme_structure_id' => $structure->id, 'code' => "{$code}.{$index}"],
                    ['label_fr' => "{$label} — sous-programme {$index}"],
                );
            }
        }
    }

    /**
     * Dairas of Biskra, needed by the scanned fixture. Other wilayas get their
     * dairas as projects are entered; the table is reference data, not a
     * complete territorial registry.
     */
    private function dairas(): void
    {
        $biskra = Wilaya::where('code', '07')->first();

        if (! $biskra) {
            return;
        }

        $dairas = [
            ['code' => '0701', 'name_fr' => 'Biskra', 'name_ar' => 'بسكرة'],
            ['code' => '0702', 'name_fr' => 'El Hadjeb', 'name_ar' => 'الحاجب'],
            ['code' => '0703', 'name_fr' => 'Sidi Okba', 'name_ar' => 'سيدي عقبة'],
            ['code' => '0704', 'name_fr' => 'Zeribet El Oued', 'name_ar' => 'زريبة الوادي'],
            ['code' => '0705', 'name_fr' => 'Tolga', 'name_ar' => 'طولقة'],
            ['code' => '0706', 'name_fr' => 'Ouled Djellal', 'name_ar' => 'أولاد جلال'],
            ['code' => '0707', 'name_fr' => 'Sidi Khaled', 'name_ar' => 'سيدي خالد'],
            ['code' => '0708', 'name_fr' => 'M\'Chouneche', 'name_ar' => 'مشونش'],
            ['code' => '0709', 'name_fr' => 'Djemorah', 'name_ar' => 'جمورة'],
            ['code' => '0710', 'name_fr' => 'El Outaya', 'name_ar' => 'الوطاية'],
            ['code' => '0711', 'name_fr' => 'Foughala', 'name_ar' => 'فوغالة'],
            ['code' => '0712', 'name_fr' => 'Ourlal', 'name_ar' => 'أورلال'],
        ];

        foreach ($dairas as $row) {
            Daira::updateOrCreate(
                ['wilaya_id' => $biskra->id, 'code' => $row['code']],
                $row,
            );
        }
    }
}
