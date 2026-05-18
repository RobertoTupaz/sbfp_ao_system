<?php

namespace App\Livewire\Analytics;

use App\Models\NutritionalStatus;
use Livewire\Component;
use Illuminate\Support\Facades\DB;

class Base extends Component
{
    public $totalPupils        = 0;
    public $totalBeneficiaries = 0;
    public $maleCount          = 0;
    public $femaleCount        = 0;

    public $nutritionalStatusCounts = [];
    public $hfaCounts               = [];
    public $gradeCounts             = [];

    public $beneficiaryMaleCount        = 0;
    public $beneficiaryFemaleCount      = 0;
    public $beneficiaryNsCounts         = [];
    public $beneficiaryGradeCounts      = [];
    public $beneficiary4psCount         = 0;
    public $beneficiaryIpCount          = 0;
    public $beneficiaryPardoCount       = 0;

    public function mount()
    {
        $this->loadStats();
    }

    public function loadStats()
    {
        $this->totalPupils        = NutritionalStatus::count();
        $this->totalBeneficiaries = NutritionalStatus::where('isBeneficiary', true)->count();
        $this->maleCount          = NutritionalStatus::whereIn('sex', ['m', 'M', 'male', 'Male'])->count();
        $this->femaleCount        = NutritionalStatus::whereIn('sex', ['f', 'F', 'female', 'Female'])->count();

        $this->nutritionalStatusCounts = NutritionalStatus::query()
            ->select('nutritional_status', DB::raw('count(*) as total'))
            ->whereNotNull('nutritional_status')
            ->where('nutritional_status', '!=', '')
            ->groupBy('nutritional_status')
            ->orderByDesc('total')
            ->pluck('total', 'nutritional_status')
            ->toArray();

        $this->hfaCounts = NutritionalStatus::query()
            ->select('height_for_age', DB::raw('count(*) as total'))
            ->whereNotNull('height_for_age')
            ->where('height_for_age', '!=', '')
            ->groupBy('height_for_age')
            ->orderByDesc('total')
            ->pluck('total', 'height_for_age')
            ->toArray();

        $grades = array_merge(['k'], array_map('strval', range(1, 12)), ['non_graded']);

        $counts = NutritionalStatus::query()
            ->select('grade', DB::raw('count(*) as total'))
            ->whereIn('grade', $grades)
            ->groupBy('grade')
            ->pluck('total', 'grade')
            ->toArray();

        $this->gradeCounts = [];
        foreach ($grades as $g) {
            $this->gradeCounts[$g] = $counts[$g] ?? 0;
        }

        $benQ = NutritionalStatus::where('isBeneficiary', true);

        $this->beneficiaryMaleCount   = (clone $benQ)->whereIn('sex', ['m', 'M', 'male', 'Male'])->count();
        $this->beneficiaryFemaleCount = (clone $benQ)->whereIn('sex', ['f', 'F', 'female', 'Female'])->count();
        $this->beneficiary4psCount    = (clone $benQ)->where('_4ps', true)->count();
        $this->beneficiaryIpCount     = (clone $benQ)->where('ip', true)->count();
        $this->beneficiaryPardoCount  = (clone $benQ)->where('pardo', true)->count();

        $this->beneficiaryNsCounts = NutritionalStatus::query()
            ->where('isBeneficiary', true)
            ->select('nutritional_status', DB::raw('count(*) as total'))
            ->whereNotNull('nutritional_status')
            ->where('nutritional_status', '!=', '')
            ->groupBy('nutritional_status')
            ->orderByDesc('total')
            ->pluck('total', 'nutritional_status')
            ->toArray();

        $benGradeCounts = NutritionalStatus::query()
            ->where('isBeneficiary', true)
            ->select('grade', DB::raw('count(*) as total'))
            ->whereIn('grade', $grades)
            ->groupBy('grade')
            ->pluck('total', 'grade')
            ->toArray();

        $this->beneficiaryGradeCounts = [];
        foreach ($grades as $g) {
            $this->beneficiaryGradeCounts[$g] = $benGradeCounts[$g] ?? 0;
        }
    }

    public function render()
    {
        return view('livewire.analytics.base');
    }
}
