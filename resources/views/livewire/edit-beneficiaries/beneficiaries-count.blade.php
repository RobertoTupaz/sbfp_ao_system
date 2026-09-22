<div>
@unless($beneficiariesSet)
<div class="mb-4">
    <div class="flex items-center gap-1.5 mb-2">
        <label class="block text-sm font-medium text-gray-700">Beneficiaries count</label>
        <x-feature-help>Set the total number of pupils to be selected as SBFP beneficiaries for this school. This count determines how many pupils the automatic selection algorithm will pick when you click "Set Beneficiaries".</x-feature-help>
    </div>
    <div class="flex gap-2 items-center max-w-sm">
        <input type="number" min="0" wire:model.defer="beneficiariesCount"
            class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md px-3 py-2" />
        <button type="button" wire:click.prevent="saveBeneficiariesCount" wire:loading.attr="disabled"
            class="px-4 py-2 bg-indigo-600 text-white rounded">Save</button>
    </div>
    <div class="text-sm text-gray-500 mt-2">Set number of beneficiaries for the generated forms</div>
</div>
@endunless
</div>