<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

$code = $kernel->call('test', [
    '--filter' => 'VerifyApi',
    '--testdox' => true,
]);

echo "Exit code: $code\n";