<?php

require_once __DIR__ . '/../vendor/autoload.php';

$credentialsFile = __DIR__ . '/../config/credentials.php';

if (is_file($credentialsFile)) {
    $credentials = require __DIR__ . '/../config/credentials.php';


} else {
    throw new \Fortnite\Exception\ConfigException('Missing config file');
}
