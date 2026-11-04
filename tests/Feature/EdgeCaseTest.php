<?php

namespace Tests\Feature;

use App\Models\Employee;
use App\Models\Evaluation;
use App\Models\Skill;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * SQLite では気づきにくく、PostgreSQL や実際の運用で問題になるケース。
 */
class EdgeCaseTest extends TestCase
{
    use RefreshDatabase;

    public function test_non_numeric_ids_return_404_instead_of_a_database_error()
    {
        $this->actingAs(User::factory()->admin()->create());

        $this->get('/employees/abc')->assertNotFound();
        $this->get('/evaluations/abc/edit')->assertNotFound();
        $this->getJson('/api/evaluations/x')->assertNotFound();
    }

    public function test_non_numeric_ids_in_input_are_validation_errors()
    {
        Sanctum::actingAs(User::factory()->create());

        $this->postJson('/api/evaluations', ['employee_id' => 'abc', 'period' => '2026-H1', 'score' => 3])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('employee_id');
    }

    public function test_search_treats_percent_and_underscore_as_plain_text()
    {
        Employee::factory()->create(['name' => '山田 太郎', 'employee_code' => 'E00001']);

        $this->actingAs(User::factory()->create());

        foreach (['%' => 0, '_' => 0, '山田' => 1, 'e000' => 1] as $search => $count) {
            $this->get(route('employees.index', ['search' => $search]))
                ->assertInertia(fn (Assert $page) => $page->where('employees.total', $count));
        }
    }

    public function test_pagination_links_keep_the_filters()
    {
        Employee::factory(16)->create(['department' => '開発部']);

        $this->actingAs(User::factory()->create())
            ->get(route('employees.index', ['department' => '開発部']))
            ->assertInertia(fn (Assert $page) => $page
                ->where('employees.total', 16)
                ->where('employees.next_page_url', fn (string $url) => str_contains(urldecode($url), 'department=開発部')));
    }

    public function test_api_patch_updates_only_the_sent_fields()
    {
        $user = User::factory()->admin()->create();
        $employee = Employee::factory()->create(['email' => 'keep@example.com']);
        $evaluation = Evaluation::factory()->for($employee)->for($user, 'evaluator')->create(['score' => 2]);

        Sanctum::actingAs($user);

        $this->patchJson("/api/employees/{$employee->id}", ['name' => '新しい 名前'])->assertOk();
        $this->patchJson("/api/evaluations/{$evaluation->id}", ['score' => 5])->assertOk();

        $this->assertDatabaseHas('employees', ['id' => $employee->id, 'name' => '新しい 名前', 'email' => 'keep@example.com']);
        $this->assertDatabaseHas('evaluations', ['id' => $evaluation->id, 'score' => 5, 'period' => $evaluation->period]);
    }

    public function test_updating_someone_elses_evaluation_is_403_even_with_invalid_input()
    {
        $evaluation = Evaluation::factory()->create();

        Sanctum::actingAs(User::factory()->create());

        $this->putJson("/api/evaluations/{$evaluation->id}", ['score' => 99])->assertForbidden();
    }

    public function test_deleting_a_skill_removes_it_from_employees()
    {
        $skill = Skill::factory()->create();
        Employee::factory()->create()->skills()->attach($skill, ['level' => 3]);

        $this->actingAs(User::factory()->admin()->create())
            ->delete(route('skills.destroy', $skill))
            ->assertRedirect();

        $this->assertDatabaseMissing('employee_skill', ['skill_id' => $skill->id]);
    }

    public function test_dashboard_shows_this_periods_numbers()
    {
        $period = Evaluation::periodFor(now());
        $popular = Skill::factory()->create(['name' => 'PHP']);
        Skill::factory()->create(['name' => 'Go']);

        Employee::factory(2)->create()->each(function (Employee $employee) use ($popular, $period) {
            $employee->skills()->attach($popular, ['level' => 3]);
            Evaluation::factory()->for($employee)->create(['period' => $period, 'score' => $employee->id === 1 ? 4 : 5]);
        });
        Evaluation::factory()->create(['period' => '2000-H1']);

        $this->actingAs(User::factory()->create())
            ->get(route('dashboard'))
            ->assertInertia(fn (Assert $page) => $page
                ->component('Dashboard')
                ->where('currentPeriod', $period)
                ->where('stats.employees', 3)
                ->where('stats.skills', 2)
                ->where('stats.evaluations', 2)
                ->where('stats.averageScore', 4.5)
                ->where('topSkills.0.name', 'PHP')
                ->where('topSkills.0.employees_count', 2)
                ->has('recentEvaluations', 3));
    }
}
