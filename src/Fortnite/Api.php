<?php

declare(strict_types=1);

namespace Fortnite;

use Fortnite\Exception\{InternalErrorException, MissingParameterException};
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class Api
{
    private const API_BASE_URL = 'https://prod.api-fortnite.com/api';

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
    public function __construct(Validator $validator, string $apiKey = '')
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

            $endpoint = self::API_BASE_URL . '/v1/account/displayName/' .
                urlencode($this->validator->getParameters()['player']);

            $client = new Client();
            $res = $client->request('GET', $endpoint, [
                'headers' => [
                    'x-api-key' => $this->apiKey,
                ],
                'http_errors' => false,
            ]);

            if ($res->getStatusCode() !== 200) {
                throw new InternalErrorException('Internal error: HTTP ' . $res->getStatusCode());
            }

            $json = (string)$res->getBody();
            $data = json_decode($json, true);

        } catch (\Exception $e) {
            throw new MissingParameterException($e->getMessage());
        } catch (GuzzleException $e) {
            throw new InternalErrorException('Internal error');
        }

        if (!isset($data['id'])) {
            throw new MissingParameterException(
                sprintf('Player %s not found', $this->validator->getParameters()['player'])
            );
        } else {
            $playerId = $data['id'];
        }

        try {
            $endpoint = self::API_BASE_URL . '/v2/stats/' . urlencode($playerId);

            $client = new Client();
            $res = $client->request('GET', $endpoint, [
                'headers' => [
                    'x-api-key' => $this->apiKey,
                ],
                'http_errors' => false,
            ]);

            if ($res->getStatusCode() !== 200) {
                throw new InternalErrorException('Internal error: HTTP ' . $res->getStatusCode());
            }

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
            'name' => $this->validator->getParameters()['player'],
            'wins' => 0,
            'kills' => 0,
            'matches_played' => 0,
        ];

        $modsToFetch = [];

        foreach (self::MODS as $mod) {
            if (($this->validator->getParameters()['include' . ucwords($mod)] ?? '') === 'true') {
                $modsToFetch[] = $mod;
            }
        }

        if (isset($data['stats']) && is_array($data['stats'])) {
            foreach ($data['stats'] as $statKey => $value) {
                if (!is_numeric($value)) {
                    continue;
                }

                foreach ($modsToFetch as $mod) {
                    if (strpos($statKey, '_' . $mod) === false) {
                        continue;
                    }

                    if (str_starts_with($statKey, 'br_placetop1_')) {
                        $dataToReturn['wins'] += (int)$value;
                    }

                    if (str_starts_with($statKey, 'br_kills_')) {
                        $dataToReturn['kills'] += (int)$value;
                    }

                    if (str_starts_with($statKey, 'br_matchesplayed_')) {
                        $dataToReturn['matches_played'] += (int)$value;
                    }
                }
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
