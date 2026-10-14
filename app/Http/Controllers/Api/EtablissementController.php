<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TabEtablissement;
use App\Models\Wilaya;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EtablissementController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = TabEtablissement::query()
            ->orderBy('wilaya')
            ->orderBy('libelle')
            ->orderBy('Code établissement');

        $wilaya = $this->resolveWilaya($request);

        if ($wilaya) {
            $query->whereIn('wilaya', $wilaya->territoryKeys());
        } elseif ($request->filled('wilaya') || $request->filled('wilaya_id') || $request->filled('wilaya_code')) {
            return response()->json([
                'message' => 'Unknown wilaya filter.',
                'data' => [],
            ], 422);
        }

        $etablissements = $query->get(['id', 'wilaya', 'Code établissement', 'libelle']);

        return response()->json([
            'data' => $etablissements->map(fn ($e) => [
                'id' => $e->id,
                'wilaya' => $e->wilaya,
                'code' => $e->{'Code établissement'},
                'label' => $e->libelle,
            ]),
        ]);
    }

    private function resolveWilaya(Request $request): ?Wilaya
    {
        if ($request->filled('wilaya_id')) {
            return Wilaya::find((int) $request->input('wilaya_id'));
        }

        if ($request->filled('wilaya_code')) {
            $code = (string) $request->input('wilaya_code');

            return Wilaya::query()
                ->where('code', $code)
                ->orWhere('code', str_pad(ltrim($code, '0'), 2, '0', STR_PAD_LEFT))
                ->orWhereRaw('ltrim(code, "0") = ?', [ltrim($code, '0')])
                ->first();
        }

        if ($request->filled('wilaya')) {
            $nameOrCode = (string) $request->input('wilaya');

            return Wilaya::where('name_fr', $nameOrCode)->first()
                ?? Wilaya::query()
                    ->where('code', $nameOrCode)
                    ->orWhereRaw('ltrim(code, "0") = ?', [ltrim($nameOrCode, '0')])
                    ->first();
        }

        return null;
    }
}
