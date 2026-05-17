<div>
    @if(auth()->check() && is_null(auth()->user()->school_id))
        <div class="space-y-2">
            @if (session()->has('message'))
                <div class="text-green-600">{{ session('message') }}</div>
            @endif

            <div class="flex items-center gap-1.5">
                <label class="text-sm font-medium text-gray-700">School ID</label>
                <x-feature-help>Link your account to a school by entering its DepEd School ID. This is required before uploading pupil data or generating forms. The ID must match a registered school in the system.</x-feature-help>
            </div>
            <input type="text" wire:model.defer="school_id" placeholder="Enter school id" class="border rounded px-2 py-1" />
            @error('school_id') <div class="text-red-600">{{ $message }}</div> @enderror

            <button wire:click="saveSchool" class="bg-blue-600 text-white px-3 py-1 rounded">Save</button>
        </div>
    @else
        <div>
            School ID: {{ auth()->user()->school_id ?? 'Not set' }}
        </div>
    @endif
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        window.addEventListener('swal:error', event => {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'error',
                    title: 'Invalid School ID',
                    text: event.detail.message || 'School ID not found.'
                });
            } else {
                alert(event.detail.message || 'School ID not found.');
            }
        });

        window.addEventListener('swal:success', event => {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'success',
                    title: 'Saved',
                    text: event.detail.message || 'School ID saved.'
                });
            } else {
                console.log(event.detail.message || 'School ID saved.');
            }
        });
    });
</script>
