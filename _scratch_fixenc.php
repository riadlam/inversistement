<?php

require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Exports\FicheSuiviPdf;
use App\Models\Project;
use App\Models\User;

auth()->login(User::where('role', 'admin')->first());

$project = Project::where('fip_id', 'FIP-2010-BISKRA-FP')->firstOrFail();

$started = microtime(true);
$fiche = FicheSuiviPdf::for($project);
$pdf = $fiche->contents();

$path = __DIR__.'/storage/app/'.$fiche->filename();
file_put_contents($path, $pdf);

printf("%s — %d bytes in %.1fs\n", $path, strlen($pdf), microtime(true) - $started);

// Page count and per-page media boxes, to prove the orientation split.
preg_match_all('/\/MediaBox\s*\[([^\]]+)\]/', $pdf, $boxes);
foreach ($boxes[1] as $index => $box) {
    $parts = preg_split('/\s+/', trim($box));
    printf("  page %d: %.0f x %.0f (%s)\n",
        $index + 1,
        (float) $parts[2],
        (float) $parts[3],
        (float) $parts[2] > (float) $parts[3] ? 'paysage' : 'portrait',
    );
}
