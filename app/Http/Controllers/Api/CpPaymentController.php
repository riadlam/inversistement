<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CpPayment;
use App\Models\Project;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CpPaymentController extends Controller
{
    public function store(Request $request, string $projectId): JsonResponse
    {
        $project = Project::where('fip_id', $projectId)->firstOrFail();

        $validated = $request->validate([
            'amount_da' => 'required|numeric|min:0',
            'description' => 'nullable|string|max:1000',
            'invoice' => 'nullable|file|max:10240',
            'paid_at' => 'required|date',
        ]);

        $invoicePath = null;
        if ($request->hasFile('invoice')) {
            $invoicePath = $request->file('invoice')->store('cp-invoices', 'public');
        }

        $payment = CpPayment::create([
            'project_id' => $project->id,
            'amount_da' => $validated['amount_da'],
            'description' => $validated['description'] ?? null,
            'invoice' => $invoicePath,
            'paid_at' => $validated['paid_at'],
            'payment_type' => 'regular',
            'created_by' => $request->user()->id,
        ]);

        return response()->json([
            'data' => $payment->load('project'),
        ], 201);
    }
}
