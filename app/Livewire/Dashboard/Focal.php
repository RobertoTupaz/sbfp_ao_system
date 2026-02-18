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

        $this->dispatch('focal-selection-saved', ['state' => $this->selectedStateGlobal, 'year' => $this->selectedYear]);
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
                    if ($c !== null && $c !== '') { $allEmpty = false; break; }
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

            $this->dispatch('focal-upload-saved', ['school' => $schoolId]);
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
