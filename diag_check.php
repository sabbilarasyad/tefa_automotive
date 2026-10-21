<?php
// Temporary diagnostic script
require __DIR__ . '/vendor/autoload.php';

$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);

$output = new \Symfony\Component\Console\Output\BufferedOutput();
$kernel->call('tefa:check', [], $output);
echo "OUTPUT:\n";
echo $output->fetch();
