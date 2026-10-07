<?php

namespace Tests\Unit;

use App\Models\NutritionalStatus;
use App\Support\SbfpForm7Ptr2026;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use Tests\TestCase;

/**
 * Builds the school-level Form 7 PTR from in-memory pupils; nothing here touches the database.
 * Loading the template takes several seconds, so the workbook is built once and shared.
 */
class SbfpForm7Ptr2026Test extends TestCase
{
    private const ES = 'School Lvl ES - SBFP PTR F-A';

    private const JSHS = 'School Lvl J-SHS - SBFP PTR F-A';

    private static ?Spreadsheet $withEndline = null;

    private static function pupil(string $grade, string $sex, string $ns, string $hfa, bool $beneficiary, array $extra = []): NutritionalStatus
    {
        return new NutritionalStatus(array_merge([
            'grade' => $grade, 'sex' => $sex, 'nutritional_status' => $ns, 'height_for_age' => $hfa,
            'isBeneficiary' => $beneficiary, 'weight' => 20.0, 'height' => 120.0,
        ], $extra));
    }

    private static function header(): array
    {
        return ['division' => 'Malaybalay City Division', 'school' => 'Test School', 'school_id' => '123456',
            'head' => 'Head Name', 'focal' => 'Focal Name', 'email' => 'school@example.com'];
    }

    private function baseline(): \Illuminate\Support\Collection
    {
        return collect([
            self::pupil('k', 'm', 'Normal', 'Normal', true, ['ip' => true]),
            self::pupil('k', 'f', 'Severely Wasted', 'Normal', true),
            self::pupil('k', 'm', 'Normal', 'Stunted', true),
            self::pupil('k', 'f', 'Normal', 'Normal', false),
            self::pupil('1', 'm', 'Normal', 'Normal', true),
            self::pupil('1', 'm', 'Normal', 'Normal', true, ['_4ps' => true]),
            self::pupil('1', 'f', 'Wasted', 'Stunted', true),
            self::pupil('1', 'f', 'Normal', 'Normal', true, ['pardo' => true]),
            self::pupil('1', 'm', 'Normal', 'Tall', true, ['ip' => true, '_4ps' => true]),
            self::pupil('5', 'm', 'Wasted', 'Normal', true),
            self::pupil('5', 'm', 'Normal', 'Normal', true),
            self::pupil('5', 'f', 'Normal', 'Stunted', true, ['dewormed' => true]),
            self::pupil('5', 'f', 'Overweight', 'Normal', true, ['_4ps' => true]),
            self::pupil('non_graded', 'f', 'Wasted', 'Normal', true),
            self::pupil('8', 'm', 'Wasted', 'Normal', true),
            self::pupil('8', 'f', 'Normal', 'Normal', true, ['_4ps' => true]),
            self::pupil('4', 'Male', 'Normal', 'Normal', false),
        ]);
    }

    private function withEndline(): Spreadsheet
    {
        if (self::$withEndline === null) {
            $baseline = $this->baseline();
            // by the end of feeding the severely wasted Kinder girl is normal and one Grade 5 boy was not measured
            $endline = $baseline->map(function ($p) {
                $copy = $p->replicate();
                if ($p->grade === 'k' && $p->sex === 'f' && $p->isBeneficiary) {
                    $copy->nutritional_status = 'Normal';
                }

                return $copy;
            });
            $endline->first(fn ($p) => $p->grade === '5' && $p->sex === 'm' && $p->nutritional_status === 'Normal')->weight = null;

            self::$withEndline = (new SbfpForm7Ptr2026)->build($baseline, $endline, self::header());
        }

        return self::$withEndline;
    }

    private function value(Spreadsheet $book, string $sheet, string $cell)
    {
        return $book->getSheetByName($sheet)->getCell($cell)->getCalculatedValue();
    }

    private function assertCells(string $sheet, array $expected, ?Spreadsheet $book = null): void
    {
        $book ??= $this->withEndline();

        foreach ($expected as $cell => $want) {
            $got = $this->value($book, $sheet, $cell);
            $this->assertEqualsWithDelta($want, $got, 1e-9, "{$sheet} {$cell}");
        }
    }

