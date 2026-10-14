<?php

namespace App\Http\Controllers;

use App\Exports\EtatGlobalExport;
use App\Exports\FicheSuiviPdf;
use App\Exports\PitchTeleprompterPdf;
use App\Models\Project;
use App\Models\Wilaya;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * The two documents the ministry exchanges: the per-project fiche de suivi and
 * the portfolio-wide DGB canevas.
 *
 * Both go through the same wilaya scoping and policies as the screens, so an
 * export can never widen what a user can see.
 */
class ExportController extends Controller
{
    /** GET /dashbored/projects/{project}/fiche-suivi.pdf */
    public function ficheSuivi(string $projectId): Response
    {
        $project = Project::visibleTo()
            ->where(fn ($query) => $query->where('fip_id', $projectId)->orWhere('id', $projectId))
            ->firstOrFail();

        $this->authorize('export', $project);

        $fiche = FicheSuiviPdf::for($project);

        return response($fiche->contents(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="'.$fiche->filename().'"',
        ]);
    }

    /** GET /dashbored/pitch.pdf — short teleprompter for live demos. */
    public function pitch(): Response
    {
        $pitch = new PitchTeleprompterPdf;

        return response($pitch->contents(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="'.$pitch->filename().'"',
        ]);
    }

    /** GET /dashbored/reports/etat-global.xlsx */
    public function etatGlobal(Request $request): BinaryFileResponse
    {
        $this->authorize('viewAny', Project::class);

        $filters = $request->validate([
            'wilaya_id' => ['nullable', 'integer', Rule::exists('wilayas', 'id')],
            'titre' => ['nullable', Rule::in(['T3', 'T4', 't3', 't4'])],
            'year' => ['nullable', 'integer', 'min:1990', 'max:2100'],
            'arretee_au' => ['nullable', 'date'],
        ]);

        $user = $request->user();

        // A wilaya-scoped user always exports their own wilaya, whatever the
        // query string says.
        if (! $user->isNational()) {
            $filters['wilaya_id'] = $user->wilaya_id;
        }

        $export = new EtatGlobalExport(
            $filters,
            isset($filters['arretee_au']) ? Carbon::parse($filters['arretee_au']) : null,
        );

        return Excel::download($export, $export->filename());
    }

    /** Wilayas the signed-in user may filter the canevas by. */
    public static function exportableWilayas(?object $user): \Illuminate\Support\Collection
    {
        if ($user?->isNational()) {
            return Wilaya::orderBy('code')->get(['id', 'code', 'name_fr']);
        }

        return Wilaya::whereKey($user?->wilaya_id)->get(['id', 'code', 'name_fr']);
    }
}
