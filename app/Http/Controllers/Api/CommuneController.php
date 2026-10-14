<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Commune;
use App\Models\Wilaya;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CommuneController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $wilaya = $this->resolveWilaya($request);

        if (! $wilaya) {
            return response()->json([
                'message' => 'Pass wilaya (name_fr), wilaya_id, or wilaya_code to list communes.',
                'data' => [],
            ], 422);
        }

        $communes = $wilaya->communeQuery()
            ->orderBy('lib_comm_fr')
            ->get(['code_comm', 'lib_comm_fr', 'lib_comm_ar', 'code_wilaya']);

        return response()->json([
            'data' => $communes->map(fn (Commune $c) => [
                'code' => $c->code_comm,
                'name_fr' => $c->lib_comm_fr,
                'name_ar' => $c->lib_comm_ar,
                'wilaya_code' => $c->code_wilaya,
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
            return Wilaya::where('name_fr', $request->input('wilaya'))->first();
        }

        return null;
    }
}
