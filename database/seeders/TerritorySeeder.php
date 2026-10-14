<?php

namespace Database\Seeders;

use App\Models\Wilaya;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Communes and établissements, the two lookups the fiche prints under the
 * "localisation" block.
 *
 * The `commune` table is ministry-owned. This seeder never wipes it. It only
 * inserts Biskra + chef-lieu stubs when the table is empty.
 *
 * Établissements come from `project_assets/tab_etablissement.sql` when that
 * dump is present next to the project.
 */
class TerritorySeeder extends Seeder
{
    /** The 33 communes of the wilaya of Biskra, in official code order. */
    private const BISKRA = [
        ['0701', 'Biskra', 'بسكرة'],
        ['0702', 'Oumache', 'أوماش'],
        ['0703', 'Branis', 'برانيس'],
        ['0704', 'Chetma', 'شتمة'],
        ['0705', 'Ouled Djellal', 'أولاد جلال'],
        ['0706', 'Ras El Miaad', 'رأس الميعاد'],
        ['0707', 'Besbes', 'بسباس'],
        ['0708', 'El Ghrous', 'الغروس'],
        ['0709', 'El Haouch', 'الحوش'],
        ['0710', 'Djemorah', 'جمورة'],
        ['0711', 'Tolga', 'طولقة'],
        ['0712', 'Lioua', 'ليوة'],
        ['0713', 'Lichana', 'ليشانة'],
        ['0714', 'Ourlal', 'أورلال'],
        ['0715', "M'Lili", 'مليلي'],
        ['0716', 'Foughala', 'فوغالة'],
        ['0717', 'Bordj Ben Azzouz', 'برج بن عزوز'],
        ['0718', 'Meziraa', 'المزيرعة'],
        ['0719', 'Bouchagroun', 'بوشقرون'],
        ['0720', 'Mekhadma', 'مخادمة'],
        ['0721', 'El Hadjeb', 'الحاجب'],
        ['0722', 'Ain Naga', 'عين الناقة'],
        ['0723', 'Zeribet El Oued', 'زريبة الوادي'],
        ['0724', 'El Mizaraa', 'المزرعة'],
        ['0725', 'Doucen', 'الدوسن'],
        ['0726', 'Chaiba', 'الشعيبة'],
        ['0727', 'Sidi Okba', 'سيدي عقبة'],
        ['0728', "M'Chouneche", 'مشونش'],
        ['0729', 'El Kantara', 'القنطرة'],
        ['0730', 'Ain Zaatout', 'عين زعطوط'],
        ['0731', 'El Outaya', 'الوطاية'],
        ['0732', 'Khenguet Sidi Nadji', 'خنقة سيدي ناجي'],
        ['0733', 'Sidi Khaled', 'سيدي خالد'],
    ];

    public function run(): void
    {
        $this->communes();
        $this->etablissements();
    }

    private function communes(): void
    {
        if (DB::table('commune')->exists()) {
            return;
        }

        foreach (self::BISKRA as [$code, $fr, $ar]) {
            DB::table('commune')->updateOrInsert(
                ['code_comm' => $code],
                ['lib_comm_fr' => $fr, 'lib_comm_ar' => $ar, 'code_wilaya' => '7'],
            );
        }

        foreach (Wilaya::where('code', '!=', '07')->get() as $wilaya) {
            DB::table('commune')->updateOrInsert(
                ['code_comm' => $wilaya->code.'01'],
                [
                    'lib_comm_fr' => $wilaya->name_fr,
                    'lib_comm_ar' => $wilaya->name_ar,
                    'code_wilaya' => $wilaya->legacyCode(),
                ],
            );
        }
    }

    private function etablissements(): void
    {
        if (DB::table('tab_etablissement')->exists()) {
            return;
        }

        $dump = base_path('../project_assets/tab_etablissement.sql');

        if (! is_file($dump)) {
            $this->command?->warn('tab_etablissement.sql not found, skipping établissements.');

            return;
        }

        $rows = $this->parseDump(file_get_contents($dump));

        if ($rows === []) {
            return;
        }

        foreach (array_chunk($rows, 500) as $chunk) {
            DB::table('tab_etablissement')->insert($chunk);
        }
    }

    /**
     * Pulls the `(id, wilaya, 'code', 'libelle')` tuples out of the phpMyAdmin
     * dump without needing a MySQL client on the machine.
     *
     * @return list<array{id: int, wilaya: float, 'Code établissement': string, libelle: string}>
     */
    private function parseDump(string $sql): array
    {
        preg_match_all(
            "/\(\s*(\d+)\s*,\s*([\d.]+)\s*,\s*'((?:[^'\\\\]|\\\\.)*)'\s*,\s*'((?:[^'\\\\]|\\\\.)*)'\s*\)/u",
            $sql,
            $matches,
            PREG_SET_ORDER,
        );

        $rows = [];
        foreach ($matches as [, $id, $wilaya, $code, $libelle]) {
            $rows[] = [
                'id' => (int) $id,
                'wilaya' => (float) $wilaya,
                'Code établissement' => stripslashes($code),
                'libelle' => stripslashes($libelle),
            ];
        }

        return $rows;
    }
}
