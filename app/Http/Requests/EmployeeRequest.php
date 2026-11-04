<?php

namespace App\Http\Requests;

use App\Models\Employee;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class EmployeeRequest extends FormRequest
{
    /**
     * ルートは auth ミドルウェアで守っているので、ここでは常に許可する。
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
        // 新規登録時は null、更新時は編集中の社員。更新時は自分自身を重複チェックから外す
        $employee = $this->route('employee');

        return [
            'employee_code' => ['required', 'string', 'max:10', Rule::unique(Employee::class)->ignore($employee)],
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:255', Rule::unique(Employee::class)->ignore($employee)],
            'department' => ['required', Rule::in(Employee::DEPARTMENTS)],
            'position' => ['required', Rule::in(Employee::POSITIONS)],
            'hired_on' => ['nullable', 'date', 'before_or_equal:today'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'employee_code' => '社員番号',
            'name' => '氏名',
            'email' => 'メールアドレス',
            'department' => '部署',
            'position' => '役職',
            'hired_on' => '入社日',
        ];
    }
}