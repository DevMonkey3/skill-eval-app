<?php

namespace App\Models;

use Carbon\CarbonInterface;
use Database\Factories\EvaluationFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $employee_id
 * @property int $evaluator_id
 * @property string $period
 * @property int $score
 * @property string|null $comment
 * @property CarbonInterface|null $created_at
 * @property CarbonInterface|null $updated_at
 *
 * evaluator_id はあえて Fillable に入れない。評価者は常にログイン中のユーザーから決める。
 */
#[Fillable(['employee_id', 'period', 'score', 'comment'])]
class Evaluation extends Model
{
    /** @use HasFactory<EvaluationFactory> */
    use HasFactory;

    public const PERIOD_PATTERN = '/^\d{4}-H[12]$/';

    /** @var array<int, string> */
    public const SCORE_LABELS = [1 => '要改善', 2 => 'やや不足', 3 => '標準', 4 => '優秀', 5 => '卓越'];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'employee_id' => 'integer',
            'evaluator_id' => 'integer',
            'score' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<Employee, $this>
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function evaluator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'evaluator_id');
    }

    /**
     * 日付から評価期間を返す。日本の会計年度に合わせ、4〜9月が上期(H1)、10〜3月が下期(H2)。
     */
    public static function periodFor(CarbonInterface $date): string
    {
        if ($date->month >= 4 && $date->month <= 9) {
            return $date->year.'-H1';
        }

        $year = $date->month >= 10 ? $date->year : $date->year - 1;

        return $year.'-H2';
    }
}
