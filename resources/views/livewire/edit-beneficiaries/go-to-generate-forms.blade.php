<div>
    <div style="width:100%;display:flex;justify-content:center;margin-top:14px;">
        <div style="max-width:900px;width:100%;display:flex;justify-content:center;">
            @if($hasBeneficiaries)
                <a href="{{ route('generate_forms') }}" class="text-sm text-white dark:text-gray-500 underline">
                    <button class="px-4 py-2 bg-indigo-600 text-white rounded">
                        {{ __('Generate Forms') }}
                    </button>
                </a>
            @endif
        </div>
    </div>
</div>
