<?php

$file = 'c:\laragon\www\skadaexam\app\Http\Controllers\Features\Pengawas\BeritaAcaraController.php';
$content = file_get_contents($file);

// Replace all occurrences of the old access control pattern with the new one
$oldPattern = '        // Check if current guru is assigned to this sesi ruangan
        $user = Auth::user();
        $guru = $user->guru;

        if (!$guru || $sesiRuangan->pengawas_id !== $guru->id) {
            return redirect()->route(\'pengawas.dashboard\')
                ->with(\'error\', \'Anda tidak memiliki akses ke sesi ruangan ini\');
        }';

$newPattern = '        // Check if current guru is assigned to this sesi ruangan
        if (!$this->checkPengawasAccess($sesiRuangan)) {
            return redirect()->route(\'pengawas.dashboard\')
                ->with(\'error\', \'Anda tidak memiliki akses ke sesi ruangan ini\');
        }';

$content = str_replace($oldPattern, $newPattern, $content);

// Also need to handle cases where we need the $guru variable
$oldPatternWithGuru = '        // Check if current guru is assigned to this sesi ruangan
        $user = Auth::user();
        $guru = $user->guru;

        if (!$guru || $sesiRuangan->pengawas_id !== $guru->id) {
            return redirect()->route(\'pengawas.dashboard\')
                ->with(\'error\', \'Anda tidak memiliki akses ke sesi ruangan ini\');
        }';

$newPatternWithGuru = '        // Check if current guru is assigned to this sesi ruangan
        $user = Auth::user();
        $guru = $user->guru;
        
        if (!$this->checkPengawasAccess($sesiRuangan)) {
            return redirect()->route(\'pengawas.dashboard\')
                ->with(\'error\', \'Anda tidak memiliki akses ke sesi ruangan ini\');
        }';

$content = str_replace($oldPatternWithGuru, $newPatternWithGuru, $content);

file_put_contents($file, $content);
echo "BeritaAcaraController access control updated successfully.\n";
