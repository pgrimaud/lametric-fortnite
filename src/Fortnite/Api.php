<?php

declare(strict_types=1);

namespace Fortnite;

use Fortnite\Exception\{InternalErrorException, MissingParameterException};
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class Api
{
    const PLATFORMS = [
        'PC'    => 'pc',
        'Xbox1' => 'xbl',
        'PS4'   => 'psn',
    ];

    const MODS = [
        'solo'  => 'p2',
        'duo'   => 'p10',
        'squad' => 'p9',
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
        $this->apiKey    = $apiKey;
    }

    /**
     * @return array
     * @throws InternalErrorException
     * @throws MissingParameterException
     */
    public function fetchData(): array
    {
        $platform = self::PLATFORMS[$this->validator->getParameters()['platform']];

        try {

            $endpoint = 'https://api.fortnitetracker.com/v1/profile/' .
                $platform . '/' .
                $this->validator->getParameters()['player'];

            $client = new Client();
            $res    = $client->request('GET', $endpoint, [
                'headers' => [
                    'TRN-Api-Key' => $this->apiKey,
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
            'name'           => $data['epicUserHandle'],
            'wins'           => 0,
            'kills'          => 0,
            'matches_played' => 0,
        ];

        $modsToFetch = [];

        foreach (self::MODS as $mod => $apiMod) {
            // Lametric switch values (true / false)...
            if ($this->validator->getParameters()['include' . ucwords($mod)] === 'true') {
                $modsToFetch[$mod] = $apiMod;
            }
        }

        foreach ($modsToFetch as $mod => $apiMod) {
            if ($this->validator->getParameters()['include' . ucwords($mod)] && isset($data['stats'][$apiMod])) {
                $dataToReturn['wins']           += $data['stats'][$apiMod]['top1']['valueInt'];
                $dataToReturn['kills']          += $data['stats'][$apiMod]['kills']['valueInt'];
                $dataToReturn['matches_played'] += $data['stats'][$apiMod]['matches']['valueInt'];
            }
        }

        $looses  = $dataToReturn['matches_played'] - $dataToReturn['wins'];
        $winrate = ($dataToReturn['wins'] / $dataToReturn['matches_played']) * 100;

        // set values
        $dataToReturn['wins']    .= ' WINS';
        $dataToReturn['kd']      = round(($dataToReturn['kills'] / $looses), 2) . ' K/D';
        $dataToReturn['winrate'] = round($winrate, 2) . '%';

        return $dataToReturn;
    }
}
