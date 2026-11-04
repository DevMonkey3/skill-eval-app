<?php

namespace Tests\Feature;

use App\Models\Employee;
use App\Models\Skill;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * 社員・スキルの登録 / 編集 / 削除は管理者だけ。一般ユーザーは閲覧と評価の登録ができる。
 */
class AdminAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_regular_users_can_view_employees_and_skills_and_write_evaluations()
    {
        $employee = Employee::factory()->create();

        $this->actingAs(User::factory()->create());

        $this->get(route('employees.index'))->assertOk();
        $this->get(route('employees.show', $employee))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->where('auth.user.is_admin', false));
        $this->get(route('skills.index'))->assertOk();
        $this->get(route('evaluations.create'))->assertOk();
    }

    public function test_regular_users_cannot_change_employees()
    {
        $employee = Employee::factory()->create();

        $this->actingAs(User::factory()->create());

        $this->get(route('employees.create'))->assertForbidden();
        $this->post(route('employees.store'), [])->assertForbidden();
        $this->get(route('employees.edit', $employee))->assertForbidden();
        $this->put(route('employees.update', $employee), [])->assertForbidden();
        $this->delete(route('employees.destroy', $employee))->assertForbidden();

        $this->assertModelExists($employee);
    }

    public function test_regular_users_cannot_change_skills_or_skill_levels()
    {
        $employee = Employee::factory()->create();
        $skill = Skill::factory()->create();
        $employee->skills()->attach($skill, ['level' => 3]);

        $this->actingAs(User::factory()->create());

        $this->get(route('skills.create'))->assertForbidden();
        $this->post(route('skills.store'), [])->assertForbidden();
        $this->put(route('skills.update', $skill), [])->assertForbidden();
        $this->delete(route('skills.destroy', $skill))->assertForbidden();
        $this->post(route('employees.skills.store', $employee), ['skill_id' => $skill->id, 'level' => 5])->assertForbidden();
        $this->delete(route('employees.skills.destroy', [$employee, $skill]))->assertForbidden();

        $this->assertDatabaseHas('employee_skill', ['employee_id' => $employee->id, 'skill_id' => $skill->id, 'level' => 3]);
    }

    public function test_regular_users_can_read_but_not_change_employees_through_the_api()
    {
        $employee = Employee::factory()->create();

        Sanctum::actingAs(User::factory()->create());

        $this->getJson('/api/employees')->assertOk();
        $this->getJson("/api/employees/{$employee->id}")->assertOk();
        $this->postJson('/api/employees', [])->assertForbidden();
        $this->putJson("/api/employees/{$employee->id}", [])->assertForbidden();
        $this->deleteJson("/api/employees/{$employee->id}")->assertForbidden();

        $this->assertModelExists($employee);
    }

    public function test_users_cannot_make_themselves_admin_when_registering()
    {
        $this->post(route('register.store'), [
            'name' => 'Test User',
            'email' => 'new@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'is_admin' => true,
        ]);

        $this->assertDatabaseHas('users', ['email' => 'new@example.com', 'is_admin' => false]);
    }
}
