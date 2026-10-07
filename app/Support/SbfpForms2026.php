<?php

namespace App\Support;

use App\Models\NutritionalStatus;
use App\Models\PrimarySecondaryBeneficiaries;
use App\Models\SchoolProfile;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * Fills the 2026 "SBFP Forms 1-6 (disaggregated by sex)" workbook.
 * Forms 1A, 1B and 2 (elementary and JHS/SHS) are generated from the beneficiary list;
 * Forms 3-6 are left as the blank template.
 */
class SbfpForms2026
{
    public const TEMPLATE = 'exel/SBFP_Forms_2026.xlsx';

    private const LIST_FIRST_ROW = 14;

    private const LIST_TEMPLATE_ROWS = 20;

    public function __construct(private $user)
    {
    }

    public function build(): Spreadsheet
    {
        $spreadsheet = IOFactory::load(public_path(self::TEMPLATE));

        $school = $this->user->school;
        $profile = SchoolProfile::where('school_id', $this->user->school_id)->first();
        $header = [
            'division' => 'Malaybalay City Division',
            'municipality' => 'Malaybalay City, Bukidnon',
            'school' => trim(($school?->school_name ?? '').($school?->district ? ' / '.$school->district : '')),
            'school_id' => $school?->school_id ?? '',
            'principal' => $profile?->school_head_name ?? '',
            'focal' => $profile?->school_focal_name ?? '',
        ];

        $beneficiaries = NutritionalStatus::where('isBeneficiary', true)
            ->orderBy('grade')
            ->orderBy('last_name')
            ->get();
        $primary = PrimarySecondaryBeneficiaries::where('name', 'Primary')->first();

        $this->fillMasterList($spreadsheet->getSheetByName('SBFP-FORM 1A'), $beneficiaries, $header, $primary, true);
        $this->fillMasterList($spreadsheet->getSheetByName('SBFP-FORM 1B'), $beneficiaries, $header, $primary, false);

        $this->fillForm2($spreadsheet->getSheetByName('SBFP-FORM 2-ES'), $beneficiaries, $primary, $header,
            ['k', '1', '2', '3', '4', '5', '6'], 16, 37, 42);
        $this->fillForm2($spreadsheet->getSheetByName('SBFP-FORM 2-J-SHS'), $beneficiaries, $primary, $header,
            ['7', '8', '9', '10', '11', '12'], 16, 34, 39);

        $spreadsheet->setActiveSheetIndex(0);

        return $spreadsheet;
    }

