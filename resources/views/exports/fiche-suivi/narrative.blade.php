@extends('exports.fiche-suivi.layout')

@php
    use App\Models\Commune;
    use App\Support\FicheFormat as F;
    use App\Support\Money;

    $land = $project->land;
    $mission = $project->designMissions->first();
    $commune = $project->commune_code
        ? Commune::where('code_comm', $project->commune_code)->value('lib_comm_fr')
        : null;
@endphp

@section('content')

    {{-- img903 — en-tête (table cells: DomPDF centres these reliably) --}}
    <table class="letterhead">
        <tr>
            <td class="republique">République Algérienne Démocratique et Populaire</td>
        </tr>
        <tr>
            <td class="ar">{{ F::ar('الجمهورية الجزائرية الديمقراطية الشعبية') }}</td>
        </tr>
        <tr>
            <td>Ministère de la Solidarité Nationale, de la Famille et de la Condition de la Femme</td>
        </tr>
        <tr>
            <td>Direction de l'Action Sociale et de la Solidarité — Wilaya de {{ $project->wilaya?->name_fr }}</td>
        </tr>
    </table>

    <table class="fiche-title">
        <tr>
            <td>
                <div class="main">
                    Fiche de suivi du projet
                    @if ($project->short_label)
                        <span class="short">({{ $project->short_label }})</span>
                    @endif
                </div>
                <div class="updated">Actualisée le : <strong>{{ F::date($project->fiche_updated_at, '—') }}</strong></div>
            </td>
        </tr>
    </table>

    {{-- img903 — les sept cases de situation --}}
    <table>
        <tr><td colspan="{{ count($phases) }}" class="key">Situation du projet</td></tr>
        <tr>
            @foreach ($phases as $key => $label)
                <td class="mid" style="width: {{ round(100 / count($phases), 2) }}%">
                    <span class="checkbox">{{ $project->phase === $key ? 'X' : '' }}</span><br>
                    {{ $label }}
                </td>
            @endforeach
        </tr>
    </table>

    <table style="margin-top: 4px;">
        <tr>
            <td class="key" style="width: 25%">Taux d'avancement</td>
            <td style="width: 25%">{{ Money::pct($budget->tauxPhysique()) }}</td>
            <td class="key" style="width: 25%">Taux d'équipement</td>
            <td>{{ Money::pct($project->equipment_progress_pct ?? 0) }}</td>
        </tr>
        <tr>
            <td class="key">Taux d'avancement financier</td>
            <td>{{ Money::pct($budget->tauxFinancier()) }}</td>
            <td class="key">Score de performance (SPU)</td>
            <td>{{ round($budget->spu()) }} / 100 — {{ F::trafficLightLabel($budget->trafficLight()) }}</td>
        </tr>
    </table>

    <div class="section">
        <div class="section-title">Identification du projet</div>
        <table>
            <tr>
                <td class="key" style="width: 34%">Intitulé de l'opération</td>
                <td>{{ $project->operationCode() }} — {{ $project->title_fr }}</td>
            </tr>
            <tr>
                <td class="key">Intitulé en arabe</td>
                <td class="ar rtl">
                    @foreach (F::arLines($project->title_ar, 72) as $line)
                        <div class="ar-line">{{ $line }}</div>
                    @endforeach
                </td>
            </tr>
            <tr>
                <td class="key">N° d'individualisation</td>
                <td>
                    {{ $project->individualisation_number ?: '—' }}
                    @if ($project->individualisation_date)
                        du {{ F::date($project->individualisation_date) }}
                    @endif
                </td>
            </tr>
            <tr>
                <td class="key">Décision de programme</td>
                <td>
                    {{ $project->decision_programme_ref ?: '—' }}
                    @if ($project->inscription_date)
                        — inscrite le {{ F::date($project->inscription_date) }}
                    @endif
                </td>
            </tr>
            <tr>
                <td class="key">Secteur / sous-secteur / chapitre</td>
                <td>
                    {{ $project->secteur?->code }} — {{ $project->secteur?->label_fr }}
                    / {{ $project->sousSecteur?->code }} — {{ $project->sousSecteur?->label_fr }}
                    / {{ $project->chapitre?->code }}
                </td>
            </tr>
            <tr>
                <td class="key">Type de programme / titre</td>
                <td>
                    {{ $project->programmeType?->code ?: '—' }}
                    — {{ strtoupper($project->titre ?: $project->budget_type ?: '—') }}
                </td>
            </tr>
            <tr>
                <td class="key">Opération gérée par</td>
                <td>{{ strtoupper($project->managed_by ?: '—') }}</td>
            </tr>
            <tr>
                <td class="key">Localisation</td>
                <td>
                    Wilaya de {{ $project->wilaya?->name_fr }}@if ($project->daira), daïra de {{ $project->daira->name_fr }}@endif@if ($commune), commune de {{ $commune }}@endif
                </td>
            </tr>
            <tr>
                <td class="key">Capacité de l'établissement</td>
                <td>
                    {{ $project->capacity ?: '—' }}
                    @if (($project->nbr_operations ?? 1) > 1)
                        — {{ $project->nbr_operations }} opérations
                    @endif
                </td>
            </tr>
            <tr>
                <td class="key">Délai contractuel</td>
                <td>{{ $project->project_duration_months ? $project->project_duration_months.' mois' : '—' }}</td>
            </tr>
            <tr>
                <td class="key">ODS initial</td>
                <td>
                    {{ F::date($project->ods_date, '—') }}
                    @if ($project->initial_launch_scope)
                        — lancement {{ $project->initial_launch_scope }}
                    @endif
                </td>
            </tr>
            <tr>
                <td class="key">Réception travaux / équipements</td>
                <td>
                    {{ F::quarter($project->works_reception_quarter, $project->works_reception_year) }}
                    / {{ F::quarter($project->equipment_reception_quarter, $project->equipment_reception_year) }}
                </td>
            </tr>
            <tr>
                <td class="key">Inauguration prévue</td>
                <td>{{ F::quarter($project->inauguration_quarter, $project->inauguration_year) }}</td>
            </tr>
        </table>
    </div>

    <div class="section">
        <div class="section-title">Assiette foncière</div>
        <table>
            <tr>
                <td class="key" style="width: 34%">Décision d'affectation</td>
                <td>
                    {{ $land?->affectation_decision_ref ?: '—' }}
                    @if ($land?->affectation_decision_date)
                        du {{ F::date($land->affectation_decision_date) }}
                    @endif
                </td>
            </tr>
            <tr>
                <td class="key">Superficie</td>
                <td>
                    {{ $land?->superficie_ha ?? 0 }} ha {{ $land?->superficie_a ?? 0 }} ar {{ $land?->superficie_ca ?? 0 }} ca
                    <span class="muted">({{ Money::da($budget->get('superficie_m2'), 0) }} m²)</span>
                </td>
            </tr>
            <tr>
                <td class="key">Situation</td>
                <td>{{ $land?->location ?: '—' }}</td>
            </tr>
        </table>
    </div>

    <div class="section">
        <div class="section-title">Maîtrise d'œuvre</div>
        <table>
            <thead>
            <tr>
                <th>Bureau d'études</th>
                <th>Esquisse approuvée le</th>
                <th>Avant-projet approuvé le</th>
                <th>Projet d'exécution approuvé le</th>
            </tr>
            </thead>
            <tbody>
            <tr>
                <td>{{ $mission?->bet_name ?: '—' }}</td>
                <td class="mid">{{ F::date($mission?->esquisse_approved_at, '—') }}</td>
                <td class="mid">{{ F::date($mission?->avant_projet_approved_at, '—') }}</td>
                <td class="mid">{{ F::date($mission?->projet_execution_approved_at, '—') }}</td>
            </tr>
            </tbody>
        </table>
    </div>

    {{-- img904–905 — étapes réglementaires par lot engagé --}}
    <div class="section">
        <div class="section-title">Situation de prise en charge du projet (étapes réglementaires)</div>
        <table>
            <thead>
            <tr>
                <th style="width: 7%">Tranche</th>
                <th style="width: 5%">Lot</th>
                <th>Désignation</th>
                <th style="width: 15%">Engagement contrôleur financier</th>
                <th style="width: 12%">Notification ODS</th>
                <th style="width: 8%">Délai</th>
                <th style="width: 19%">Entreprise</th>
            </tr>
            </thead>
            <tbody>
            @forelse ($awardedLots as $lot)
                @php
                    $engagement = $lot->milestones->firstWhere('type', 'engagement_controleur_financier');
                    $ods = $lot->milestones->firstWhere('type', 'ods_notification');
                @endphp
                <tr>
                    <td class="mid">{{ $lot->tranche }}</td>
                    <td class="mid">{{ $lot->lot_number }}</td>
                    <td>{{ $lot->designation }}</td>
                    <td class="mid">{{ F::date($engagement?->date, '—') }}</td>
                    <td class="mid">{{ F::date($ods?->date ?? $lot->launch_date, '—') }}</td>
                    <td class="mid">{{ $lot->delai_months ? $lot->delai_months.' mois' : '—' }}</td>
                    <td>{{ $lot->contractor?->name ?: '—' }}</td>
                </tr>
            @empty
                <tr><td colspan="7" class="mid muted">Aucun lot engagé à ce jour.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>

    {{-- img905 — AP initiale vers AP actuelle --}}
    <div class="section">
        <div class="section-title">Autorisation de programme</div>
        <table>
            <tr>
                <td class="key" style="width: 40%">AP initiale</td>
                <td class="num" colspan="3">{{ F::da($budget->apInitiale()) }}</td>
            </tr>
            <tr>
                <td class="key">Nombre de réévaluations</td>
                <td class="num" colspan="3">{{ $project->reevaluation_count ?? 0 }}</td>
            </tr>
            <tr>
                <th style="width: 40%">Réévaluation</th>
                <th style="width: 12%">Année</th>
                <th style="width: 24%">Montant</th>
                <th>Décision</th>
            </tr>
            @foreach ($revaluationSlots as $slot)
                <tr>
                    <td>{{ F::ordinal($slot['sequence']) }} réévaluation</td>
                    <td class="mid">{{ $slot['year'] }}</td>
                    <td class="num">{{ F::da($slot['amount']) }}</td>
                    <td>{{ $slot['reference'] }}</td>
                </tr>
            @endforeach
            <tr class="total">
                <td>Total de la réévaluation accordée</td>
                <td></td>
                <td class="num">{{ F::da($budget->totalReevaluations()) }}</td>
                <td></td>
            </tr>
            <tr class="total">
                <td>AP actuelle</td>
                <td></td>
                <td class="num">{{ F::da($budget->apActuelle()) }}</td>
                <td></td>
            </tr>
        </table>
    </div>

    <div class="section">
        <div class="section-title">Situation financière</div>
        <table>
            <tr>
                <td class="key" style="width: 30%">Cumul des engagements</td>
                <td class="num" style="width: 20%">{{ F::da($budget->cumulEngagements()) }}</td>
                <td class="key" style="width: 30%">Cumul des paiements</td>
                <td class="num">{{ F::da($budget->cumulPaiements()) }}</td>
            </tr>
            <tr>
                <td class="key">Programme en cours (PEC)</td>
                <td class="num">{{ F::da($budget->pec()) }}</td>
                <td class="key">Reste à réaliser (RAR)</td>
                <td class="num">{{ F::da($budget->rar()) }}</td>
            </tr>
            <tr>
                <td class="key">Reliquat d'AE à engager</td>
                <td class="num">{{ F::da($budget->reliquatAe()) }}</td>
                <td class="key">Reste à engager (lots)</td>
                <td class="num">{{ F::da($budget->resteAEngager()) }}</td>
            </tr>
        </table>
    </div>

@endsection
