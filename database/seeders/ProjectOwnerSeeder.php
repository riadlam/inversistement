<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProjectOwnerSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        DB::table('project_owners')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        $owners = [
            ['email' => 'contact@sonatrach.dz',              'name' => 'SONATRACH Spa',                          'company_name' => 'SONATRACH Spa',                          'nif' => '099716001234567', 'business_register' => '00B-0123456'],
            ['email' => 'contact@sonelgaz.dz',               'name' => 'SONELGAZ Spa',                          'company_name' => 'SONELGAZ Spa',                          'nif' => '099716001234568', 'business_register' => '00B-0123457'],
            ['email' => 'contact@cosider.dz',                 'name' => 'COSIDER Construction',                  'company_name' => 'COSIDER Travaux Publics Spa',             'nif' => '099716001234569', 'business_register' => '00B-0123458'],
            ['email' => 'contact@engtp.dz',                   'name' => 'ENGTP Spa',                             'company_name' => 'Entreprise Nationale des Grands Travaux', 'nif' => '099716001234570', 'business_register' => '00B-0123459'],
            ['email' => 'contact@madar-holding.dz',           'name' => 'MADAR Holding',                         'company_name' => 'MADAR Holding Spa',                      'nif' => '099716001234571', 'business_register' => '00B-0123460'],
            ['email' => 'contact@eniem.dz',                   'name' => 'ENIEM Spa',                             'company_name' => 'ENIEM Spa',                              'nif' => '099716001234572', 'business_register' => '00B-0123461'],
            ['email' => 'contact@algerietelecom.dz',          'name' => 'Algerian Telecom',                       'company_name' => 'Algérie Télécom Spa',                    'nif' => '099716001234573', 'business_register' => '00B-0123462'],
            ['email' => 'contact@sntf.dz',                    'name' => 'SNTF (National Rail)',                  'company_name' => 'SNTF — Société Nationale des Transports', 'nif' => '099716001234574', 'business_register' => '00B-0123463'],
            ['email' => 'contact@seaal.dz',                   'name' => 'SEAAL Spa',                             'company_name' => 'SEAAL — Alger Eau',                      'nif' => '099716001234575', 'business_register' => '00B-0123464'],
            ['email' => 'contact@geniecivil-ouest.dz',        'name' => 'EPE SPA — Génie Civil Ouest',           'company_name' => 'Génie Civil Ouest EPE Spa',              'nif' => '099716001234576', 'business_register' => '00B-0123465'],
        ];

        $now = now();

        DB::table('project_owners')->insert(array_map(fn ($o) => [...$o, 'created_at' => $now, 'updated_at' => $now], $owners));
    }
}
