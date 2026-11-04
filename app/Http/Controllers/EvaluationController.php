<?php

namespace App\Http\Controllers;

use App\Http\Requests\EvaluationRequest;
use App\Models\Employee;
use App\Models\Evaluation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class EvaluationController extends Controller
{
    /**
     * 評価一覧 (期間で絞り込み)
     */
    public function index(Request $request): Response
    {
        $period = $request->string('period')->value();

        $evaluations = Evaluation::query()
            ->with(['employee:id,name,employee_code', 'evaluator:id,name'])
            ->when($period, fn ($query) => $query->where('period', $period))
            // 同じ時刻の行があってもページ間で重複・欠落しないよう、id でも並べる
            ->latest()
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('evaluations/Index', [
            'evaluations' => $evaluations,
            'periods' => Evaluation::query()->distinct()->orderByDesc('period')->pluck('period'),
            'filters' => ['period' => $period],
            'scoreLabels' => Evaluation::SCORE_LABELS,
        ]);
    }

    public function create(Request $request): Response
    {
        return Inertia::render('evaluations/Form', [
            ...$this->formOptions(),
            'defaultPeriod' => Evaluation::periodFor(now()),
            // 社員詳細の「評価する」から来たときは、その社員を選択済みにする
            'selectedEmployeeId' => $request->integer('employee_id') ?: null,
        ]);
    }

    public function store(EvaluationRequest $request): RedirectResponse
    {
        // リレーション経由で作るので evaluator_id は自動的にログインユーザーになる
        $request->user()?->evaluations()->create($request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => '評価を登録しました。']);

        return to_route('evaluations.index');
    }

    public function edit(Evaluation $evaluation): Response
    {
        Gate::authorize('update', $evaluation);

        return Inertia::render('evaluations/Form', [
            ...$this->formOptions(),
            'evaluation' => $evaluation,
            'defaultPeriod' => $evaluation->period,
            'selectedEmployeeId' => $evaluation->employee_id,
        ]);
    }

    /**
     * 本人の評価かどうかは EvaluationRequest::authorize() で確認済み。
     */
    public function update(EvaluationRequest $request, Evaluation $evaluation): RedirectResponse
    {
        $evaluation->update($request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => '評価を更新しました。']);

        return to_route('evaluations.index');
    }

    public function destroy(Evaluation $evaluation): RedirectResponse
    {
        Gate::authorize('delete', $evaluation);

        $evaluation->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => '評価を削除しました。']);

        return to_route('evaluations.index');
    }

    /**
     * @return array<string, mixed>
     */
    private function formOptions(): array
    {
        return [
            'employees' => Employee::query()->orderBy('employee_code')->get(['id', 'employee_code', 'name']),
            'scoreLabels' => Evaluation::SCORE_LABELS,
        ];
    }
}
