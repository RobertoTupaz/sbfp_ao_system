<div style="font-family:system-ui, -apple-system, 'Segoe UI', Roboto, 'Helvetica Neue', Arial;">
    <div style="display:flex;gap:16px;flex-wrap:wrap;">
        <section style="flex:1;min-width:300px;border:1px solid #e6e6e6;padding:16px;border-radius:8px;box-shadow:0 1px 2px rgba(0,0,0,0.03);">
            <header style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;">
                <span class="text-2xl">Beneficiaries</span>
            </header>
            <div style="display:grid;grid-template-columns:repeat(2,1fr);gap:12px;">
                <div>
                    <legend style="font-weight:600;margin-bottom:8px;">Grade</legend>
                    <label style="display:flex;align-items:center;gap:8px;"><input type="checkbox" wire:model="primary_all_kinder" /> All Kinder</label>
                </div>
            </div>
        </section>
    </div>
    <div style="width:100%;display:flex;justify-content:center;margin-top:14px;">
        <div style="max-width:900px;width:100%;display:flex;justify-content:center;">
            <div style="width:100%;">
                <button wire:click="save" wire:loading.attr="disabled" style="width:100%;background:#0b74ff;color:#fff;border:none;padding:12px;border-radius:8px;cursor:pointer;display:block;">Set Beneficiaries</button>
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
                        title: 'Beneficiaries set successfully',
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
