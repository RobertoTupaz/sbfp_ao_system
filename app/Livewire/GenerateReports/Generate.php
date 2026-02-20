<?php

namespace App\Livewire\GenerateReports;

use Livewire\Component;
use App\Models\AllSchool;
use App\Models\Form_1;
use App\Models\SchoolYear;
use App\Models\State;
use Illuminate\Support\Facades\Log;
use Jantinnerezo\LivewireAlert\Facades\LivewireAlert;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class Generate extends Component
{
    public $schoolYears = [];
    public $selectedYear = '';
    public $states = [];
    public $selectedStateGlobal = '';

    public function mount()
    {
        $this->schoolYears = SchoolYear::orderBy('school_year')->pluck('school_year')->toArray();
        $this->states = State::orderBy('id')->pluck('name')->toArray();

        $month = (int) date('n');
        $year = (int) date('Y');
        $defaultYear = ($month >= 6) ? $year : ($year - 1);

        // prefer any previously saved selections in session (set by other components)
        $defaultValue = in_array((string) $defaultYear, $this->schoolYears, true)
            ? (string) $defaultYear
            : '';

        $this->selectedYear = session('focal_selected_year', $defaultValue);
        $this->selectedStateGlobal = session('focal_selected_state', '');
    }

    public function generateBaseline()
    {
        $template = public_path('exel/baseline.xlsx');
        if (!file_exists($template)) {
            session()->flash('error', 'baseline.xlsx not found in public/exel');
            return;
        }

        $state = $this->selectedStateGlobal ?: session('focal_selected_state', '');
        $year = $this->selectedYear ?: session('focal_selected_year', '');

        $query = Form_1::query()
            ->select([
                'school_id',
                'name',
                'sex',
                'grade',
                'bmi_a',
                'hfa',
                'parent_consent_milk',
                'in_4ps',
                'ip',
                'pardo',
                'dewormed',
                'beneficiary_of_sbfp_in_previous_year',
            ]);

        if ($state !== '') {
            $query->where('survey_state', $state);
        }
        if (!empty($year)) {
            $query->where('school_year', $year);
        }

        $records = $query->orderBy('school_id')->orderBy('name')->get();

        if ($records->isEmpty()) {
            session()->flash('error', 'No baseline records found for the selected filters.');
            return;
        }

        $schoolIds = $records->pluck('school_id')->unique()->values();
        $schoolNames = AllSchool::whereIn('school_id', $schoolIds)
            ->pluck('school_name', 'school_id');

        try {
            $spreadsheet = IOFactory::load($template);
            $baseSheet = $spreadsheet->getActiveSheet();

            // Remove all extra sheets in template
            while ($spreadsheet->getSheetCount() > 1) {
                $spreadsheet->removeSheetByIndex(1);
            }

            if ($baseSheet->getTitle() !== 'TEMPLATE_BASE') {
                $baseSheet->setTitle('TEMPLATE_BASE');
            }

            $groups = $records->groupBy('school_id');

            foreach ($groups as $schoolId => $rows) {
                $schoolName = $schoolNames[$schoolId] ?? 'Unknown school';
                $rawTitle = $schoolId . ' - ' . $schoolName;
                $title = $this->uniqueSheetTitle($spreadsheet, $rawTitle);

                $sheet = clone $baseSheet;
                $sheet->setTitle($title);
                $spreadsheet->addSheet($sheet);

                // Header info
                $sheet->setCellValue('A1', 'Baseline generated for school: ' . $schoolId);
                $sheet->setCellValue('A2', $schoolName);

                // Clear existing template rows from row 4 onward
                if ($sheet->getHighestRow() > 3) {
                    $sheet->removeRow(4, $sheet->getHighestRow() - 3);
                }

                // Fill school records
                $rowNumber = 4;
                foreach ($rows as $idx => $rec) {
                    $sheet->setCellValueByColumnAndRow(1, $rowNumber, $idx + 1);
                    $sheet->setCellValueByColumnAndRow(2, $rowNumber, strtolower($rec->name ?? ''));
                    $sheet->setCellValueByColumnAndRow(3, $rowNumber, strtolower($rec->sex ?? 'm') === 'male' ? 'm' : 'f');
                    $sheet->setCellValueByColumnAndRow(4, $rowNumber, strtolower($rec->grade ?? ''));
                    $sheet->setCellValueByColumnAndRow(11, $rowNumber, strtolower($rec->bmi_a ?? ''));
                    $sheet->setCellValueByColumnAndRow(12, $rowNumber, strtolower($rec->hfa ?? ''));
                    $sheet->setCellValueByColumnAndRow(13, $rowNumber, $rec->parent_consent_milk ? 'yes' : 'no');
                    $sheet->setCellValueByColumnAndRow(14, $rowNumber, $rec->in_4ps ? 'yes' : 'no');
                    $sheet->setCellValueByColumnAndRow(15, $rowNumber, $rec->ip ? 'yes' : 'no');
                    $sheet->setCellValueByColumnAndRow(16, $rowNumber, $rec->pardo ? 'yes' : 'no');
                    $sheet->setCellValueByColumnAndRow(17, $rowNumber, $rec->dewormed ? 'yes' : 'no');
                    $sheet->setCellValueByColumnAndRow(18, $rowNumber, $rec->beneficiary_of_sbfp_in_previous_year ? 'yes' : 'no');
                    $rowNumber++;
                }
            }

            // Prepare output
            $outputDir = public_path('downloaded_exel');
            if (!is_dir($outputDir)) {
                @mkdir($outputDir, 0775, true);
            }

            $statePart = $state !== '' ? preg_replace('/[^A-Za-z0-9_-]/', '_', strtolower($state)) : 'all_states';
            $yearPart = !empty($year) ? preg_replace('/[^0-9-]/', '', (string)$year) : 'all_years';
            $fileName = 'baseline_' . $statePart . '_' . $yearPart . '_' . now()->format('Ymd_His') . '.xlsx';
            $outputPath = $outputDir . DIRECTORY_SEPARATOR . $fileName;

            $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
            $writer->setPreCalculateFormulas(false);
            $writer->save($outputPath);

            return response()->download($outputPath);
        } catch (\Throwable $e) {
            Log::error('GenerateReports baseline failed: ' . $e->getMessage());
            session()->flash('error', 'Failed to generate baseline report.');
        }
    }

    private function uniqueSheetTitleFromArray(string $title, array $used): string
    {
        $title = preg_replace('/[\\\\\\/\\?\\*\\[\\]\\:]/', '', $title);
        $base = substr($title, 0, 31);
        $name = $base;
        $i = 1;

        while (in_array($name, $used, true)) {
            $suffix = '_' . $i++;
            $name = substr($base, 0, 31 - strlen($suffix)) . $suffix;
        }

        return $name;
    }

    private function uniqueSheetTitle($spreadsheet, string $title): string
    {
        $base = substr($title, 0, 31);
        $name = $base;
        $i = 1;

        while ($spreadsheet->sheetNameExists($name)) {
            $suffix = '_' . $i++;
            $name = substr($base, 0, 31 - strlen($suffix)) . $suffix;
        }

        return $name;
    }

    public function updatedSelectedYear()
    {
        // Placeholder for handling selection changes if needed
    }
    public function updatedSelectedStateGlobal()
    {
        // placeholder if state-specific actions are needed
    }

    public function saveSelections()
    {
        session([
            'focal_selected_state' => $this->selectedStateGlobal,
            'focal_selected_year' => $this->selectedYear,
        ]);

        // $this->dispatch('focal-selection-saved', ['state' => $this->selectedStateGlobal, 'year' => $this->selectedYear]);
        $this->notif();
    }

    public function notif()
    {
        LivewireAlert::title('Changes saved!')
            ->success()
            ->show();
    }

    private function sheetTitle(string $schoolName, string $schoolId): string
    {
        $title = trim($schoolName . ' ' . $schoolId);
        $title = preg_replace('/[\\\\\\/\\?\\*\\:\\[\\]]/', '_', $title) ?? '';
        if ($title === '') {
            $title = 'School_' . $schoolId;
        }
        return mb_substr($title, 0, Worksheet::SHEET_TITLE_MAXIMUM_LENGTH);
    }

    public function render()
    {
        return view('livewire.generate-reports.generate');
    }
}
