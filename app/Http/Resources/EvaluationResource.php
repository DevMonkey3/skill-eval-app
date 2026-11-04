<?php

namespace App\Http\Resources;

use App\Models\Evaluation;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Evaluation
 */
class EvaluationResource extends JsonResource
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
            'period' => $this->period,
            'score' => $this->score,
            'score_label' => Evaluation::SCORE_LABELS[$this->score] ?? null,
            'comment' => $this->comment,
            'employee' => EmployeeResource::make($this->whenLoaded('employee')),
            // 評価者はメールアドレスなどを出さず、id と名前だけにする
            'evaluator' => $this->whenLoaded('evaluator', fn () => [
                'id' => $this->evaluator->id,
                'name' => $this->evaluator->name,
            ]),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