    private function fillMasterList(Worksheet $sheet, $records, array $header, ?PrimarySecondaryBeneficiaries $primary, bool $isForm1A): void
    {
        $sheet->setCellValue('B7', 'Division: '.$header['division']);
        $sheet->setCellValue('H7', 'Name of Principal : '.$header['principal']);
        $sheet->setCellValue('B8', 'City/ Municipality/Barangay : '.$header['municipality']);
        $sheet->setCellValue('H8', 'Name of Feeding Focal Person : '.$header['focal']);
        $sheet->setCellValue('B9', 'Name of School / School District : '.$header['school']);
        $sheet->setCellValue('B10', 'School  ID Number: '.$header['school_id']);

        // Make room for more pupils than the template's blank rows. Inserting before the last
        // data row keeps ranges such as AVERAGE(P14:P33) covering the new rows.
        $extra = $records->count() - self::LIST_TEMPLATE_ROWS;
        if ($extra > 0) {
            $lastRow = self::LIST_FIRST_ROW + self::LIST_TEMPLATE_ROWS - 1;
            $lastColumn = $sheet->getHighestDataColumn(self::LIST_FIRST_ROW);
            $columns = range('A', $lastColumn);
            $sheet->insertNewRowBefore($lastRow, $extra);
            $height = $sheet->getRowDimension(self::LIST_FIRST_ROW)->getRowHeight();
            for ($i = 0; $i < $extra; $i++) {
                $row = $lastRow + $i;
                foreach ($columns as $col) {
                    $sheet->getCell($col.$row)->setXfIndex($sheet->getCell($col.self::LIST_FIRST_ROW)->getXfIndex());
                }
                $sheet->getRowDimension($row)->setRowHeight($height);
            }
        }

        foreach ($records as $i => $rec) {
            $r = self::LIST_FIRST_ROW + $i;

            $sheet->setCellValue("B{$r}", $i + 1);
            $sheet->setCellValue("C{$r}", $rec->full_name ?? '');
            $sheet->setCellValue("D{$r}", strtoupper((string) $rec->sex));
            $sheet->setCellValue("E{$r}", $this->gradeLabel($rec->grade).($rec->section ? ' - '.$rec->section : ''));
            $sheet->setCellValue("F{$r}", $rec->birthday?->format('m/d/Y') ?? '');
            $sheet->setCellValue("G{$r}", $rec->date_of_weighing?->format('m/d/Y') ?? '');
            $sheet->setCellValue("H{$r}", $rec->age_years !== null ? $rec->age_years.' / '.($rec->age_months ?? 0) : '');
            $sheet->setCellValue("I{$r}", $rec->weight ?? '');
            $sheet->setCellValue("J{$r}", $rec->height ?? '');
            $sheet->setCellValue("K{$r}", $rec->bmi ?? '');
            $sheet->setCellValue("L{$r}", $rec->nutritional_status ?? '');
            $sheet->setCellValue("M{$r}", $rec->height_for_age ?? '');

            if ($isForm1A) {
                $sheet->setCellValue("N{$r}", $this->isPrimary($rec, $primary) ? 'Primary' : 'Secondary');
                // O (pregnant) and P (with 0-1 year-old child) are not recorded in the system; left blank.
                $sheet->setCellValue("Q{$r}", $rec->dewormed ? 'Yes' : 'No');
                $sheet->setCellValue("R{$r}", $rec->parent_consent_milk ? 'Yes' : 'No');
                $sheet->setCellValue("S{$r}", $rec->_4ps ? 'Yes' : 'No');
                $sheet->setCellValue("T{$r}", $rec->sbfp_previous_beneficiary ? 'Yes' : 'No');
            }
        }
    }

