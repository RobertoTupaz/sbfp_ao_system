<div class="space-y-6">

    {{-- Summary cards --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <div class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1">Total Pupils</div>
            <div class="text-3xl font-bold text-gray-800">{{ number_format($totalPupils) }}</div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <div class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1">Beneficiaries</div>
            <div class="text-3xl font-bold text-blue-600">{{ number_format($totalBeneficiaries) }}</div>
            @if($totalPupils > 0)
                <div class="text-xs text-gray-400 mt-1">{{ round($totalBeneficiaries / $totalPupils * 100, 1) }}% of total</div>
            @endif
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <div class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1">Male</div>
            <div class="text-3xl font-bold text-indigo-600">{{ number_format($maleCount) }}</div>
            @if($totalPupils > 0)
                <div class="text-xs text-gray-400 mt-1">{{ round($maleCount / $totalPupils * 100, 1) }}%</div>
            @endif
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <div class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1">Female</div>
            <div class="text-3xl font-bold text-pink-500">{{ number_format($femaleCount) }}</div>
            @if($totalPupils > 0)
                <div class="text-xs text-gray-400 mt-1">{{ round($femaleCount / $totalPupils * 100, 1) }}%</div>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        {{-- Nutritional Status --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <div class="flex items-center gap-1.5 mb-4">
                <h3 class="text-sm font-semibold text-gray-700">Nutritional Status (BMI)</h3>
                <x-feature-help>Distribution of pupils across BMI-for-age nutritional status categories based on WHO reference tables.</x-feature-help>
            </div>
            @php
                $nsTotal = array_sum($nutritionalStatusCounts);
                $nsColors = [
                    'severely wasted' => '#ef4444',
                    'wasted'          => '#fb923c',
                    'normal'          => '#22c55e',
                    'overweight'      => '#facc15',
                    'obese'           => '#f59e0b',
                ];
            @endphp
            @if($nsTotal > 0)
                <div class="space-y-3">
                    @foreach($nutritionalStatusCounts as $status => $count)
                        @php
                            $pct = round($count / $nsTotal * 100, 1);
                            $color = $nsColors[strtolower($status)] ?? '#9ca3af';
                        @endphp
                        <div>
                            <div class="flex justify-between text-xs text-gray-600 mb-1">
                                <span class="font-medium">{{ ucfirst($status) }}</span>
                                <span>{{ number_format($count) }} <span class="text-gray-400">({{ $pct }}%)</span></span>
                            </div>
                            <div class="w-full bg-gray-100 rounded-full h-2.5">
                                <div class="h-2.5 rounded-full" style="width: {{ $pct }}%; background-color: {{ $color }};"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-sm text-gray-400 py-4 text-center">No nutritional status data available.</div>
            @endif
        </div>

        {{-- Height for Age --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <div class="flex items-center gap-1.5 mb-4">
                <h3 class="text-sm font-semibold text-gray-700">Height for Age (HFA)</h3>
                <x-feature-help>Distribution of pupils across height-for-age categories based on WHO reference tables.</x-feature-help>
            </div>
            @php
                $hfaTotal = array_sum($hfaCounts);
                $hfaColors = [
                    'Severely Stunted' => '#ef4444',
                    'Stunted'          => '#fb923c',
                    'Normal'           => '#22c55e',
                    'Tall'             => '#3b82f6',
                ];
            @endphp
            @if($hfaTotal > 0)
                <div class="space-y-3">
                    @foreach($hfaCounts as $status => $count)
                        @php
                            $pct = round($count / $hfaTotal * 100, 1);
                            $color = $hfaColors[$status] ?? '#9ca3af';
                        @endphp
                        <div>
                            <div class="flex justify-between text-xs text-gray-600 mb-1">
                                <span class="font-medium">{{ $status }}</span>
                                <span>{{ number_format($count) }} <span class="text-gray-400">({{ $pct }}%)</span></span>
                            </div>
                            <div class="w-full bg-gray-100 rounded-full h-2.5">
                                <div class="h-2.5 rounded-full" style="width: {{ $pct }}%; background-color: {{ $color }};"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-sm text-gray-400 py-4 text-center">No HFA data available.</div>
            @endif
        </div>

    </div>

    {{-- Beneficiary Analytics --}}
    @if($totalBeneficiaries > 0)
    <div class="border-t border-gray-200 pt-6">
        <h3 class="text-sm font-bold text-gray-700 mb-4 uppercase tracking-wide">Beneficiary Breakdown</h3>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

            {{-- Gender & Special Categories --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                <h4 class="text-sm font-semibold text-gray-700 mb-4">Gender & Special Categories</h4>
                <div class="space-y-3">
                    @php
                        $benItems = [
                            ['label' => 'Male',   'count' => $beneficiaryMaleCount,   'color' => '#6366f1'],
                            ['label' => 'Female', 'count' => $beneficiaryFemaleCount, 'color' => '#ec4899'],
                            ['label' => '4Ps',    'count' => $beneficiary4psCount,    'color' => '#60a5fa'],
                            ['label' => 'IP',     'count' => $beneficiaryIpCount,     'color' => '#14b8a6'],
                            ['label' => 'PARDO',  'count' => $beneficiaryPardoCount,  'color' => '#f59e0b'],
                        ];
                    @endphp
                    @foreach($benItems as $item)
                        @php $pct = $totalBeneficiaries > 0 ? round($item['count'] / $totalBeneficiaries * 100, 1) : 0; @endphp
                        <div>
                            <div class="flex justify-between text-xs text-gray-600 mb-1">
                                <span class="font-medium">{{ $item['label'] }}</span>
                                <span>{{ number_format($item['count']) }} <span class="text-gray-400">({{ $pct }}%)</span></span>
                            </div>
                            <div class="w-full bg-gray-100 rounded-full h-2.5">
                                <div class="h-2.5 rounded-full" style="width: {{ $pct }}%; background-color: {{ $item['color'] }};"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Nutritional Status of Beneficiaries --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                <h4 class="text-sm font-semibold text-gray-700 mb-4">Nutritional Status (Beneficiaries)</h4>
                @php
                    $benNsTotal = array_sum($beneficiaryNsCounts);
                    $nsColors = [
                        'severely wasted' => '#ef4444',
                        'wasted'          => '#fb923c',
                        'normal'          => '#22c55e',
                        'overweight'      => '#facc15',
                        'obese'           => '#f59e0b',
                    ];
                @endphp
                @if($benNsTotal > 0)
                    <div class="space-y-3">
                        @foreach($beneficiaryNsCounts as $status => $count)
                            @php
                                $pct = round($count / $benNsTotal * 100, 1);
                                $color = $nsColors[strtolower($status)] ?? '#9ca3af';
                            @endphp
                            <div>
                                <div class="flex justify-between text-xs text-gray-600 mb-1">
                                    <span class="font-medium">{{ ucfirst($status) }}</span>
                                    <span>{{ number_format($count) }} <span class="text-gray-400">({{ $pct }}%)</span></span>
                                </div>
                                <div class="w-full bg-gray-100 rounded-full h-2.5">
                                    <div class="h-2.5 rounded-full" style="width: {{ $pct }}%; background-color: {{ $color }};"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-sm text-gray-400 py-4 text-center">No nutritional status data for beneficiaries.</div>
                @endif
            </div>

        </div>

        {{-- Beneficiaries by Grade --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 mt-6">
            <h4 class="text-sm font-semibold text-gray-700 mb-4">Beneficiaries by Grade</h4>
            @php $benGradeMax = max(array_values($beneficiaryGradeCounts) ?: [1]); @endphp
            <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-7 gap-3">
                @foreach($beneficiaryGradeCounts as $grade => $count)
                    @php $pct = $benGradeMax > 0 ? round($count / $benGradeMax * 100) : 0; @endphp
                    <div class="flex flex-col items-center gap-1">
                        <div class="w-full bg-gray-100 rounded-lg overflow-hidden" style="height:80px; display:flex; align-items:flex-end;">
                            <div class="w-full bg-blue-500 rounded-t-lg transition-all" style="height:{{ max($pct, $count > 0 ? 4 : 0) }}%"></div>
                        </div>
                        <div class="text-xs font-semibold text-gray-700">{{ number_format($count) }}</div>
                        <div class="text-xs text-gray-400">
                            {{ $grade === 'k' ? 'K' : ($grade === 'non_graded' ? 'NG' : 'G'.$grade) }}
                        </div>
                    </div>
                @endforeach
            </div>
            <div class="text-xs text-gray-400 mt-2">K = Kinder &nbsp;&middot;&nbsp; NG = Non-graded &nbsp;&middot;&nbsp; G1–G12 = Grade 1–12</div>
        </div>
    </div>
    @endif

    {{-- Grade distribution --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
        <div class="flex items-center gap-1.5 mb-4">
            <h3 class="text-sm font-semibold text-gray-700">Pupils by Grade</h3>
            <x-feature-help>Number of enrolled pupils per grade level.</x-feature-help>
        </div>
        @php $gradeMax = max(array_values($gradeCounts) ?: [1]); @endphp
        <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-7 gap-3">
            @foreach($gradeCounts as $grade => $count)
                @php $pct = $gradeMax > 0 ? round($count / $gradeMax * 100) : 0; @endphp
                <div class="flex flex-col items-center gap-1">
                    <div class="w-full bg-gray-100 rounded-lg overflow-hidden" style="height:80px; display:flex; align-items:flex-end;">
                        <div class="w-full bg-indigo-500 rounded-t-lg transition-all" style="height:{{ max($pct, $count > 0 ? 4 : 0) }}%"></div>
                    </div>
                    <div class="text-xs font-semibold text-gray-700">{{ number_format($count) }}</div>
                    <div class="text-xs text-gray-400">
                        {{ $grade === 'k' ? 'K' : ($grade === 'non_graded' ? 'NG' : 'G'.$grade) }}
                    </div>
                </div>
            @endforeach
        </div>
        <div class="text-xs text-gray-400 mt-2">K = Kinder &nbsp;&middot;&nbsp; NG = Non-graded &nbsp;&middot;&nbsp; G1–G12 = Grade 1–12</div>
    </div>

</div>
