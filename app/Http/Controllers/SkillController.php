<?php

namespace App\Http\Controllers;

use App\Http\Requests\SkillRequest;
use App\Models\Skill;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class SkillController extends Controller
{
    /**
     * スキル一覧 (保有者数と平均レベルつき)
     */
    public function index(): Response
    {
        $skills = Skill::query()
            ->withCount('employees')
            ->withAvg('employees as average_level', 'employee_skill.level')
            ->orderBy('name')
            ->get();

        return Inertia::render('skills/Index', [
            'skills' => $skills,
            'categories' => Skill::CATEGORIES,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('skills/Form', [
            'categories' => Skill::CATEGORIES,
        ]);
    }

    public function store(SkillRequest $request): RedirectResponse
    {
        Skill::create($request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => 'スキルを登録しました。']);

        return to_route('skills.index');
    }

    public function edit(Skill $skill): Response
    {
        return Inertia::render('skills/Form', [
            'skill' => $skill,
            'categories' => Skill::CATEGORIES,
        ]);
    }

    public function update(SkillRequest $request, Skill $skill): RedirectResponse
    {
        $skill->update($request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => 'スキルを更新しました。']);

        return to_route('skills.index');
    }

    public function destroy(Skill $skill): RedirectResponse
    {
        $skill->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => 'スキルを削除しました。']);

        return to_route('skills.index');
    }
}
