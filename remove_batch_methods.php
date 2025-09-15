<?php
// Script to remove batch-related methods from SiswaController
$file = "C:\\laragon\\www\\skadaexam\\app\\Http\\Controllers\\Features\\Data\\SiswaController.php";
$content = file_get_contents($file);

// Methods to remove with their doc comments
$patterns = [
    // batchImport method
    "/\s+\/\*\*\s+\* New batch import method that processes students in batches.*?public function batchImport\(Request \\\$request\).*?(?=\s+\/\*\*|\s+public function|\s+\}$)/s",
    
    // getBatchImportStatus method
    "/\s+\/\*\*\s+\* Get the status of the batch import process.*?public function getBatchImportStatus\(\).*?(?=\s+\/\*\*|\s+public function|\s+\}$)/s",
    
    // batchSync method
    "/\s+\/\*\*\s+\* Batch sync method that processes students in batches.*?public function batchSync\(Request \\\$request\).*?(?=\s+\/\*\*|\s+public function|\s+\}$)/s",
    
    // getBatchSyncStatus method
    "/\s+\/\*\*\s+\* Get the status of the batch sync process.*?public function getBatchSyncStatus\(\).*?(?=\s+\/\*\*|\s+public function|\s+\}$)/s",
    
    // logBatchSyncError method
    "/\s+\/\*\*\s+\* Log client-side batch sync errors.*?public function logBatchSyncError\(Request \\\$request\).*?(?=\s+\/\*\*|\s+public function|\s+\}$)/s",
];

$replaced = 0;
foreach ($patterns as $pattern) {
    $newContent = preg_replace($pattern, "", $content, -1, $count);
    if ($count > 0) {
        $content = $newContent;
        $replaced += $count;
        echo "Removed {$count} method(s)\n";
    }
}

file_put_contents($file, $content);
echo "Done! Removed a total of {$replaced} method(s)\n";

