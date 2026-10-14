<?php

$files = [
    'resources/views/livewire/dashboard/project-fiche.blade.php',
    'resources/views/livewire/dashboard/reports-center.blade.php',
    'resources/views/livewire/dashboard/project-show.blade.php',
    'resources/views/exports/fiche-suivi/narrative.blade.php',
    'resources/views/exports/fiche-suivi/lots.blade.php',
    'resources/views/exports/fiche-suivi/layout.blade.php',
    'app/Livewire/Dashboard/ProjectFiche.php',
    'app/Livewire/Dashboard/ReportsCenter.php',
    'app/Exports/EtatGlobalExport.php',
    'app/Exports/FicheSuiviPdf.php',
    'app/Support/FicheFormat.php',
    'app/Support/ArabicText.php',
    'app/Http/Controllers/ExportController.php',
    'database/factories/ProjectFactory.php',
];

foreach ($files as $file) {
    $path = __DIR__.'/'.$file;

    if (! is_file($path)) {
        printf("MISSING  %s\n", $file);

        continue;
    }

    $raw = file_get_contents($path);
    $bom = str_starts_with($raw, "\xEF\xBB\xBF") ? ' BOM!' : '';
    $valid = mb_check_encoding($raw, 'UTF-8');
    // U+FFFD and the classic cp1252 mojibake signature "Ã".
    $mojibake = preg_match('/\x{FFFD}|Ã.|Â./u', $raw, $m) ? '  mojibake: '.$m[0] : '';

    printf("%-7s %s%s%s\n", $valid ? 'utf8' : 'BROKEN', $file, $bom, $mojibake);
}
