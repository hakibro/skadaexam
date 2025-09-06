/**
 * Batch Processing JavaScript
 *
 * Handles batch import and sync operations for large datasets.
 * Supports progress tracking, error handling, and resumable operations.
 */

// Batch Import variables
let batchImportActive = false;
let batchImportPollingInterval = null;
let batchImportResults = {
    created_kelas: 0,
    updated_kelas: 0,
    created_siswa: 0,
    updated_siswa: 0,
    skipped: 0,
    errors: [],
};

// Batch Sync variables
let batchSyncActive = false;
let batchSyncPollingInterval = null;
let batchSyncResults = {
    created_kelas: 0,
    updated_kelas: 0,
    created_siswa: 0,
    updated_siswa: 0,
    skipped: 0,
    errors: [],
};

// DOM Elements - Batch Import
const batchImportSection = document.getElementById("batch-import-section");
const batchImportResultsSection = document.getElementById(
    "batch-import-results-section"
);
const batchImportErrorSection = document.getElementById(
    "batch-import-error-section"
);
const cancelBatchImportBtn = document.getElementById("cancel-batch-import-btn");
const closeBatchImportResultsBtn = document.getElementById(
    "close-batch-import-results-btn"
);
const closeBatchImportErrorBtn = document.getElementById(
    "close-batch-import-error-btn"
);
const retryBatchImportBtn = document.getElementById("retry-batch-import-btn");

// DOM Elements - Batch Sync
const batchSyncSection = document.getElementById("batch-sync-section");
const batchSyncResultsSection = document.getElementById(
    "batch-sync-results-section"
);
const batchSyncErrorSection = document.getElementById(
    "batch-sync-error-section"
);
const cancelBatchSyncBtn = document.getElementById("cancel-batch-sync-btn");
const closeBatchSyncResultsBtn = document.getElementById(
    "close-batch-sync-results-btn"
);
const closeBatchSyncErrorBtn = document.getElementById(
    "close-batch-sync-error-btn"
);
const retryBatchSyncBtn = document.getElementById("retry-batch-sync-btn");

// DOM Elements - Buttons to start operations
const startBatchImportBtn = document.getElementById("batch-import-btn");
const startBatchSyncBtn = document.getElementById("batch-sync-btn");

/**
 * Initialize batch processing event listeners
 */
function initBatchProcessing() {
    // Initialize import listeners
    if (startBatchImportBtn) {
        startBatchImportBtn.addEventListener("click", startBatchImport);
    }

    if (cancelBatchImportBtn) {
        cancelBatchImportBtn.addEventListener("click", cancelBatchImport);
    }

    if (closeBatchImportResultsBtn) {
        closeBatchImportResultsBtn.addEventListener("click", () => {
            hideElement(batchImportResultsSection);
            performSearch(); // Refresh data
        });
    }

    if (closeBatchImportErrorBtn) {
        closeBatchImportErrorBtn.addEventListener("click", () => {
            hideElement(batchImportErrorSection);
        });
    }

    if (retryBatchImportBtn) {
        retryBatchImportBtn.addEventListener("click", () => {
            hideElement(batchImportErrorSection);
            startBatchImport();
        });
    }

    // Initialize sync listeners
    if (startBatchSyncBtn) {
        startBatchSyncBtn.addEventListener("click", startBatchSync);
    }

    if (cancelBatchSyncBtn) {
        cancelBatchSyncBtn.addEventListener("click", cancelBatchSync);
    }

    if (closeBatchSyncResultsBtn) {
        closeBatchSyncResultsBtn.addEventListener("click", () => {
            hideElement(batchSyncResultsSection);
            performSearch(); // Refresh data
        });
    }

    if (closeBatchSyncErrorBtn) {
        closeBatchSyncErrorBtn.addEventListener("click", () => {
            hideElement(batchSyncErrorSection);
        });
    }

    if (retryBatchSyncBtn) {
        retryBatchSyncBtn.addEventListener("click", () => {
            hideElement(batchSyncErrorSection);
            startBatchSync();
        });
    }
}

/**
 * Start batch import operation
 */
