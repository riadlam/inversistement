<?php

require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use setasign\Fpdi\Fpdi;

$path = $argv[1] ?? __DIR__.'/storage/app/fiche-suivi-ferme-ped-20260908.pdf';
$bytes = file_get_contents($path);

$pdf = new Fpdi;
$pages = $pdf->setSourceFile($path);
echo "pages: {$pages}\n";

for ($page = 1; $page <= $pages; $page++) {
    $size = $pdf->getTemplateSize($pdf->importPage($page));
    printf("  page %d: %.0f x %.0f mm — %s\n", $page, $size['width'], $size['height'], $size['orientation'] === 'L' ? 'paysage' : 'portrait');
}

echo "\nembedded font names:\n";
preg_match_all('/\/BaseFont\s*\/([A-Za-z0-9+\-_,]+)/', $bytes, $fonts);
foreach (array_count_values($fonts[1]) as $name => $count) {
    echo "  {$name} (x{$count})\n";
}

echo "\nfont cache:\n";
foreach (glob(storage_path('fonts/*')) as $file) {
    printf("  %-46s %d\n", basename($file), filesize($file));
}
