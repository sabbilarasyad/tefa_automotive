<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

// Capture output
ob_start();
$exitCode = $kernel->call('test', [
    '--filter' => 'VerifyApi',
    '--testdox' => true,
]);
$output = ob_get_clean();

echo "Exit code: $exitCode\n";
echo $output;

exit($exitCode);