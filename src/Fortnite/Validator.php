<?php

declare(strict_types=1);

namespace Fortnite;

use Fortnite\Exception\MissingParameterException;

class Validator
{
    /**
     * @var array
     */
    private array $defaultParameters = [
        'includeSolo' => 'true',
        'includeDuo' => 'true',
        'includeSquad' => 'true',
        'includeTrio' => 'true',
        'showWins' => 'true',
        'showWinrate' => 'true',
        'showKd' => 'true',
    ];

    /**
     * @var array
     */
    private array $parameters = [];

    /**
     * @var array
     */
    private array $mandatoryParameters = [
        'player',
    ];

    /**
     * @param array $parameters
     * @param array $config
     */
    public function __construct(array $parameters = [], array $config = [])
    {
        $this->parameters = $parameters;

        if ((!isset($this->parameters['player']) || $this->parameters['player'] === '') && isset($config['player'])) {
            $this->parameters['player'] = $config['player'];
        }

        if ((!isset($this->parameters['api_key']) || $this->parameters['api_key'] === '') && isset($this->parameters['includeApiKey']) && $this->parameters['includeApiKey'] !== '') {
            $this->parameters['api_key'] = $this->parameters['includeApiKey'];
        }

        if ((!isset($this->parameters['api_key']) || $this->parameters['api_key'] === '') && isset($config['api_key'])) {
            $this->parameters['api_key'] = $config['api_key'];
        }

        foreach ($this->defaultParameters as $parameter => $defaultValue) {
            if (!isset($this->parameters[$parameter]) || $this->parameters[$parameter] === '') {
                $this->parameters[$parameter] = $defaultValue;
            }
        }
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
