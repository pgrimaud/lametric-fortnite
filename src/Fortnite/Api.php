<?php

declare(strict_types=1);

namespace Fortnite;

use Fortnite\Exception\{InternalErrorException, MissingParameterException};
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class Api
{
    const PLATFORMS = [
        'PC' => 'pc',
        'Xbox1' => 'xbl',
        'PS4' => 'psn',
    ];

    const MODS = [
        'solo',
        'duo',
        'squad',
        'trio'
    ];

    /**
     * @var Validator
     */
    private Validator $validator;

    /**
     * @var string
     */
    private string $apiKey;

    /**
     * @param Validator $validator
     * @param string $apiKey
     */
    public function __construct(Validator $validator, string $apiKey)
    {
        $this->validator = $validator;
        $this->apiKey = $apiKey;
    }

    /**
     * @return array
     * @throws InternalErrorException
     * @throws MissingParameterException
     */
    public function fetchData(): array
    {
        try {

            $endpoint = 'https://fortniteapi.io/v1/lookup?' .
                'username=' .
                $this->validator->getParameters()['player'];

            $client = new Client();
            $res = $client->request('GET', $endpoint, [
                'headers' => [
                    'Authorization' => $this->apiKey,
                ],
            ]);

            $json = (string)$res->getBody();
            $data = json_decode($json, true);

        } catch (\Exception $e) {
            throw new MissingParameterException($e->getMessage());
        } catch (GuzzleException $e) {
            throw new InternalErrorException('Internal error');
        }

        $playerId = $data["account_id"];

        try {
            $endpoint = 'https://fortniteapi.io/v1/stats?account=' . $playerId;

            $client = new Client();
            $res = $client->request('GET', $endpoint, [
                'headers' => [
                    'Authorization' => $this->apiKey,
                ],
            ]);

            $json = (string)$res->getBody();
            $data = json_decode($json, true);


        } catch (\Exception $e) {
            throw new MissingParameterException($e->getMessage());
        } catch (GuzzleException $e) {
            throw new InternalErrorException('Internal error');
        }

        return $this->formatData($data);
    }

    /**
     * @param array $data
     *
     * @return array
     *
     * @throws \Exception
     */
    private function formatData(array $data): array
    {
        if (isset($data['error'])) {
            throw new \Exception($data['error']);
        }

        $dataToReturn = [
            'name' => $data['name'],
            'wins' => 0,
            'kills' => 0,
            'matches_played' => 0,
        ];

        $modsToFetch = [];

        foreach (self::MODS as $mod) {
            // Lametric switch values (true / false)...
            if ($this->validator->getParameters()['include' . ucwords($mod)] === 'true') {
                $modsToFetch[] = $mod;
            }
        }

        foreach ($modsToFetch as $mod) {
            if ($this->validator->getParameters()['include' . ucwords($mod)] && isset($data['global_stats'][$mod])) {
                $dataToReturn['wins'] += $data['global_stats'][$mod]['placetop1'];
                $dataToReturn['kills'] += $data['global_stats'][$mod]['kills'];
                $dataToReturn['matches_played'] += $data['global_stats'][$mod]['matchesplayed'];
            }
        }

        if ($dataToReturn['matches_played'] - $dataToReturn['wins'] > 0) {
            $looses = ($dataToReturn['kills'] / ($dataToReturn['matches_played'] - $dataToReturn['wins']));
        } else {
            $looses = 0;
        }

        if ($dataToReturn['matches_played'] > 0) {
            $winrate = ($dataToReturn['wins'] / $dataToReturn['matches_played']) * 100;
        } else {
            $winrate = 0;
        }

        // set values
        $dataToReturn['wins'] .= ' WINS';
        $dataToReturn['kd'] = round($looses, 2) . ' K/D';
        $dataToReturn['winrate'] = round($winrate, 2) . '%';

        return $dataToReturn;
    }
}
