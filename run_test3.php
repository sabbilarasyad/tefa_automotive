<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

// Capture output to variable
$output = '';
$callback = function ($type, $buffer) use (&$output) {
    $output .= $buffer;
    return '';
};

ob_start($callback);
$exitCode = $kernel->call('test', [
    '--filter' => 'VerifyApi',
    '--testdox' => true,
]);
$output = ob_get_clean();

file_put_contents('test_result.txt', "Exit code: $exitCode\n\n$output");
echo "Exit code: $exitCode\n";
echo "Output length: " . strlen($output) . "\n";
echo "First 500 chars:\n" . substr($output, 0, 500) . "\n";
echo "Last 500 chars:\n" . substr($output, -500) . "\n";

exit($exitCode);