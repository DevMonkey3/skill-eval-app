<?php

namespace App\Http\Controllers;

use App\Http\Requests\EmployeeRequest;
use App\Models\Employee;
use App\Models\Evaluation;
use App\Models\Skill;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class EmployeeController extends Controller
{
    /**
     * 社員一覧 (名前・社員番号での検索と部署での絞り込みつき)
     */
    public function index(Request $request): Response
    {
        $search = $request->string('search')->trim()->value();
        $department = $request->string('department')->value();

        $employees = Employee::query()
            ->withCount('skills')
            ->when($search, fn ($query) => $query->where(fn ($query) => $query
                ->whereLike('name', "%{$search}%")
                ->orWhereLike('employee_code', "%{$search}%")))
            ->when($department, fn ($query) => $query->where('department', $department))
            ->orderBy('employee_code')
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('employees/Index', [
            'employees' => $employees,
            'filters' => ['search' => $search, 'department' => $department],
            'departments' => Employee::DEPARTMENTS,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('employees/Form', $this->formOptions());
    }

    public function store(EmployeeRequest $request): RedirectResponse
    {
        $employee = Employee::create($request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => '社員を登録しました。']);

        return to_route('employees.show', $employee);
    }

    /**
     * 社員の詳細 (保有スキルと評価履歴)
     */
    public function show(Employee $employee): Response
    {
        $employee->load(['skills', 'evaluations.evaluator:id,name']);

        return Inertia::render('employees/Show', [
            'employee' => $employee,
            // まだ持っていないスキルだけを「追加」の選択肢にする
            'availableSkills' => Skill::query()
                ->whereKeyNot($employee->skills->modelKeys())
                ->orderBy('name')
                ->get(['id', 'name', 'category']),
            'levelLabels' => Skill::LEVEL_LABELS,
            'scoreLabels' => Evaluation::SCORE_LABELS,
        ]);
    }

    public function edit(Employee $employee): Response
    {
        return Inertia::render('employees/Form', [
            'employee' => $employee,
            ...$this->formOptions(),
        ]);
    }

    public function update(EmployeeRequest $request, Employee $employee): RedirectResponse
    {
        $employee->update($request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => '社員情報を更新しました。']);

        return to_route('employees.show', $employee);
    }

    public function destroy(Employee $employee): RedirectResponse
    {
        $employee->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => '社員を削除しました。']);

        return to_route('employees.index');
    }

    /**
     * 登録・編集フォームのセレクトボックスの選択肢
     *
     * @return array<string, list<string>>
     */
    private function formOptions(): array
    {
        return [
            'departments' => Employee::DEPARTMENTS,
            'positions' => Employee::POSITIONS,
        ];
    }
}
