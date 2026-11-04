<?php

namespace Tests\Feature;

use App\Models\Employee;
use App\Models\Skill;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class SkillTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_shows_holder_count_and_average_level()
    {
        $skill = Skill::factory()->create(['name' => 'Laravel']);
        Employee::factory()->create()->skills()->attach($skill, ['level' => 2]);
        Employee::factory()->create()->skills()->attach($skill, ['level' => 4]);

        $this->actingAs(User::factory()->create())
            ->get(route('skills.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('skills/Index')
                ->where('skills.0.name', 'Laravel')
                ->where('skills.0.employees_count', 2)
                ->where('skills.0.average_level', fn ($average) => (float) $average === 3.0));
    }

    public function test_skill_can_be_created_updated_and_deleted()
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('skills.store'), ['name' => 'Laravel', 'category' => 'フレームワーク'])
            ->assertRedirect(route('skills.index'));

        $skill = Skill::query()->where('name', 'Laravel')->firstOrFail();

        $this->actingAs($user)
            ->put(route('skills.update', $skill), ['name' => 'Laravel 13', 'category' => 'フレームワーク'])
            ->assertRedirect(route('skills.index'));
        $this->assertSame('Laravel 13', $skill->fresh()?->name);

        $this->actingAs($user)
            ->delete(route('skills.destroy', $skill))
            ->assertRedirect(route('skills.index'));
        $this->assertModelMissing($skill);
    }

    public function test_name_must_be_unique_and_category_must_be_known()
    {
        Skill::factory()->create(['name' => 'Laravel']);

        $this->actingAs(User::factory()->create())
            ->post(route('skills.store'), ['name' => 'Laravel', 'category' => '存在しないカテゴリ'])
            ->assertSessionHasErrors(['name', 'category']);
    }
}
