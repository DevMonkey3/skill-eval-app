<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Evaluation;
use App\Models\Skill;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(): Response
    {
        $currentPeriod = Evaluation::periodFor(now());

        // 今期の件数と平均スコアを 1 回のクエリで取る
        $current = Evaluation::query()
            ->where('period', $currentPeriod)
            ->toBase()
            ->selectRaw('count(*) as total, avg(score) as average')
            ->first();

        return Inertia::render('Dashboard', [
            'currentPeriod' => $currentPeriod,
            'stats' => [
                'employees' => Employee::query()->count(),
                'skills' => Skill::query()->count(),
                'evaluations' => (int) ($current->total ?? 0),
                'averageScore' => round((float) ($current->average ?? 0), 1),
            ],
            'topSkills' => Skill::query()
                ->withCount('employees')
                ->orderByDesc('employees_count')
                ->orderBy('name')
                ->limit(5)
                ->get(['id', 'name', 'category']),
            'recentEvaluations' => Evaluation::query()
                ->with(['employee:id,name', 'evaluator:id,name'])
                ->latest()
                ->orderByDesc('id')
                ->limit(5)
                ->get(),
            'scoreLabels' => Evaluation::SCORE_LABELS,
        ]);
    }
}
