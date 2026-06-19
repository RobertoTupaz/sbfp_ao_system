<?php

namespace App\Livewire\GenerateForms;

use App\Models\Beneficiaries;
use App\Models\SchoolProfile;
use App\Models\SwappedPupils;
use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\NutritionalStatus;
use App\Models\PrimarySecondaryBeneficiaries;
use Illuminate\Support\Facades\Log;

class Buttons extends Component
{
    use WithFileUploads;

    public $uploadJson;
    public $beneficiariesCount;

    public function mount()
    {
        $beneficiaries = Beneficiaries::first();
        $this->beneficiariesCount = $beneficiaries ? $beneficiaries->beneficiaries_count : 0;
    }

    private function ensureDownloadDir(): void
    {
        $dir = public_path('downloaded_exel');
        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);
        }
    }
    public function render()
    {
        return view('livewire.generate-forms.buttons');
    }

    public function generateForm1()
    {
        $template = public_path('exel/Form1.xlsx');
        if (!file_exists($template)) {
            session()->flash('error', 'Form1.xlsx not found in public/exel');
            Log::error('Form1 write failed - template not found: ' . $template);
            return;
        }

        try {
            // load the existing spreadsheet
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($template);
            $sheet = $spreadsheet->getActiveSheet();

            $sheet->setCellValueByColumnAndRow(1, 8, "Name of School / School District : " . (auth()->user()->school->school_name ?? '') . " - " . (auth()->user()->school->district ?? ''));
            $sheet->setCellValueByColumnAndRow(1, 9, "School  ID Number: " . (auth()->user()->school?->school_id ?? ''));

            // starting row in the template
            $startRow = 13;
            $endRow = 0;

            // fetch records from database
            $records = NutritionalStatus::where("isBeneficiary", "=", true)->orderBy('grade')->get();

            // Map new_pupil_id → "removed pupil name - reason" for remarks column
            $swapRemarks = SwappedPupils::query()
                ->join('nutritional_statuses as old', 'swapped_pupils.old_pupil_id', '=', 'old.id')
                ->select('swapped_pupils.new_pupil_id', 'old.full_name as old_name', 'swapped_pupils.reason')
                ->get()
                ->keyBy('new_pupil_id')
                ->map(fn($row) => trim($row->old_name . ($row->reason ? ' - ' . $row->reason : '')))
                ->toArray();

            foreach ($records as $index => $rec) {
                $row = $startRow + $index;

                // write columns:
                // A: No., B: Name, C: Sex, D: Grade, E: Section,
                // F: Date of Birth, G: Date of weighing, H: Age years, I: Age months,
                // J: Weight, K: Height, L: BMI, M: Nutritional status, N: Height for age

                $sheet->setCellValueByColumnAndRow(1, $row, $index + 1);
                $sheet->setCellValueByColumnAndRow(2, $row, $rec->full_name ?? '');
                $sheet->setCellValueByColumnAndRow(3, $row, $rec->sex ?? '');

                $sheet->setCellValueByColumnAndRow(4, $row, $rec->grade ?? '');
                $sheet->setCellValueByColumnAndRow(5, $row, $rec->section ?? '');

                $sheet->setCellValueByColumnAndRow(6, $row, $rec->birthday ? \Carbon\Carbon::parse($rec->birthday)->toDateString() : '');
                $sheet->setCellValueByColumnAndRow(7, $row, $rec->date_of_weighing ? \Carbon\Carbon::parse($rec->date_of_weighing)->toDateString() : '');
                $sheet->setCellValueByColumnAndRow(8, $row, $rec->age_years ?? '');
                $sheet->setCellValueByColumnAndRow(9, $row, $rec->age_months ?? '');
                $sheet->setCellValueByColumnAndRow(10, $row, $rec->weight ?? '');
                $sheet->setCellValueByColumnAndRow(11, $row, $rec->height ?? '');
                $sheet->setCellValueByColumnAndRow(12, $row, $rec->bmi ?? '');
                $sheet->setCellValueByColumnAndRow(13, $row, $rec->nutritional_status ?? '');
                $sheet->setCellValueByColumnAndRow(14, $row, $rec->height_for_age ?? '');
                $sheet->setCellValueByColumnAndRow(15, $row, $rec->_4ps == 1 ? 'Yes' : 'No');
                $sheet->setCellValueByColumnAndRow(16, $row, $rec->ip == 1 ? 'Yes' : 'No');
                $sheet->setCellValueByColumnAndRow(17, $row, $rec->pardo == 1 ? 'Yes' : 'No');
                $sheet->setCellValueByColumnAndRow(18, $row, $rec->dewormed == 1 ? 'Yes' : 'No');
                $sheet->setCellValueByColumnAndRow(19, $row, $rec->parent_consent_milk == 1 ? 'Yes' : 'No');
                $sheet->setCellValueByColumnAndRow(20, $row, $rec->sbfp_previous_beneficiary == 1 ? 'Yes' : 'No');

                $sheet->setCellValueByColumnAndRow(24, $row, $rec->first_name ?? '');
                $sheet->setCellValueByColumnAndRow(25, $row, $rec->last_name ?? '');
                $sheet->setCellValueByColumnAndRow(26, $row, $rec->suffix_name ?? '');

                // Column 21 — Remarks: name of the removed pupil this beneficiary replaced
                $sheet->setCellValueByColumnAndRow(21, $row, $swapRemarks[$rec->id] ?? '');

                $endRow = $row;
            }

            // add a footer row below the last data row and merge columns A and B
            $footerRow = $endRow > 0 ? $endRow + 4 : $startRow;
            $sheet->mergeCells("A{$footerRow}:B{$footerRow}");
            $sheet->setCellValue("A{$footerRow}", 'Prepared by :');
            $sheet->getStyle("A{$footerRow}:B{$footerRow}")
                ->getAlignment()
                ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);

            $sheet->mergeCells("M{$footerRow}:N{$footerRow}");
            $sheet->setCellValue("M{$footerRow}", 'Approved by :');
            $sheet->getStyle("M{$footerRow}:N{$footerRow}")
                ->getAlignment()
                ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);

            $schoolProfile = SchoolProfile::where('school_id', auth()->user()->school_id)->first();

            $focalNameRow = $footerRow + 1;
            $sheet->mergeCells("A{$focalNameRow}:B{$focalNameRow}");
            $sheet->setCellValue("A{$focalNameRow}", $schoolProfile?->school_focal_name ?? '');
            $sheet->getStyle("A{$focalNameRow}:B{$focalNameRow}")
                ->getFont()
                ->setBold(true);

            $sheet->mergeCells("M{$focalNameRow}:N{$focalNameRow}");
            $sheet->setCellValue("M{$focalNameRow}", $schoolProfile?->school_head_name ?? '');
            $sheet->getStyle("M{$focalNameRow}:N{$focalNameRow}")
                ->getFont()
                ->setBold(true);

            $positionRow = $focalNameRow + 1;
            $sheet->mergeCells("A{$positionRow}:B{$positionRow}");
            $sheet->setCellValue("A{$positionRow}", 'SBFP DepEd Focal');

            $sheet->mergeCells("M{$positionRow}:N{$positionRow}");
            $sheet->setCellValue("M{$positionRow}", 'School Head');

            $principalRowHeader = 6;
            $focalNameRowHeader = 7;

            $sheet->setCellValue("H{$principalRowHeader}", "Name of Principal : ".$schoolProfile?->school_head_name ?? '');
            $sheet->setCellValue("H{$focalNameRowHeader}", "Name of Feeding Focal Person : ".$schoolProfile?->school_focal_name ?? '');

            // save spreadsheet to a new temp file so the original template remains unchanged
            $schoolSlug = preg_replace('/[^A-Za-z0-9]+/', '_', auth()->user()->school?->school_name ?? 'Unknown');
            $datetime = \Carbon\Carbon::now()->format('Y-m-d_H-i-s');
            $outFileName = 'Form_1-' . $schoolSlug . '-' . $datetime . '.xlsx';
            $this->ensureDownloadDir();
            $outFile = public_path('downloaded_exel/' . $outFileName);
            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
            $writer->save($outFile);

            session()->flash('success', 'Form1.xlsx generated for download.');
            Log::info($outFileName . ' successfully written with ' . $records->count() . ' records.');

            // dispatch browser event to trigger download of the generated public file
            $downloadUrl = asset('downloaded_exel/' . $outFileName);
            $this->dispatch('form1-ready', $downloadUrl);
            Log::info('Form1 download URL dispatched: ' . $downloadUrl);
        } catch (\Throwable $e) {
            Log::error('Error writing Form1.xlsx: ' . $e->getMessage());
            session()->flash('error', 'Failed to update Form1.xlsx: ' . $e->getMessage());
        }
    }

    public function generateForm7()
    {
        $template = public_path('exel/form7school_level.xlsx');
        if (!file_exists($template)) {
            session()->flash('error', 'Form7.xlsx not found in public/exel');
            Log::error('Form7 write failed - template not found: ' . $template);
            return;
        }

        try {
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($template);
            $sheet = $spreadsheet->getActiveSheet();

            $schoolProfile = SchoolProfile::where('school_id', auth()->user()->school_id)->first();
            $sheet->setCellValueByColumnAndRow(11, 9,  auth()->user()->school?->school_name ?? '');
            $sheet->setCellValueByColumnAndRow(11, 11, $schoolProfile?->school_head_name ?? '');
            $sheet->setCellValueByColumnAndRow(28, 11, $schoolProfile?->school_email ?? '');
            $sheet->setCellValueByColumnAndRow(6,  12, auth()->user()->school?->school_id ?? '');
            $sheet->setCellValueByColumnAndRow(30, 12, NutritionalStatus::count());
            $sheet->setCellValueByColumnAndRow(6,  396, $schoolProfile?->school_focal_name ?? '');
            $sheet->setCellValueByColumnAndRow(26, 396, $schoolProfile?->school_head_name ?? '');
            $sheet->setCellValueByColumnAndRow(13, 398, \Carbon\Carbon::today()->format('F d, Y'));

            $allGrades    = array_merge(['k'], array_map('strval', range(1, 12)), ['non_graded']);
            $subsetGrades = array_merge(['k'], array_map('strval', range(1, 6)), ['non_graded']);

            $zero = (object) [
                'total' => 0, 'total_m' => 0, 'total_f' => 0,
                'severely_wasted_m' => 0, 'severely_wasted_f' => 0,
                'wasted_m' => 0, 'wasted_f' => 0,
                'severely_stunted_m' => 0, 'severely_stunted_f' => 0,
                'stunted_m' => 0, 'stunted_f' => 0,
                'ip_m' => 0, 'ip_f' => 0,
                'fourPs_m' => 0, 'fourPs_f' => 0,
                'pardo_m' => 0, 'pardo_f' => 0,
                'all_normal_m' => 0, 'all_normal_f' => 0,
                'sw_m' => 0, 'sw_f' => 0,
                'normal_m' => 0, 'normal_f' => 0,
                'overweight_m' => 0, 'overweight_f' => 0,
                'obese_m' => 0, 'obese_f' => 0,
                'ss_m' => 0, 'ss_f' => 0,
                'hfa_normal_m' => 0, 'hfa_normal_f' => 0,
                'tall_m' => 0, 'tall_f' => 0,
                'male' => 0, 'female' => 0,
                'dewormed_m' => 0, 'dewormed_f' => 0,
                'fourps_m' => 0, 'fourps_f' => 0,
                'prev_ben_m' => 0, 'prev_ben_f' => 0,
            ];

            // Query 1 — all 14 grades: priority-category breakdown
            $gsRaw = NutritionalStatus::where('isBeneficiary', true)
                ->whereIn('grade', $allGrades)
                ->selectRaw("
                    grade,
                    COUNT(*) as total,
                    SUM(sex='m') as total_m, SUM(sex='f') as total_f,
                    SUM(sex='m' AND nutritional_status='severely wasted') as severely_wasted_m,
                    SUM(sex='f' AND nutritional_status='severely wasted') as severely_wasted_f,
                    SUM(sex='m' AND nutritional_status='wasted') as wasted_m,
                    SUM(sex='f' AND nutritional_status='wasted') as wasted_f,
                    SUM(sex='m' AND height_for_age='severely stunted' AND nutritional_status IN ('normal','overweight','obese')) as severely_stunted_m,
                    SUM(sex='f' AND height_for_age='severely stunted' AND nutritional_status IN ('normal','overweight','obese')) as severely_stunted_f,
                    SUM(sex='m' AND height_for_age='stunted' AND nutritional_status IN ('normal','overweight','obese')) as stunted_m,
                    SUM(sex='f' AND height_for_age='stunted' AND nutritional_status IN ('normal','overweight','obese')) as stunted_f,
                    SUM(sex='m' AND ip=1 AND nutritional_status IN ('normal','overweight','obese') AND height_for_age IN ('normal','tall')) as ip_m,
                    SUM(sex='f' AND ip=1 AND nutritional_status IN ('normal','overweight','obese') AND height_for_age IN ('normal','tall')) as ip_f,
                    SUM(sex='m' AND _4ps=1 AND ip=0 AND nutritional_status IN ('normal','overweight','obese') AND height_for_age IN ('normal','tall')) as fourPs_m,
                    SUM(sex='f' AND _4ps=1 AND ip=0 AND nutritional_status IN ('normal','overweight','obese') AND height_for_age IN ('normal','tall')) as fourPs_f,
                    SUM(sex='m' AND pardo=1 AND _4ps=0 AND ip=0 AND nutritional_status IN ('normal','overweight','obese') AND height_for_age IN ('normal','tall')) as pardo_m,
                    SUM(sex='f' AND pardo=1 AND _4ps=0 AND ip=0 AND nutritional_status IN ('normal','overweight','obese') AND height_for_age IN ('normal','tall')) as pardo_f,
                    SUM(sex='m' AND nutritional_status IN ('normal','overweight','obese') AND height_for_age IN ('normal','tall')) as all_normal_m,
                    SUM(sex='f' AND nutritional_status IN ('normal','overweight','obese') AND height_for_age IN ('normal','tall')) as all_normal_f
                ")
                ->groupBy('grade')->get()->keyBy('grade');

            $gs = [];
            foreach ($allGrades as $g) { $gs[$g] = $gsRaw->get($g) ?? $zero; }

            // Query 2 — subset grades: NS + HFA + counts all in one pass
            $subRaw = NutritionalStatus::where('isBeneficiary', true)
                ->whereIn('grade', $subsetGrades)
                ->selectRaw("
                    grade,
                    SUM(sex='m') as male, SUM(sex='f') as female, COUNT(*) as total,
                    SUM(sex='m' AND nutritional_status='severely wasted') as sw_m,
                    SUM(sex='f' AND nutritional_status='severely wasted') as sw_f,
                    SUM(sex='m' AND nutritional_status='wasted') as wasted_m,
                    SUM(sex='f' AND nutritional_status='wasted') as wasted_f,
                    SUM(sex='m' AND nutritional_status='normal') as normal_m,
                    SUM(sex='f' AND nutritional_status='normal') as normal_f,
                    SUM(sex='m' AND nutritional_status='overweight') as overweight_m,
                    SUM(sex='f' AND nutritional_status='overweight') as overweight_f,
                    SUM(sex='m' AND nutritional_status='obese') as obese_m,
                    SUM(sex='f' AND nutritional_status='obese') as obese_f,
                    SUM(sex='m' AND height_for_age='severely stunted') as ss_m,
                    SUM(sex='f' AND height_for_age='severely stunted') as ss_f,
                    SUM(sex='m' AND height_for_age='stunted') as stunted_m,
                    SUM(sex='f' AND height_for_age='stunted') as stunted_f,
                    SUM(sex='m' AND height_for_age='normal') as hfa_normal_m,
                    SUM(sex='f' AND height_for_age='normal') as hfa_normal_f,
                    SUM(sex='m' AND height_for_age='tall') as tall_m,
                    SUM(sex='f' AND height_for_age='tall') as tall_f,
                    SUM(sex='m' AND dewormed=1) as dewormed_m,
                    SUM(sex='f' AND dewormed=1) as dewormed_f,
                    SUM(sex='m' AND _4ps=1) as fourps_m,
                    SUM(sex='f' AND _4ps=1) as fourps_f,
                    SUM(sex='m' AND sbfp_previous_beneficiary=1) as prev_ben_m,
                    SUM(sex='f' AND sbfp_previous_beneficiary=1) as prev_ben_f
                ")
                ->groupBy('grade')->get()->keyBy('grade');

            $sub = [];
            foreach ($subsetGrades as $g) { $sub[$g] = $subRaw->get($g) ?? $zero; }

            // ── Table 1: SBFP Coverage by Grade ──────────────────────────────
            // Closure writes one copy; called 3 times with different row offsets.
            // Base rows: kinder=18/19, grade1=21/22, grades2-6/NG step by 3.
            // Duplicate 1 starts at row 54  → offset 36
            // Duplicate 2 starts at row 157 → offset 139
            $writeCoverageTable = function (int $offset) use ($sheet, $gs) {
                $k = $gs['k'];
                $sheet->setCellValueByColumnAndRow(10, 18 + $offset, $k->all_normal_m);
                $sheet->setCellValueByColumnAndRow(10, 19 + $offset, $k->all_normal_f);
                $sheet->setCellValueByColumnAndRow(14, 18 + $offset, $k->severely_wasted_m);
                $sheet->setCellValueByColumnAndRow(14, 19 + $offset, $k->severely_wasted_f);
                $sheet->setCellValueByColumnAndRow(18, 18 + $offset, $k->wasted_m);
                $sheet->setCellValueByColumnAndRow(18, 19 + $offset, $k->wasted_f);
                $sheet->setCellValueByColumnAndRow(22, 18 + $offset, $k->severely_stunted_m + $k->stunted_m);
                $sheet->setCellValueByColumnAndRow(22, 19 + $offset, $k->severely_stunted_f + $k->stunted_f);

                $g1 = $gs['1'];
                $sheet->setCellValueByColumnAndRow(10, 21 + $offset, $g1->total_m - ($g1->severely_wasted_m + $g1->wasted_m + $g1->severely_stunted_m + $g1->stunted_m + $g1->pardo_m + $g1->ip_m + $g1->fourPs_m));
                $sheet->setCellValueByColumnAndRow(10, 22 + $offset, $g1->total_f - ($g1->severely_wasted_f + $g1->wasted_f + $g1->severely_stunted_f + $g1->stunted_f + $g1->pardo_f + $g1->ip_f + $g1->fourPs_f));
                $sheet->setCellValueByColumnAndRow(14, 21 + $offset, $g1->severely_wasted_m);
                $sheet->setCellValueByColumnAndRow(14, 22 + $offset, $g1->severely_wasted_f);
                $sheet->setCellValueByColumnAndRow(18, 21 + $offset, $g1->wasted_m);
                $sheet->setCellValueByColumnAndRow(18, 22 + $offset, $g1->wasted_f);
                $sheet->setCellValueByColumnAndRow(22, 21 + $offset, $g1->severely_stunted_m + $g1->stunted_m);
                $sheet->setCellValueByColumnAndRow(22, 22 + $offset, $g1->severely_stunted_f + $g1->stunted_f);
                $sheet->setCellValueByColumnAndRow(26, 21 + $offset, $g1->pardo_m);
                $sheet->setCellValueByColumnAndRow(26, 22 + $offset, $g1->pardo_f);
                $sheet->setCellValueByColumnAndRow(30, 21 + $offset, $g1->ip_m);
                $sheet->setCellValueByColumnAndRow(30, 22 + $offset, $g1->ip_f);
                $sheet->setCellValueByColumnAndRow(34, 21 + $offset, $g1->fourPs_m);
                $sheet->setCellValueByColumnAndRow(34, 22 + $offset, $g1->fourPs_f);

                foreach (['2' => [24,25], '3' => [27,28], '4' => [30,31], '5' => [33,34], '6' => [36,37], 'non_graded' => [39,40]] as $g => [$rm, $rf]) {
                    $s = $gs[$g];
                    $sheet->setCellValueByColumnAndRow(14, $rm + $offset, $s->severely_wasted_m);
                    $sheet->setCellValueByColumnAndRow(14, $rf + $offset, $s->severely_wasted_f);
                    $sheet->setCellValueByColumnAndRow(18, $rm + $offset, $s->wasted_m);
                    $sheet->setCellValueByColumnAndRow(18, $rf + $offset, $s->wasted_f);
                    $sheet->setCellValueByColumnAndRow(22, $rm + $offset, $s->severely_stunted_m + $s->stunted_m);
                    $sheet->setCellValueByColumnAndRow(22, $rf + $offset, $s->severely_stunted_f + $s->stunted_f);
                    $sheet->setCellValueByColumnAndRow(26, $rm + $offset, $s->pardo_m);
                    $sheet->setCellValueByColumnAndRow(26, $rf + $offset, $s->pardo_f);
                    $sheet->setCellValueByColumnAndRow(30, $rm + $offset, $s->ip_m);
                    $sheet->setCellValueByColumnAndRow(30, $rf + $offset, $s->ip_f);
                    $sheet->setCellValueByColumnAndRow(34, $rm + $offset, $s->fourPs_m);
                    $sheet->setCellValueByColumnAndRow(34, $rf + $offset, $s->fourPs_f);
                }
            };

            $writeCoverageTable(0);    // original  — kinder row 18
            $writeCoverageTable(36);   // duplicate 1 — kinder row 54
            $writeCoverageTable(139);  // duplicate 2 — kinder row 157

            // ── NS breakdown (rows 193-215) ──────────────────────────────────
            foreach (['k' => [193,194], '1' => [196,197], '2' => [199,200], '3' => [202,203], '4' => [205,206], '5' => [208,209], '6' => [211,212], 'non_graded' => [214,215]] as $g => [$rm, $rf]) {
                $s = $sub[$g];
                $sheet->setCellValueByColumnAndRow(10, $rm, $s->sw_m);
                $sheet->setCellValueByColumnAndRow(10, $rf, $s->sw_f);
                $sheet->setCellValueByColumnAndRow(17, $rm, $s->wasted_m);
                $sheet->setCellValueByColumnAndRow(17, $rf, $s->wasted_f);
                $sheet->setCellValueByColumnAndRow(24, $rm, $s->normal_m);
                $sheet->setCellValueByColumnAndRow(24, $rf, $s->normal_f);
                $sheet->setCellValueByColumnAndRow(31, $rm, $s->overweight_m);
                $sheet->setCellValueByColumnAndRow(31, $rf, $s->overweight_f);
                $sheet->setCellValueByColumnAndRow(38, $rm, $s->obese_m);
                $sheet->setCellValueByColumnAndRow(38, $rf, $s->obese_f);
            }

            // ── HFA breakdown (rows 229-251) ─────────────────────────────────
            foreach (['k' => [229,230], '1' => [232,233], '2' => [235,236], '3' => [238,239], '4' => [241,242], '5' => [244,245], '6' => [247,248], 'non_graded' => [250,251]] as $g => [$rm, $rf]) {
                $s = $sub[$g];
                $sheet->setCellValueByColumnAndRow(10, $rm, $s->ss_m);
                $sheet->setCellValueByColumnAndRow(10, $rf, $s->ss_f);
                $sheet->setCellValueByColumnAndRow(17, $rm, $s->stunted_m);
                $sheet->setCellValueByColumnAndRow(17, $rf, $s->stunted_f);
                $sheet->setCellValueByColumnAndRow(24, $rm, $s->hfa_normal_m);
                $sheet->setCellValueByColumnAndRow(24, $rf, $s->hfa_normal_f);
                $sheet->setCellValueByColumnAndRow(31, $rm, $s->tall_m);
                $sheet->setCellValueByColumnAndRow(31, $rf, $s->tall_f);
            }

            // ── Beneficiary counts (rows 263-285) ────────────────────────────
            foreach (['k' => [263,264], '1' => [266,267], '2' => [269,270], '3' => [272,273], '4' => [275,276], '5' => [278,279], '6' => [281,282], 'non_graded' => [284,285]] as $g => [$rm, $rf]) {
                $s = $sub[$g];
                $sheet->setCellValueByColumnAndRow(10, $rm, $s->male);
                $sheet->setCellValueByColumnAndRow(10, $rf, $s->female);
                $sheet->setCellValueByColumnAndRow(15, $rm, $s->male);
                $sheet->setCellValueByColumnAndRow(15, $rf, $s->female);
                $sheet->setCellValueByColumnAndRow(22, $rm, $s->dewormed_m);
                $sheet->setCellValueByColumnAndRow(22, $rf, $s->dewormed_f);
                $sheet->setCellValueByColumnAndRow(29, $rm, $s->fourps_m);
                $sheet->setCellValueByColumnAndRow(29, $rf, $s->fourps_f);
                $sheet->setCellValueByColumnAndRow(36, $rm, $s->prev_ben_m);
                $sheet->setCellValueByColumnAndRow(36, $rf, $s->prev_ben_f);
            }

            // ── Attendance table (rows 298-320) ──────────────────────────────
            $attendanceCols = [10, 17, 24, 31, 38, 45, 52, 59, 66, 73];
            foreach (['k' => [298,299], '1' => [301,302], '2' => [304,305], '3' => [307,308], '4' => [310,311], '5' => [313,314], '6' => [316,317], 'non_graded' => [319,320]] as $g => [$rm, $rf]) {
                $male   = $sub[$g]->male;
                $female = $sub[$g]->female;
                foreach ($attendanceCols as $col) {
                    $sheet->setCellValueByColumnAndRow($col, $rm, $male);
                    $sheet->setCellValueByColumnAndRow($col, $rf, $female);
                }
            }

            // ── Completion rate (rows 330-331) ────────────────────────────────
            $maleSum   = array_sum(array_map(fn($g) => $sub[$g]->male,   $subsetGrades));
            $femaleSum = array_sum(array_map(fn($g) => $sub[$g]->female, $subsetGrades));
            $sheet->setCellValueByColumnAndRow(20, 330, $maleSum);
            $sheet->setCellValueByColumnAndRow(23, 330, $femaleSum);
            $sheet->setCellValueByColumnAndRow(20, 331, $maleSum);
            $sheet->setCellValueByColumnAndRow(23, 331, $femaleSum);

            // save spreadsheet to a new temp file so the original template remains unchanged
            $schoolSlug = preg_replace('/[^A-Za-z0-9]+/', '_', auth()->user()->school?->school_name ?? 'Unknown');
            $datetime = \Carbon\Carbon::now()->format('Y-m-d_H-i-s');
            $outFileName = 'Form_7-' . $schoolSlug . '-' . $datetime . '.xlsx';
            $this->ensureDownloadDir();
            $outFile = public_path('downloaded_exel/' . $outFileName);
            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
            $writer->save($outFile);

            session()->flash('success', 'Form7.xlsx generated for download.');

            // dispatch browser event to trigger download of the generated public file
            $downloadUrl = asset('downloaded_exel/' . $outFileName);
            $this->dispatch('form7-ready', $downloadUrl);
            Log::info('Form7 download URL dispatched: ' . $downloadUrl);
        } catch (\Throwable $e) {
            Log::error('Error writing Form7.xlsx: ' . $e->getMessage());
            session()->flash('error', 'Failed to update Form7.xlsx: ' . $e->getMessage());
        }
    }

    public function generateSnsElem()
    {
        $template = public_path('exel/sns_elem.xlsx');
        if (!file_exists($template)) {
            session()->flash('error', 'sns_elem.xlsx not found in public/exel');
            Log::error('sns_elem write failed - template not found: ' . $template);
            return;
        }

        try {
            // load the existing spreadsheet
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($template);
            $sheet = $spreadsheet->getActiveSheet();

            $sheet->setCellValueByColumnAndRow(1, 4, "NUTRITIONAL STATUS REPORT OF " . strtoupper(auth()->user()->school->school_name) ?? '');
            $sheet->setCellValueByColumnAndRow(1, 5, "Baseline SY 2025 - 2026");

            $schoolProfile = SchoolProfile::where('school_id', auth()->user()->school_id)->first();
            $sheet->setCellValueByColumnAndRow(2, 40, $schoolProfile?->school_focal_name ?? '');
            $sheet->setCellValueByColumnAndRow(16, 40, $schoolProfile?->school_head_name ?? '');

            // fetch records from database
            $kinderStats = NutritionalStatus::where('grade', 'k')
                ->selectRaw('
                    COUNT(*) as total,
                    SUM(sex = "m") as kinder_male,

                    SUM(sex = "m" AND nutritional_status = "severely wasted") as kinder_sw_male,
                    SUM(sex = "m" AND nutritional_status = "wasted") as kinder_wasted_male,
                    SUM(sex = "m" AND nutritional_status = "normal") as kinder_weight_normal_male,
                    SUM(sex = "m" AND nutritional_status = "overweight") as kinder_overweight_male,
                    SUM(sex = "m" AND nutritional_status = "obese") as kinder_obese_male,

                    SUM(sex = "m" AND height_for_age = "severely stunted") as kinder_ss_male,
                    SUM(sex = "m" AND height_for_age = "stunted") as kinder_stunted_male,
                    SUM(sex = "m" AND height_for_age = "normal") as kinder_height_normal_male,
                    SUM(sex = "m" AND height_for_age = "tall") as kinder_tall_male,

                    SUM(sex = "f") as kinder_female,

                    SUM(sex = "f" AND nutritional_status = "severely wasted") as kinder_sw_female,
                    SUM(sex = "f" AND nutritional_status = "wasted") as kinder_wasted_female,
                    SUM(sex = "f" AND nutritional_status = "normal") as kinder_weight_normal_female,
                    SUM(sex = "f" AND nutritional_status = "overweight") as kinder_overweight_female,
                    SUM(sex = "f" AND nutritional_status = "obese") as kinder_obese_female,

                    SUM(sex = "f" AND height_for_age = "severely stunted") as kinder_ss_female,
                    SUM(sex = "f" AND height_for_age = "stunted") as kinder_stunted_female,
                    SUM(sex = "f" AND height_for_age = "normal") as kinder_hfa_normal_female,
                    SUM(sex = "f" AND height_for_age = "tall") as kinder_tall_female
                ')
                ->first();

            $sheet->setCellValueByColumnAndRow(3, 10, $kinderStats->kinder_male ?? 0);
            $sheet->setCellValueByColumnAndRow(6, 10, $kinderStats->kinder_sw_male ?? 0);
            $sheet->setCellValueByColumnAndRow(8, 10, $kinderStats->kinder_wasted_male ?? 0);
            $sheet->setCellValueByColumnAndRow(10, 10, $kinderStats->kinder_weight_normal_male ?? 0);
            $sheet->setCellValueByColumnAndRow(12, 10, $kinderStats->kinder_overweight_male ?? 0);
            $sheet->setCellValueByColumnAndRow(14, 10, $kinderStats->kinder_obese_male ?? 0);
            $sheet->setCellValueByColumnAndRow(16, 10, $kinderStats->kinder_ss_male ?? 0);
            $sheet->setCellValueByColumnAndRow(18, 10, $kinderStats->kinder_stunted_male ?? 0);
            $sheet->setCellValueByColumnAndRow(20, 10, $kinderStats->kinder_height_normal_male ?? 0);
            $sheet->setCellValueByColumnAndRow(22, 10, $kinderStats->kinder_tall_male ?? 0);

            $sheet->setCellValueByColumnAndRow(3, 11, $kinderStats->kinder_female ?? 0);
            $sheet->setCellValueByColumnAndRow(6, 11, $kinderStats->kinder_sw_female ?? 0);
            $sheet->setCellValueByColumnAndRow(8, 11, $kinderStats->kinder_wasted_female ?? 0);
            $sheet->setCellValueByColumnAndRow(10, 11, $kinderStats->kinder_weight_normal_female ?? 0);
            $sheet->setCellValueByColumnAndRow(12, 11, $kinderStats->kinder_overweight_female ?? 0);
            $sheet->setCellValueByColumnAndRow(14, 11, $kinderStats->kinder_obese_female ?? 0);
            $sheet->setCellValueByColumnAndRow(16, 11, $kinderStats->kinder_ss_female ?? 0);
            $sheet->setCellValueByColumnAndRow(18, 11, $kinderStats->kinder_stunted_female ?? 0);
            $sheet->setCellValueByColumnAndRow(20, 11, $kinderStats->kinder_hfa_normal_female ?? 0);
            $sheet->setCellValueByColumnAndRow(22, 11, $kinderStats->kinder_tall_female ?? 0);


            $nonGradedStats = NutritionalStatus::where('grade', 'non_graded')
                ->selectRaw('
                    COUNT(*) as total,
                    SUM(sex = "m") as non_graded_male,

                    SUM(sex = "m" AND nutritional_status = "severely wasted") as non_graded_sw_male,
                    SUM(sex = "m" AND nutritional_status = "wasted") as non_graded_wasted_male,
                    SUM(sex = "m" AND nutritional_status = "normal") as non_graded_weight_normal_male,
                    SUM(sex = "m" AND nutritional_status = "overweight") as non_graded_overweight_male,
                    SUM(sex = "m" AND nutritional_status = "obese") as non_graded_obese_male,

                    SUM(sex = "m" AND height_for_age = "severely stunted") as non_graded_ss_male,
                    SUM(sex = "m" AND height_for_age = "stunted") as non_graded_stunted_male,
                    SUM(sex = "m" AND height_for_age = "normal") as non_graded_height_normal_male,
                    SUM(sex = "m" AND height_for_age = "tall") as non_graded_tall_male,

                    SUM(sex = "f") as non_graded_female,

                    SUM(sex = "f" AND nutritional_status = "severely wasted") as non_graded_sw_female,
                    SUM(sex = "f" AND nutritional_status = "wasted") as non_graded_wasted_female,
                    SUM(sex = "f" AND nutritional_status = "normal") as non_graded_weight_normal_female,
                    SUM(sex = "f" AND nutritional_status = "overweight") as non_graded_overweight_female,
                    SUM(sex = "f" AND nutritional_status = "obese") as non_graded_obese_female,

                    SUM(sex = "f" AND height_for_age = "severely stunted") as non_graded_ss_female,
                    SUM(sex = "f" AND height_for_age = "stunted") as non_graded_stunted_female,
                    SUM(sex = "f" AND height_for_age = "normal") as non_graded_hfa_normal_female,
                    SUM(sex = "f" AND height_for_age = "tall") as non_graded_tall_female
                ')
                ->first();

            $sheet->setCellValueByColumnAndRow(3, 31, $nonGradedStats->non_graded_male ?? 0);
            $sheet->setCellValueByColumnAndRow(6, 31, $nonGradedStats->non_graded_sw_male ?? 0);
            $sheet->setCellValueByColumnAndRow(8, 31, $nonGradedStats->non_graded_wasted_male ?? 0);
            $sheet->setCellValueByColumnAndRow(10, 31, $nonGradedStats->non_graded_weight_normal_male ?? 0);
            $sheet->setCellValueByColumnAndRow(12, 31, $nonGradedStats->non_graded_overweight_male ?? 0);
            $sheet->setCellValueByColumnAndRow(14, 31, $nonGradedStats->non_graded_obese_male ?? 0);
            $sheet->setCellValueByColumnAndRow(16, 31, $nonGradedStats->non_graded_ss_male ?? 0);
            $sheet->setCellValueByColumnAndRow(18, 31, $nonGradedStats->non_graded_stunted_male ?? 0);
            $sheet->setCellValueByColumnAndRow(20, 31, $nonGradedStats->non_graded_height_normal_male ?? 0);
            $sheet->setCellValueByColumnAndRow(22, 31, $nonGradedStats->non_graded_tall_male ?? 0);
            $sheet->setCellValueByColumnAndRow(3, 32, $nonGradedStats->non_graded_female ?? 0);
            $sheet->setCellValueByColumnAndRow(6, 32, $nonGradedStats->non_graded_sw_female ?? 0);
            $sheet->setCellValueByColumnAndRow(8, 32, $nonGradedStats->non_graded_wasted_female ?? 0);
            $sheet->setCellValueByColumnAndRow(10, 32, $nonGradedStats->non_graded_weight_normal_female ?? 0);
            $sheet->setCellValueByColumnAndRow(12, 32, $nonGradedStats->non_graded_overweight_female ?? 0);
            $sheet->setCellValueByColumnAndRow(14, 32, $nonGradedStats->non_graded_obese_female ?? 0);
            $sheet->setCellValueByColumnAndRow(16, 32, $nonGradedStats->non_graded_ss_female ?? 0);
            $sheet->setCellValueByColumnAndRow(18, 32, $nonGradedStats->non_graded_stunted_female ?? 0);
            $sheet->setCellValueByColumnAndRow(20, 32, $nonGradedStats->non_graded_hfa_normal_female ?? 0);
            $sheet->setCellValueByColumnAndRow(22, 32, $nonGradedStats->non_graded_tall_female ?? 0);








            $gradeStats = [];
            // starting row in the template
            $startRow = 13;
            $endRow = 0;

            for ($grade = 1; $grade <= 6; $grade++) {

                $gradeStats[$grade] = NutritionalStatus::where('grade', (string) $grade)
                    ->selectRaw('
                        COUNT(*) as total,

                        SUM(sex = "m") as male,
                        SUM(sex = "m" AND nutritional_status = "severely wasted") as sw_male,
                        SUM(sex = "m" AND nutritional_status = "wasted") as wasted_male,
                        SUM(sex = "m" AND nutritional_status = "normal") as weight_normal_male,
                        SUM(sex = "m" AND nutritional_status = "overweight") as overweight_male,
                        SUM(sex = "m" AND nutritional_status = "obese") as obese_male,

                        SUM(sex = "m" AND height_for_age = "severely stunted") as ss_male,
                        SUM(sex = "m" AND height_for_age = "stunted") as stunted_male,
                        SUM(sex = "m" AND height_for_age = "normal") as hfa_normal_male,
                        SUM(sex = "m" AND height_for_age = "tall") as tall_male,

                        SUM(sex = "f") as female,
                        SUM(sex = "f" AND nutritional_status = "severely wasted") as sw_female,
                        SUM(sex = "f" AND nutritional_status = "wasted") as wasted_female,
                        SUM(sex = "f" AND nutritional_status = "normal") as weight_normal_female,
                        SUM(sex = "f" AND nutritional_status = "overweight") as overweight_female,
                        SUM(sex = "f" AND nutritional_status = "obese") as obese_female,

                        SUM(sex = "f" AND height_for_age = "severely stunted") as ss_female,
                        SUM(sex = "f" AND height_for_age = "stunted") as stunted_female,
                        SUM(sex = "f" AND height_for_age = "normal") as hfa_normal_female,
                        SUM(sex = "f" AND height_for_age = "tall") as tall_female
                    ')
                    ->first();
            }

            foreach ($gradeStats as $gradeStat) {
                $sheet->setCellValueByColumnAndRow(3, $startRow, $gradeStat->male ?? 0);
                $sheet->setCellValueByColumnAndRow(6, $startRow, $gradeStat->sw_male ?? 0);
                $sheet->setCellValueByColumnAndRow(8, $startRow, $gradeStat->wasted_male ?? 0);
                $sheet->setCellValueByColumnAndRow(10, $startRow, $gradeStat->weight_normal_male ?? 0);
                $sheet->setCellValueByColumnAndRow(12, $startRow, $gradeStat->overweight_male ?? 0);
                $sheet->setCellValueByColumnAndRow(14, $startRow, $gradeStat->obese_male ?? 0);
                $sheet->setCellValueByColumnAndRow(16, $startRow, $gradeStat->ss_male ?? 0);
                $sheet->setCellValueByColumnAndRow(18, $startRow, $gradeStat->stunted_male ?? 0);
                $sheet->setCellValueByColumnAndRow(20, $startRow, $gradeStat->hfa_normal_male ?? 0);
                $sheet->setCellValueByColumnAndRow(22, $startRow, $gradeStat->tall_male ?? 0);

                $startRow++;

                $sheet->setCellValueByColumnAndRow(3, ($startRow), $gradeStat->female ?? 0);
                $sheet->setCellValueByColumnAndRow(6, ($startRow), $gradeStat->sw_female ?? 0);
                $sheet->setCellValueByColumnAndRow(8, ($startRow), $gradeStat->wasted_female ?? 0);
                $sheet->setCellValueByColumnAndRow(10, ($startRow), $gradeStat->weight_normal_female ?? 0);
                $sheet->setCellValueByColumnAndRow(12, ($startRow), $gradeStat->overweight_female ?? 0);
                $sheet->setCellValueByColumnAndRow(14, ($startRow), $gradeStat->obese_female ?? 0);
                $sheet->setCellValueByColumnAndRow(16, ($startRow), $gradeStat->ss_female ?? 0);
                $sheet->setCellValueByColumnAndRow(18, ($startRow), $gradeStat->stunted_female ?? 0);
                $sheet->setCellValueByColumnAndRow(20, ($startRow), $gradeStat->hfa_normal_female ?? 0);
                $sheet->setCellValueByColumnAndRow(22, ($startRow), $gradeStat->tall_female ?? 0);

                $startRow = $startRow + 2;
                $endRow = $startRow + 1;
            }

            // save spreadsheet to a new temp file so the original template remains unchanged
            $schoolSlug = preg_replace('/[^A-Za-z0-9]+/', '_', auth()->user()->school?->school_name ?? 'Unknown');
            $datetime = \Carbon\Carbon::now()->format('Y-m-d_H-i-s');
            $outFileName = 'SNS_Elementary-' . $schoolSlug . '-' . $datetime . '.xlsx';
            $this->ensureDownloadDir();
            $outFile = public_path('downloaded_exel/' . $outFileName);
            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
            $writer->save($outFile);

            session()->flash('success', 'SNS_Elem.xlsx generated for download.');

            // dispatch browser event to trigger download of the generated public file
            $downloadUrl = asset('downloaded_exel/' . $outFileName);
            // dispatch SNS Elementary specific event
            $this->dispatch('sns-elem-ready', $downloadUrl);
        } catch (\Throwable $e) {
            Log::error('Error writing SNS_Elem.xlsx: ' . $e->getMessage());
            session()->flash('error', 'Failed to update SNS_Elem.xlsx: ' . $e->getMessage());
        }
    }

    public function generateSnsHighSchool()
    {
        $template = public_path('exel/sns_highschool.xlsx');
        if (!file_exists($template)) {
            session()->flash('error', 'sns_highschool.xlsx not found in public/exel');
            Log::error('sns_highschool write failed - template not found: ' . $template);
            return;
        }

        try {
            // load the existing spreadsheet
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($template);
            $sheet = $spreadsheet->getActiveSheet();

            $sheet->setCellValueByColumnAndRow(1, 4, "NUTRITIONAL STATUS REPORT OF " . strtoupper(auth()->user()->school->school_name) ?? '');
            $sheet->setCellValueByColumnAndRow(1, 5, "Baseline SY 2025 - 2026");

            $schoolProfile = SchoolProfile::where('school_id', auth()->user()->school_id)->first();
            $sheet->setCellValueByColumnAndRow(2, 34, $schoolProfile?->school_focal_name ?? '');
            $sheet->setCellValueByColumnAndRow(16, 34, $schoolProfile?->school_head_name ?? '');

            $gradeStats = [];
            // starting row in the template
            $startRow = 10;
            $endRow = 0;

            for ($grade = 7; $grade <= 12; $grade++) {

                $gradeStats[$grade] = NutritionalStatus::where('grade', (string) $grade)
                    ->selectRaw('
                        COUNT(*) as total,

                        SUM(sex = "m") as male,
                        SUM(sex = "m" AND nutritional_status = "severely wasted") as sw_male,
                        SUM(sex = "m" AND nutritional_status = "wasted") as wasted_male,
                        SUM(sex = "m" AND nutritional_status = "normal") as weight_normal_male,
                        SUM(sex = "m" AND nutritional_status = "overweight") as overweight_male,
                        SUM(sex = "m" AND nutritional_status = "obese") as obese_male,

                        SUM(sex = "m" AND height_for_age = "severely stunted") as ss_male,
                        SUM(sex = "m" AND height_for_age = "stunted") as stunted_male,
                        SUM(sex = "m" AND height_for_age = "normal") as hfa_normal_male,
                        SUM(sex = "m" AND height_for_age = "tall") as tall_male,

                        SUM(sex = "f") as female,
                        SUM(sex = "f" AND nutritional_status = "severely wasted") as sw_female,
                        SUM(sex = "f" AND nutritional_status = "wasted") as wasted_female,
                        SUM(sex = "f" AND nutritional_status = "normal") as weight_normal_female,
                        SUM(sex = "f" AND nutritional_status = "overweight") as overweight_female,
                        SUM(sex = "f" AND nutritional_status = "obese") as obese_female,

                        SUM(sex = "f" AND height_for_age = "severely stunted") as ss_female,
                        SUM(sex = "f" AND height_for_age = "stunted") as stunted_female,
                        SUM(sex = "f" AND height_for_age = "normal") as hfa_normal_female,
                        SUM(sex = "f" AND height_for_age = "tall") as tall_female
                    ')
                    ->first();
            }

            foreach ($gradeStats as $gradeStat) {
                $sheet->setCellValueByColumnAndRow(3, $startRow, $gradeStat->male ?? 0);
                $sheet->setCellValueByColumnAndRow(6, $startRow, $gradeStat->sw_male ?? 0);
                $sheet->setCellValueByColumnAndRow(8, $startRow, $gradeStat->wasted_male ?? 0);
                $sheet->setCellValueByColumnAndRow(10, $startRow, $gradeStat->weight_normal_male ?? 0);
                $sheet->setCellValueByColumnAndRow(12, $startRow, $gradeStat->overweight_male ?? 0);
                $sheet->setCellValueByColumnAndRow(14, $startRow, $gradeStat->obese_male ?? 0);
                $sheet->setCellValueByColumnAndRow(16, $startRow, $gradeStat->ss_male ?? 0);
                $sheet->setCellValueByColumnAndRow(18, $startRow, $gradeStat->stunted_male ?? 0);
                $sheet->setCellValueByColumnAndRow(20, $startRow, $gradeStat->hfa_normal_male ?? 0);
                $sheet->setCellValueByColumnAndRow(22, $startRow, $gradeStat->tall_male ?? 0);

                $startRow++;

                $sheet->setCellValueByColumnAndRow(3, ($startRow), $gradeStat->female ?? 0);
                $sheet->setCellValueByColumnAndRow(6, ($startRow), $gradeStat->sw_female ?? 0);
                $sheet->setCellValueByColumnAndRow(8, ($startRow), $gradeStat->wasted_female ?? 0);
                $sheet->setCellValueByColumnAndRow(10, ($startRow), $gradeStat->weight_normal_female ?? 0);
                $sheet->setCellValueByColumnAndRow(12, ($startRow), $gradeStat->overweight_female ?? 0);
                $sheet->setCellValueByColumnAndRow(14, ($startRow), $gradeStat->obese_female ?? 0);
                $sheet->setCellValueByColumnAndRow(16, ($startRow), $gradeStat->ss_female ?? 0);
                $sheet->setCellValueByColumnAndRow(18, ($startRow), $gradeStat->stunted_female ?? 0);
                $sheet->setCellValueByColumnAndRow(20, ($startRow), $gradeStat->hfa_normal_female ?? 0);
                $sheet->setCellValueByColumnAndRow(22, ($startRow), $gradeStat->tall_female ?? 0);

                $startRow = $startRow + 2;
                $endRow = $startRow + 1;
            }

            // save spreadsheet to a new temp file so the original template remains unchanged
            $schoolSlug = preg_replace('/[^A-Za-z0-9]+/', '_', auth()->user()->school?->school_name ?? 'Unknown');
            $datetime = \Carbon\Carbon::now()->format('Y-m-d_H-i-s');
            $outFileName = 'SNS_High_School-' . $schoolSlug . '-' . $datetime . '.xlsx';
            $this->ensureDownloadDir();
            $outFile = public_path('downloaded_exel/' . $outFileName);
            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
            $writer->save($outFile);

            session()->flash('success', 'SNS_HighSchool.xlsx generated for download.');

            // dispatch browser event to trigger download of the generated public file
            $downloadUrl = asset('downloaded_exel/' . $outFileName);
            // dispatch SNS High School specific event
            $this->dispatch('sns-highschool-ready', $downloadUrl);
        } catch (\Throwable $e) {
            Log::error('Error writing SNS_HighSchool.xlsx: ' . $e->getMessage());
            session()->flash('error', 'Failed to update SNS_HighSchool.xlsx: ' . $e->getMessage());
        }
    }

    public function generateJson()
    {
        try {
            $records = NutritionalStatus::orderBy('id')->get();
            $json = $records->toJson(JSON_PRETTY_PRINT);

            $outFileName = 'nutritional_statuses_' . time() . '.json';
            $this->ensureDownloadDir();
            $outFile = public_path('downloaded_exel/' . $outFileName);

            if (file_put_contents($outFile, $json) === false) {
                throw new \Exception('Failed to write JSON file');
            }

            session()->flash('success', 'nutritional_statuses JSON generated for download.');

            $downloadUrl = asset('downloaded_exel/' . $outFileName);
            $this->dispatch('json-ready', $downloadUrl);
        } catch (\Throwable $e) {
            Log::error('Error writing nutritional_statuses JSON: ' . $e->getMessage());
            session()->flash('error', 'Failed to export JSON: ' . $e->getMessage());
        }
    }

    public function importJson()
    {
        try {
            $this->validate([
                'uploadJson' => 'required|file|mimes:json,txt',
            ]);

            $path = $this->uploadJson->getRealPath();
            $content = file_get_contents($path);
            $decoded = json_decode($content, true);

            if (!is_array($decoded)) {
                throw new \Exception('Invalid JSON structure: expected an array of records');
            }

            $model = new NutritionalStatus();
            $fillable = $model->getFillable();
            $count = 0;

            foreach ($decoded as $item) {
                if (!is_array($item)) continue;

                $data = array_intersect_key($item, array_flip($fillable));

                if (isset($item['id']) && $item['id']) {
                    NutritionalStatus::updateOrCreate(['id' => $item['id']], $data);
                } else {
                    NutritionalStatus::create($data);
                }

                $count++;
            }

            $this->reset('uploadJson');
            session()->flash('success', "Imported {$count} nutritional_status records.");
            $this->dispatch('json-imported', ['count' => $count]);
        } catch (\Throwable $e) {
            Log::error('Error importing nutritional_statuses JSON: ' . $e->getMessage());
            session()->flash('error', 'Failed to import JSON: ' . $e->getMessage());
        }
    }

    public function generateForm2()
    {
        $template = public_path('exel/Form2.xlsx');
        if (!file_exists($template)) {
            session()->flash('error', 'Form2.xlsx not found in public/exel');
            Log::error('Form2 write failed - template not found: ' . $template);
            return;
        }

        try {
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($template);
            $sheet = $spreadsheet->getActiveSheet();

            $schoolProfile = SchoolProfile::where('school_id', auth()->user()->school_id)->first();

            // ── Header fields ────────────────────────────────────────────────
            $school = auth()->user()->school;
            $districtNum = $school?->district;

            // Row 4  – school name in the pre-title header area
            $sheet->setCellValue('B4', $school?->school_name ?? '');

            // Rows 6-9 – form body header fields
            $sheet->setCellValue('B6', 'Malaybalay City Division');           // Schools Division Office
            $sheet->setCellValue('B7', 'Malaybalay City, Bukidnon');          // City/Municipality/Barangay
            $schoolNameWithDistrict = trim(($school?->school_name ?? '') . ($districtNum ? ' / ' . $districtNum : ''));
            $sheet->setCellValue('D8', $schoolNameWithDistrict);              // Name of School / School District
            $sheet->setCellValue('D9', $school?->school_id ?? '');            // School ID Number
            // B10 (Date of Start of Feeding) – not stored in DB, left blank
            // B11 (Last Mile School Y/N)     – not stored in DB, left blank

            // ── Data query: aggregate per grade ──────────────────────────────
            $grades = ['k', '1', '2', '3', '4', '5', '6'];

            // ── Determine primary-beneficiary conditions from the settings table ─
            // A secondary beneficiary is isBeneficiary=true AND NOT a primary.
            // Columns J (pardo), K (stunted/SS), L (indigent=4Ps), M (IP)
            // must only count SECONDARY beneficiaries.
            $primaryConfig = PrimarySecondaryBeneficiaries::where('name', 'Primary')->first();
            $primaryClauses = [];
            if ($primaryConfig?->all_kinder)       $primaryClauses[] = "grade = 'k'";
            if ($primaryConfig?->all_grade_1)      $primaryClauses[] = "grade = '1'";
            if ($primaryConfig?->all_grade_2)      $primaryClauses[] = "grade = '2'";
            if ($primaryConfig?->all_grade_3)      $primaryClauses[] = "grade = '3'";
            if ($primaryConfig?->severely_wasted)  $primaryClauses[] = "nutritional_status = 'severely wasted'";
            if ($primaryConfig?->wasted)           $primaryClauses[] = "nutritional_status = 'wasted'";
            if ($primaryConfig?->normal_weight)    $primaryClauses[] = "nutritional_status = 'normal'";
            if ($primaryConfig?->overweight_obese) $primaryClauses[] = "nutritional_status IN ('overweight','obese')";
            if ($primaryConfig?->severely_stunted) $primaryClauses[] = "height_for_age = 'severely stunted'";
            if ($primaryConfig?->stunted)          $primaryClauses[] = "height_for_age = 'stunted'";
            if ($primaryConfig?->normal_height)    $primaryClauses[] = "height_for_age = 'normal'";
            if ($primaryConfig?->tall)             $primaryClauses[] = "height_for_age = 'tall'";
            if ($primaryConfig?->_4ps)             $primaryClauses[] = "_4ps = 1";
            if ($primaryConfig?->ip)               $primaryClauses[] = "ip = 1";
            if ($primaryConfig?->pardo)            $primaryClauses[] = "pardo = 1";

            // SQL fragment that is TRUE when a row is NOT a primary beneficiary
            $notPrimary = empty($primaryClauses)
                ? '1'
                : 'NOT (' . implode(' OR ', $primaryClauses) . ')';

            $zero = (object) [
                'sw' => 0, 'wasted' => 0, 'normal_weight' => 0, 'overweight_obese' => 0,
                'severely_stunted' => 0, 'stunted' => 0, 'normal_height' => 0, 'tall' => 0,
                'pardo_cnt' => 0, 'stunted_ss' => 0, 'ip_cnt' => 0, 'dewormed_cnt' => 0,
                'fourps_sec' => 0, 'fourps_all' => 0, 'prev_ben' => 0,
            ];

            // Aliases for pardo, ip, dewormed use a _cnt suffix to avoid
            // colliding with the model's boolean casts on those column names,
            // which would turn the SUM result into TRUE/FALSE.
            // Columns J, K, L, M filter by $notPrimary (secondary beneficiaries only).
            // fourps_sec (col L) = secondary 4Ps; fourps_all (col O) = all 4Ps beneficiaries.
            $statsRaw = NutritionalStatus::where('isBeneficiary', true)
                ->whereIn('grade', $grades)
                ->selectRaw("
                    grade,
                    SUM(nutritional_status = 'severely wasted')                               AS sw,
                    SUM(nutritional_status = 'wasted')                                        AS wasted,
                    SUM(nutritional_status = 'normal')                                        AS normal_weight,
                    SUM(nutritional_status IN ('overweight','obese'))                          AS overweight_obese,
                    SUM(height_for_age = 'severely stunted')                                  AS severely_stunted,
                    SUM(height_for_age = 'stunted')                                           AS stunted,
                    SUM(height_for_age = 'normal')                                            AS normal_height,
                    SUM(height_for_age = 'tall')                                              AS tall,
                    SUM(pardo = 1 AND ({$notPrimary}))                                        AS pardo_cnt,
                    SUM(height_for_age IN ('stunted','severely stunted') AND ({$notPrimary})) AS stunted_ss,
                    SUM(ip = 1 AND ({$notPrimary}))                                           AS ip_cnt,
                    SUM(dewormed = 1)                                                         AS dewormed_cnt,
                    SUM(_4ps = 1 AND ({$notPrimary}))                                         AS fourps_sec,
                    SUM(_4ps = 1)                                                             AS fourps_all,
                    SUM(sbfp_previous_beneficiary = 1)                                        AS prev_ben
                ")
                ->groupBy('grade')
                ->get()
                ->keyBy('grade');

            // ── Row map: grade => Excel row ──────────────────────────────────
            // B=2 Severely Wasted | C=3 Wasted | D=4 Normal | E=5 Overweight+Obese
            // F=6 Severely Stunted | G=7 Stunted | H=8 Normal(HFA) | I=9 Tall
            // J=10 PARDO | K=11 Stunted/SS | L=12 Indigent | M=13 IP
            // N=14 Dewormed | O=15 4Ps | P=16 Prev beneficiary
            $rowMap = ['k' => 14, '1' => 15, '2' => 16, '3' => 17, '4' => 18, '5' => 19, '6' => 20];

            $totals = array_fill_keys(
                ['sw','wasted','normal_weight','overweight_obese','severely_stunted',
                 'stunted','normal_height','tall','pardo_cnt','stunted_ss','ip_cnt',
                 'dewormed_cnt','fourps_sec','fourps_all','prev_ben'],
                0
            );

            foreach ($rowMap as $grade => $row) {
                $s = $statsRaw->get($grade) ?? $zero;

                $sheet->setCellValueByColumnAndRow(2,  $row, $s->sw);
                $sheet->setCellValueByColumnAndRow(3,  $row, $s->wasted);
                $sheet->setCellValueByColumnAndRow(4,  $row, $s->normal_weight);
                $sheet->setCellValueByColumnAndRow(5,  $row, $s->overweight_obese);
                $sheet->setCellValueByColumnAndRow(6,  $row, $s->severely_stunted);
                $sheet->setCellValueByColumnAndRow(7,  $row, $s->stunted);
                $sheet->setCellValueByColumnAndRow(8,  $row, $s->normal_height);
                $sheet->setCellValueByColumnAndRow(9,  $row, $s->tall);
                $sheet->setCellValueByColumnAndRow(10, $row, $s->pardo_cnt);        // J: secondary PARDO
                $sheet->setCellValueByColumnAndRow(11, $row, $s->stunted_ss);       // K: secondary Stunted/SS
                $sheet->setCellValueByColumnAndRow(12, $row, $s->fourps_sec);       // L: secondary Indigent (4Ps)
                $sheet->setCellValueByColumnAndRow(13, $row, $s->ip_cnt);           // M: secondary IP
                $sheet->setCellValueByColumnAndRow(14, $row, $s->dewormed_cnt);     // N: all dewormed
                $sheet->setCellValueByColumnAndRow(15, $row, $s->fourps_all);       // O: all 4Ps beneficiaries
                $sheet->setCellValueByColumnAndRow(16, $row, $s->prev_ben);         // P: prev year beneficiaries

                foreach (array_keys($totals) as $key) {
                    $totals[$key] += (int) ($s->$key ?? 0);
                }
            }

            // ── Totals row (row 21) ──────────────────────────────────────────
            $sheet->setCellValueByColumnAndRow(2,  21, $totals['sw']);
            $sheet->setCellValueByColumnAndRow(3,  21, $totals['wasted']);
            $sheet->setCellValueByColumnAndRow(4,  21, $totals['normal_weight']);
            $sheet->setCellValueByColumnAndRow(5,  21, $totals['overweight_obese']);
            $sheet->setCellValueByColumnAndRow(6,  21, $totals['severely_stunted']);
            $sheet->setCellValueByColumnAndRow(7,  21, $totals['stunted']);
            $sheet->setCellValueByColumnAndRow(8,  21, $totals['normal_height']);
            $sheet->setCellValueByColumnAndRow(9,  21, $totals['tall']);
            $sheet->setCellValueByColumnAndRow(10, 21, $totals['pardo_cnt']);        // J: secondary PARDO
            $sheet->setCellValueByColumnAndRow(11, 21, $totals['stunted_ss']);       // K: secondary Stunted/SS
            $sheet->setCellValueByColumnAndRow(12, 21, $totals['fourps_sec']);       // L: secondary Indigent (4Ps)
            $sheet->setCellValueByColumnAndRow(13, 21, $totals['ip_cnt']);           // M: secondary IP
            $sheet->setCellValueByColumnAndRow(14, 21, $totals['dewormed_cnt']);     // N: all dewormed
            $sheet->setCellValueByColumnAndRow(15, 21, $totals['fourps_all']);       // O: all 4Ps beneficiaries
            $sheet->setCellValueByColumnAndRow(16, 21, $totals['prev_ben']);         // P: prev year beneficiaries

            // ── Footer signatures (row 22) ───────────────────────────────────
            $sheet->setCellValueByColumnAndRow(2,  22, $schoolProfile?->school_focal_name ?? '');  // B22 - SBFP Focal
            $sheet->setCellValueByColumnAndRow(12, 22, $schoolProfile?->school_head_name ?? '');  // L22 - School Head

            $schoolSlug = preg_replace('/[^A-Za-z0-9]+/', '_', auth()->user()->school?->school_name ?? 'Unknown');
            $datetime = \Carbon\Carbon::now()->format('Y-m-d_H-i-s');
            $outFileName = 'Form_2-' . $schoolSlug . '-' . $datetime . '.xlsx';
            $this->ensureDownloadDir();
            $outFile = public_path('downloaded_exel/' . $outFileName);
            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
            $writer->save($outFile);

            session()->flash('success', 'Form2.xlsx generated for download.');
            Log::info($outFileName . ' successfully written with ' . array_sum($totals) . ' data points.');

            $downloadUrl = asset('downloaded_exel/' . $outFileName);
            $this->dispatch('form2-ready', $downloadUrl);
        } catch (\Throwable $e) {
            Log::error('Error writing Form2.xlsx: ' . $e->getMessage());
            session()->flash('error', 'Failed to generate Form2.xlsx: ' . $e->getMessage());
        }
    }

    public function generateForm2New()
    {
        $template = public_path('exel/Form2New.xlsx');
        if (!file_exists($template)) {
            session()->flash('error', 'Form2New.xlsx not found in public/exel');
            Log::error('Form 2 New write failed - template not found: ' . $template);
            return;
        }

        try {
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($template);
            $sheet = $spreadsheet->getActiveSheet();
            $school = auth()->user()->school;
            $schoolProfile = SchoolProfile::where('school_id', auth()->user()->school_id)->first();
            $districtNum = $school?->district;
            $schoolNameWithDistrict = trim(($school?->school_name ?? '') . ($districtNum ? ' / ' . $districtNum : ''));

            // The new template stores each header line in a merged cell beginning in column A.
            $sheet->setCellValue('A6', 'Schools Division Office: Malaybalay City Division');
            $sheet->setCellValue('A7', 'City/ Municipality/Barangay : Malaybalay City, Bukidnon');
            $sheet->setCellValue('A8', 'Name of School / School District : ' . $schoolNameWithDistrict);
            $sheet->setCellValue('A9', 'School ID Number: ' . ($school?->school_id ?? ''));
            $sheet->setCellValue('A10', 'Date of Start of Feeding: ');
            $sheet->setCellValue('A11', 'Last Mile School:  ___Y   ___N');

            $grades = ['k', '1', '2', '3', '4', '5', '6', 'non_graded'];
            $primaryConfig = PrimarySecondaryBeneficiaries::where('name', 'Primary')->first();
            $primaryClauses = [];

            if ($primaryConfig?->all_kinder)       $primaryClauses[] = "grade = 'k'";
            if ($primaryConfig?->all_grade_1)      $primaryClauses[] = "grade = '1'";
            if ($primaryConfig?->all_grade_2)      $primaryClauses[] = "grade = '2'";
            if ($primaryConfig?->all_grade_3)      $primaryClauses[] = "grade = '3'";
            if ($primaryConfig?->severely_wasted)  $primaryClauses[] = "nutritional_status = 'severely wasted'";
            if ($primaryConfig?->wasted)           $primaryClauses[] = "nutritional_status = 'wasted'";
            if ($primaryConfig?->normal_weight)    $primaryClauses[] = "nutritional_status = 'normal'";
            if ($primaryConfig?->overweight_obese) $primaryClauses[] = "nutritional_status IN ('overweight','obese')";
            if ($primaryConfig?->severely_stunted) $primaryClauses[] = "height_for_age = 'severely stunted'";
            if ($primaryConfig?->stunted)          $primaryClauses[] = "height_for_age = 'stunted'";
            if ($primaryConfig?->normal_height)    $primaryClauses[] = "height_for_age = 'normal'";
            if ($primaryConfig?->tall)             $primaryClauses[] = "height_for_age = 'tall'";
            if ($primaryConfig?->_4ps)             $primaryClauses[] = '_4ps = 1';
            if ($primaryConfig?->ip)               $primaryClauses[] = 'ip = 1';
            if ($primaryConfig?->pardo)            $primaryClauses[] = 'pardo = 1';

            $notPrimary = empty($primaryClauses)
                ? '1'
                : 'NOT (' . implode(' OR ', $primaryClauses) . ')';

            $sexGroupSql = "CASE
                WHEN LOWER(TRIM(sex)) IN ('m', 'male') THEN 'm'
                WHEN LOWER(TRIM(sex)) IN ('f', 'female') THEN 'f'
            END";

            $statsRaw = NutritionalStatus::where('isBeneficiary', true)
                ->whereIn('grade', $grades)
                ->whereRaw("{$sexGroupSql} IS NOT NULL")
                ->selectRaw("
                    grade,
                    {$sexGroupSql} AS sex_group,
                    SUM(nutritional_status = 'severely wasted')                               AS sw,
                    SUM(nutritional_status = 'wasted')                                        AS wasted,
                    SUM(nutritional_status = 'normal')                                        AS normal_weight,
                    SUM(nutritional_status IN ('overweight','obese'))                          AS overweight_obese,
                    SUM(height_for_age = 'severely stunted')                                  AS severely_stunted,
                    SUM(height_for_age = 'stunted')                                           AS stunted,
                    SUM(height_for_age = 'normal')                                            AS normal_height,
                    SUM(height_for_age = 'tall')                                              AS tall,
                    SUM(pardo = 1 AND ({$notPrimary}))                                        AS pardo_cnt,
                    SUM(height_for_age IN ('stunted','severely stunted') AND ({$notPrimary})) AS stunted_ss,
                    SUM(_4ps = 1 AND ({$notPrimary}))                                         AS fourps_sec,
                    SUM(ip = 1 AND ({$notPrimary}))                                           AS ip_cnt,
                    SUM(dewormed = 1)                                                         AS dewormed_cnt,
                    SUM(_4ps = 1)                                                             AS fourps_all,
                    SUM(sbfp_previous_beneficiary = 1)                                        AS prev_ben
                ")
                ->groupBy('grade')
                ->groupByRaw($sexGroupSql)
                ->get()
                ->keyBy(fn ($row) => $row->grade . ':' . $row->sex_group);

            $zero = (object) [
                'sw' => 0,
                'wasted' => 0,
                'normal_weight' => 0,
                'overweight_obese' => 0,
                'severely_stunted' => 0,
                'stunted' => 0,
                'normal_height' => 0,
                'tall' => 0,
                'pardo_cnt' => 0,
                'stunted_ss' => 0,
                'fourps_sec' => 0,
                'ip_cnt' => 0,
                'dewormed_cnt' => 0,
                'fourps_all' => 0,
                'prev_ben' => 0,
            ];

            // Each grade occupies three rows: male, female, and a formula-driven total.
            $rowMap = [
                'k' => [14, 15],
                '1' => [17, 18],
                '2' => [20, 21],
                '3' => [23, 24],
                '4' => [26, 27],
                '5' => [29, 30],
                '6' => [32, 33],
                'non_graded' => [35, 36],
            ];
            $columnMap = [
                3 => 'sw',
                4 => 'wasted',
                5 => 'normal_weight',
                6 => 'overweight_obese',
                7 => 'severely_stunted',
                8 => 'stunted',
                9 => 'normal_height',
                10 => 'tall',
                11 => 'pardo_cnt',
                12 => 'stunted_ss',
                13 => 'fourps_sec',
                14 => 'ip_cnt',
                15 => 'dewormed_cnt',
                16 => 'fourps_all',
                17 => 'prev_ben',
            ];

            foreach ($rowMap as $grade => [$maleRow, $femaleRow]) {
                foreach (['m' => $maleRow, 'f' => $femaleRow] as $sex => $row) {
                    $stats = $statsRaw->get($grade . ':' . $sex) ?? $zero;

                    foreach ($columnMap as $column => $field) {
                        $sheet->setCellValueByColumnAndRow($column, $row, (int) ($stats->$field ?? 0));
                    }
                }
            }

            // Total and grand-total rows remain formulas supplied by the official template.
            $sheet->setCellValue('A44', $schoolProfile?->school_focal_name ?? '');
            $sheet->setCellValue('K44', $schoolProfile?->school_head_name ?? '');

            $schoolSlug = preg_replace('/[^A-Za-z0-9]+/', '_', $school?->school_name ?? 'Unknown');
            $datetime = \Carbon\Carbon::now()->format('Y-m-d_H-i-s');
            $outFileName = 'Form_2_New-' . $schoolSlug . '-' . $datetime . '.xlsx';
            $this->ensureDownloadDir();
            $outFile = public_path('downloaded_exel/' . $outFileName);
            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
            $writer->save($outFile);

            session()->flash('success', 'Form 2 New generated for download.');
            Log::info($outFileName . ' successfully written.');

            $downloadUrl = asset('downloaded_exel/' . $outFileName);
            $this->dispatch('form2-new-ready', $downloadUrl);
        } catch (\Throwable $e) {
            Log::error('Error writing Form2New.xlsx: ' . $e->getMessage());
            session()->flash('error', 'Failed to generate Form2New.xlsx: ' . $e->getMessage());
        }
    }

    public function saveBeneficiariesCount()
    {
        try {
            $this->validate([
                'beneficiariesCount' => 'required|integer|min:0',
            ]);

            $beneficiaries = Beneficiaries::first();
            $beneficiaries->beneficiaries_count = $this->beneficiariesCount;
            $beneficiaries->save();

            session()->flash('success', 'Beneficiaries count saved.');
            $this->dispatch('beneficiaries-saved', ['count' => $this->beneficiariesCount]);
        } catch (\Throwable $e) {
            Log::error('Error saving beneficiaries count: ' . $e->getMessage());
            session()->flash('error', 'Failed to save beneficiaries count: ' . $e->getMessage());
        }
    }
}