function startBatchImport() {
    if (
        !confirm(
            "Start batch import of student data? This may take several minutes."
        )
    ) {
        return;
    }

    // Reset results
    batchImportResults = {
        created_kelas: 0,
        updated_kelas: 0,
        created_siswa: 0,
        updated_siswa: 0,
        skipped: 0,
        errors: [],
    };

    // Show batch import section
    showElement(batchImportSection);
    hideElement(batchImportResultsSection);
    hideElement(batchImportErrorSection);

    // Set initial UI values
    setTextContent("batch-import-status-text", "Initializing import...");
    setTextContent("batch-import-percentage", "0%");
    setTextContent("batch-import-current-batch", "0");
    setTextContent("batch-import-total-batches", "0");
    setTextContent("batch-import-message", "Starting batch import...");
    setProgressBar("batch-import-progress-bar", 0);
    resetCounters("batch-import");

    // Start the batch import
    batchImportActive = true;

    fetch("/data/siswa/batch-import", {
        method: "POST",
        headers: {
            "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]')
                .content,
            "Content-Type": "application/json",
            Accept: "application/json",
        },
        body: JSON.stringify({ batch_size: 50 }), // Configurable batch size
    })
        .then((response) => response.json())
        .then((data) => {
            if (data.success) {
                // If data is still processing, start polling for updates
                if (data.status === "processing") {
                    updateBatchImportUI(data);
                    startBatchImportPolling();
                    processBatchImportNextBatch(data.next_batch_url);
                } else if (data.status === "completed") {
                    // Import completed in one go
                    completeBatchImport(data);
                }
            } else {
                showBatchImportError(data.error || "Failed to start import");
            }
        })
        .catch((error) => {
            console.error("Batch import initialization error:", error);
            showBatchImportError(
                "Import initialization failed: " + error.message
            );
        });
}

/**
 * Process the next batch in the import
 */
function processBatchImportNextBatch(url) {
    if (!batchImportActive) return;

    fetch(url, {
        method: "POST",
        headers: {
            "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]')
                .content,
            "Content-Type": "application/json",
            Accept: "application/json",
        },
    })
        .then((response) => response.json())
        .then((data) => {
            if (data.success) {
                // Update results with batch results
                if (data.batch_results) {
                    batchImportResults.created_kelas +=
                        data.batch_results.created_kelas || 0;
                    batchImportResults.updated_kelas +=
                        data.batch_results.updated_kelas || 0;
                    batchImportResults.created_siswa +=
                        data.batch_results.created_siswa || 0;
                    batchImportResults.updated_siswa +=
                        data.batch_results.updated_siswa || 0;
                    batchImportResults.skipped +=
                        data.batch_results.skipped || 0;

                    if (
                        data.batch_results.errors &&
                        data.batch_results.errors.length
                    ) {
                        batchImportResults.errors =
                            batchImportResults.errors.concat(
                                data.batch_results.errors
                            );
                    }
                }

                // Update UI with latest counts
                updateCounters("batch-import", batchImportResults);

                // If still processing, continue with next batch
                if (data.status === "processing") {
                    updateBatchImportUI(data);
                    processBatchImportNextBatch(data.next_batch_url);
                } else if (data.status === "completed") {
                    completeBatchImport(data);
                }
            } else {
                showBatchImportError(data.error || "Batch processing failed");
            }
        })
        .catch((error) => {
            console.error("Batch processing error:", error);
            showBatchImportError("Batch processing failed: " + error.message);
        });
}

/**
 * Start polling for batch import progress
 */
function startBatchImportPolling() {
    if (batchImportPollingInterval) {
        clearInterval(batchImportPollingInterval);
    }

    batchImportPollingInterval = setInterval(() => {
        if (!batchImportActive) {
            clearInterval(batchImportPollingInterval);
            return;
        }

        fetch("/data/siswa/batch-import-status")
            .then((response) => response.json())
            .then((data) => {
                if (data.status === "error") {
                    showBatchImportError(
                        data.message || "An error occurred during import"
                    );
                    stopBatchImportPolling();
                } else if (data.status === "completed") {
                    completeBatchImport(data);
                    stopBatchImportPolling();
                } else {
                    // Just update UI based on polling data
                    setTextContent("batch-import-status-text", data.status);
                    setTextContent(
                        "batch-import-percentage",
                        data.progress + "%"
                    );
                    setTextContent("batch-import-message", data.message);
                    setProgressBar("batch-import-progress-bar", data.progress);

                    if (data.results) {
                        updateCounters("batch-import", data.results);
                    }
                }
            })
            .catch((error) => {
                console.error("Progress polling error:", error);
                // Don't stop polling on network errors
            });
    }, 2000);
}

