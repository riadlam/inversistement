<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Fiche de suivi — {{ $project->fip_id }}</title>
    <style>
        @font-face {
            font-family: 'amiri';
            font-style: normal;
            font-weight: 400;
            src: url("{{ resource_path('fonts/Amiri-Regular.ttf') }}") format('truetype');
        }

        @font-face {
            font-family: 'amiri';
            font-style: normal;
            font-weight: 700;
            src: url("{{ resource_path('fonts/Amiri-Bold.ttf') }}") format('truetype');
        }

        @page { margin: 14mm 10mm 16mm 10mm; }

        body {
            font-family: 'dejavu sans', sans-serif;
            font-size: 7.5pt;
            color: #000;
            line-height: 1.25;
        }

        .ar { font-family: 'amiri', 'dejavu sans', sans-serif; font-size: 9pt; }
        .rtl { text-align: right; }

        /*
         * Already shaped and reversed: letting the renderer wrap would flip
         * the visual order of the lines, so each one is laid out on its own.
         */
        .ar-line { white-space: nowrap; line-height: 1.5; }

        h1, h2, h3 { margin: 0; font-weight: bold; }
        h2 { font-size: 9pt; text-transform: uppercase; margin-bottom: 4px; }

        .letterhead {
            width: 100%;
            border-collapse: collapse;
            margin: 0 0 6px 0;
            line-height: 1.35;
        }
        .letterhead td {
            border: none !important;
            padding: 1px 0;
            text-align: center !important;
            font-size: 8pt;
            vertical-align: middle;
        }
        .letterhead .republique { text-transform: uppercase; font-weight: bold; }
        .letterhead .ar {
            font-family: 'amiri', 'dejavu sans', sans-serif;
            font-size: 9pt;
            direction: ltr;
        }

        .fiche-title {
            width: 100%;
            border-collapse: collapse;
            margin: 8px 0;
        }
        .fiche-title td {
            border: 1.5pt solid #000 !important;
            padding: 4px;
            text-align: center !important;
            vertical-align: middle;
        }
        .fiche-title .main { font-size: 12pt; font-weight: bold; text-transform: uppercase; }
        .fiche-title .short { font-size: 9pt; font-style: italic; text-transform: none; }
        .fiche-title .updated { font-size: 8pt; margin-top: 2px; }

        .section { margin-top: 8px; page-break-inside: avoid; }
        .section-title {
            background: #d9d9d9;
            border: 0.75pt solid #000;
            padding: 2px 4px;
            font-size: 8pt;
            font-weight: bold;
            text-transform: uppercase;
        }

        table { width: 100%; border-collapse: collapse; }
        th, td { border: 0.5pt solid #000; padding: 2px 3px; vertical-align: top; }
        th { background: #efefef; font-size: 6.5pt; text-align: center; font-weight: bold; }
        td.num { text-align: right; white-space: nowrap; }
        td.mid { text-align: center; }
        td.key { background: #f6f6f6; font-weight: bold; }
        tr.total td { background: #e8e8e8; font-weight: bold; }

        .checkbox {
            display: inline-block;
            border: 0.75pt solid #000;
            width: 9px;
            height: 9px;
            text-align: center;
            line-height: 9px;
            font-size: 7pt;
        }

        .footer {
            position: fixed;
            bottom: -10mm;
            left: 0;
            right: 0;
            font-size: 6pt;
            color: #444;
        }
        .footer .right { float: right; }

        .signature { margin-top: 18px; text-align: right; font-size: 8pt; }
        .muted { color: #555; }
    </style>
</head>
<body>

<div class="footer">
    {{ $project->operationCode() }} — {{ $project->wilaya?->name_fr }}
    <span class="right">Éditée le {{ $generatedAt->format('d/m/Y à H:i') }}</span>
</div>

@yield('content')

</body>
</html>
