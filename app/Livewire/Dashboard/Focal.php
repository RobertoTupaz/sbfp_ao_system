<?php

namespace App\Livewire\Dashboard;

use Livewire\Component;
use App\Models\AllSchool;
use App\Models\SchoolYear;
use App\Models\Form_1;
use App\Models\State;
use Livewire\WithFileUploads;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Jantinnerezo\LivewireAlert\Facades\LivewireAlert;

class Focal extends Component
{
    use WithFileUploads;
    public $schoolsByDistrict = [];
    public $search = '';
    public $schoolYears = [];
    public $selectedYear = '';
    public $uploads = [];
    public $states = [];
    public $selectedState = [];
    public $selectedStateGlobal = '';
    public $schoolsWithData = [];

    public function mount()
    {
        $this->schoolYears = SchoolYear::orderBy('school_year')->pluck('school_year')->toArray();
        $this->states = State::orderBy('id')->pluck('name')->toArray();

        $month = (int) date('n');
        $year = (int) date('Y');
        $defaultYear = ($month >= 6) ? $year : ($year - 1);

        // Use the default only if it exists in the available school years
        $defaultValue = in_array((string) $defaultYear, $this->schoolYears, true)
            ? (string) $defaultYear
            : '';

        // restore persisted selections if available
        $this->selectedYear = session('focal_selected_year', $defaultValue);
        $this->selectedStateGlobal = session('focal_selected_state', '');
        $this->loadSchools();
    }

    public function saveSelections()
    {
        session([
            'focal_selected_state' => $this->selectedStateGlobal,
            'focal_selected_year' => $this->selectedYear,
        ]);

        //$this->dispatch('focal-selection-saved', ['state' => $this->selectedStateGlobal, 'year' => $this->selectedYear]);
    
        $this->notif();
    }

    public function notif()
    {
        LivewireAlert::title('Changes saved!')
            ->success()
            ->show();
    }

    public function updatedSearch()
    {
        $this->loadSchools();
    }

    public function updatedSelectedStateGlobal()
    {
        $this->loadSchools();
    }

    public function updatedSelectedYear()
    {
        $this->loadSchools();
    }

