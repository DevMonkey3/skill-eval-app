<?php

namespace Database\Factories;

use App\Models\Employee;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Employee>
 */
class EmployeeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'employee_code' => fake()->unique()->numerify('E#####'),
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'department' => fake()->randomElement(Employee::DEPARTMENTS),
            'position' => fake()->randomElement(Employee::POSITIONS),
            'hired_on' => fake()->dateTimeBetween('-10 years')->format('Y-m-d'),
        ];
    }
}
