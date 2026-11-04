<?php

namespace Tests\Feature\Api;

use App\Models\Employee;
use App\Models\Skill;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class EmployeeApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_requests_without_a_token_are_rejected()
    {
        $this->getJson('/api/employees')->assertUnauthorized();
    }

    public function test_employees_can_be_listed()
    {
        Sanctum::actingAs(User::factory()->admin()->create());
        Employee::factory(3)->create();

        $this->getJson('/api/employees')
            ->assertOk()
            ->assertJsonCount(3, 'data')
            ->assertJsonStructure([
                'data' => [['id', 'employee_code', 'name', 'email', 'department', 'position', 'hired_on', 'skills_count']],
                'links',
                'meta' => ['current_page', 'last_page', 'total'],
            ]);
    }

    public function test_per_page_is_capped_at_100()
    {
        Sanctum::actingAs(User::factory()->admin()->create());

        $this->getJson('/api/employees?per_page=100000')
            ->assertOk()
            ->assertJsonPath('meta.per_page', 100);
    }

    public function test_employee_can_be_shown_with_skill_levels()
    {
        Sanctum::actingAs(User::factory()->admin()->create());
        $employee = Employee::factory()->create();
        $employee->skills()->attach(Skill::factory()->create(['name' => 'Laravel']), ['level' => 4]);

        $this->getJson("/api/employees/{$employee->id}")
            ->assertOk()
            ->assertJsonPath('data.skills.0.name', 'Laravel')
            ->assertJsonPath('data.skills.0.level', 4);
    }

    public function test_missing_employee_returns_404()
    {
        Sanctum::actingAs(User::factory()->admin()->create());

        // PostgreSQL では ID の連番がテスト間でリセットされないので、確実に存在しない大きな ID を使う
        $this->getJson('/api/employees/999999999')->assertNotFound();
    }

    public function test_store_validates_input()
    {
        Sanctum::actingAs(User::factory()->admin()->create());

        $this->postJson('/api/employees', ['email' => 'not-an-email'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['employee_code', 'name', 'email', 'department', 'position']);
    }

    public function test_employee_can_be_created_updated_and_deleted()
    {
        Sanctum::actingAs(User::factory()->admin()->create());
        $data = Employee::factory()->make()->toArray();

        $id = $this->postJson('/api/employees', $data)
            ->assertCreated()
            ->assertJsonPath('data.email', $data['email'])
            ->json('data.id');

        $this->putJson("/api/employees/{$id}", [...$data, 'name' => '更新後の名前'])
            ->assertOk()
            ->assertJsonPath('data.name', '更新後の名前');

        $this->deleteJson("/api/employees/{$id}")->assertNoContent();

        $this->assertDatabaseCount('employees', 0);
    }
}