    public function saveForm1($schoolId)
    {
        if (empty($this->uploads[$schoolId])) {
            Log::info("Save requested but no upload found for school {$schoolId}");
            return;
        }

        try {
            $uploaded = $this->uploads[$schoolId];
            $path = $uploaded->getRealPath();
            $spreadsheet = IOFactory::load($path);
            $sheet = $spreadsheet->getActiveSheet();

            $highestColumn = $sheet->getHighestColumn();
            $highestRow = $sheet->getHighestRow();

            $rows = [];
            for ($row = 13; $row <= $highestRow; $row++) {
                $rowData = $sheet->rangeToArray("A{$row}:{$highestColumn}{$row}", null, true, true, false);
                if (empty($rowData) || !isset($rowData[0])) {
                    continue;
                }
                $cells = $rowData[0];

                // stop processing when column A contains the marker
                $firstCol = isset($cells[0]) ? trim((string) $cells[0]) : null;
                if ($firstCol === 'Prepared by :' || strcasecmp($firstCol, 'Prepared by :') === 0) {
                    break;
                }

                $allEmpty = true;
                foreach ($cells as $c) {
                    if ($c !== null && $c !== '') {
                        $allEmpty = false;
                        break;
                    }
                }
                if ($allEmpty) {
                    continue;
                }
                $rows[] = $cells;
            }

            Log::info($this->selectedYear);

            // Persist each parsed row to the Form_1 model
            $saved = 0;

            // determine state/year used for this upload
            $stateForUpload = isset($this->selectedState[$schoolId]) && $this->selectedState[$schoolId] !== ''
                ? $this->selectedState[$schoolId]
                : ($this->selectedStateGlobal !== '' ? $this->selectedStateGlobal : null);

            // Delete existing records for this school + (state if set) + (year if set)
            try {
                $deleteQuery = Form_1::where('school_id', $schoolId);
                if ($stateForUpload !== null) {
                    $deleteQuery->where('survey_state', $stateForUpload);
                }
                if (!empty($this->selectedYear)) {
                    $deleteQuery->where('school_year', $this->selectedYear);
                }
                $deletedCount = $deleteQuery->delete();
                if ($deletedCount) {
                    Log::info("Deleted {$deletedCount} existing Form_1 rows for school {$schoolId}");
                }
            } catch (\Throwable $e) {
                Log::error('Error deleting existing Form_1 rows for school ' . $schoolId . ': ' . $e->getMessage());
            }
            foreach ($rows as $cells) {
                try {
                    $data = [
                        'school_id' => $schoolId,
                        'school_year' => $this->selectedYear ?: null,
                        'survey_state' => $stateForUpload,
                        'name' => isset($cells[1]) ? trim((string)$cells[1]) : null,
                        'sex' => isset($cells[2]) ? trim((string)$cells[2]) : null,
                        'grade' => isset($cells[3]) ? trim((string)$cells[3]) : null,
                        'section' => isset($cells[4]) ? trim((string)$cells[4]) : null,
                        'date_of_birth' => $this->parseDate($cells[5] ?? null),
                        'date_of_weighing_or_measuring' => $this->parseDate($cells[6] ?? null),
                        'age_in_years' => isset($cells[7]) && $cells[7] !== '' ? (int)$cells[7] : null,
                        'age_in_months' => isset($cells[8]) && $cells[8] !== '' ? (int)$cells[8] : null,
                        'weight' => isset($cells[9]) && $cells[9] !== '' ? (int)$cells[9] : null,
                        'height' => isset($cells[10]) && $cells[10] !== '' ? (float)$cells[10] : null,
                        'bmi_for_6_years_and_above' => isset($cells[11]) ? trim((string)$cells[11]) : null,
                        'bmi_a' => isset($cells[12]) ? trim((string)$cells[12]) : null,
                        'hfa' => isset($cells[13]) ? trim((string)$cells[13]) : null,
                        'in_4ps' => $this->toBool($cells[14] ?? null),
                        'ip' => $this->toBool($cells[15] ?? null),
                        'pardo' => $this->toBool($cells[16] ?? null),
                        'dewormed' => $this->toBool($cells[17] ?? null),
                        'parent_consent_milk' => $this->toBool($cells[18] ?? null),
                        'beneficiary_of_sbfp_in_previous_year' => $this->toBool($cells[19] ?? null),
                    ];

                    Form_1::create($data);
                    $saved++;
                } catch (\Throwable $e) {
                    Log::error('Error saving Form_1 row for school ' . $schoolId . ': ' . $e->getMessage(), ['row' => $cells]);
                }
            }

            Log::info("Saved {$saved} Form_1 rows for school {$schoolId}");

            // reset the upload for this school
            $this->uploads[$schoolId] = null;

            //$this->dispatch('focal-upload-saved', ['school' => $schoolId]);
            $this->notif();
            $this->loadSchools();
        } catch (\Throwable $e) {
            Log::error('Error processing uploaded Form1 for school ' . $schoolId . ': ' . $e->getMessage());
            $this->dispatch('focal-upload-error', ['school' => $schoolId, 'message' => $e->getMessage()]);
        }
    }

    public function loadSchools()
    {
        $query = AllSchool::query();

        if (!empty($this->search)) {
            $term = "%{$this->search}%";
            $query->where(function ($q) use ($term) {
                $q->where('school_name', 'like', $term)
                    ->orWhere('district', 'like', $term)
                    ->orWhere('school_id', 'like', $term);
            });
        }

        // if (!empty($this->selectedYear)) {
        //     $query->where('school_year', $this->selectedYear);
        // }

        $this->schoolsByDistrict = $query
            ->orderBy('district')
            ->orderBy('school_name')
            ->get()
            ->groupBy('district')
            ->toArray();

        // compute which schools already have data for the chosen state/year
        $schoolIds = [];
        foreach ($this->schoolsByDistrict as $group) {
            foreach ($group as $s) {
                $schoolIds[] = $s['school_id'];
            }
        }
        $schoolIds = array_values(array_unique($schoolIds));

        if (empty($schoolIds)) {
            $this->schoolsWithData = [];
            return;
        }

        $q = Form_1::whereIn('school_id', $schoolIds);
        if ($this->selectedStateGlobal !== '') {
            $q->where('survey_state', $this->selectedStateGlobal);
        }
        if (!empty($this->selectedYear)) {
            $q->where('school_year', $this->selectedYear);
        }

        $idsWith = $q->distinct()->pluck('school_id')->toArray();
        $this->schoolsWithData = array_flip($idsWith);
    }

    public function logClicked($action, $schoolId)
    {
        $norm = strtolower(str_replace('open', '', $action));
        if ($norm === 'baseline') {
            $this->generateBaseline($schoolId);
        } else if ($norm === 'midline') {
            $this->generateMidline($schoolId);
        } else if ($norm === 'form7') {
            $this->generateForm7($schoolId);
        } else if ($norm === 'form2') {
            $this->generateForm2($schoolId);
        }
    }

