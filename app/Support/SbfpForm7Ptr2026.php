<?php

namespace App\Support;

use App\Models\NutritionalStatus;
use App\Models\SchoolProfile;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * Fills the school-level sheets of the 2026 SBFP Program Terminal Report (Form 7 / PTR Form A),
 * "disaggregated by sex": the elementary sheet and the J-SHS sheet.
 *
 * "Before feeding" figures come from the baseline data and "after feeding" figures from the
 * endline data. The beneficiary list is the same in every state.
 */
class SbfpForm7Ptr2026
{
    public const TEMPLATE = 'exel/SBFP_Form7_PTR_2026.xlsx';

    private const SHEET_ES = 'School Lvl ES - SBFP PTR F-A';

    private const SHEET_JSHS = 'School Lvl J-SHS - SBFP PTR F-A';

    /**
     * Row layout of each sheet. Every grade block is three rows (M, F, Total); a section starts at
     * its first block and ends with a GRAND TOTAL block after the last grade block.
     */
    private const LAYOUT = [
        self::SHEET_ES => [
            'blocks' => ['k', '1', '2', '3', '4', '5', '6', 'sped', 'multigrade'],
            'sections' => ['profile' => 19, 'bmi' => 55, 'hfa' => 91, 'attendance' => 128],
            'completion' => 160,
            'signature' => 220,
            'date' => 'M222',
        ],
        self::SHEET_JSHS => [
            'blocks' => ['7', '8', '9', '10', '11', '12', 'sped', 'multigrade'],
            'sections' => ['profile' => 19, 'bmi' => 52, 'hfa' => 85, 'attendance' => 119],
            'completion' => 148,
            'signature' => 209,
            'date' => 'M211',
        ],
    ];

    /** Section 1 value columns (each is a merged group of cells; the top-left cell takes the value). */
    private const COL_ENROLLMENT = 'J';

    private const COL_TARGET = 'N';

    private const COL_UNIVERSAL_SW_W = 'R';

    private const COL_INDIGENT = 'AH';

    private const COL_PARDO = 'AL';

    private const COL_STUNTED = 'AP';

    private const COL_IP = 'AT';

    private const COL_DEWORMED = 'BB';

    private const COL_4PS = 'BG';

    /**
     * Section 1 columns the system has data for (used for the GRAND TOTAL rows). Adolescent pregnant,
     * adolescent mother, undernourished high school, last mile school and repeater are not recorded.
     */
    private const PROFILE_COLUMNS = ['J', 'N', 'R', 'AH', 'AL', 'AP', 'AT', 'BB', 'BG'];

    /** Section 1 "No." column => "%" column. */
    private const PROFILE_PERCENT = ['BB' => 'BE', 'BG' => 'BJ'];

    /** Section 1 "%" column of the Repeater column, which is left blank. */
    private const REPEATER_PERCENT = 'BO';

    /** BMI before / after feeding: nutritional status => value column. */
    private const BMI_BEFORE = ['severely wasted' => 'J', 'wasted' => 'Q', 'normal' => 'X', 'overweight' => 'AE', 'obese' => 'AL'];

    private const BMI_AFTER = ['severely wasted' => 'AS', 'wasted' => 'AZ', 'normal' => 'BG', 'overweight' => 'BN', 'obese' => 'BU'];

    /** HFA before / after feeding: height-for-age => value column. */
    private const HFA_BEFORE = ['severely stunted' => 'J', 'stunted' => 'Q', 'normal' => 'X', 'tall' => 'AE'];

    private const HFA_AFTER = ['severely stunted' => 'AL', 'stunted' => 'AS', 'normal' => 'AZ', 'tall' => 'BG'];

    /** The ten month columns (0-9) of the attendance table. */
    private const ATTENDANCE_COLUMNS = ['J', 'Q', 'X', 'AE', 'AL', 'AS', 'AZ', 'BG', 'BN', 'BU'];

    /** Value column => "%" column, for the BMI / HFA / attendance tables (a "%" column is 5 + 2 columns after its value). */
    private const PERCENT_OF = [
        'J' => 'O', 'Q' => 'V', 'X' => 'AC', 'AE' => 'AJ', 'AL' => 'AQ',
        'AS' => 'AX', 'AZ' => 'BE', 'BG' => 'BL', 'BN' => 'BS', 'BU' => 'BZ',
    ];

