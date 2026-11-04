<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\EvaluationRequest;
use App\Http\Resources\EvaluationResource;
use App\Models\Evaluation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Number;

class EvaluationController extends Controller
{
    /**
     * GET /api/evaluations?period=&employee_id=&per_page=
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $period = $request->string('period')->value();
        $employeeId = $request->integer('employee_id');
        $perPage = (int) Number::clamp($request->integer('per_page', 20), 1, 100);

        $evaluations = Evaluation::query()
            ->with(['employee', 'evaluator:id,name'])
            ->when($period, fn ($query) => $query->where('period', $period))
            ->when($employeeId, fn ($query) => $query->where('employee_id', $employeeId))
            ->latest()
            ->paginate($perPage)
            ->withQueryString();

        return EvaluationResource::collection($evaluations);
    }

    public function store(EvaluationRequest $request): JsonResponse
    {
        $evaluation = $request->user()?->evaluations()->create($request->validated());

        return EvaluationResource::make($evaluation?->load(['employee', 'evaluator:id,name']))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Evaluation $evaluation): EvaluationResource
    {
        return EvaluationResource::make($evaluation->load(['employee', 'evaluator:id,name']));
    }

    public function update(EvaluationRequest $request, Evaluation $evaluation): EvaluationResource
    {
        Gate::authorize('update', $evaluation);

        $evaluation->update($request->validated());

        return EvaluationResource::make($evaluation->load(['employee', 'evaluator:id,name']));
    }

    public function destroy(Evaluation $evaluation): Response
    {
        Gate::authorize('delete', $evaluation);

        $evaluation->delete();

        return response()->noContent();
    }
}