    public function test_the_row_layout_matches_the_template(): void
    {
        $book = $this->withEndline();

        $labels = [
            self::ES => [
                'B19' => 'Kinder', 'B22' => 'Grade 1', 'B34' => 'Grade 5', 'B37' => 'Grade 6', 'B40' => 'SPED', 'B43' => 'Multigrade', 'B46' => 'GRAND TOTAL',
                'B55' => 'Kinder', 'B70' => 'Grade 5', 'B76' => 'SPED', 'B82' => 'GRAND TOTAL',
                'B91' => 'Kinder', 'B106' => 'Grade 5', 'B112' => 'SPED', 'B118' => 'GRAND TOTAL',
                'B128' => 'Kinder', 'B143' => 'Grade 5', 'B149' => 'SPED', 'B155' => 'GRAND TOTAL',
                'T159' => 'M', 'W159' => 'F', 'Z159' => 'Total', 'A221' => 'SBFP Coordinator', 'W221' => 'School Head', 'K222' => 'Date:',
            ],
            self::JSHS => [
                'B19' => 'Grade 7', 'B34' => 'Grade 12', 'B37' => 'SPED', 'B40' => 'Multigrade', 'B43' => 'GRAND TOTAL',
                'B52' => 'Grade 7', 'B67' => 'Grade 12', 'B76' => 'GRAND TOTAL',
                'B85' => 'Grade 7', 'B100' => 'Grade 12', 'B109' => 'GRAND TOTAL',
                'B119' => 'Grade 7', 'B134' => 'Grade 12', 'B143' => 'GRAND TOTAL',
                'T147' => 'M', 'W147' => 'F', 'Z147' => 'Total', 'A210' => 'SBFP Coordinator', 'W210' => 'School Head', 'K211' => 'Date:',
            ],
        ];

        foreach ($labels as $sheet => $cells) {
            foreach ($cells as $cell => $label) {
                $this->assertSame($label, trim((string) $this->value($book, $sheet, $cell)), "{$sheet} {$cell}");
            }
        }
    }

    public function test_header_and_signatures_are_filled(): void
    {
        foreach ([self::ES => ['AD12' => 15, 'sig' => 220, 'date' => 'M222'], self::JSHS => ['AD12' => 2, 'sig' => 209, 'date' => 'M211']] as $sheet => $at) {
            $this->assertSame('Malaybalay City Division', $this->value($this->withEndline(), $sheet, 'K8'));
            $this->assertSame('Test School', $this->value($this->withEndline(), $sheet, 'K9'));
            $this->assertSame('Head Name', $this->value($this->withEndline(), $sheet, 'J11'));
            $this->assertSame('school@example.com', $this->value($this->withEndline(), $sheet, 'AB11'));
            $this->assertCells($sheet, ['F12' => 123456, 'AD12' => $at['AD12']]);
            $this->assertSame('Focal Name', $this->value($this->withEndline(), $sheet, 'A'.$at['sig']));
            $this->assertSame('Head Name', $this->value($this->withEndline(), $sheet, 'W'.$at['sig']));
            $this->assertSame(date('F d, Y'), $this->value($this->withEndline(), $sheet, $at['date']));
        }
    }

    public function test_profile_of_beneficiaries(): void
    {
        $this->assertCells(self::ES, [
            // Kinder: universal, so everyone but the stunted learner is "Universal, SW, W"
            'J19' => 2, 'N19' => 2, 'R19' => 1, 'AP19' => 1, 'AT19' => 0,
            'J20' => 2, 'N20' => 1, 'R20' => 1,
            // Grade 1: the leftover learners are universal; IP, 4Ps and PARDO are counted once each
            'J22' => 3, 'N22' => 3, 'R22' => 1, 'AH22' => 1, 'AT22' => 1, 'AL22' => 0, 'BG22' => 2,
            'J23' => 2, 'N23' => 2, 'R23' => 1, 'AL23' => 1,
            // not a beneficiary: enrolled but not a target; sex written out as "Male" still counts
            'J31' => 1, 'N31' => 0,
            // Grade 5: only severely wasted / wasted count under "Universal, SW, W"
            'J34' => 2, 'N34' => 2, 'R34' => 1,
            'J35' => 2, 'N35' => 2, 'AP35' => 1, 'AH35' => 1, 'BB35' => 1, 'BG35' => 1,
            // Non-graded pupils are reported on the SPED row
            'J41' => 1, 'N41' => 1, 'R41' => 1,
            // GRAND TOTAL rows and the Total row built on them
            'J46' => 8, 'N46' => 7, 'R46' => 3, 'J47' => 7, 'N47' => 6, 'R47' => 3, 'J48' => 15, 'N48' => 13, 'R48' => 6,
            // percentages
            'BE19' => 0, 'BE35' => 1, 'BJ22' => 1,
        ]);

        // Grades 7-12 are on the J-SHS sheet and count severely wasted / wasted under "Universal, SW, W" too
        $this->assertCells(self::JSHS, [
            'J22' => 1, 'N22' => 1, 'R22' => 1, 'J23' => 1, 'N23' => 1, 'R23' => 0, 'AH23' => 1, 'BG23' => 1, 'J43' => 1, 'J44' => 1, 'J45' => 2,
        ]);
    }

