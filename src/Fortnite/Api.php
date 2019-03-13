<?php

namespace Fortnite;

use Fortnite\Exception\ConfigException;
use Fortnite\Exception\InternalErrorException;
use Fortnite\Exception\MissingParameterException;
use GuzzleHttp\Client;
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

            $endpoint = 'https://api.fortnitetracker.com/v1/profile/' .
                $this->validator->getParameters()['platform'] . '/' .
                $this->validator->getParameters()['player'];

            $client = new Client();
            $res    = $client->request('GET', $endpoint, [
                'headers' => [
                    'TRN-Api-Key' => $this->credentials['api-key'],
                ]
            ]);

            $data = (string)$res->getBody();
            print_r($data);
            exit;

        } catch (\Exception $e) {
            throw new MissingParameterException($e->getMessage());
        } catch (GuzzleException $e) {
            throw new InternalErrorException('Internal error');
        }

        return $this->formatData($data);
    }

    /**
     * @param $data
     * @return array
     */
    private function formatData($data)
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
