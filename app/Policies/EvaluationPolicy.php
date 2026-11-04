<?php

namespace App\Policies;

use App\Models\Evaluation;
use App\Models\User;

class EvaluationPolicy
{
    /**
     * 評価を編集できるのは、その評価を書いた本人だけ。
     */
    public function update(User $user, Evaluation $evaluation): bool
    {
        return $user->id === $evaluation->evaluator_id;
    }

    /**
     * 評価を削除できるのも、その評価を書いた本人だけ。
     */
    public function delete(User $user, Evaluation $evaluation): bool
    {
        return $user->id === $evaluation->evaluator_id;
    }
}
