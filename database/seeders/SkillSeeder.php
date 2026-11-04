<?php

namespace Database\Seeders;

use App\Models\Skill;
use Illuminate\Database\Seeder;

class SkillSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $skills = [
            'PHP' => '言語',
            'TypeScript' => '言語',
            'Python' => '言語',
            'Laravel' => 'フレームワーク',
            'Vue.js' => 'フレームワーク',
            'React' => 'フレームワーク',
            'AWS' => 'インフラ',
            'Docker' => 'インフラ',
            'Linux' => 'インフラ',
            'PostgreSQL' => 'データベース',
            'MySQL' => 'データベース',
            'Redis' => 'データベース',
            'Git' => 'ツール',
            'GitHub Actions' => 'ツール',
            'Figma' => 'ツール',
        ];

        foreach ($skills as $name => $category) {
            Skill::create(['name' => $name, 'category' => $category]);
        }
    }
}
