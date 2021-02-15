<?php

declare(strict_types=1);

namespace Fortnite;

use Fortnite\Exception\MissingParameterException;

class Validator
{
    /**
     * @var array
     */
    private array $parameters = [];

    /**
     * @var array
     */
    private array $mandatoryParameters = [
        'platform',
        'player',
    ];

    /**
     * @param array $parameters
     */
    public function __construct(array $parameters = [])
    {
        $this->parameters = $parameters;
    }

    /**
     * @throws MissingParameterException
     */
    public function check(): void
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
    public function getParameters(): array
    {
        return $this->parameters;
    }
}
