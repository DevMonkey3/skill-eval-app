<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\EmployeeRequest;
use App\Http\Resources\EmployeeResource;
use App\Models\Employee;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Number;

class EmployeeController extends Controller
{
    /**
     * GET /api/employees?search=&department=&per_page=
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $search = $request->string('search')->trim()->value();
        $department = $request->string('department')->value();
        // ?per_page=100000 のような大量取得を防ぐため 1〜100 に収める
        $perPage = (int) Number::clamp($request->integer('per_page', 15), 1, 100);

        $employees = Employee::query()
            ->withCount('skills')
            ->when($search, fn ($query) => $query->where(fn ($query) => $query
                ->whereLike('name', "%{$search}%")
                ->orWhereLike('employee_code', "%{$search}%")))
            ->when($department, fn ($query) => $query->where('department', $department))
            ->orderBy('employee_code')
            ->paginate($perPage)
            ->withQueryString();

        return EmployeeResource::collection($employees);
    }

    public function store(EmployeeRequest $request): JsonResponse
    {
        $employee = Employee::create($request->validated());

        return EmployeeResource::make($employee)->response()->setStatusCode(201);
    }

    public function show(Employee $employee): EmployeeResource
    {
        return EmployeeResource::make($employee->load('skills')->loadCount('skills'));
    }

    public function update(EmployeeRequest $request, Employee $employee): EmployeeResource
    {
        $employee->update($request->validated());

        return EmployeeResource::make($employee);
    }

    public function destroy(Employee $employee): Response
    {
        $employee->delete();

        return response()->noContent();
    }
}
