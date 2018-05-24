<?php

namespace Fortnite;

use Fortnite\Exception\ConfigException;
use Fortnite\Exception\InternalErrorException;
use Fortnite\Exception\MissingParameterException;
use GuzzleHttp\Exception\GuzzleException;

class Api
{
    const PLATFORMS = [
        'PC'    => 'pc',
        'Xbox1' => 'xb1',
        'PS4'   => 'ps4',
    ];

    const MODS = [
        'solo',
        'duo',
        'squad',
    ];

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
        if (!is_file(__DIR__ . '/../../config/credentials.php')) {
            throw new ConfigException('Internal error: missing config file');
        } else {
            $this->credentials = require __DIR__ . '/../../config/credentials.php';
        }

        $this->validator = $validator;
    }

    /**
     * @return array
     * @throws InternalErrorException
     * @throws MissingParameterException
     */
    public function fetchData()
    {
        try {
            $auth = Auth::login($this->credentials['email'], $this->credentials['password']);
            /** @var Stats $data */
            $data = $auth->profile->stats->lookup($this->validator->getParameters()['player']);
        } catch (\Exception $e) {
            throw new MissingParameterException($e->getMessage());
        } catch (GuzzleException $e) {
            throw new InternalErrorException('Internal error');
        }

        return $this->formatData($data);
    }

    /**
     * @param Stats $data
     * @return array
     */
    private function formatData(Stats $data)
    {
        $dataToReturn = [
            'name'           => $data->display_name,
            'wins'           => 0,
            'kills'          => 0,
            'matches_played' => 0,
        ];

        $platform = self::PLATFORMS[$this->validator->getParameters()['platform']];

        $modsToFetch = [];

        foreach (self::MODS as $mod) {
            // Lametric switch values (true / false)...
            if ($this->validator->getParameters()['include' . ucwords($mod)] === 'true') {
                $modsToFetch[] = $mod;
            }
        }

        foreach ($modsToFetch as $mod) {
            if ($this->validator->getParameters()['include' . ucwords($mod)] && count($data->{$platform}->{$mod})) {
                $dataToReturn['wins']           += $data->{$platform}->{$mod}->wins;
                $dataToReturn['kills']          += $data->{$platform}->{$mod}->kills;
                $dataToReturn['matches_played'] += $data->{$platform}->{$mod}->matches_played;
            }
        }

        // set values
        $dataToReturn['wins']    .= ' WINS';
        $dataToReturn['kd']      = round(
            $dataToReturn['kills'] /
            ($dataToReturn['matches_played'] - $dataToReturn['wins']),
            2
        ) . ' K/D';
        $dataToReturn['winrate'] = round(($dataToReturn['wins'] / $dataToReturn['matches_played']) * 100, 2) . '%';

        return $dataToReturn;
    }
}