    public static function forUser($user): Spreadsheet
    {
        $school = $user->school;
        $profile = SchoolProfile::where('school_id', $user->school_id)->first();

        $header = [
            'division' => 'Malaybalay City Division',
            'school' => $school?->school_name ?? '',
            'school_id' => $school?->school_id ?? '',
            'head' => $profile?->school_head_name ?? '',
            'focal' => $profile?->school_focal_name ?? '',
            'email' => $profile?->school_email ?? '',
        ];

        // Soft-deleted pupils are excluded; only the baseline/midline/endline filter is lifted.
        $state = fn (string $name) => NutritionalStatus::withoutGlobalScope('survey_state')
            ->where('survey_state', $name)
            ->get();

        return (new self)->build($state('baseline'), $state('endline'), $header);
    }

    public function build(Collection $baseline, Collection $endline, array $header): Spreadsheet
    {
        $reader = IOFactory::createReader('Xlsx');
        $reader->setLoadSheetsOnly([self::SHEET_ES, self::SHEET_JSHS]);
        $spreadsheet = $reader->load(public_path(self::TEMPLATE));

        foreach (self::LAYOUT as $title => $layout) {
            $this->fillSheet($spreadsheet->getSheetByName($title), $layout, $title === self::SHEET_ES, $baseline, $endline, $header);
        }

        $spreadsheet->setActiveSheetIndex(0);

        return $spreadsheet;
    }

    private function fillSheet(Worksheet $sheet, array $layout, bool $elementary, Collection $baseline, Collection $endline, array $header): void
    {
        $blocks = $layout['blocks'];
        $before = $this->groupByBlock($baseline, $blocks, $elementary);
        $after = $this->groupByBlock($endline, $blocks, $elementary);
        $hasEndline = $endline->isNotEmpty();

        // ── Header ───────────────────────────────────────────────────────────
        $sheet->setCellValue('K8', $header['division']);
        $sheet->setCellValue('K9', $header['school']);
        $sheet->setCellValue('J11', $header['head']);
        $sheet->setCellValue('AB11', $header['email']);
        $sheet->setCellValue('F12', $header['school_id']);
        // The template leaves this box unmerged, and a number in a single narrow column shows as ####.
        $sheet->mergeCells('AD12:AQ12');
        $sheet->setCellValue('AD12', $this->countAll($before));
        $sheet->getStyle('AD12:AQ12')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

        // ── 1. Profile of beneficiaries ─────────────────────────────────────
        $first = $layout['sections']['profile'];
        foreach ($blocks as $i => $block) {
            $rows = ['m' => $first + 3 * $i, 'f' => $first + 3 * $i + 1];

            foreach ($rows as $sex => $row) {
                $all = $before[$block][$sex];
                $beneficiaries = $all->filter(fn ($p) => $p->isBeneficiary);
                $categories = $this->categories($beneficiaries, $block);

                $sheet->setCellValue(self::COL_ENROLLMENT.$row, $all->count());
                $sheet->setCellValue(self::COL_TARGET.$row, $beneficiaries->count());
                $sheet->setCellValue(self::COL_UNIVERSAL_SW_W.$row, $categories['universal']);
                $sheet->setCellValue(self::COL_INDIGENT.$row, $categories['indigent']);
                $sheet->setCellValue(self::COL_PARDO.$row, $categories['pardo']);
                $sheet->setCellValue(self::COL_STUNTED.$row, $categories['stunted']);
                $sheet->setCellValue(self::COL_IP.$row, $categories['ip']);
                $sheet->setCellValue(self::COL_DEWORMED.$row, $beneficiaries->filter(fn ($p) => $p->dewormed)->count());
                $sheet->setCellValue(self::COL_4PS.$row, $beneficiaries->filter(fn ($p) => $p->_4ps)->count());
            }
        }
        $this->fillGrandTotals($sheet, $first, count($blocks), self::PROFILE_COLUMNS);
        $this->fillPercents($sheet, $first, count($blocks), self::PROFILE_PERCENT, '0.00%');
        $this->clearColumns($sheet, $first, count($blocks), [self::REPEATER_PERCENT]);

        // ── 2. BMI before / after feeding, 3. HFA before / after feeding ───
        foreach ([
            'bmi' => [self::BMI_BEFORE, self::BMI_AFTER, fn ($p) => $this->key($p->nutritional_status)],
            'hfa' => [self::HFA_BEFORE, self::HFA_AFTER, fn ($p) => $this->key($p->height_for_age)],
        ] as $section => [$beforeColumns, $afterColumns, $category]) {
            $first = $layout['sections'][$section];

            foreach ($blocks as $i => $block) {
                foreach (['m' => $first + 3 * $i, 'f' => $first + 3 * $i + 1] as $sex => $row) {
                    $this->writeCounts($sheet, $row, $beforeColumns, $before[$block][$sex], $category);
                    if ($hasEndline) {
                        $this->writeCounts($sheet, $row, $afterColumns, $after[$block][$sex], $category);
                    }
                }
            }

            $columns = array_merge(array_values($beforeColumns), $hasEndline ? array_values($afterColumns) : []);
            $this->fillGrandTotals($sheet, $first, count($blocks), $columns);
            $this->fillPercents($sheet, $first, count($blocks), $this->percentColumns($columns));

            if (! $hasEndline) {
                // nothing recorded after feeding yet: leave that part of the table empty
                $afterColumnList = array_values($afterColumns);
                $this->clearColumns($sheet, $first, count($blocks), array_merge($afterColumnList, array_values($this->percentColumns($afterColumnList))));
            }
        }

        // ── 4. Percentage attendance ────────────────────────────────────────
        // Attendance is not recorded in the system: every month shows the beneficiaries of the grade and sex.
        $first = $layout['sections']['attendance'];
        foreach ($blocks as $i => $block) {
            foreach (['m' => $first + 3 * $i, 'f' => $first + 3 * $i + 1] as $sex => $row) {
                $count = $before[$block][$sex]->filter(fn ($p) => $p->isBeneficiary)->count();
                foreach (self::ATTENDANCE_COLUMNS as $column) {
                    $sheet->setCellValue($column.$row, $count);
                }
            }
        }
        $this->fillGrandTotals($sheet, $first, count($blocks), self::ATTENDANCE_COLUMNS);
        $this->fillPercents($sheet, $first, count($blocks), $this->percentColumns(self::ATTENDANCE_COLUMNS));

        // ── 5. Completion rate ──────────────────────────────────────────────
        $row = $layout['completion'];
        $started = ['m' => 0, 'f' => 0];
        $completed = ['m' => 0, 'f' => 0];
        foreach ($blocks as $block) {
            foreach (['m', 'f'] as $sex) {
                $started[$sex] += $before[$block][$sex]->filter(fn ($p) => $p->isBeneficiary)->count();
                $completed[$sex] += $after[$block][$sex]
                    ->filter(fn ($p) => $p->isBeneficiary && $p->weight !== null && $p->height !== null)
                    ->count();
            }
        }
        foreach (['T' => 'm', 'W' => 'f'] as $column => $sex) {
            $sheet->setCellValue($column.$row, $started[$sex]);
            if ($hasEndline) {
                $sheet->setCellValue($column.($row + 1), $completed[$sex]);
            }
        }
        $sheet->setCellValue('Z'.$row, "=SUM(T{$row},W{$row})");
        if ($hasEndline) {
            $sheet->setCellValue('Z'.($row + 1), '=SUM(T'.($row + 1).',W'.($row + 1).')');
            foreach (['T', 'W', 'Z'] as $column) {
                $sheet->setCellValue($column.($row + 2), "=IFERROR({$column}".($row + 1)."/{$column}{$row},0)");
                $sheet->getStyle($column.($row + 2))->getNumberFormat()->setFormatCode('0.00%');
            }
        }

        // ── Signatures ─────────────────────────────────────────────────────
        $name = $layout['signature'];
        foreach (['A'.$name.':V'.$name => $header['focal'], 'W'.$name.':AQ'.$name => $header['head']] as $range => $person) {
            $sheet->mergeCells($range);
            $cell = strtok($range, ':');
            $sheet->setCellValue($cell, $person);
            $sheet->getStyle($range)->getFont()->setBold(true);
            $sheet->getStyle($range)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        }
        $sheet->setCellValue($layout['date'], Carbon::today()->format('F d, Y'));
    }

