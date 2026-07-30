<?php

$dir = new RecursiveIteratorIterator(new RecursiveDirectoryIterator('.'));
$found = false;

foreach ($dir as $file) {
    if ($file->getExtension() !== 'php') continue;
    $path = $file->getPathname();
    $content = file_get_contents($path);

    if (substr($content, 0, 3) === "\xEF\xBB\xBF") {
        echo "BOM found: $path\n";
        file_put_contents($path, substr($content, 3));
        echo "  -> stripped\n";
        $found = true;
    }
}

if (!$found) {
    echo "No BOM found in any .php file.\n";
} else {
    echo "Done.\n";
}