/**
 * Stop batch import polling
 */
function stopBatchImportPolling() {
    if (batchImportPollingInterval) {
        clearInterval(batchImportPollingInterval);
        batchImportPollingInterval = null;
    }
}

/**
 * Update the batch import UI with the latest data
 */
function updateBatchImportUI(data) {
    setTextContent("batch-import-status-text", data.status);
    setTextContent("batch-import-percentage", data.progress + "%");
    setTextContent("batch-import-current-batch", data.current_batch);
    setTextContent("batch-import-total-batches", data.total_batches);
    setProgressBar("batch-import-progress-bar", data.progress);
}

/**
 * Complete the batch import process
 */
function completeBatchImport(data) {
    stopBatchImportPolling();
    batchImportActive = false;

    // Hide progress section
    hideElement(batchImportSection);

    // Show results section
    showElement(batchImportResultsSection);

    // Update final results
    const finalResults = data.results || batchImportResults;
    setTextContent(
        "batch-import-results-content",
        data.message || "Import completed successfully!"
    );

    // Update stats in results section
    setTextContent("results-created-kelas", finalResults.created_kelas);
    setTextContent("results-updated-kelas", finalResults.updated_kelas);
    setTextContent("results-created-siswa", finalResults.created_siswa);
    setTextContent("results-updated-siswa", finalResults.updated_siswa);

    // Display errors if any
    if (finalResults.errors && finalResults.errors.length > 0) {
        showElement("batch-import-errors-container");

        // Format errors for display
        const errorHtml = finalResults.errors
            .map((err) => {
                if (typeof err === "string") {
                    return `<div class="py-1">${err}</div>`;
                } else {
                    return `<div class="py-1">${
                        err.error || JSON.stringify(err)
                    }</div>`;
                }
            })
            .join("");

        document.getElementById("batch-import-errors").innerHTML = errorHtml;
    } else {
        hideElement("batch-import-errors-container");
    }
}

/**
 * Show batch import error
 */
function showBatchImportError(errorMessage) {
    stopBatchImportPolling();
    batchImportActive = false;

    // Hide progress section
    hideElement(batchImportSection);

    // Show error section
    showElement(batchImportErrorSection);
    setTextContent("batch-import-error-content", errorMessage);
}

/**
 * Cancel the batch import operation
 */
function cancelBatchImport() {
    if (
        !confirm(
            "Are you sure you want to cancel the import? Progress will be lost."
        )
    ) {
        return;
    }

    stopBatchImportPolling();
    batchImportActive = false;
    hideElement(batchImportSection);

    // Show cancelled message
    showToast("Import cancelled by user", "warning");
}

/**
 * Start batch sync operation
 */
function startBatchSync() {
    if (
        !confirm(
            "Start batch sync of student data? This may take several minutes."
        )
    ) {
        return;
    }

    // Reset results
    batchSyncResults = {
        created_kelas: 0,
        updated_kelas: 0,
        created_siswa: 0,
        updated_siswa: 0,
        skipped: 0,
        errors: [],
    };

    // Show batch sync section
    showElement(batchSyncSection);
    hideElement(batchSyncResultsSection);
    hideElement(batchSyncErrorSection);

    // Set initial UI values
    setTextContent("batch-sync-status-text", "Initializing sync...");
    setTextContent("batch-sync-percentage", "0%");
    setTextContent("batch-sync-current-batch", "0");
    setTextContent("batch-sync-total-batches", "0");
    setTextContent("batch-sync-message", "Starting batch sync...");
    setProgressBar("batch-sync-progress-bar", 0);
    resetCounters("batch-sync");

    // Start the batch sync
    batchSyncActive = true;

    // Log that we're starting
    console.log("Starting batch sync process");

    fetch("/data/siswa/batch-sync", {
        method: "POST",
        headers: {
            "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]')
                .content,
            "Content-Type": "application/json",
            Accept: "application/json",
        },
        body: JSON.stringify({ batch_size: 50 }), // Configurable batch size
    })
        .then((response) => {
            if (!response.ok) {
                throw new Error(
                    `Server returned ${response.status}: ${response.statusText}`
                );
            }
            return response.json();
        })
        .then((data) => {
            console.log("Received batch sync initialization response:", data);
            if (data.success) {
                // If data is still processing, start polling for updates
                if (data.status === "processing") {
                    updateBatchSyncUI(data);
                    startBatchSyncPolling();
                    processBatchSyncNextBatch(data.next_batch_url);
                } else if (data.status === "completed") {
                    // Sync completed in one go
                    completeBatchSync(data);
                }
            } else {
                showBatchSyncError(data.error || "Failed to start sync");
            }
        })
        .catch((error) => {
            console.error("Batch sync initialization error:", error);
            showBatchSyncError("Sync initialization failed: " + error.message);
        });
}

