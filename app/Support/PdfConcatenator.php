<?php

namespace App\Support;

use RuntimeException;
use setasign\Fpdi\Fpdi;

/**
 * Joins several PDF strings into one, keeping each page's own size and
 * orientation.
 *
 * The official fiche is portrait for the narrative pages and landscape for the
 * lot tables, but DomPDF fixes the paper size for a whole document. Rendering
 * the halves separately and stitching them here is the only way to get the
 * mixed-orientation document the ministry expects out of DomPDF.
 */
final class PdfConcatenator
{
    /**
     * @param  list<string>  $documents  Raw PDF bytes, in output order.
     */
    public static function join(array $documents): string
    {
        $documents = array_values(array_filter($documents, fn ($pdf) => $pdf !== '' && $pdf !== null));

        if ($documents === []) {
            throw new RuntimeException('Nothing to concatenate.');
        }

        if (count($documents) === 1) {
            return $documents[0];
        }

        $pdf = new Fpdi;
        $pdf->SetAutoPageBreak(false);
        $temporary = [];

        try {
            foreach ($documents as $document) {
                // FPDI reads from a stream, and its stream reader needs a
                // seekable handle, so each part goes through a temp file.
                $path = tempnam(sys_get_temp_dir(), 'fiche');
                file_put_contents($path, $document);
                $temporary[] = $path;

                $pages = $pdf->setSourceFile($path);

                for ($page = 1; $page <= $pages; $page++) {
                    $template = $pdf->importPage($page);
                    $size = $pdf->getTemplateSize($template);

                    $pdf->AddPage($size['orientation'], [$size['width'], $size['height']]);
                    $pdf->useTemplate($template);
                }
            }

            return $pdf->Output('S');
        } finally {
            foreach ($temporary as $path) {
                @unlink($path);
            }
        }
    }
}
