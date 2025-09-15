<?php
// Script to remove batch-related methods from SiswaController
$file = "C:\\laragon\\www\\skadaexam\\app\\Http\\Controllers\\Features\\Data\\SiswaController.php";
$content = file_get_contents($file);

// Methods to remove with their doc comments
$patterns = [
    // Private processBatchImport method
    "/\s+private function processBatchImport\(\).*?(?=\s+\/\*\*|\s+public function|\s+private function|\s+\}$)/s",
    
    // Private processBatchSync method
    "/\s+private function processBatchSync\(\).*?(?=\s+\/\*\*|\s+public function|\s+private function|\s+\}$)/s",
    
    // getBatchImportStatus method (if still exists)
    "/\s+public function getBatchImportStatus\(\).*?(?=\s+\/\*\*|\s+public function|\s+\}$)/s",
    
    // getBatchSyncStatus method (if still exists)
    "/\s+public function getBatchSyncStatus\(\).*?(?=\s+\/\*\*|\s+public function|\s+\}$)/s",
    
    // logBatchSyncError method (if still exists)
    "/\s+public function logBatchSyncError\(Request \\\$request\).*?(?=\s+\/\*\*|\s+public function|\s+\}$)/s",
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

