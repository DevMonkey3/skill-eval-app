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
     * 「自分が書いた評価か」はコントローラーの Gate (EvaluationPolicy) で確認する。
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'employee_id' => ['required', 'integer', Rule::exists(Employee::class, 'id')],
            'period' => [
                'required',
                'string',
                'regex:'.Evaluation::PERIOD_PATTERN,
                // DB のユニーク制約と同じ条件。先にここで弾いて分かりやすいメッセージを返す
                Rule::unique(Evaluation::class)
                    ->where('employee_id', $this->input('employee_id'))
                    ->where('evaluator_id', $this->user()?->getAuthIdentifier())
                    ->ignore($this->route('evaluation')),
            ],
            'score' => ['required', 'integer', 'between:1,5'],
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