<?php

namespace Tests\Feature;

use App\Models\Employee;
use App\Models\Skill;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class EmployeeTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_the_login_page()
    {
        $this->get(route('employees.index'))->assertRedirect(route('login'));
    }

    public function test_index_lists_employees()
    {
        Employee::factory()->create(['name' => '山田 太郎']);

        $this->actingAs(User::factory()->admin()->create())
            ->get(route('employees.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('employees/Index')
                ->has('employees.data', 1)
                ->where('employees.data.0.name', '山田 太郎'));
    }

    public function test_index_can_be_searched_and_filtered()
    {
        Employee::factory()->create(['name' => '山田 太郎', 'department' => '開発部']);
        Employee::factory()->create(['name' => '佐藤 花子', 'department' => '営業部']);

        $user = User::factory()->admin()->create();

        $this->actingAs($user)
            ->get(route('employees.index', ['search' => '山田']))
            ->assertInertia(fn (Assert $page) => $page
                ->has('employees.data', 1)
                ->where('employees.data.0.name', '山田 太郎'));

        $this->actingAs($user)
            ->get(route('employees.index', ['department' => '営業部']))
            ->assertInertia(fn (Assert $page) => $page
                ->has('employees.data', 1)
                ->where('employees.data.0.name', '佐藤 花子'));
    }

    public function test_employee_can_be_created()
    {
        $data = Employee::factory()->make()->toArray();

        $response = $this->actingAs(User::factory()->admin()->create())
            ->post(route('employees.store'), $data);

        $employee = Employee::query()->where('email', $data['email'])->firstOrFail();
        $response->assertRedirect(route('employees.show', $employee));
    }

    public function test_required_fields_are_validated()
    {
        $this->actingAs(User::factory()->admin()->create())
            ->post(route('employees.store'), [])
            ->assertSessionHasErrors(['employee_code', 'name', 'email', 'department', 'position']);

        $this->assertDatabaseCount('employees', 0);
    }

    public function test_email_and_code_must_be_unique()
    {
        $existing = Employee::factory()->create();

        $this->actingAs(User::factory()->admin()->create())
            ->post(route('employees.store'), Employee::factory()->make([
                'email' => $existing->email,
                'employee_code' => $existing->employee_code,
            ])->toArray())
            ->assertSessionHasErrors(['email', 'employee_code']);
    }

    public function test_employee_can_be_updated_keeping_its_own_email()
    {
        $employee = Employee::factory()->create();

        $this->actingAs(User::factory()->admin()->create())
            ->put(route('employees.update', $employee), [...$employee->toArray(), 'name' => '新しい 名前'])
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('employees.show', $employee));

        $this->assertSame('新しい 名前', $employee->fresh()?->name);
    }

    public function test_deleting_an_employee_also_deletes_its_skill_links()
    {
        $employee = Employee::factory()->create();
        $employee->skills()->attach(Skill::factory()->create(), ['level' => 3]);

        $this->actingAs(User::factory()->admin()->create())
            ->delete(route('employees.destroy', $employee))
            ->assertRedirect(route('employees.index'));

        $this->assertModelMissing($employee);
        $this->assertDatabaseCount('employee_skill', 0);
    }

    public function test_skill_can_be_added_updated_and_removed()
    {
        $user = User::factory()->admin()->create();
        $employee = Employee::factory()->create();
        $skill = Skill::factory()->create();

        // 追加
        $this->actingAs($user)
            ->post(route('employees.skills.store', $employee), ['skill_id' => $skill->id, 'level' => 2]);
        $this->assertDatabaseHas('employee_skill', ['employee_id' => $employee->id, 'skill_id' => $skill->id, 'level' => 2]);

        // 同じスキルをもう一度送るとレベル更新になり、行は増えない
        $this->actingAs($user)
            ->post(route('employees.skills.store', $employee), ['skill_id' => $skill->id, 'level' => 5]);
        $this->assertDatabaseHas('employee_skill', ['employee_id' => $employee->id, 'skill_id' => $skill->id, 'level' => 5]);
        $this->assertDatabaseCount('employee_skill', 1);

        // 削除
        $this->actingAs($user)
            ->delete(route('employees.skills.destroy', [$employee, $skill]));
        $this->assertDatabaseCount('employee_skill', 0);
    }

    public function test_skill_level_must_be_between_1_and_5()
    {
        $employee = Employee::factory()->create();

        $this->actingAs(User::factory()->admin()->create())
            ->post(route('employees.skills.store', $employee), [
                'skill_id' => Skill::factory()->create()->id,
                'level' => 6,
            ])
            ->assertSessionHasErrors('level');
    }
}
