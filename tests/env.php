<?php

require __DIR__ . '/../vendor/autoload.php';

$dotenv = new Symfony\Component\Dotenv\Dotenv;
$dotenv->usePutenv(true);
$dotenv->load(__DIR__ . '/.env');
$env = new ArrayObject($_ENV, ArrayObject::ARRAY_AS_PROPS | ArrayObject::STD_PROP_LIST);
