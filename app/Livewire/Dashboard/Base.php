<?php

namespace App\Livewire\Dashboard;

use App\Models\District;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithFileUploads;

class Base extends Component
{
    public $showSchoolCard = false;
    use WithFileUploads;

    /**
     * Livewire event listeners.
     */

    public $districts;
    public $district_id;
    public $schools = [];
    public $school_id;
    public $excel_file;
    public $nfp_file;
    public $milk_file;

    #[On('excel-uploaded')]
    public function showSuccessAlert()
    {
        $this->showSchoolCard = true;
        $this->js(<<<JS
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: 'Excel imported successfully!',
                timer: 2500,
                showConfirmButton: false
            });
        JS);
    }

    #[On('excel-upload-error')]
    public function showErrorAlert($message = 'Excel import failed!')
    {
        Log::error($message);
        $this->js(<<<JS
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: '{$message}',
                timer: 3500,
                showConfirmButton: true
            });
        JS);
    }

    public function mount()
    {
    $this->districts = District::all();
    $this->schools = [];
    }

    public function userDistrictSelection($districtId) {
        $this->district_id = $districtId;
        $this->showSchoolCard = false;
        // Load schools for selected district
        if ($districtId) {
            $this->schools = \App\Models\School::where('district_id', $districtId)->get();
        } else {
            $this->schools = [];
        }
    }

    public function userSchoolSelection($schoolId) {
        $this->school_id = $schoolId;
        $this->showSchoolCard = false;
    }

    public function uploadExcel()
    {
        try {
            $this->validate([
                'district_id' => 'required|exists:district,id',
                'school_id' => 'required|exists:school,id',
                'nfp_file' => 'required|file|mimes:xlsx,xls',
            ]);

            $path = $this->nfp_file->store('temp');
            $fullPath = storage_path('app/' . $path);

            \Maatwebsite\Excel\Facades\Excel::import(new class($this->school_id) implements \Maatwebsite\Excel\Concerns\ToCollection {
                protected $school_id;
                
                public function __construct($school_id) {
                    $this->school_id = $school_id;
                }

                public function collection($rows)
                {
                    foreach ($rows as $i => $row) {
                        if ($i < 13) continue; // skip first 13 rows, start at row 14 (index 13)
                        $data = $row->toArray();
                        if (empty($data[1])) continue; // skip empty name

                        // Clean up name (remove extra commas/spaces)
                        $name = trim(preg_replace('/\s*,\s*/', ' ', $data[1] ?? ''));

                        // Convert Excel serial dates to PHP date format and clean up date strings
                        foreach ([4, 5] as $dateIdx) {
                            if (isset($data[$dateIdx]) && is_numeric($data[$dateIdx]) && $data[$dateIdx] > 30000) {
                                $dt = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($data[$dateIdx]);
                                $data[$dateIdx] = $dt ? $dt->format('Y-m-d') : $data[$dateIdx];
                            } elseif (isset($data[$dateIdx]) && is_string($data[$dateIdx])) {
                                $str = preg_replace('/\s*-\s*/', '-', $data[$dateIdx]);
                                // Convert M/D/YYYY or MM/DD/YYYY or MM-DD-YYYY to Y-m-d
                                if (preg_match('/^(\d{1,2})[\/\-](\d{1,2})[\/\-](\d{4})$/', $str, $m)) {
                                    $data[$dateIdx] = sprintf('%04d-%02d-%02d', $m[3], $m[1], $m[2]);
                                } else {
                                    $data[$dateIdx] = $str;
                                }
                            }
                        }

                        // Ensure height is decimal
                        $height = isset($data[8]) ? floatval(preg_replace('/[^\d.]/', '', $data[8])) : null;

                        \App\Models\Form_1::create([
                            'school_id' => $this->school_id,
                            'name' => $name,
                            'sex' => $data[2] ?? '',
                            'grade_section' => $data[3] ?? '',
                            'date_of_birth' => $data[4] ?? null,
                            'date_of_weighing_or_measuring' => $data[5] ?? null,
                            'age_in_years_or_months' => $data[6] ?? '',
                            'weight' => $data[7] ?? null,
                            'height' => $height,
                            'bmi_for_6_years_and_above' => $data[9] ?? '',
                            'bmi_a' => $data[10] ?? '',
                            'bmi_b' => $data[11] ?? '',
                            'parents_consent_for_milk' => !empty($data[12]),
                            'participation_in_4ps' => !empty($data[13]),
                            'beneficiary_of_sbfp_in_previous_year' => !empty($data[14]),
                        ]);
                    }
                }
            }, $fullPath);

            $this->dispatch('excel-uploaded');
        } catch (\Throwable $e) {
            $msg = $e->getMessage();
            $this->dispatch('excel-upload-error', message: $msg);
        }
    }

    public function testExcelRead()
    {
        $this->validate([
            'excel_file' => 'required|file|mimes:xlsx,xls',
        ]);

        $path = $this->excel_file->store('temp');
        $fullPath = storage_path('app/' . $path);


        \Maatwebsite\Excel\Facades\Excel::import(new class implements \Maatwebsite\Excel\Concerns\ToCollection {
            public function collection($rows)
            {
                foreach ($rows as $i => $row) {
                    if ($i < 13) continue; // skip first 13 rows, start at row 14 (index 13)
                    $data = $row->toArray();
                    // Convert Excel serial dates to PHP date format
                    foreach ([4, 5] as $dateIdx) {
                        if (isset($data[$dateIdx]) && is_numeric($data[$dateIdx]) && $data[$dateIdx] > 30000) {
                            $dt = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($data[$dateIdx]);
                            $data[$dateIdx] = $dt ? $dt->format('Y-m-d') : $data[$dateIdx];
                        }
                    }
                    logger()->info('Excel Row', ['row' => $data]);
                }
            }
        }, $fullPath);

        session()->flash('success', 'Excel rows logged to Laravel log!');
    }

    public function render()
    {
        return view('livewire.dashboard.base');
    }
}
