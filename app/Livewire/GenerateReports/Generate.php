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
            ])
            ->where('survey_state', $this->selectedStateGlobal ?: session('focal_selected_state', ''))
            ->where('school_year', $this->selectedYear ?: session('focal_selected_year', ''));

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

            // // Remove all extra sheets in template
            // while ($spreadsheet->getSheetCount() > 1) {
            //     $spreadsheet->removeSheetByIndex(1);
            // }

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
                $sheet->setCellValue('A2', 'Baseline generated for school: ' . $schoolId);
                $sheet->setCellValue('A3', $schoolName);

                // Keep template default text/structure intact when cloning.
                // Previously we cleared rows from row 4 onward which removed
                // the template's default labels and instructions. Skip that
                // clearing so each cloned sheet preserves the template text.

                // Fill school records
                $rowNumber = 4;
                $kinder = [
                    'male' => [
                        'severely_wasted' => 0,
                        'wasted' => 0,
                        'normal_weight' => 0,
                        'overweight' => 0,
                        'obese' => 0,
                        'severely_stunted' => 0,
                        'stunted' => 0,
                        'normal_height' => 0,
                        'tall' => 0,
                    ],
                    'female' => [
                        'severely_wasted' => 0,
                        'wasted' => 0,
                        'normal_weight' => 0,
                        'overweight' => 0,
                        'obese' => 0,
                        'severely_stunted' => 0,
                        'stunted' => 0,
                        'normal_height' => 0,
                        'tall' => 0,
                    ],
                ];

                $grade1 = [
                    'male' => [
                        'severely_wasted' => 0,
                        'wasted' => 0,
                        'normal_weight' => 0,
                        'overweight' => 0,
                        'obese' => 0,
                        'severely_stunted' => 0,
                        'stunted' => 0,
                        'normal_height' => 0,
                        'tall' => 0,
                    ],
                    'female' => [
                        'severely_wasted' => 0,
                        'wasted' => 0,
                        'normal_weight' => 0,
                        'overweight' => 0,
                        'obese' => 0,
                        'severely_stunted' => 0,
                        'stunted' => 0,
                        'normal_height' => 0,
                        'tall' => 0,
                    ],
                ];

                $grade2 = [
                    'male' => [
                        'severely_wasted' => 0,
                        'wasted' => 0,
                        'normal_weight' => 0,
                        'overweight' => 0,
                        'obese' => 0,
                        'severely_stunted' => 0,
                        'stunted' => 0,
                        'normal_height' => 0,
                        'tall' => 0,
                    ],
                    'female' => [
                        'severely_wasted' => 0,
                        'wasted' => 0,
                        'normal_weight' => 0,
                        'overweight' => 0,
                        'obese' => 0,
                        'severely_stunted' => 0,
                        'stunted' => 0,
                        'normal_height' => 0,
                        'tall' => 0,
                    ],
                ];

                $grade3 = [
                    'male' => [
                        'severely_wasted' => 0,
                        'wasted' => 0,
                        'normal_weight' => 0,
                        'overweight' => 0,
                        'obese' => 0,
                        'severely_stunted' => 0,
                        'stunted' => 0,
                        'normal_height' => 0,
                        'tall' => 0,
                    ],
                    'female' => [
                        'severely_wasted' => 0,
                        'wasted' => 0,
                        'normal_weight' => 0,
                        'overweight' => 0,
                        'obese' => 0,
                        'severely_stunted' => 0,
                        'stunted' => 0,
                        'normal_height' => 0,
                        'tall' => 0,
                    ],
                ];

                $grade4 = [
                    'male' => [
                        'severely_wasted' => 0,
                        'wasted' => 0,
                        'normal_weight' => 0,
                        'overweight' => 0,
                        'obese' => 0,
                        'severely_stunted' => 0,
                        'stunted' => 0,
                        'normal_height' => 0,
                        'tall' => 0,
                    ],
                    'female' => [
                        'severely_wasted' => 0,
                        'wasted' => 0,
                        'normal_weight' => 0,
                        'overweight' => 0,
                        'obese' => 0,
                        'severely_stunted' => 0,
                        'stunted' => 0,
                        'normal_height' => 0,
                        'tall' => 0,
                    ],
                ];

                $grade5 = [
                    'male' => [
                        'severely_wasted' => 0,
                        'wasted' => 0,
                        'normal_weight' => 0,
                        'overweight' => 0,
                        'obese' => 0,
                        'severely_stunted' => 0,
                        'stunted' => 0,
                        'normal_height' => 0,
                        'tall' => 0,
                    ],
                    'female' => [
                        'severely_wasted' => 0,
                        'wasted' => 0,
                        'normal_weight' => 0,
                        'overweight' => 0,
                        'obese' => 0,
                        'severely_stunted' => 0,
                        'stunted' => 0,
                        'normal_height' => 0,
                        'tall' => 0,
                    ],
                ];

                $grade6 = [
                    'male' => [
                        'severely_wasted' => 0,
                        'wasted' => 0,
                        'normal_weight' => 0,
                        'overweight' => 0,
                        'obese' => 0,
                        'severely_stunted' => 0,
                        'stunted' => 0,
                        'normal_height' => 0,
                        'tall' => 0,
                    ],
                    'female' => [
                        'severely_wasted' => 0,
                        'wasted' => 0,
                        'normal_weight' => 0,
                        'overweight' => 0,
                        'obese' => 0,
                        'severely_stunted' => 0,
                        'stunted' => 0,
                        'normal_height' => 0,
                        'tall' => 0,
                    ],
                ];

                $non_graded = [
                    'male' => [
                        'severely_wasted' => 0,
                        'wasted' => 0,
                        'normal_weight' => 0,
                        'overweight' => 0,
                        'obese' => 0,
                        'severely_stunted' => 0,
                        'stunted' => 0,
                        'normal_height' => 0,
                        'tall' => 0,
                    ],
                    'female' => [
                        'severely_wasted' => 0,
                        'wasted' => 0,
                        'normal_weight' => 0,
                        'overweight' => 0,
                        'obese' => 0,
                        'severely_stunted' => 0,
                        'stunted' => 0,
                        'normal_height' => 0,
                        'tall' => 0,
                    ],
                ];

                //Secondary beneficiaries
                //stunted and severely stunted
                $stunting = [
                    'male' => [
                        'severely_wasted' => 0,
                        'wasted' => 0,
                        'normal_weight' => 0,
                        'overweight' => 0,
                        'obese' => 0,
                        'severely_stunted' => 0,
                        'stunted' => 0,
                        'normal_height' => 0,
                        'tall' => 0,
                    ],
                    'female' => [
                        'severely_wasted' => 0,
                        'wasted' => 0,
                        'normal_weight' => 0,
                        'overweight' => 0,
                        'obese' => 0,
                        'severely_stunted' => 0,
                        'stunted' => 0,
                        'normal_height' => 0,
                        'tall' => 0,
                    ]
                ];

                $_4ps = [
                    'male' => [
                        'severely_wasted' => 0,
                        'wasted' => 0,
                        'normal_weight' => 0,
                        'overweight' => 0,
                        'obese' => 0,
                        'severely_stunted' => 0,
                        'stunted' => 0,
                        'normal_height' => 0,
                        'tall' => 0,
                    ],
                    'female' => [
                        'severely_wasted' => 0,
                        'wasted' => 0,
                        'normal_weight' => 0,
                        'overweight' => 0,
                        'obese' => 0,
                        'severely_stunted' => 0,
                        'stunted' => 0,
                        'normal_height' => 0,
                        'tall' => 0,
                    ]
                ];

                $ip = [
                    'male' => [
                        'severely_wasted' => 0,
                        'wasted' => 0,
                        'normal_weight' => 0,
                        'overweight' => 0,
                        'obese' => 0,
                        'severely_stunted' => 0,
                        'stunted' => 0,
                        'normal_height' => 0,
                        'tall' => 0,
                    ],
                    'female' => [
                        'severely_wasted' => 0,
                        'wasted' => 0,
                        'normal_weight' => 0,
                        'overweight' => 0,
                        'obese' => 0,
                        'severely_stunted' => 0,
                        'stunted' => 0,
                        'normal_height' => 0,
                        'tall' => 0,
                    ]
                ];

                $pardo = [
                    'male' => [
                        'severely_wasted' => 0,
                        'wasted' => 0,
                        'normal_weight' => 0,
                        'overweight' => 0,
                        'obese' => 0,
                        'severely_stunted' => 0,
                        'stunted' => 0,
                        'normal_height' => 0,
                        'tall' => 0,
                    ],
                    'female' => [
                        'severely_wasted' => 0,
                        'wasted' => 0,
                        'normal_weight' => 0,
                        'overweight' => 0,
                        'obese' => 0,
                        'severely_stunted' => 0,
                        'stunted' => 0,
                        'normal_height' => 0,
                        'tall' => 0,
                    ]
                ];

                foreach ($rows as $idx => $rec) {

                    if (strtolower(strtolower($rec->grade)) == 'k') {
                        if (strtolower($rec->sex) == 'm') {
                            if (strtolower($rec->bmi_a) == 'severely wasted') {
                                $kinder['male']['severely_wasted'] += 1;
                            } else if (strtolower($rec->bmi_a) == 'wasted') {
                                $kinder['male']['wasted'] += 1;
                            } else if (strtolower($rec->bmi_a) == 'normal weight') {
                                $kinder['male']['normal_weight'] += 1;
                            } else if (strtolower($rec->bmi_a) == 'overweight') {
                                $kinder['male']['overweight'] += 1;
                            } else if (strtolower($rec->bmi_a) == 'obese') {
                                $kinder['male']['obese'] += 1;
                            }

                            if (strtolower($rec->hfa) == 'severely stunted') {
                                $kinder['male']['severely_stunted'] += 1;
                            } else if (strtolower($rec->hfa) == 'stunted') {
                                $kinder['male']['stunted'] += 1;
                            } else if (strtolower($rec->hfa) == 'normal') {
                                $kinder['male']['normal_height'] += 1;
                            } else if (strtolower($rec->hfa) == 'tall') {
                                $kinder['male']['tall'] += 1;
                            }
                        } else {
                            if (strtolower($rec->bmi_a) == 'severely_wasted') {
                                $kinder['female']['severely_wasted'] += 1;
                            } else if (strtolower($rec->bmi_a) == 'wasted') {
                                $kinder['female']['wasted'] += 1;
                            } else if (strtolower($rec->bmi_a) == 'normal_weight') {
                                $kinder['female']['normal_weight'] += 1;
                            } else if (strtolower($rec->bmi_a) == 'overweight') {
                                $kinder['female']['overweight'] += 1;
                            } else if (strtolower($rec->bmi_a) == 'obese') {
                                $kinder['female']['obese'] += 1;
                            }

                            if (strtolower($rec->hfa) == 'severely_stunted') {
                                $kinder['female']['severely_stunted'] += 1;
                            } else if (strtolower($rec->hfa) == 'stunted') {
                                $kinder['female']['stunted'] += 1;
                            } else if (strtolower($rec->hfa) == 'normal_height') {
                                $kinder['female']['normal_height'] += 1;
                            } else if (strtolower($rec->hfa) == 'tall') {
                                $kinder['female']['tall'] += 1;
                            }
                        }
                    } else if (strtolower($rec->grade) == '1') {
                        if (strtolower($rec->sex) == 'm') {
                            if (strtolower($rec->bmi_a) == 'severely wasted') {
                                $grade1['male']['severely_wasted'] += 1;

                                if (strtolower($rec->hfa) == 'severely stunted') {
                                    $grade1['male']['severely_stunted'] += 1;
                                } else if (strtolower($rec->hfa) == 'stunted') {
                                    $grade1['male']['stunted'] += 1;
                                } else if (strtolower($rec->hfa) == 'normal') {
                                    $grade1['male']['normal_height'] += 1;
                                } else if (strtolower($rec->hfa) == 'tall') {
                                    $grade1['male']['tall'] += 1;
                                }
                            } else if (strtolower($rec->bmi_a) == 'wasted') {
                                $grade1['male']['wasted'] += 1;

                                if (strtolower($rec->hfa) == 'severely stunted') {
                                    $grade1['male']['severely_stunted'] += 1;
                                } else if (strtolower($rec->hfa) == 'stunted') {
                                    $grade1['male']['stunted'] += 1;
                                } else if (strtolower($rec->hfa) == 'normal') {
                                    $grade1['male']['normal_height'] += 1;
                                } else if (strtolower($rec->hfa) == 'tall') {
                                    $grade1['male']['tall'] += 1;
                                }
                            }
                        } else {
                            if (strtolower($rec->bmi_a) == 'severely_wasted') {
                                $grade1['female']['severely_wasted'] += 1;

                                if (strtolower($rec->hfa) == 'severely stunted') {
                                    $grade1['female']['severely_stunted'] += 1;
                                } else if (strtolower($rec->hfa) == 'stunted') {
                                    $grade1['female']['stunted'] += 1;
                                } else if (strtolower($rec->hfa) == 'normal') {
                                    $grade1['female']['normal_height'] += 1;
                                } else if (strtolower($rec->hfa) == 'tall') {
                                    $grade1['female']['tall'] += 1;
                                }
                            } else if (strtolower($rec->bmi_a) == 'wasted') {
                                $grade1['female']['wasted'] += 1;

                                if (strtolower($rec->hfa) == 'severely stunted') {
                                    $grade1['female']['severely_stunted'] += 1;
                                } else if (strtolower($rec->hfa) == 'stunted') {
                                    $grade1['female']['stunted'] += 1;
                                } else if (strtolower($rec->hfa) == 'normal') {
                                    $grade1['female']['normal_height'] += 1;
                                } else if (strtolower($rec->hfa) == 'tall') {
                                    $grade1['female']['tall'] += 1;
                                }
                            }
                        }
                    } else if (strtolower($rec->grade) == '2') {
                        if (strtolower($rec->sex) == 'm') {
                            if (strtolower($rec->bmi_a) == 'severely wasted') {
                                $grade2['male']['severely_wasted'] += 1;

                                if (strtolower($rec->hfa) == 'severely stunted') {
                                    $grade2['male']['severely_stunted'] += 1;
                                } else if (strtolower($rec->hfa) == 'stunted') {
                                    $grade2['male']['stunted'] += 1;
                                } else if (strtolower($rec->hfa) == 'normal') {
                                    $grade2['male']['normal_height'] += 1;
                                } else if (strtolower($rec->hfa) == 'tall') {
                                    $grade2['male']['tall'] += 1;
                                }
                            } else if (strtolower($rec->bmi_a) == 'wasted') {
                                $grade2['male']['wasted'] += 1;

                                if (strtolower($rec->hfa) == 'severely stunted') {
                                    $grade2['male']['severely_stunted'] += 1;
                                } else if (strtolower($rec->hfa) == 'stunted') {
                                    $grade2['male']['stunted'] += 1;
                                } else if (strtolower($rec->hfa) == 'normal') {
                                    $grade2['male']['normal_height'] += 1;
                                } else if (strtolower($rec->hfa) == 'tall') {
                                    $grade2['male']['tall'] += 1;
                                }
                            }
                        } else {
                            if (strtolower($rec->bmi_a) == 'severely wasted') {
                                $grade2['female']['severely_wasted'] += 1;

                                if (strtolower($rec->hfa) == 'severely stunted') {
                                    $grade2['female']['severely_stunted'] += 1;
                                } else if (strtolower($rec->hfa) == 'stunted') {
                                    $grade2['female']['stunted'] += 1;
                                } else if (strtolower($rec->hfa) == 'normal') {
                                    $grade2['female']['normal_height'] += 1;
                                } else if (strtolower($rec->hfa) == 'tall') {
                                    $grade2['female']['tall'] += 1;
                                }
                            } else if (strtolower($rec->bmi_a) == 'wasted') {
                                $grade2['female']['wasted'] += 1;

                                if (strtolower($rec->hfa) == 'severely stunted') {
                                    $grade2['female']['severely_stunted'] += 1;
                                } else if (strtolower($rec->hfa) == 'stunted') {
                                    $grade2['female']['stunted'] += 1;
                                } else if (strtolower($rec->hfa) == 'normal') {
                                    $grade2['female']['normal_height'] += 1;
                                } else if (strtolower($rec->hfa) == 'tall') {
                                    $grade2['female']['tall'] += 1;
                                }
                            }
                        }
                    } else if (strtolower($rec->grade) == '3') {
                        if (strtolower($rec->sex) == 'm') {
                            if (strtolower($rec->bmi_a) == 'severely wasted') {
                                $grade3['male']['severely_wasted'] += 1;

                                if (strtolower($rec->hfa) == 'severely stunted') {
                                    $grade3['male']['severely_stunted'] += 1;
                                } else if (strtolower($rec->hfa) == 'stunted') {
                                    $grade3['male']['stunted'] += 1;
                                } else if (strtolower($rec->hfa) == 'normal') {
                                    $grade3['male']['normal_height'] += 1;
                                } else if (strtolower($rec->hfa) == 'tall') {
                                    $grade3['male']['tall'] += 1;
                                }
                            } else if (strtolower($rec->bmi_a) == 'wasted') {
                                $grade3['male']['wasted'] += 1;

                                if (strtolower($rec->hfa) == 'severely stunted') {
                                    $grade3['male']['severely_stunted'] += 1;
                                } else if (strtolower($rec->hfa) == 'stunted') {
                                    $grade3['male']['stunted'] += 1;
                                } else if (strtolower($rec->hfa) == 'normal') {
                                    $grade3['male']['normal_height'] += 1;
                                } else if (strtolower($rec->hfa) == 'tall') {
                                    $grade3['male']['tall'] += 1;
                                }
                            }
                        } else {
                            if (strtolower($rec->bmi_a) == 'severely_wasted') {
                                $grade3['female']['severely_wasted'] += 1;

                                if (strtolower($rec->hfa) == 'severely_stunted') {
                                    $grade3['female']['severely_stunted'] += 1;
                                } else if (strtolower($rec->hfa) == 'stunted') {
                                    $grade3['female']['stunted'] += 1;
                                } else if (strtolower($rec->hfa) == 'normal') {
                                    $grade3['female']['normal_height'] += 1;
                                } else if (strtolower($rec->hfa) == 'tall') {
                                    $grade3['female']['tall'] += 1;
                                }
                            } else if (strtolower($rec->bmi_a) == 'wasted') {
                                $grade3['female']['wasted'] += 1;

                                if (strtolower($rec->hfa) == 'severely_stunted') {
                                    $grade3['female']['severely_stunted'] += 1;
                                } else if (strtolower($rec->hfa) == 'stunted') {
                                    $grade3['female']['stunted'] += 1;
                                } else if (strtolower($rec->hfa) == 'normal') {
                                    $grade3['female']['normal_height'] += 1;
                                } else if (strtolower($rec->hfa) == 'tall') {
                                    $grade3['female']['tall'] += 1;
                                }
                            }
                        }
                    } else if (strtolower($rec->grade) == '4') {
                        if (strtolower($rec->sex) == 'm') {
                            if (strtolower($rec->bmi_a) == 'severely wasted') {
                                $grade4['male']['severely_wasted'] += 1;

                                if (strtolower($rec->hfa) == 'severely_stunted') {
                                    $grade4['male']['severely_stunted'] += 1;
                                } else if (strtolower($rec->hfa) == 'stunted') {
                                    $grade4['male']['stunted'] += 1;
                                } else if (strtolower($rec->hfa) == 'normal') {
                                    $grade4['male']['normal_height'] += 1;
                                } else if (strtolower($rec->hfa) == 'tall') {
                                    $grade4['male']['tall'] += 1;
                                }
                            } else if (strtolower($rec->bmi_a) == 'wasted') {
                                $grade4['male']['wasted'] += 1;

                                if (strtolower($rec->hfa) == 'severely_stunted') {
                                    $grade4['male']['severely_stunted'] += 1;
                                } else if (strtolower($rec->hfa) == 'stunted') {
                                    $grade4['male']['stunted'] += 1;
                                } else if (strtolower($rec->hfa) == 'normal') {
                                    $grade4['male']['normal_height'] += 1;
                                } else if (strtolower($rec->hfa) == 'tall') {
                                    $grade4['male']['tall'] += 1;
                                }
                            }
                        } else {
                            if (strtolower($rec->bmi_a) == 'severely wasted') {
                                $grade4['female']['severely_wasted'] += 1;

                                if (strtolower($rec->hfa) == 'severely stunted') {
                                    $grade4['female']['severely_stunted'] += 1;
                                } else if (strtolower($rec->hfa) == 'stunted') {
                                    $grade4['female']['stunted'] += 1;
                                } else if (strtolower($rec->hfa) == 'normal') {
                                    $grade4['female']['normal_height'] += 1;
                                } else if (strtolower($rec->hfa) == 'tall') {
                                    $grade4['female']['tall'] += 1;
                                }
                            } else if (strtolower($rec->bmi_a) == 'wasted') {
                                $grade4['female']['wasted'] += 1;

                                if (strtolower($rec->hfa) == 'severely stunted') {
                                    $grade4['female']['severely_stunted'] += 1;
                                } else if (strtolower($rec->hfa) == 'stunted') {
                                    $grade4['female']['stunted'] += 1;
                                } else if (strtolower($rec->hfa) == 'normal') {
                                    $grade4['female']['normal_height'] += 1;
                                } else if (strtolower($rec->hfa) == 'tall') {
                                    $grade4['female']['tall'] += 1;
                                }
                            }
                        }
                    } else if (strtolower($rec->grade) == '5') {

                        if (strtolower($rec->sex) == 'm') {
                            if (strtolower($rec->bmi_a) == 'severely wasted') {
                                $grade5['male']['severely_wasted'] += 1;

                                if (strtolower($rec->hfa) == 'severely stunted') {
                                    $grade5['male']['severely_stunted'] += 1;
                                } else if (strtolower($rec->hfa) == 'stunted') {
                                    $grade5['male']['stunted'] += 1;
                                } else if (strtolower($rec->hfa) == 'normal') {
                                    $grade5['male']['normal_height'] += 1;
                                } else if (strtolower($rec->hfa) == 'tall') {
                                    $grade5['male']['tall'] += 1;
                                }
                            } else if (strtolower($rec->bmi_a) == 'wasted') {
                                $grade5['male']['wasted'] += 1;

                                if (strtolower($rec->hfa) == 'severely stunted') {
                                    $grade5['male']['severely_stunted'] += 1;
                                } else if (strtolower($rec->hfa) == 'stunted') {
                                    $grade5['male']['stunted'] += 1;
                                } else if (strtolower($rec->hfa) == 'normal') {
                                    $grade5['male']['normal_height'] += 1;
                                } else if (strtolower($rec->hfa) == 'tall') {
                                    $grade5['male']['tall'] += 1;
                                }
                            }
                        } else {
                            if (strtolower($rec->bmi_a) == 'severely wasted') {
                                $grade5['female']['severely_wasted'] += 1;

                                if (strtolower($rec->hfa) == 'severely stunted') {
                                    $grade5['female']['severely_stunted'] += 1;
                                } else if (strtolower($rec->hfa) == 'stunted') {
                                    $grade5['female']['stunted'] += 1;
                                } else if (strtolower($rec->hfa) == 'normal') {
                                    $grade5['female']['normal_height'] += 1;
                                } else if (strtolower($rec->hfa) == 'tall') {
                                    $grade5['female']['tall'] += 1;
                                }
                            } else if (strtolower($rec->bmi_a) == 'wasted') {
                                $grade5['female']['wasted'] += 1;

                                if (strtolower($rec->hfa) == 'severely stunted') {
                                    $grade5['female']['severely_stunted'] += 1;
                                } else if (strtolower($rec->hfa) == 'stunted') {
                                    $grade5['female']['stunted'] += 1;
                                } else if (strtolower($rec->hfa) == 'normal') {
                                    $grade5['female']['normal_height'] += 1;
                                } else if (strtolower($rec->hfa) == 'tall') {
                                    $grade5['female']['tall'] += 1;
                                }
                            }
                        }
                    } else if (strtolower($rec->grade) == '6') {
                        if (strtolower($rec->sex) == 'm') {
                            if (strtolower($rec->bmi_a) == 'severely wasted') {
                                $grade6['male']['severely_wasted'] += 1;

                                if (strtolower($rec->hfa) == 'severely stunted') {
                                    $grade6['male']['severely_stunted'] += 1;
                                } else if (strtolower($rec->hfa) == 'stunted') {
                                    $grade6['male']['stunted'] += 1;
                                } else if (strtolower($rec->hfa) == 'normal') {
                                    $grade6['male']['normal_height'] += 1;
                                } else if (strtolower($rec->hfa) == 'tall') {
                                    $grade6['male']['tall'] += 1;
                                }
                            } else if (strtolower($rec->bmi_a) == 'wasted') {
                                $grade6['male']['wasted'] += 1;

                                if (strtolower($rec->hfa) == 'severely stunted') {
                                    $grade6['male']['severely_stunted'] += 1;
                                } else if (strtolower($rec->hfa) == 'stunted') {
                                    $grade6['male']['stunted'] += 1;
                                } else if (strtolower($rec->hfa) == 'normal') {
                                    $grade6['male']['normal_height'] += 1;
                                } else if (strtolower($rec->hfa) == 'tall') {
                                    $grade6['male']['tall'] += 1;
                                }
                            }
                        } else {
                            if (strtolower($rec->bmi_a) == 'severely wasted') {
                                $grade6['female']['severely_wasted'] += 1;

                                if (strtolower($rec->hfa) == 'severely stunted') {
                                    $grade6['female']['severely_stunted'] += 1;
                                } else if (strtolower($rec->hfa) == 'stunted') {
                                    $grade6['female']['stunted'] += 1;
                                } else if (strtolower($rec->hfa) == 'normal') {
                                    $grade6['female']['normal_height'] += 1;
                                } else if (strtolower($rec->hfa) == 'tall') {
                                    $grade6['female']['tall'] += 1;
                                }
                            } else if (strtolower($rec->bmi_a) == 'wasted') {
                                $grade6['female']['wasted'] += 1;

                                if (strtolower($rec->hfa) == 'severely stunted') {
                                    $grade6['female']['severely_stunted'] += 1;
                                } else if (strtolower($rec->hfa) == 'stunted') {
                                    $grade6['female']['stunted'] += 1;
                                } else if (strtolower($rec->hfa) == 'normal') {
                                    $grade6['female']['normal_height'] += 1;
                                } else if (strtolower($rec->hfa) == 'tall') {
                                    $grade6['female']['tall'] += 1;
                                }
                            }
                        }
                    } else if (strtolower($rec->grade) == 'non_graded') {
                        if (strtolower($rec->sex) == 'm') {
                            if (strtolower($rec->bmi_a) == 'severely wasted') {
                                $non_graded['male']['severely_wasted'] += 1;

                                if (strtolower($rec->hfa) == 'severely stunted') {
                                    $non_graded['male']['severely_stunted'] += 1;
                                } else if (strtolower($rec->hfa) == 'stunted') {
                                    $non_graded['male']['stunted'] += 1;
                                } else if (strtolower($rec->hfa) == 'normal') {
                                    $non_graded['male']['normal_height'] += 1;
                                } else if (strtolower($rec->hfa) == 'tall') {
                                    $non_graded['male']['tall'] += 1;
                                }
                            } else if (strtolower($rec->bmi_a) == 'wasted') {
                                $non_graded['male']['wasted'] += 1;

                                if (strtolower($rec->hfa) == 'severely stunted') {
                                    $non_graded['male']['severely_stunted'] += 1;
                                } else if (strtolower($rec->hfa) == 'stunted') {
                                    $non_graded['male']['stunted'] += 1;
                                } else if (strtolower($rec->hfa) == 'normal') {
                                    $non_graded['male']['normal_height'] += 1;
                                } else if (strtolower($rec->hfa) == 'tall') {
                                    $non_graded['male']['tall'] += 1;
                                }
                            }
                        } else {
                            if (strtolower($rec->bmi_a) == 'severely wasted') {
                                $non_graded['female']['severely_wasted'] += 1;

                                if (strtolower($rec->hfa) == 'severely stunted') {
                                    $non_graded['female']['severely_stunted'] += 1;
                                } else if (strtolower($rec->hfa) == 'stunted') {
                                    $non_graded['female']['stunted'] += 1;
                                } else if (strtolower($rec->hfa) == 'normal') {
                                    $non_graded['female']['normal_height'] += 1;
                                } else if (strtolower($rec->hfa) == 'tall') {
                                    $non_graded['female']['tall'] += 1;
                                }
                            } else if (strtolower($rec->bmi_a) == 'wasted') {
                                $non_graded['female']['wasted'] += 1;

                                if (strtolower($rec->hfa) == 'severely stunted') {
                                    $non_graded['female']['severely_stunted'] += 1;
                                } else if (strtolower($rec->hfa) == 'stunted') {
                                    $non_graded['female']['stunted'] += 1;
                                } else if (strtolower($rec->hfa) == 'normal') {
                                    $non_graded['female']['normal_height'] += 1;
                                } else if (strtolower($rec->hfa) == 'tall') {
                                    $non_graded['female']['tall'] += 1;
                                }
                            }
                        }
                    }


                    //Secondary Beneficiaries
                    //Stunting Beneficiaries
                    if (strtolower($rec->grade) != 'k' && strtolower($rec->bmi_a) != 'severely wasted' && strtolower($rec->bmi_a) != 'wasted') {
                        if (strtolower($rec->sex) == 'm' && strtolower($rec->bmi_a) == 'normal' && strtolower($rec->hfa) == 'stunted') {
                            $stunting['male']['normal_weight'] += 1;
                            $stunting['male']['stunted'] += 1;
                        } else if (strtolower($rec->sex) == 'm' && strtolower($rec->bmi_a) == 'normal' && strtolower($rec->hfa) == 'severely stunted') {
                            $stunting['male']['normal_weight'] += 1;
                            $stunting['male']['severely_stunted'] += 1;
                        } else if (strtolower($rec->sex) == 'm' && strtolower($rec->bmi_a) == 'overweight' && strtolower($rec->hfa) == 'stunted') {
                            $stunting['male']['overweight'] += 1;
                            $stunting['male']['stunted'] += 1;
                        } else if (strtolower($rec->sex) == 'm' && strtolower($rec->bmi_a) == 'overweight' && strtolower($rec->hfa) == 'severely stunted') {
                            $stunting['male']['overweight'] += 1;
                            $stunting['male']['severely_stunted'] += 1;
                        }

                        if (strtolower($rec->sex) == 'f' && strtolower($rec->bmi_a) == 'normal' && strtolower($rec->hfa) == 'stunted') {
                            $stunting['female']['normal_weight'] += 1;
                            $stunting['female']['stunted'] += 1;
                        } else if (strtolower($rec->sex) == 'f' && strtolower($rec->bmi_a) == 'normal' && strtolower($rec->hfa) == 'severely stunted') {
                            $stunting['female']['normal_weight'] += 1;
                            $stunting['female']['severely_stunted'] += 1;
                        } else if (strtolower($rec->sex) == 'f' && strtolower($rec->bmi_a) == 'overweight' && strtolower($rec->hfa) == 'stunted') {
                            $stunting['female']['overweight'] += 1;
                            $stunting['female']['normal_height'] += 1;
                        } else if (strtolower($rec->sex) == 'f' && strtolower($rec->bmi_a) == 'overweight' && strtolower($rec->hfa) == 'severely stunted') {
                            $stunting['female']['overweight'] += 1;
                            $stunting['female']['severely_stunted'] += 1;
                        }
                    }

                    //IP
                    if (strtolower($rec->grade) != 'k' && strtolower($rec->bmi_a) != 'severely wasted' && strtolower($rec->bmi_a) != 'wasted') {
                        Log::info($rec->bmi_a);
                        if (strtolower($rec->sex) == 'm' && strtolower($rec->bmi_a) == 'normal' && strtolower($rec->hfa) == 'normal' && $rec->ip == true) {
                            $ip['male']['normal_height'] += 1;
                            $ip['male']['normal_weight'] += 1;
                            Log::info($ip['male']['normal_height']);
                        } else if (strtolower($rec->sex) == 'm' && strtolower($rec->bmi_a) == 'overweight' && strtolower($rec->hfa) == 'normal' && $rec->ip == true) {
                            $ip['male']['overweight'] += 1;
                            $ip['male']['normal_height'] += 1;
                        }

                        if (strtolower($rec->sex) == 'f' && strtolower($rec->bmi_a) == 'normal' && strtolower($rec->hfa) == 'normal' && $rec->ip == true) {
                            $ip['female']['normal_height'] += 1;
                            $ip['female']['normal_weight'] += 1;
                        } else if (strtolower($rec->sex) == 'f' && strtolower($rec->bmi_a) == 'overweight' && strtolower($rec->hfa) == 'normal' && $rec->ip == true) {
                            $ip['female']['overweight'] += 1;
                            $ip['female']['normal_height'] += 1;
                        }
                    }

                    //4ps
                    if (strtolower($rec->grade) != 'k' && strtolower($rec->bmi_a) != 'severely wasted' && strtolower($rec->bmi_a) != 'wasted') {
                        if (strtolower($rec->sex) == 'm' && strtolower($rec->bmi_a) == 'normal' && strtolower($rec->hfa) == 'normal' && $rec->ip == false && $rec->in_4ps == true) {
                            $_4ps['male']['normal_height'] += 1;
                            $_4ps['male']['normal_weight'] += 1;
                        } else if (strtolower($rec->sex) == 'm' && strtolower($rec->bmi_a) == 'overweight' && strtolower($rec->hfa) == 'normal' && $rec->ip == false && $rec->in_4ps == true) {
                            $_4ps['male']['overweight'] += 1;
                            $_4ps['male']['normal_height'] += 1;
                        }

                        if (strtolower($rec->sex) == 'f' && strtolower($rec->bmi_a) == 'normal' && strtolower($rec->hfa) == 'normal' && $rec->ip == false && $rec->in_4ps == true) {
                            $_4ps['female']['normal_height'] += 1;
                            $_4ps['female']['normal_weight'] += 1;
                        } else if (strtolower($rec->sex) == 'f' && strtolower($rec->bmi_a) == 'overweight' && strtolower($rec->hfa) == 'normal' && $rec->ip == false && $rec->in_4ps == true) {
                            $_4ps['female']['overweight'] += 1;
                            $_4ps['female']['normal_height'] += 1;
                        }
                    }

                    //Pardo
                    if (strtolower($rec->grade) != 'k' && strtolower($rec->bmi_a) != 'severely wasted' && strtolower($rec->bmi_a) != 'wasted') {
                        if (strtolower($rec->sex) == 'm' && strtolower($rec->bmi_a) == 'normal' && strtolower($rec->hfa) == 'normal' && $rec->ip == false && $rec->in_4ps == false && $rec->pardo == false) {
                            $pardo['male']['normal_height'] += 1;
                            $pardo['male']['normal_weight'] += 1;
                        } else if (strtolower($rec->sex) == 'm' && strtolower($rec->bmi_a) == 'overweight' && strtolower($rec->hfa) == 'normal' && $rec->ip == false && $rec->in_4ps == false && $rec->pardo == false) {
                            $pardo['male']['overweight'] += 1;
                            $pardo['male']['normal_height'] += 1;
                        }

                        if (strtolower($rec->sex) == 'f' && strtolower($rec->bmi_a) == 'normal' && strtolower($rec->hfa) == 'normal' && $rec->ip == false && $rec->in_4ps == false && $rec->pardo == false) {
                            $pardo['female']['normal_height'] += 1;
                            $pardo['female']['normal_weight'] += 1;
                        } else if (strtolower($rec->sex) == 'f' && strtolower($rec->bmi_a) == 'overweight' && strtolower($rec->hfa) == 'normal' && $rec->ip == false && $rec->in_4ps == false && $rec->pardo == false) {
                            $pardo['female']['overweight'] += 1;
                            $pardo['female']['normal_height'] += 1;
                        }
                    }
                }

                // Male counts
                $sheet->setCellValueByColumnAndRow(3, 10, $kinder['male']['severely_wasted']);
                $sheet->setCellValueByColumnAndRow(4, 10, $kinder['male']['wasted']);
                $sheet->setCellValueByColumnAndRow(5, 10, $kinder['male']['normal_weight']);
                $sheet->setCellValueByColumnAndRow(6, 10, $kinder['male']['overweight']);
                $sheet->setCellValueByColumnAndRow(7, 10, $kinder['male']['severely_stunted']);
                $sheet->setCellValueByColumnAndRow(8, 10, $kinder['male']['stunted']);
                $sheet->setCellValueByColumnAndRow(9, 10, $kinder['male']['normal_height']);
                $sheet->setCellValueByColumnAndRow(10, 10, $kinder['male']['tall']);

                // Female counts
                $sheet->setCellValueByColumnAndRow(3, 11, $kinder['female']['severely_wasted']);
                $sheet->setCellValueByColumnAndRow(4, 11, $kinder['female']['wasted']);
                $sheet->setCellValueByColumnAndRow(5, 11, $kinder['female']['normal_weight']);
                $sheet->setCellValueByColumnAndRow(6, 11, $kinder['female']['overweight']);
                $sheet->setCellValueByColumnAndRow(7, 11, $kinder['female']['severely_stunted']);
                $sheet->setCellValueByColumnAndRow(8, 11, $kinder['female']['stunted']);
                $sheet->setCellValueByColumnAndRow(9, 11, $kinder['female']['normal_height']);
                $sheet->setCellValueByColumnAndRow(10, 11, $kinder['female']['tall']);


                // Male counts
                $sheet->setCellValueByColumnAndRow(3, 13, $grade1['male']['severely_wasted']);
                $sheet->setCellValueByColumnAndRow(4, 13, $grade1['male']['wasted']);
                $sheet->setCellValueByColumnAndRow(5, 13, $grade1['male']['normal_weight']);
                $sheet->setCellValueByColumnAndRow(6, 13, $grade1['male']['overweight']);
                $sheet->setCellValueByColumnAndRow(7, 13, $grade1['male']['severely_stunted']);
                $sheet->setCellValueByColumnAndRow(8, 13, $grade1['male']['stunted']);
                $sheet->setCellValueByColumnAndRow(9, 13, $grade1['male']['normal_height']);
                $sheet->setCellValueByColumnAndRow(10, 13, $grade1['male']['tall']);

                // Female counts
                $sheet->setCellValueByColumnAndRow(3, 14, $grade1['female']['severely_wasted']);
                $sheet->setCellValueByColumnAndRow(4, 14, $grade1['female']['wasted']);
                $sheet->setCellValueByColumnAndRow(5, 14, $grade1['female']['normal_weight']);
                $sheet->setCellValueByColumnAndRow(6, 14, $grade1['female']['overweight']);
                $sheet->setCellValueByColumnAndRow(7, 14, $grade1['female']['severely_stunted']);
                $sheet->setCellValueByColumnAndRow(8, 14, $grade1['female']['stunted']);
                $sheet->setCellValueByColumnAndRow(9, 14, $grade1['female']['normal_height']);
                $sheet->setCellValueByColumnAndRow(10, 14, $grade1['female']['tall']);


                // Male counts
                $sheet->setCellValueByColumnAndRow(3, 16, $grade2['male']['severely_wasted']);
                $sheet->setCellValueByColumnAndRow(4, 16, $grade2['male']['wasted']);
                $sheet->setCellValueByColumnAndRow(5, 16, $grade2['male']['normal_weight']);
                $sheet->setCellValueByColumnAndRow(6, 16, $grade2['male']['overweight']);
                $sheet->setCellValueByColumnAndRow(7, 16, $grade2['male']['severely_stunted']);
                $sheet->setCellValueByColumnAndRow(8, 16, $grade2['male']['stunted']);
                $sheet->setCellValueByColumnAndRow(9, 16, $grade2['male']['normal_height']);
                $sheet->setCellValueByColumnAndRow(10, 16, $grade2['male']['tall']);

                // Female counts
                $sheet->setCellValueByColumnAndRow(3, 17, $grade2['female']['severely_wasted']);
                $sheet->setCellValueByColumnAndRow(4, 17, $grade2['female']['wasted']);
                $sheet->setCellValueByColumnAndRow(5, 17, $grade2['female']['normal_weight']);
                $sheet->setCellValueByColumnAndRow(6, 17, $grade2['female']['overweight']);
                $sheet->setCellValueByColumnAndRow(7, 17, $grade2['female']['severely_stunted']);
                $sheet->setCellValueByColumnAndRow(8, 17, $grade2['female']['stunted']);
                $sheet->setCellValueByColumnAndRow(9, 17, $grade2['female']['normal_height']);
                $sheet->setCellValueByColumnAndRow(10, 17, $grade2['female']['tall']);

                // Male counts
                $sheet->setCellValueByColumnAndRow(3, 19, $grade3['male']['severely_wasted']);
                $sheet->setCellValueByColumnAndRow(4, 19, $grade3['male']['wasted']);
                $sheet->setCellValueByColumnAndRow(5, 19, $grade3['male']['normal_weight']);
                $sheet->setCellValueByColumnAndRow(6, 19, $grade3['male']['overweight']);
                $sheet->setCellValueByColumnAndRow(7, 19, $grade3['male']['severely_stunted']);
                $sheet->setCellValueByColumnAndRow(8, 19, $grade3['male']['stunted']);
                $sheet->setCellValueByColumnAndRow(9, 19, $grade3['male']['normal_height']);
                $sheet->setCellValueByColumnAndRow(10, 19, $grade3['male']['tall']);

                // Female counts
                $sheet->setCellValueByColumnAndRow(3, 20, $grade3['female']['severely_wasted']);
                $sheet->setCellValueByColumnAndRow(4, 20, $grade3['female']['wasted']);
                $sheet->setCellValueByColumnAndRow(5, 20, $grade3['female']['normal_weight']);
                $sheet->setCellValueByColumnAndRow(6, 20, $grade3['female']['overweight']);
                $sheet->setCellValueByColumnAndRow(7, 20, $grade3['female']['severely_stunted']);
                $sheet->setCellValueByColumnAndRow(8, 20, $grade3['female']['stunted']);
                $sheet->setCellValueByColumnAndRow(9, 20, $grade3['female']['normal_height']);
                $sheet->setCellValueByColumnAndRow(10, 20, $grade3['female']['tall']);


                // Male counts
                $sheet->setCellValueByColumnAndRow(3, 22, $grade4['male']['severely_wasted']);
                $sheet->setCellValueByColumnAndRow(4, 22, $grade4['male']['wasted']);
                $sheet->setCellValueByColumnAndRow(5, 22, $grade4['male']['normal_weight']);
                $sheet->setCellValueByColumnAndRow(6, 22, $grade4['male']['overweight']);
                $sheet->setCellValueByColumnAndRow(7, 22, $grade4['male']['severely_stunted']);
                $sheet->setCellValueByColumnAndRow(8, 22, $grade4['male']['stunted']);
                $sheet->setCellValueByColumnAndRow(9, 22, $grade4['male']['normal_height']);
                $sheet->setCellValueByColumnAndRow(10, 22, $grade4['male']['tall']);

                // Female counts
                $sheet->setCellValueByColumnAndRow(3, 23, $grade4['female']['severely_wasted']);
                $sheet->setCellValueByColumnAndRow(4, 23, $grade4['female']['wasted']);
                $sheet->setCellValueByColumnAndRow(5, 23, $grade4['female']['normal_weight']);
                $sheet->setCellValueByColumnAndRow(6, 23, $grade4['female']['overweight']);
                $sheet->setCellValueByColumnAndRow(7, 23, $grade4['female']['severely_stunted']);
                $sheet->setCellValueByColumnAndRow(8, 23, $grade4['female']['stunted']);
                $sheet->setCellValueByColumnAndRow(9, 23, $grade4['female']['normal_height']);
                $sheet->setCellValueByColumnAndRow(10, 23, $grade4['female']['tall']);

                // Male counts
                $sheet->setCellValueByColumnAndRow(3, 25, $grade5['male']['severely_wasted']);
                $sheet->setCellValueByColumnAndRow(4, 25, $grade5['male']['wasted']);
                $sheet->setCellValueByColumnAndRow(5, 25, $grade5['male']['normal_weight']);
                $sheet->setCellValueByColumnAndRow(6, 25, $grade5['male']['overweight']);
                $sheet->setCellValueByColumnAndRow(7, 25, $grade5['male']['severely_stunted']);
                $sheet->setCellValueByColumnAndRow(8, 25, $grade5['male']['stunted']);
                $sheet->setCellValueByColumnAndRow(9, 25, $grade5['male']['normal_height']);
                $sheet->setCellValueByColumnAndRow(10, 25, $grade5['male']['tall']);

                // Female counts
                $sheet->setCellValueByColumnAndRow(3, 26, $grade5['female']['severely_wasted']);
                $sheet->setCellValueByColumnAndRow(4, 26, $grade5['female']['wasted']);
                $sheet->setCellValueByColumnAndRow(5, 26, $grade5['female']['normal_weight']);
                $sheet->setCellValueByColumnAndRow(6, 26, $grade5['female']['overweight']);
                $sheet->setCellValueByColumnAndRow(7, 26, $grade5['female']['severely_stunted']);
                $sheet->setCellValueByColumnAndRow(8, 26, $grade5['female']['stunted']);
                $sheet->setCellValueByColumnAndRow(9, 26, $grade5['female']['normal_height']);
                $sheet->setCellValueByColumnAndRow(10, 26, $grade5['female']['tall']);

                // Male counts
                $sheet->setCellValueByColumnAndRow(3, 28, $grade6['male']['severely_wasted']);
                $sheet->setCellValueByColumnAndRow(4, 28, $grade6['male']['wasted']);
                $sheet->setCellValueByColumnAndRow(5, 28, $grade6['male']['normal_weight']);
                $sheet->setCellValueByColumnAndRow(6, 28, $grade6['male']['overweight']);
                $sheet->setCellValueByColumnAndRow(7, 28, $grade6['male']['severely_stunted']);
                $sheet->setCellValueByColumnAndRow(8, 28, $grade6['male']['stunted']);
                $sheet->setCellValueByColumnAndRow(9, 28, $grade6['male']['normal_height']);
                $sheet->setCellValueByColumnAndRow(10, 28, $grade6['male']['tall']);

                // Female counts
                $sheet->setCellValueByColumnAndRow(3, 29, $grade6['female']['severely_wasted']);
                $sheet->setCellValueByColumnAndRow(4, 29, $grade6['female']['wasted']);
                $sheet->setCellValueByColumnAndRow(5, 29, $grade6['female']['normal_weight']);
                $sheet->setCellValueByColumnAndRow(6, 29, $grade6['female']['overweight']);
                $sheet->setCellValueByColumnAndRow(7, 29, $grade6['female']['severely_stunted']);
                $sheet->setCellValueByColumnAndRow(8, 29, $grade6['female']['stunted']);
                $sheet->setCellValueByColumnAndRow(9, 29, $grade6['female']['normal_height']);
                $sheet->setCellValueByColumnAndRow(10, 29, $grade6['female']['tall']);


                //Secondary Beneficiaries
                //Stunting Beneficiaries
                $sheet->setCellValueByColumnAndRow(5, 43, $stunting['male']['normal_height']);
                $sheet->setCellValueByColumnAndRow(6, 43, $stunting['male']['overweight']);
                $sheet->setCellValueByColumnAndRow(7, 43, $stunting['male']['stunted']);
                $sheet->setCellValueByColumnAndRow(8, 43, $stunting['male']['severely_stunted']);

                $sheet->setCellValueByColumnAndRow(5, 44, $stunting['female']['normal_height']);
                $sheet->setCellValueByColumnAndRow(6, 44, $stunting['female']['overweight']);
                $sheet->setCellValueByColumnAndRow(7, 44, $stunting['female']['stunted']);
                $sheet->setCellValueByColumnAndRow(8, 44, $stunting['female']['severely_stunted']);

                //IP
                $sheet->setCellValueByColumnAndRow(5, 52, $ip['male']['normal_weight']);
                $sheet->setCellValueByColumnAndRow(6, 52, $ip['male']['overweight']);
                $sheet->setCellValueByColumnAndRow(9, 52, $ip['male']['normal_height']);
                $sheet->setCellValueByColumnAndRow(10, 52, $ip['male']['tall']);

                $sheet->setCellValueByColumnAndRow(5, 53, $ip['female']['normal_weight']);
                $sheet->setCellValueByColumnAndRow(6, 53, $ip['female']['overweight']);
                $sheet->setCellValueByColumnAndRow(9, 53, $ip['female']['normal_height']);
                $sheet->setCellValueByColumnAndRow(10, 53, $ip['female']['tall']);

                //4ps
                $sheet->setCellValueByColumnAndRow(5, 49, $_4ps['male']['normal_weight']);
                $sheet->setCellValueByColumnAndRow(6, 49, $_4ps['male']['overweight']);
                $sheet->setCellValueByColumnAndRow(9, 49, $_4ps['male']['normal_height']);
                $sheet->setCellValueByColumnAndRow(10, 49, $_4ps['male']['tall']);

                $sheet->setCellValueByColumnAndRow(5, 50, $_4ps['female']['normal_weight']);
                $sheet->setCellValueByColumnAndRow(6, 50, $_4ps['female']['overweight']);
                $sheet->setCellValueByColumnAndRow(9, 50, $_4ps['female']['normal_height']);
                $sheet->setCellValueByColumnAndRow(10, 50, $_4ps['female']['tall']);

                //pardo
                $sheet->setCellValueByColumnAndRow(5, 46, $pardo['male']['normal_weight']);
                $sheet->setCellValueByColumnAndRow(6, 46, $pardo['male']['overweight']);
                $sheet->setCellValueByColumnAndRow(9, 46, $pardo['male']['normal_height']);
                $sheet->setCellValueByColumnAndRow(10, 46, $pardo['male']['tall']);

                $sheet->setCellValueByColumnAndRow(5, 47, $pardo['female']['normal_weight']);
                $sheet->setCellValueByColumnAndRow(6, 47, $pardo['female']['overweight']);
                $sheet->setCellValueByColumnAndRow(9, 47, $pardo['female']['normal_height']);
                $sheet->setCellValueByColumnAndRow(10, 47, $pardo['female']['tall']);
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
