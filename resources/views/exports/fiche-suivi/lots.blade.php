@extends('exports.fiche-suivi.layout')

@php
    use App\Models\ProjectLot;
    use App\Services\Fiche\LotBuckets;
    use App\Support\FicheFormat as F;
    use App\Support\Money;

    /**
     * Each bucket prints the columns the scan gives it: réceptionnés show a
     * date, en cours de réalisation an avancement, en cours de lancement the
     * blocking state with its Arabic annotation, non lancés the estimation
     * provenance (img907–img910).
     */
    $tables = [
        'total_receptionne' => ['contractor', 'reception'],
        'total_3' => ['contractor', 'progress'],
        'total_1' => ['blocking'],
        'total_2' => ['estimation'],
    ];
@endphp

@section('content')

    <h2>Consistance physique du projet — {{ $project->operationCode() }}</h2>
    <div class="muted" style="margin-bottom: 6px;">{{ $project->title_fr }}</div>

    @foreach ($tables as $bucket => $extras)
        @php
            $rows = $buckets->lots($bucket);
            $caption = LotBuckets::TABLES[$bucket];
            $columns = 4 + count($extras);
        @endphp
        <div class="section">
            <div class="section-title">{{ $caption['title'] }}</div>
            <table>
                <thead>
                <tr>
                    <th style="width: 6%">Tranche</th>
                    <th style="width: 5%">Lot</th>
                    <th>Désignation</th>
                    <th style="width: 13%">Montant (DA)</th>
                    @if (in_array('contractor', $extras, true))
                        <th style="width: 16%">Entreprise</th>
                    @endif
                    @if (in_array('reception', $extras, true))
                        <th style="width: 11%">Réception</th>
                    @endif
                    @if (in_array('progress', $extras, true))
                        <th style="width: 11%">Avancement</th>
                    @endif
                    @if (in_array('blocking', $extras, true))
                        <th style="width: 24%">Situation</th>
                        <th style="width: 24%">Motif (ar)</th>
                    @endif
                    @if (in_array('estimation', $extras, true))
                        <th style="width: 14%">Établie par</th>
                        <th style="width: 16%">Base d'estimation</th>
                    @endif
                </tr>
                </thead>
                <tbody>
                @forelse ($rows as $lot)
                    <tr>
                        <td class="mid">{{ $lot->tranche }}</td>
                        <td class="mid">{{ $lot->lot_number }}</td>
                        <td>{{ $lot->designation }}</td>
                        <td class="num">{{ F::da($lot->effectiveAmountDa()) }}</td>
                        @if (in_array('contractor', $extras, true))
                            <td>{{ $lot->contractor?->name ?: '—' }}</td>
                        @endif
                        @if (in_array('reception', $extras, true))
                            <td class="mid">{{ F::date($lot->reception_date, '—') }}</td>
                        @endif
                        @if (in_array('progress', $extras, true))
                            <td class="mid">{{ Money::pct($lot->progress_pct) }}</td>
                        @endif
                        @if (in_array('blocking', $extras, true))
                            <td>{{ ProjectLot::blockingReasonLabels()[$lot->blocking_reason] ?? '—' }}</td>
                            <td class="ar rtl">
                                @foreach (F::arLines($lot->blocking_note_ar, 46) as $line)
                                    <div class="ar-line">{{ $line }}</div>
                                @endforeach
                            </td>
                        @endif
                        @if (in_array('estimation', $extras, true))
                            <td>{{ $lot->estimated_by ?: '—' }}</td>
                            <td>{{ $lot->estimation_basis ? strtoupper($lot->estimation_basis) : '—' }}</td>
                        @endif
                    </tr>
                @empty
                    <tr><td colspan="{{ $columns }}" class="mid muted">Aucun lot dans cette situation.</td></tr>
                @endforelse
                <tr class="total">
                    <td colspan="3">{{ $caption['total'] }}</td>
                    <td class="num">{{ F::da($buckets->total($bucket)) }}</td>
                    @if (count($extras) > 0)
                        <td colspan="{{ count($extras) }}"></td>
                    @endif
                </tr>
                </tbody>
            </table>
        </div>
    @endforeach

    <div class="section">
        <div class="section-title">Raccordement du projet aux différents réseaux</div>
        <table>
            <thead>
            <tr>
                <th style="width: 20%">Réseau</th>
                <th style="width: 12%">Raccordé</th>
                <th>Motif</th>
            </tr>
            </thead>
            <tbody>
            @foreach ($networks as $network)
                <tr>
                    <td>{{ $network['label'] }}</td>
                    <td class="mid">{{ F::yesNo($network['connected']) }}</td>
                    <td>{{ $network['motif'] ?: '—' }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>

    @if ($project->observations)
        <div class="section">
            <div class="section-title">Observations</div>
            <table><tr><td>{{ $project->observations }}</td></tr></table>
        </div>
    @endif

    <div class="signature">
        Le Directeur de l'Action Sociale et de la Solidarité<br>
        Wilaya de {{ $project->wilaya?->name_fr }}
    </div>

@endsection
