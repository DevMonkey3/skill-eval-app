<?php

namespace App\Http\Controllers;

use App\Http\Requests\EmployeeSkillRequest;
use App\Models\Employee;
use App\Models\Skill;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;

class EmployeeSkillController extends Controller
{
    /**
     * スキルの追加とレベル変更を1つで扱う。
     * 未登録なら追加、登録済みならレベルを更新し、他のスキルには触らない。
     */
    public function store(EmployeeSkillRequest $request, Employee $employee): RedirectResponse
    {
        $employee->skills()->syncWithoutDetaching([
            $request->integer('skill_id') => ['level' => $request->integer('level')],
        ]);

        Inertia::flash('toast', ['type' => 'success', 'message' => 'スキルを更新しました。']);

        return back();
    }

    public function destroy(Employee $employee, Skill $skill): RedirectResponse
    {
        $employee->skills()->detach($skill);

        Inertia::flash('toast', ['type' => 'success', 'message' => 'スキルを外しました。']);

        return back();
    }
}