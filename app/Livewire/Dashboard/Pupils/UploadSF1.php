<?php

namespace App\Livewire\Dashboard\Pupils;

use App\Models\NutritionalStatus;
use Livewire\Component;
use Livewire\WithFileUploads;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Illuminate\Support\Facades\Storage;

class UploadSF1 extends Component
{
    use WithFileUploads;

    public $excel;
    public $rows = [];
    public $preview = false;

    protected $rules = [
        'excel' => 'required|file|mimes:xlsx,xls,csv'
    ];

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

        while (true) {
            $colA = trim((string) $sheet->getCell('A' . $row)->getValue());

            if ($colA < 2000 || $colA === null) {
                break;
            }

            $colC = trim((string) $sheet->getCell('C' . $row)->getValue());
            $colG = trim((string) $sheet->getCell('G' . $row)->getValue());
            $colH = trim((string) $sheet->getCell('H' . $row)->getValue());
            $colN = trim((string) $sheet->getCell('N' . $row)->getValue());

            $this->rows[] = [
                'A' => $colA,
                'C' => $colC,
                'G' => $colG,
                'H' => $colH,
                'N' => $colN,
                'row' => $row,
            ];



            $row++;
        }
    }

    public function save()
    {
        session()->flash('message', 'Imported successfully');
        return;
        // If rows already parsed (unlikely), use them. Otherwise parse from uploaded file directly.
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

            $grade = trim((string) $sheet->getCell('AE' . 4)->getValue());
            $section = trim((string) $sheet->getCell('AM' . 4)->getValue());

            $this->rows = [];

            $row = 7;
            while (true) {
                $colA = trim((string) $sheet->getCell('A' . $row)->getValue());
                if ($colA === '' || $colA === null) {
                    break;
                }

                $colC = trim((string) $sheet->getCell('C' . $row)->getValue());
                $colG = trim((string) $sheet->getCell('G' . $row)->getValue());
                $colH = trim((string) $sheet->getCell('H' . $row)->getValue());
                $colN = trim((string) $sheet->getCell('N' . $row)->getValue());

                $this->rows[] = [
                    'A' => $colA,
                    'C' => $colC,
                    'G' => $colG,
                    'H' => $colH,
                    'N' => $colN,
                    'row' => $row,
                ];

                /*
                                    [
                        'school_id' => auth()->user()->school_id,
                        'grade' => $grade,
                        'section' => $section,
                        'student_id' => $colA,
                    ],
                */

                NutritionalStatus::create(
                    [
                        'full_name' => $colC,
                        'last_name' => $colC,
                        'age_year' => $colG,
                        '' => $colH,
                        'weight' => $colN,
                    ]
                );

                $row++;
            }
        }

        $filename = 'uploads/sf1_' . now()->format('Ymd_His') . '.json';
        Storage::put($filename, json_encode($this->rows, JSON_PRETTY_PRINT));

        session()->flash('message', 'Imported rows saved to storage/' . $filename);

        // reset after save
        $this->excel = null;
        $this->rows = [];
        $this->preview = false;
    }
    public function render()
    {
        return view('livewire.dashboard.pupils.upload-s-f1');
    }
}
