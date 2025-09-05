<?php

// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Create a debug log file
$logFile = __DIR__ . '/../storage/logs/debug.log';
file_put_contents($logFile, "Debug log started at " . date('Y-m-d H:i:s') . "\n", FILE_APPEND);

// Function to log messages
function debugLog($message, $data = null)
{
    global $logFile;
    $log = date('Y-m-d H:i:s') . ' - ' . $message;
    if ($data !== null) {
        $log .= ' - ' . (is_array($data) || is_object($data) ? json_encode($data) : $data);
    }
    $log .= "\n";
    file_put_contents($logFile, $log, FILE_APPEND);
}

// Log server variables
debugLog('SERVER', $_SERVER);

// Log request details
debugLog('REQUEST_URI', $_SERVER['REQUEST_URI']);
debugLog('REQUEST_METHOD', $_SERVER['REQUEST_METHOD']);

// Log PHP info
debugLog('PHP Version', phpversion());
debugLog('PHP Memory Limit', ini_get('memory_limit'));
debugLog('PHP Max Execution Time', ini_get('max_execution_time'));

echo "Debug information has been logged to: " . $logFile;