    private function fillForm2(Worksheet $sheet, $records, ?PrimarySecondaryBeneficiaries $primary, array $header, array $grades, int $firstRow, int $grandRow, int $signatureRow): void
    {
        $sheet->setCellValue('A6', 'Schools Division Office:  '.$header['division']);
        $sheet->setCellValue('A7', 'City/ Municipality/Barangay : '.$header['municipality']);
        $sheet->setCellValue('A8', 'Name of School / School District :  '.$header['school']);
        $sheet->setCellValue('A9', 'School  ID Number:  '.$header['school_id']);

        $sheet->setCellValue('A'.$signatureRow, $header['focal']);
        $sheet->setCellValue('M'.$signatureRow, $header['principal']);

        // C..F weight status, H..K height status, N PARDO, O stunted, P indigent (4Ps), Q IP
        // (secondary targets only), R dewormed, S 4Ps (all), T previous beneficiaries
        $columns = [
            'C' => fn ($p) => $this->in($p->nutritional_status, ['severely wasted']),
            'D' => fn ($p) => $this->in($p->nutritional_status, ['wasted']),
            'E' => fn ($p) => $this->in($p->nutritional_status, ['normal']),
            'F' => fn ($p) => $this->in($p->nutritional_status, ['overweight', 'obese']),
            'H' => fn ($p) => $this->in($p->height_for_age, ['severely stunted']),
            'I' => fn ($p) => $this->in($p->height_for_age, ['stunted']),
            'J' => fn ($p) => $this->in($p->height_for_age, ['normal']),
            'K' => fn ($p) => $this->in($p->height_for_age, ['tall']),
            'N' => fn ($p) => $p->pardo && ! $this->isPrimary($p, $primary),
            'O' => fn ($p) => $this->in($p->height_for_age, ['stunted', 'severely stunted']) && ! $this->isPrimary($p, $primary),
            'P' => fn ($p) => $p->_4ps && ! $this->isPrimary($p, $primary),
            'Q' => fn ($p) => $p->ip && ! $this->isPrimary($p, $primary),
            'R' => fn ($p) => (bool) $p->dewormed,
            'S' => fn ($p) => (bool) $p->_4ps,
            'T' => fn ($p) => (bool) $p->sbfp_previous_beneficiary,
        ];

        $sexTotals = [
            'm' => array_fill_keys(array_keys($columns), 0),
            'f' => array_fill_keys(array_keys($columns), 0),
        ];

        foreach ($grades as $g => $grade) {
            // each grade takes three rows: male, female, total (formulas already in the template)
            $maleRow = $firstRow + ($g * 3);

            foreach (['m' => $maleRow, 'f' => $maleRow + 1] as $sex => $row) {
                $group = $records->filter(fn ($p) => (string) $p->grade === $grade && $this->sexKey($p->sex) === $sex);

                foreach ($columns as $col => $test) {
                    $count = $group->filter($test)->count();
                    $sheet->setCellValue($col.$row, $count);
                    $sexTotals[$sex][$col] += $count;
                }

                $sheet->setCellValue("G{$row}", "=SUM(C{$row}:F{$row})");
                $sheet->setCellValue("L{$row}", "=SUM(H{$row}:K{$row})");
            }
        }

        // Grand-total M/F rows (the template's grand total row sums these for the height columns)
        foreach (['m' => $grandRow, 'f' => $grandRow + 1] as $sex => $row) {
            foreach ($sexTotals[$sex] as $col => $count) {
                $sheet->setCellValue($col.$row, $count);
            }
            $sheet->setCellValue("G{$row}", "=SUM(C{$row}:F{$row})");
            $sheet->setCellValue("L{$row}", "=SUM(H{$row}:K{$row})");
        }
    }

    private function in(?string $value, array $options): bool
    {
        return in_array(strtolower(trim((string) $value)), $options, true);
    }

    private function sexKey(?string $sex): ?string
    {
        return match (strtolower(trim((string) $sex))) {
            'm', 'male' => 'm',
            'f', 'female' => 'f',
            default => null,
        };
    }

    private function gradeLabel(?string $grade): string
    {
        return match (strtolower((string) $grade)) {
            'k', 'kinder' => 'Kinder',
            'non_graded', 'non-graded' => 'Non-graded',
            '' => '',
            default => 'Grade '.$grade,
        };
    }

    /** A pupil is primary when any condition enabled in the Primary beneficiary settings matches. */
    private function isPrimary(NutritionalStatus $p, ?PrimarySecondaryBeneficiaries $c): bool
    {
        if (! $c) {
            return false;
        }

        $grade = strtolower((string) $p->grade);
        $ns = strtolower((string) $p->nutritional_status);
        $hfa = strtolower((string) $p->height_for_age);

        return ($c->all_kinder && $grade === 'k')
            || ($c->all_grade_1 && $grade === '1')
            || ($c->all_grade_2 && $grade === '2')
            || ($c->all_grade_3 && $grade === '3')
            || ($c->severely_wasted && $ns === 'severely wasted')
            || ($c->wasted && $ns === 'wasted')
            || ($c->normal_weight && $ns === 'normal')
            || ($c->overweight_obese && in_array($ns, ['overweight', 'obese'], true))
            || ($c->severely_stunted && $hfa === 'severely stunted')
            || ($c->stunted && $hfa === 'stunted')
            || ($c->normal_height && $hfa === 'normal')
            || ($c->tall && $hfa === 'tall')
            || ($c->_4ps && $p->_4ps)
            || ($c->ip && $p->ip)
            || ($c->pardo && $p->pardo);
    }
}
