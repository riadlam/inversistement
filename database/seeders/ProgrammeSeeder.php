<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProgrammeSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            ['code' => 'PS-01', 'label_fr' => 'Action sociale et solidarité nationale',            'label_ar' => 'العمل الاجتماعي والتضامن الوطني',              'type' => 'T3'],
            ['code' => 'PS-02', 'label_fr' => 'Promotion de la jeunesse et des sports',            'label_ar' => 'ترقية الشباب والرياضة',                        'type' => 'T3'],
            ['code' => 'PS-03', 'label_fr' => 'Équipements et infrastructures socio-éducatifs',    'label_ar' => 'التجهيزات والهياكل الاجتماعية التربوية',        'type' => 'T3'],
            ['code' => 'PS-04', 'label_fr' => 'Développement de l\'enfance et de la famille',      'label_ar' => 'تنمية الطفولة والأسرة',                         'type' => 'T3'],
            ['code' => 'PS-05', 'label_fr' => 'Insertion professionnelle et emploi',               'label_ar' => 'الإدماج المهني والتشغيل',                       'type' => 'T4'],
            ['code' => 'ED-01', 'label_fr' => 'Enseignement fondamental et secondaire',            'label_ar' => 'التعليم الأساسي والثانوي',                      'type' => 'T3'],
            ['code' => 'ED-02', 'label_fr' => 'Enseignement supérieur et recherche',               'label_ar' => 'التعليم العالي والبحث',                         'type' => 'T3'],
            ['code' => 'ED-03', 'label_fr' => 'Formation et enseignement professionnels',          'label_ar' => 'التكوين والتعليم المهنيان',                     'type' => 'T3'],
            ['code' => 'ED-04', 'label_fr' => 'Alphabétisation et éducation des adultes',          'label_ar' => 'محو الأمية وتعليم الكبار',                      'type' => 'T4'],
            ['code' => 'HE-01', 'label_fr' => 'Santé de base et prévention',                      'label_ar' => 'الصحة الأساسية والوقاية',                       'type' => 'T3'],
            ['code' => 'HE-02', 'label_fr' => 'Infrastructures hospitalières et équipements',      'label_ar' => 'الهياكل الاستشفائية والتجهيزات',                 'type' => 'T3'],
            ['code' => 'HE-03', 'label_fr' => 'Santé maternelle et infantile',                    'label_ar' => 'صحة الأم والطفل',                               'type' => 'T4'],
            ['code' => 'TR-01', 'label_fr' => 'Routes et transports terrestres',                  'label_ar' => 'الطرق والنقل البري',                           'type' => 'T3'],
            ['code' => 'TR-02', 'label_fr' => 'Transports ferroviaires',                          'label_ar' => 'النقل بالسكك الحديدية',                        'type' => 'T3'],
            ['code' => 'TR-03', 'label_fr' => 'Transports aériens et aéroports',                  'label_ar' => 'النقل الجوي والمطارات',                         'type' => 'T3'],
            ['code' => 'WS-01', 'label_fr' => 'Alimentation en eau potable',                      'label_ar' => 'التزويد بالمياه الصالحة للشرب',                 'type' => 'T3'],
            ['code' => 'WS-02', 'label_fr' => 'Assainissement et épuration',                      'label_ar' => 'التطهير وتصفية المياه',                         'type' => 'T3'],
            ['code' => 'WS-03', 'label_fr' => 'Ressources hydrauliques et barrages',              'label_ar' => 'الموارد المائية والسدود',                       'type' => 'T3'],
            ['code' => 'UR-01', 'label_fr' => 'Habitat et urbanisme',                              'label_ar' => 'السكن والتعمير',                                'type' => 'T3'],
            ['code' => 'UR-02', 'label_fr' => 'Rénovation urbaine et revitalisation',             'label_ar' => 'التجديد الحضري والتنشيط',                       'type' => 'T3'],
            ['code' => 'AG-01', 'label_fr' => 'Développement agricole et rural',                  'label_ar' => 'التنمية الفلاحية والريفية',                     'type' => 'T3'],
            ['code' => 'AG-02', 'label_fr' => 'Sécurité alimentaire et élevage',                  'label_ar' => 'الأمن الغذائي والمواشي',                        'type' => 'T4'],
            ['code' => 'EN-01', 'label_fr' => 'Énergies renouvelables et efficacité',             'label_ar' => 'الطاقات المتجددة والنجاعة',                     'type' => 'T3'],
            ['code' => 'EN-02', 'label_fr' => 'Électrification rurale et distribution',           'label_ar' => 'الكهربة الريفية والتوزيع',                      'type' => 'T3'],
            ['code' => 'EN-03', 'label_fr' => 'Hydrocarbures et mining',                          'label_ar' => 'المحروقات والمناجم',                             'type' => 'T3'],
            ['code' => 'EQ-01', 'label_fr' => 'Équipements communaux et locaux',                  'label_ar' => 'التجهيزات البلدية والمحلية',                     'type' => 'T3'],
            ['code' => 'EQ-02', 'label_fr' => 'Technologies de l\'information et digitalisation',  'label_ar' => 'تكنولوجيا المعلومات والرقمنة',                   'type' => 'T3'],
            ['code' => 'EC-01', 'label_fr' => 'Développement économique et industrie',            'label_ar' => 'التنمية الاقتصادية والصناعة',                    'type' => 'T3'],
            ['code' => 'EC-02', 'label_fr' => 'Soutien aux PME et entrepreneurs',                 'label_ar' => 'دعم المؤسسات الصغيرة والمقاولين',                'type' => 'T4'],
            ['code' => 'EC-03', 'label_fr' => 'Tourisme et artisanat',                            'label_ar' => 'السياحة والصناعات التقليدية',                    'type' => 'T3'],
            ['code' => 'CU-01', 'label_fr' => 'Culture, patrimoine et loisirs',                   'label_ar' => 'الثقافة والتراث والترفيه',                       'type' => 'T3'],
            ['code' => 'CU-02', 'label_fr' => 'Protection du patrimoine historique',              'label_ar' => 'حماية التراث التاريخي',                          'type' => 'T4'],
            ['code' => 'ENV-01', 'label_fr' => 'Protection de l\'environnement et biodiversité',  'label_ar' => 'حماية البيئة والتنوع البيولوجي',                  'type' => 'T3'],
            ['code' => 'ENV-02', 'label_fr' => 'Gestion des déchets et recyclage',                'label_ar' => 'تسيير النفايات والتدوير',                        'type' => 'T3'],
            ['code' => 'DS-01', 'label_fr' => 'Développement des zones d\'ombre et Sud',          'label_ar' => 'تنمية المناطق الظل والجنوب',                     'type' => 'T3'],
            ['code' => 'DS-02', 'label_fr' => 'Aménagement du territoire et urbanisme',           'label_ar' => 'تهيئة الإقليم والتعمير',                         'type' => 'T3'],
        ];

        DB::table('programmes')->insert($items);
    }
}
