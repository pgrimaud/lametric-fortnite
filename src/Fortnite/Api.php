<?php

namespace Fortnite;

use Fortnite\Exception\ConfigException;

class Api
{
    /**
     * @var array
     */
    private $credentials;

    /**
     * @var Validator
     */
    private $validator;

    /**
     * Api constructor.
     * @param Validator $validator
     * @throws ConfigException
     */
    public function __construct(Validator $validator)
    {
        $credentialsFile = __DIR__ . '/../../config/credentials.php';

        if (!is_file($credentialsFile)) {
            throw new ConfigException('Internal error: missing config file');
        } else {
            $this->credentials = require $credentialsFile;
        }

        $this->validator = $validator;
    }

    public function getStatistics()
    {
        $auth = Auth::login($this->credentials['email'], $this->credentials['password']);
    }
}
