<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class WilayaSeeder extends Seeder
{
    public function run(): void
    {
        $wilayas = [
            ['01', 'أدرار', 'Adrar'],
            ['02', 'الشلف', 'Chlef'],
            ['03', 'الأغواط', 'Laghouat'],
            ['04', 'أم البواقي', 'Oum El Bouaghi'],
            ['05', 'باتنة', 'Batna'],
            ['06', 'بجاية', 'Béjaïa'],
            ['07', 'بسكرة', 'Biskra'],
            ['08', 'بشار', 'Béchar'],
            ['09', 'البليدة', 'Blida'],
            ['10', 'البويرة', 'Bouira'],
            ['11', 'تمنراست', 'Tamanrasset'],
            ['12', 'تبسة', 'Tébessa'],
            ['13', 'تلمسان', 'Tlemcen'],
            ['14', 'تيارت', 'Tiaret'],
            ['15', 'تيزي وزو', 'Tizi Ouzou'],
            ['16', 'الجزائر', 'Alger'],
            ['17', 'الجلفة', 'Djelfa'],
            ['18', 'جيجل', 'Jijel'],
            ['19', 'سطيف', 'Sétif'],
            ['20', 'سعيدة', 'Saïda'],
            ['21', 'سكيكدة', 'Skikda'],
            ['22', 'سيدي بلعباس', 'Sidi Bel Abbès'],
            ['23', 'عنابة', 'Annaba'],
            ['24', 'قالمة', 'Guelma'],
            ['25', 'قسنطينة', 'Constantine'],
            ['26', 'المدية', 'Médéa'],
            ['27', 'مستغانم', 'Mostaganem'],
            ['28', 'المسيلة', "M'Sila"],
            ['29', 'معسكر', 'Mascara'],
            ['30', 'ورقلة', 'Ouargla'],
            ['31', 'وهران', 'Oran'],
            ['32', 'البيض', 'El Bayadh'],
            ['33', 'إليزي', 'Illizi'],
            ['34', 'برج بوعريريج', 'Bordj Bou Arreridj'],
            ['35', 'بومرداس', 'Boumerdès'],
            ['36', 'الطارف', 'El Tarf'],
            ['37', 'تندوف', 'Tindouf'],
            ['38', 'تيسمسيلت', 'Tissemsilt'],
            ['39', 'الوادي', 'El Oued'],
            ['40', 'خنشلة', 'Khenchela'],
            ['41', 'سوق أهراس', 'Souk Ahras'],
            ['42', 'تيبازة', 'Tipaza'],
            ['43', 'ميلة', 'Mila'],
            ['44', 'عين الدفلى', 'Aïn Defla'],
            ['45', 'النعامة', 'Naâma'],
            ['46', 'عين تموشنت', 'Aïn Témouchent'],
            ['47', 'غرداية', 'Ghardaïa'],
            ['48', 'غليزان', 'Relizane'],
            ['49', 'تميمون', 'Timimoun'],
            ['50', 'برج باجي مختار', 'Bordj Badji Mokhtar'],
            ['51', 'أولاد جلال', 'Ouled Djellal'],
            ['52', 'بني عباس', 'Béni Abbès'],
            ['53', 'إن صالح', 'In Salah'],
            ['54', 'إن قزام', 'In Guezzam'],
            ['55', 'تقرت', 'Touggourt'],
            ['56', 'جانت', 'Djanet'],
            ['57', 'المغير', 'El Meghaier'],
            ['58', 'المنيعة', 'El Meniaa'],
        ];

        foreach ($wilayas as $w) {
            DB::table('wilayas')->updateOrInsert(
                ['code' => $w[0]],
                ['name_ar' => $w[1], 'name_fr' => $w[2]],
            );
        }
    }
}
