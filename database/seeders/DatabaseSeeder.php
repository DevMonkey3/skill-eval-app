<?php

namespace Database\Seeders;

use App\Models\Employee;
use App\Models\Evaluation;
use App\Models\Skill;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // パスワードはどちらも "password" (UserFactory の既定値)
        // test@ は社員・スキルも編集できる管理者、manager@ は閲覧と評価だけができる一般ユーザー
        $users = collect([
            User::factory()->admin()->create(['name' => 'テスト ユーザー', 'email' => 'test@example.com']),
            User::factory()->create(['name' => '評価 マネージャー', 'email' => 'manager@example.com']),
        ]);

        $this->call(SkillSeeder::class);
        $skills = Skill::all();

        Employee::factory(40)->create()->each(function (Employee $employee) use ($skills, $users) {
            // 3〜6個のスキルをランダムなレベルで付ける
            $employee->skills()->attach(
                $skills->random(rand(3, 6))->mapWithKeys(fn (Skill $skill) => [$skill->id => ['level' => rand(1, 5)]])
            );

            // 前期と今期の評価を1件ずつ作る
            foreach ([Evaluation::periodFor(now()->subMonths(6)), Evaluation::periodFor(now())] as $period) {
                Evaluation::factory()
                    ->for($employee)
                    ->for($users->random(), 'evaluator')
                    ->create(['period' => $period]);
            }
        });
    }
}
