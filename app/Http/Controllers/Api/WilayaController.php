<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Wilaya;
use Illuminate\Http\JsonResponse;

class WilayaController extends Controller
{
    public function index(): JsonResponse
    {
        $wilayas = Wilaya::orderBy('code')->get(['id', 'code', 'name_fr', 'name_ar']);

        return response()->json([
            'data' => $wilayas->map(fn (Wilaya $w) => [
                'id' => $w->id,
                'code' => $w->code,
                'legacy_code' => $w->legacyCode(),
                'name_fr' => $w->name_fr,
                'name_ar' => $w->name_ar,
            ]),
        ]);
    }
}
