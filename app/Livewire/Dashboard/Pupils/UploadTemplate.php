<?php

namespace App\Livewire\Dashboard\Pupils;

use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * Uploads the simplified class-list template (e.g. "Grade 5 (Sampaguita).xlsx").
 *
 * Layout: two header rows, data from row 3. Columns:
 * A #, B Last, C First, D Middle, E Suffix, F Grade, G/H/I Birthday (Month/Day/Year),
 * J Sex, K Height, L Weight, M 4PS, N IP, O PARDO, P Dewormed,
 * Q Parent consent (milk), R SBFP previous beneficiary.
 */
class UploadTemplate extends UploadSF1
{
    protected const FIRST_DATA_ROW = 3;

    public function importPreview()
    {
        $this->validate();

        try {
            $spreadsheet = IOFactory::load($this->excel->getRealPath());
        } catch (\Exception $e) {
            $this->addError('excel', 'Unable to read spreadsheet file.');

            return;
        }

        $this->rows = $this->readRows($spreadsheet);
        $this->preview = true;
    }

    protected function readRows(Spreadsheet $spreadsheet): array
    {
        $sheet = $spreadsheet->getSheet(0);

        // Fallbacks for grade/section come from the sheet name, e.g. "Grade 5 (Sampaguita)"
        $sheetGrade = null;
        $sheetSection = '';
        if (preg_match('/^(.*?)\s*\((.*)\)\s*$/', trim($sheet->getTitle()), $m)) {
            $sheetGrade = $this->processGrade($m[1]);
            $sheetSection = trim($m[2]);
        } elseif (preg_match('/\d+/', $sheet->getTitle())) {
            $sheetGrade = $this->processGrade($sheet->getTitle());
        }

        $rows = [];
        $highestRow = $sheet->getHighestDataRow();

        for ($row = self::FIRST_DATA_ROW; $row <= $highestRow; $row++) {
            $last = trim((string) $sheet->getCell('B'.$row)->getValue());
            $first = trim((string) $sheet->getCell('C'.$row)->getValue());

            if ($last === '' && $first === '') {
                continue;
            }

            $rows[] = $this->parseTemplateRow($sheet, $row, $sheetGrade, $sheetSection);
        }

        return $rows;
    }

    protected function parseTemplateRow(Worksheet $sheet, int $row, ?string $sheetGrade, string $sheetSection): array
    {
        $value = fn (string $col) => $sheet->getCell($col.$row)->getCalculatedValue();

        $last = trim((string) $value('B'));
        $first = trim((string) $value('C'));
        $middle = trim((string) $value('D'));
        $suffix = trim((string) $value('E'));

        $gradeCell = trim((string) $value('F'));
        $grade = $gradeCell !== '' ? $this->processGrade($gradeCell) : ($sheetGrade ?? 'non-graded');

        $birthdate = $this->parseTemplateBirthdate($value('G'), $value('H'), $value('I'));
        $ageData = $this->calculateAge($birthdate);

        $fullname = implode(', ', array_filter([$last, trim($first.' '.$suffix), $middle], fn ($p) => $p !== ''));

        return [
            'lrn' => '',
            'fullname' => $fullname,
            'last_name' => $last,
            'first_name' => $first,
            'middle_name' => $middle,
            'sex' => $this->normalizeSex($value('J')),
            'birthdate' => $birthdate,
            'age_years' => $ageData['years'],
            'age_months' => $ageData['months'],
            'ip' => $this->parseBoolean($value('N')),
            'grade' => $grade,
            'section' => $sheetSection,
            'row' => $row,
            'weight' => $this->parseMeasurement($value('L')),
            'height' => $this->parseMeasurement($value('K')),
            'dewormed' => $this->parseBoolean($value('P')),
            '_4ps' => $this->parseBoolean($value('M')),
            'pardo' => $this->parseBoolean($value('O')),
            'parent_consent_milk' => $this->parseBoolean($value('Q')),
            'sbfp_previous_beneficiary' => $this->parseBoolean($value('R')),
        ];
    }

    protected function parseTemplateBirthdate($month, $day, $year): ?string
    {
        $month = trim((string) $month);
        $day = trim((string) $day);
        $year = trim((string) $year);

        if ($month === '' || $day === '' || $year === '') {
            return null;
        }

        try {
            if (! is_numeric($month)) {
                $month = Carbon::parse('1 '.$month.' 2000')->month;
            }

            if (! checkdate((int) $month, (int) $day, (int) $year)) {
                return null;
            }

            return sprintf('%04d-%02d-%02d', (int) $year, (int) $month, (int) $day);
        } catch (\Exception $e) {
            return null;
        }
    }

    public function render()
    {
        return view('livewire.dashboard.pupils.upload-s-f1', [
            'title' => 'Template upload',
            'help' => 'Upload the class-list template (one Excel sheet per class, e.g. "Grade 5 (Sampaguita)"). Columns: name, grade, birthday (month/day/year), sex, height, weight and the Y/N flags. Grade and section fall back to the sheet name. Review the preview table before saving.',
        ]);
    }
}
