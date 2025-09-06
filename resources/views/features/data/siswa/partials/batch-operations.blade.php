{{-- Batch Processing UI Components --}}
<div id="batch-processing-components">
    {{-- Batch Import Section --}}
    <div id="batch-import-section" class="hidden">
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-6 mb-4">
            <h4 class="text-lg font-medium text-blue-900 mb-2">
                <i class="fa-solid fa-cloud-download-alt mr-2"></i>Batch Import Progress
            </h4>
            <p class="text-sm text-blue-700 mb-4">
                Processing data in batches to prevent timeouts. Please wait while the import completes.
            </p>

            {{-- Overall Progress --}}
            <div class="mb-6">
                <div class="flex justify-between text-sm text-gray-600 mb-1">
                    <span id="batch-import-status-text">Initializing...</span>
                    <div>
                        <span id="batch-import-current-batch">0</span>/<span id="batch-import-total-batches">0</span>
                        batches
                        (<span id="batch-import-percentage">0%</span>)
                    </div>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-3">
                    <div id="batch-import-progress-bar" class="bg-blue-600 h-3 rounded-full transition-all duration-300"
                        style="width: 0%">
                    </div>
                </div>
            </div>

            {{-- Batch Details --}}
            <div class="text-sm mb-4">
                <div id="batch-import-message" class="font-medium mb-2">Starting import...</div>
                <div id="batch-import-counts" class="grid grid-cols-2 gap-2 text-gray-600">
                    <div>Classes created: <span id="batch-import-created-kelas">0</span></div>
                    <div>Classes updated: <span id="batch-import-updated-kelas">0</span></div>
                    <div>Students created: <span id="batch-import-created-siswa">0</span></div>
                    <div>Students updated: <span id="batch-import-updated-siswa">0</span></div>
                </div>
            </div>

            {{-- Cancel Button --}}
            <button type="button" id="cancel-batch-import-btn"
                class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg text-sm">
                <i class="fa-solid fa-times mr-1"></i>Cancel Import
            </button>
        </div>
    </div>

    {{-- Batch Import Results Section --}}
    <div id="batch-import-results-section" class="hidden">
        <div class="bg-green-50 border border-green-200 rounded-lg p-6 mb-4">
            <h4 class="text-lg font-medium text-green-900 mb-3">
                <i class="fa-solid fa-check-circle mr-2"></i>Batch Import Completed
            </h4>
            <div id="batch-import-results-content" class="text-sm text-green-700 mb-4"></div>
            <div id="batch-import-results-stats" class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-4">
                <div class="bg-white p-3 rounded-lg shadow-sm">
                    <div class="text-xs text-gray-500">Classes Created</div>
                    <div id="results-created-kelas" class="text-lg font-semibold">0</div>
                </div>
                <div class="bg-white p-3 rounded-lg shadow-sm">
                    <div class="text-xs text-gray-500">Classes Updated</div>
                    <div id="results-updated-kelas" class="text-lg font-semibold">0</div>
                </div>
                <div class="bg-white p-3 rounded-lg shadow-sm">
                    <div class="text-xs text-gray-500">Students Created</div>
                    <div id="results-created-siswa" class="text-lg font-semibold">0</div>
                </div>
                <div class="bg-white p-3 rounded-lg shadow-sm">
                    <div class="text-xs text-gray-500">Students Updated</div>
                    <div id="results-updated-siswa" class="text-lg font-semibold">0</div>
                </div>
            </div>
            <div id="batch-import-errors-container" class="hidden mb-4">
                <h5 class="font-medium text-red-800 mb-2">Errors Occurred</h5>
                <div id="batch-import-errors"
                    class="bg-white p-3 rounded-lg border border-red-200 max-h-40 overflow-y-auto text-xs text-red-600">
                </div>
            </div>
            <button type="button" id="close-batch-import-results-btn"
                class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg text-sm">
                Close
            </button>
        </div>
    </div>

    {{-- Batch Import Error Section --}}
    <div id="batch-import-error-section" class="hidden">
        <div class="bg-red-50 border border-red-200 rounded-lg p-6 mb-4">
            <h4 class="text-lg font-medium text-red-900 mb-3">
                <i class="fa-solid fa-exclamation-triangle mr-2"></i>Import Failed
            </h4>
            <div id="batch-import-error-content" class="text-sm text-red-700 mb-4"></div>
            <div class="flex gap-2">
                <button type="button" id="retry-batch-import-btn"
                    class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg text-sm">
                    <i class="fa-solid fa-redo mr-1"></i>Retry Import
                </button>
                <button type="button" id="close-batch-import-error-btn"
                    class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg text-sm">
                    Close
                </button>
            </div>
        </div>
    </div>

    {{-- Batch Sync Section --}}
    <div id="batch-sync-section" class="hidden">
        <div class="bg-purple-50 border border-purple-200 rounded-lg p-6 mb-4">
            <h4 class="text-lg font-medium text-purple-900 mb-2">
                <i class="fa-solid fa-sync mr-2"></i>Batch Sync Progress
            </h4>
            <p class="text-sm text-purple-700 mb-4">
                Syncing data in batches to prevent timeouts. Please wait while the sync completes.
            </p>

            {{-- Overall Progress --}}
            <div class="mb-6">
                <div class="flex justify-between text-sm text-gray-600 mb-1">
                    <span id="batch-sync-status-text">Initializing...</span>
                    <div>
                        <span id="batch-sync-current-batch">0</span>/<span id="batch-sync-total-batches">0</span>
                        batches
                        (<span id="batch-sync-percentage">0%</span>)
                    </div>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-3">
                    <div id="batch-sync-progress-bar" class="bg-purple-600 h-3 rounded-full transition-all duration-300"
                        style="width: 0%">
                    </div>
                </div>
            </div>

            {{-- Batch Details --}}
            <div class="text-sm mb-4">
                <div id="batch-sync-message" class="font-medium mb-2">Starting sync...</div>
                <div id="batch-sync-counts" class="grid grid-cols-2 gap-2 text-gray-600">
                    <div>Classes created: <span id="batch-sync-created-kelas">0</span></div>
                    <div>Classes updated: <span id="batch-sync-updated-kelas">0</span></div>
                    <div>Students created: <span id="batch-sync-created-siswa">0</span></div>
                    <div>Students updated: <span id="batch-sync-updated-siswa">0</span></div>
                </div>
            </div>

            {{-- Cancel Button --}}
            <button type="button" id="cancel-batch-sync-btn"
                class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg text-sm">
                <i class="fa-solid fa-times mr-1"></i>Cancel Sync
            </button>
        </div>
    </div>

    {{-- Batch Sync Results Section --}}
    <div id="batch-sync-results-section" class="hidden">
        <div class="bg-green-50 border border-green-200 rounded-lg p-6 mb-4">
            <h4 class="text-lg font-medium text-green-900 mb-3">
                <i class="fa-solid fa-check-circle mr-2"></i>Batch Sync Completed
            </h4>
            <div id="batch-sync-results-content" class="text-sm text-green-700 mb-4"></div>
            <div id="batch-sync-results-stats" class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-4">
                <div class="bg-white p-3 rounded-lg shadow-sm">
                    <div class="text-xs text-gray-500">Classes Created</div>
                    <div id="sync-results-created-kelas" class="text-lg font-semibold">0</div>
                </div>
                <div class="bg-white p-3 rounded-lg shadow-sm">
                    <div class="text-xs text-gray-500">Classes Updated</div>
                    <div id="sync-results-updated-kelas" class="text-lg font-semibold">0</div>
                </div>
                <div class="bg-white p-3 rounded-lg shadow-sm">
                    <div class="text-xs text-gray-500">Students Created</div>
                    <div id="sync-results-created-siswa" class="text-lg font-semibold">0</div>
                </div>
                <div class="bg-white p-3 rounded-lg shadow-sm">
                    <div class="text-xs text-gray-500">Students Updated</div>
                    <div id="sync-results-updated-siswa" class="text-lg font-semibold">0</div>
                </div>
            </div>
            <div id="batch-sync-errors-container" class="hidden mb-4">
                <h5 class="font-medium text-red-800 mb-2">Errors Occurred</h5>
                <div id="batch-sync-errors"
                    class="bg-white p-3 rounded-lg border border-red-200 max-h-40 overflow-y-auto text-xs text-red-600">
                </div>
            </div>
            <button type="button" id="close-batch-sync-results-btn"
                class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg text-sm">
                Close
            </button>
        </div>
    </div>

    {{-- Batch Sync Error Section --}}
    <div id="batch-sync-error-section" class="hidden">
        <div class="bg-red-50 border border-red-200 rounded-lg p-6 mb-4">
            <h4 class="text-lg font-medium text-red-900 mb-3">
                <i class="fa-solid fa-exclamation-triangle mr-2"></i>Sync Failed
            </h4>
            <div id="batch-sync-error-content" class="text-sm text-red-700 mb-4"></div>
            <div class="flex gap-2">
                <button type="button" id="retry-batch-sync-btn"
                    class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg text-sm">
                    <i class="fa-solid fa-redo mr-1"></i>Retry Sync
                </button>
                <button type="button" id="close-batch-sync-error-btn"
                    class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg text-sm">
                    Close
                </button>
            </div>
        </div>
    </div>
</div>
