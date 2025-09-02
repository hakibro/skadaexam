import "./bootstrap";

import Alpine from "alpinejs";

window.Alpine = Alpine;

Alpine.start();

// Utility untuk manajemen interval yang lebih baik
window.IntervalManager = {
    intervals: {},

    // Membuat interval baru dengan ID
    create: function (id, callback, delay) {
        // Bersihkan interval yang sudah ada dengan ID yang sama
        this.clear(id);

        // Buat interval baru
        this.intervals[id] = setInterval(callback, delay);
        return this.intervals[id];
    },

    // Membersihkan interval berdasarkan ID
    clear: function (id) {
        if (this.intervals[id]) {
            clearInterval(this.intervals[id]);
            delete this.intervals[id];
            return true;
        }
        return false;
    },

    // Membersihkan semua interval
    clearAll: function () {
        for (const id in this.intervals) {
            clearInterval(this.intervals[id]);
            delete this.intervals[id];
        }
    },
};

// Bersihkan interval saat navigasi
window.addEventListener("beforeunload", function () {
    window.IntervalManager.clearAll();
});
