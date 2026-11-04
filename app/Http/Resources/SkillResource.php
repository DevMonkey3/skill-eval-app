<?php

namespace App\Http\Resources;

use App\Models\Skill;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Skill
 */
class SkillResource extends JsonResource
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
            'name' => $this->name,
            'category' => $this->category,
            // 社員経由で読み込んだときだけ、その社員のレベルを含める
            'level' => $this->whenPivotLoaded('employee_skill', fn () => (int) $this->pivot->getAttribute('level')),
        ];
    }
}
