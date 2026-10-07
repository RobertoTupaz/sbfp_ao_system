<?php

namespace App\Livewire\Dashboard\Pupils;

use App\Models\NutritionalStatus;
use App\Support\NutritionalCalculator;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class UploadSF1 extends Component
{
    use WithFileUploads;

    public $excel;

    public $rows = [];

    public $preview = false;

    public $changeAllGrade = '';

    public $changeAllSection = '';

    protected $rules = [
        'excel' => 'required|file|mimes:xlsx,xls,csv',
    ];

    public function updatedExcel()
    {
        $this->preview = false;
        $this->rows = [];
        $this->changeAllGrade = '';
        $this->changeAllSection = '';
    }

    public function deleteRow($index)
    {
        array_splice($this->rows, $index, 1);
        $this->rows = array_values($this->rows);
    }

    public function updatedChangeAllGrade($value)
    {
        if ($value === '') {
            return;
        }
        foreach ($this->rows as $index => $row) {
            $this->rows[$index]['grade'] = $value;
        }
    }

    public function updatedChangeAllSection($value)
    {
        foreach ($this->rows as $index => $row) {
            $this->rows[$index]['section'] = $value;
        }
    }

    protected function normalizeGrade($value): string
    {
        $v = strtolower(trim((string) $value));

        if (in_array($v, ['kinder', 'kindergarten', 'k'], true)) {
            return 'k';
        }

        if (in_array($v, ['non-graded', 'non graded', 'non_graded'], true)) {
            return 'non_graded';
        }

        return $value;
    }

    protected function processGrade($gradeValue)
    {
        $grade = trim((string) $gradeValue);
        $gradeLower = strtolower($grade);

        // Kindergarten: "k", "kinder", "kindergarten", "grade k"
        if (preg_match('/^(grade\s*)?(k|kinder|kindergarten)$/', $gradeLower)) {
            return 'k';
        }

        // Try to extract a number from the grade
        if (preg_match('/\d+/', $grade, $matches)) {
            return $matches[0];
        }

        // Roman numerals (I–XII), e.g. "Grade V" => 5
        if (preg_match('/\b(xii|xi|x|ix|viii|vii|vi|v|iv|iii|ii|i)\b/', $gradeLower, $matches)) {
            $roman = ['i' => 1, 'ii' => 2, 'iii' => 3, 'iv' => 4, 'v' => 5, 'vi' => 6,
                'vii' => 7, 'viii' => 8, 'ix' => 9, 'x' => 10, 'xi' => 11, 'xii' => 12];

            return (string) $roman[$matches[1]];
        }

        // Default to non-graded
        return 'non-graded';
    }

    protected function getSf1Sheet(Spreadsheet $spreadsheet): Worksheet
    {
        $sheetIndex = $spreadsheet->getSheetCount() === 1 ? 0 : 1;

        return $spreadsheet->getSheet($sheetIndex);
    }

    public function importPreview()
    {
        // kept for backward compatibility, but not used in UI
        $this->validate();

        $path = $this->excel->getRealPath();

        try {
            $spreadsheet = IOFactory::load($path);
        } catch (\Exception $e) {
            $this->addError('excel', 'Unable to read spreadsheet file.');

            return;
        }

        $sheet = $this->getSf1Sheet($spreadsheet);

        $this->rows = [];

        $row = 7;
        $malesBatchDone = false;

        // pupils grade
        $grade = $this->processGrade($sheet->getCell('AE'. 4)->getValue());
        // pupils section
        $section = trim((string) $sheet->getCell('AM'. 4)->getValue());

        while (true) {
            $lrn = trim((string) $sheet->getCell('A'.$row)->getValue());
            $sex = trim((string) $sheet->getCell('G'.$row)->getValue());

            if ($malesBatchDone && $sex == null) {
                break;
            }
            if ($lrn < 2000 || $lrn === null) {
                $malesBatchDone = true;
                $row++;

                continue;
            }

            $this->rows[] = $this->parseSf1Row($sheet, $row, $grade, $section);

            $row++;
        }

        $this->preview = true;
    }

    protected function readRows(Spreadsheet $spreadsheet): array
    {
        $sheet = $this->getSf1Sheet($spreadsheet);

        $grade = $this->processGrade($sheet->getCell('AE'. 4)->getValue());
        $section = trim((string) $sheet->getCell('AM'. 4)->getValue());

        $rows = [];

        $row = 7;
        while (true) {
            $lrn = trim((string) $sheet->getCell('A'.$row)->getValue());
            if ($lrn === '' || $lrn === null) {
                break;
            }

            $rows[] = $this->parseSf1Row($sheet, $row, $grade, $section);

            $row++;
        }

        return $rows;
    }

    public function save()
    {
        // If rows not parsed yet, parse from uploaded file first
        if (empty($this->rows)) {
            if (! $this->excel) {
                $this->addError('excel', 'Please select an Excel file to upload.');

                return;
            }

            $this->validate();

            $path = $this->excel->getRealPath();

            try {
                $spreadsheet = IOFactory::load($path);
            } catch (\Exception $e) {
                $this->addError('excel', 'Unable to read spreadsheet file.');

                return;
            }

            $this->rows = $this->readRows($spreadsheet);
        }

        DB::transaction(function () {
            foreach ($this->rows as $row) {
                $measurements = $this->calculateNutritionalData($row);

                NutritionalStatus::create([
                    'full_name' => $row['fullname'] ?? null,
                    'last_name' => $row['last_name'] ?? null,
                    'first_name' => $row['first_name'] ?? null,
                    'suffix_name' => $row['middle_name'] ?? null,
                    'sex' => $this->normalizeSex($row['sex'] ?? null),
                    'birthday' => $row['birthdate'] ?? null,
                    'weight' => $measurements['weight'],
                    'height' => $measurements['height'],
                    'age_years' => $row['age_years'] ?? null,
                    'age_months' => $row['age_months'] ?? null,
                    'bmi' => $measurements['bmi'],
                    'nutritional_status' => $measurements['nutritional_status'],
                    'height_for_age' => $measurements['height_for_age'],
                    'grade' => isset($row['grade']) ? $this->normalizeGrade($row['grade']) : null,
                    'section' => $row['section'] ?? null,
                    'ip' => $this->parseBoolean($row['ip'] ?? false),
                    '_4ps' => $this->parseBoolean($row['_4ps'] ?? false),
                    'pardo' => $this->parseBoolean($row['pardo'] ?? false),
                    'dewormed' => $this->parseBoolean($row['dewormed'] ?? false),
                    'sbfp_previous_beneficiary' => $this->parseBoolean($row['sbfp_previous_beneficiary'] ?? false),
                    'parent_consent_milk' => array_key_exists('parent_consent_milk', $row)
                        ? $this->parseBoolean($row['parent_consent_milk'])
                        : null,
                ]);
            }
        });

        // Save parsed data to JSON for reference
        $filename = 'uploads/sf1_'.now()->format('Ymd_His').'.json';
        Storage::put($filename, json_encode($this->rows, JSON_PRETTY_PRINT));

        session()->flash('message', count($this->rows).' records imported successfully. Data saved to storage/'.$filename);

        // Reset after save
        $this->excel = null;
        $this->rows = [];
        $this->preview = false;
    }

    protected function parseSf1Row(Worksheet $sheet, int $row, $grade, string $section): array
    {
        $fullname = trim((string) $sheet->getCell('C'.$row)->getValue());
        $birthdate = $this->parseDate($sheet->getCell('H'.$row)->getValue());
        $nameParts = $this->parseFullname($fullname);
        $ageData = $this->calculateAge($birthdate);

        return [
            'lrn' => trim((string) $sheet->getCell('A'.$row)->getValue()),
            'fullname' => $fullname,
            'last_name' => $nameParts['last_name'],
            'first_name' => $nameParts['first_name'],
            'middle_name' => $nameParts['middle_name'],
            'sex' => $this->normalizeSex($sheet->getCell('G'.$row)->getValue()),
            'birthdate' => $birthdate,
            'age_years' => $ageData['years'],
            'age_months' => $ageData['months'],
            'ip' => $sheet->getCell('N'.$row)->getValue(),
            'grade' => $grade,
            'section' => $section,
            'row' => $row,
            'weight' => $this->parseMeasurement($sheet->getCell('AV'.$row)->getCalculatedValue()),
            'height' => $this->parseMeasurement($sheet->getCell('AW'.$row)->getCalculatedValue()),
            'dewormed' => $this->parseBoolean($sheet->getCell('AX'.$row)->getCalculatedValue()),
            '_4ps' => $this->parseBoolean($sheet->getCell('AY'.$row)->getCalculatedValue()),
            'sbfp_previous_beneficiary' => $this->parseBoolean($sheet->getCell('AZ'.$row)->getCalculatedValue()),
            'pardo' => false,
        ];
    }

    protected function calculateNutritionalData(array $row): array
    {
        $weight = $this->parseMeasurement($row['weight'] ?? null);
        $height = $this->parseMeasurement($row['height'] ?? null);
        $ageInMonths = ((int) ($row['age_years'] ?? 0) * 12) + (int) ($row['age_months'] ?? 0);

        return [
            'weight' => $weight,
            'height' => $height,
        ] + NutritionalCalculator::classify($weight, $height, $ageInMonths, $this->normalizeSex($row['sex'] ?? null));
    }

    protected function parseMeasurement($value): ?float
    {
        if ($value === null || trim((string) $value) === '') {
            return null;
        }

        $value = str_replace(',', '', trim((string) $value));

        return is_numeric($value) && (float) $value > 0 ? (float) $value : null;
    }

    protected function parseBoolean($value): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        $value = strtolower(trim((string) $value));

        return ! in_array($value, ['', '0', 'no', 'n', 'false'], true);
    }

    protected function normalizeSex($value): ?string
    {
        $value = strtolower(trim((string) $value));

        return match ($value) {
            'm', 'male' => 'm',
            'f', 'female' => 'f',
            default => $value !== '' ? $value : null,
        };
    }

    protected function parseDate($dateValue)
    {
        if (empty($dateValue)) {
            return null;
        }

        try {
            // Handle various date formats
            $dateValue = trim((string) $dateValue);

            // If it's numeric (Excel date serial), convert it
            if (is_numeric($dateValue) && $dateValue > 0) {
                $date = \DateTime::createFromFormat('U', ($dateValue - 25569) * 86400);

                return $date ? $date->format('Y-m-d') : null;
            }

            // Check if it matches MM-DD-YYYY format (e.g., 08-23-2015)
            if (preg_match('/^\d{1,2}-\d{1,2}-\d{4}$/', $dateValue)) {
                return Carbon::createFromFormat('m-d-Y', $dateValue)->format('Y-m-d');
            }

            // Try to parse as string with flexible parsing
            return Carbon::parse($dateValue)->format('Y-m-d');
        } catch (\Exception $e) {
            return null;
        }
    }

    protected function parseAge($ageValue)
    {
        if (empty($ageValue)) {
            return null;
        }

        // Extract the first number found
        if (preg_match('/\d+/', trim((string) $ageValue), $matches)) {
            return (int) $matches[0];
        }

        return null;
    }

    protected function parseFullname($fullname)
    {
        if (empty($fullname)) {
            return [
                'last_name' => null,
                'first_name' => null,
                'middle_name' => null,
            ];
        }

        $fullname = trim((string) $fullname);

        // Check if format is "lastname, firstname, middlename" or similar
        if (strpos($fullname, ',') !== false) {
            $parts = array_map('trim', explode(',', $fullname));

            return [
                'last_name' => $parts[0] ?? null,
                'first_name' => $parts[1] ?? null,
                'middle_name' => $parts[2] ?? null,
            ];
        }

        // Fallback: treat as first name only
        return [
            'last_name' => null,
            'first_name' => $fullname,
            'middle_name' => null,
        ];
    }

    protected function calculateAge($birthdate)
    {
        if (empty($birthdate)) {
            return [
                'years' => null,
                'months' => null,
            ];
        }

        try {
            $birth = Carbon::parse($birthdate);
            $now = Carbon::now();

            // Return null if birthdate is in the future
            if ($birth->isFuture()) {
                return [
                    'years' => null,
                    'months' => null,
                ];
            }

            $years = $birth->diffInYears($now);
            $months = $birth->copy()->addYears($years)->diffInMonths($now);

            return [
                'years' => $years,
                'months' => $months,
            ];
        } catch (\Exception $e) {
            return [
                'years' => null,
                'months' => null,
            ];
        }
    }

    public function render()
    {
        return view('livewire.dashboard.pupils.upload-s-f1', [
            'title' => 'SF1 upload',
            'help' => 'Upload a School Form 1 (SF1) Excel file exported from the DepEd LIS system. The file is parsed to extract pupil names, grade, section, birth dates, and demographic flags. Review the preview table before saving.',
        ]);
    }
}
