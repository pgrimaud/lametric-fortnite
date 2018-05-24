<?php

use Fortnite\Api;
use Fortnite\Response;
use Fortnite\Validator;

require_once __DIR__ . '/../vendor/autoload.php';

$response = new Response();

try {
    $validator = new Validator($_GET);
    $validator->check();

    $api  = new Api($validator);
    $data = $api->fetchData();

    echo $response->returnData($validator, $data);
} Catch (Exception $e) {
    echo $response->returnError($e->getMessage());
}