    public function test_nutritional_status_before_and_after_feeding(): void
    {
        $this->assertCells(self::ES, [
            // BMI before feeding (baseline), beneficiaries only
            'X55' => 2, 'J56' => 1, 'X58' => 3, 'Q59' => 1, 'X59' => 1, 'X67' => 0, 'Q70' => 1, 'X70' => 1, 'AE71' => 1, 'Q77' => 1,
            'X82' => 6, 'Q82' => 1, 'J83' => 1, 'Q83' => 2, 'X83' => 2, 'O55' => 0, 'O56' => 1,
            // BMI after feeding (endline): the Kinder girl recovered
            'BG55' => 2, 'AS56' => 0, 'BG56' => 1, 'AZ70' => 1, 'BG70' => 1, 'BG57' => 3, 'BL55' => 2 / 3,
            // HFA before and after feeding
            'X91' => 1, 'Q91' => 1, 'X92' => 1, 'X94' => 2, 'AE94' => 1, 'Q95' => 1, 'X106' => 2, 'Q107' => 1, 'X113' => 1,
            'AZ91' => 1, 'AS91' => 1,
        ]);
    }

    public function test_attendance_table_uses_the_beneficiary_counts_like_the_old_form_7(): void
    {
        $expected = [];
        foreach (['J', 'Q', 'X', 'AE', 'AL', 'AS', 'AZ', 'BG', 'BN', 'BU'] as $month) {
            $expected += [$month.'128' => 2, $month.'129' => 1, $month.'131' => 3, $month.'140' => 0, $month.'155' => 7, $month.'156' => 6];
        }

        $this->assertCells(self::ES, $expected);
    }

    public function test_completion_rate(): void
    {
        $this->assertCells(self::ES, [
            'T160' => 7, 'W160' => 6, 'Z160' => 13,
            // the unmeasured Grade 5 boy did not complete
            'T161' => 6, 'W161' => 6, 'Z161' => 12,
            'T162' => 6 / 7, 'W162' => 1, 'Z162' => 12 / 13,
        ]);
        $this->assertCells(self::JSHS, ['T148' => 1, 'W148' => 1, 'Z148' => 2, 'T149' => 1, 'W149' => 1, 'Z149' => 2]);
    }

    public function test_no_formula_shows_an_error(): void
    {
        foreach ([self::ES, self::JSHS] as $title) {
            $sheet = $this->withEndline()->getSheetByName($title);
            $errors = [];

            foreach ($sheet->getCoordinates() as $coordinate) {
                $cell = $sheet->getCell($coordinate);
                if ($cell->isFormula()) {
                    $value = $cell->getCalculatedValue();
                    if (is_string($value) && str_starts_with($value, '#')) {
                        $errors[] = "{$coordinate}={$value}";
                    }
                }
            }

            $this->assertSame([], $errors, $title);
        }
    }

    public function test_after_feeding_is_left_empty_until_there_is_endline_data(): void
    {
        $book = (new SbfpForm7Ptr2026)->build($this->baseline(), collect(), self::header());

        foreach (['AS56', 'BG55', 'BL55', 'AL91', 'T161', 'W161', 'Z161', 'T162'] as $cell) {
            $this->assertNull($book->getSheetByName(self::ES)->getCell($cell)->getValue(), $cell);
        }

        // the before-feeding side and the start-of-feeding count are still there
        $this->assertCells(self::ES, ['J56' => 1, 'X55' => 2, 'T160' => 7, 'W160' => 6], $book);
    }
}
