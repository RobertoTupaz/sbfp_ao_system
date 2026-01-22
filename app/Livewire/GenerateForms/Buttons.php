<?php

namespace App\Livewire\GenerateForms;

use App\Models\Beneficiaries;
use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\NutritionalStatus;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Maatwebsite\Excel\Facades\Excel;

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

            // starting row in the template
            $startRow = 13;
            $endRow = 0;

            // fetch records from database
            $records = NutritionalStatus::orderBy('id')->get();

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
            $sheet->setCellValue("M{$footerRow}", 'Prepared by :');
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
            $outFile = public_path('exel/' . $outFileName);
            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
            $writer->save($outFile);

            session()->flash('success', 'Form1.xlsx generated for download.');
            Log::info($outFileName . ' successfully written with ' . $records->count() . ' records.');

            // dispatch browser event to trigger download of the generated public file
            $downloadUrl = asset('exel/' . $outFileName);
            $this->dispatch('form1-ready', $downloadUrl);
            Log::info('Form1 download URL dispatched: ' . $downloadUrl);
        } catch (\Throwable $e) {
            Log::error('Error writing Form1.xlsx: ' . $e->getMessage());
            session()->flash('error', 'Failed to update Form1.xlsx: ' . $e->getMessage());
        }
    }

    public function generateJson()
    {
        try {
            $records = NutritionalStatus::orderBy('id')->get();
            $json = $records->toJson(JSON_PRETTY_PRINT);

            $outFileName = 'nutritional_statuses_' . time() . '.json';
            $outFile = public_path('exel/' . $outFileName);

            if (file_put_contents($outFile, $json) === false) {
                throw new \Exception('Failed to write JSON file');
            }

            session()->flash('success', 'nutritional_statuses JSON generated for download.');
            Log::info($outFileName . ' successfully written with ' . $records->count() . ' records.');

            $downloadUrl = asset('exel/' . $outFileName);
            $this->dispatch('json-ready', $downloadUrl);
            Log::info('JSON download URL dispatched: ' . $downloadUrl);
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
            Log::info('Imported nutritional_statuses JSON: ' . $count . ' records.');
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
            Log::info('Beneficiaries count saved: ' . $this->beneficiariesCount);
            $this->dispatch('beneficiaries-saved', ['count' => $this->beneficiariesCount]);
        } catch (\Throwable $e) {
            Log::error('Error saving beneficiaries count: ' . $e->getMessage());
            session()->flash('error', 'Failed to save beneficiaries count: ' . $e->getMessage());
        }
    }
}