    /**
     * Pupils per block and sex, e.g. $groups['k']['m'] => Collection.
     * Non-graded pupils are reported on the SPED row of the elementary sheet.
     */
    private function groupByBlock(Collection $pupils, array $blocks, bool $elementary): array
    {
        $groups = [];
        foreach ($blocks as $block) {
            $groups[$block] = ['m' => collect(), 'f' => collect()];
        }

        foreach ($pupils as $pupil) {
            $grade = $this->gradeKey($pupil->grade);
            $block = ($grade === 'non_graded' && $elementary) ? 'sped' : $grade;
            $sex = $this->sexKey($pupil->sex);

            if ($block !== null && $sex !== null && isset($groups[$block])) {
                $groups[$block][$sex]->push($pupil);
            }
        }

        return $groups;
    }

    private function countAll(array $groups): int
    {
        return collect($groups)->sum(fn ($sexes) => $sexes['m']->count() + $sexes['f']->count());
    }

    /**
     * The "Actual" breakdown of a group of beneficiaries. Each learner is counted once:
     * Severely wasted / wasted, then stunted, then IP, then 4Ps (indigent), then PARDO.
     * Kinder and Grade 1 are fed universally, so their remaining learners count under "Universal, SW, W".
     */
    private function categories(Collection $beneficiaries, string $block): array
    {
        $counts = ['universal' => 0, 'stunted' => 0, 'ip' => 0, 'indigent' => 0, 'pardo' => 0];

        foreach ($beneficiaries as $p) {
            $ns = $this->key($p->nutritional_status);
            $hfa = $this->key($p->height_for_age);

            $wasted = in_array($ns, ['severely wasted', 'wasted'], true);
            $wellNourished = in_array($ns, ['normal', 'overweight', 'obese'], true);
            $normalHeight = in_array($hfa, ['normal', 'tall'], true);

            $stunted = $wellNourished && in_array($hfa, ['severely stunted', 'stunted'], true);
            $ip = $wellNourished && $normalHeight && $p->ip;
            $indigent = $wellNourished && $normalHeight && $p->_4ps && ! $p->ip;
            $pardo = $wellNourished && $normalHeight && $p->pardo && ! $p->_4ps && ! $p->ip;

            $counts['stunted'] += (int) $stunted;
            $counts['ip'] += (int) ($ip && $block !== 'k');
            $counts['indigent'] += (int) ($indigent && $block !== 'k');
            $counts['pardo'] += (int) ($pardo && $block !== 'k');

            $counts['universal'] += (int) match ($block) {
                'k' => $wasted || ($wellNourished && $normalHeight),
                '1' => ! ($stunted || $ip || $indigent || $pardo),
                default => $wasted,
            };
        }

        return $counts;
    }

