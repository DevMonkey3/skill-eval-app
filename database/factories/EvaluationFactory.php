<?php

namespace Database\Factories;

use App\Models\Employee;
use App\Models\Evaluation;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Evaluation>
 */
class EvaluationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'employee_id' => Employee::factory(),
            'evaluator_id' => User::factory(),
            'period' => fake()->randomElement(['2025-H1', '2025-H2', '2026-H1']),
            'score' => fake()->numberBetween(1, 5),
            'comment' => fake()->realText(80),
        ];
    }
}
