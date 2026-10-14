<?php

namespace Database\Seeders;

use App\Jobs\RecalculateProjectMetrics;
use App\Models\Project;
use Illuminate\Database\Seeder;

/**
 * The observers that normally keep `spu_scores` and `alerts` current are muted
 * during seeding (WithoutModelEvents) and their job is queued, so a freshly
 * seeded database would open with empty SPU and alert screens. Running the job
 * inline once at the end fills them from the same code path the app uses.
 */
class MetricsSnapshotSeeder extends Seeder
{
    public function run(): void
    {
        // Nobody is listening while the database is being built, and Reverb is
        // usually not up during a deploy's seed step.
        config(['broadcasting.default' => 'null']);

        Project::query()->orderBy('id')->chunkById(50, function ($projects) {
            foreach ($projects as $project) {
                RecalculateProjectMetrics::dispatchSync($project->id);
            }
        });
    }
}
