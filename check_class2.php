<?php
require_once 'vendor/autoload.php';
$ref = new ReflectionClass('App\Http\Requests\Api\SparepartStockAdjustmentRequest');
echo 'File: ' . $ref->getFileName() . PHP_EOL;
echo 'Namespace: ' . $ref->getNamespaceName() . PHP_EOL;
echo 'Class: ' . $ref->getShortName() . PHP_EOL;