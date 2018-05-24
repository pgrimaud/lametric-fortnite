<?php

namespace Fortnite;

use Fortnite\Exception\MissingParameterException;

class Validator
{
    /**
     * @var array
     */
    private $parameters = [];

    /**
     * @var array
     */
    private $mandatoryParameters = [
        'platform',
        'player'
    ];

    /**
     * Validator constructor.
     * @param $parameters
     */
    public function __construct($parameters)
    {
        $this->parameters = $parameters;
    }

    /**
     * @throws MissingParameterException
     */
    public function check()
    {
        foreach ($this->mandatoryParameters as $parameter) {
            if (empty($this->parameters[$parameter])) {
                throw new MissingParameterException('Missing ' . $parameter . ' name');
            }
        }
    }

    /**
     * @return array
     */
    public function getParameters()
    {
        return $this->parameters;
    }
}