    public function generateBaseline($schoolId)
    {
        $template = public_path('exel/FormulatedExel.xlsx');
        if (!file_exists($template)) {
            session()->flash('error', 'FormulatedExel.xlsx not found in public/exel');
            Log::error('FormulatedExel write failed - template not found: ' . $template);
            return;
        }

        try {
            $spreadsheet = IOFactory::load($template);

            // select the worksheet by name
            $sheet = $spreadsheet->getSheetByName('F1_B');
            if (!$sheet) {
                session()->flash('error', 'Template does not contain a sheet named F1_B');
                Log::error('Sheet F1_B not found in template: ' . $template);
                return;
            }

            // write a small marker and the school name into the sheet (example)
            $school = AllSchool::where('school_id', $schoolId)->first();
            $schoolName = $school ? $school->school_name : 'Unknown school';
            $sheet->setCellValue('A1', 'Baseline generated for school: ' . $schoolId);
            $sheet->setCellValue('A2', $schoolName);

            // fetch Form_1 rows for this school + selected state/year from session
            $year = session('focal_selected_year', '');
            $state = session('focal_selected_state', '');

            $query = Form_1::where('school_id', $schoolId);
            if ($state !== '') {
                $query->where('survey_state', $state);
            }
            if (!empty($year)) {
                $query->where('school_year', $year);
            }
            $records = $query->orderBy('name')->get();

            // starting row in the template
            $startRow = 4;
            foreach ($records as $index => $rec) {
                $row = $startRow + $index;

                $sheet->setCellValueByColumnAndRow(1, $row, $index + 1); // No.
                $sheet->setCellValueByColumnAndRow(2, $row, strtolower($rec->name ?? ''));
                $sheet->setCellValueByColumnAndRow(3, $row, strtolower($rec->sex ?? 'm') == 'male' ? 'm' : 'f');
                $sheet->setCellValueByColumnAndRow(4, $row, strtolower($rec->grade ?? ''));
                $sheet->setCellValueByColumnAndRow(11, $row, strtolower($rec->bmi_a ?? ''));
                $sheet->setCellValueByColumnAndRow(12, $row, strtolower($rec->hfa ?? ''));
                $sheet->setCellValueByColumnAndRow(13, $row, ($rec->parent_consent_milk ? 'yes' : 'no'));
                $sheet->setCellValueByColumnAndRow(14, $row, ($rec->in_4ps ? 'yes' : 'no'));
                $sheet->setCellValueByColumnAndRow(15, $row, ($rec->ip ? 'yes' : 'no'));
                $sheet->setCellValueByColumnAndRow(16, $row, ($rec->pardo ? 'yes' : 'no'));
                $sheet->setCellValueByColumnAndRow(17, $row, ($rec->dewormed ? 'yes' : 'no'));
                $sheet->setCellValueByColumnAndRow(18, $row, ($rec->beneficiary_of_sbfp_in_previous_year ? 'yes' : 'no'));
            }

            // save spreadsheet to a new temp file so the original template remains unchanged
            $outFileName = 'Forms_' . time() . '.xlsx';
            $outFile = public_path('downloaded_exel/' . $outFileName);
            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
            $writer->setPreCalculateFormulas(false);
            $writer->save($outFile);

            $downloadUrl = asset('downloaded_exel/' . $outFileName);
            $this->dispatch('focal-baseline-ready', $downloadUrl);
        } catch (\Throwable $e) {
            Log::error('Error generating baseline for school ' . $schoolId . ': ' . $e->getMessage());
            session()->flash('error', 'Error generating baseline');
            $this->dispatch('focal-baseline-error', ['school' => $schoolId, 'message' => $e->getMessage()]);
        }
    }

    public function generateMidline($schoolId) {}

    public function generateForm7($schoolId) {}

    public function generateForm2($schoolId) {}

    public function render()
    {
        return view('livewire.dashboard.focal');
    }

    private function parseDate($value)
    {
        if ($value === null || $value === '') {
            return null;
        }

        if ($value instanceof \DateTime) {
            return $value->format('Y-m-d');
        }

        if (is_numeric($value)) {
            try {
                $timestamp = ($value - 25569) * 86400; // Excel to Unix
                return Carbon::createFromTimestampUTC((int)round($timestamp))->toDateString();
            } catch (\Throwable $e) {
                // fall through to string parse
            }
        }

        try {
            return Carbon::parse((string)$value)->toDateString();
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function toBool($value)
    {
        if ($value === null || $value === '') {
            return false;
        }
        $s = strtolower(trim((string)$value));
        return in_array($s, ['1', 'true', 'yes', 'y', 'x', '✓', 't'], true);
    }
}
