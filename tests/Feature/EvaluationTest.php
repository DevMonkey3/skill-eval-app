<?php

namespace Tests\Feature;

use App\Models\Employee;
use App\Models\Evaluation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class EvaluationTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_can_be_filtered_by_period()
    {
        Evaluation::factory()->create(['period' => '2026-H1']);
        Evaluation::factory()->create(['period' => '2025-H2']);

        $this->actingAs(User::factory()->create())
            ->get(route('evaluations.index', ['period' => '2026-H1']))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('evaluations/Index')
                ->has('evaluations.data', 1)
                ->where('evaluations.data.0.period', '2026-H1')
                ->where('periods', ['2026-H1', '2025-H2']));
    }

    public function test_evaluator_is_always_the_logged_in_user()
    {
        $user = User::factory()->create();
        $someoneElse = User::factory()->create();
        $employee = Employee::factory()->create();

        $this->actingAs($user)
            ->post(route('evaluations.store'), [
                'employee_id' => $employee->id,
                'period' => '2026-H1',
                'score' => 4,
                'comment' => 'よく頑張りました',
                'evaluator_id' => $someoneElse->id, // なりすましの試み。無視されるべき
            ])
            ->assertRedirect(route('evaluations.index'));

        $this->assertDatabaseHas('evaluations', [
            'employee_id' => $employee->id,
            'evaluator_id' => $user->id,
            'score' => 4,
        ]);
    }

    public function test_period_format_and_score_range_are_validated()
    {
        $this->actingAs(User::factory()->create())
            ->post(route('evaluations.store'), [
                'employee_id' => Employee::factory()->create()->id,
                'period' => '2026-H3',
                'score' => 9,
            ])
            ->assertSessionHasErrors(['period', 'score']);
    }

    public function test_same_employee_cannot_be_evaluated_twice_in_one_period()
    {
        $user = User::factory()->create();
        $evaluation = Evaluation::factory()->for($user, 'evaluator')->create(['period' => '2026-H1']);

        $this->actingAs($user)
            ->post(route('evaluations.store'), [
                'employee_id' => $evaluation->employee_id,
                'period' => '2026-H1',
                'score' => 3,
            ])
            ->assertSessionHasErrors('period');

        $this->assertDatabaseCount('evaluations', 1);
    }

    public function test_evaluator_can_update_their_own_evaluation()
    {
        $user = User::factory()->create();
        $evaluation = Evaluation::factory()->for($user, 'evaluator')->create(['score' => 2]);

        $this->actingAs($user)
            ->put(route('evaluations.update', $evaluation), [
                'employee_id' => $evaluation->employee_id,
                'period' => $evaluation->period,
                'score' => 5,
            ])
            ->assertRedirect(route('evaluations.index'));

        $this->assertSame(5, $evaluation->fresh()?->score);
    }

    public function test_other_users_cannot_edit_update_or_delete_an_evaluation()
    {
        $evaluation = Evaluation::factory()->create(['score' => 2]);
        $otherUser = User::factory()->create();

        $this->actingAs($otherUser)
            ->get(route('evaluations.edit', $evaluation))
            ->assertForbidden();

        // 正しい内容を送っても、本人でなければ 403
        $this->actingAs($otherUser)
            ->put(route('evaluations.update', $evaluation), [
                'employee_id' => $evaluation->employee_id,
                'period' => $evaluation->period,
                'score' => 5,
            ])
            ->assertForbidden();

        $this->actingAs($otherUser)
            ->delete(route('evaluations.destroy', $evaluation))
            ->assertForbidden();

        $this->assertSame(2, $evaluation->fresh()?->score);
    }
}