/**
 * Process the next batch in the sync
 */
function processBatchSyncNextBatch(url) {
    if (!batchSyncActive) return;

    console.log("Processing next batch:", url);

    fetch(url, {
        method: "POST",
        headers: {
            "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]')
                .content,
            "Content-Type": "application/json",
            Accept: "application/json",
        },
    })
        .then((response) => {
            if (!response.ok) {
                throw new Error(
                    `Server returned ${response.status}: ${response.statusText}`
                );
            }
            return response.json();
        })
        .then((data) => {
            console.log("Batch processing response:", data);
            if (data.success) {
                // Update results with batch results
                if (data.batch_results) {
                    batchSyncResults.created_kelas +=
                        data.batch_results.created_kelas || 0;
                    batchSyncResults.updated_kelas +=
                        data.batch_results.updated_kelas || 0;
                    batchSyncResults.created_siswa +=
                        data.batch_results.created_siswa || 0;
                    batchSyncResults.updated_siswa +=
                        data.batch_results.updated_siswa || 0;
                    batchSyncResults.skipped += data.batch_results.skipped || 0;

                    if (
                        data.batch_results.errors &&
                        data.batch_results.errors.length
                    ) {
                        batchSyncResults.errors =
                            batchSyncResults.errors.concat(
                                data.batch_results.errors
                            );
                    }
                }

                // Update UI with latest counts
                updateCounters("batch-sync", batchSyncResults);

                // If still processing, continue with next batch
                if (data.status === "processing") {
                    updateBatchSyncUI(data);
                    processBatchSyncNextBatch(data.next_batch_url);
                } else if (data.status === "completed") {
                    completeBatchSync(data);
                }
            } else {
                showBatchSyncError(data.error || "Batch processing failed");
            }
        })
        .catch((error) => {
            console.error("Batch processing error:", error);
            console.error(
                "Error details:",
                error.stack || "No stack trace available"
            );
            showBatchSyncError("Batch processing failed: " + error.message);

            // Attempt to report the error to the server for logging
            try {
                fetch("/data/siswa/batch-sync-error", {
                    method: "POST",
                    headers: {
                        "X-CSRF-TOKEN": document.querySelector(
                            'meta[name="csrf-token"]'
                        ).content,
                        "Content-Type": "application/json",
                    },
                    body: JSON.stringify({
                        error: error.message,
                        url: url,
                        stack: error.stack,
                    }),
                }).catch((e) => console.error("Failed to report error:", e));
            } catch (e) {
                console.error("Error reporting failed:", e);
            }
        });
}

/**
 * Start polling for batch sync progress
 */
function startBatchSyncPolling() {
    if (batchSyncPollingInterval) {
        clearInterval(batchSyncPollingInterval);
    }

    batchSyncPollingInterval = setInterval(() => {
        if (!batchSyncActive) {
            clearInterval(batchSyncPollingInterval);
            return;
        }

        fetch("/data/siswa/batch-sync-status")
            .then((response) => response.json())
            .then((data) => {
                if (data.status === "error") {
                    showBatchSyncError(
                        data.message || "An error occurred during sync"
                    );
                    stopBatchSyncPolling();
                } else if (data.status === "completed") {
                    completeBatchSync(data);
                    stopBatchSyncPolling();
                } else {
                    // Just update UI based on polling data
                    setTextContent("batch-sync-status-text", data.status);
                    setTextContent(
                        "batch-sync-percentage",
                        data.progress + "%"
                    );
                    setTextContent("batch-sync-message", data.message);
                    setProgressBar("batch-sync-progress-bar", data.progress);

                    if (data.results) {
                        updateCounters("batch-sync", data.results);
                    }
                }
            })
            .catch((error) => {
                console.error("Progress polling error:", error);
                // Don't stop polling on network errors
            });
    }, 2000);
}

/**
 * Stop batch sync polling
 */
function stopBatchSyncPolling() {
    if (batchSyncPollingInterval) {
        clearInterval(batchSyncPollingInterval);
        batchSyncPollingInterval = null;
    }
}

