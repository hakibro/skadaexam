<?php
// File: public/test_batch_sync_api.php
// Purpose: Simple HTML page to test the batch-sync-status endpoint

require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Set mock session data
session(['batch_sync_status' => 'processing']);
session(['batch_sync_progress' => 67]);
session(['batch_sync_message' => 'Test message from HTML tester']);
session(['batch_sync_results' => [
    'created_kelas' => 5,
    'updated_kelas' => 10,
    'created_siswa' => 100,
    'updated_siswa' => 50,
    'skipped' => 3,
    'errors' => []
]]);
session(['batch_sync_data' => [
    'current_batch' => 7,
    'batch_count' => 10
]]);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Batch Sync API Tester</title>
    <script>
        function testEndpoint() {
            const resultDiv = document.getElementById('result');
            const url = document.getElementById('endpoint-url').value;

            resultDiv.innerHTML = '<p>Testing endpoint...</p>';

            fetch(url)
                .then(response => {
                    document.getElementById('status-code').textContent = response.status;
                    document.getElementById('status-text').textContent = response.statusText;
                    document.getElementById('content-type').textContent = response.headers.get('Content-Type');

                    if (!response.ok) {
                        throw new Error(`HTTP error! Status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    document.getElementById('response').textContent = JSON.stringify(data, null, 2);
                })
                .catch(error => {
                    document.getElementById('response').textContent = 'Error: ' + error.message;
                });
        }

        document.addEventListener('DOMContentLoaded', function() {
            // Auto-populate the URL field based on current location
            const baseUrl = window.location.protocol + '//' + window.location.host;
            document.getElementById('endpoint-url').value = `${baseUrl}/data/siswa/batch-sync-status`;
        });
    </script>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }

        h1,
        h2 {
            color: #333;
        }

        .input-group {
            margin-bottom: 15px;
        }

        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }

        input[type="text"] {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

        button {
            background-color: #4CAF50;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        pre {
            background-color: #f5f5f5;
            padding: 15px;
            border-radius: 4px;
            overflow-x: auto;
        }

        .info-box {
            background-color: #e7f3fe;
            border-left: 6px solid #2196F3;
            padding: 10px;
            margin-bottom: 15px;
        }

        .session-data {
            background-color: #fff3cd;
            border-left: 6px solid #ffc107;
            padding: 10px;
            margin-bottom: 15px;
        }
    </style>
</head>

<body>
    <h1>Batch Sync API Tester</h1>

    <div class="info-box">
        <p>This tool helps debug the batch-sync-status endpoint by making direct AJAX calls and displaying the results.</p>
    </div>

    <div class="session-data">
        <h3>Current Session Data:</h3>
        <pre><?php echo json_encode([
                    'batch_sync_status' => session('batch_sync_status'),
                    'batch_sync_progress' => session('batch_sync_progress'),
                    'batch_sync_message' => session('batch_sync_message'),
                    'batch_sync_results' => session('batch_sync_results'),
                    'batch_sync_data' => session('batch_sync_data')
                ], JSON_PRETTY_PRINT); ?></pre>
    </div>

    <div class="input-group">
        <label for="endpoint-url">Endpoint URL:</label>
        <input type="text" id="endpoint-url" placeholder="Enter the full URL to the endpoint">
    </div>

    <button onclick="testEndpoint()">Test Endpoint</button>

    <h2>Response</h2>
    <div id="result">
        <p><strong>Status Code:</strong> <span id="status-code">-</span></p>
        <p><strong>Status Text:</strong> <span id="status-text">-</span></p>
        <p><strong>Content-Type:</strong> <span id="content-type">-</span></p>
        <h3>Response Body:</h3>
        <pre id="response">Click "Test Endpoint" to see response</pre>
    </div>
</body>

</html>