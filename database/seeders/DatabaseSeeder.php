<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $this->call([
            // Access control
            PermissionSeeder::class,
            RolePermissionSeeder::class,

            // Calculation engine
            CalculationRuleSeeder::class,

            // Reference data
            WilayaSeeder::class,
            TerritorySeeder::class,
            ReferenceDataSeeder::class,
            ProgrammeSeeder::class,
            ProjectOwnerSeeder::class,
            MaturationChecklistSeeder::class,

            // Accounts
            DassUserSeeder::class,
            RoleTestAccountSeeder::class,

            // Demo portfolio, plus the scanned Biskra fiche as export fixture
            PortfolioSeeder::class,
            BiskraFermePedagogiqueSeeder::class,

            // SPU snapshots and alerts, derived from everything above
            MetricsSnapshotSeeder::class,
        ]);
    }
}
