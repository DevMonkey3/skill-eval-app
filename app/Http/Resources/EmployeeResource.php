<?php

namespace App\Http\Resources;

use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Employee
 */
class EmployeeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'employee_code' => $this->employee_code,
            'name' => $this->name,
            'email' => $this->email,
            'department' => $this->department,
            'position' => $this->position,
            'hired_on' => $this->hired_on?->toDateString(),
            // withCount / load したときだけ含める (余計なクエリを発生させない)
            'skills_count' => $this->whenCounted('skills'),
            'skills' => SkillResource::collection($this->whenLoaded('skills')),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
