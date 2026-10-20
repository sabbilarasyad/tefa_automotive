<?php

$content = file_get_contents('test_output.txt');
$len = strlen($content);
echo "File length: $len\n\n";

// Output in chunks
$chunkSize = 2000;
for ($i = 0; $i < $len; $i += $chunkSize) {
    echo substr($content, $i, $chunkSize);
}