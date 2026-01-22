<div style="font-family:system-ui, -apple-system, 'Segoe UI', Roboto, 'Helvetica Neue', Arial;">
    <div style="display:flex;gap:16px;flex-wrap:wrap;">
        <section style="flex:1;min-width:300px;border:1px solid #e6e6e6;padding:16px;border-radius:8px;box-shadow:0 1px 2px rgba(0,0,0,0.03);">
            <header style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;">
                <span class="text-2xl">Primary Beneficiaries</span>
            </header>
            <div style="display:grid;grid-template-columns:repeat(2,1fr);gap:12px;">
                <div>
                    <legend style="font-weight:600;margin-bottom:8px;">Grade</legend>
                    <label style="display:flex;align-items:center;gap:8px;"><input type="checkbox" wire:model="primary_all_kinder" /> All Kinder</label>
                    <label style="display:flex;align-items:center;gap:8px;margin-top:8px;"><input type="checkbox" wire:model="primary_all_grade_1" /> All Grade 1</label>
                    <label style="display:flex;align-items:center;gap:8px;margin-top:8px;"><input type="checkbox" wire:model="primary_all_grade_2" /> All Grade 2</label>
                    <label style="display:flex;align-items:center;gap:8px;margin-top:8px;"><input type="checkbox" wire:model="primary_all_grade_3" /> All Grade 3</label>
                </div>

                <div>
                    <fieldset style="border:none;padding:0;margin:0">
                        <legend style="font-weight:600;margin-bottom:8px;">Nutrition</legend>
                        <label style="display:flex;align-items:center;gap:8px;"><input type="checkbox" wire:model="primary_severely_wasted" /> Severely Wasted</label>
                        <label style="display:flex;align-items:center;gap:8px;margin-top:8px;"><input type="checkbox" wire:model="primary_wasted" /> Wasted</label>
                        <label style="display:flex;align-items:center;gap:8px;margin-top:8px;"><input type="checkbox" wire:model="primary_normal_weight" /> Normal Weight</label>
                        <label style="display:flex;align-items:center;gap:8px;margin-top:8px;"><input type="checkbox" wire:model="primary_overweight_obese" /> Overweight/Obese</label>
                    </fieldset>
                </div>

                <div>
                    <legend style="font-weight:600;margin-bottom:8px;display:block;">Stunting</legend>
                    <label style="display:flex;align-items:center;gap:8px;"><input type="checkbox" wire:model="primary_severely_stunted" /> Severely Stunted</label>
                    <label style="display:flex;align-items:center;gap:8px;margin-top:8px;"><input type="checkbox" wire:model="primary_stunted" /> Stunted</label>
                    <label style="display:flex;align-items:center;gap:8px;margin-top:8px;"><input type="checkbox" wire:model="primary_normal_height" /> Normal Height</label>
                    <label style="display:flex;align-items:center;gap:8px;margin-top:8px;"><input type="checkbox" wire:model="primary_tall" /> Tall</label>
                </div>

                <div>
                    <legend style="font-weight:600;margin-bottom:8px;display:block;">Vulnerability</legend>
                    <label style="display:flex;align-items:center;gap:8px;"><input type="checkbox" wire:model="primary_4ps" /> 4Ps</label>
                    <label style="display:flex;align-items:center;gap:8px;margin-top:8px;"><input type="checkbox" wire:model="primary_ip" /> IP</label>
                    <label style="display:flex;align-items:center;gap:8px;margin-top:8px;"><input type="checkbox" wire:model="primary_pardo" /> Pardo</label>
                </div>
            </div>
        </section>

        <section style="flex:1;min-width:300px;border:1px solid #e6e6e6;padding:16px;border-radius:8px;box-shadow:0 1px 2px rgba(0,0,0,0.03);">
            <header style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;">
                <span class="text-2xl">Secondary Beneficiaries</span>
            </header>
            <div style="display:grid;grid-template-columns:repeat(2,1fr);gap:12px;">
                <div>
                    <legend style="font-weight:600;margin-bottom:8px;">Grade</legend>
                    <label style="display:flex;align-items:center;gap:8px;"><input type="checkbox" wire:model="secondary_all_kinder" /> All Kinder</label>
                    <label style="display:flex;align-items:center;gap:8px;margin-top:8px;"><input type="checkbox" wire:model="secondary_all_grade_1" /> All Grade 1</label>
                    <label style="display:flex;align-items:center;gap:8px;margin-top:8px;"><input type="checkbox" wire:model="secondary_all_grade_2" /> All Grade 2</label>
                    <label style="display:flex;align-items:center;gap:8px;margin-top:8px;"><input type="checkbox" wire:model="secondary_all_grade_3" /> All Grade 3</label>
                </div>

                <div>
                    <fieldset style="border:none;padding:0;margin:0">
                        <legend style="font-weight:600;margin-bottom:8px;">Nutrition</legend>
                        <label style="display:flex;align-items:center;gap:8px;"><input type="checkbox" wire:model="secondary_severely_wasted" /> Severely Wasted</label>
                        <label style="display:flex;align-items:center;gap:8px;margin-top:8px;"><input type="checkbox" wire:model="secondary_wasted" /> Wasted</label>
                        <label style="display:flex;align-items:center;gap:8px;margin-top:8px;"><input type="checkbox" wire:model="secondary_normal_weight" /> Normal Weight</label>
                        <label style="display:flex;align-items:center;gap:8px;margin-top:8px;"><input type="checkbox" wire:model="secondary_overweight_obese" /> Overweight/Obese</label>
                    </fieldset>
                </div>

                <div>
                    <legend style="font-weight:600;margin-bottom:8px;display:block;">Stunting</legend>
                    <label style="display:flex;align-items:center;gap:8px;"><input type="checkbox" wire:model="secondary_severely_stunted" /> Severely Stunted</label>
                    <label style="display:flex;align-items:center;gap:8px;margin-top:8px;"><input type="checkbox" wire:model="secondary_stunted" /> Stunted</label>
                    <label style="display:flex;align-items:center;gap:8px;margin-top:8px;"><input type="checkbox" wire:model="secondary_normal_height" /> Normal Height</label>
                    <label style="display:flex;align-items:center;gap:8px;margin-top:8px;"><input type="checkbox" wire:model="secondary_tall" /> Tall</label>
                </div>

                <div>
                    <legend style="font-weight:600;margin-bottom:8px;display:block;">Vulnerability</legend>
                    <label style="display:flex;align-items:center;gap:8px;"><input type="checkbox" wire:model="secondary_4ps" /> 4Ps</label>
                    <label style="display:flex;align-items:center;gap:8px;margin-top:8px;"><input type="checkbox" wire:model="secondary_ip" /> IP</label>
                    <label style="display:flex;align-items:center;gap:8px;margin-top:8px;"><input type="checkbox" wire:model="secondary_pardo" /> Pardo</label>
                </div>
            </div>
        </section>
        
    </div>
    <div style="width:100%;display:flex;justify-content:center;margin-top:14px;">
        <div style="max-width:900px;width:100%;display:flex;justify-content:center;">
            <div style="width:100%;">
                <button wire:click="save" wire:loading.attr="disabled" style="width:100%;background:#0b74ff;color:#fff;border:none;padding:12px;border-radius:8px;cursor:pointer;display:block;">Save Changes</button>
            </div>
        </div>
    </div>
    <script>
        window.addEventListener('beneficiaries-saved', event => {
            const msg = (event && event.detail && event.detail.message) ? event.detail.message : 'Saved.';
            const swal = window.Swal || window.sweetAlert;
            if (swal) {
                if (typeof swal.fire === 'function') {
                    swal.fire({
                        icon: 'success',
                        title: 'Saved',
                        text: msg,
                        timer: 2000,
                        showConfirmButton: false
                    });
                } else if (typeof swal === 'function') {
                    swal({
                        icon: 'success',
                        title: 'Saved',
                        text: msg,
                        timer: 2000,
                        showConfirmButton: false
                    });
                }
            } else {
                alert(msg);
            }
        });
    </script>
</div>
