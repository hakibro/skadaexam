
        function switchTab(sesiId) {
            // Hide all panels
            document.querySelectorAll('.tab-panel').forEach(panel => panel.classList.add('hidden'));
            // Show selected panel
            document.getElementById(`tab-${sesiId}-panel`).classList.remove('hidden');

            // Update tab buttons
            document.querySelectorAll('[id^="tab-btn-"]').forEach(btn => {
                btn.classList.remove('bg-white', 'text-gray-900', 'shadow-md');
                btn.classList.add('text-white/70', 'hover:text-white', 'hover:bg-white/10');
            });
            const activeBtn = document.getElementById(`tab-btn-${sesiId}`);
            if (activeBtn) {
                activeBtn.classList.remove('text-white/70', 'hover:text-white', 'hover:bg-white/10');
                activeBtn.classList.add('bg-white', 'text-gray-900', 'shadow-md');
            }
        }

        function toggleCollapse(id) {
            const el = document.getElementById(id);
            const icon = document.getElementById(id + '-icon');
            if (el) {
                el.classList.toggle('hidden');
                if (icon) {
                    icon.classList.toggle('rotate-180');
                }
            }
        }

        let activePelanggaranId = null;

        function openPelanggaranModal(pelanggaranId, siswaNama) {
            activePelanggaranId = pelanggaranId;
            document.getElementById('pelanggaran-modal-siswa').textContent = siswaNama;
            document.getElementById('pelanggaran-modal-catatan').value = '';
            document.querySelector('input[name="pelanggaran-action"][value="dismiss"]').checked = true;
            document.getElementById('pelanggaran-modal').classList.remove('hidden');
            document.getElementById('pelanggaran-modal').classList.add('flex');
        }

        function closePelanggaranModal() {
            activePelanggaranId = null;
            document.getElementById('pelanggaran-modal').classList.add('hidden');
            document.getElementById('pelanggaran-modal').classList.remove('flex');
        }

        function submitPelanggaranAction() {
            if (!activePelanggaranId) return;

            const pelanggaranId = activePelanggaranId;
            const action = document.querySelector('input[name="pelanggaran-action"]:checked').value;
            const catatan = document.getElementById('pelanggaran-modal-catatan').value;
            const submitBtn = document.getElementById('pelanggaran-modal-submit');
            const originalText = submitBtn.innerHTML;

            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin mr-1"></i> Memproses...';

            fetch(`/features/pengawas/process-violation/${pelanggaranId}`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        action: action,
                        catatan_pengawas: catatan
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const statusMap = {
                            dismiss: '<span class="text-emerald-600"><i class="fa-solid fa-check-circle mr-0.5"></i>Diabaikan</span>',
                            warning: '<span class="text-amber-600"><i class="fa-solid fa-triangle-exclamation mr-0.5"></i>Peringatan</span>',
                            suspend: '<span class="text-orange-600"><i class="fa-solid fa-pause-circle mr-0.5"></i>Dihentikan Sementara</span>',
                            remove: '<span class="text-red-600"><i class="fa-solid fa-circle-xmark mr-0.5"></i>Dikeluarkan dari Ujian</span>',
                        };

                        const statusEl = document.getElementById(`pelanggaran-status-${pelanggaranId}`);
                        if (statusEl) statusEl.innerHTML = statusMap[action] || statusEl.innerHTML;

                        const aksiEl = document.getElementById(`pelanggaran-aksi-${pelanggaranId}`);
                        if (aksiEl) aksiEl.classList.add('hidden');

                        // Update colors to "sudah ditindak" (resolved) state
                        const itemEl = document.getElementById(`pelanggaran-item-${pelanggaranId}`);
                        if (itemEl) {
                            itemEl.classList.remove('border-red-200', 'bg-red-50/40');
                            itemEl.classList.add('border-gray-200', 'bg-gray-50');
                        }

                        const avatarEl = document.getElementById(`pelanggaran-avatar-${pelanggaranId}`);
                        if (avatarEl) {
                            avatarEl.classList.remove('bg-red-100');
                            avatarEl.classList.add('bg-gray-200');
                            const avatarIcon = avatarEl.querySelector('i');
                            if (avatarIcon) {
                                avatarIcon.classList.remove('text-red-600');
                                avatarIcon.classList.add('text-gray-500');
                            }
                        }

                        const detailEl = document.getElementById(`pelanggaran-detail-${pelanggaranId}`);
                        if (detailEl) {
                            detailEl.classList.remove('border-red-100', 'bg-red-50');
                            detailEl.classList.add('border-gray-200', 'bg-gray-50');
                        }

                        if (catatan) {
                            const catatanWrap = document.getElementById(`pelanggaran-catatan-${pelanggaranId}`);
                            const catatanText = document.getElementById(`pelanggaran-catatan-text-${pelanggaranId}`);
                            if (catatanWrap && catatanText) {
                                catatanWrap.classList.remove('hidden');
                                catatanText.textContent = catatan;
                            }
                        }

                        closePelanggaranModal();

                        const toast = document.createElement('div');
                        toast.className =
                            'fixed bottom-24 right-4 bg-green-600 text-white px-4 py-2.5 rounded-lg shadow-lg text-sm z-50 font-medium flex items-center gap-2 animate-fade-in';
                        toast.innerHTML = `<i class="fa-solid fa-check-circle"></i> ${data.message}`;
                        document.body.appendChild(toast);
                        setTimeout(() => {
                            toast.style.opacity = '0';
                            toast.style.transition = 'opacity 0.3s ease-out';
                            setTimeout(() => toast.remove(), 300);
                        }, 3000);
                    } else {
                        alert(data.message || 'Gagal memproses pelanggaran');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Terjadi kesalahan saat memproses pelanggaran');
                })
                .finally(() => {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalText;
                });
        }

        function refreshKehadiran(sesiId, btn) {
            const icon = btn.querySelector('i');
            btn.disabled = true;
            icon.classList.add('fa-spin');

            fetch(`/features/pengawas/assignment/${sesiId}/attendance-summary`, {
                    headers: {
                        'Accept': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById(`kehadiran-hadir-${sesiId}`).textContent = data.data.hadir;
                        document.getElementById(`kehadiran-tidakhadir-${sesiId}`).textContent = data.data.tidak_hadir;
                        document.getElementById(`kehadiran-belumabsen-${sesiId}`).textContent = data.data
                            .belum_absen;
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                })
                .finally(() => {
                    icon.classList.remove('fa-spin');
                    btn.disabled = false;
                });
        }

        // ===================== Pelanggaran auto-refresh =====================
        const pelanggaranTimers = {};
        const pelanggaranTindakanLabels = {
            peringatan: ['Peringatan', 'text-amber-600', 'fa-triangle-exclamation'],
            hentikan_sementara: ['Dihentikan Sementara', 'text-orange-600', 'fa-pause-circle'],
            keluarkan: ['Dikeluarkan dari Ujian', 'text-red-600', 'fa-circle-xmark'],
        };

        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text ?? '';
            return div.innerHTML;
        }

        function pelanggaranStatusBadge(p) {
            if (p.tindakan && pelanggaranTindakanLabels[p.tindakan]) {
                const [label, color, icon] = pelanggaranTindakanLabels[p.tindakan];
                return `<span class="${color}"><i class="fa-solid ${icon} mr-0.5"></i>${label}</span>`;
            }
            if (p.is_dismissed) {
                return '<span class="text-emerald-600"><i class="fa-solid fa-check-circle mr-0.5"></i>Diabaikan</span>';
            }
            return '<span class="text-amber-600"><i class="fa-solid fa-clock mr-0.5"></i>Menunggu Tindakan</span>';
        }

        function renderPelanggaranItem(p) {
            const isHandled = p.is_dismissed || p.is_finalized;
            const itemColor = isHandled ? 'border-gray-200 bg-gray-50' : 'border-red-200 bg-red-50/40';
            const avatarColor = isHandled ? 'bg-gray-200' : 'bg-red-100';
            const avatarIconColor = isHandled ? 'text-gray-500' : 'text-red-600';
            const detailColor = isHandled ? 'border-gray-200 bg-gray-50' : 'border-red-100 bg-red-50';
            const namaSiswa = p.siswa?.nama ?? `Siswa #${p.siswa_id}`;
            const waktu = p.waktu_pelanggaran ? new Date(p.waktu_pelanggaran).toLocaleString('id-ID', {
                day: '2-digit',
                month: 'short',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit'
            }) : '-';
            const catatanHidden = p.catatan_pengawas ? '' : 'hidden';

            return `
                <div id="pelanggaran-item-${p.id}" class="border rounded-xl overflow-hidden ${itemColor}">
                    <div class="w-full flex items-center justify-between gap-2 px-4 py-3 cursor-pointer" onclick="togglePelanggaranDetail(${p.id})">
                        <div class="flex items-center gap-2.5 min-w-0 flex-1">
                            <div id="pelanggaran-avatar-${p.id}" class="w-7 h-7 rounded-full flex items-center justify-center flex-shrink-0 ${avatarColor}">
                                <i class="fa-solid fa-user text-xs ${avatarIconColor}"></i>
                            </div>
                            <div class="min-w-0">
                                <span class="text-sm font-semibold text-gray-900 block truncate">${escapeHtml(namaSiswa)}</span>
                                <span class="text-xs font-medium" id="pelanggaran-status-${p.id}">${pelanggaranStatusBadge(p)}</span>
                            </div>
                        </div>
                        <div class="flex items-center gap-2 flex-shrink-0">
                            <div id="pelanggaran-aksi-${p.id}" class="flex-shrink-0 ${isHandled ? 'hidden' : ''}">
                                <button type="button" onclick="event.stopPropagation(); openPelanggaranModal(${p.id}, '${escapeHtml(namaSiswa).replace(/'/g, "\\'")}')"
                                    class="inline-flex items-center gap-1.5 text-xs font-semibold text-white bg-red-600 hover:bg-red-700 px-3 py-1.5 rounded-lg transition whitespace-nowrap">
                                    <i class="fa-solid fa-gavel"></i>
                                    <span class="hidden sm:inline">Tindak Lanjut</span>
                                </button>
                            </div>
                            <i id="pelanggaran-chevron-${p.id}" class="fa-solid fa-chevron-down text-gray-400 text-xs transition-transform"></i>
                        </div>
                    </div>
                    <div id="pelanggaran-detail-${p.id}" class="hidden px-4 py-3 text-sm text-gray-700 space-y-2 border-t ${detailColor}">
                        <p><span class="font-semibold">Jenis:</span> ${escapeHtml(p.jenis_pelanggaran)}</p>
                        <p><span class="font-semibold">Waktu:</span> ${waktu}</p>
                        <p><span class="font-semibold">Deskripsi:</span> ${escapeHtml(p.deskripsi ?? '-')}</p>
                        <p id="pelanggaran-catatan-${p.id}" class="${catatanHidden}">
                            <span class="font-semibold">Catatan Pengawas:</span>
                            <span id="pelanggaran-catatan-text-${p.id}">${escapeHtml(p.catatan_pengawas ?? '')}</span>
                        </p>
                    </div>
                </div>
            `;
        }

        function togglePelanggaranDetail(id) {
            const detail = document.getElementById(`pelanggaran-detail-${id}`);
            const chevron = document.getElementById(`pelanggaran-chevron-${id}`);
            if (!detail) return;
            detail.classList.toggle('hidden');
            if (chevron) chevron.classList.toggle('rotate-180');
        }

        function refreshPelanggaranList(sesiId) {
            fetch(`/features/pengawas/get-violations/${sesiId}`, {
                    headers: {
                        'Accept': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (!data.success) return;

                    const listEl = document.getElementById(`pelanggaran-list-${sesiId}`);
                    const countEl = document.getElementById(`pelanggaran-count-${sesiId}`);
                    if (!listEl) return;

                    if (countEl) countEl.textContent = data.violations.length;

                    if (data.violations.length === 0) {
                        listEl.innerHTML = `
                            <div class="text-center text-xs text-gray-400 py-4">
                                <i class="fa-solid fa-shield-check text-emerald-500 text-base mb-1 block"></i>
                                Tidak ada pelanggaran
                            </div>
                        `;
                        return;
                    }

                    const expandedIds = new Set();
                    listEl.querySelectorAll('[id^="pelanggaran-detail-"]').forEach(el => {
                        if (!el.classList.contains('hidden')) {
                            expandedIds.add(el.id.replace('pelanggaran-detail-', ''));
                        }
                    });

                    listEl.innerHTML = data.violations.map(renderPelanggaranItem).join('');

                    expandedIds.forEach(id => {
                        const detail = document.getElementById(`pelanggaran-detail-${id}`);
                        const chevron = document.getElementById(`pelanggaran-chevron-${id}`);
                        if (detail) detail.classList.remove('hidden');
                        if (chevron) chevron.classList.add('rotate-180');
                    });
                })
                .catch(error => console.error('Error refreshing pelanggaran:', error));
        }

        function setPelanggaranInterval(sesiId, ms) {
            ms = parseInt(ms, 10);

            if (pelanggaranTimers[sesiId]) {
                clearInterval(pelanggaranTimers[sesiId]);
                delete pelanggaranTimers[sesiId];
            }

            if (ms > 0) {
                pelanggaranTimers[sesiId] = setInterval(() => refreshPelanggaranList(sesiId), ms);
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('[id^="pelanggaran-interval-"]').forEach(select => {
                const sesiId = select.id.replace('pelanggaran-interval-', '');
                setPelanggaranInterval(sesiId, select.value);
            });
        });

        function copyToken(token) {
            navigator.clipboard.writeText(token).then(() => {
                const toast = document.createElement('div');
                toast.className =
                    'fixed bottom-4 right-4 bg-green-600 text-white px-4 py-2.5 rounded-lg shadow-lg text-sm z-50 font-medium flex items-center gap-2 animate-fade-in';
                toast.innerHTML = '<i class="fa-solid fa-check-circle"></i> Token berhasil disalin!';
                document.body.appendChild(toast);
                setTimeout(() => {
                    toast.style.opacity = '0';
                    toast.style.transition = 'opacity 0.3s ease-out';
                    setTimeout(() => toast.remove(), 300);
                }, 2000);
            }).catch(() => {
                alert('Gagal menyalin token');
            });
        }

        function generateTokenAjax(sesiId, btn) {
            const originalText = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Generating...';

            fetch(`/features/pengawas/generate-token/${sesiId}`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const tokenInfo = document.getElementById(`token-info-${sesiId}`);

                        tokenInfo.innerHTML = `
                            <div class="flex items-center gap-2 bg-white rounded-lg p-3 border border-indigo-200">
                                <code class="flex-1 text-lg md:text-xl font-mono font-bold text-indigo-700 tracking-widest select-all">${data.data.token}</code>
                                <button onclick="copyToken('${data.data.token}')" class="text-indigo-600 hover:text-gray-700 transition p-1.5 hover:bg-white/70 rounded-lg">
                                    <i class="fa-regular fa-copy"></i>
                                </button>
                            </div>
                            <div id="token-status-${sesiId}" class="text-xs font-medium text-indigo-600">
                                <i class="fa-regular fa-hourglass-end"></i>
                                <span id="token-expiry-text-${sesiId}">Berlaku hingga ${data.data.expires_at_formatted}</span>
                                <span id="token-countdown-${sesiId}" class="ml-2 font-bold text-indigo-800">
                                    (<span id="token-countdown-time-${sesiId}" data-expires="${data.data.expires_at}"></span>)
                                </span>
                            </div>
                        `;

                        startCountdownForToken(sesiId);

                        const toast = document.createElement('div');
                        toast.className =
                            'fixed bottom-24 right-4 bg-green-600 text-white px-4 py-2.5 rounded-lg shadow-lg text-sm z-50 font-medium flex items-center gap-2 animate-fade-in';
                        toast.innerHTML = '<i class="fa-solid fa-check-circle"></i> Token berhasil dibuat!';
                        document.body.appendChild(toast);
                        setTimeout(() => {
                            toast.style.opacity = '0';
                            toast.style.transition = 'opacity 0.3s ease-out';
                            setTimeout(() => toast.remove(), 300);
                        }, 2000);

                        btn.disabled = false;
                        btn.innerHTML = originalText;
                    } else {
                        alert(data.message || 'Gagal membuat token');
                        btn.disabled = false;
                        btn.innerHTML = originalText;
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Terjadi kesalahan saat membuat token');
                    btn.disabled = false;
                    btn.innerHTML = originalText;
                });
        }

        function startCountdownForToken(sesiId) {
            const countdownEl = document.getElementById(`token-countdown-time-${sesiId}`);
            if (!countdownEl) return;

            const expiresAt = countdownEl.getAttribute('data-expires');
            if (!expiresAt) return;

            const updateCountdown = () => {
                const now = new Date();
                const expiry = new Date(expiresAt);
                const diff = expiry - now;

                if (diff <= 0) {
                    countdownEl.textContent = '00:00';
                    const statusEl = document.getElementById(`token-status-${sesiId}`);
                    const tokenCodeEl = document.getElementById(`token-card-${sesiId}`).querySelector('code');
                    const tokenBorderEl = document.getElementById(`token-card-${sesiId}`).querySelector('.border');
                    const tokenCopyBtn = document.getElementById(`token-card-${sesiId}`).querySelector(
                        'button[onclick*="copyToken"]');
                    const iconEl = statusEl?.querySelector('i');
                    const textEl = document.getElementById(`token-expiry-text-${sesiId}`);
                    const countdownSpan = document.getElementById(`token-countdown-${sesiId}`);

                    if (statusEl) statusEl.className = 'text-xs font-medium text-red-600';
                    if (tokenCodeEl) tokenCodeEl.className =
                        'flex-1 text-xl font-mono font-bold text-red-500 tracking-widest select-all';
                    if (tokenBorderEl) tokenBorderEl.className = tokenBorderEl.className.replace('border-indigo-200',
                        'border-red-300');
                    if (tokenCopyBtn) tokenCopyBtn.className = tokenCopyBtn.className.replace('text-indigo-600',
                        'text-red-500');
                    if (iconEl) iconEl.className = 'fa-regular fa-circle-xmark';
                    if (textEl) textEl.textContent = 'Token telah kedaluwarsa';
                    if (countdownSpan) countdownSpan.remove();

                    return;
                }

                const minutes = Math.floor(diff / 60000);
                const seconds = Math.floor((diff % 60000) / 1000);
                countdownEl.textContent =
                    `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;

                setTimeout(updateCountdown, 1000);
            };

            updateCountdown();
        }

        document.addEventListener('DOMContentLoaded', function() {
            // Start countdown timers for all existing tokens
            document.querySelectorAll('[id^="token-countdown-time-"]').forEach(el => {
                const sesiId = el.id.replace('token-countdown-time-', '');
                startCountdownForToken(sesiId);
            });
        });
    