    private function writeCounts(Worksheet $sheet, int $row, array $columns, Collection $group, callable $category): void
    {
        // nutritional status tables cover the beneficiaries only
        $counts = $group->filter(fn ($p) => $p->isBeneficiary)->countBy($category);

        foreach ($columns as $key => $column) {
            $sheet->setCellValue($column.$row, (int) ($counts[$key] ?? 0));
        }
    }

    /** M and F GRAND TOTAL rows: the sum of the same column over every grade block. */
    private function fillGrandTotals(Worksheet $sheet, int $first, int $blockCount, array $columns): void
    {
        foreach (['m' => 0, 'f' => 1] as $offset) {
            $grandRow = $first + 3 * $blockCount + $offset;

            foreach ($columns as $column) {
                $cells = [];
                for ($i = 0; $i < $blockCount; $i++) {
                    $cells[] = $column.($first + 3 * $i + $offset);
                }
                $sheet->setCellValue($column.$grandRow, '=SUM('.implode(',', $cells).')');
            }
        }
    }

    /**
     * The template's "%" cells divide by the Total row and show #DIV/0! while it is empty.
     * Rewrite them (including the GRAND TOTAL M/F rows) so an empty total reads 0%.
     */
    private function fillPercents(Worksheet $sheet, int $first, int $blockCount, array $pairs, ?string $format = null): void
    {
        for ($i = 0; $i <= $blockCount; $i++) {
            $male = $first + 3 * $i;
            $total = $male + 2;

            foreach ($pairs as $value => $percent) {
                foreach ([$male, $male + 1] as $row) {
                    $sheet->setCellValue($percent.$row, "=IFERROR({$value}{$row}/{$value}{$total},0)");
                    if ($format) {
                        $sheet->getStyle($percent.$row)->getNumberFormat()->setFormatCode($format);
                    }
                }
            }
        }
    }

    /** Empties the given columns on the M, F and Total rows of every block and the GRAND TOTAL block. */
    private function clearColumns(Worksheet $sheet, int $first, int $blockCount, array $columns): void
    {
        for ($i = 0; $i <= $blockCount; $i++) {
            foreach ($columns as $column) {
                foreach ([0, 1, 2] as $offset) {
                    $sheet->setCellValue($column.($first + 3 * $i + $offset), null);
                }
            }
        }
    }

    private function percentColumns(array $valueColumns): array
    {
        return array_intersect_key(self::PERCENT_OF, array_flip($valueColumns));
    }

    private function key(?string $value): string
    {
        return strtolower(trim((string) $value));
    }

    private function sexKey(?string $sex): ?string
    {
        return match ($this->key($sex)) {
            'm', 'male' => 'm',
            'f', 'female' => 'f',
            default => null,
        };
    }

    private function gradeKey(?string $grade): ?string
    {
        $grade = $this->key($grade);

        return match (true) {
            $grade === '' => null,
            in_array($grade, ['k', 'kinder', 'kindergarten'], true) => 'k',
            in_array($grade, ['non_graded', 'non-graded', 'non graded'], true) => 'non_graded',
            (bool) preg_match('/\d+/', $grade, $m) => (string) (int) $m[0],
            default => null,
        };
    }
}
