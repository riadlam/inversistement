<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Pitch teleprompter</title>
    <style>
        @page { margin: 10mm 12mm 12mm 12mm; }

        body {
            font-family: 'dejavu sans', sans-serif;
            font-size: 9pt;
            color: #111;
            line-height: 1.3;
        }

        h1 {
            margin: 0 0 2px 0;
            font-size: 14pt;
            text-align: center;
        }

        .subtitle {
            text-align: center;
            font-size: 8pt;
            color: #444;
            margin-bottom: 8px;
        }

        .tip {
            background: #f3f4f6;
            border: 0.5pt solid #ccc;
            padding: 4px 6px;
            margin-bottom: 8px;
            font-size: 8pt;
        }

        .step {
            margin-bottom: 5px;
            page-break-inside: avoid;
        }

        .step .head {
            font-size: 10pt;
            font-weight: bold;
        }

        .step .n {
            display: inline-block;
            width: 18px;
            height: 18px;
            line-height: 18px;
            text-align: center;
            background: #111;
            color: #fff;
            font-size: 8pt;
            margin-right: 4px;
        }

        .step .say {
            margin: 1px 0 0 22px;
            font-size: 8.5pt;
        }

        .step .url {
            margin: 1px 0 0 22px;
            font-family: 'dejavu sans mono', monospace;
            font-size: 6.5pt;
            color: #1d4ed8;
            word-wrap: break-word;
        }

        h2 {
            margin: 10px 0 4px 0;
            font-size: 10pt;
            border-bottom: 0.75pt solid #111;
            padding-bottom: 2px;
        }

        .hidden-item {
            margin-bottom: 3px;
            page-break-inside: avoid;
        }

        .hidden-item .t { font-size: 8pt; font-weight: bold; }
        .hidden-item .url {
            font-family: 'dejavu sans mono', monospace;
            font-size: 6.5pt;
            color: #1d4ed8;
            margin-left: 0;
        }

        table.accounts {
            width: 100%;
            border-collapse: collapse;
            font-size: 8pt;
            margin-top: 4px;
        }

        table.accounts th, table.accounts td {
            border: 0.5pt solid #999;
            padding: 3px 5px;
            text-align: left;
        }

        table.accounts th { background: #efefef; }

        .pwd {
            margin-top: 4px;
            font-size: 8.5pt;
            font-weight: bold;
        }

        .footer {
            margin-top: 8px;
            font-size: 7pt;
            color: #666;
            text-align: center;
        }
    </style>
</head>
<body>

    <h1>Investment system — live pitch</h1>
    <div class="subtitle">Teleprompter · speak each step in order · do not jump</div>

    <div class="tip">
        Demo project: <strong>{{ $demoFip }}</strong>
        &nbsp;·&nbsp; Host: <strong>{{ $baseUrl }}</strong>
        &nbsp;·&nbsp; Seed accounts: <code>php artisan db:seed --class=RoleTestAccountSeeder</code>
    </div>

    @foreach ($steps as $step)
        <div class="step">
            <div class="head"><span class="n">{{ $step['n'] }}</span>{{ $step['title'] }}</div>
            <div class="say">{{ $step['say'] }}</div>
            <div class="url">{{ $step['url'] }}</div>
        </div>
    @endforeach

    <h2>Hidden strengths (show last)</h2>
    @foreach ($hidden as $item)
        <div class="hidden-item">
            <div class="t">• {{ $item['title'] }}</div>
            <div class="url">{{ $item['url'] }}</div>
        </div>
    @endforeach

    <h2>Demo accounts</h2>
    <p class="pwd">Password for all: <code>password</code></p>
    <table class="accounts">
        <thead>
        <tr>
            <th>Email</th>
            <th>Role</th>
            <th>Scope</th>
        </tr>
        </thead>
        <tbody>
        @foreach ($accounts as $account)
            <tr>
                <td>{{ $account['email'] }}</td>
                <td>{{ $account['role'] }}</td>
                <td>{{ $account['scope'] }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>

    <div class="footer">
        Start as DASS → {{ $demoFip }} → fiche → dossier as RP/CB/Ordonnateur.
        If the IP changes, replace the host with your APP_URL.
    </div>

</body>
</html>
