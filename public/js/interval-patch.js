/**
 * Patch untuk memperbaiki polling interval yang tidak dibersihkan dengan benar
 */

document.addEventListener("DOMContentLoaded", function () {
    // Patch untuk fungsi polling di halaman siswa
    const originalSiswaPolling = window.pollImportProgress;
    const originalSyncPolling = window.pollSyncProgress;

    // Bersihkan interval yang sudah ada saat halaman dimuat
    if (window.importProgressInterval) {
        clearInterval(window.importProgressInterval);
        console.log("[Patch] Cleared existing importProgressInterval");
    }

    if (window.syncProgressInterval) {
        clearInterval(window.syncProgressInterval);
        console.log("[Patch] Cleared existing syncProgressInterval");
    }

    // Pastikan interval dibersihkan ketika halaman dinavigasi keluar
    window.addEventListener("beforeunload", function () {
        if (window.importProgressInterval) {
            clearInterval(window.importProgressInterval);
            console.log("[Patch] Cleared importProgressInterval before unload");
        }

        if (window.syncProgressInterval) {
            clearInterval(window.syncProgressInterval);
            console.log("[Patch] Cleared syncProgressInterval before unload");
        }
    });
});
