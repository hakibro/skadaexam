class SiswaManager {
    constructor() {
        this.initializeElements();
        this.attachEventListeners();
        this.currentFilters = {};
        this.debounceTimer = null;
        this.progressIntervals = {};
    }

    initializeElements() {
        // Filter elements
        this.elements = {
            searchInput: document.getElementById("search-input"),
            paymentFilter: document.getElementById("payment-filter"),
            rekomendasiFilter: document.getElementById("rekomendasi-filter"),
            kelasFilter: document.getElementById("kelas-filter"),
            clearFiltersBtn: document.getElementById("clear-filters"),
            loadingState: document.getElementById("loading-state"),
            resultsContainer: document.getElementById("results-container"),

            // Bulk elements
            bulkActions: document.getElementById("bulk-actions"),
            selectedCount: document.getElementById("selected-count"),
            bulkUpdateRekomendasiBtn: document.getElementById(
                "bulk-update-rekomendasi"
            ),
            bulkRekomendasiSelect: document.getElementById(
                "bulk-rekomendasi-select"
            ),
            bulkDeleteBtn: document.getElementById("bulk-delete"),
            clearSelectionBtn: document.getElementById("clear-selection"),

            // API elements
            testConnectionBtn: document.getElementById("test-connection-btn"),
            testSingleStudentBtn: document.getElementById(
                "test-single-student-btn"
            ),
            testSingleStudentBtnPopulated: document.getElementById(
                "test-single-student-btn-populated"
            ),
            checkApiStatusBtn: document.getElementById("check-api-status-btn"),
            syncApiBtn: document.getElementById("sync-api-btn"),
            importBtn: document.getElementById("import-btn"),
        };
    }

    attachEventListeners() {
        // Filter listeners
        this.elements.searchInput?.addEventListener("input", () =>
            this.performSearch()
        );
        this.elements.paymentFilter?.addEventListener("change", () =>
            this.performSearch(true)
        );
        this.elements.rekomendasiFilter?.addEventListener("change", () =>
            this.performSearch(true)
        );
        this.elements.kelasFilter?.addEventListener("change", () =>
            this.performSearch(true)
        );
        this.elements.clearFiltersBtn?.addEventListener("click", () =>
            this.clearFilters()
        );

        // Bulk action listeners
        this.elements.bulkUpdateRekomendasiBtn?.addEventListener("click", () =>
            this.bulkUpdateRekomendasi()
        );
        this.elements.bulkDeleteBtn?.addEventListener("click", () =>
            this.bulkDelete()
        );
        this.elements.clearSelectionBtn?.addEventListener("click", () =>
            this.clearSelection()
        );

        // API listeners
        this.elements.testConnectionBtn?.addEventListener("click", () =>
            this.testApiConnection()
        );
        this.elements.testSingleStudentBtn?.addEventListener("click", () =>
            this.testSingleStudent()
        );
        this.elements.testSingleStudentBtnPopulated?.addEventListener(
            "click",
            () => this.testSingleStudent()
        );
        this.elements.checkApiStatusBtn?.addEventListener("click", () =>
            this.checkApiStatus()
        );
        this.elements.syncApiBtn?.addEventListener("click", () =>
            this.syncData()
        );
        this.elements.importBtn?.addEventListener("click", () =>
            this.importData()
        );

        // Initial bulk actions setup
        this.attachBulkEventListeners();
    }

    // Filter functionality
    performSearch(immediate = false) {
        clearTimeout(this.debounceTimer);

        const delay = immediate ? 0 : 300;
        this.debounceTimer = setTimeout(() => {
            this.executeSearch();
        }, delay);
    }

    executeSearch() {
        const filters = {
            search: this.elements.searchInput?.value || "",
            status_pembayaran: this.elements.paymentFilter?.value || "",
            rekomendasi: this.elements.rekomendasiFilter?.value || "",
            kelas: this.elements.kelasFilter?.value || "",
        };

        this.currentFilters = filters;
        this.showLoading();

        const formData = new FormData();
        Object.keys(filters).forEach((key) => {
            if (filters[key]) formData.append(key, filters[key]);
        });

        fetch("/data/siswa-search", {
            method: "POST",
            headers: {
                "X-CSRF-TOKEN": document.querySelector(
                    'meta[name="csrf-token"]'
                ).content,
                Accept: "application/json",
                "X-Requested-With": "XMLHttpRequest",
            },
            body: formData,
        })
            .then((response) => response.json())
            .then((data) => {
                if (data.success) {
                    this.updateResults(data.data);
                } else {
                    this.showError(
                        "Search failed: " + (data.error || "Unknown error")
                    );
                }
            })
            .catch((error) => {
                this.showError("Search request failed: " + error.message);
            })
            .finally(() => {
                this.hideLoading();
            });
    }

    updateResults(data) {
        // Update table
        if (data.table) {
            const tableContainer = document.getElementById("table-container");
            if (tableContainer) {
                tableContainer.innerHTML = data.table;
                this.attachBulkEventListeners();
            }
        }

        // Update pagination
        if (data.pagination) {
            const paginationContainer = document.getElementById(
                "pagination-container"
            );
            if (paginationContainer) {
                paginationContainer.innerHTML = data.pagination;
            }
        }

        // Update stats
        if (data.stats) {
            this.updateStats(data.stats);
        }
    }

    updateStats(stats) {
        const elements = {
            showingCount: document.getElementById("showing-count"),
            totalCount: document.getElementById("total-count"),
            siswaCount: document.getElementById("siswa-count"),
            statLunas: document.getElementById("stat-lunas"),
            statBelumLunas: document.getElementById("stat-belum-lunas"),
            statRekomendasi: document.getElementById("stat-rekomendasi"),
        };

        if (elements.showingCount)
            elements.showingCount.textContent = stats.showing || 0;
        if (elements.totalCount)
            elements.totalCount.textContent = stats.total || 0;
        if (elements.siswaCount)
            elements.siswaCount.textContent = stats.total || 0;
        if (elements.statLunas)
            elements.statLunas.textContent = stats.lunas || 0;
        if (elements.statBelumLunas)
            elements.statBelumLunas.textContent = stats.belum_lunas || 0;
        if (elements.statRekomendasi)
            elements.statRekomendasi.textContent = stats.rekomendasi || 0;
    }

    clearFilters() {
        if (this.elements.searchInput) this.elements.searchInput.value = "";
        if (this.elements.paymentFilter) this.elements.paymentFilter.value = "";
        if (this.elements.rekomendasiFilter)
            this.elements.rekomendasiFilter.value = "";
        if (this.elements.kelasFilter) this.elements.kelasFilter.value = "";
        this.performSearch(true);
    }

    // Bulk actions
    attachBulkEventListeners() {
        const selectAllCheckbox = document.getElementById("select-all");
        const siswaCheckboxes = document.querySelectorAll(".siswa-checkbox");

        if (selectAllCheckbox) {
            selectAllCheckbox.addEventListener("change", function () {
                siswaCheckboxes.forEach((checkbox) => {
                    checkbox.checked = this.checked;
                });
                siswaManager.updateBulkActions();
            });
        }

        siswaCheckboxes.forEach((checkbox) => {
            checkbox.addEventListener("change", () => this.updateBulkActions());
        });

        this.updateBulkActions();
    }

    updateBulkActions() {
        const checkedBoxes = document.querySelectorAll(
            ".siswa-checkbox:checked"
        );
        const count = checkedBoxes.length;

        if (this.elements.selectedCount) {
            this.elements.selectedCount.textContent = count;
        }

        if (this.elements.bulkActions) {
            this.elements.bulkActions.classList.toggle("hidden", count === 0);
        }
    }

    bulkUpdateRekomendasi() {
        const checkedBoxes = document.querySelectorAll(
            ".siswa-checkbox:checked"
        );
        const ids = Array.from(checkedBoxes).map((cb) => cb.value);
        const rekomendasi = this.elements.bulkRekomendasiSelect?.value;

        if (ids.length === 0) {
            this.showAlert("Please select at least one student");
            return;
        }

        if (
            !confirm(
                `Update rekomendasi for ${ids.length} students to "${rekomendasi}"?`
            )
        ) {
            return;
        }

        this.makeRequest("/data/siswa-bulk-update-rekomendasi", {
            ids: ids,
            rekomendasi: rekomendasi,
        }).then((data) => {
            if (data.success) {
                this.showSuccess(data.message);
                this.executeSearch();
                this.clearSelection();
            } else {
                this.showError(data.message || "Unknown error");
            }
        });
    }

    bulkDelete() {
        const checkedBoxes = document.querySelectorAll(
            ".siswa-checkbox:checked"
        );
        const ids = Array.from(checkedBoxes).map((cb) => cb.value);

        if (ids.length === 0) {
            this.showAlert("Please select at least one student");
            return;
        }

        if (
            !confirm(
                `Delete ${ids.length} selected students? This action cannot be undone.`
            )
        ) {
            return;
        }

        this.makeRequest("/data/siswa-bulk-delete", { ids: ids }).then(
            (data) => {
                if (data.success) {
                    this.showSuccess(data.message);
                    this.executeSearch();
                    this.clearSelection();
                } else {
                    this.showError(data.message || "Unknown error");
                }
            }
        );
    }

    clearSelection() {
        const checkboxes = document.querySelectorAll(
            ".siswa-checkbox, #select-all"
        );
        checkboxes.forEach((checkbox) => (checkbox.checked = false));
        this.updateBulkActions();
    }

    // API functions
    testApiConnection() {
        const btn = this.elements.testConnectionBtn;
        this.setButtonLoading(btn, "Testing...");

        this.makeRequest("/data/siswa-test-api-connection")
            .then((data) => {
                const statusDiv = document.getElementById("connection-status");
                if (statusDiv) {
                    statusDiv.classList.remove("hidden");
                    statusDiv.innerHTML = this.createStatusHTML(data);
                }
            })
            .finally(() => {
                this.resetButton(btn, "Test API Connection", "fa-plug");
            });
    }

    testSingleStudent() {
        this.makeRequest("/data/siswa-test-api-single-student").then((data) => {
            const resultDiv = document.getElementById("single-student-result");
            const contentDiv = document.getElementById(
                "single-student-content"
            );

            if (resultDiv) resultDiv.classList.remove("hidden");
            if (contentDiv) {
                contentDiv.innerHTML = this.createTestResultHTML(data);
            }
        });
    }

    checkApiStatus() {
        const btn = this.elements.checkApiStatusBtn;
        this.setButtonLoading(btn, "Checking...");

        this.makeRequest("/data/siswa-check-api-status")
            .then((data) => {
                const displayDiv =
                    document.getElementById("api-status-display");
                const contentDiv =
                    document.getElementById("api-status-content");

                if (displayDiv && contentDiv) {
                    displayDiv.classList.remove("hidden");
                    contentDiv.innerHTML = this.createApiStatusHTML(data);
                }
            })
            .finally(() => {
                this.resetButton(btn, "Check API", "fa-wifi");
            });
    }

    // Utility methods
    makeRequest(url, data = null, method = "POST") {
        const options = {
            method: method,
            headers: {
                "X-CSRF-TOKEN": document.querySelector(
                    'meta[name="csrf-token"]'
                ).content,
                Accept: "application/json",
            },
        };

        if (data) {
            options.headers["Content-Type"] = "application/json";
            options.body = JSON.stringify(data);
        }

        return fetch(url, options)
            .then((response) => response.json())
            .catch((error) => {
                this.showError("Request failed: " + error.message);
                throw error;
            });
    }

    setButtonLoading(button, text) {
        if (button) {
            button.disabled = true;
            button.innerHTML = `<i class="fa-solid fa-spinner fa-spin mr-2"></i>${text}`;
        }
    }

    resetButton(button, text, icon) {
        if (button) {
            button.disabled = false;
            button.innerHTML = `<i class="fa-solid ${icon} mr-2"></i>${text}`;
        }
    }

    showLoading() {
        this.elements.loadingState?.classList.remove("hidden");
        this.elements.resultsContainer?.classList.add("hidden");
    }

    hideLoading() {
        this.elements.loadingState?.classList.add("hidden");
        this.elements.resultsContainer?.classList.remove("hidden");
    }

    showSuccess(message) {
        this.showToast(message, "success");
    }

    showError(message) {
        this.showToast(message, "error");
    }

    showAlert(message) {
        alert(message);
    }

    showToast(message, type = "info") {
        // Simple toast implementation
        const toast = document.createElement("div");
        toast.className = `fixed top-4 right-4 p-4 rounded-lg shadow-lg z-50 ${
            type === "success"
                ? "bg-green-500 text-white"
                : type === "error"
                ? "bg-red-500 text-white"
                : "bg-blue-500 text-white"
        }`;
        toast.textContent = message;

        document.body.appendChild(toast);

        setTimeout(() => {
            toast.remove();
        }, 5000);
    }

    createStatusHTML(data) {
        const bgClass = data.success
            ? "bg-green-50 border-green-200 text-green-800"
            : "bg-red-50 border-red-200 text-red-800";
        const icon = data.success ? "fa-check-circle" : "fa-times-circle";
        const title = data.success
            ? "Connection Successful!"
            : "Connection Failed!";

        return `
            <div class="p-3 border rounded ${bgClass}">
                <div class="flex items-center">
                    <i class="fa-solid ${icon} mr-2"></i>
                    <strong>${title}</strong>
                </div>
                <div class="mt-1 text-sm">${
                    data.error || data.message || ""
                }</div>
            </div>
        `;
    }

    createTestResultHTML(data) {
        const bgClass = data.success ? "text-green-800" : "text-red-800";
        const icon = data.success ? "fa-check-circle" : "fa-times-circle";
        const title = data.success ? "Test Successful!" : "Test Failed!";

        return `
            <div class="${bgClass} mb-3">
                <i class="fa-solid ${icon} mr-2"></i>
                <strong>${title}</strong>
            </div>
            <pre class="bg-gray-100 p-3 rounded text-xs overflow-auto">${JSON.stringify(
                data,
                null,
                2
            )}</pre>
        `;
    }

    createApiStatusHTML(data) {
        if (data.success && data.api_status === "online") {
            return `
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div class="bg-green-50 border border-green-200 rounded-lg p-4 text-center">
                        <div class="text-2xl font-bold text-green-600">✓</div>
                        <div class="text-sm text-green-700">API Online</div>
                    </div>
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 text-center">
                        <div class="text-2xl font-bold text-blue-600">${
                            data.data?.api_students_count || 0
                        }</div>
                        <div class="text-sm text-blue-700">API Students</div>
                    </div>
                    <div class="bg-purple-50 border border-purple-200 rounded-lg p-4 text-center">
                        <div class="text-2xl font-bold text-purple-600">${
                            data.data?.local_students_count || 0
                        }</div>
                        <div class="text-sm text-purple-700">Local Students</div>
                    </div>
                    <div class="bg-gray-50 border border-gray-200 rounded-lg p-4 text-center">
                        <div class="text-2xl font-bold text-gray-600">${(
                            data.data?.response_time || 0
                        ).toFixed(2)}s</div>
                        <div class="text-sm text-gray-700">Response Time</div>
                    </div>
                </div>
            `;
        } else {
            return `
                <div class="text-center">
                    <div class="text-4xl text-red-600 mb-2">✗</div>
                    <div class="text-lg font-semibold text-red-800">API Offline</div>
                    <div class="text-sm text-red-600 mt-2">${
                        data.error || "Unable to connect"
                    }</div>
                </div>
            `;
        }
    }
}

// Initialize when DOM is loaded
let siswaManager;
document.addEventListener("DOMContentLoaded", function () {
    siswaManager = new SiswaManager();
});
