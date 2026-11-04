<?php

namespace App\Http\Requests;

use App\Models\Skill;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class EmployeeSkillRequest extends FormRequest
{
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
            'skill_id' => ['required', 'integer', Rule::exists(Skill::class, 'id')],
            'level' => ['required', 'integer', 'between:1,5'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'skill_id' => 'スキル',
            'level' => 'レベル',
        ];
    }
}
