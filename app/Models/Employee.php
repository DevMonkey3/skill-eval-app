<?php

namespace App\Models;

use Carbon\CarbonInterface;
use Database\Factories\EmployeeFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $employee_code
 * @property string $name
 * @property string $email
 * @property string $department
 * @property string $position
 * @property CarbonInterface|null $hired_on
 * @property CarbonInterface|null $created_at
 * @property CarbonInterface|null $updated_at
 */
#[Fillable(['employee_code', 'name', 'email', 'department', 'position', 'hired_on'])]
class Employee extends Model
{
    /** @use HasFactory<EmployeeFactory> */
    use HasFactory;

    /** @var list<string> */
    public const DEPARTMENTS = ['開発部', '営業部', '人事部', '総務部', 'マーケティング部'];

    /** @var list<string> */
    public const POSITIONS = ['メンバー', 'リーダー', 'マネージャー', '部長'];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            // JSON にすると "2020-04-01" になり、<input type="date"> にそのまま入る
            'hired_on' => 'date:Y-m-d',
        ];
    }

    /**
     * 氏名・社員番号の部分一致検索 (大文字小文字は区別しない)。
     * 入力の % と _ はワイルドカードではなく文字として扱う。
     *
     * @param  Builder<self>  $query
     */
    #[Scope]
    protected function search(Builder $query, string $term): void
    {
        // エスケープ文字は ! を使う (\ は DB や PDO によって解釈が変わるため)
        $escaped = str_replace(['!', '%', '_'], ['!!', '!%', '!_'], mb_strtolower($term));
        $like = '%'.$escaped.'%';

        // SQLite と PostgreSQL のどちらでも同じ動きになるよう、lower() と ESCAPE を明示する
        $query->where(fn (Builder $query) => $query
            ->whereRaw("lower(name) like ? escape '!'", [$like])
            ->orWhereRaw("lower(employee_code) like ? escape '!'", [$like]));
    }

    /**
     * @return BelongsToMany<Skill, $this>
     */
    public function skills(): BelongsToMany
    {
        return $this->belongsToMany(Skill::class)
            ->withPivot('level')
            ->withTimestamps()
            ->orderBy('name');
    }

    /**
     * @return HasMany<Evaluation, $this>
     */
    public function evaluations(): HasMany
    {
        return $this->hasMany(Evaluation::class)->latest('period');
    }
}
