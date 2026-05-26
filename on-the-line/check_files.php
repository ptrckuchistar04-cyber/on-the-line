<?php
echo "<h2>File Structure Check</h2>";

$projectPath = __DIR__; // Current directory
echo "<p><strong>Project Root:</strong> " . $projectPath . "</p>";

echo "<h3>Files in root folder:</h3>";
$files = scandir($projectPath);
echo "<ul>";
foreach ($files as $file) {
    if ($file != '.' && $file != '..') {
        $fullPath = $projectPath . '/' . $file;
        $type = is_dir($fullPath) ? '📁 Folder' : '📄 File';
        echo "<li>$type: $file</li>";
    }
}
echo "</ul>";

// Check specific folders
$foldersToCheck = ['config', 'includes', 'assets', 'assets/css', 'assets/js', 'assets/images', 'customer'];
echo "<h3>Folder Check:</h3>";
foreach ($foldersToCheck as $folder) {
    $path = $projectPath . '/' . $folder;
    if (is_dir($path)) {
        echo "<p style='color: green;'>✅ $folder/ exists</p>";
        $subFiles = scandir($path);
        foreach ($subFiles as $file) {
            if ($file != '.' && $file != '..') {
                echo "&nbsp;&nbsp;&nbsp;- $file<br>";
            }
        }
    } else {
        echo "<p style='color: red;'>❌ $folder/ MISSING</p>";
    }
}
?>s