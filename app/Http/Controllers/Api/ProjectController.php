<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MaturationChecklistDefinition;
use App\Models\Programme;
use App\Models\Project;
use App\Models\RisksFile;
use App\Models\CpProjection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProjectController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title_fr' => 'required|string|max:255',
            'title_ar' => 'required|string|max:255',
            'description' => 'nullable|string|max:10000',

            'programme_code' => 'required|string|max:50|exists:programmes,code',
            'wilaya_code' => 'required|string|max:2|exists:wilayas,code',
            'commune_code' => 'nullable|string|max:5',
            'establishment_id' => 'nullable|integer|exists:tab_etablissement,id',
            'project_owner_id' => 'required|integer|exists:project_owners,id',

            'nature' => 'nullable|in:new,extension,rehabilitation',
            'deadline' => 'required|date',

            'ae_da' => 'required|numeric|min:0',
            'cp_da' => 'required|numeric|min:0',

            'cp_projections' => 'required|array|min:1',
            'cp_projections.*.year_offset' => 'required|integer|min:0',
            'cp_projections.*.amount_da' => 'required|numeric|min:0',

            'amount_committed_da' => 'required|numeric|min:0',
            'ods_date' => 'required|date',
            'project_duration_months' => 'required|integer|min:1|max:600',

            'company_name' => 'required|string|max:255',
            'nif' => 'required|string|max:50',
            'business_register' => 'required|string|max:50',

            'nc13_code_id' => 'nullable|integer|exists:nc13_codes,id',
            'budget_type' => 'nullable|in:T3,T4',

            'maturation' => 'nullable|array',
            'maturation.*' => 'string|exists:maturation_checklist_definitions,key',

            'risk_files' => 'nullable|array',
            'risk_files.*.notes' => 'nullable|string|max:1000',
            'risk_files.*.file' => 'required_with:risk_files.*.notes|file|max:10240',
        ]);

        $programme = Programme::where('code', $validated['programme_code'])->firstOrFail();
        $wilaya = DB::table('wilayas')->where('code', $validated['wilaya_code'])->firstOrFail();

        $fipId = $this->generateFipId();

        $project = DB::transaction(function () use ($validated, $programme, $wilaya, $fipId, $request) {
            $project = Project::create([
                'fip_id' => $fipId,
                'title_fr' => $validated['title_fr'],
                'title_ar' => $validated['title_ar'],
                'description' => $validated['description'] ?? null,
                'programme_id' => $programme->id,
                'wilaya_id' => $wilaya->id,
                'commune_code' => $validated['commune_code'] ?? null,
                'establishment_id' => $validated['establishment_id'] ?? null,
                'project_owner_id' => $validated['project_owner_id'],
                'nature' => $validated['nature'] ?? 'new',
                'status' => 'in_arbitration',
                'deadline' => $validated['deadline'],
                'ae_da' => $validated['ae_da'],
                'cp_da' => $validated['cp_da'],
                'amount_committed_da' => $validated['amount_committed_da'],
                'ods_date' => $validated['ods_date'],
                'project_duration_months' => $validated['project_duration_months'],
                'company_name' => $validated['company_name'],
                'nif' => $validated['nif'],
                'business_register' => $validated['business_register'],
                'nc13_code_id' => $validated['nc13_code_id'] ?? null,
                'budget_type' => $validated['budget_type'] ?? null,
                'created_by' => $request->user()->id,
            ]);

            if (! empty($validated['maturation'])) {
                $definitionIds = MaturationChecklistDefinition::whereIn('key', $validated['maturation'])->pluck('id');
                $pivotData = $definitionIds->mapWithKeys(fn ($id) => [$id => ['checked' => true]]);
                $project->maturationItems()->attach($pivotData->toArray());
            }

            foreach ($validated['cp_projections'] as $proj) {
                $project->cpProjections()->create([
                    'year_offset' => $proj['year_offset'],
                    'amount_da' => $proj['amount_da'],
                ]);
            }

            if (! empty($validated['risk_files'])) {
                foreach ($validated['risk_files'] as $rf) {
                    if (! isset($rf['file'])) {
                        continue;
                    }
                    $path = $rf['file']->store('risk-files', 'public');
                    RisksFile::create([
                        'project_id' => $project->id,
                        'notes' => $rf['notes'] ?? null,
                        'file' => $path,
                    ]);
                }
            }

            return $project;
        });

        $project->load(['programme', 'maturationItems', 'riskFiles', 'cpProjections', 'nc13Code']);

        return response()->json([
            'data' => $project,
        ], 201);
    }

    public function update(Request $request, string $fipId): JsonResponse
    {
        $project = Project::where('fip_id', $fipId)->firstOrFail();

        $validated = $request->validate([
            'title_fr' => 'sometimes|string|max:255',
            'title_ar' => 'sometimes|string|max:255',
            'description' => 'nullable|string|max:10000',
            'commune_code' => 'nullable|string|max:5',
            'establishment_id' => 'nullable|integer|exists:tab_etablissement,id',
            'project_owner_id' => 'sometimes|integer|exists:project_owners,id',
            'nature' => 'nullable|in:new,extension,rehabilitation',
            'deadline' => 'sometimes|date',
            'ae_da' => 'sometimes|numeric|min:0',
            'cp_da' => 'sometimes|numeric|min:0',
            'amount_committed_da' => 'sometimes|numeric|min:0',
            'ods_date' => 'sometimes|date',
            'project_duration_months' => 'sometimes|integer|min:1|max:600',
            'company_name' => 'sometimes|string|max:255',
            'nif' => 'sometimes|string|max:50',
            'business_register' => 'sometimes|string|max:50',
            'status' => 'sometimes|string|in:draft,submitted,in_execution,completed,closed,cancelled',
            'nc13_code_id' => 'nullable|integer|exists:nc13_codes,id',
            'budget_type' => 'nullable|in:T3,T4',
        ]);

        $project->update($validated);

        return response()->json(['data' => $project->fresh()]);
    }

    public function destroy(string $fipId): JsonResponse
    {
        $project = Project::where('fip_id', $fipId)->firstOrFail();
        $project->delete();

        return response()->json(['message' => 'Project deleted.']);
    }

    public function destroyRiskFile(int $id): JsonResponse
    {
        $file = RisksFile::findOrFail($id);
        $file->delete();

        return response()->json(['message' => 'Risk file deleted.']);
    }

    private function generateFipId(): string
    {
        $year = now()->format('Y');
        $prefix = "FIP-{$year}-";
        $last = Project::withTrashed()
            ->where('fip_id', 'like', "{$prefix}%")
            ->orderBy('fip_id', 'desc')
            ->value('fip_id');

        if ($last) {
            $num = (int) Str::afterLast($last, '-') + 1;
        } else {
            $num = 1;
        }

        return $prefix . str_pad((string) $num, 3, '0', STR_PAD_LEFT);
    }
}
