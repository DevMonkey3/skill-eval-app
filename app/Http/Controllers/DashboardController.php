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
        $currentEvaluations = Evaluation::query()->where('period', $currentPeriod);

        return Inertia::render('Dashboard', [
            'currentPeriod' => $currentPeriod,
            'stats' => [
                'employees' => Employee::query()->count(),
                'skills' => Skill::query()->count(),
                'evaluations' => (clone $currentEvaluations)->count(),
                'averageScore' => round((float) (clone $currentEvaluations)->avg('score'), 1),
            ],
            'topSkills' => Skill::query()
                ->withCount('employees')
                ->orderByDesc('employees_count')
                ->limit(5)
                ->get(['id', 'name', 'category']),
            'recentEvaluations' => Evaluation::query()
                ->with(['employee:id,name', 'evaluator:id,name'])
                ->latest()
                ->limit(5)
                ->get(),
            'scoreLabels' => Evaluation::SCORE_LABELS,
        ]);
    }
}
