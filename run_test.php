<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

$input = new Symfony\Component\Console\Input\ArgvInput(['artisan', 'test', '--filter=VerifyApi']);
$output = new Symfony\Component\Console\Output\ConsoleOutput();

$exitCode = $kernel->handle($input, $output);
exit($exitCode);