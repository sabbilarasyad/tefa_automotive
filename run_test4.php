<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

ob_start();
$code = $kernel->call('test', [
    '--filter' => 'VerifyApi',
    '--testdox' => true,
]);
$output = ob_get_clean();

file_put_contents('test_output.txt', "Exit code: $code\n\n$output");
echo "Exit code: $code\n";
echo "Output length: " . strlen($output) . "\n";