<?php

namespace App\Livewire\GenerateForms;

use App\Models\Beneficiaries;
use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\NutritionalStatus;
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
            $sheet->setCellValueByColumnAndRow(1, 9, "School  ID Number: " . auth()->user()->school->school_id ?? '');

            // starting row in the template
            $startRow = 13;
            $endRow = 0;

            // fetch records from database
            $records = NutritionalStatus::where("isBeneficiary", "=", true)->orderBy('grade')->get();

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

                $endRow = $row;
            }

            // add a footer row below the last data row and merge columns A and B
            $footerRow = $endRow > 0 ? $endRow + 2 : $startRow;
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

            $focalNameRow = $footerRow + 1;
            $sheet->mergeCells("A{$focalNameRow}:B{$focalNameRow}");
            $sheet->setCellValue("A{$focalNameRow}", '(Focal Person Name)');
            $sheet->getStyle("A{$focalNameRow}:B{$focalNameRow}")
                ->getFont()
                ->setBold(true);

            $sheet->mergeCells("M{$focalNameRow}:N{$focalNameRow}");
            $sheet->setCellValue("M{$focalNameRow}", '(Full Name)');
            $sheet->getStyle("M{$focalNameRow}:N{$focalNameRow}")
                ->getFont()
                ->setBold(true);

            $positionRow = $focalNameRow + 1;
            $sheet->mergeCells("A{$positionRow}:B{$positionRow}");
            $sheet->setCellValue("A{$positionRow}", 'SBFP DepEd Focal');

            $sheet->mergeCells("M{$positionRow}:N{$positionRow}");
            $sheet->setCellValue("M{$positionRow}", 'School Head');

            // save spreadsheet to a new temp file so the original template remains unchanged
            $outFileName = 'Form1_filled_' . time() . '.xlsx';
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
            // load the existing spreadsheet
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($template);
            $sheet = $spreadsheet->getActiveSheet();

            // Build per-grade aggregates: kinder (k), grades 1..12, and non_graded
            $grades = array_merge(['k'], range(1, 12), ['non_graded']);
            $gradeStats = [];

            foreach ($grades as $grade) {
                $g = (string) $grade;
                $gradeStats[$g] = NutritionalStatus::where('grade', $g)
                    ->where('isBeneficiary', true)
                    ->selectRaw('
                        COUNT(*) as total,
                        SUM(sex = "m") as total_m,
                        SUM(sex = "f") as total_f,

                        SUM(sex = "m" AND nutritional_status = "severely wasted") as severely_wasted_m,
                        SUM(sex = "f" AND nutritional_status = "severely wasted") as severely_wasted_f,

                        SUM(sex = "m" AND nutritional_status = "wasted") as wasted_m,
                        SUM(sex = "f" AND nutritional_status = "wasted") as wasted_f,

                        SUM(sex = "m" AND height_for_age = "severely stunted" AND nutritional_status IN ("normal","overweight","obese")) as severely_stunted_m,
                        SUM(sex = "f" AND height_for_age = "severely stunted" AND nutritional_status IN ("normal","overweight","obese")) as severely_stunted_f,

                        SUM(sex = "m" AND height_for_age = "stunted" AND nutritional_status IN ("normal","overweight","obese")) as stunted_m,
                        SUM(sex = "f" AND height_for_age = "stunted" AND nutritional_status IN ("normal","overweight","obese")) as stunted_f,

                        SUM(sex = "m" AND ip = 1 AND nutritional_status IN ("normal","overweight","obese") AND height_for_age IN ("normal","tall")) as ip_m,
                        SUM(sex = "f" AND ip = 1 AND nutritional_status IN ("normal","overweight","obese") AND height_for_age IN ("normal","tall")) as ip_f,

                        SUM(sex = "m" AND _4ps = 1 AND ip = 0 AND nutritional_status IN ("normal","overweight","obese") AND height_for_age IN ("normal","tall")) as fourPs_m,
                        SUM(sex = "f" AND _4ps = 1 AND ip = 0 AND nutritional_status IN ("normal","overweight","obese") AND height_for_age IN ("normal","tall")) as fourPs_f,

                        SUM(sex = "m" AND pardo = 1 AND _4ps = 0 AND ip = 0 AND nutritional_status IN ("normal","overweight","obese") AND height_for_age IN ("normal","tall")) as pardo_m,
                        SUM(sex = "f" AND pardo = 1 AND _4ps = 0 AND ip = 0 AND nutritional_status IN ("normal","overweight","obese") AND height_for_age IN ("normal","tall")) as pardo_f
                    ')
                    ->first();
            }
            // aggregated counts for kinder beneficiaries (separate male/female)
            $kinderCounts = NutritionalStatus::where('grade', 'k')
                ->where('isBeneficiary', true)
                ->selectRaw('
                    SUM(sex = "m" AND nutritional_status IN ("normal","overweight","obese") AND height_for_age IN ("normal","tall")) as all_kinder_m,
                    SUM(sex = "f" AND nutritional_status IN ("normal","overweight","obese") AND height_for_age IN ("normal","tall")) as all_kinder_f
                ')
                ->first();

            $sheet->setCellValueByColumnAndRow(10, 18, $kinderCounts->all_kinder_m ?? 0);
            $sheet->setCellValueByColumnAndRow(10, 19, $kinderCounts->all_kinder_f ?? 0);

            $sheet->setCellValueByColumnAndRow(14, 18, $gradeStats['k']->severely_wasted_m ?? 0);
            $sheet->setCellValueByColumnAndRow(14, 19, $gradeStats['k']->severely_wasted_f ?? 0);

            $sheet->setCellValueByColumnAndRow(18, 18, $gradeStats['k']->wasted_m ?? 0);
            $sheet->setCellValueByColumnAndRow(18, 19, $gradeStats['k']->wasted_f ?? 0);

            $sheet->setCellValueByColumnAndRow(22, 18, $gradeStats['k']->severely_stunted_m + $gradeStats['k']->stunted_m ?? 0);
            $sheet->setCellValueByColumnAndRow(22, 19, $gradeStats['k']->severely_stunted_f + $gradeStats['k']->stunted_f ?? 0);

            $sheet->setCellValueByColumnAndRow(14, 21, $gradeStats['1']->severely_wasted_m ?? 0);
            $sheet->setCellValueByColumnAndRow(14, 22, $gradeStats['1']->severely_wasted_f ?? 0);

            $sheet->setCellValueByColumnAndRow(18, 21, $gradeStats['1']->wasted_m ?? 0);
            $sheet->setCellValueByColumnAndRow(18, 22, $gradeStats['1']->wasted_f ?? 0);

            $sheet->setCellValueByColumnAndRow(22, 21, $gradeStats['1']->severely_stunted_m + $gradeStats['1']->stunted_m ?? 0);
            $sheet->setCellValueByColumnAndRow(22, 22, $gradeStats['1']->severely_stunted_f + $gradeStats['1']->stunted_f ?? 0);

            $sheet->setCellValueByColumnAndRow(26, 21, $gradeStats['1']->pardo_m ?? 0);
            $sheet->setCellValueByColumnAndRow(26, 22, $gradeStats['1']->pardo_f ?? 0);

            $sheet->setCellValueByColumnAndRow(30, 21, $gradeStats['1']->ip_m ?? 0);
            $sheet->setCellValueByColumnAndRow(30, 22, $gradeStats['1']->ip_f ?? 0);

            $sheet->setCellValueByColumnAndRow(34, 21, $gradeStats['1']->fourPS_m ?? 0);
            $sheet->setCellValueByColumnAndRow(34, 22, $gradeStats['1']->fourPS_f ?? 0);

            $sheet->setCellValueByColumnAndRow(14, 24, $gradeStats['2']->severely_wasted_m ?? 0);
            $sheet->setCellValueByColumnAndRow(14, 25, $gradeStats['2']->severely_wasted_f ?? 0);

            $sheet->setCellValueByColumnAndRow(18, 24, $gradeStats['2']->wasted_m ?? 0);
            $sheet->setCellValueByColumnAndRow(18, 25, $gradeStats['2']->wasted_f ?? 0);

            $sheet->setCellValueByColumnAndRow(22, 24, $gradeStats['2']->severely_stunted_m + $gradeStats['2']->stunted_m ?? 0);
            $sheet->setCellValueByColumnAndRow(22, 25, $gradeStats['2']->severely_stunted_f + $gradeStats['2']->stunted_f ?? 0);

            $sheet->setCellValueByColumnAndRow(26, 24, $gradeStats['2']->pardo_m ?? 0);
            $sheet->setCellValueByColumnAndRow(26, 25, $gradeStats['2']->pardo_f ?? 0);

            $sheet->setCellValueByColumnAndRow(30, 24, $gradeStats['2']->ip_m ?? 0);
            $sheet->setCellValueByColumnAndRow(30, 25, $gradeStats['2']->ip_f ?? 0);

            $sheet->setCellValueByColumnAndRow(34, 24, $gradeStats['2']->fourPS_m ?? 0);
            $sheet->setCellValueByColumnAndRow(34, 25, $gradeStats['2']->fourPS_f ?? 0);

            //Grade 3
            $sheet->setCellValueByColumnAndRow(14, 27, $gradeStats['3']->severely_wasted_m ?? 0);
            $sheet->setCellValueByColumnAndRow(14, 28, $gradeStats['3']->severely_wasted_f ?? 0);

            $sheet->setCellValueByColumnAndRow(18, 27, $gradeStats['3']->wasted_m ?? 0);
            $sheet->setCellValueByColumnAndRow(18, 28, $gradeStats['3']->wasted_f ?? 0);

            $sheet->setCellValueByColumnAndRow(22, 27, $gradeStats['3']->severely_stunted_m + $gradeStats['3']->stunted_m ?? 0);
            $sheet->setCellValueByColumnAndRow(22, 28, $gradeStats['3']->severely_stunted_f + $gradeStats['3']->stunted_f ?? 0);

            $sheet->setCellValueByColumnAndRow(26, 27, $gradeStats['3']->pardo_m ?? 0);
            $sheet->setCellValueByColumnAndRow(26, 28, $gradeStats['3']->pardo_f ?? 0);

            $sheet->setCellValueByColumnAndRow(30, 27, $gradeStats['3']->ip_m ?? 0);
            $sheet->setCellValueByColumnAndRow(30, 28, $gradeStats['3']->ip_f ?? 0);

            $sheet->setCellValueByColumnAndRow(34, 27, $gradeStats['3']->fourPS_m ?? 0);
            $sheet->setCellValueByColumnAndRow(34, 28, $gradeStats['3']->fourPS_f ?? 0);

            //Grade 4
            $sheet->setCellValueByColumnAndRow(14, 30, $gradeStats['4']->severely_wasted_m ?? 0);
            $sheet->setCellValueByColumnAndRow(14, 31, $gradeStats['4']->severely_wasted_f ?? 0);

            $sheet->setCellValueByColumnAndRow(18, 30, $gradeStats['4']->wasted_m ?? 0);
            $sheet->setCellValueByColumnAndRow(18, 31, $gradeStats['4']->wasted_f ?? 0);

            $sheet->setCellValueByColumnAndRow(22, 30, $gradeStats['4']->severely_stunted_m + $gradeStats['4']->stunted_m ?? 0);
            $sheet->setCellValueByColumnAndRow(22, 31, $gradeStats['4']->severely_stunted_f + $gradeStats['4']->stunted_f ?? 0);

            $sheet->setCellValueByColumnAndRow(26, 30, $gradeStats['4']->pardo_m ?? 0);
            $sheet->setCellValueByColumnAndRow(26, 31, $gradeStats['4']->pardo_f ?? 0);

            $sheet->setCellValueByColumnAndRow(30, 30, $gradeStats['4']->ip_m ?? 0);
            $sheet->setCellValueByColumnAndRow(30, 31, $gradeStats['4']->ip_f ?? 0);

            $sheet->setCellValueByColumnAndRow(34, 30, $gradeStats['4']->fourPS_m ?? 0);
            $sheet->setCellValueByColumnAndRow(34, 31, $gradeStats['4']->fourPS_f ?? 0);

            //Grade 5
            $sheet->setCellValueByColumnAndRow(14, 33, $gradeStats['5']->severely_wasted_m ?? 0);
            $sheet->setCellValueByColumnAndRow(14, 34, $gradeStats['5']->severely_wasted_f ?? 0);

            $sheet->setCellValueByColumnAndRow(18, 33, $gradeStats['5']->wasted_m ?? 0);
            $sheet->setCellValueByColumnAndRow(18, 34, $gradeStats['5']->wasted_f ?? 0);

            $sheet->setCellValueByColumnAndRow(22, 33, $gradeStats['5']->severely_stunted_m + $gradeStats['5']->stunted_m ?? 0);
            $sheet->setCellValueByColumnAndRow(22, 34, $gradeStats['5']->severely_stunted_f + $gradeStats['5']->stunted_f ?? 0);

            $sheet->setCellValueByColumnAndRow(26, 33, $gradeStats['5']->pardo_m ?? 0);
            $sheet->setCellValueByColumnAndRow(26, 34, $gradeStats['5']->pardo_f ?? 0);

            $sheet->setCellValueByColumnAndRow(30, 33, $gradeStats['5']->ip_m ?? 0);
            $sheet->setCellValueByColumnAndRow(30, 34, $gradeStats['5']->ip_f ?? 0);

            $sheet->setCellValueByColumnAndRow(34, 33, $gradeStats['5']->fourPS_m ?? 0);
            $sheet->setCellValueByColumnAndRow(34, 34, $gradeStats['5']->fourPS_f ?? 0);

            //Grade 6
            $sheet->setCellValueByColumnAndRow(14, 36, $gradeStats['6']->severely_wasted_m ?? 0);
            $sheet->setCellValueByColumnAndRow(14, 37, $gradeStats['6']->severely_wasted_f ?? 0);

            $sheet->setCellValueByColumnAndRow(18, 36, $gradeStats['6']->wasted_m ?? 0);
            $sheet->setCellValueByColumnAndRow(18, 37, $gradeStats['6']->wasted_f ?? 0);

            $sheet->setCellValueByColumnAndRow(22, 36, $gradeStats['6']->severely_stunted_m + $gradeStats['6']->stunted_m ?? 0);
            $sheet->setCellValueByColumnAndRow(22, 37, $gradeStats['6']->severely_stunted_f + $gradeStats['6']->stunted_f ?? 0);

            $sheet->setCellValueByColumnAndRow(26, 36, $gradeStats['6']->pardo_m ?? 0);
            $sheet->setCellValueByColumnAndRow(26, 37, $gradeStats['6']->pardo_f ?? 0);

            $sheet->setCellValueByColumnAndRow(30, 36, $gradeStats['6']->ip_m ?? 0);
            $sheet->setCellValueByColumnAndRow(30, 37, $gradeStats['6']->ip_f ?? 0);

            $sheet->setCellValueByColumnAndRow(34, 36, $gradeStats['6']->fourPS_m ?? 0);
            $sheet->setCellValueByColumnAndRow(34, 37, $gradeStats['6']->fourPS_f ?? 0);

            //Non graded
            $sheet->setCellValueByColumnAndRow(14, 39, $gradeStats['non_graded']->severely_wasted_m ?? 0);
            $sheet->setCellValueByColumnAndRow(14, 40, $gradeStats['non_graded']->severely_wasted_f ?? 0);

            $sheet->setCellValueByColumnAndRow(18, 39, $gradeStats['non_graded']->wasted_m ?? 0);
            $sheet->setCellValueByColumnAndRow(18, 40, $gradeStats['non_graded']->wasted_f ?? 0);

            $sheet->setCellValueByColumnAndRow(22, 39, $gradeStats['non_graded']->severely_stunted_m + $gradeStats['non_graded']->stunted_m ?? 0);
            $sheet->setCellValueByColumnAndRow(22, 40, $gradeStats['non_graded']->severely_stunted_f + $gradeStats['non_graded']->stunted_f ?? 0);

            $sheet->setCellValueByColumnAndRow(26, 39, $gradeStats['non_graded']->pardo_m ?? 0);
            $sheet->setCellValueByColumnAndRow(26, 40, $gradeStats['non_graded']->pardo_f ?? 0);

            $sheet->setCellValueByColumnAndRow(30, 39, $gradeStats['non_graded']->ip_m ?? 0);
            $sheet->setCellValueByColumnAndRow(30, 40, $gradeStats['non_graded']->ip_f ?? 0);

            $sheet->setCellValueByColumnAndRow(34, 39, $gradeStats['non_graded']->fourPS_m ?? 0);
            $sheet->setCellValueByColumnAndRow(34, 40, $gradeStats['non_graded']->fourPS_f ?? 0);




            // Create a query to get the nutritional status sum of kinder, grade 1 to grade 6 and non-graded,
            // separate by sex and grade level.
            $targetGrades = array_merge(['k'], range(1, 6), ['non_graded']);
            $nutritionalSums = [];

            foreach ($targetGrades as $grade) {
                $g = (string) $grade;
                $nutritionalSums[$g] = NutritionalStatus::where('grade', $g)
                    ->where('isBeneficiary', true)
                    ->selectRaw("
                        SUM(sex = \"m\" AND nutritional_status = \"severely wasted\") as sw_m,
                        SUM(sex = \"f\" AND nutritional_status = \"severely wasted\") as sw_f,

                        SUM(sex = \"m\" AND nutritional_status = \"wasted\") as wasted_m,
                        SUM(sex = \"f\" AND nutritional_status = \"wasted\") as wasted_f,

                        SUM(sex = \"m\" AND nutritional_status = \"normal\") as normal_m,
                        SUM(sex = \"f\" AND nutritional_status = \"normal\") as normal_f,

                        SUM(sex = \"m\" AND nutritional_status = \"overweight\") as overweight_m,
                        SUM(sex = \"f\" AND nutritional_status = \"overweight\") as overweight_f,

                        SUM(sex = \"m\" AND nutritional_status = \"obese\") as obese_m,
                        SUM(sex = \"f\" AND nutritional_status = \"obese\") as obese_f
                    ")
                    ->first();
            }

            //For Kinder
            $sheet->setCellValueByColumnAndRow(10, 193, $nutritionalSums['k']->sw_m ?? 0);
            $sheet->setCellValueByColumnAndRow(10, 194, $nutritionalSums['k']->sw_f ?? 0);
            $sheet->setCellValueByColumnAndRow(17, 193, $nutritionalSums['k']->wasted_m ?? 0);
            $sheet->setCellValueByColumnAndRow(17, 194, $nutritionalSums['k']->wasted_f ?? 0);
            $sheet->setCellValueByColumnAndRow(24, 193, $nutritionalSums['k']->normal_m ?? 0);
            $sheet->setCellValueByColumnAndRow(24, 194, $nutritionalSums['k']->normal_f ?? 0);
            $sheet->setCellValueByColumnAndRow(31, 193, $nutritionalSums['k']->overweight_m ?? 0);
            $sheet->setCellValueByColumnAndRow(31, 194, $nutritionalSums['k']->overweight_f ?? 0);
            $sheet->setCellValueByColumnAndRow(38, 193, $nutritionalSums['k']->obese_m ?? 0);
            $sheet->setCellValueByColumnAndRow(38, 194, $nutritionalSums['k']->obese_f ?? 0);

            //For Grade 1
            $sheet->setCellValueByColumnAndRow(10, 196, $nutritionalSums['1']->sw_m ?? 0);
            $sheet->setCellValueByColumnAndRow(10, 197, $nutritionalSums['1']->sw_f ?? 0);
            $sheet->setCellValueByColumnAndRow(17, 196, $nutritionalSums['1']->wasted_m ?? 0);
            $sheet->setCellValueByColumnAndRow(17, 197, $nutritionalSums['1']->wasted_f ?? 0);
            $sheet->setCellValueByColumnAndRow(24, 196, $nutritionalSums['1']->normal_m ?? 0);
            $sheet->setCellValueByColumnAndRow(24, 197, $nutritionalSums['1']->normal_f ?? 0);
            $sheet->setCellValueByColumnAndRow(31, 196, $nutritionalSums['1']->overweight_m ?? 0);
            $sheet->setCellValueByColumnAndRow(31, 197, $nutritionalSums['1']->overweight_f ?? 0);
            $sheet->setCellValueByColumnAndRow(38, 196, $nutritionalSums['1']->obese_m ?? 0);
            $sheet->setCellValueByColumnAndRow(38, 197, $nutritionalSums['1']->obese_f ?? 0);

            //For Grade 2
            $sheet->setCellValueByColumnAndRow(10, 199, $nutritionalSums['2']->sw_m ?? 0);
            $sheet->setCellValueByColumnAndRow(10, 200, $nutritionalSums['2']->sw_f ?? 0);
            $sheet->setCellValueByColumnAndRow(17, 199, $nutritionalSums['2']->wasted_m ?? 0);
            $sheet->setCellValueByColumnAndRow(17, 200, $nutritionalSums['2']->wasted_f ?? 0);
            $sheet->setCellValueByColumnAndRow(24, 199, $nutritionalSums['2']->normal_m ?? 0);
            $sheet->setCellValueByColumnAndRow(24, 200, $nutritionalSums['2']->normal_f ?? 0);
            $sheet->setCellValueByColumnAndRow(31, 199, $nutritionalSums['2']->overweight_m ?? 0);
            $sheet->setCellValueByColumnAndRow(31, 200, $nutritionalSums['2']->overweight_f ?? 0);
            $sheet->setCellValueByColumnAndRow(38, 199, $nutritionalSums['2']->obese_m ?? 0);
            $sheet->setCellValueByColumnAndRow(38, 200, $nutritionalSums['2']->obese_f ?? 0);

            //For Grade 3
            $sheet->setCellValueByColumnAndRow(10, 202, $nutritionalSums['3']->sw_m ?? 0);
            $sheet->setCellValueByColumnAndRow(10, 203, $nutritionalSums['3']->sw_f ?? 0);
            $sheet->setCellValueByColumnAndRow(17, 202, $nutritionalSums['3']->wasted_m ?? 0);
            $sheet->setCellValueByColumnAndRow(17, 203, $nutritionalSums['3']->wasted_f ?? 0);
            $sheet->setCellValueByColumnAndRow(24, 202, $nutritionalSums['3']->normal_m ?? 0);
            $sheet->setCellValueByColumnAndRow(24, 203, $nutritionalSums['3']->normal_f ?? 0);
            $sheet->setCellValueByColumnAndRow(31, 202, $nutritionalSums['3']->overweight_m ?? 0);
            $sheet->setCellValueByColumnAndRow(31, 203, $nutritionalSums['3']->overweight_f ?? 0);
            $sheet->setCellValueByColumnAndRow(38, 202, $nutritionalSums['3']->obese_m ?? 0);
            $sheet->setCellValueByColumnAndRow(38, 203, $nutritionalSums['3']->obese_f ?? 0);

            //For Grade 4
            $sheet->setCellValueByColumnAndRow(10, 205, $nutritionalSums['4']->sw_m ?? 0);
            $sheet->setCellValueByColumnAndRow(10, 206, $nutritionalSums['4']->sw_f ?? 0);
            $sheet->setCellValueByColumnAndRow(17, 205, $nutritionalSums['4']->wasted_m ?? 0);
            $sheet->setCellValueByColumnAndRow(17, 206, $nutritionalSums['4']->wasted_f ?? 0);
            $sheet->setCellValueByColumnAndRow(24, 205, $nutritionalSums['4']->normal_m ?? 0);
            $sheet->setCellValueByColumnAndRow(24, 206, $nutritionalSums['4']->normal_f ?? 0);
            $sheet->setCellValueByColumnAndRow(31, 205, $nutritionalSums['4']->overweight_m ?? 0);
            $sheet->setCellValueByColumnAndRow(31, 206, $nutritionalSums['4']->overweight_f ?? 0);
            $sheet->setCellValueByColumnAndRow(38, 205, $nutritionalSums['4']->obese_m ?? 0);
            $sheet->setCellValueByColumnAndRow(38, 206, $nutritionalSums['4']->obese_f ?? 0);

            //For Grade 5
            $sheet->setCellValueByColumnAndRow(10, 208, $nutritionalSums['5']->sw_m ?? 0);
            $sheet->setCellValueByColumnAndRow(10, 209, $nutritionalSums['5']->sw_f ?? 0);
            $sheet->setCellValueByColumnAndRow(17, 208, $nutritionalSums['5']->wasted_m ?? 0);
            $sheet->setCellValueByColumnAndRow(17, 209, $nutritionalSums['5']->wasted_f ?? 0);
            $sheet->setCellValueByColumnAndRow(24, 208, $nutritionalSums['5']->normal_m ?? 0);
            $sheet->setCellValueByColumnAndRow(24, 209, $nutritionalSums['5']->normal_f ?? 0);
            $sheet->setCellValueByColumnAndRow(31, 208, $nutritionalSums['5']->overweight_m ?? 0);
            $sheet->setCellValueByColumnAndRow(31, 209, $nutritionalSums['5']->overweight_f ?? 0);
            $sheet->setCellValueByColumnAndRow(38, 208, $nutritionalSums['5']->obese_m ?? 0);
            $sheet->setCellValueByColumnAndRow(38, 209, $nutritionalSums['5']->obese_f ?? 0);

            //For Grade 6
            $sheet->setCellValueByColumnAndRow(10, 211, $nutritionalSums['6']->sw_m ?? 0);
            $sheet->setCellValueByColumnAndRow(10, 212, $nutritionalSums['6']->sw_f ?? 0);
            $sheet->setCellValueByColumnAndRow(17, 211, $nutritionalSums['6']->wasted_m ?? 0);
            $sheet->setCellValueByColumnAndRow(17, 212, $nutritionalSums['6']->wasted_f ?? 0);
            $sheet->setCellValueByColumnAndRow(24, 211, $nutritionalSums['6']->normal_m ?? 0);
            $sheet->setCellValueByColumnAndRow(24, 212, $nutritionalSums['6']->normal_f ?? 0);
            $sheet->setCellValueByColumnAndRow(31, 211, $nutritionalSums['6']->overweight_m ?? 0);
            $sheet->setCellValueByColumnAndRow(31, 212, $nutritionalSums['6']->overweight_f ?? 0);
            $sheet->setCellValueByColumnAndRow(38, 211, $nutritionalSums['6']->obese_m ?? 0);
            $sheet->setCellValueByColumnAndRow(38, 212, $nutritionalSums['6']->obese_f ?? 0);

            //For Non-graded
            $sheet->setCellValueByColumnAndRow(10, 214, $nutritionalSums['non_graded']->sw_m ?? 0);
            $sheet->setCellValueByColumnAndRow(10, 215, $nutritionalSums['non_graded']->sw_f ?? 0);
            $sheet->setCellValueByColumnAndRow(17, 214, $nutritionalSums['non_graded']->wasted_m ?? 0);
            $sheet->setCellValueByColumnAndRow(17, 215, $nutritionalSums['non_graded']->wasted_f ?? 0);
            $sheet->setCellValueByColumnAndRow(24, 214, $nutritionalSums['non_graded']->normal_m ?? 0);
            $sheet->setCellValueByColumnAndRow(24, 215, $nutritionalSums['non_graded']->normal_f ?? 0);
            $sheet->setCellValueByColumnAndRow(31, 214, $nutritionalSums['non_graded']->overweight_m ?? 0);
            $sheet->setCellValueByColumnAndRow(31, 215, $nutritionalSums['non_graded']->overweight_f ?? 0);
            $sheet->setCellValueByColumnAndRow(38, 214, $nutritionalSums['non_graded']->obese_m ?? 0);
            $sheet->setCellValueByColumnAndRow(38, 215, $nutritionalSums['non_graded']->obese_f ?? 0);


            //Create a query to get the beneficiary height_for_age sum of kinder, grade 1 to grade 6 and non-graded,
            $targetGradesHfa = array_merge(['k'], range(1, 6), ['non_graded']);
            $hfaSums = [];

            foreach ($targetGradesHfa as $grade) {
                $g = (string) $grade;
                $hfaSums[$g] = NutritionalStatus::where('grade', $g)
                    ->where('isBeneficiary', true)
                    ->selectRaw("
                        SUM(sex = 'm' AND height_for_age = 'severely stunted') as ss_m,
                        SUM(sex = 'f' AND height_for_age = 'severely stunted') as ss_f,

                        SUM(sex = 'm' AND height_for_age = 'stunted') as stunted_m,
                        SUM(sex = 'f' AND height_for_age = 'stunted') as stunted_f,

                        SUM(sex = 'm' AND height_for_age = 'normal') as normal_m,
                        SUM(sex = 'f' AND height_for_age = 'normal') as normal_f,

                        SUM(sex = 'm' AND height_for_age = 'tall') as tall_m,
                        SUM(sex = 'f' AND height_for_age = 'tall') as tall_f
                    ")
                    ->first();
            }

            //For Kinder
            $sheet->setCellValueByColumnAndRow(10, 229, $hfaSums['k']->ss_m ?? 0);
            $sheet->setCellValueByColumnAndRow(10, 230, $hfaSums['k']->ss_f ?? 0);
            $sheet->setCellValueByColumnAndRow(17, 229, $hfaSums['k']->stunted_m ?? 0);
            $sheet->setCellValueByColumnAndRow(17, 230, $hfaSums['k']->stunted_f ?? 0);
            $sheet->setCellValueByColumnAndRow(24, 229, $hfaSums['k']->normal_m ?? 0);
            $sheet->setCellValueByColumnAndRow(24, 230, $hfaSums['k']->normal_f ?? 0);
            $sheet->setCellValueByColumnAndRow(31, 229, $hfaSums['k']->tall_m ?? 0);
            $sheet->setCellValueByColumnAndRow(31, 230, $hfaSums['k']->tall_f ?? 0);

            //For Grade 1
            $sheet->setCellValueByColumnAndRow(10, 232, $hfaSums['1']->ss_m ?? 0);
            $sheet->setCellValueByColumnAndRow(10, 233, $hfaSums['1']->ss_f ?? 0);
            $sheet->setCellValueByColumnAndRow(17, 232, $hfaSums['1']->stunted_m ?? 0);
            $sheet->setCellValueByColumnAndRow(17, 233, $hfaSums['1']->stunted_f ?? 0);
            $sheet->setCellValueByColumnAndRow(24, 232, $hfaSums['1']->normal_m ?? 0);
            $sheet->setCellValueByColumnAndRow(24, 233, $hfaSums['1']->normal_f ?? 0);
            $sheet->setCellValueByColumnAndRow(31, 232, $hfaSums['1']->tall_m ?? 0);
            $sheet->setCellValueByColumnAndRow(31, 233, $hfaSums['1']->tall_f ?? 0);

            //For Grade 2
            $sheet->setCellValueByColumnAndRow(10, 235, $hfaSums['2']->ss_m ?? 0);
            $sheet->setCellValueByColumnAndRow(10, 236, $hfaSums['2']->ss_f ?? 0);
            $sheet->setCellValueByColumnAndRow(17, 235, $hfaSums['2']->stunted_m ?? 0);
            $sheet->setCellValueByColumnAndRow(17, 236, $hfaSums['2']->stunted_f ?? 0);
            $sheet->setCellValueByColumnAndRow(24, 235, $hfaSums['2']->normal_m ?? 0);
            $sheet->setCellValueByColumnAndRow(24, 236, $hfaSums['2']->normal_f ?? 0);
            $sheet->setCellValueByColumnAndRow(31, 235, $hfaSums['2']->tall_m ?? 0);
            $sheet->setCellValueByColumnAndRow(31, 236, $hfaSums['2']->tall_f ?? 0);

            //For Grade 3
            $sheet->setCellValueByColumnAndRow(10, 238, $hfaSums['3']->ss_m ?? 0);
            $sheet->setCellValueByColumnAndRow(10, 239, $hfaSums['3']->ss_f ?? 0);
            $sheet->setCellValueByColumnAndRow(17, 238, $hfaSums['3']->stunted_m ?? 0);
            $sheet->setCellValueByColumnAndRow(17, 239, $hfaSums['3']->stunted_f ?? 0);
            $sheet->setCellValueByColumnAndRow(24, 238, $hfaSums['3']->normal_m ?? 0);
            $sheet->setCellValueByColumnAndRow(24, 239, $hfaSums['3']->normal_f ?? 0);
            $sheet->setCellValueByColumnAndRow(31, 238, $hfaSums['3']->tall_m ?? 0);
            $sheet->setCellValueByColumnAndRow(31, 239, $hfaSums['3']->tall_f ?? 0);

            //For Grade 4
            $sheet->setCellValueByColumnAndRow(10, 241, $hfaSums['4']->ss_m ?? 0);
            $sheet->setCellValueByColumnAndRow(10, 242, $hfaSums['4']->ss_f ?? 0);
            $sheet->setCellValueByColumnAndRow(17, 241, $hfaSums['4']->stunted_m ?? 0);
            $sheet->setCellValueByColumnAndRow(17, 242, $hfaSums['4']->stunted_f ?? 0);
            $sheet->setCellValueByColumnAndRow(24, 241, $hfaSums['4']->normal_m ?? 0);
            $sheet->setCellValueByColumnAndRow(24, 242, $hfaSums['4']->normal_f ?? 0);
            $sheet->setCellValueByColumnAndRow(31, 241, $hfaSums['4']->tall_m ?? 0);
            $sheet->setCellValueByColumnAndRow(31, 242, $hfaSums['4']->tall_f ?? 0);

            //For Grade 5
            $sheet->setCellValueByColumnAndRow(10, 244, $hfaSums['5']->ss_m ?? 0);
            $sheet->setCellValueByColumnAndRow(10, 245, $hfaSums['5']->ss_f ?? 0);
            $sheet->setCellValueByColumnAndRow(17, 244, $hfaSums['5']->stunted_m ?? 0);
            $sheet->setCellValueByColumnAndRow(17, 245, $hfaSums['5']->stunted_f ?? 0);
            $sheet->setCellValueByColumnAndRow(24, 244, $hfaSums['5']->normal_m ?? 0);
            $sheet->setCellValueByColumnAndRow(24, 245, $hfaSums['5']->normal_f ?? 0);
            $sheet->setCellValueByColumnAndRow(31, 244, $hfaSums['5']->tall_m ?? 0);
            $sheet->setCellValueByColumnAndRow(31, 245, $hfaSums['5']->tall_f ?? 0);

            //For Grade 6
            $sheet->setCellValueByColumnAndRow(10, 247, $hfaSums['6']->ss_m ?? 0);
            $sheet->setCellValueByColumnAndRow(10, 248, $hfaSums['6']->ss_f ?? 0);
            $sheet->setCellValueByColumnAndRow(17, 247, $hfaSums['6']->stunted_m ?? 0);
            $sheet->setCellValueByColumnAndRow(17, 248, $hfaSums['6']->stunted_f ?? 0);
            $sheet->setCellValueByColumnAndRow(24, 247, $hfaSums['6']->normal_m ?? 0);
            $sheet->setCellValueByColumnAndRow(24, 248, $hfaSums['6']->normal_f ?? 0);
            $sheet->setCellValueByColumnAndRow(31, 247, $hfaSums['6']->tall_m ?? 0);
            $sheet->setCellValueByColumnAndRow(31, 248, $hfaSums['6']->tall_f ?? 0);

            //For Non-graded
            $sheet->setCellValueByColumnAndRow(10, 250, $hfaSums['non_graded']->ss_m ?? 0);
            $sheet->setCellValueByColumnAndRow(10, 251, $hfaSums['non_graded']->ss_f ?? 0);
            $sheet->setCellValueByColumnAndRow(17, 250, $hfaSums['non_graded']->stunted_m ?? 0);
            $sheet->setCellValueByColumnAndRow(17, 251, $hfaSums['non_graded']->stunted_f ?? 0);
            $sheet->setCellValueByColumnAndRow(24, 250, $hfaSums['non_graded']->normal_m ?? 0);
            $sheet->setCellValueByColumnAndRow(24, 251, $hfaSums['non_graded']->normal_f ?? 0);
            $sheet->setCellValueByColumnAndRow(31, 250, $hfaSums['non_graded']->tall_m ?? 0);
            $sheet->setCellValueByColumnAndRow(31, 251, $hfaSums['non_graded']->tall_f ?? 0);

            //Create a query to get the beneficiary count of kinder, grade 1 to grade 6 and non-graded, male and female.
            // Create a query to get the beneficiary count (male/female) for kinder, grades 1..6 and non_graded
            $targetGradesCounts = array_merge(['k'], range(1, 6), ['non_graded']);
            $beneficiaryCounts = [];

            foreach ($targetGradesCounts as $grade) {
                $g = (string) $grade;
                $beneficiaryCounts[$g] = NutritionalStatus::where('grade', $g)
                    ->where('isBeneficiary', true)
                    ->selectRaw("
                        SUM(sex = 'm') as male,
                        SUM(sex = 'f') as female,
                        COUNT(*) as total,
                        SUM(sex = 'm' AND dewormed = 1) as dewormed_m,
                        SUM(sex = 'f' AND dewormed = 1) as dewormed_f,
                        SUM(dewormed = 1) as dewormed_total,
                        SUM(sex = 'm' AND _4ps = 1) as fourps_m,
                        SUM(sex = 'f' AND _4ps = 1) as fourps_f,
                        SUM(_4ps = 1) as fourps_total,
                        SUM(sex = 'm' AND sbfp_previous_beneficiary = 1) as prev_ben_m,
                        SUM(sex = 'f' AND sbfp_previous_beneficiary = 1) as prev_ben_f,
                        SUM(sbfp_previous_beneficiary = 1) as prev_ben_total
                    ")
                    ->first();
            }

            //For kinder
            $sheet->setCellValueByColumnAndRow(10, 263, $beneficiaryCounts['k']->male ?? 0);
            $sheet->setCellValueByColumnAndRow(10, 264, $beneficiaryCounts['k']->female ?? 0);
            $sheet->setCellValueByColumnAndRow(15, 263, $beneficiaryCounts['k']->male ?? 0);
            $sheet->setCellValueByColumnAndRow(15, 264, $beneficiaryCounts['k']->female ?? 0);
            $sheet->setCellValueByColumnAndRow(22, 263, $beneficiaryCounts['k']->dewormed_m ?? 0);
            $sheet->setCellValueByColumnAndRow(22, 264, $beneficiaryCounts['k']->dewormed_f ?? 0);
            $sheet->setCellValueByColumnAndRow(29, 263, $beneficiaryCounts['k']->fourps_m ?? 0);
            $sheet->setCellValueByColumnAndRow(29, 264, $beneficiaryCounts['k']->fourps_f ?? 0);
            $sheet->setCellValueByColumnAndRow(36, 263, $beneficiaryCounts['k']->prev_ben_m ?? 0);
            $sheet->setCellValueByColumnAndRow(36, 264, $beneficiaryCounts['k']->prev_ben_f ?? 0);

            //For Grade 1
            $sheet->setCellValueByColumnAndRow(10, 266, $beneficiaryCounts['1']->male ?? 0);
            $sheet->setCellValueByColumnAndRow(10, 267, $beneficiaryCounts['1']->female ?? 0);
            $sheet->setCellValueByColumnAndRow(15, 266, $beneficiaryCounts['1']->male ?? 0);
            $sheet->setCellValueByColumnAndRow(15, 267, $beneficiaryCounts['1']->female ?? 0);
            $sheet->setCellValueByColumnAndRow(22, 266, $beneficiaryCounts['1']->dewormed_m ?? 0);
            $sheet->setCellValueByColumnAndRow(22, 267, $beneficiaryCounts['1']->dewormed_f ?? 0);
            $sheet->setCellValueByColumnAndRow(29, 266, $beneficiaryCounts['1']->fourps_m ?? 0);
            $sheet->setCellValueByColumnAndRow(29, 267, $beneficiaryCounts['1']->fourps_f ?? 0);
            $sheet->setCellValueByColumnAndRow(36, 266, $beneficiaryCounts['1']->prev_ben_m ?? 0);
            $sheet->setCellValueByColumnAndRow(36, 267, $beneficiaryCounts['1']->prev_ben_f ?? 0);

            //For Grade 2
            $sheet->setCellValueByColumnAndRow(10, 269, $beneficiaryCounts['2']->male ?? 0);
            $sheet->setCellValueByColumnAndRow(10, 270, $beneficiaryCounts['2']->female ?? 0);
            $sheet->setCellValueByColumnAndRow(15, 269, $beneficiaryCounts['2']->male ?? 0);
            $sheet->setCellValueByColumnAndRow(15, 270, $beneficiaryCounts['2']->female ?? 0);
            $sheet->setCellValueByColumnAndRow(22, 269, $beneficiaryCounts['2']->dewormed_m ?? 0);
            $sheet->setCellValueByColumnAndRow(22, 270, $beneficiaryCounts['2']->dewormed_f ?? 0);
            $sheet->setCellValueByColumnAndRow(29, 269, $beneficiaryCounts['2']->fourps_m ?? 0);
            $sheet->setCellValueByColumnAndRow(29, 270, $beneficiaryCounts['2']->fourps_f ?? 0);
            $sheet->setCellValueByColumnAndRow(36, 269, $beneficiaryCounts['2']->prev_ben_m ?? 0);
            $sheet->setCellValueByColumnAndRow(36, 270, $beneficiaryCounts['2']->prev_ben_f ?? 0);

            //For Grade 3
            $sheet->setCellValueByColumnAndRow(10, 272, $beneficiaryCounts['3']->male ?? 0);
            $sheet->setCellValueByColumnAndRow(10, 273, $beneficiaryCounts['3']->female ?? 0);
            $sheet->setCellValueByColumnAndRow(15, 272, $beneficiaryCounts['3']->male ?? 0);
            $sheet->setCellValueByColumnAndRow(15, 273, $beneficiaryCounts['3']->female ?? 0);
            $sheet->setCellValueByColumnAndRow(22, 272, $beneficiaryCounts['3']->dewormed_m ?? 0);
            $sheet->setCellValueByColumnAndRow(22, 273, $beneficiaryCounts['3']->dewormed_f ?? 0);
            $sheet->setCellValueByColumnAndRow(29, 272, $beneficiaryCounts['3']->fourps_m ?? 0);
            $sheet->setCellValueByColumnAndRow(29, 273, $beneficiaryCounts['3']->fourps_f ?? 0);
            $sheet->setCellValueByColumnAndRow(36, 272, $beneficiaryCounts['3']->prev_ben_m ?? 0);
            $sheet->setCellValueByColumnAndRow(36, 273, $beneficiaryCounts['3']->prev_ben_f ?? 0);

            //For Grade 4
            $sheet->setCellValueByColumnAndRow(10, 275, $beneficiaryCounts['4']->male ?? 0);
            $sheet->setCellValueByColumnAndRow(10, 276, $beneficiaryCounts['4']->female ?? 0);
            $sheet->setCellValueByColumnAndRow(15, 275, $beneficiaryCounts['4']->male ?? 0);
            $sheet->setCellValueByColumnAndRow(15, 276, $beneficiaryCounts['4']->female ?? 0);
            $sheet->setCellValueByColumnAndRow(22, 275, $beneficiaryCounts['4']->dewormed_m ?? 0);
            $sheet->setCellValueByColumnAndRow(22, 276, $beneficiaryCounts['4']->dewormed_f ?? 0);
            $sheet->setCellValueByColumnAndRow(29, 275, $beneficiaryCounts['4']->fourps_m ?? 0);
            $sheet->setCellValueByColumnAndRow(29, 276, $beneficiaryCounts['4']->fourps_f ?? 0);
            $sheet->setCellValueByColumnAndRow(36, 275, $beneficiaryCounts['4']->prev_ben_m ?? 0);
            $sheet->setCellValueByColumnAndRow(36, 276, $beneficiaryCounts['4']->prev_ben_f ?? 0);

            //For Grade 5
            $sheet->setCellValueByColumnAndRow(10, 278, $beneficiaryCounts['5']->male ?? 0);
            $sheet->setCellValueByColumnAndRow(10, 279, $beneficiaryCounts['5']->female ?? 0);
            $sheet->setCellValueByColumnAndRow(15, 278, $beneficiaryCounts['5']->male ?? 0);
            $sheet->setCellValueByColumnAndRow(15, 279, $beneficiaryCounts['5']->female ?? 0);
            $sheet->setCellValueByColumnAndRow(22, 278, $beneficiaryCounts['5']->dewormed_m ?? 0);
            $sheet->setCellValueByColumnAndRow(22, 279, $beneficiaryCounts['5']->dewormed_f ?? 0);
            $sheet->setCellValueByColumnAndRow(29, 278, $beneficiaryCounts['5']->fourps_m ?? 0);
            $sheet->setCellValueByColumnAndRow(29, 279, $beneficiaryCounts['5']->fourps_f ?? 0);
            $sheet->setCellValueByColumnAndRow(36, 278, $beneficiaryCounts['5']->prev_ben_m ?? 0);
            $sheet->setCellValueByColumnAndRow(36, 279, $beneficiaryCounts['5']->prev_ben_f ?? 0);

            //For Grade 6
            $sheet->setCellValueByColumnAndRow(10, 281, $beneficiaryCounts['6']->male ?? 0);
            $sheet->setCellValueByColumnAndRow(10, 282, $beneficiaryCounts['6']->female ?? 0);
            $sheet->setCellValueByColumnAndRow(15, 281, $beneficiaryCounts['6']->male ?? 0);
            $sheet->setCellValueByColumnAndRow(15, 282, $beneficiaryCounts['6']->female ?? 0);
            $sheet->setCellValueByColumnAndRow(22, 281, $beneficiaryCounts['6']->dewormed_m ?? 0);
            $sheet->setCellValueByColumnAndRow(22, 282, $beneficiaryCounts['6']->dewormed_f ?? 0);
            $sheet->setCellValueByColumnAndRow(29, 281, $beneficiaryCounts['6']->fourps_m ?? 0);
            $sheet->setCellValueByColumnAndRow(29, 282, $beneficiaryCounts['6']->fourps_f ?? 0);
            $sheet->setCellValueByColumnAndRow(36, 281, $beneficiaryCounts['6']->prev_ben_m ?? 0);
            $sheet->setCellValueByColumnAndRow(36, 282, $beneficiaryCounts['6']->prev_ben_f ?? 0);

            //For Non-graded
            $sheet->setCellValueByColumnAndRow(10, 284, $beneficiaryCounts['non_graded']->male ?? 0);
            $sheet->setCellValueByColumnAndRow(10, 285, $beneficiaryCounts['non_graded']->female ?? 0);
            $sheet->setCellValueByColumnAndRow(15, 284, $beneficiaryCounts['non_graded']->male ?? 0);
            $sheet->setCellValueByColumnAndRow(15, 285, $beneficiaryCounts['non_graded']->female ?? 0);
            $sheet->setCellValueByColumnAndRow(22, 284, $beneficiaryCounts['non_graded']->dewormed_m ?? 0);
            $sheet->setCellValueByColumnAndRow(22, 285, $beneficiaryCounts['non_graded']->dewormed_f ?? 0);
            $sheet->setCellValueByColumnAndRow(29, 284, $beneficiaryCounts['non_graded']->fourps_m ?? 0);
            $sheet->setCellValueByColumnAndRow(29, 285, $beneficiaryCounts['non_graded']->fourps_f ?? 0);
            $sheet->setCellValueByColumnAndRow(36, 284, $beneficiaryCounts['non_graded']->prev_ben_m ?? 0);
            $sheet->setCellValueByColumnAndRow(36, 285, $beneficiaryCounts['non_graded']->prev_ben_f ?? 0);




            //9. Percentage and attendance table
            //For kinder
            $sheet->setCellValueByColumnAndRow(10, 298, $beneficiaryCounts['k']->male ?? 0);
            $sheet->setCellValueByColumnAndRow(10, 299, $beneficiaryCounts['k']->female ?? 0);
            $sheet->setCellValueByColumnAndRow(17, 298, $beneficiaryCounts['k']->male ?? 0);
            $sheet->setCellValueByColumnAndRow(17, 299, $beneficiaryCounts['k']->female ?? 0);
            $sheet->setCellValueByColumnAndRow(24, 298, $beneficiaryCounts['k']->male ?? 0);
            $sheet->setCellValueByColumnAndRow(24, 299, $beneficiaryCounts['k']->female ?? 0);
            $sheet->setCellValueByColumnAndRow(31, 298, $beneficiaryCounts['k']->male ?? 0);
            $sheet->setCellValueByColumnAndRow(31, 299, $beneficiaryCounts['k']->female ?? 0);
            $sheet->setCellValueByColumnAndRow(38, 298, $beneficiaryCounts['k']->male ?? 0);
            $sheet->setCellValueByColumnAndRow(38, 299, $beneficiaryCounts['k']->female ?? 0);
            $sheet->setCellValueByColumnAndRow(45, 298, $beneficiaryCounts['k']->male ?? 0);
            $sheet->setCellValueByColumnAndRow(45, 299, $beneficiaryCounts['k']->female ?? 0);
            $sheet->setCellValueByColumnAndRow(52, 298, $beneficiaryCounts['k']->male ?? 0);
            $sheet->setCellValueByColumnAndRow(52, 299, $beneficiaryCounts['k']->female ?? 0);
            $sheet->setCellValueByColumnAndRow(59, 298, $beneficiaryCounts['k']->male ?? 0);
            $sheet->setCellValueByColumnAndRow(59, 299, $beneficiaryCounts['k']->female ?? 0);
            $sheet->setCellValueByColumnAndRow(66, 298, $beneficiaryCounts['k']->male ?? 0);
            $sheet->setCellValueByColumnAndRow(66, 299, $beneficiaryCounts['k']->female ?? 0);
            $sheet->setCellValueByColumnAndRow(73, 298, $beneficiaryCounts['k']->male ?? 0);
            $sheet->setCellValueByColumnAndRow(73, 299, $beneficiaryCounts['k']->female ?? 0);


            //For Grade 1
            $sheet->setCellValueByColumnAndRow(10, 301, $beneficiaryCounts['1']->male ?? 0);
            $sheet->setCellValueByColumnAndRow(10, 302, $beneficiaryCounts['1']->female ?? 0);
            $sheet->setCellValueByColumnAndRow(17, 301, $beneficiaryCounts['1']->male ?? 0);
            $sheet->setCellValueByColumnAndRow(17, 302, $beneficiaryCounts['1']->female ?? 0);
            $sheet->setCellValueByColumnAndRow(24, 301, $beneficiaryCounts['1']->male ?? 0);
            $sheet->setCellValueByColumnAndRow(24, 302, $beneficiaryCounts['1']->female ?? 0);
            $sheet->setCellValueByColumnAndRow(31, 301, $beneficiaryCounts['1']->male ?? 0);
            $sheet->setCellValueByColumnAndRow(31, 302, $beneficiaryCounts['1']->female ?? 0);
            $sheet->setCellValueByColumnAndRow(38, 301, $beneficiaryCounts['1']->male ?? 0);
            $sheet->setCellValueByColumnAndRow(38, 302, $beneficiaryCounts['1']->female ?? 0);
            $sheet->setCellValueByColumnAndRow(45, 301, $beneficiaryCounts['1']->male ?? 0);
            $sheet->setCellValueByColumnAndRow(45, 302, $beneficiaryCounts['1']->female ?? 0);
            $sheet->setCellValueByColumnAndRow(52, 301, $beneficiaryCounts['1']->male ?? 0);
            $sheet->setCellValueByColumnAndRow(52, 302, $beneficiaryCounts['1']->female ?? 0);
            $sheet->setCellValueByColumnAndRow(59, 301, $beneficiaryCounts['1']->male ?? 0);
            $sheet->setCellValueByColumnAndRow(59, 302, $beneficiaryCounts['1']->female ?? 0);
            $sheet->setCellValueByColumnAndRow(66, 301, $beneficiaryCounts['1']->male ?? 0);
            $sheet->setCellValueByColumnAndRow(66, 302, $beneficiaryCounts['1']->female ?? 0);
            $sheet->setCellValueByColumnAndRow(73, 301, $beneficiaryCounts['1']->male ?? 0);
            $sheet->setCellValueByColumnAndRow(73, 302, $beneficiaryCounts['1']->female ?? 0);

            //For Grade 2
            $sheet->setCellValueByColumnAndRow(10, 304, $beneficiaryCounts['2']->male ?? 0);
            $sheet->setCellValueByColumnAndRow(10, 305, $beneficiaryCounts['2']->female ?? 0);
            $sheet->setCellValueByColumnAndRow(17, 304, $beneficiaryCounts['2']->male ?? 0);
            $sheet->setCellValueByColumnAndRow(17, 305, $beneficiaryCounts['2']->female ?? 0);
            $sheet->setCellValueByColumnAndRow(24, 304, $beneficiaryCounts['2']->male ?? 0);
            $sheet->setCellValueByColumnAndRow(24, 305, $beneficiaryCounts['2']->female ?? 0);
            $sheet->setCellValueByColumnAndRow(31, 304, $beneficiaryCounts['2']->male ?? 0);
            $sheet->setCellValueByColumnAndRow(31, 305, $beneficiaryCounts['2']->female ?? 0);
            $sheet->setCellValueByColumnAndRow(38, 304, $beneficiaryCounts['2']->male ?? 0);
            $sheet->setCellValueByColumnAndRow(38, 305, $beneficiaryCounts['2']->female ?? 0);
            $sheet->setCellValueByColumnAndRow(45, 304, $beneficiaryCounts['2']->male ?? 0);
            $sheet->setCellValueByColumnAndRow(45, 305, $beneficiaryCounts['2']->female ?? 0);
            $sheet->setCellValueByColumnAndRow(52, 304, $beneficiaryCounts['2']->male ?? 0);
            $sheet->setCellValueByColumnAndRow(52, 305, $beneficiaryCounts['2']->female ?? 0);
            $sheet->setCellValueByColumnAndRow(59, 304, $beneficiaryCounts['2']->male ?? 0);
            $sheet->setCellValueByColumnAndRow(59, 305, $beneficiaryCounts['2']->female ?? 0);
            $sheet->setCellValueByColumnAndRow(66, 304, $beneficiaryCounts['2']->male ?? 0);
            $sheet->setCellValueByColumnAndRow(66, 305, $beneficiaryCounts['2']->female ?? 0);
            $sheet->setCellValueByColumnAndRow(73, 304, $beneficiaryCounts['2']->male ?? 0);
            $sheet->setCellValueByColumnAndRow(73, 305, $beneficiaryCounts['2']->female ?? 0);

            //For Grade 3
            $sheet->setCellValueByColumnAndRow(10, 307, $beneficiaryCounts['3']->male ?? 0);
            $sheet->setCellValueByColumnAndRow(10, 308, $beneficiaryCounts['3']->female ?? 0);
            $sheet->setCellValueByColumnAndRow(17, 307, $beneficiaryCounts['3']->male ?? 0);
            $sheet->setCellValueByColumnAndRow(17, 308, $beneficiaryCounts['3']->female ?? 0);
            $sheet->setCellValueByColumnAndRow(24, 307, $beneficiaryCounts['3']->male ?? 0);
            $sheet->setCellValueByColumnAndRow(24, 308, $beneficiaryCounts['3']->female ?? 0);
            $sheet->setCellValueByColumnAndRow(31, 307, $beneficiaryCounts['3']->male ?? 0);
            $sheet->setCellValueByColumnAndRow(31, 308, $beneficiaryCounts['3']->female ?? 0);
            $sheet->setCellValueByColumnAndRow(38, 307, $beneficiaryCounts['3']->male ?? 0);
            $sheet->setCellValueByColumnAndRow(38, 308, $beneficiaryCounts['3']->female ?? 0);
            $sheet->setCellValueByColumnAndRow(45, 307, $beneficiaryCounts['3']->male ?? 0);
            $sheet->setCellValueByColumnAndRow(45, 308, $beneficiaryCounts['3']->female ?? 0);
            $sheet->setCellValueByColumnAndRow(52, 307, $beneficiaryCounts['3']->male ?? 0);
            $sheet->setCellValueByColumnAndRow(52, 308, $beneficiaryCounts['3']->female ?? 0);
            $sheet->setCellValueByColumnAndRow(59, 307, $beneficiaryCounts['3']->male ?? 0);
            $sheet->setCellValueByColumnAndRow(59, 308, $beneficiaryCounts['3']->female ?? 0);
            $sheet->setCellValueByColumnAndRow(66, 307, $beneficiaryCounts['3']->male ?? 0);
            $sheet->setCellValueByColumnAndRow(66, 308, $beneficiaryCounts['3']->female ?? 0);
            $sheet->setCellValueByColumnAndRow(73, 307, $beneficiaryCounts['3']->male ?? 0);
            $sheet->setCellValueByColumnAndRow(73, 308, $beneficiaryCounts['3']->female ?? 0);

            //For Grade 4
            $sheet->setCellValueByColumnAndRow(10, 310, $beneficiaryCounts['4']->male ?? 0);
            $sheet->setCellValueByColumnAndRow(10, 311, $beneficiaryCounts['4']->female ?? 0);
            $sheet->setCellValueByColumnAndRow(17, 310, $beneficiaryCounts['4']->male ?? 0);
            $sheet->setCellValueByColumnAndRow(17, 311, $beneficiaryCounts['4']->female ?? 0);
            $sheet->setCellValueByColumnAndRow(24, 310, $beneficiaryCounts['4']->male ?? 0);
            $sheet->setCellValueByColumnAndRow(24, 311, $beneficiaryCounts['4']->female ?? 0);
            $sheet->setCellValueByColumnAndRow(31, 310, $beneficiaryCounts['4']->male ?? 0);
            $sheet->setCellValueByColumnAndRow(31, 311, $beneficiaryCounts['4']->female ?? 0);
            $sheet->setCellValueByColumnAndRow(38, 310, $beneficiaryCounts['4']->male ?? 0);
            $sheet->setCellValueByColumnAndRow(38, 311, $beneficiaryCounts['4']->female ?? 0);
            $sheet->setCellValueByColumnAndRow(45, 310, $beneficiaryCounts['4']->male ?? 0);
            $sheet->setCellValueByColumnAndRow(45, 311, $beneficiaryCounts['4']->female ?? 0);
            $sheet->setCellValueByColumnAndRow(52, 310, $beneficiaryCounts['4']->male ?? 0);
            $sheet->setCellValueByColumnAndRow(52, 311, $beneficiaryCounts['4']->female ?? 0);
            $sheet->setCellValueByColumnAndRow(59, 310, $beneficiaryCounts['4']->male ?? 0);
            $sheet->setCellValueByColumnAndRow(59, 311, $beneficiaryCounts['4']->female ?? 0);
            $sheet->setCellValueByColumnAndRow(66, 310, $beneficiaryCounts['4']->male ?? 0);
            $sheet->setCellValueByColumnAndRow(66, 311, $beneficiaryCounts['4']->female ?? 0);
            $sheet->setCellValueByColumnAndRow(73, 310, $beneficiaryCounts['4']->male ?? 0);
            $sheet->setCellValueByColumnAndRow(73, 311, $beneficiaryCounts['4']->female ?? 0);

            //For Grade 5
            $sheet->setCellValueByColumnAndRow(10, 313, $beneficiaryCounts['5']->male ?? 0);
            $sheet->setCellValueByColumnAndRow(10, 314, $beneficiaryCounts['5']->female ?? 0);
            $sheet->setCellValueByColumnAndRow(17, 313, $beneficiaryCounts['5']->male ?? 0);
            $sheet->setCellValueByColumnAndRow(17, 314, $beneficiaryCounts['5']->female ?? 0);
            $sheet->setCellValueByColumnAndRow(24, 313, $beneficiaryCounts['5']->male ?? 0);
            $sheet->setCellValueByColumnAndRow(24, 314, $beneficiaryCounts['5']->female ?? 0);
            $sheet->setCellValueByColumnAndRow(31, 313, $beneficiaryCounts['5']->male ?? 0);
            $sheet->setCellValueByColumnAndRow(31, 314, $beneficiaryCounts['5']->female ?? 0);
            $sheet->setCellValueByColumnAndRow(38, 313, $beneficiaryCounts['5']->male ?? 0);
            $sheet->setCellValueByColumnAndRow(38, 314, $beneficiaryCounts['5']->female ?? 0);
            $sheet->setCellValueByColumnAndRow(45, 313, $beneficiaryCounts['5']->male ?? 0);
            $sheet->setCellValueByColumnAndRow(45, 314, $beneficiaryCounts['5']->female ?? 0);
            $sheet->setCellValueByColumnAndRow(52, 313, $beneficiaryCounts['5']->male ?? 0);
            $sheet->setCellValueByColumnAndRow(52, 314, $beneficiaryCounts['5']->female ?? 0);
            $sheet->setCellValueByColumnAndRow(59, 313, $beneficiaryCounts['5']->male ?? 0);
            $sheet->setCellValueByColumnAndRow(59, 314, $beneficiaryCounts['5']->female ?? 0);
            $sheet->setCellValueByColumnAndRow(66, 313, $beneficiaryCounts['5']->male ?? 0);
            $sheet->setCellValueByColumnAndRow(66, 314, $beneficiaryCounts['5']->female ?? 0);
            $sheet->setCellValueByColumnAndRow(73, 313, $beneficiaryCounts['5']->male ?? 0);
            $sheet->setCellValueByColumnAndRow(73, 314, $beneficiaryCounts['5']->female ?? 0);

            //For Grade 6
            $sheet->setCellValueByColumnAndRow(10, 316, $beneficiaryCounts['6']->male ?? 0);
            $sheet->setCellValueByColumnAndRow(10, 317, $beneficiaryCounts['6']->female ?? 0);
            $sheet->setCellValueByColumnAndRow(17, 316, $beneficiaryCounts['6']->male ?? 0);
            $sheet->setCellValueByColumnAndRow(17, 317, $beneficiaryCounts['6']->female ?? 0);
            $sheet->setCellValueByColumnAndRow(24, 316, $beneficiaryCounts['6']->male ?? 0);
            $sheet->setCellValueByColumnAndRow(24, 317, $beneficiaryCounts['6']->female ?? 0);
            $sheet->setCellValueByColumnAndRow(31, 316, $beneficiaryCounts['6']->male ?? 0);
            $sheet->setCellValueByColumnAndRow(31, 317, $beneficiaryCounts['6']->female ?? 0);
            $sheet->setCellValueByColumnAndRow(38, 316, $beneficiaryCounts['6']->male ?? 0);
            $sheet->setCellValueByColumnAndRow(38, 317, $beneficiaryCounts['6']->female ?? 0);
            $sheet->setCellValueByColumnAndRow(45, 316, $beneficiaryCounts['6']->male ?? 0);
            $sheet->setCellValueByColumnAndRow(45, 317, $beneficiaryCounts['6']->female ?? 0);
            $sheet->setCellValueByColumnAndRow(52, 316, $beneficiaryCounts['6']->male ?? 0);
            $sheet->setCellValueByColumnAndRow(52, 317, $beneficiaryCounts['6']->female ?? 0);
            $sheet->setCellValueByColumnAndRow(59, 316, $beneficiaryCounts['6']->male ?? 0);
            $sheet->setCellValueByColumnAndRow(59, 317, $beneficiaryCounts['6']->female ?? 0);
            $sheet->setCellValueByColumnAndRow(66, 316, $beneficiaryCounts['6']->male ?? 0);
            $sheet->setCellValueByColumnAndRow(66, 317, $beneficiaryCounts['6']->female ?? 0);
            $sheet->setCellValueByColumnAndRow(73, 316, $beneficiaryCounts['6']->male ?? 0);
            $sheet->setCellValueByColumnAndRow(73, 317, $beneficiaryCounts['6']->female ?? 0);

            //For Non-graded
            $sheet->setCellValueByColumnAndRow(10, 319, $beneficiaryCounts['non_graded']->male ?? 0);
            $sheet->setCellValueByColumnAndRow(10, 320, $beneficiaryCounts['non_graded']->female ?? 0);
            $sheet->setCellValueByColumnAndRow(17, 319, $beneficiaryCounts['non_graded']->male ?? 0);
            $sheet->setCellValueByColumnAndRow(17, 320, $beneficiaryCounts['non_graded']->female ?? 0);
            $sheet->setCellValueByColumnAndRow(24, 319, $beneficiaryCounts['non_graded']->male ?? 0);
            $sheet->setCellValueByColumnAndRow(24, 320, $beneficiaryCounts['non_graded']->female ?? 0);
            $sheet->setCellValueByColumnAndRow(31, 319, $beneficiaryCounts['non_graded']->male ?? 0);
            $sheet->setCellValueByColumnAndRow(31, 320, $beneficiaryCounts['non_graded']->female ?? 0);
            $sheet->setCellValueByColumnAndRow(38, 319, $beneficiaryCounts['non_graded']->male ?? 0);
            $sheet->setCellValueByColumnAndRow(38, 320, $beneficiaryCounts['non_graded']->female ?? 0);
            $sheet->setCellValueByColumnAndRow(45, 319, $beneficiaryCounts['non_graded']->male ?? 0);
            $sheet->setCellValueByColumnAndRow(45, 320, $beneficiaryCounts['non_graded']->female ?? 0);
            $sheet->setCellValueByColumnAndRow(52, 319, $beneficiaryCounts['non_graded']->male ?? 0);
            $sheet->setCellValueByColumnAndRow(52, 320, $beneficiaryCounts['non_graded']->female ?? 0);
            $sheet->setCellValueByColumnAndRow(59, 319, $beneficiaryCounts['non_graded']->male ?? 0);
            $sheet->setCellValueByColumnAndRow(59, 320, $beneficiaryCounts['non_graded']->female ?? 0);
            $sheet->setCellValueByColumnAndRow(66, 319, $beneficiaryCounts['non_graded']->male ?? 0);
            $sheet->setCellValueByColumnAndRow(66, 320, $beneficiaryCounts['non_graded']->female ?? 0);
            $sheet->setCellValueByColumnAndRow(73, 319, $beneficiaryCounts['non_graded']->male ?? 0);
            $sheet->setCellValueByColumnAndRow(73, 320, $beneficiaryCounts['non_graded']->female ?? 0);





            //10. Completion rate table
            $maleSum = $beneficiaryCounts['non_graded']->male + $beneficiaryCounts['6']->male + $beneficiaryCounts['5']->male + $beneficiaryCounts['4']->male + $beneficiaryCounts['3']->male + $beneficiaryCounts['2']->male + $beneficiaryCounts['1']->male + $beneficiaryCounts['k']->male;
            $femaleSum = $beneficiaryCounts['non_graded']->female + $beneficiaryCounts['6']->female + $beneficiaryCounts['5']->female + $beneficiaryCounts['4']->female + $beneficiaryCounts['3']->female + $beneficiaryCounts['2']->female + $beneficiaryCounts['1']->female + $beneficiaryCounts['k']->female;


            $sheet->setCellValueByColumnAndRow(20, 330, $maleSum ?? 0);
            $sheet->setCellValueByColumnAndRow(23, 330, $femaleSum ?? 0);
            $sheet->setCellValueByColumnAndRow(20, 331, $maleSum ?? 0);
            $sheet->setCellValueByColumnAndRow(23, 331, $femaleSum ?? 0);





            // save spreadsheet to a new temp file so the original template remains unchanged
            $outFileName = 'Form7_filled_' . time() . '.xlsx';
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
            $outFileName = 'SNS_Elem_' . time() . '.xlsx';
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
            $outFileName = 'SNS_HighSchool_' . time() . '.xlsx';
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
