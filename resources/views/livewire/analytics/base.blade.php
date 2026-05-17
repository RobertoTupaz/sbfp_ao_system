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
                    'severely wasted' => 'bg-red-500',
                    'wasted'          => 'bg-orange-400',
                    'normal'          => 'bg-green-500',
                    'overweight'      => 'bg-yellow-400',
                    'obese'           => 'bg-amber-500',
                ];
            @endphp
            @if($nsTotal > 0)
                <div class="space-y-3">
                    @foreach($nutritionalStatusCounts as $status => $count)
                        @php
                            $pct = round($count / $nsTotal * 100, 1);
                            $bar = $nsColors[strtolower($status)] ?? 'bg-gray-400';
                        @endphp
                        <div>
                            <div class="flex justify-between text-xs text-gray-600 mb-1">
                                <span class="font-medium">{{ ucfirst($status) }}</span>
                                <span>{{ number_format($count) }} <span class="text-gray-400">({{ $pct }}%)</span></span>
                            </div>
                            <div class="w-full bg-gray-100 rounded-full h-2.5">
                                <div class="{{ $bar }} h-2.5 rounded-full" style="width: {{ $pct }}%"></div>
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
                    'Severely Stunted' => 'bg-red-500',
                    'Stunted'          => 'bg-orange-400',
                    'Normal'           => 'bg-green-500',
                    'Tall'             => 'bg-blue-500',
                ];
            @endphp
            @if($hfaTotal > 0)
                <div class="space-y-3">
                    @foreach($hfaCounts as $status => $count)
                        @php
                            $pct = round($count / $hfaTotal * 100, 1);
                            $bar = $hfaColors[$status] ?? 'bg-gray-400';
                        @endphp
                        <div>
                            <div class="flex justify-between text-xs text-gray-600 mb-1">
                                <span class="font-medium">{{ $status }}</span>
                                <span>{{ number_format($count) }} <span class="text-gray-400">({{ $pct }}%)</span></span>
                            </div>
                            <div class="w-full bg-gray-100 rounded-full h-2.5">
                                <div class="{{ $bar }} h-2.5 rounded-full" style="width: {{ $pct }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-sm text-gray-400 py-4 text-center">No HFA data available.</div>
            @endif
        </div>

    </div>

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
