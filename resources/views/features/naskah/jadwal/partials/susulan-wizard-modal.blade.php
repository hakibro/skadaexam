{{-- Modal Wizard Ujian Susulan --}}
<div id="susulan-wizard-modal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title"
    role="dialog" aria-modal="true">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" onclick="closeSusulanWizard()"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
        <div
            class="inline-block w-full max-w-4xl p-6 my-8 overflow-hidden text-left align-middle transition-all transform bg-white shadow-xl rounded-lg">
            {{-- Header --}}
            <div class="flex justify-between items-center mb-4 pb-4 border-b">
                <h3 class="text-lg font-semibold text-gray-900">
                    <i class="fa-solid fa-clock-rotate-left text-purple-600 mr-2"></i>
                    Wizard Ujian Susulan
                </h3>
                <button type="button" onclick="closeSusulanWizard()" class="text-gray-400 hover:text-gray-500">
                    <i class="fa-solid fa-times text-xl"></i>
                </button>
            </div>

            {{-- Progress Steps --}}
            <div class="mb-6">
                <div class="flex items-center justify-between">
                    <div class="wizard-step flex flex-col items-center" data-step="1">
                        <div
                            class="wizard-step-circle w-10 h-10 rounded-full flex items-center justify-center bg-purple-600 text-white font-bold">
                            1</div>
                        <span class="text-xs mt-1 text-purple-600 font-medium">Ruangan</span>
                    </div>
                    <div class="flex-1 h-1 bg-gray-200 mx-2">
                        <div class="wizard-progress-bar h-full bg-purple-600" style="width: 0%"></div>
                    </div>
                    <div class="wizard-step flex flex-col items-center" data-step="2">
                        <div
                            class="wizard-step-circle w-10 h-10 rounded-full flex items-center justify-center bg-gray-200 text-gray-500 font-bold">
                            2</div>
                        <span class="text-xs mt-1 text-gray-500">Sesi</span>
                    </div>
                    <div class="flex-1 h-1 bg-gray-200 mx-2">
                        <div class="wizard-progress-bar h-full bg-purple-600" style="width: 0%"></div>
                    </div>
                    <div class="wizard-step flex flex-col items-center" data-step="3">
                        <div
                            class="wizard-step-circle w-10 h-10 rounded-full flex items-center justify-center bg-gray-200 text-gray-500 font-bold">
                            3</div>
                        <span class="text-xs mt-1 text-gray-500">Duplikasi</span>
                    </div>
                    <div class="flex-1 h-1 bg-gray-200 mx-2">
                        <div class="wizard-progress-bar h-full bg-purple-600" style="width: 0%"></div>
                    </div>
                    <div class="wizard-step flex flex-col items-center" data-step="4">
                        <div
                            class="wizard-step-circle w-10 h-10 rounded-full flex items-center justify-center bg-gray-200 text-gray-500 font-bold">
                            4</div>
                        <span class="text-xs mt-1 text-gray-500">Assign Sesi</span>
                    </div>
                </div>
            </div>

            {{-- Step Content --}}
            <div id="wizard-content" class="min-h-[300px]">
                {{-- Step 1: Buat Ruangan --}}
                <div class="wizard-panel" data-step="1">
                    <h4 class="font-medium text-gray-900 mb-4">Step 1: Buat Ruangan Ujian Susulan</h4>
                    <p class="text-sm text-gray-600 mb-4">Buat ruangan khusus untuk ujian susulan atau gunakan ruangan
                        yang sudah ada.</p>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nama Ruangan <span
                                    class="text-red-500">*</span></label>
                            <input type="text" id="susulan-nama-ruangan"
                                class="w-full border-gray-300 rounded-md shadow-sm focus:ring-purple-500 focus:border-purple-500"
                                placeholder="Contoh: Ruangan Susulan 1">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Kapasitas <span
                                    class="text-red-500">*</span></label>
                            <input type="number" id="susulan-kapasitas"
                                class="w-full border-gray-300 rounded-md shadow-sm focus:ring-purple-500 focus:border-purple-500"
                                value="30" min="1">
                        </div>
                        <div class="bg-blue-50 p-3 rounded-lg">
                            <p class="text-sm text-blue-700"><i class="fa-solid fa-info-circle mr-1"></i> Jadwal yang
                                dipilih: <strong id="selected-jadwal-count">0</strong></p>
                        </div>
                    </div>
                </div>

                {{-- Step 2: Buat Sesi Dinamis --}}
                <div class="wizard-panel hidden" data-step="2">
                    <h4 class="font-medium text-gray-900 mb-4">Step 2: Buat Sesi Ruangan</h4>
                    <p class="text-sm text-gray-600 mb-4">Tambahkan sesi ujian untuk ruangan susulan. Anda bisa menambah
                        atau menghapus sesi sesuai kebutuhan.</p>
                    <div id="sesi-container" class="space-y-3 max-h-64 overflow-y-auto">
                        {{-- Sesi template akan di-clone via JS --}}
                    </div>
                    <button type="button" onclick="addSesiRow()"
                        class="mt-3 inline-flex items-center px-3 py-1.5 bg-green-600 hover:bg-green-700 text-white text-sm rounded-md transition">
                        <i class="fa-solid fa-plus mr-1"></i> Tambah Sesi
                    </button>
                </div>

                {{-- Step 3: Duplikasi Jadwal dengan Durasi --}}
                <div class="wizard-panel hidden" data-step="3">
                    <h4 class="font-medium text-gray-900 mb-4">Step 3: Duplikasi Jadwal Ujian</h4>
                    <p class="text-sm text-gray-600 mb-4">Tentukan tanggal dan durasi untuk jadwal ujian susulan.</p>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Ujian Susulan <span
                                    class="text-red-500">*</span></label>
                            <input type="date" id="susulan-tanggal"
                                class="w-full border-gray-300 rounded-md shadow-sm focus:ring-purple-500 focus:border-purple-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Mode Durasi</label>
                            <div class="flex gap-4 mt-2">
                                <label class="inline-flex items-center">
                                    <input type="radio" name="durasi_mode" value="bulk" checked
                                        class="text-purple-600 focus:ring-purple-500" onchange="toggleDurasiMode()">
                                    <span class="ml-2 text-sm">Durasi Sama (Bulk)</span>
                                </label>
                                <label class="inline-flex items-center">
                                    <input type="radio" name="durasi_mode" value="per_jadwal"
                                        class="text-purple-600 focus:ring-purple-500" onchange="toggleDurasiMode()">
                                    <span class="ml-2 text-sm">Durasi Per Jadwal</span>
                                </label>
                            </div>
                        </div>
                        <div id="durasi-bulk-container">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Durasi (menit)</label>
                            <select id="susulan-durasi-bulk"
                                class="w-full border-gray-300 rounded-md shadow-sm focus:ring-purple-500 focus:border-purple-500">
                                <option value="25">25 menit</option>
                                <option value="30" selected>30 menit</option>
                                <option value="45">45 menit</option>
                                <option value="60">60 menit</option>
                                <option value="90">90 menit</option>
                                <option value="120">120 menit</option>
                            </select>
                        </div>
                        <div id="durasi-per-jadwal-container" class="hidden">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Durasi Per Jadwal</label>
                            <div id="durasi-per-jadwal-list" class="space-y-2 max-h-48 overflow-y-auto"></div>
                        </div>
                    </div>
                </div>

                {{-- Step 4: Drag & Drop Assign Sesi --}}
                <div class="wizard-panel hidden" data-step="4">
                    <h4 class="font-medium text-gray-900 mb-4">Step 4: Assign Jadwal ke Sesi</h4>
                    <p class="text-sm text-gray-600 mb-4">Drag jadwal ke sesi yang tersedia. Siswa akan otomatis
                        di-assign dan di-enroll.</p>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <h5 class="font-medium text-sm text-gray-700 mb-2">Jadwal Susulan</h5>
                            <div id="jadwal-to-assign"
                                class="border border-dashed border-gray-300 rounded-lg p-3 min-h-[200px] max-h-[400px] overflow-y-auto bg-gray-50 space-y-2">
                                {{-- Jadwal items akan ditambahkan via JS --}}
                            </div>
                        </div>
                        <div>
                            <h5 class="font-medium text-sm text-gray-700 mb-2">Sesi Tersedia</h5>
                            <div id="sesi-dropzone" class="space-y-2 max-h-[400px] overflow-y-auto">
                                {{-- Sesi dropzones akan ditambahkan via JS --}}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Footer Navigation --}}
            <div class="flex justify-between items-center mt-6 pt-4 border-t">
                <button type="button" id="wizard-prev-btn" onclick="wizardPrev()"
                    class="hidden inline-flex items-center px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-md transition">
                    <i class="fa-solid fa-arrow-left mr-2"></i> Sebelumnya
                </button>
                <div></div>
                <div class="flex gap-2">
                    <button type="button" onclick="closeSusulanWizard()"
                        class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-md transition">
                        Batal
                    </button>
                    <button type="button" id="wizard-next-btn" onclick="wizardNext()"
                        class="inline-flex items-center px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-md transition">
                        Selanjutnya <i class="fa-solid fa-arrow-right ml-2"></i>
                    </button>
                    <button type="button" id="wizard-finish-btn" onclick="wizardFinish()"
                        class="hidden inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-md transition">
                        <i class="fa-solid fa-check mr-2"></i> Selesai
                    </button>
                </div>
            </div>

            {{-- Loading Overlay --}}
            <div id="wizard-loading"
                class="hidden absolute inset-0 bg-white bg-opacity-80 flex items-center justify-center">
                <div class="text-center">
                    <i class="fa-solid fa-spinner fa-spin text-3xl text-purple-600 mb-2"></i>
                    <p class="text-gray-600">Memproses...</p>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Sesi Row Template --}}
