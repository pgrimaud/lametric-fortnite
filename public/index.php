<?php

use Fortnite\Api;
use Fortnite\Response;
use Fortnite\Validator;

require_once __DIR__ . '/../vendor/autoload.php';

try {

    $validator = new Validator($_GET);
    $validator->check();

    $api   = new Api($validator);
    $sdata = $api->getStatistics();

    /* $auth = Auth::login($credentials['email'], $credentials['password']);

    $user = 'Nilzèn';
    //$user = 'ZiiKoO';

    $sandy = $auth->profile->stats->lookup($user);

    print_r($sandy);

    echo $user . ' has won ' . $sandy->pc->solo->wins . ' solo games, ' . $sandy->pc->duo->wins . ' duo games and ' . $sandy->pc->squad->wins . ' squad games!';
    */

} Catch (Exception $e) {
    $response = new Response();
    echo $response->returnError($e->getMessage());
}
