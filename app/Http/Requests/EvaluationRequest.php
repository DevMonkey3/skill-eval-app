<?php

namespace App\Http\Requests;

use App\Models\Employee;
use App\Models\Evaluation;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class EvaluationRequest extends FormRequest
{
    /**
     * 新規登録は誰でもできる。更新は EvaluationPolicy で「評価を書いた本人か」を確認する。
     * ここで確認すると、他人の評価に不正な値を送った場合も 422 ではなく 403 になる。
     */
    public function authorize(): bool
    {
        $evaluation = $this->route('evaluation');

        if (! $evaluation instanceof Evaluation) {
            return true;
        }

        return $this->user()?->can('update', $evaluation) ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $evaluation = $this->route('evaluation');
        $evaluation = $evaluation instanceof Evaluation ? $evaluation : null;

        // API の PATCH では送った項目だけを更新できるようにする
        $required = $this->isMethod('PATCH') ? 'sometimes' : 'required';

        return [
            // bail: 数値でなければそこで止め、PostgreSQL に文字列の ID で問い合わせないようにする
            'employee_id' => ['bail', $required, 'integer', Rule::exists(Employee::class, 'id')],
            'period' => [
                $required,
                'string',
                'regex:'.Evaluation::PERIOD_PATTERN,
                // DB のユニーク制約と同じ条件。先にここで弾いて分かりやすいメッセージを返す
                Rule::unique(Evaluation::class)
                    ->where('employee_id', $this->integer('employee_id', $evaluation->employee_id ?? 0))
                    ->where('evaluator_id', $this->user()?->getAuthIdentifier())
                    ->ignore($evaluation),
            ],
            'score' => [$required, 'integer', 'between:1,5'],
            'comment' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'employee_id' => '社員',
            'period' => '評価期間',
            'score' => 'スコア',
            'comment' => 'コメント',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'period.regex' => '評価期間は「2026-H1」の形式で入力してください。',
            'period.unique' => 'この社員のこの期間の評価は、すでに登録済みです。',
        ];
    }
}
