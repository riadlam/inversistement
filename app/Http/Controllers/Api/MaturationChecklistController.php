<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MaturationChecklistDefinition;
use Illuminate\Http\JsonResponse;

class MaturationChecklistController extends Controller
{
    public function index(): JsonResponse
    {
        $items = MaturationChecklistDefinition::orderBy('sort_order')
            ->get(['id', 'key', 'label']);

        return response()->json(['data' => $items]);
    }
}
