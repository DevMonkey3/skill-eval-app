<?php

namespace App\Models;

use Carbon\CarbonInterface;
use Database\Factories\SkillFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * @property int $id
 * @property string $name
 * @property string $category
 * @property CarbonInterface|null $created_at
 * @property CarbonInterface|null $updated_at
 * @property-read Pivot $pivot 社員経由で読み込んだときの中間テーブル (employee_skill) の行
 */
#[Fillable(['name', 'category'])]
class Skill extends Model
{
    /** @use HasFactory<SkillFactory> */
    use HasFactory;

    /** @var list<string> */
    public const CATEGORIES = ['言語', 'フレームワーク', 'インフラ', 'データベース', 'ツール'];

    /** @var array<int, string> */
    public const LEVEL_LABELS = [1 => '入門', 2 => '基礎', 3 => '実務', 4 => '上級', 5 => 'エキスパート'];

    /**
     * @return BelongsToMany<Employee, $this>
     */
    public function employees(): BelongsToMany
    {
        return $this->belongsToMany(Employee::class)
            ->withPivot('level')
            ->withTimestamps();
    }
}
