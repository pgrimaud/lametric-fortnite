<?php

use Fortnite\Auth;

require_once __DIR__ . '/../vendor/autoload.php';

$credentialsFile = __DIR__ . '/../config/credentials.php';

if (is_file($credentialsFile)) {
    $credentials = require __DIR__ . '/../config/credentials.php';

    echo '{
    "frames": [
        {
            "text": "ortnite",
            "icon": "i20640"
        }
    ]
}';

//    $auth = Auth::login($credentials['email'], $credentials['password']);
//
//    $user = 'Nilzèn';
//    //$user = 'ZiiKoO';
//
//    $sandy = $auth->profile->stats->lookup($user);
//
//    print_r($sandy);
//
//    echo $user . ' has won ' . $sandy->pc->solo->wins . ' solo games, ' . $sandy->pc->duo->wins . ' duo games and ' . $sandy->pc->squad->wins . ' squad games!';

} else {
    throw new \Fortnite\Exception\ConfigException('Missing config file');
}
