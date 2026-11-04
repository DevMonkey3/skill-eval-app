<?php

namespace Tests\Feature\Api;

use App\Models\Employee;
use App\Models\Evaluation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class EvaluationApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_evaluations_can_be_listed_and_filtered()
    {
        Sanctum::actingAs(User::factory()->create());
        Evaluation::factory()->create(['period' => '2026-H1']);
        Evaluation::factory()->create(['period' => '2025-H2']);

        $this->getJson('/api/evaluations?period=2026-H1')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.period', '2026-H1')
            ->assertJsonStructure(['data' => [['id', 'score', 'score_label', 'employee' => ['id', 'name'], 'evaluator' => ['id', 'name']]]]);
    }

    public function test_evaluation_is_created_for_the_token_owner()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);
        $employee = Employee::factory()->create();

        $this->postJson('/api/evaluations', [
            'employee_id' => $employee->id,
            'period' => '2026-H1',
            'score' => 5,
        ])
            ->assertCreated()
            ->assertJsonPath('data.evaluator.id', $user->id)
            ->assertJsonPath('data.score_label', '卓越');
    }

    public function test_duplicate_evaluation_returns_422()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);
        $evaluation = Evaluation::factory()->for($user, 'evaluator')->create(['period' => '2026-H1']);

        $this->postJson('/api/evaluations', [
            'employee_id' => $evaluation->employee_id,
            'period' => '2026-H1',
            'score' => 3,
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('period');
    }

    public function test_only_the_evaluator_can_update_or_delete()
    {
        $evaluation = Evaluation::factory()->create();
        $payload = [
            'employee_id' => $evaluation->employee_id,
            'period' => $evaluation->period,
            'score' => 1,
        ];

        Sanctum::actingAs(User::factory()->create());
        $this->putJson("/api/evaluations/{$evaluation->id}", $payload)->assertForbidden();
        $this->deleteJson("/api/evaluations/{$evaluation->id}")->assertForbidden();

        Sanctum::actingAs($evaluation->evaluator()->firstOrFail());
        $this->putJson("/api/evaluations/{$evaluation->id}", $payload)
            ->assertOk()
            ->assertJsonPath('data.score', 1);
        $this->deleteJson("/api/evaluations/{$evaluation->id}")->assertNoContent();
    }
}
