<?php

namespace App\Models;

use Carbon\CarbonInterface;
use Database\Factories\EmployeeFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
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
