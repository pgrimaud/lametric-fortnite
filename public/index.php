<?php

declare(strict_types=1);

use Fortnite\{Api, Response, Validator};

require_once __DIR__ . '/../vendor/autoload.php';
$config = require_once __DIR__ . '/../config/parameters.php';

if (!empty($config['sentry_key'])) {
    Sentry\init(['dsn' => $config['sentry_key']]);
}

$response = new Response();

try {
    $validator = new Validator($_GET, $config);
    $validator->check();

    $apiKey = trim((string)($validator->getParameters()['api_key'] ?? ''));
    if ($apiKey === '') {
        $apiKey = $config['api_key'] ?? '';
    }

    $api  = new Api($validator, $apiKey);
    $data = $api->fetchData();

    echo $response->returnData($validator, $data);
} catch (Exception $e) {
    echo $response->returnError($e->getMessage());
}
