<?php

namespace App\Livewire\Dashboard\Pupils;

use App\Models\NutritionalStatus;
use Livewire\Component;
use Livewire\WithFileUploads;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class UploadSF1 extends Component
{
    use WithFileUploads;

    public $excel;
    public $rows = [];
    public $preview = false;
    public $changeAllGrade = '';

    protected $rules = [
        'excel' => 'required|file|mimes:xlsx,xls,csv'
    ];

    public function updatedExcel()
    {
        $this->preview = false;
        $this->rows = [];
        $this->changeAllGrade = '';
    }

    public function updatedChangeAllGrade($value)
    {
        if ($value === '') return;
        foreach ($this->rows as $index => $row) {
            $this->rows[$index]['grade'] = $value;
        }
    }

    protected function processGrade($gradeValue)
    {
        $grade = trim((string) $gradeValue);
        $gradeLower = strtolower($grade);

        // Check if "kinder" or "kindergarten"
        if ($gradeLower === 'kinder' || $gradeLower === 'kindergarten') {
            return 'kinder';
        }

        // Try to extract a number from the grade
        if (preg_match('/\d+/', $grade, $matches)) {
            return $matches[0];
        }

        // Default to non-graded
        return 'non-graded';
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

        try {
            $sheet = $spreadsheet->getSheet(1);
        } catch (\Exception $e) {
            $this->addError('excel', 'File does not contain a second sheet.');
            return;
        }

        $this->rows = [];

        $row = 7;
        $malesBatchDone = false;

        //pupils grade
        $grade = $this->processGrade($sheet->getCell('AE' . 4)->getValue());
        //pupils section
        $section = trim((string) $sheet->getCell('AM' . 4)->getValue());
    
        while (true) {
            $lrn = trim((string) $sheet->getCell('A' . $row)->getValue());
            $sex = trim((string) $sheet->getCell('G' . $row)->getValue());

            if ($malesBatchDone & $sex == null) {
                break;
            }
            if ($lrn < 2000 || $lrn === null) {
                $malesBatchDone = true;
                $row++;
                continue;
            }

            $fullname = trim((string) $sheet->getCell('C' . $row)->getValue());
            $sex = trim((string) $sheet->getCell('G' . $row)->getValue());
            $birthdate = trim((string) $sheet->getCell('H' . $row)->getValue());
            $ip = trim((string) $sheet->getCell('N' . $row)->getValue());

            // Parse fullname and calculate age
            $nameParts = $this->parseFullname($fullname);
            $parsedBirthdate = $this->parseDate($birthdate);
            $ageData = $this->calculateAge($parsedBirthdate);

            $this->rows[] = [
                'lrn' => $lrn,
                'fullname' => $fullname,
                'last_name' => $nameParts['last_name'],
                'first_name' => $nameParts['first_name'],
                'middle_name' => $nameParts['middle_name'],
                'sex' => $sex,
                'birthdate' => $parsedBirthdate,
                'age_years' => $ageData['years'],
                'age_months' => $ageData['months'],
                'ip' => $ip,
                'grade' => $grade,
                'section' => $section,
                'row' => $row,
                '_4ps' => false,
                'pardo' => false,
                'dewormed' => false,
                'sbfp_previous_beneficiary' => false,
            ];

            $row++;
        }

        $this->preview = true;
    }

    public function save()
    {
        // If rows not parsed yet, parse from uploaded file first
        if (empty($this->rows)) {
            if (!$this->excel) {
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

            try {
                $sheet = $spreadsheet->getSheet(1);
            } catch (\Exception $e) {
                $this->addError('excel', 'File does not contain a second sheet.');
                return;
            }

            $grade = $this->processGrade($sheet->getCell('AE' . 4)->getValue());
            $section = trim((string) $sheet->getCell('AM' . 4)->getValue());

            $this->rows = [];

            $row = 7;
            while (true) {
                $lrn = trim((string) $sheet->getCell('A' . $row)->getValue());
                if ($lrn === '' || $lrn === null) {
                    break;
                }

                $fullname = trim((string) $sheet->getCell('C' . $row)->getValue());
                $sex = trim((string) $sheet->getCell('G' . $row)->getValue());
                $birthdate = trim((string) $sheet->getCell('H' . $row)->getValue());
                $ip = trim((string) $sheet->getCell('N' . $row)->getValue());

                // Parse fullname and calculate age
                $nameParts = $this->parseFullname($fullname);
                $parsedBirthdate = $this->parseDate($birthdate);
                $ageData = $this->calculateAge($parsedBirthdate);

                $this->rows[] = [
                    'lrn' => $lrn,
                    'fullname' => $fullname,
                    'last_name' => $nameParts['last_name'],
                    'first_name' => $nameParts['first_name'],
                    'middle_name' => $nameParts['middle_name'],
                    'sex' => $sex,
                    'birthdate' => $parsedBirthdate,
                    'age_years' => $ageData['years'],
                    'age_months' => $ageData['months'],
                    'ip' => $ip,
                    'grade' => $grade,
                    'section' => $section,
                    'row' => $row,
                    '_4ps' => false,
                    'pardo' => false,
                    'dewormed' => false,
                    'sbfp_previous_beneficiary' => false,
                ];

                $row++;
            }
        }

        // Create NutritionalStatus records from parsed rows
        foreach ($this->rows as $row) {
            NutritionalStatus::create([
                'full_name' => $row['fullname'] ?? null,
                'last_name' => $row['last_name'] ?? null,
                'first_name' => $row['first_name'] ?? null,
                'suffix_name' => $row['middle_name'] ?? null,
                'sex' => $row['sex'] ?? null,
                'birthday' => $row['birthdate'] ?? null,
                'weight' => !empty($row['ip']) ? (float) $row['ip'] : null,
                'age_years' => $row['age_years'] ?? null,
                'age_months' => $row['age_months'] ?? null,
                'grade' => $row['grade'] ?? null,
                'section' => $row['section'] ?? null,
                'ip' => !empty($row['ip']) ? true : false,
                '_4ps' => !empty($row['_4ps']),
                'pardo' => !empty($row['pardo']),
                'dewormed' => !empty($row['dewormed']),
                'sbfp_previous_beneficiary' => !empty($row['sbfp_previous_beneficiary']),
            ]);
        }

        // Save parsed data to JSON for reference
        $filename = 'uploads/sf1_' . now()->format('Ymd_His') . '.json';
        Storage::put($filename, json_encode($this->rows, JSON_PRETTY_PRINT));

        session()->flash('message', count($this->rows) . ' records imported successfully. Data saved to storage/' . $filename);

        // Reset after save
        $this->excel = null;
        $this->rows = [];
        $this->preview = false;
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
        return view('livewire.dashboard.pupils.upload-s-f1');
    }
}
