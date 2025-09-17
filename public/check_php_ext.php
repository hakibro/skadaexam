<?php
$extensions = [
    "mbstring",
    "bcmath",
    "json",
    "ctype",
    "tokenizer",
    "xml",
    "curl",
    "pdo_mysql",
    "fileinfo",
    "gd",
    "zip",
    "exif",
    "openssl",
    "intl",
    "redis",
    "memcached"
];

echo "<h2>Laravel PHP Extension Checker</h2>";
echo "<table border='1' cellpadding='6' cellspacing='0'>";
echo "<tr><th>Extension</th><th>Status</th></tr>";

foreach ($extensions as $ext) {
    $status = extension_loaded($ext) ? "✅ Loaded" : "❌ Missing";
    echo "<tr><td>$ext</td><td>$status</td></tr>";
}

echo "</table>";
