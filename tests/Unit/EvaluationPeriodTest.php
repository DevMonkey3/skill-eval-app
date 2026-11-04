<?php

namespace Tests\Unit;

use App\Models\Evaluation;
use Carbon\CarbonImmutable;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class EvaluationPeriodTest extends TestCase
{
    /**
     * @return array<string, array{string, string}>
     */
    public static function dates(): array
    {
        return [
            '4月1日は上期の始まり' => ['2026-04-01', '2026-H1'],
            '9月30日は上期の終わり' => ['2026-09-30', '2026-H1'],
            '10月1日は下期の始まり' => ['2026-10-01', '2026-H2'],
            '12月31日は下期' => ['2026-12-31', '2026-H2'],
            '1月1日は前年度の下期' => ['2027-01-01', '2026-H2'],
            '3月31日は前年度の下期の終わり' => ['2027-03-31', '2026-H2'],
        ];
    }

    #[DataProvider('dates')]
    public function test_period_follows_the_japanese_fiscal_year(string $date, string $expected)
    {
        $this->assertSame($expected, Evaluation::periodFor(CarbonImmutable::parse($date)));
    }
}