/**
 * Update the batch sync UI with the latest data
 */
function updateBatchSyncUI(data) {
    setTextContent("batch-sync-status-text", data.status);
    setTextContent("batch-sync-percentage", data.progress + "%");
    setTextContent("batch-sync-current-batch", data.current_batch);
    setTextContent("batch-sync-total-batches", data.total_batches);
    setProgressBar("batch-sync-progress-bar", data.progress);
}

/**
 * Complete the batch sync process
 */
function completeBatchSync(data) {
    stopBatchSyncPolling();
    batchSyncActive = false;

    // Hide progress section
    hideElement(batchSyncSection);

    // Show results section
    showElement(batchSyncResultsSection);

    // Update final results
    const finalResults = data.results || batchSyncResults;
    setTextContent(
        "batch-sync-results-content",
        data.message || "Sync completed successfully!"
    );

    // Update stats in results section
    setTextContent("sync-results-created-kelas", finalResults.created_kelas);
    setTextContent("sync-results-updated-kelas", finalResults.updated_kelas);
    setTextContent("sync-results-created-siswa", finalResults.created_siswa);
    setTextContent("sync-results-updated-siswa", finalResults.updated_siswa);

    // Display errors if any
    if (finalResults.errors && finalResults.errors.length > 0) {
        showElement("batch-sync-errors-container");

        // Format errors for display
        const errorHtml = finalResults.errors
            .map((err) => {
                if (typeof err === "string") {
                    return `<div class="py-1">${err}</div>`;
                } else {
                    return `<div class="py-1">${
                        err.error || JSON.stringify(err)
                    }</div>`;
                }
            })
            .join("");

        document.getElementById("batch-sync-errors").innerHTML = errorHtml;
    } else {
        hideElement("batch-sync-errors-container");
    }
}

/**
 * Show batch sync error
 */
function showBatchSyncError(errorMessage) {
    stopBatchSyncPolling();
    batchSyncActive = false;

    // Hide progress section
    hideElement(batchSyncSection);

    // Show error section
    showElement(batchSyncErrorSection);
    setTextContent("batch-sync-error-content", errorMessage);
}

/**
 * Cancel the batch sync operation
 */
function cancelBatchSync() {
    if (
        !confirm(
            "Are you sure you want to cancel the sync? Progress will be lost."
        )
    ) {
        return;
    }

    stopBatchSyncPolling();
    batchSyncActive = false;
    hideElement(batchSyncSection);

    // Show cancelled message
    showToast("Sync cancelled by user", "warning");
}

/**
 * Helper function to set text content of an element
 */
function setTextContent(elementId, text) {
    const element = document.getElementById(elementId);
    if (element) {
        element.textContent = text;
    }
}

/**
 * Helper function to set the width of a progress bar
 */
function setProgressBar(elementId, percentage) {
    const element = document.getElementById(elementId);
    if (element) {
        element.style.width = `${percentage}%`;
    }
}

/**
 * Helper function to show an element
 */
function showElement(elementOrId) {
    const element =
        typeof elementOrId === "string"
            ? document.getElementById(elementOrId)
            : elementOrId;
    if (element) {
        element.classList.remove("hidden");
    }
}

/**
 * Helper function to hide an element
 */
function hideElement(elementOrId) {
    const element =
        typeof elementOrId === "string"
            ? document.getElementById(elementOrId)
            : elementOrId;
    if (element) {
        element.classList.add("hidden");
    }
}

/**
 * Reset all counters for a batch operation
 */
function resetCounters(prefix) {
    setTextContent(`${prefix}-created-kelas`, "0");
    setTextContent(`${prefix}-updated-kelas`, "0");
    setTextContent(`${prefix}-created-siswa`, "0");
    setTextContent(`${prefix}-updated-siswa`, "0");
}

/**
 * Update all counters for a batch operation
 */
function updateCounters(prefix, data) {
    setTextContent(`${prefix}-created-kelas`, data.created_kelas || 0);
    setTextContent(`${prefix}-updated-kelas`, data.updated_kelas || 0);
    setTextContent(`${prefix}-created-siswa`, data.created_siswa || 0);
    setTextContent(`${prefix}-updated-siswa`, data.updated_siswa || 0);
}

// Initialize batch processing when the document is loaded
document.addEventListener("DOMContentLoaded", initBatchProcessing);
