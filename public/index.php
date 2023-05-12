<?php

declare(strict_types=1);

use Fortnite\{Api, Response, Validator};

require_once __DIR__ . '/../vendor/autoload.php';
$config = require_once __DIR__ . '/../config/parameters.php';

Sentry\init(['dsn' => $config['sentry_key']]);

$response = new Response();

try {
    $validator = new Validator($_GET);
    $validator->check();

    $api  = new Api($validator, $config['api-key']);
    $data = $api->fetchData();

    echo $response->returnData($validator, $data);
} catch (Exception $e) {
    echo $response->returnError($e->getMessage());
}
