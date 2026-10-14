<?php

/**
 * Only the settings the official fiche needs; everything else falls back to
 * the package defaults.
 */
return [
    'show_warnings' => false,
    'public_path' => public_path(),
    'convert_entities' => true,

    'options' => [
        /*
         * The Amiri faces live in resources/fonts and are referenced from the
         * export stylesheet with a file:// URL, so DomPDF must be allowed to
         * read from the project root and to cache the subsetted faces.
         */
        'font_dir' => storage_path('fonts'),
        'font_cache' => storage_path('fonts'),
        'temp_dir' => storage_path('app/dompdf'),
        'chroot' => base_path(),

        'default_font' => 'dejavu sans',
        'default_paper_size' => 'a4',
        'default_paper_orientation' => 'portrait',
        'dpi' => 96,

        'enable_font_subsetting' => true,
        'enable_remote' => false,
        'enable_php' => false,
        'enable_javascript' => false,
        'enable_html5_parser' => true,
        'is_html5_parser_enabled' => true,
    ],
];