<template id="sesi-row-template">
    <div class="sesi-row flex items-center gap-2 p-3 bg-gray-50 rounded-lg">
        <span class="sesi-number font-medium text-gray-700 w-16">Sesi 1</span>
        <input type="text"
            class="sesi-nama flex-1 border-gray-300 rounded-md shadow-sm text-sm focus:ring-purple-500 focus:border-purple-500"
            placeholder="Nama Sesi">
        <input type="time"
            class="sesi-waktu-mulai border-gray-300 rounded-md shadow-sm text-sm focus:ring-purple-500 focus:border-purple-500"
            value="08:00">
        <span class="text-gray-500">-</span>
        <input type="time"
            class="sesi-waktu-selesai border-gray-300 rounded-md shadow-sm text-sm focus:ring-purple-500 focus:border-purple-500"
            value="09:00">
        <button type="button" onclick="removeSesiRow(this)"
            class="p-1.5 text-red-500 hover:bg-red-100 rounded transition">
            <i class="fa-solid fa-trash text-sm"></i>
        </button>
    </div>
</template>

<script>
    // Wizard State
    let wizardState = {
        currentStep: 1,
        totalSteps: 4,
        selectedJadwalIds: [],
        ruangan: null,
        sesiList: [],
        duplicatedJadwal: [],
        assignments: {}
    };

    // Routes
    const susulanRoutes = {
        ruangan: "{{ route('naskah.jadwal.susulan-wizard.ruangan') }}",
        sesi: "{{ route('naskah.jadwal.susulan-wizard.sesi') }}",
        duplikasi: "{{ route('naskah.jadwal.susulan-wizard.duplikasi') }}",
        assign: "{{ route('naskah.jadwal.susulan-wizard.assign') }}",
        detach: "{{ route('naskah.jadwal.susulan-wizard.detach') }}",
        finalize: "{{ route('naskah.jadwal.susulan-wizard.finalize') }}"
    };

    function openSusulanWizard() {
        const checkedBoxes = document.querySelectorAll('.jadwal-checkbox:checked');
        if (checkedBoxes.length === 0) {
            alert('Pilih minimal satu jadwal ujian');
            return;
        }

        wizardState.selectedJadwalIds = Array.from(checkedBoxes).map(cb => cb.value);
        document.getElementById('selected-jadwal-count').textContent = wizardState.selectedJadwalIds.length;

        // Reset wizard
        wizardState.currentStep = 1;
        wizardState.ruangan = null;
        wizardState.sesiList = [];
        wizardState.duplicatedJadwal = [];
        wizardState.assignments = {};

        updateWizardUI();
        initSesiContainer();

        document.getElementById('susulan-wizard-modal').classList.remove('hidden');
    }

    function closeSusulanWizard() {
        document.getElementById('susulan-wizard-modal').classList.add('hidden');
    }

    function updateWizardUI() {
        // Update step circles
        document.querySelectorAll('.wizard-step').forEach(step => {
            const stepNum = parseInt(step.dataset.step);
            const circle = step.querySelector('.wizard-step-circle');
            const label = step.querySelector('span');

            if (stepNum < wizardState.currentStep) {
                circle.className =
                    'wizard-step-circle w-10 h-10 rounded-full flex items-center justify-center bg-green-500 text-white font-bold';
                circle.innerHTML = '<i class="fa-solid fa-check"></i>';
                label.className = 'text-xs mt-1 text-green-600 font-medium';
            } else if (stepNum === wizardState.currentStep) {
                circle.className =
                    'wizard-step-circle w-10 h-10 rounded-full flex items-center justify-center bg-purple-600 text-white font-bold';
                circle.textContent = stepNum;
                label.className = 'text-xs mt-1 text-purple-600 font-medium';
            } else {
                circle.className =
                    'wizard-step-circle w-10 h-10 rounded-full flex items-center justify-center bg-gray-200 text-gray-500 font-bold';
                circle.textContent = stepNum;
                label.className = 'text-xs mt-1 text-gray-500';
            }
        });

        // Update panels
        document.querySelectorAll('.wizard-panel').forEach(panel => {
            const stepNum = parseInt(panel.dataset.step);
            panel.classList.toggle('hidden', stepNum !== wizardState.currentStep);
        });

        // Update buttons
        document.getElementById('wizard-prev-btn').classList.toggle('hidden', wizardState.currentStep === 1);
        document.getElementById('wizard-next-btn').classList.toggle('hidden', wizardState.currentStep === wizardState
            .totalSteps);
        document.getElementById('wizard-finish-btn').classList.toggle('hidden', wizardState.currentStep !== wizardState
            .totalSteps);
    }

    function showLoading(show) {
        document.getElementById('wizard-loading').classList.toggle('hidden', !show);
    }

    async function wizardNext() {
        showLoading(true);
        try {
            if (wizardState.currentStep === 1) {
                await stepCreateRuangan();
            } else if (wizardState.currentStep === 2) {
                await stepCreateSesi();
            } else if (wizardState.currentStep === 3) {
                await stepDuplicateJadwal();
            }
            wizardState.currentStep++;
            updateWizardUI();

            if (wizardState.currentStep === 4) {
                renderAssignmentUI();
            }
        } catch (error) {
            alert(error.message || 'Terjadi kesalahan');
        }
        showLoading(false);
    }

    function wizardPrev() {
        if (wizardState.currentStep > 1) {
            wizardState.currentStep--;
            updateWizardUI();
        }
    }

    async function stepCreateRuangan() {
        const namaRuangan = document.getElementById('susulan-nama-ruangan').value.trim();
        const kapasitas = document.getElementById('susulan-kapasitas').value;

        if (!namaRuangan) throw new Error('Nama ruangan harus diisi');
        if (!kapasitas || kapasitas < 1) throw new Error('Kapasitas harus minimal 1');

        const response = await fetch(susulanRoutes.ruangan, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                jadwal_ids: wizardState.selectedJadwalIds,
                nama_ruangan: namaRuangan,
                kapasitas: parseInt(kapasitas)
            })
        });

        const data = await response.json();
        if (!response.ok) throw new Error(data.message);

        wizardState.ruangan = data.ruangan;
    }

    async function stepCreateSesi() {
        const sesiRows = document.querySelectorAll('#sesi-container .sesi-row');
        if (sesiRows.length === 0) throw new Error('Tambahkan minimal satu sesi');

        const sesiData = [];
        sesiRows.forEach((row, index) => {
            const nama = row.querySelector('.sesi-nama').value.trim() || `Sesi ${index + 1}`;
            const waktuMulai = row.querySelector('.sesi-waktu-mulai').value;
            const waktuSelesai = row.querySelector('.sesi-waktu-selesai').value;

            if (!waktuMulai || !waktuSelesai) throw new Error(`Sesi ${index + 1}: Waktu harus diisi`);

            sesiData.push({
                nama_sesi: nama,
                waktu_mulai: waktuMulai,
                waktu_selesai: waktuSelesai
            });
        });

        const response = await fetch(susulanRoutes.sesi, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                ruangan_id: wizardState.ruangan.id,
                sesi: sesiData
            })
        });

        const data = await response.json();
        if (!response.ok) throw new Error(data.message);

        wizardState.sesiList = data.sesi;

        // Populate durasi per jadwal if needed
        populateDurasiPerJadwal();
    }

    async function stepDuplicateJadwal() {
        const tanggal = document.getElementById('susulan-tanggal').value;
        if (!tanggal) throw new Error('Tanggal harus diisi');

        const durasiMode = document.querySelector('input[name="durasi_mode"]:checked').value;
        let payload = {
            jadwal_ids: wizardState.selectedJadwalIds,
            tanggal: tanggal,
            durasi_mode: durasiMode
        };

        if (durasiMode === 'bulk') {
            payload.durasi_bulk = parseInt(document.getElementById('susulan-durasi-bulk').value);
        } else {
            const durasiPerJadwal = {};
            document.querySelectorAll('#durasi-per-jadwal-list .durasi-input').forEach(input => {
                durasiPerJadwal[input.dataset.jadwalId] = parseInt(input.value);
            });
            payload.durasi_per_jadwal = durasiPerJadwal;
        }

        const response = await fetch(susulanRoutes.duplikasi, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify(payload)
        });

        const data = await response.json();
        if (!response.ok) throw new Error(data.message);

        wizardState.duplicatedJadwal = data.jadwal;
    }

    async function wizardFinish() {
        showLoading(true);
        try {
            const jadwalBaruIds = wizardState.duplicatedJadwal.map(j => j.id);

            const response = await fetch(susulanRoutes.finalize, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    jadwal_baru_ids: jadwalBaruIds
                })
            });

            const data = await response.json();
            if (!response.ok) throw new Error(data.message);

            alert(data.message);
            window.location.reload();
        } catch (error) {
            alert(error.message || 'Terjadi kesalahan');
        }
        showLoading(false);
    }

    // Sesi Management
    function initSesiContainer() {
        const container = document.getElementById('sesi-container');
        container.innerHTML = '';
        addSesiRow();
    }

    function addSesiRow() {
        const template = document.getElementById('sesi-row-template');
        const clone = template.content.cloneNode(true);
        const container = document.getElementById('sesi-container');
        const count = container.querySelectorAll('.sesi-row').length + 1;

        clone.querySelector('.sesi-number').textContent = `Sesi ${count}`;
        clone.querySelector('.sesi-nama').value = `Sesi ${count}`;

        container.appendChild(clone);
        updateSesiNumbers();
    }

    function removeSesiRow(btn) {
        const container = document.getElementById('sesi-container');
        if (container.querySelectorAll('.sesi-row').length > 1) {
            btn.closest('.sesi-row').remove();
            updateSesiNumbers();
        } else {
            alert('Minimal harus ada satu sesi');
        }
    }

    function updateSesiNumbers() {
        document.querySelectorAll('#sesi-container .sesi-row').forEach((row, index) => {
            row.querySelector('.sesi-number').textContent = `Sesi ${index + 1}`;
        });
    }

    // Durasi Mode Toggle
    function toggleDurasiMode() {
        const mode = document.querySelector('input[name="durasi_mode"]:checked').value;
        document.getElementById('durasi-bulk-container').classList.toggle('hidden', mode !== 'bulk');
        document.getElementById('durasi-per-jadwal-container').classList.toggle('hidden', mode !== 'per_jadwal');
    }

    function populateDurasiPerJadwal() {
        const container = document.getElementById('durasi-per-jadwal-list');
        container.innerHTML = '';

        // Get jadwal info from checkboxes' row data
        wizardState.selectedJadwalIds.forEach(jadwalId => {
            const checkbox = document.querySelector(`.jadwal-checkbox[value="${jadwalId}"]`);
            const row = checkbox.closest('tr');
            const judul = row.querySelector('td:nth-child(3) .font-medium').textContent;

            container.innerHTML += `
            <div class="flex items-center gap-2 p-2 bg-gray-50 rounded">
                <span class="flex-1 text-sm truncate">${judul}</span>
                <input type="number" class="durasi-input w-20 border-gray-300 rounded-md text-sm" data-jadwal-id="${jadwalId}" value="30" min="1">
                <span class="text-sm text-gray-500">menit</span>
            </div>
        `;
        });
    }

    // Assignment UI (Step 4)
    function renderAssignmentUI() {
        const jadwalContainer = document.getElementById('jadwal-to-assign');
        jadwalContainer.innerHTML = '';

        wizardState.duplicatedJadwal.forEach(jadwal => {
            jadwalContainer.innerHTML += `
            <div class="jadwal-item p-2 bg-white border rounded-lg cursor-move hover:shadow-md transition" 
                 data-jadwal-id="${jadwal.id}" data-jadwal-asli-id="${jadwal.jadwal_asli_id}" draggable="true">
                <div class="font-medium text-sm">${jadwal.judul}</div>
                <div class="text-xs text-gray-500">${jadwal.durasi_menit} menit</div>
            </div>
        `;
        });

        const sesiContainer = document.getElementById('sesi-dropzone');
        sesiContainer.innerHTML = '';

        wizardState.sesiList.forEach(sesi => {
            sesiContainer.innerHTML += `
            <div class="sesi-drop p-3 border-2 border-dashed border-gray-300 rounded-lg min-h-[80px] bg-white transition hover:border-purple-400"
                 data-sesi-id="${sesi.id}">
                <div class="font-medium text-sm text-gray-700 mb-2">${sesi.nama_sesi}</div>
                <div class="text-xs text-gray-500 mb-2">${sesi.waktu_mulai} - ${sesi.waktu_selesai}</div>
                <div class="assigned-jadwal space-y-1"></div>
            </div>
        `;
        });

        initDragDrop();
    }

    function initDragDrop() {
        document.querySelectorAll('.jadwal-item').forEach(item => {
            item.addEventListener('dragstart', (e) => {
                e.dataTransfer.setData('jadwal-id', item.dataset.jadwalId);
                e.dataTransfer.setData('jadwal-asli-id', item.dataset.jadwalAsliId);
                item.classList.add('opacity-50');
            });
            item.addEventListener('dragend', () => item.classList.remove('opacity-50'));
        });

        document.querySelectorAll('.sesi-drop').forEach(drop => {
            drop.addEventListener('dragover', (e) => {
                e.preventDefault();
                drop.classList.add('border-purple-500', 'bg-purple-50');
            });
            drop.addEventListener('dragleave', () => {
                drop.classList.remove('border-purple-500', 'bg-purple-50');
            });
            drop.addEventListener('drop', async (e) => {
                e.preventDefault();
                drop.classList.remove('border-purple-500', 'bg-purple-50');

                const jadwalId = e.dataTransfer.getData('jadwal-id');
                const jadwalAsliId = e.dataTransfer.getData('jadwal-asli-id');
                const sesiId = drop.dataset.sesiId;

                await assignJadwalToSesi(jadwalId, jadwalAsliId, sesiId, drop);
            });
        });
    }

    async function assignJadwalToSesi(jadwalBaruId, jadwalAsliId, sesiId, dropzone) {
        showLoading(true);
        try {
            const response = await fetch(susulanRoutes.assign, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    jadwal_baru_id: jadwalBaruId,
                    jadwal_asli_id: jadwalAsliId,
                    sesi_ruangan_id: sesiId
                })
            });

            const data = await response.json();
            if (!response.ok) throw new Error(data.message);

            // Move jadwal item to dropzone
            const jadwalItem = document.querySelector(`.jadwal-item[data-jadwal-id="${jadwalBaruId}"]`);
            const assignedContainer = dropzone.querySelector('.assigned-jadwal');

            jadwalItem.classList.remove('cursor-move');
            jadwalItem.draggable = false;
            jadwalItem.innerHTML +=
                `<div class="text-xs text-green-600 mt-1"><i class="fa-solid fa-check"></i> ${data.enrolled_count} siswa enrolled</div>`;

            assignedContainer.appendChild(jadwalItem);

            wizardState.assignments[jadwalBaruId] = sesiId;
        } catch (error) {
            alert(error.message);
        }
        showLoading(false);
    }
</script>
