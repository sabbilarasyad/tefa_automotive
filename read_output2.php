<?php

$content = file_get_contents('test_output.txt');
$len = strlen($content);
echo "File length: $len\n\n";

// Output in small chunks
$chunkSize = 500;
for ($i = 0; $i < $len; $i += $chunkSize) {
    echo "=== CHUNK $i ===\n";
    echo substr($content, $i, $chunkSize);
    echo "\n=== END CHUNK ===\n\n";
}